<?php

namespace Pg\Modules\Payments\Models\Systems;

use Pg\Modules\Payments\Models\Payment_driver_model;

/**
 * Tcheckout payment system driver model
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

class Tcheckout_model extends Payment_driver_model
{
    public $payment_data = array(
        'gid'           => 'tcheckout',
        'name'          => '2Checkout',
        'settings_data' => 'a:2:{s:9:"seller_id";s:9:"2checkout";s:11:"secret_word";s:18:"2checkout@mail.com";}',
        'logo'          => 'logo_tcheckout.png',
    );
    public $settings = array(
        "seller_id"   => array("type" => "text", "content" => "string", "size" => "middle"),
        "secret_word" => array("type" => "text", "content" => "string", "size" => "middle"),
    );
    protected $variables = array(
        "sid"           => "seller_id",
        "cart_order_id" => "id_payment",
        "order_number"  => "id_order",
        "total"         => "amount",
        "key"           => "hash",
    );
    protected $notifications_variables = array(
        "vendor_id"  => "seller_id",
        "invoice_id" => "id_payment",
        "sale_id"    => "amount",
        "md5_hash"   => "hash",
    );
    protected $currencies = array('ARS', 'AUD', 'BRL', 'GBP', 'CAD', 'DKK', 'EUR', 'HKD',
        'INR', 'ILS', 'JPY', 'LTL', 'MYR', 'MXN', 'NZD', 'NOK',
        'PHP', 'RON', 'RUB', 'SGD', 'ZAR', 'SEK', 'CHF', 'TRY',
        'AED', 'USD',);

    public function __construct()
    {
        parent::__construct();
    }

    public function func_request($payment_data, $system_settings)
    {
        $return = array("errors" => array(), "info" => array(), "data" => $payment_data);
        $send_data = array(
            "sid"           => $system_settings["settings_data"]["seller_id"],
            "total"         => $payment_data["amount"],
            "cart_order_id" => "CART-" . $payment_data["id_payment"],
            //"demo" => "Y",
            "x_receipt_link_url" => site_url() . "payments/responce/tcheckout",
        );

        if (in_array($payment_data["currency_gid"], $this->currencies)) {
            $send_data["currency_code"] = $payment_data["currency_gid"];
        }

        $this->send_data("https://www.2checkout.com/checkout/purchase", $send_data, "get");

        return $return;
    }

    public function func_responce($payment_data, $system_settings)
    {
        $return = array("errors" => array(), "info" => array(), "data" => array(), "type" => "html");

        foreach ($this->variables as $payment_var => $site_var) {
            $return["data"][$site_var] = isset($payment_data[$payment_var]) ? $this->CI->input->xss_clean($payment_data[$payment_var]) : "";
        }

        if (isset($return['data']['id_payment'])) {
            $return['data']['id_payment'] = str_replace('CART-', '', $return['data']['id_payment']);
        }
        if ($system_settings["settings_data"]["seller_id"] != $data["seller_id"]) {
            $error = true;
        }

        $this->CI->load->model("Payments_model");
        $site_payment_data = $this->CI->Payments_model->get_payment_by_id($return['data']['id_payment']);
        if (floatval($site_payment_data["amount"]) != floatval($return['data']['amount'])) {
            $error = true;
        }

        if ($payment_data['demo'] == 'Y') {
            $return['data']['id_order'] = '1';
        }

        $server_side_hash = $system_settings["settings_data"]["secret_word"] .
            $return['data']['seller_id'] .
            $return['data']['id_order'] .
            strval($return['data']['amount']);
        $server_side_hash = strtoupper(md5($server_side_hash));
        if ($server_side_hash != $return['data']["hash"]) {
            $error = true;
        }

        if ($error) {
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
