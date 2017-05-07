<?php

/**
 * like me helper
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category    helpers
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Nikita Savanaev <nsavanaev@pilotgroup.net>
 **/
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

if (!function_exists('play')) {
    function play($params)
    {
        $CI = &get_instance();
        $CI->load->model("like_me/models/Like_me_model");

        if (!empty($params['value']['user'])) {
            $user_data = $params['value']['user'];
            $CI->view->assign('user_data', $user_data);
        } else {
            $play_more = unserialize($CI->pg_module->get_module_config('like_me', 'play_more'));
            if (!$CI->pg_module->is_module_installed('perfect_match')) {
                unset($play_more['perfect']);
            }
            $CI->view->assign('play_more', $play_more);
        }
        if ($params['value']['type'] == 'matches') {
            $module_gid = $CI->pg_module->get_module_config('like_me', 'chat_more');
            $settings = $CI->Like_me_model->getActionModules($module_gid);
            $CI->view->assign('settings', $settings);

            $return = $CI->view->fetch('like_me_matches', 'user', 'like_me');
        } else {
            $return = $CI->view->fetch('helper_play', 'user', 'like_me');
        }

        return $return;
    }
}

if (!function_exists('like_me_start')) {
    function like_me_start($params)
    {
        $ci = &get_instance();

        return $ci->view->fetch('helper_start', 'user', 'like_me');
    }
}
