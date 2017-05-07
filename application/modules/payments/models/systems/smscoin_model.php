<?php

namespace Pg\Modules\Payments\Models\Systems;

use Pg\Modules\Payments\Models\Payment_driver_model;

/**
 * SMSCoin payment system driver model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * */
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class SMSCoin_model extends Payment_driver_model
{
    public $payment_data = array(
        'gid'           => 'smscoin',
        'name'          => 'SMS Coin',
        'settings_data' => 'a:2:{s:8:"purse_id";s:7:"smscoin";s:11:"secret_word";s:16:"smscoin@mail.com";}',
        'logo'          => 'logo_smscoin.png',
        'tarifs_type'   => 1,
    );
    public $settings = array(
        "purse_id"    => array("type" => "text", "content" => "string", "size" => "middle"),
        "secret_word" => array("type" => "text", "content" => "string", "size" => "middle"),
    );
    protected $variables = array(
        "s_purse"        => "purse_id",
        "s_order_id"     => "id_payment",
        "s_amount"       => "amount",
        "s_clear_amount" => "clear_amount",
        "s_inv"          => "inv",
        "s_phone"        => "phone",
        "s_sign_v2"      => "sign",
        "s_status"       => "status",
    );

    public function __construct()
    {
        parent::__construct();
    }

    public function func_request($payment_data, $system_settings)
    {
        $return = array("errors" => array(), "info" => array(), "data" => $payment_data);
        $description = mb_substr(str_replace(":", "", $payment_data["payment_data"]["name"]), 0, 127);
        $sign = $system_settings["settings_data"]["purse_id"] . "::" . $payment_data["id"]
            . "::" . $payment_data["amount"] . "::1::" . $description . "::" . $system_settings["settings_data"]["secret_word"];
        $send_data = array(
            "s_purse"        => $system_settings["settings_data"]["purse_id"],
            "s_order_id"     => $payment_data["id"],
            "s_amount"       => $payment_data["amount"],
            "s_clear_amount" => 1,
            "s_sign"         => md5($sign),
            "s_description"  => $description,
        );
        $this->send_data("http://service.smscoin.com/bank/", $send_data, "post");

        return $return;
    }

    public function func_responce($payment_data, $system_settings)
    {
        $return = array("errors" => array(), "info" => array(), "data" => array(), "type" => "exit");

        foreach ($this->variables as $payment_var => $site_var) {
            $return["data"][$site_var] = isset($payment_data[$payment_var]) ? $this->CI->input->xss_clean($payment_data[$payment_var]) : "";
        }

        $error = false;

        if ($system_settings["settings_data"]["purse_id"] != $return["data"]["purse_id"]) {
            $error = true;
        }

        $this->CI->load->model("Payments_model");
        $site_payment_data = $this->CI->Payments_model->get_payment_by_id($return['data']['id_payment']);
        if (floatval($site_payment_data['amount']) != floatval($return['data']['amount'])) {
            $error = true;
        }

        // making the reference signature
        if (isset($return["data"]["inv"]) && isset($return["data"]["phone"])) {
            $reference = md5(sprintf("%s::%s::%s::%s::%s::%s::%s", $system_settings["settings_data"]['secret_word'], $return["data"]["purse_id"], $return["data"]["id_payment"], $return["data"]["amount"], $return["data"]["clear_amount"], $return["data"]["inv"], $return["data"]["phone"]));
        } else {
            $reference = md5(sprintf("%s::%s::%s::%s::%s::%s", $system_settings["settings_data"]['secret_word'], $return["data"]["purse"], $return["data"]["id_payment"], $return["data"]["amount"], $return["data"]["clear_amount"], $return["data"]["status"]));
        }

        // validating the signature
        $return["data"]["status"] = ($return["data"]["sign"] == $reference) ? 1 : -1;

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
