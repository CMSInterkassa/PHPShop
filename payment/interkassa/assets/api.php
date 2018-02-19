<?php
if($_SERVER['REQUEST_METHOD']!='POST') die();
session_start();
require_once dirname(__DIR__) . '/functions.php';
switch ($_GET['nYg']) {
  case 'nYs':
    $configInterkassa = getConfigPHPShop('interkassa');
    $sign = json_encode(array('sign'=>IkSignFormation($_POST, $configInterkassa['ik_secret_key'])));
    echo $sign;
    break;
  case 'nYa':
    $configInterkassa = getConfigPHPShop('interkassa');
   $post_data = $_POST;
    $sign = IkSignFormation($post_data, $configInterkassa['ik_secret_key']);
    $post_data['ik_sign'] = $sign;
    $result = getAnswerFromAPI($post_data);
    writeLog('', $result);
    echo json_encode($result);
  break;
}
exit;