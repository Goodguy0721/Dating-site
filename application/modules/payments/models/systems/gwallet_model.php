<?php

namespace Pg\Modules\Payments\Models\Systems;

use Pg\Modules\Payments\Models\Payment_driver_model;

/**
 * Google Wallet payment system driver model
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

class GWallet_model extends Payment_driver_model
{
    public $payment_data = array(
        'gid'           => 'gwallet',
        'name'          => 'Google Wallet',
        'settings_data' => 'a:2:{s:9:"seller_id";s:9:"seller id";s:13:"seller_secret";s:13:"seller secret";}',
        'logo'          => 'logo_gwallet.png',
    );
    public $settings = array(
        "seller_id"     => array("type" => "text", "content" => "string", "size" => "middle"),
        "seller_secret" => array("type" => "text", "content" => "string", "size" => "middle"),
    );
    protected $variables = array(
        'price'        => 'amount',
        'currencyCode' => 'currency',
        'orderId'      => 'payment_id',
    );
    protected $checkout_url = 'https://wallet.google.com/inapp/lib/buy.js';
    protected $test_checkout_url = 'https://sandbox.google.com/checkout/inapp/lib/buy.js';

    public function func_request($payment_data, $system_settings)
    {
        $return = array("errors" => array(), "info" => array(), "data" => $payment_data);

        return $return;
    }

    public function func_responce($payment_data, $system_settings)
    {
        $return = array("errors" => array(), "info" => array(), "data" => array(), "type" => "exit");

        $this->CI->load->library('JWT');

        try {
            $payment_data = (array) JWT::decode($payment_data['jwt'], $system_settings["settings_data"]["seller_secret"]);
            $payment_data = array_merge($payment_data['request'], $payment_data['responce']);
        } catch (Exception $e) {
            $payment_data = array();
        }

        foreach ($this->variables as $payment_var => $site_var) {
            $return["data"][$site_var] = isset($payment_data[$payment_var]) ? $this->CI->input->xss_clean($payment_data[$payment_var]) : "";
        }

        $error = false;

        $this->CI->load->model("Payments_model");
        $site_payment_data = $this->CI->Payments_model->get_payment_by_id($return['data']['id_payment']);
        if (floatval($site_payment_data['amount']) != floatval($return['data']['amount']) ||
            $site_payment_data['currency_gid'] != $return['data']['currency']) {
            $error = true;
        }

        if ($error) {
            $return["data"]["status"] = -1;
        } else {
            $return["data"]["status"] = 1;

            echo $return['data']['payment_id'];
        }

        return $return;
    }

    public function func_js()
    {
        return true;
    }

    public function get_settings_map()
    {
        foreach ($this->settings as $param_id => $param_data) {
            $this->settings[$param_id]["name"] = l('system_field_' . $param_id, 'payments');
        }

        return $this->settings;
    }

    public function get_html_map()
    {
        foreach ($this->html_fields as $param_id => $param_data) {
            $this->html_fields[$param_id]["name"] = l('html_field_' . $param_id, 'payments');
        }

        return $this->html_fields;
    }

    public function get_js($payment_data, $system_settings)
    {
        $time = time();

        $payload = array(
            'iss'     => $system_settings['settings_data']['seller_id'],
            'aud'     => 'Google',
            'typ'     => 'google/payments/inapp/item/v1',
            'exp'     => $time + 3600,
            'iat'     => $time,
            'request' => array(
                'name'         => $payment_data['payment_data']['name'],
                'description'  => '',
                'price'        => number_format($payment_data['amount'], 2),
                'currencyCode' => $payment_data['currency_gid'],
                'sellerData'   => 'user_id:' . $payment_data['id_user'] . ',offer_code:' . $payment_data['id_payment'],
            ),
        );

        $this->CI->load->library('JWT');

        try {
            $jwt = JWT::encode($payload, $system_settings["settings_data"]["seller_secret"]);
        } catch (Exception $e) {
            return '';
        }

        return '
	<script src="' . $this->checkout_url . '"></script>
	<script type="text/javascript">
		$(function(){
			google.payments.inapp.buy({
				parameters: {},
				jwt: "' . $jwt . '",
				success: function(){},
				failure: function(){}
			});
		});
	</script>';
    }
}
