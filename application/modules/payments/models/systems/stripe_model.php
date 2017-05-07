<?php

namespace Pg\Modules\Payments\Models\Systems;

use Pg\Modules\Payments\Models\Payment_driver_model;

/**
 * Payments module
 *
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Stripe payment system driver model
 *
 * @subpackage 	Payments
 *
 * @category	models
 *
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
class Stripe_model extends Payment_driver_model
{
    /**
     *  Payment settings
     *
     *  @return void
     */
    public $payment_data = array(
        'gid'           => 'stripe',
        'name'          => 'Stripe',
        'settings_data' => 'a:2:{s:11:"secret_word";s:11:"secret word";s:12:"publish_word";s:12:"publish word";}',
        'tarifs_type'   => 0,
        'logo' => 'stripe.png',
        //'is_test' => 1,
    );

    public $is_test = true;

    /**
     * System settings
     *
     * @var array
     */
    public $settings = array(
        'secret_word'  => array('type' => 'text', 'content' => 'string', 'size' => 'middle'),
        'publish_word' => array('type' => 'text', 'content' => 'string', 'size' => 'middle'),
    );

    /**
     * Response variables
     *
     * @var array
     */
    protected $variables = array(
        'stripeToken' => 'token',
        'invoice'     => 'id_payment',
    );

    /**
     * Checkout url
     *
     * @var string
     */
    protected $checkout_url = 'https://checkout.stripe.com/checkout.js';

    /**
     * Checkout url for test mode
     *
     * @var string
     */
    protected $test_checkout_url = 'https://checkout.stripe.com/checkout.js';

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
        $errors = array();
        if (empty($payment_data['payment_data']['token'])) {
            $errors[] = l('error_stripe_token', 'payments');
        }
        $this->CI->load->library('Stripe_api');
        $this->CI->stripe_api->setApiKey($system_settings['settings_data']['secret_word']);
        // Create the charge on Stripe's servers - this will charge the user's card
        try {
            $this->CI->stripe_api->chargeCreate(array(
                'amount'   => $payment_data['amount'] * 100, // amount in cents
                'currency' => $payment_data['currency_gid'],
                'source'   => $payment_data['payment_data']['token'], )
            );
            $this->CI->load->model('Payments_model');
            $this->CI->Payments_model->change_payment_status($payment_data['id'],
                true);
        } catch (\Exception $e) {
            $errors[] = $e->getMessage();
        }

        return array('errors' => $errors, 'info' => array(), 'data' => $payment_data);
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
        $return = array("errors" => array(), "info" => array(), "data" => array(),
            "type" => "html");

        foreach ($this->variables as $payment_var => $site_var) {
            $return['data'][$site_var] = isset($payment_data[$payment_var]) ? $this->CI->input->xss_clean($payment_data[$payment_var])
                    : "";
        }

        $this->CI->load->model("Payments_model");
        $site_payment_data = $this->CI->Payments_model->get_payment_by_id($return["data"]['id_payment']);

        $this->CI->load->library('Stripe_api');
        $this->CI->stripe_api->setApiKey($system_settings['settings_data']['secret_word']);

        if ($return['data']['token']) {
            try {
                $this->CI->stripe_api->chargeCreate(array(
                    "amount"   => $site_payment_data['amount'] * 100,
                    "currency" => $site_payment_data["currency_gid"],
                    "card"     => $return['data']['token'],
                ));
                $return['info'][] = l('success_payment_send', 'payments');
            } catch (\Exception $e) {
                $return['errors'][] = $e->getMessage();
            }
        } else {
            $return['errors'][] = l('error_stripe_token', 'payments');
        }

        if (!empty($return['errors'])) {
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
            $this->settings[$param_id]["name"] = l('system_field_' . $param_id,
                'payments');
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
        if ($this->is_test === true) {
            $checkout_url = $this->test_checkout_url;
        } else {
            $checkout_url = $this->checkout_url;
        }

        $code = '<form data-payment="stripe" action="' . site_url() . 'payments/responce/stripe" method="POST">';
            $code .=  '<script src="' . $checkout_url . '" class="stripe-button" data-key="' . $system_settings['settings_data']['publish_word'] . '" data-amount="' . ($payment_data['amount'] * 100) . '" data-description="' . htmlspecialchars($payment_data["payment_data"]["name"]) . '"></script>';
            $code .=  '<input type="hidden" name="invoice" value="' . htmlspecialchars($payment_data['id']) . '">';
        $code .=  '</form>';

        return $code;
    }
}