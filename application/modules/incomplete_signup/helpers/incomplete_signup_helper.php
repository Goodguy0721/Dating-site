<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

if (!function_exists('not_registered_add_filter')) {
    function not_registered_add_filter($filter)
    {
        $CI = &get_instance();
        $CI->load->model("Incomplete_signup_model");

        $delay = $CI->pg_module->get_module_config('incomplete_signup', 'show_delay');
        $time = date("Y-m-d H:i:s", time() - $delay * 60);
        $attrs["where"]['date_created <']  = $time;

        $filter_data["not_registered"] = $CI->Incomplete_signup_model->get_users_count($attrs);

        $CI->view->assign('filter', $filter);
        $CI->view->assign('filter_data', $filter_data);

        return $CI->view->fetch('helper_not_registered_filter', 'admin', 'incomplete_signup');
    }
}

if (!function_exists('incomplete_signup_script')) {
    function incomplete_signup_script()
    {
        $CI = &get_instance();

        $CI->view->assign('site_url', site_url());
        $CI->view->assign('timeout_send_data', $CI->pg_module->get_module_config('incomplete_signup', 'timeout_send_data') * 1000);

        return $CI->view->fetch('helper_incomplete_signup_script', 'user', 'incomplete_signup');
    }
}
