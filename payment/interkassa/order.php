<?php
/**
 * Модуль оплаты через платежный шлюз "Интеркасса"
 * www@smartbyte.pro
 * @author www.gateon.net
 * @version 1.0
 * @package PHPShopPayment 5.x Enterprise
 * Модуль платежной формы
 */

if(empty($GLOBALS['SysValue'])) exit(header("Location: /"));

$pay_params = array();

// initialize variable
$secret_key = $SysValue['interkassa']['ik_secret_key'];
$test_key = $SysValue['interkassa']['ik_test_key'];
$test_mode = $SysValue['interkassa']['ik_test_mode'];
$sign_type = $SysValue['interkassa']['ik_sign_type'];
$merchantId = $SysValue['interkassa']['ik_merchant_id'];

$currency = $GLOBALS['PHPShopSystem']->getDefaultValutaIso();
$mrh_ouid = explode("-", $_POST['ouid']);
$ik_pm_id = $mrh_ouid[0]."".$mrh_ouid[1];
$amount = number_format($GLOBALS['SysValue']['other']['total'], 2, '.', '');
$desc = "#".$ik_pm_id;

//create sign
$data = array(
	'ik_co_id' => $merchantId,
	'ik_cur' => $currency,
	'ik_am' => $amount,
	'ik_pm_no' => $ik_pm_id,
	'ik_desc' => $desc
);

ksort($data, SORT_STRING);
$data['secret'] = $secret_key;
$signString = implode(':', $data);
$sign = base64_encode(md5($signString, true));
unset($data['secret']);

// Out HTMl payment form
$disp = '
<div align="center">

 <p><br></p>

<form name="payment" action="https://sci.interkassa.com/" method="POST">
<input type="hidden" name="ik_co_id" value="'.$merchantId.'">
<input type="hidden" name="ik_cur" value="'.$currency.'">
<input type="hidden" name="ik_am" value="'.$amount.'">
<input type="hidden" name="ik_pm_no" value="'.$ik_pm_id.'">
<input type="hidden" name="ik_desc" value="'.$desc.'">
<input type="hidden" name="ik_sign" value="'.$sign.'">
	  <table>
<tr>
	<td><img src="images/shop/icon-client-new.gif" alt="" width="16" height="16" border="0" align="left">
	<a href="javascript:payment.submit();"><u>Оплатить через платежную систему</u></a></td>
</tr>
</table>
      </form>
</div>';
?>