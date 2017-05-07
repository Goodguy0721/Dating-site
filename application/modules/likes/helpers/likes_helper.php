<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

if (!function_exists('like_block')) {
    function like_block($attrs)
    {
        $CI = &get_instance();

        if (empty($attrs['gid'])) {
            log_message('Empty gid');

            return false;
        }
        $CI->load->model('Likes_model');
        $CI->Likes_model->remember_gid($attrs['gid']);

        $attrs['block_class'] = !empty($attrs['block_class']) ? $attrs['block_class'] . ' pointer' : 'pointer';
        $attrs['template'] = isset($attrs['template']) ? $attrs['template'] : '';

        $block_class = !empty($attrs['block_class']) ? ' ' . $attrs['block_class'] : '';
        $num_class = !empty($attrs['num_class']) ? ' ' . $attrs['num_class'] : '';
        $btn_class = !empty($attrs['btn_class']) ? ' ' . $attrs['btn_class'] : '';
        $CI->view->assign('likes_helper_block_class', $block_class);
        $CI->view->assign('likes_helper_num_class', $num_class);
        $CI->view->assign('likes_helper_btn_class', $btn_class);
        $CI->view->assign('likes_helper_gid', $attrs['gid']);
        $CI->view->assign('template', $attrs['template']);
        $button = $CI->view->fetch('helper_button', 'user', 'likes');

        return $button;
    }
}

if (!function_exists('likes')) {
    function likes()
    {
        $CI = &get_instance();
        $data['can_like'] = ($CI->session->userdata('auth_type') == 'user');
        $data['like_title'] = l('like', 'likes');
        $CI->view->assign('likes_helper_data', $data);

        return $CI->view->fetch('helper_likes', 'user', 'likes');
    }
}
