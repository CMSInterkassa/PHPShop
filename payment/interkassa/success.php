<?
/**
 * Модуль оплаты через платежный шлюз "Интеркасса"
 * www@smartbyte.pro
 * @author www.gateon.net
 * @version 1.0
 * @package PHPShopPayment 5.x Enterprise
 * Модуль Успешной оплаты
 */

if(empty($GLOBALS['SysValue'])) exit(header("Location: /"));
if($_REQUEST['ik_inv_st'] == 'success'){
	$order_metod="Interkassa";
	$success_function=false;
	$my_crc = "NoN";
	$crc = "NoN";
	$inv_id = $_REQUEST['ik_pm_no'];
}

?>
