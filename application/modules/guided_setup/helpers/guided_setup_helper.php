<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

if (!function_exists('guidePageBtn')) {
    function guidePageBtn($params = array())
    {
        if(!isset($params['menu_gid'])) {
            return;
        }

        $CI = &get_instance();

        $CI->load->model("Guided_setup_model");
        $menu = $CI->Guided_setup_model->getMenuByGid($params['menu_gid']);
        $CI->view->assign('guided_menu', $menu);

        return $CI->view->fetch('helper_btn_' . $params['menu_gid'], 'admin', 'guided_setup');
    }
}

