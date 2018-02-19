<?
/*
+-------------------------------------+
|  PHPShop Enterprise                 |
|  Success Function Interkassa        |
+-------------------------------------+
*/

if(empty($GLOBALS['SysValue'])) exit(header("Location: /"));

if(isset($_GET['ik_payment_ouid']) && !empty($_GET['payment']) && $_GET['payment'] == 'interkassa') {

    $inv_id = $_GET['ik_payment_ouid'];
    if(!empty($_SESSION['UsersId']))
      header("Location: {$SysValue['dir']['dir']}/users/order.html?order_info={$inv_id}#Order");
    else
      header("Location: {$SysValue['dir']['dir']}/clients/?mail={$_SESSION['uMail']}&order={$inv_id}");
    exit;
}