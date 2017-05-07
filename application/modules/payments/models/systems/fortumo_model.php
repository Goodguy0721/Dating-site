<?php

namespace Pg\Modules\Payments\Models\Systems;

use Pg\Modules\Payments\Models\Payment_driver_model;

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Fortumo payment system driver model
 *
 * @package PG_RealEstate
 * @subpackage Payments
 *
 * @category	models
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Katya Kashkova <katya@pilotgroup.net>
 *
 * @version $Revision: 2 $ $Date: 2010-04-02 15:07:07 +0300 (Ср, 02 апр 2010) $ $Author: kkashkova $
 * */
class Fortumo_model extends Payment_driver_model
{
    public $payment_data = array(
        'gid'           => 'fortumo',
        'name'          => 'Fortumo',
        'settings_data' => 'a:2:{s:9:"seller_id";s:9:"seller id";s:11:"seller_word";s:11:"secret word";}',
        'logo'          => 'logo_fortumo.png',
    );

    /**
     * System settings
     *
     * @var array
     */
    public $settings = array(
        'seller_id'   => array('type' => 'text', 'content' => 'string', 'size' => 'middle'),
        'secret_word' => array('type' => 'text', 'content' => 'string', 'size' => 'middle'),
        'amount_rate' => array('type' => 'text', 'content' => 'string', 'size' => 'middle'),
    );

    /**
     * Response variables
     *
     * @var array
     */
    protected $variables = array(
        'service_id' => 'seller_id',
        'cuid'       => 'id_payment',
        'price'      => 'amount',
        'currency'   => 'currency',
        'sig'        => 'hash',
        'payment_id' => 'transaction_id',
        'sender'     => 'sender',
        'operator'   => 'operator',
        'status'     => 'status',
    );

    /**
     * Available services IPs
     *
     * @var array
     */
    protected $available_ips = array('79.125.125.1', '79.125.5.205',
        '79.125.5.95', '54.72.6.126', '54.72.6.27', '54.72.6.17', '54.72.6.23',);

    /**
     * Checkout url
     *
     * @var string
     */
    protected $checkout_url = '//fortumo.com/mobile_payments/';

    /**
     * Checkout url for test mode
     *
     * @var string
     */
    protected $test_checkout_url = '';

    /**
     * Javascript checkout url
     *
     * @var string
     */
    protected $js_checkout_url = '//fortumo.com/javascripts/fortumopay.js';

    /**
     * Javascript checkout url for test mode
     *
     * @var string
     */
    protected $test_js_checkout_url = '';

    /**
     * Do request to payment system
     *
     * @param array $payment_data    payment data
     * @param array $system_settings system settings
     *
     * @return void
     */
    public function func_request($payment_data, $system_settings)
    {
        $return = array("errors" => array(), "info" => array(), "data" => $payment_data);

        return $return;
    }

    /**
     * Process response from payment system
     *
     * @param array $payment_data    payment data
     * @param array $system_settings system settings
     *
     * @return array
     */
    public function func_responce($payment_data, $system_settings)
    {
        $return = array("errors" => array(), "info" => array(), "data" => array(), "type" => "exit");

        foreach ($this->variables as $payment_var => $site_var) {
            $return['data'][$site_var] = isset($payment_data[$payment_var]) ? $this->CI->input->xss_clean($payment_data[$payment_var]) : "";
        }

        unset($payment_data['sig']);

        if (!in_array($_SERVER['REMOTE_ADDR'], $this->available_ips)) {
            exit;
        }

        if ($system_settings["settings_data"]["seller_id"] != $return["data"]["seller_id"]) {
            $error = true;
        }

        $this->CI->load->model("Payments_model");
        $site_payment_data = $this->CI->Payments_model->get_payment_by_id($return["data"]['id_payment']);
        $server_side_amount = $site_payment_data["amount"] * max(intval($system_settings['settings_data']['amount_rate']), 1);
        if (floatval($server_side_amount) > floatval($return["data"]['amount']) ||
            $site_payment_data["currency_gid"] != $return["data"]['currency']) {
            $error = true;
        }

        $server_side_hash = $this->getSignature($payment_data, $system_settings['settings_data']['secret_word']);
        if (!empty($system_settings['settings_data']['secret_word']) && $server_side_hash != $return['data']['hash']) {
            $error = true;
        }

        if ($return['data']['status'] != 'completed') {
            $error = true;
        }

        if ($error) {
            $return["data"]["status"] = -1;
        } else {
            $return["data"]["status"] = 1;

            echo('OK');
        }

        return $return;
    }

    /**
     * Use javascript code
     *
     * @return boolean
     */
    public function func_js()
    {
        return true;
    }

    /**
     * Return formatted system settings
     *
     * @return array
     */
    public function get_settings_map()
    {
        foreach ($this->settings as $param_id => $param_data) {
            $this->settings[$param_id]["name"] = l('system_field_' . $param_id, 'payments');
        }

        return $this->settings;
    }

    /**
     * Return javascript code
     *
     * @param array $payment_data payment data
     * @param $system_settings system settings
     *
     * @return string
     */
    public function get_js($payment_data, $system_settings)
    {
        if ($system_settings['settings_data']['is_test'] == 2) {
            $checkout_url = $this->test_checkout_url;
        } else {
            $checkout_url = $this->checkout_url;
        }

        $service_url = $checkout_url . $system_settings['settings_data']['seller_id'] . '.xml';

        $amount = $payment_data['amount'] * max(intval($system_settings['settings_data']['amount_rate']), 1);

        $tc = array();

        if (function_exists('curl_init')) {
            $ch = curl_init($service_url);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
            $file_content = curl_exec($ch);
            curl_close($ch);
        } else {
            $file_content = file_get_contents($service_url);
        }

        try {
            $xml = simplexml_load_string($file_content);
            if ($xml) {
                $countries = $xml->countrues->children();

                foreach ($countries->xpath('//price_point') as $price_point) {
                    $attrs = $price_point->attributes();
                    if (strval($attrs['currency']) == $payment_data['currency_gid'] &&
                        floatval($attrs['amount']) == floatval($amount)) {
                        $tc[] = intval($attrs['id']);
                    }
                }
                unset($xml);
            }
        } catch (Exception $e) {
        }

        $params = array(
            'cuid'         => $payment_data['id_payment'],
            'tc_amount'    => $amount,
            'callback_url' => urlencode(site_url()),
        );

        if (!empty($tc)) {
            $params['tc_id'] = current($tc);
        }

        if ($system_settings['settings_data']['is_test'] == 2) {
            $params['test'] = 'ok';
        }

        $user_id = $this->CI->session->userdata('user_id');

        $this->CI->load->model('Users_model');

        $user = $this->CI->Users_model->get_user_by_id($user_id);

        $params['msisdn'] = preg_replace('/[^\d]/i', '', $user['phone']);

        $params['hash'] = $this->getSignature($params, $system_settings['settings_data']['secret_word']);

        foreach ($params as $k => $v) {
            $params[$k] = $k . '=' . $v;
        }

        $rel = $system_settings['settings_data']['seller_id'] . '?' . implode('&', $params);

        if ($system_settings['settings_data']['is_test'] == 2) {
            $js_checkout_url = $this->test_js_checkout_url;
        } else {
            $js_checkout_url = $this->js_checkout_url;
        }

        return '
	<script type="text/javascript" src="' . $js_checkout_url . '"></script>
	<script>
		$(function(){
			setTimeout(\'$("#fmp-button img").trigger("click");\', 1000);
            function fortumo_popup(){
                if(!$(\'#fmpboxContent:visible\').length){
                    document.location.href = \'' . site_url() . '\';
                }else{
                    setTimeout(fortumo_popup, 1000);
                }
            }
            setTimeout(fortumo_popup, 1000);
        });
	</script>
	<a id="fmp-button" href="#" rel="' . $rel . '" class="">
		<img src="//fortumo.com/images/fmp/fortumopay_96x47.png" width="96" height="47" alt="Mobile Payments by Fortumo" border="0" />
	</a>';
    }

    /**
     * Return payment signature
     *
     * @param array  $params payment parameters
     * @param string $secret system secret
     *
     * @return string
     */
    private function getSignature($params, $secret)
    {
        ksort($params);
        $sig = '';
        foreach ($params as $k => $v) {
            if ($k != 'sig') {
                $sig .= $k . '=' . $v;
            }
        }
        $sig .= $secret;

        return md5($sig);
    }
}
