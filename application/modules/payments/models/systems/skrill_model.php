<?php

namespace Pg\Modules\Payments\Models\Systems;

use Pg\Modules\Payments\Models\Payment_driver_model;

/**
 * Scrill payment system driver model
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

class Skrill_model extends Payment_driver_model
{
    public $payment_data = array(
        'gid'           => 'skrill',
        'name'          => 'Skrill',
        'settings_data' => 'a:2:{s:9:"seller_id";s:9:"seller id";s:11:"seller_word";s:11:"secret word";}',
        'logo'          => 'logo_skrill.png',
    );
    public $settings = array(
        'seller_id'   => array("type" => "text", "content" => "string", "size" => "middle"),
        'secret_word' => array("type" => "text", "content" => "string", "size" => "middle"),
    );
    protected $variables = array(
        'pay_to_email'      => 'seller_id',
        'merchant_id'       => 'merchant_id',
        'mb_transaction_id' => 'id_payment',
        'mb_amount'         => 'amount',
        'mb_currency'       => 'currency',
        'md5sig'            => 'hash',
        'status'            => 'status',
    );
    protected $available_languages = array(
        'EN', 'DE', 'ES', 'FR', 'IT', 'PL',
        'GR', 'RO', 'RU', 'TR', 'CN', 'CZ',
        'NL', 'DA', 'SV', 'FI',
    );
    protected $checkout_url = 'https://www.moneybookers.com/app/payment.pl';
    protected $test_checkout_url = 'https://www.moneybookers.com/app/test_payment.pl';

    public function func_request($payment_data, $system_settings)
    {
        $return = array("errors" => array(), "info" => array(), "data" => $payment_data);

        $send_data = array(
            'pay_to_email'        => $system_settings["settings_data"]["seller_id"],
            'tansaction_id'       => $payment_data["id_payment"],
            'amount'              => $payment_data["amount"],
            'currency'            => $payment_data["currency_gid"],
            'detail1_description' => l('field_payment_name', 'payments'),
            'detail1_text'        => $payment_data["payment_data"]["name"],
            'return_url'          => site_url(),
            'status_url'          => site_url() . "payments/responce/skrill",
            'cancel_url'          => site_url(),
        );

        $current_lang = $this->CI->pg_language->get_lang_by_id($this->CI->pg_language->current_lang_id);
        $current_lang['code'] = strtoupper($current_lang['code']);
        if (in_array($current_lang['code'], $this->available_languages)) {
            $send_data['language'] = $current_lang['code'];
        }

        $user_id = $this->CI->session->userdata('user_id');
        $this->CI->load->model('Users_model');
        $user = $this->CI->Users_model->get_user_by_id($user_id);

        $send_data['pay_from_email'] = $user['email'];
        $send_data['first_name'] = $user['fname'];
        $send_data['last_name'] = $user['sname'];
        $send_data['phone_number'] = $user['phone'];

        $this->send_data($this->checkout_url, $send_data, "post");

        return $return;
    }

    public function func_responce($payment_data, $system_settings)
    {
        $return = array("errors" => array(), "info" => array(), "data" => array(), "type" => "exit");

        foreach ($this->variables as $payment_var => $site_var) {
            $return['data'][$site_var] = isset($payment_data[$payment_var]) ? $this->CI->input->xss_clean($payment_data[$payment_var]) : "";
        }

        $error = false;

        if ($system_settings["settings_data"]["seller_id"] != $return['data']['seller_id']) {
            $error = true;
        }

        $this->CI->load->model("Payments_model");
        $site_payment_data = $this->CI->Payments_model->get_payment_by_id($return['data']['id_payment']);
        if (floatval($site_payment_data["amount"]) != floatval($return['data']['amount'])) {
            $error = true;
        }

        $server_side_hash = $return['data']['merchant_id'] . $return['data']['id_payment'] .
            strtoupper(md5($system_settings['settings_data']['secret_word'])) .
            $return['data']['amount'] . $return['data']['currency'] . $return['data']['status'];
        $server_side_hash = strtoupper(trim(md5($server_side_hash)));
        if ($server_side_hash != $return['data']['hash']) {
            $error = true;
        }

        if ($error) {
            $return["data"]["status"] = -1;
        } else {
            switch ($return["data"]["status"]) {
                case 2: $return["data"]["status"] = 1;
                    break;
                case 0: $return["data"]["status"] = 0;
                    break;
                default: $return["data"]["status"] = -1;
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
