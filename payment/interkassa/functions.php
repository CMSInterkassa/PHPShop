<?php
function IkSignFormation($data, $secret_key){
    if (!empty($data['ik_sign'])) unset($data['ik_sign']);

    $dataSet = array();
    foreach ($data as $key => $value) {
        if (!preg_match('/ik_/', $key)) continue;
        $dataSet[$key] = $value;
    }

    ksort($dataSet, SORT_STRING);
    array_push($dataSet, $secret_key);
    $arg = implode(':', $dataSet);
    $ik_sign = base64_encode(md5($arg, true));

    return $ik_sign;
}

function getIkPaymentSystems($id_cashBox, $ik_api_id, $ik_api_key)
{
    $username = $ik_api_id;
    $password = $ik_api_key;
    $remote_url = 'https://api.interkassa.com/v1/paysystem-input-payway?checkoutId=' . $id_cashBox;

    $businessAcc = getIkBusinessAcc($username, $password);

    $ikHeaders = [];
    $ikHeaders[] = "Authorization: Basic " . base64_encode("$username:$password");
    if(!empty($businessAcc)) {
        $ikHeaders[] = "Ik-Api-Account-Id: " . $businessAcc;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $remote_url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $ikHeaders);
    $response = curl_exec($ch);

    if(empty($response))
        return '<strong style="color:red;">Error!!! System response empty!</strong>';

    $json_data = json_decode($response);
    if ($json_data->status != 'error') {
        $payment_systems = array();
        if(!empty($json_data->data)){
            foreach ($json_data->data as $ps => $info) {
                $payment_system = $info->ser;
                if (!array_key_exists($payment_system, $payment_systems)) {
                    $payment_systems[$payment_system] = array();
                    foreach ($info->name as $name) {
                        if ($name->l == 'en') {
                            $payment_systems[$payment_system]['title'] = ucfirst($name->v);
                        }
                        $payment_systems[$payment_system]['name'][$name->l] = $name->v;
                    }
                }
                $payment_systems[$payment_system]['currency'][strtoupper($info->curAls)] = $info->als;
            }
        }

        return !empty($payment_systems)? $payment_systems : '<strong style="color:red;">API connection error or system response empty!</strong>';
    } else {
        if(!empty($json_data->message))
            return '<strong style="color:red;">API connection error!<br>' . $json_data->message . '</strong>';
        else
            return '<strong style="color:red;">API connection error or system response empty!</strong>';
    }
}

function getIkBusinessAcc($username = '', $password = '')
{
    $tmpLocationFile = __DIR__ . '/tmpLocalStorageBusinessAcc.ini';
    $dataBusinessAcc = function_exists('file_get_contents')? file_get_contents($tmpLocationFile) : '{}';
    $dataBusinessAcc = json_decode($dataBusinessAcc, 1);
    $businessAcc = is_string($dataBusinessAcc['businessAcc'])? trim($dataBusinessAcc['businessAcc']) : '';
    if(empty($businessAcc) || sha1($username . $password) !== $dataBusinessAcc['hash']) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://api.interkassa.com/v1/account');
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ["Authorization: Basic " . base64_encode("$username:$password")]);
        $response = curl_exec($curl);

        if (!empty($response['data'])) {
            foreach ($response['data'] as $id => $data) {
                if ($data['tp'] == 'b') {
                    $businessAcc = $id;
                    break;
                }
            }
        }

        if(function_exists('file_put_contents')){
            $updData = [
                'businessAcc' => $businessAcc,
                'hash' => sha1($username . $password)
            ];
            file_put_contents($tmpLocationFile, json_encode($updData, JSON_PRETTY_PRINT));
        }

        return $businessAcc;
    }

    return $businessAcc;
}


function getAnswerFromAPI($data){
    $ch = curl_init('https://sci.interkassa.com/');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    return $result;
}

function getConfigPHPShop($tag = '')
{
    $SysValue = parse_ini_file( dirname(dirname(__DIR__)) . "/phpshop/inc/config.ini", 1);
    while (list($section, $array) = each($SysValue)) {
        while (list($key, $value) = each($array))
            $SysValue['other'][chr(73) . chr(110) . chr(105) . ucfirst(strtolower($section)) . ucfirst(strtolower($key))] = $value;
    }

    return (!empty($SysValue[$tag])? $SysValue[$tag] : $SysValue);
}

function writeLog($MY_HASH = '', $data = array()) {
    $addData = (!empty($data) && is_array($data))? json_encode($data, JSON_PRETTY_PRINT) : '';

    $str = "
  Interkassa Payment Start ------------------
  date=" . date("F j, Y, g:i a") . "\n
  $addData\n
  MY_HASH = $MY_HASH
  REQUEST_URI = " . $_SERVER['REQUEST_URI'] . "
  IP = " . $_SERVER['REMOTE_ADDR'] . "
  Interkassa Payment End --------------------
  ";
   file_put_contents(__DIR__ . 'info.log', $str, FILE_APPEND);
}

function UpdateNumOrder($uid) {
    $last_num = substr($uid, -2);
    $total = strlen($uid);
    $ferst_num = substr($uid, 0, ($total - 2));
    return $ferst_num . "-" . $last_num;
}
