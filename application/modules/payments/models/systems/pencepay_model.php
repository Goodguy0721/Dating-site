<?php

namespace Pg\Modules\Payments\Models\Systems;

use Pg\Modules\Payments\Models\Payment_driver_model;

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Pencepay payment system driver model
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
class Pencepay_model extends Payment_driver_model
{
    public $payment_data = array(
        'gid'           => 'pencepay',
        'name'          => 'Pencepay',
        'settings_data' => 'a:1:{s:9:"seller_id";s:9:"seller id";}',
        'logo'          => 'logo_pencepay.png',
    );

    /**
     * System settings
     *
     * @var array
     */
    public $settings = array(
        'seller_id' => array('type' => 'text', 'content' => 'string', 'size' => 'middle'),
    );

    /**
     * Response variables
     *
     * @var array
     */
    protected $variables = array(
        'service'       => 'seller_id',
        'phone'         => 'phone',
        'country'       => 'country',
        'mno'           => 'mno',
        'amount'        => 'amount',
        'status'        => 'status',
        'clientid'      => 'payment_id',
        'enduserprice'  => 'enduserprice',
        'transactionid' => 'transactionid',
    );

    /**
     * Abailable languages
     *
     * @var array
     */
    private $available_languages = array(
        'bg', 'bs', 'cg', 'cs', 'da', 'de', 'el', 'en', 'es', 'et',
        'fi', 'fr', 'hr', 'hu', 'id', 'it', 'ko', 'lt', 'lv', 'mc',
        'mk', 'ms', 'no', 'sl', 'sq', 'sr', 'sv', 'th', 'tr', 'vi',
        'zh',
    );

    /**
     * Checkout url
     *
     * @var string
     */
    private $checkout_url = 'https://service.pencepay.com/widget/WidgetModule';

    /**
     * Checkout url for test mode
     *
     * @var string
     */
    private $test_checkout_url = '';

    /**
     * Javascript url
     *
     * @var string
     */
    private $js_checkout_url = 'https://service.pencepay.com/widget/js/c-mobile-payment-scripts.js';

    /**
     * Javascript url for test mode
     *
     * @var string
     */
    private $test_js_checkout_url = '';

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

        $error = false;

        $this->CI->load->model("Payments_model");
        $site_payment_data = $this->CI->Payments_model->get_payment_by_id($return['data']['id_payment']);
        if (floatval($site_payment_data["amount"]) != floatval($return['data']['amount'])) {
            $error = true;
        }

        if ($return['data']['status'] != 'success') {
            $error = true;
        }

        if ($error) {
            $return["data"]["status"] = -1;
        } else {
            $return["data"]["status"] = 1;
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
        if ($system_settings['is_test'] == 2) {
            $checkout_url = $this->test_checkout_url;
        } else {
            $checkout_url = $this->checkout_url;
        }

        $href = $checkout_url . '?api=' . $system_settings['settings_data']['seller_id'];

        $current_lang = $this->CI->pg_language->get_lang_by_id($this->CI->pg_language->current_lang_id);
        $current_lang['code'] = strtolower($current_lang['code']);
        if (in_array($current_lang['code'], $this->available_languages)) {
            $href .= '&locale=' . $current_lang['code'];
        }

        $href .= '&amount=' . $payment_data['amount'] . '&clientid=' . $payment_data['id_payment'];

        $user_id = $this->CI->session->userdata('user_id');

        $this->CI->load->model('Users_model');

        $user = $this->CI->Users_model->get_user_by_id($user_id);

        $href .= '&phone=' . preg_replace('/[^\d]/i', '', $user['phone']);

        if ($system_settings['settings_data']['is_test'] == 2) {
            $js_checkout_url = $this->test_js_checkout_url;
        } else {
            $js_checkout_url = $this->js_checkout_url;
        }

        return '
	<script type="text/javascript" src="' . $js_checkout_url . '"></script>
	<script>
		$(function(){
			setTimeout(\'$("#c-mobile-payment-widget img").trigger("click");\', 1000);
		});
	</script>
	<a id="c-mobile-payment-widget" href="' . $href . '" class="hide">
		<img src="http://service.pencepay.com/res/css/r/pencepay/button.png"/>
	</a>';
    }
}
