<?php

namespace Pg\Modules\Payments\Models\Systems;

use Pg\Modules\Payments\Models\Payment_driver_model;

/**
 * Egold payment system driver model
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

class Egold_model extends Payment_driver_model
{
    public $payment_data = array();
    public $settings = array(
        "seller_id"   => array("type" => "text", "content" => "string", "size" => "middle"),
        "seller_name" => array("type" => "text", "content" => "string", "size" => "middle"),
    );
    protected $variables = array(
        "PAYEE_ACCOUNT"  => "seller_id",
        "PAYMENT_AMOUNT" => "amount",
        "PAYMENT_ID"     => "id_payment",
    );

    public function __construct()
    {
        parent::__construct();
    }

    public function func_request($payment_data, $system_settings)
    {
        $return = array("errors" => array(), "info" => array(), "data" => $payment_data);

        switch ($payment_data["currency_gid"]) {
            case "USD": $currency = 1;
                break;
            case "EUR": $currency = 85;
                break;
            case "GBP": $currency = 44;
                break;
            case "CAD": $currency = 2;
                break;
            case "JPY": $currency = 81;
                break;
            case "AUD": $currency = 61;
                break;
            default: $currency = 1;
        }

        $send_data = array(
            "PAYEE_ACCOUNT"    => $system_settings["settings_data"]["seller_id"],
            "PAYEE_NAME"       => $system_settings["settings_data"]["seller_name"],
            "PAYMENT_METAL_ID" => "1",
            "PAYMENT_UNITS"    => $currency,
            "PAYMENT_AMOUNT"   => $payment_data["amount"],
            "PAYMENT_ID"       => $payment_data["id_payment"],
            "STATUS_URL"       => site_url() . "payments/responce/egold",
            "PAYMENT_URL"      => site_url(),
            "NOPAYMENT_URL"    => site_url(),
        );
        $this->send_data("https://www.e-gold.com/sci_asp/payments.asp", $send_data, "post");

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
        if (isset($payment_data['PAYMENT_ID'])) {
            $return["data"]["status"] = -1;
        } else {
            $return["data"]["status"] = 1;
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
