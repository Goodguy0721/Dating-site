<?php

namespace Pg\Modules\Payments\Models\Systems;

use Pg\Modules\Payments\Models\Payment_driver_model;

/**
 * Webmoney payment system driver model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Katya Kashkova <katya@pilotgroup.net>
 *
 * @version $Revision: 2 $ $Date: 2010-04-02 15:07:07 +0300 (Ср, 02 апр 2010) $ $Author: kkashkova $
 * */
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Webmoney_model extends Payment_driver_model
{
    public $payment_data = array(
        'gid'           => 'webmoney',
        'name'          => 'Webmoney',
        'settings_data' => 'a:2:{s:9:"seller_id";s:13:"R397656178472";s:11:"secret_word";s:10:"secret_key";}',
        'logo'          => 'logo_webmoney.png',
    );
    public $settings = array(
        "seller_id"   => array("type" => "text", "content" => "string", "size" => "middle"),
        "secret_word" => array("type" => "text", "content" => "string", "size" => "middle"),
    );
    protected $variables = array(
        "LMI_PAYEE_PURSE"    => "seller_id",
        "LMI_PAYMENT_AMOUNT" => "amount",
        "LMI_PAYMENT_NO"     => "id_payment",
        "LMI_MODE"           => "test_mode",
        "LMI_PAYER_WM"       => "payer_wm",
        "LMI_PAYER_PURSE"    => "payer_purse",
        "LMI_SYS_INVS_NO"    => "sys_invs_no",
        "LMI_SYS_TRANS_NO"   => "sys_trans_no",
        "LMI_SYS_TRANS_DATE" => "sys_trans_date",
        "LMI_HASH"           => "hash",
    );
    protected $pre_variables = array(
        "LMI_PAYEE_PURSE"    => "seller_id",
        "LMI_PAYMENT_AMOUNT" => "amount",
        "LMI_PAYMENT_NO"     => "id_payment",
        "LMI_MODE"           => "test_mode",
        "LMI_PAYER_WM"       => "payer_wm",
        "LMI_PAYER_PURSE"    => "payer_purse",
    );

    public function __construct()
    {
        parent::__construct();
    }

    public function func_request($payment_data, $system_settings)
    {
        $return = array("errors" => array(), "info" => array(), "data" => $payment_data);

        $send_data = array(
            "LMI_PAYEE_PURSE"    => $system_settings["settings_data"]["seller_id"],
            "LMI_PAYMENT_AMOUNT" => $payment_data["amount"],
            "LMI_PAYMENT_NO"     => $payment_data["id_payment"],
            "LMI_SIM_MODE"       => "0",
            //"LMI_PAYMENT_DESC" => $desc,
            "LMI_PAYMENT_DESC_BASE64" => base64_encode($payment_data["payment_data"]["name"]),
            "LMI_RESULT_URL"          => site_url() . "payments/responce/webmoney",
            "LMI_SUCCESS_URL"         => site_url(),
            "LMI_SUCCESS_METHOD"      => "POST",
            "LMI_FAIL_URL"            => site_url(),
            "LMI_FAIL_METHOD"         => "POST",
        );
        $this->send_data("https://merchant.webmoney.ru/lmi/payment.asp", $send_data, "post");

        return $return;
    }

    public function func_responce($payment_data, $system_settings)
    {
        $return = array("errors" => array(), "info" => array(), "data" => array(), "type" => "exit");

        if (isset($payment_data["LMI_PREREQUEST"]) && $payment_data["LMI_PREREQUEST"] == 1) {
            /// this is pre-order request

            foreach ($this->pre_variables as $payment_var => $site_var) {
                $data[$site_var] = isset($payment_data[$payment_var]) ? $this->CI->input->xss_clean($payment_data[$payment_var]) : "";
            }

            $error = false;

            if ($system_settings["settings_data"]["seller_id"] != $data["seller_id"]) {
                $error = true;
            }

            $this->CI->load->model("Payments_model");
            $site_payment_data = $this->CI->Payments_model->get_payment_by_id($data['id_payment']);
            if (floatval($site_payment_data["amount"]) != floatval($data['amount'])) {
                $error = true;
            }

            if ($error) {
                echo "ERROR";
            } else {
                echo "YES";
            }

            exit();
        } else {
            foreach ($this->variables as $payment_var => $site_var) {
                $data[$site_var] = isset($payment_data[$payment_var]) ? $this->CI->input->xss_clean($payment_data[$payment_var]) : "";
            }

            $error = false;

            if ($system_settings["settings_data"]["seller_id"] != $data["seller_id"]) {
                $error = true;
            }

            $this->CI->load->model("Payments_model");
            $site_payment_data = $this->CI->Payments_model->get_payment_by_id($data['id_payment']);
            if (floatval($site_payment_data["amount"]) != floatval($data['amount'])) {
                $error = true;
            }

            $server_side_hash = $data['seller_id'] . strval($data['amount']) . $data['id_payment'] .
                $data['test_mode'] . $data['sys_invs_no'] . $data['sys_trans_no'] .
                $data['sys_trans_date'] . $system_settings["settings_data"]["secret_word"] .
                $data['payer_purse'] . $data['payer_wm'];
            $server_side_hash = strtoupper(hash('sha256', $server_side_hash));
            if ($server_side_hash != $data["hash"]) {
                $error = true;
            }

            $return["data"] = $data;
            if ($error) {
                $return["data"]["status"] = -1;
            } else {
                $return["data"]["status"] = 1;
            }
        }

        return $return;
    }

    public function get_settings_map()
    {
        foreach ($this->settings as $param_id => $param_data) {
            $this->settings[$param_id]["name"] = l('system_field_' . $param_id, 'payments');
        }

        return $this->settings;
    }
}
