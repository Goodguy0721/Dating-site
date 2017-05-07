<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

if (!function_exists('favourites_button')) {
    function favourites_button($params)
    {
        $CI = &get_instance();
        $CI->load->model('Favourites_model');
        if (!isset($params['id_user']) || empty($params['id_user'])) {
            return '';
        }

        if ($CI->session->userdata('auth_type') != 'user') {
            return '';
        }

        $user_id = $CI->session->userdata('user_id');
        if (!$user_id || $user_id == $params['id_user']) {
            return '';
        }

        if (in_array($params['id_user'], $CI->Favourites_model->get_list_users_ids($user_id))) {
            $action = 'remove';
        } else {
            $action = 'add';
        }
        $CI->view->assign('action', $action);

        $CI->view->assign('id_dest_user', $params['id_user']);

        if (empty($params['view_type'])) {
            $params['view_type'] = 'button';
        }

        return $CI->view->fetch('helper_favourites_' . $params['view_type'], 'user', 'favourites');
    }
}
