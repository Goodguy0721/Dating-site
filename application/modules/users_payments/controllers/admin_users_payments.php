<?php

namespace Pg\Modules\Users_payments\Controllers;

use Pg\Libraries\View;

/**
 * Users payments admin side controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Alexander Batukhtin <abatukhtin@pilotgroup.net>
 *
 * @version $Revision: 1 $ $Date: 2012-09-12 11:26:22 +0300 (Ср, 12 сент 2012) $ $Author: abatukhtin $
 **/
class Admin_users_payments extends \Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function ajax_add_funds_form()
    {
        $this->view->render('funds_form');
    }

    public function ajax_add_funds()
    {
        $this->load->model('payments/models/Payment_currency_model');
        $amount = abs(floatval($this->input->post('amount', true)));
        $user_ids = $this->input->post('user_ids', true);

        if (empty($amount)) {
            $return['error'] = l('error_empty_amount', 'users_payments');
        } elseif (empty($user_ids)) {
            $return['error'] = l('error_empty_users_list', 'users_payments');
        } else {
            $this->load->helper('payments');

            foreach ($user_ids as $id_user) {
                $payment_data = send_payment('account',
                                            $id_user,
                                            $amount,
                                            $this->Payment_currency_model->default_currency["gid"],
                                            'manual',
                                            array('name' => l('added_by_admin', 'users_payments'), 'lang' => 'added_by_admin', 'module' => 'users_payments'));
                $payment_data['data']['status'] = 1;
                receive_payment('manual', $payment_data['data']);
            }
            $return['success'] = '';
            $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_add_funds', 'users_payments'));
        }
        $this->view->assign($return);

        return;
    }
}
