<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

if (!function_exists('friendlist_links')) {
    function friendlist_links($params)
    {
        $id_dest_user = filter_var($params['id_user'], FILTER_VALIDATE_INT);
        if (!$id_dest_user) {
            return '';
        }

        $CI = &get_instance();

        if ($CI->session->userdata('auth_type') !== 'user') {
            return '';
        }

        $id_user = $CI->session->userdata('user_id');
        if ($id_user == $id_dest_user) {
            return '';
        }

        $CI->load->model('Friendlist_model');
        $statuses = $CI->Friendlist_model->get_statuses($id_user, $id_dest_user);
        $buttons = array();
        foreach ($statuses['allowed_btns'] as $key => $value) {
            if ($value['allow']) {
                $buttons[$key] = $value;
            }
        }

        $CI->view->assign('id_user', $id_user);
        $CI->view->assign('id_dest_user', $id_dest_user);
        $CI->view->assign('buttons', $buttons);

        $CI->view->assign('friendlist_button_rand', rand(100000, 999999));

        if (empty($params['view_type'])) {
            $params['view_type'] = 'button';
        }

        return $CI->view->fetch('helper_lists_' . $params['view_type'], 'user', 'friendlist');
    }
}

if (!function_exists('friend_input')) {
    function friend_input($params)
    {
        $CI = &get_instance();
        $CI->load->model('Users_model');
        $CI->load->model('Friendlist_model');

        $user_id = $CI->session->userdata('user_id');

        $friends_ids = $CI->Friendlist_model->get_friendlist_users_ids($user_id);
        if (empty($friends_ids)) {
            return '';
        }

        if (!isset($params['id_user']) && !empty($params['id_user'])) {
            $data['user'] = $CI->Users_model->get_user_by_id($params['id_user']);
        }

        $data['var_user_name'] = isset($params['var_user_name']) ? $params['var_user_name'] : 'id_user';
        $data['var_js_name'] = isset($params['var_js_name']) ? $params['var_js_name'] : '';
        $data['placeholder'] = isset($params['placeholder']) ? $params['placeholder'] : '';
        $data['values_callback'] = isset($params['values_callback']) ? $params['values_callback'] : '';

        $data['rand'] = rand(100000, 999999);

        $CI->view->assign('friend_helper_data', $data);

        return $CI->view->fetch('helper_friend_input', 'user', 'friendlist');
    }
}

if (!function_exists('friend_select')) {
    function friend_select($params)
    {
        $CI = &get_instance();
        $CI->load->model('Users_model');
        $CI->load->model('Friendlist_model');

        $user_id = $CI->session->userdata('user_id');
        $friends_ids = $CI->Friendlist_model->get_friendlist_users_ids($user_id);
        if (empty($friends_ids)) {
            return '';
        }

        if (isset($params['id_user']) && !empty($params['id_user'])) {
            $data['user'] = $CI->Users_model->get_user($params['id_user']);
        }

        $data['var_user_name'] = isset($params['var_user_name']) ? $params['var_user_name'] : 'id_user';
        $data['var_js_name'] = isset($params['var_js_name']) ? $params['var_js_name'] : '';

        $data['rand'] = rand(100000, 999999);

        $CI->view->assign('friend_helper_data', $data);

        return $CI->view->fetch('helper_friend_select', 'user', 'friendlist');
    }
}

if (!function_exists('friend_requests')) {
    function friend_requests($attrs)
    {
        $CI = &get_instance();
        $CI->load->model('Friendlist_model');
        $count = $CI->Friendlist_model->get_list_count((int) $CI->session->userdata('user_id'), 'request_in');
        $CI->view->assign('friend_requests_count', $count);

        return $CI->view->fetch('helper_friend_requests_' . $attrs['template'], 'user', 'friendlist');
    }
}

if (!function_exists('add_friendlist_button')) {
    function add_friendlist_button($params)
    {
        $CI = &get_instance();
        $CI->load->model('Friendlist_model');
        if (!isset($params['id_user']) || empty($params['id_user'])) {
            return '';
        }
        $user_id = $CI->session->userdata('user_id');
        $blacklist_ids = $CI->Friendlist_model->get_list_users_ids($user_id);
        if (in_array($params['id_user'], $blacklist_ids)) {
            return '';
        }
        $CI->view->assign('id_dest_user', $params['id_user']);

        return $CI->view->fetch('helper_add_friendlist', 'user', 'friendlist');
    }
}
