<?php
/**
 * Обработчик оповещения о платеже
 */
session_start();

require_once 'functions.php';

$_classPath = "/../../";
$classPath = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR;
include($classPath . "phpshop/class/obj.class.php");
PHPShopObj::loadClass("base");
PHPShopObj::loadClass("order");
PHPShopObj::loadClass("file");
PHPShopObj::loadClass("orm");
PHPShopObj::loadClass("payment");
PHPShopObj::loadClass("modules");
PHPShopObj::loadClass("system");

$PHPShopBase = new PHPShopBase($classPath . "phpshop/inc/config.ini");

class InterkassaPayment extends PHPShopPaymentResult {

    function __construct() {
        $this -> debug = false;
        $this -> log = true;
        $this->option();
        parent::__construct();
    }

    /**
     * Настройка модуля
     */
    function option() {
        $this->payment_name = 'interkassa';
    }

    /**
     * Проверка подписи
     * @return boolean
     */
    function check() {
        global $SysValue;

        $order_id = (int)$_REQUEST['ik_pm_no'];

        if($SysValue['interkassa']['ik_test_mode'])
            $secret_key = $SysValue['interkassa']['ik_test_key'];
        else
            $secret_key = $SysValue['interkassa']['ik_secret_key'];

        $ik_cashbox = $SysValue['interkassa']['ik_cashbox'];

        $sign = IkSignFormation($_POST, $secret_key);

        $this->out_summ = $_POST['ik_am'];
        $this->inv_id = $_REQUEST['ik_payment_ouid'];
        $this->crc = $_POST['ik_sign'];
        $this->my_crc = $sign;

        if ($this->my_crc == $this->crc && $_POST['ik_co_id'] == $ik_cashbox) {
            if ($_POST['ik_inv_st'] == 'success') {
                return true;
            } elseif ($_POST['ik_inv_st'] == 'fail') {
                return false;
            }
        }

        return false;
    }

    function true_num($uid) {
        return $uid;
    }

    function done() {
        $this->log();

       header("Location: /done/");
        exit;
    }

    /**
     * Ошибка
     */
    function error($type = 1) {
        $this->log();

        header("Location: /fail/");
        exit;
    }

}

(new InterkassaPayment());