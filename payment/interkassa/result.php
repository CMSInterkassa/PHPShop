<?php
/**
 * Модуль оплаты через платежный шлюз "Интеркасса"
 * www@smartbyte.pro
 * @author www.gateon.net
 * @version 1.0
 * @package PHPShopPayment 5.x Enterprise
 * Модуль взаимодействия
 */

//write log
function WriteLog($MY_LMI_HASH) {
    $handle = fopen("../paymentlog.log", "a+");

    foreach ($_POST as $k => $v)
        @$post.=$k . "=" . $v . "\r\n";

    $str = "
      Interkassa Payment Start ------------------
      date=" . date("F j, Y, g:i a") . "
      $post
      MY_LMI_HASH=$MY_LMI_HASH
      REQUEST_URI=" . $_SERVER['REQUEST_URI'] . "
      IP=" . $_SERVER['REMOTE_ADDR'] . "
      Interkassa Payment End --------------------
      ";
    fwrite($handle, $str);
    fclose($handle);
}

function UpdateNumOrder($uid) {
    $last_num = substr($uid, -2);
    $total = strlen($uid);
    $ferst_num = substr($uid, 0, ($total - 2));
    return $ferst_num . "-" . $last_num;
}

// Parse config.ini
$SysValue = parse_ini_file("../../phpshop/inc/config.ini", 1);
while (list($section, $array) = each($SysValue))
    while (list($key, $value) = each($array))
        $SysValue['other'][chr(73) . chr(110) . chr(105) . ucfirst(strtolower($section)) . ucfirst(strtolower($key))] = $value;

//get secret key 
if(isset($_REQUEST['ik_pw_via']) && $_REQUEST['ik_pw_via'] == 'test_interkassa_test_xts'){
  $secret_key = $SysValue['interkassa']['ik_test_key'];
} else {
  $secret_key = $SysValue['interkassa']['ik_secret_key'];
}

//get request data
$data = array();
    foreach ($_REQUEST as $key => $value) {
    if (!preg_match('/ik_/', $key)) continue;
    $data[$key] = $value;
}

//create sign hash
$ik_sign = $data['ik_sign'];
unset($data['ik_sign']);
ksort($data, SORT_STRING);
array_push($data, $secret_key);
$signString = implode(':', $data);
$sign = base64_encode(md5($signString, true));

if ($sign != (string)$_POST['ik_sign']) {
    echo "bad sign\n";
    WriteLog($sign."bad sign\n");
    exit();
} else {
// change order state to paid
// Connect to MySQL
  $link_db=mysqli_connect($SysValue['connect']['host'], $SysValue['connect']['user_db'], $SysValue['connect']['pass_db']);
  mysqli_select_db($link_db,$SysValue['connect']['dbase']);

  $new_uid = UpdateNumOrder($_POST['ik_pm_no']);

// If isset order
  $sql = "select uid from " . $SysValue['base']['table_name1'] . " where uid='$new_uid'";
  $result = mysqli_query($link_db,$sql);
  $row = mysqli_fetch_array($result);
  $uid = $row['uid'];

  if ($uid == $new_uid) {
// write payment to database
    $sql = "INSERT INTO " . $SysValue['base']['table_name33'] . " VALUES 
('$new_uid','Interkassa','" . $_POST['ik_am'] . "','" . time() . "')";        
    $result = mysqli_query($link_db,$sql);

    $sql = "UPDATE " . $SysValue['base']['table_name1'] . " SET statusi=4 WHERE uid='$new_uid'";
    $result = mysqli_query($link_db,$sql);
    WriteLog($sign);

// print OK signature
    echo "OK" . $_POST['ik_pm_no'] . "\n";
  } else {
    WriteLog($sign);
    echo "bad order num\n";
    exit();
  }
}
?>
