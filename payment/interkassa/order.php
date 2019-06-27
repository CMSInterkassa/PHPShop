<?php
if(empty($GLOBALS['SysValue'])) exit(header("Location: /"));

require_once 'functions.php';

$ik_dir = "/payment/interkassa/";
$pay_params = array();
// initialize variable
$secret_key = $SysValue['interkassa']['ik_secret_key'];
$ik_cashbox = $SysValue['interkassa']['ik_cashbox'];
$ik_api_id = $SysValue['interkassa']['ik_api_id'];
$ik_api_key = $SysValue['interkassa']['ik_api_key'];

$currency = $GLOBALS['PHPShopSystem']->getDefaultValutaIso();
$mrh_ouid = explode("-", $_POST['ouid']);
$ik_pm_id = $mrh_ouid[0]."".$mrh_ouid[1];
$amount = number_format($GLOBALS['SysValue']['other']['total'], 2, '.', '');
$desc = "order ".$ik_pm_id;

  $_uri = ($_SERVER['HTTPS'] ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
  $ik_ia_u = "$_uri/payment/interkassa/result.php?ik_payment_ouid=" . $_POST['ouid'];
  $ik_suc_u = "$_uri/done/";
  $ik_fal_u = "$_uri/fail/";
//create sign
$FormData = array(
	'ik_co_id' => $ik_cashbox,
	'ik_cur' => $currency,
	'ik_am' => $amount,
	'ik_pm_no' => $ik_pm_id,
	'ik_desc' => $desc,
    'ik_ia_u' => $ik_ia_u,
    'ik_suc_u' => $ik_suc_u,
    'ik_fal_u' => $ik_fal_u
);

if($SysValue['interkassa']['ik_test_mode'])
    $FormData['ik_pw_via'] = 'test_interkassa_test_xts';

$FormData['ik_sign'] = IkSignFormation($FormData, $secret_key);

$disp = '
<div align="center">
 <p><br></p>
<form id="checkout_confirmation" name="payment" action="javascript:selpayIK.selPaysys();" method="POST">';
    foreach ($FormData as $fl => $value) {
        $disp.= '<input type="hidden" name="' . $fl . '" value="' . $value . '">';
    }
$disp.='<table><tr><td>
    <img src="/payment/interkassa/assets/paysystems/interkassa.png" border="0" >
	<button type="submit" class="btn btn-info btn-lg">Оплатить</button>
    </td></tr></table>
</form>
</div>';
$html = '<link rel="stylesheet" type="text/css" href="' . $ik_dir . 'assets/ik.css">'
    . '<script type="text/javascript" src="' . $ik_dir . 'assets/ik.js"></script>';
if($SysValue['interkassa']['ik_api_enable']) {
  $paysys = getIkPaymentSystems($ik_cashbox, $ik_api_id, $ik_api_key);
  if (is_array($paysys)) {
    $html .= '<div class="ik_block">' .
        '<button type="button" class="btn btn-info btn-lg sel-ps-ik" data-toggle="modal" style="display:none" data-target=".ik_modal">modal</button>' .
        '<div class="modal fade ik_modal" tabindex="-1" role="dialog">' .
        '<div class="modal-dialog modal-lg" role="document">' .
        '<div class="modal-content" id="plans">' .
        '<div class="modal-body">' .
        '<h3>1. Выберите удобный способ оплаты<br>2. Укажите валюту<br>3. Нажмите "Оплатить"</h3>' .
        '<div class="row">';
    foreach ($paysys as $ps => $info) {
      $currencyQ = '';
      foreach ($info['currency'] as $currency => $currencyAlias)
        $currencyQ .= '<a class="btn btn-primary-currency btn-sm notActive" data-toggle="fun"	data-title="' . $currencyAlias . '">' . $currency . '</a>';
      $html .=
          '<div class="col-sm-3 text-center payment_system">' .
          '<div class="panel panel-warning panel-pricing">' .
          '<div class="panel-heading">' .
          '<div class="panel-image">' .
          '<img src="' . $ik_dir . 'assets/paysystems/' . $ps . '.png" alt="' . $info['title'] . '">' .
          '</div>' .
          '</div>' .
          '<div class="form-group">' .
          '<div class="input-group">' .
          '<div id="radioBtn" class="btn-group radioBtn">' .
          $currencyQ .
          '</div>' .
          '</div>' .
          '</div>' .
          '<div class="panel-footer">' .
          '<a class="btn btn-block btn-success ik-payment-confirmation" data-title="' . $ps . '" href="#">Оплатить через <br><strong>' . $info['title'] . '</strong></a>' .
          '</div>' .
          '</div>' .
          '</div>';
    }
    $html .= '</div>' .
        '</div>' .
        '</div>' .
        '</div>' .
        '</div>' .
        '</div>';
  }
}
$disp .= $html;
