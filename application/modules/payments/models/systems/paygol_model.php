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

class PayGol_model extends Payment_driver_model
{
    public $payment_data = array(
        'gid'           => 'paygol',
        'name'          => 'Paygol',
        'settings_data' => 'a:1:{s:9:"serviceid";s:6:"paygol";}',
        'logo'          => 'logo_paygol.png',
    );
    public $settings = array(
        "serviceid" => array("type" => "text", "content" => "string", "size" => "middle"),
    );
    protected $variables = array(
        'service_id' => 'serviceid',
        'price'      => 'amount',
        'currency'   => 'currency_gid',
        'custom'     => 'id_payment',
    );
    protected $currencies = array(
        'AED', 'AFN', 'ALL', 'AMD', 'ANG',
        'AOA', 'ARS', 'AUD', 'AWG', 'AZN',
        'BAM', 'BBD', 'BDT', 'BGN', 'BHD',
        'BIF', 'BMD', 'BND', 'BOB', 'BRL',
        'BSD', 'BTN', 'BWP', 'BYR', 'BZD',
        'CAD', 'CDF', 'CHF', 'CLP', 'CNY',
        'COP', 'CRC', 'CUC', 'CUP', 'CVE',
        'CZK', 'DJF', 'DKK', 'DOP', 'DZD',
        'EGP', 'ERN', 'ETB', 'EUR', 'FJD',
        'FKP', 'GBP', 'GEL', 'GGP', 'GHS',
        'GIP', 'GMD', 'GNF', 'GTQ', 'GYD',
        'HKD', 'HNL', 'HRK', 'HTG', 'HUF',
        'IDR', 'ILS', 'IMP', 'INR', 'IQD',
        'IRR', 'ISK', 'JEP', 'JMD', 'JOD',
        'JPY', 'KES', 'KGS', 'KHR', 'KMF',
        'KPW', 'KRW', 'KWD', 'KYD', 'KZT',
        'LAK', 'LBP', 'LKR', 'LRD', 'LSL',
        'LTL', 'LVL', 'LYD', 'MAD', 'MDL',
        'MGA', 'MKD', 'MMK', 'MNT', 'MOP',
        'MRO', 'MUR', 'MVR', 'MWK', 'MXN',
        'MYR', 'MZN', 'NAD', 'NGN', 'NIO',
        'NOK', 'NPR', 'NZD', 'OMR', 'PAB',
        'PEN', 'PGK', 'PHP', 'PKR', 'PLN',
        'PYG', 'QAR', 'RON', 'RSD', 'RUB',
        'RWF', 'SAR', 'SBD', 'SCR', 'SDG',
        'SEK', 'SGD', 'SHP', 'SLL', 'SOS',
        'SPL', 'SRD', 'STD', 'SVC', 'SYP',
        'SZL', 'THB', 'TJS', 'TMT', 'TND',
        'TOP', 'TRY', 'TTD', 'TVD', 'TWD',
        'TZS', 'UAH', 'UGX', 'USD', 'UYU',
        'UZS', 'VEF', 'VND', 'VUV', 'WST',
        'XAF', 'XCD', 'XDR', 'XOF', 'XPF',
        'YER', 'ZAR', 'ZMW', 'ZWD',
    );

    public function func_request($payment_data, $system_settings)
    {
        $return = array("errors" => array(), "info" => array(), "data" => $payment_data);
        $send_data = array(
            "pg_serviceid"  => $system_settings["settings_data"]["serviceid"],
            "pg_custom"     => $payment_data["id"],
            "pg_price"      => $payment_data["amount"],
            "pg_name"       => $payment_data["payment_data"]["name"],
            "pg_currency"   => $payment_data["currency_gid"],
            "pg_return_url" => site_url(),
            "notify_url"    => site_url() . "payments/responce/paygol",
            "cancel_url"    => site_url(),
        );

        if (!in_array($send_data['pg_currency'], $this->currencies)) {
            unset($send_data['pg_currency']);
        }

        $this->send_data("http://www.paygol.com/micropayment/paynow", $send_data, "post");

        return $return;
    }

    public function func_responce($payment_data, $system_settings)
    {
        $return = array("errors" => array(), "info" => array(), "data" => array(), "type" => "exit");

        foreach ($this->variables as $payment_var => $site_var) {
            $return["data"][$site_var] = isset($payment_data[$payment_var]) ? $this->CI->input->xss_clean($payment_data[$payment_var]) : "";
        }

        if ($system_settings["settings_data"]["serviceid"] != $return["data"]["serviceid"]) {
            $error = true;
        }

        $this->CI->load->model("Payments_model");
        $site_payment_data = $this->CI->Payments_model->get_payment_by_id($return["data"]['id_payment']);
        if (floatval($site_payment_data["amount"]) != floatval($return["data"]['amount']) ||
            $site_payment_data["currency_gid"] != $return["data"]['currency_gid']) {
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
