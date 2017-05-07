<?php

use Pg\Modules\Memberships\Models\Memberships_model;

/**
 * Send_vip module
 *
 * @package 	PG_Dating
 *
 * @copyright 	Copyright (c) 2000-2015 PG Dating Pro - php dating software
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
if (!function_exists('send_vip_block')) {
    function send_vip_block()
    {
        $ci = &get_instance();
        $ci->load->model('Send_vip_model');
        $ci->load->model('payments/models/Payment_currency_model');

        $base_currency = $ci->Payment_currency_model->get_currency_default(true);
        $cur_currency = $base_currency['gid'];
        $currency = $ci->pg_module->get_module_config('send_vip', 'fee_currency');
        $use_fee = $ci->pg_module->get_module_config('send_vip', 'use_fee');
        $transfer_fee = $ci->pg_module->get_module_config('send_vip', 'fee_price');
        if ($currency == '%') {
            $koef = (float) $transfer_fee / 100;//ToDo: May be $koef need store in config table?
        } else {
            $koef = 1;
        }

        $ci->load->model('Users_model');
        $ci->load->model('Memberships_model');

        $memberships = $ci->Memberships_model->getMembershipsList();
        $ci->Memberships_model->setFormatSettings('get_services', true);
        $memberships = $ci->Memberships_model->formatMemberships($memberships);
        $ci->Memberships_model->setFormatSettings('get_services', false);
        $all_services = Memberships_model::getServicesByMemberships($memberships);
        foreach ($memberships as $key => $value) {
            $js_memberships[$memberships[$key]['id']] = $memberships[$key];
        }
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
        $friends_only = $ci->pg_module->get_module_config('send_vip', 'to_whom');

        $action = site_url() . "send_vip/confirm/";

        $ci->view->assign('cur_currency', $cur_currency);
        $ci->view->assign('currency', $currency);
        $ci->view->assign('use_fee', $use_fee);
        $ci->view->assign('transfer_fee', $transfer_fee);
        $ci->view->assign('koef', $koef);
        $ci->view->assign('rand', mt_rand(100, 500));
        $ci->view->assign('action', $action);
        $ci->view->assign('user_id', $ci->session->userdata('user_id'));
        $ci->view->assign('memberships', $memberships);
        $ci->view->assign('memberships_count', $ci->Memberships_model->getMembershipsCount());
        $ci->view->assign('all_services', $all_services);
        $ci->view->assign('js_memberships', json_encode($js_memberships));
        $ci->view->assign('friends_only', $friends_only);

        return $ci->view->fetch('helper_send_vip_form', 'user', 'send_vip');
    }
}

if (!function_exists('send_vip_view_block')) {
    function send_vip_view_block()
    {
        $ci = &get_instance();
        $ci->load->model('Send_vip_model');
        $ci->load->model('Users_model');
        $ci->load->model('payments/models/Payment_currency_model');
        $ci->load->model('Memberships_model');
        $transactions = $ci->Send_vip_model->getTransaction();
        $id_user = $ci->session->userdata('user_id');
        $lang_id = $ci->pg_language->current_lang_id;

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

            $memberships_names_arr = $ci->Memberships_model->getMembershipsList();
            foreach ($memberships_names_arr as $key => $value) {
                $memberships_names[$value['id']]['name'] = $value['name_' . $lang_id];
                $memberships_names[$value['id']]['price'] = $value['price'];
            }

            foreach ($transactions as $key => $transaction) {
                $transactions[$key]['transfer_fee'] = $transaction['transfer_fee'];
                $transactions[$key]['name'] = $username_arr[$transaction['id_user'] . '_name'];
                $transactions[$key]['rand'] = mt_rand(0, 10000);
                if ($transaction['id_user'] == $id_user) {
                    $transactions[$key]['comment'] = $username_arr[$transaction['id_sender'] . '_name'] .
                            ' ' . l('send_vip_gift_from', 'send_vip') . ' ' .
                            $memberships_names[$transaction['id_membership']]['name'];
                } else {
                    $transactions[$key]['comment'] = $memberships_names[$transaction['id_membership']]['name'] .
                            ' ' . l('send_vip_gift_for', 'send_vip') . ' ' .
                            $username_arr[$transaction['id_user'] . '_name'];
                }
                $transactions[$key]['membership_name'] = $memberships_names[$transaction['id_membership']]['name'];
                if ($transactions[$key]['id_sender'] == $id_user) {
                    $transactions[$key]['full_amount'] = '-' .
                            ($memberships_names[$transaction['id_membership']]['price'] +
                             $transactions[$key]['transfer_fee']);
                }

                if ($transaction['status'] == 'waiting') {
                    $transactions[$key]['declineLink'] = 'send_vip/decline';
                    if ($transaction['id_user'] == $id_user) {
                        $transactions[$key]['approveLink'] = 'send_vip/approve';
                    }
                }
            }

            return $transactions;
        }
    }
}
