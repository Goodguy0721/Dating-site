<?php

/**
 * Show chat
 *
 * @package PG_Core
 * @subpackage application
 *
 * @category    helpers
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Alexander Batukhtin <abatukhtin@pilotgroup.net>
 * */
if (!function_exists('chats_block')) {
    function chats_block()
    {
        /* <custom> */
        return false;
        /* </custom> */

        $ci = &get_instance();
        $ci->load->model('Chats_model');
        $chat = $ci->Chats_model->get_active(true);
        if (empty($chat) || !in_array('include', $chat->get_activities())) {
            return '';
        }
        $ci->view->assign('include_block', $chat->include_block());

        return $ci->view->fetch('helper_block', 'user', 'chats');
    }
}

if (!function_exists('helper_btn_chat')) {
    function helper_btn_chat($params)
    {
        $ci = &get_instance();
        $ci->load->model('Chats_model');
        $chat = $ci->Chats_model->get_active(true);
        if (!isset($chat)) {
            return;
        }
        $ci->view->assign('chat_gid', $chat->get_gid());
        $ci->view->assign('user_id', intval($params['user_id']));

        if (empty($params['view_type'])) {
            $params['view_type'] = 'button';
        }

        return $ci->view->fetch('helper_btn_chat_' . $params['view_type'], 'user', 'chats');
    }
}
