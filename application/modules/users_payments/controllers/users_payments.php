<?php

namespace Pg\Modules\Users_payments\Controllers;

use Pg\Libraries\View;

/**
 * Users payments user side controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Alexander Batukhtin <abatukhtin@pilotgroup.net>
 *
 * @version $Revision: 1 $ $Date: 2012-09-12 10:32:07 +0300 (Ср, 12 сент 2012) $ $Author: abatukhtin $
 **/
class Users_payments extends \Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function save_payment()
    {
        $system_gid = $this->input->post('system_gid', true);
        if (empty($system_gid)) {
            $this->system_messages->addMessage(View::MSG_ERROR, l('error_empty_system_gid', 'users_payments'));
        }
        if ($system_gid) {
            $user_id = $this->session->userdata('user_id');
            $amount = abs(floatval(str_replace(',', '.', $this->input->post('amount', true))));
            if (empty($amount)) {
                $this->system_messages->addMessage(View::MSG_ERROR, l('error_empty_amount', 'users_payments'));
            } elseif (empty($system_gid)) {
                $this->system_messages->addMessage(View::MSG_ERROR, l('error_empty_system_gid', 'users_payments'));
            } else {
                $this->load->model('payments/models/Payment_currency_model');
                $base_currency = $this->Payment_currency_model->get_currency_default(true);
                $this->load->helper('payments');
                $additional['name'] = l('header_add_funds', 'users_payments');
                $additional['lang'] = 'header_add_funds';
                $additional['module'] = 'users_payments';
                $payment_data = send_payment('account',
                                            $user_id,
                                            $amount,
                                            $base_currency['gid'],
                                            $system_gid,
                                            $additional,
                                            'form');
                if (!empty($payment_data['errors'])) {
                    $this->system_messages->addMessage(View::MSG_ERROR, $payment_data['errors']);
                }
                if (!empty($payment_data['info'])) {
                    $this->system_messages->addMessage(View::MSG_INFO, $payment_data['info']);
                }
            }
        }

        $this->load->helper('seo');
        $url = rewrite_link('users', 'account', array('action' => 'update'));
        redirect($url);
    }
}
