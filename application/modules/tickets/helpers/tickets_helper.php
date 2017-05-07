<?php

/**
 * Comments helper
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	helpers
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Nikita Savanaev
 *
 * @version $Revision: 2 $ $Date: 2014-04-22 10:07:07 +0400 $
 **/
if (!function_exists('get_admin_message_block')) {
    function get_admin_message_block($param)
    {
        if ($param) {
            $CI = &get_instance();
            $CI->load->model('Tickets_model');
            $settings = $CI->Tickets_model->get_settings();
            if ($settings['status_personal_communication']) {
                $CI->load->model('Users_model');
                $contact_data = array();
                $contact_data = $CI->Users_model->format_user($CI->Users_model->get_user_by_id($param['gid']));
                if ($contact_data) {
                    $CI->view->assign('contact_data', $contact_data);
                } elseif (empty($contact_data) && $param['gid']) {
                    return false;
                }

                $CI->view->assign('rand', mt_rand(0, 100000));

                return $CI->view->fetch('helper_admin_message_block', 'admin', 'tickets');
            } else {
                return false;
            }
        }

        return false;
    }
}

if (!function_exists('admin_new_messages')) {
    function admin_new_messages($attrs)
    {
        $CI = &get_instance();
        $CI->load->model('Tickets_model');
        $settings = $CI->Tickets_model->get_settings();
        if ($settings['status_personal_communication']) {
            if ('user' != $CI->session->userdata('auth_type')) {
                return false;
            }
            $id_user = $CI->session->userdata('user_id');
            if (!$id_user) {
                log_message('Empty user id');

                return false;
            }
            if (empty($attrs['template'])) {
                $attrs['template'] = 'header';
            }
            $CI->load->model('Tickets_model');
            $count = $CI->Tickets_model->get_count_new_messages($id_user, filter_var($attrs['is_admin'], FILTER_VALIDATE_BOOLEAN));
            $CI->view->assign('admin_messages_count', $count);

            $params = array(
                'where' => array(
                    'id_user'         => $id_user,
                    'is_new'          => 1,
                    'is_admin_sender' => 1,
                ),
            );

            $messages = $CI->Tickets_model->get_messages($params, 1, $CI->Tickets_model->messages_max_count_header, null, array('date_created' => 'DESC'));

            $CI->view->assign('messages', $messages);
            $CI->view->assign('messages_max_count', $CI->Tickets_model->messages_max_count_header);

            return $CI->view->fetch('helper_admin_new_messages_' . $attrs['template'], 'user', 'tickets');
        } else {
            return false;
        }
    }
}

if (!function_exists('contact_user_link')) {
    function contact_user_link($attrs)
    {
        $CI = &get_instance();
        $CI->view->assign('id_user', $attrs['id_user']);
        $CI->load->model('Tickets_model');
        $settings = $CI->Tickets_model->get_settings();
        if ($settings['status_personal_communication']) {
            return $CI->view->fetch('helper_contact_user_link', 'admin', 'tickets');
        } else {
            return false;
        }
    }
}
