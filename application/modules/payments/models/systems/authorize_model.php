<?php

namespace Pg\Modules\Payments\Models\Systems;

use Pg\Modules\Payments\Models\Payment_driver_model;

/**
 * Authorize payment system driver model
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

class Authorize_model extends Payment_driver_model
{
    public $payment_data = array(
        "gid"           => "authorize",
        'name'          => 'Authorize.net',
        'settings_data' => 'a:2:{s:15:"transaction_key";s:16:"ab34986jg5678655";s:9:"seller_id";s:11:"authnettest";}',
        'logo'          => 'logo_authorize.png',
    );
    public $settings = array(
        "transaction_key" => array("type" => "text", "content" => "string", "size" => "middle"),
        "seller_id"       => array("type" => "text", "content" => "string", "size" => "middle"),
    );
    protected $variables = array(
        "x_response_code"        => "response_code",
        "x_response_subcode"     => "response_subcode",
        "x_response_reason_code" => "response_reason_code",
        "x_response_reason_text" => "response_reason_text",
        "x_trans_id"             => "trans_id",
        "x_invoice_num"          => "id_payment",
        "x_amount"               => "amount",
        "x_currency_code"        => "currency_gid",
    );

    /**
     * Available currencies
     *
     * @var
     */
    protected $currencies = array('AUD', 'USD', 'CAD', 'EUR', 'GBP', 'NZD');

    public function __construct()
    {
        parent::__construct();
    }

    public function func_request($payment_data, $system_settings)
    {
        $return = array("errors" => array(), "info" => array(), "data" => $payment_data);

        $time = time();

        $send_data = array(
            "x_login"          => $system_settings["settings_data"]["seller_id"],
            "x_amount"         => $payment_data["amount"],
            "x_invoice_num"    => $payment_data["id_payment"],
            "x_description"    => $payment_data["payment_data"]["name"],
            "x_test_request"   => "FALSE",
            "x_fp_sequence"    => $payment_data["id_payment"],
            "x_fp_timestamp"   => $time,
            "x_show_form"      => "PAYMENT_FORM",
            "x_relay_response" => "TRUE",
            "x_relay_url"      => site_url() . "payments/responce/authorize",
        );

        $hash_str = $system_settings["settings_data"]["seller_id"] . "^" . $payment_data["id_payment"] . "^" . $time . "^" . $payment_data["amount"] . "^";

        if (in_array($payment_data["currency_gid"], $this->currencies)) {
            $send_data["x_currency_code"] = $payment_data["currency_gid"];
            $hash_str .= $payment_data["currency_gid"];
        }

        if (phpversion() >= '5.1.2') {
            $hash_str = hash_hmac("md5", $hash_str, $system_settings["settings_data"]["transaction_key"]);
        } else {
            $fingerprint = bin2hex(mhash(MHASH_MD5, $hash_str, $system_settings["settings_data"]["transaction_key"]));
        }

        $send_data["x_fp_hash"] = $hash_str;

        $this->send_data("https://secure.authorize.net/gateway/transact.dll", $send_data, "post");

        return $return;
    }

    public function func_responce($payment_data, $system_settings)
    {
        $return = array("errors" => array(), "info" => array(), "data" => array(), "type" => "exit");

        foreach ($this->variables as $payment_var => $site_var) {
            $return["data"][$site_var] = isset($payment_data[$payment_var]) ? $this->CI->input->xss_clean($payment_data[$payment_var]) : "";
        }

        //// get status
        $return["data"]["status"] = 0;
        if (isset($return["data"]['response_code'])) {
            switch ($return["data"]['response_code']) {
                case "1": $return["data"]["status"] = 1;
                    break;
                case "2":
                case "3":
                    $return["data"]["status"] = -1;
                    break;
                default: $return["data"]["status"] = 0;
                    break;
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
