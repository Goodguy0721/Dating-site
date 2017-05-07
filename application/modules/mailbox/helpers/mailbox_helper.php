<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

if (!function_exists('new_messages')) {
    function new_messages($attrs)
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
        if (empty($attrs['template'])) {
            $attrs['template'] = 'header';
        }
        $CI->load->model('Mailbox_model');
        $CI->Mailbox_model->set_format_settings('get_sender', true);
        $params = array(
            'where' => array(
                'id_user' => $user_id,
                'is_new'  => 1,
                'folder'  => 'inbox',
            ),
        );
        $messages_count = $CI->Mailbox_model->get_messages_count($params);
        $messages = $CI->Mailbox_model->get_messages($params, 1, $CI->Mailbox_model->messages_max_count_header, array('date_add' => 'DESC'));

        $CI->view->assign('messages', $messages);
        $CI->view->assign('messages_count', $messages_count);
        $CI->view->assign('messages_max_count', $CI->Mailbox_model->messages_max_count_header);

        return $CI->view->fetch('helper_new_messages_' . $attrs['template'], 'user', 'mailbox');
    }
}

if (!function_exists('send_message_button')) {

    /**
     * Return new message button
     *
     * @param array $user_id user identifier
     *
     * @return string
     */
    function send_message_button($params)
    {
        $CI = &get_instance();

        if (!isset($params['id_user'])) {
            return '';
        }

        if ($CI->session->userdata('auth_type') == 'user') {
            $user_id = $CI->session->userdata('user_id');
            if ($params['id_user'] == $user_id) {
                return '';
            }
        }

        $CI->view->assign('user_id', $params['id_user']);
        $CI->view->assign('message_button_rand', rand(100000, 999999));

        if (empty($params['view_type'])) {
            $params['view_type'] = 'button';
        }

        switch ($params['view_type']) {
            case 'link':
                return $CI->view->fetch('helper_message_link', 'user', 'mailbox');
                break;

            case 'icon':
                return $CI->view->fetch('helper_message_icon', 'user', 'mailbox');
                break;

            case 'button':
            default:
                $type = (!empty($params['type'])) ? trim(strip_tags($params['type'])) . '_' : '';

                return $CI->view->fetch('helper_' . $type . 'message_button', 'user', 'mailbox');
                break;
        }
    }
}
