<?php

/**
 * Send_money module
 *
 * @package 	PG_Dating
 *
 * @copyright 	Copyright (c) 2000-2015 PG Dating Pro - php dating software
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
if (!function_exists('send_money_block')) {
    function send_money_block()
    {
        $ci = &get_instance();
        $ci->load->model('Send_money_model');
        $ci->load->model('payments/models/Payment_currency_model');

        $base_currency = $ci->Payment_currency_model->get_currency_default(true);
        $cur_currency = $base_currency['gid'];
        $currency = $ci->pg_module->get_module_config('send_money', 'fee_currency');
        $use_fee = $ci->pg_module->get_module_config('send_money', 'use_fee');
        $transfer_fee = $ci->pg_module->get_module_config('send_money', 'fee_price');
        if ($currency == '%') {
            $koef = (float) $transfer_fee / 100;//ToDo: May be $koef need store in config table?
        } else {
            $koef = 1;
        }

        $ci->load->model('Users_model');

        if ($ci->pg_module->is_module_installed('friendlist')) {
            $ci->load->model('Friendlist_model');
            $friends_count = $ci->Friendlist_model->get_friendlist_count($ci->session->userdata('user_id'));
            $friends_ids = $ci->Friendlist_model->get_friendlist_users_ids($ci->session->userdata('user_id'));
            $friends = $ci->Users_model->get_users_list(null, null, null, null, $friends_ids);
            foreach ($friends as $value) {
                $friends_names[$value['id']] = $value['name'];
            }
            $ci->view->assign('friends_list', $friends_names);
            $ci->view->assign('friends_count', $friends_count);
        }
        $friends_only = $ci->pg_module->get_module_config('send_money', 'to_whom');

        $action = site_url() . "send_money/confirm/";

        $ci->view->assign('cur_currency', $cur_currency);
        $ci->view->assign('currency', $currency);
        $ci->view->assign('use_fee', $use_fee);
        $ci->view->assign('transfer_fee', $transfer_fee);
        $ci->view->assign('koef', $koef);
        $ci->view->assign('rand', mt_rand(100, 500));
        $ci->view->assign('action', $action);
        $ci->view->assign('friends_only', $friends_only);
        $ci->view->assign('user_id', $ci->session->userdata('user_id'));
        $success = $ci->session->userdata('send_money_msg');
        if ($success) {
            $success_txt = l('send_money_transaction_saved', 'send_money');
            $ci->view->assign('send_money_success', $success_txt);
            $ci->session->unset_userdata('send_money_msg');
        }

        return $ci->view->fetch('helper_send_money_form', 'user', 'send_money');
    }
}

if (!function_exists('send_money_view_block')) {
    function send_money_view_block()
    {
        $ci = &get_instance();
        $ci->load->model('Send_money_model');
        $ci->load->model('Users_model');
        $ci->load->model('payments/models/Payment_currency_model');
        $transactions = $ci->Send_money_model->getTransaction();
        $id_user = $ci->session->userdata('user_id');

        foreach ($transactions as $key => $transaction) {
            if ($transaction['id_sender'] != $id_user && $transaction['id_user'] != $id_user) {
                unset($transactions[$key]);
            } else {
                $id_users_arr[] = $transaction['id_user'];
                $id_users_arr[] = $transaction['id_sender'];
            }
        }

        if (!empty($id_users_arr)) {
            $users_arr = $ci->Users_model->get_users_list(null, null, null, null, $id_users_arr);
        }
        if (isset($users_arr)) {
            foreach ($users_arr as $value) {
                $username_arr[$value['id'] . '_name'] = $value['fname'] . ' ' . $value['sname'];
            }
            foreach ($transactions as $key => $transaction) {
                if ($transactions[$key]['id_sender'] == $id_user) {
                    $transactions[$key]['amount'] = '-' . $transactions[$key]['amount'];
                    $transactions[$key]['full_amount'] = '-' . $transactions[$key]['full_amount'];
                }
                $transactions[$key]['amount'] = number_format($transactions[$key]['amount'], 2, '.', '');
                $transactions[$key]['transfer_fee'] = $transaction['full_amount'] - $transaction['amount'];
                if ($transaction['id_user'] == $id_user) {
                    $transactions[$key]['comment'] = l('send_money_gift_from', 'send_money') .
                            ' ' . $username_arr[$transaction['id_sender'] . '_name'];
                } else {
                    $transactions[$key]['comment'] = l('send_money_gift_for', 'send_money') .
                            ' ' . $username_arr[$transaction['id_user'] . '_name'];
                }
                $transactions[$key]['rand'] = mt_rand(0, 10000);

                if ($transaction['status'] == 'waiting') {
                    $transactions[$key]['declineLink'] = 'send_money/decline';
                    if ($transaction['id_user'] == $id_user) {
                        $transactions[$key]['approveLink'] = 'send_money/approve';
                    }
                }
            }

            return $transactions;
        }
    }
}
