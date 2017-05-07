<?php

namespace Pg\Modules\Payments\Models\Systems;

use Pg\Modules\Payments\Models\Payment_driver_model;

/**
 * Chronopay payment system driver model
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

class Chronopay_model extends Payment_driver_model
{
    public $payment_data = array();
    public $settings = array(
        "seller_id" => array("type" => "text", "content" => "string", "size" => "middle"),
    );
    protected $variables = array(
        "total"            => "amount",
        "currency"         => "currency",
        "cs1"              => "id_payment",
        "date"             => "payment_date",
        "transaction_type" => "transaction_status",
    );

    public function __construct()
    {
        parent::__construct();
    }

    public function func_request($payment_data, $system_settings)
    {
        $return = array("errors" => array(), "info" => array(), "data" => $payment_data);

        $send_data = array(
            'product_id'    => $system_settings["settings_data"]["seller_id"],
            'product_price' => $payment_data["amount"],
            'product_name'  => $payment_data["payment_data"]["name"],
            'cs1'           => $payment_data["id_payment"],
            'cb_type'       => "P",
            'cb_url'        => site_url() . "payments/responce/chronopay",
            'decline_url'   => site_url(),
        );
        $this->send_data("https://secure.chronopay.com/index_shop.cgi", $send_data, "post");

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
        if (isset($return["data"]['transaction_status'])) {
            switch ($return["data"]['transaction_status']) {
                case "decline": $return["data"]["status"] = -1;
                    break;
                default: $return["data"]["status"] = 1;
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
