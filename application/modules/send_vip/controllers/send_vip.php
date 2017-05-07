<?php

namespace Pg\Modules\Send_vip\Controllers;

use Pg\Libraries\View;

/**
 * Send money module
 *
 * @package 	PG_RealEstate
 *
 * @copyright 	Copyright (c) 2000-2014 PG Real Estate - php real estate listing software
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
class Send_vip extends \Controller
{
    public function save()
    {
        $this->load->model('Send_vip_model');
        $data              = array(
            'id_user'       => $this->session->userdata('id_user'),
            'id_sender'     => $this->session->userdata('user_id'),
            'id_membership' => $this->session->userdata('id_membership'),
        );
        $this->session->unset_userdata('id_user');
        $this->session->unset_userdata('id_membership');
        $payment_gid       = $this->input->post('system_gid', true);
        $this->load->model("payments/models/Payment_systems_model");
        $payment_gids_temp = $this->Payment_systems_model->get_active_system_list();
        foreach ($payment_gids_temp as $value) {
            $payment_gids[] = $value['gid'];
        }
        $payment_gids[] = 'account';

        $use_fee = $this->pg_module->get_module_config('send_vip', 'use_fee');
        if ($use_fee == 'use') {
            $currency     = $this->pg_module->get_module_config('send_vip', 'fee_currency');
            $transfer_fee = $this->pg_module->get_module_config('send_vip', 'fee_price');
            if ($currency == '%') {
                $koef = (float) $transfer_fee / 100; //ToDo: May be $koef need store in config table?
            } else {
                $koef = 1;
            }
            $this->view->assign('use_fee', $use_fee);
        } else {
            $transfer_fee = null;
            $koef         = null;
        }

        $transaction_data = $this->Send_vip_model->validateTransaction(null, $data, $koef, $transfer_fee);
        if (!empty($transaction_data['errors']) || !in_array($payment_gid, $payment_gids)) {
            $this->system_messages->addMessage(View::MSG_ERROR, $transaction_data['errors']);
        } else {
            $this->load->model('payments/models/Payment_currency_model');

            $currency_gid = $this->Payment_currency_model->default_currency["gid"];

            if ($payment_gid == 'account') {
                $transaction_id                             = $this->Send_vip_model->saveTransaction(null, $transaction_data['data']);
                $sender                                     = $this->Users_model->get_user_by_id($transaction_data['data']['id_sender']);
                $sender['account'] -= $transaction_data['data']['full_amount'];
                $this->Users_model->save_user($transaction_data['data']['id_sender'], $sender);
                $transaction_data['data']['id_transaction'] = $transaction_id;

                $is_send = $this->Send_vip_model->sendLetter($transaction_data['data']);
                if (!$is_send) {
                    $this->system_messages->addMessage(View::MSG_ERROR, $validate_data['errors']);
                }

                $success_txt = l('send_vip_transaction_saved', 'send_vip');
                $this->system_messages->addMessage(View::MSG_SUCCESS, $success_txt);

                return redirect(site_url() . 'users/account/donate/', 'hard');
            } else {
                $to_user_row = $this->Users_model->get_user_by_id($transaction_data['data']['id_user']);
                $to_user     = $to_user_row['fname'] . ' ' . $to_user_row['sname'];
                $message     = l('send_vip', 'send_vip') . ': ' . $to_user;

                $payment_data = array(
                    'name'        => $message,
                    'transaction' => $transaction_data['data'],
                );

                $this->load->helper('payments');
                send_payment('send_vip', $transaction_data['data']['id_sender'], $transaction_data['data']['full_amount'], $currency_gid, $payment_gid, $payment_data, 'form');
            }
        }
    }

    public function confirm()
    {
        $post_data = array();
        if ($this->input->post('btn_send_vip_save', true)) {
            $this->load->model('Send_vip_model');

            $id_user = $this->input->post('id_user', true);
            if (!empty($id_user)) {
                $post_data['id_user'] = $id_user;
            } else {
                $post_data['id_user'] = $this->input->post('friend', true);
            }
            $post_data['id_sender']     = $this->session->userdata('user_id');
            $post_data['id_membership'] = $this->input->post('membership', true);
            $this->load->model('payments/models/Payment_currency_model');
            $use_fee                    = $this->pg_module->get_module_config('send_vip', 'use_fee');
            if ($use_fee == 'use') {
                $currency     = $this->pg_module->get_module_config('send_vip', 'fee_currency');
                $transfer_fee = $this->pg_module->get_module_config('send_vip', 'fee_price');
                if ($currency == '%') {
                    $koef = (float) $transfer_fee / 100; //ToDo: May be $koef need store in config table?
                } else {
                    $koef = 1;
                }
                $this->view->assign('use_fee', $use_fee);
            } else {
                $transfer_fee = null;
                $koef         = null;
            }
            $validate_data = $this->Send_vip_model->validateTransaction(null, $post_data, $koef, $transfer_fee);
            if (empty($validate_data['errors'])) {
                $currency    = $this->Payment_currency_model->get_currency_default(true);
                $this->load->model('Users_model');
                $to_user_row = $this->Users_model->get_user_by_id($validate_data['data']['id_user']);
                $to_user     = $to_user_row['fname'] . ' ' . $to_user_row['sname'];

                $this->load->model("payments/models/Payment_systems_model");
                $billing_systems = $this->Payment_systems_model->get_active_system_list();
                $pay_type        = $this->pg_module->get_module_config('send_vip', 'transfer_type');

                $this->view->assign('currency', $currency['gid']);
                $this->view->assign('amount', $validate_data['data']['full_amount']);
                $this->view->assign('to_user', $to_user);
                $this->view->assign('membership_name', $validate_data['data']['membership_name']);
                $this->view->assign('transfer_fee', number_format($validate_data['data']['transfer_fee'], 2, '.', ''));
                $this->view->assign('pay_type', $pay_type);
                $this->view->assign('billing_systems', $billing_systems);

                $this->session->set_userdata('id_membership', $validate_data['data']['id_membership']);
                $this->session->set_userdata('full_amount', $validate_data['data']['full_amount']);
                $this->session->set_userdata('id_user', $validate_data['data']['id_user']);

                // breadcrumbs
                $this->load->model('Menu_model');
                $this->Menu_model->breadcrumbs_set_parent('account-item');
                $this->Menu_model->breadcrumbs_set_active(l('send_vip', 'send_vip'));
                $this->view->render('confirm');
            } else {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data['errors']);
                redirect(site_url() . 'users/account/donate');
            }
        } else {
            $this->system_messages->addMessage(View::MSG_ERROR, $validate_data['errors']);
            redirect(site_url() . 'users/account/donate');
        }
    }

    public function approve($transaction_id)
    {
        $this->load->model('Send_vip_model');
        $this->system_messages->addMessage(View::MSG_SUCCESS, $this->Send_vip_model->statusTransaction($transaction_id, 'approve'));
        redirect(site_url() . 'users/account/donate');
    }

    public function decline($transaction_id)
    {
        $this->load->model('Send_vip_model');
        $this->system_messages->addMessage(View::MSG_ERROR, $this->Send_vip_model->statusTransaction($transaction_id, 'decline'));
        redirect(site_url() . 'users/account/donate');
    }

    public function ajaxGetSendVipBlock()
    {
        $this->load->helper('send_vip');
        exit(send_vip_block());
    }

    public function ajaxValidateTransaction()
    {
        $post_data['friend']        = $this->input->post('friend', true);
        $post_data['id_user']       = $this->input->post('id_user', true);
        $post_data['amount']        = $this->input->post('amount', true);
        $post_data['full_amount']   = $this->input->post('full_amount', true);
        $post_data['id_membership'] = $this->input->post('membership', true);
        if (empty($post_data['id_user'])) {
            $post_data['id_user'] = $post_data['friend'];
        }
        $post_data['id_sender'] = $this->session->userdata('user_id');
        $this->load->model('Send_vip_model');
        $return                 = $this->Send_vip_model->validateTransaction(null, $post_data);
        $this->view->assign('errors', implode('<br>', $return['errors']));
        $this->view->render();
    }

    public function ajaxApprove($transaction_id)
    {
        $this->load->model('Send_vip_model');
        exit($this->Send_vip_model->statusTransaction($transaction_id, 'approve'));
    }

    public function ajaxDecline($transaction_id)
    {
        $this->load->model('Send_vip_model');
        exit($this->Send_vip_model->statusTransaction($transaction_id, 'decline'));
    }
}
