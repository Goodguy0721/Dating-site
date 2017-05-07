<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

if (!function_exists('show_social_networking_link')) {
    function show_social_networking_link()
    {
        $ci = &get_instance();
        $user_id = $ci->session->userdata('user_id');
        $ci->load->model('social_networking/models/Social_networking_services_model');
        $ci->load->model('users_connections/models/Users_connections_model');
        $services = $ci->Social_networking_services_model
            ->get_services_list(null, array('where' => array('oauth_status' => 1)));
        $apps = array();
        $unapps = array();
        foreach ($services as $id => $val) {
            $connection = $ci->Users_connections_model->get_connection_by_user_id($val['id'], $user_id);
            if ($connection && isset($connection['id'])) {
                $unapps[$id] = $val;
            } else {
                $apps[$id] = $val;
            }
        }
        $ci->view->assign("applications", $apps);
        $ci->view->assign("un_applications", $unapps);
        $ci->view->assign("site_url", site_url());
        echo $ci->view->fetch("oauth_link", 'user', 'users_connections');
    }
}

if (!function_exists('show_social_networking_login')) {
    function show_social_networking_login()
    {
        $ci = &get_instance();
        $ci->load->model('social_networking/models/Social_networking_services_model');
        $services = $ci->Social_networking_services_model->get_services_list(null, array('where' => array('oauth_status' => 1)));
        $ci->view->assign("services", $services);
        $ci->view->assign("site_url", site_url());

        return $ci->view->fetch('oauth_login', 'user', 'users_connections');
    }
}

if (!function_exists('show_social_networking_add')) {
    function show_social_networking_add()
    {
        $ci = &get_instance();
        $ci->load->model('social_networking/models/Social_networking_services_model');
        $services = $ci->Social_networking_services_model->get_services_list(null, ['where' => ['oauth_status' => 1]]);
        $ci->view->assign("services", $services);
        $ci->view->assign("site_url", site_url());

        return $ci->view->fetch('add_social_account', 'user', 'users_connections');
    }
}