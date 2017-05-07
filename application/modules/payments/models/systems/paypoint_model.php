<?php

namespace Pg\Modules\Payments\Models\Systems;

use Pg\Modules\Payments\Models\Payment_driver_model;

/**
 * Paypoint payment system driver model
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

class Paypoint_model extends Payment_driver_model
{
    public $payment_data = array();
    public $settings = array(
        "seller_id" => array("type" => "text", "content" => "string", "size" => "middle"),
    );
    protected $variables = array(
        "intInstID"   => "seller_id",
        "fltAmount"   => "amount",
        "strCurrency" => "currency",
        "strCartID"   => "id_payment",
        "intTestMode" => "test_mode",
    );

    public function __construct()
    {
        parent::__construct();
    }

    public function func_request($payment_data, $system_settings)
    {
        $return = array("errors" => array(), "info" => array(), "data" => $payment_data);

        $send_data = array(
            "intInstID"   => $system_settings["settings_data"]["seller_id"],
            "fltAmount"   => $payment_data["amount"],
            "strCurrency" => $payment_data["currency_gid"],
            "strCartID"   => $payment_data["id_payment"],
            "intTestMode" => "0",
            "strDesc"     => $payment_data["payment_data"]["name"],
        );
        $this->send_data("https://secure.metacharge.com/mcpe/purser", $send_data, "post");

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
        if (isset($payment_data['IntStatus'])) {
            if ($payment_data['IntStatus'] == 1) {
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
