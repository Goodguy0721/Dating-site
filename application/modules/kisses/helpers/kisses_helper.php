<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

if (!function_exists("kisses_list")) {
    /**
     * Show mark as spam button
     *
     * @param array $data
     *
     * @return html
     */
    function kisses_list($data)
    {
        $CI = &get_instance();

        if (empty($data["user_id"])) {
            return '';
        }

        $CI->view->assign('user_id', $data["user_id"]);

        $user_id = $CI->session->userdata("user_id");
        if (!$user_id) {
            $CI->view->assign('is_user', 0);
        } else {
            $CI->view->assign('is_user', 1);
        }

        $CI->load->model('Kisses_model');
        $count = $CI->Kisses_model->get_count();
        if ($count == 0) {
            return '';
        }

        $CI->view->assign('kisses_button_rand', rand(100000, 999999));

        if (empty($data['view_type'])) {
            $data['view_type'] = 'button';
        }

        return $CI->view->fetch("helper_kisses_" . $data['view_type'], "user", "kisses");
    }

    /**
     * Echo count kisses
     *
     * @param array $data
     *
     * @return html
     */
    if (!function_exists('new_kisses')) {
        function new_kisses($attrs)
        {
            $CI = &get_instance();
            if ('user' != $CI->session->userdata("auth_type")) {
                return false;
            }

            $user_id = $CI->session->userdata("user_id");

            if (!$user_id) {
                log_message('Empty user id');

                return false;
            }

            /* deprecation: use view_type */
            if (!empty($attrs['template'])) {
                $attrs['view_type'] = $attrs['template'];
            }
            /* deprecation: use view_type */

            if (empty($attrs['view_type'])) {
                $attrs['view_type'] = 'header';
            }

            $CI->load->model('Kisses_model');
            $count = $CI->Kisses_model->new_kisses_count($user_id);
            $CI->view->assign('kisses_count', $count);

            return $CI->view->fetch('helper_new_kisses_' . $attrs['view_type'], 'user', 'kisses');
        }
    }
}
