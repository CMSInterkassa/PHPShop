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

    // Create a stream
    $opts = array(
        'http' => array(
            'method' => "GET",
            'header' => "Authorization: Basic " . base64_encode("$username:$password")
        )
    );

    $context = stream_context_create($opts);
    $file = file_get_contents($remote_url, false, $context);
    $json_data = json_decode($file);

    if ($json_data->status != 'error') {
        $payment_systems = array();
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
        return $payment_systems;
    } else
        return '<strong style="color:red;">API connection error!<br>' . $json_data->message . '</strong>';
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
