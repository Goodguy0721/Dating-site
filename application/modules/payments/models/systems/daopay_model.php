<?php

namespace Pg\Modules\Payments\Models\Systems;

use Pg\Modules\Payments\Models\Payment_driver_model;

/**
 * DaoPay payment system driver model
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

class DaoPay_model extends Payment_driver_model
{
    public $payment_data = array();
    public $settings = array(
        "appcode" => array("type" => "text", "content" => "string", "size" => "middle"),
    );
    protected $variables = array(
        "tid" => "trans_id",
    );

    public function func_request($payment_data, $system_settings)
    {
        $return = array("errors" => array(), "info" => array(), "data" => $payment_data);
        $description = mb_substr(str_replace(":", "", $payment_data["payment_data"]["name"]), 0, 127);
        $send_data = array(
            "appcode" => $system_settings["settings_data"]["appcode"],
            "method"  => "sms",
            "price"   => $payment_data["amount"],
            "daoid"   => $payment_data["id"],
        );

        $this->send_data("http://daopay.com/payment/", $send_data, "get");

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
        if (isset($payment_data['tid']) && isset($payment_data['stat'])) {
            if ($payment_data['stat'] == "ok" || $payment_data['stat'] == "part") {
                $return["data"]["status"] = 1;
            } else {
                $return["data"]["status"] = -1;
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
