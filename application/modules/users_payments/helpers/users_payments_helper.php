<?php

if (!function_exists('update_account_block')) {
    function update_account_block()
    {
        $CI = &get_instance();
        $CI->load->model('payments/models/Payment_systems_model');

        $billing_systems = $CI->Payment_systems_model->get_active_system_list();
        $CI->view->assign('billing_systems', $billing_systems);

        $CI->load->model('payments/models/Payment_currency_model');
        $base_currency = $CI->Payment_currency_model->get_currency_default(true);
        $CI->view->assign('base_currency', $base_currency);

        return $CI->view->fetch('helper_update_account', 'user', 'users_payments');
    }
}

if (!function_exists('add_funds_button')) {
    function button_add_funds()
    {
        $CI = &get_instance();

        $CI->load->model('payments/models/Payment_currency_model');
        $base_currency = $CI->Payment_currency_model->get_currency_default(true);
        $CI->view->assign('base_currency', $base_currency);

        return $CI->view->fetch('helper_add_funds', 'admin', 'users_payments');
    }
}

if (!function_exists('user_account')) {
    function user_account()
    {
        $CI = &get_instance();
        if ('user' != $CI->session->userdata('auth_type')) {
            return false;
        }
        $user_id = $CI->session->userdata('user_id');

        $CI->load->model('Users_payments_model');
        $CI->view->assign('user_account', $CI->Users_payments_model->get_user_account($user_id));

        $CI->load->model('payments/models/Payment_currency_model');
        $CI->view->assign('base_currency', $CI->Payment_currency_model->get_currency_default(true));

        if ($CI->pg_module->is_module_installed('services')) {
            $CI->load->model('services/models/Services_users_model');
            $user_services = $CI->Services_users_model->getUserServices($user_id, $CI->session->userdata('lang_id'));
            $CI->view->assign('user_services', $user_services);
        }

        if ($CI->pg_module->is_module_installed('memberships')) {
            $CI->load->model('memberships/models/Memberships_users_model');
            $user_memberships = $CI->Memberships_users_model->getUserMemberships($user_id);
            $CI->view->assign('user_memberships', $user_memberships);
        }

        return $CI->view->fetch('helper_account', 'user', 'users_payments');
    }
}
