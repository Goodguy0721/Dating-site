<?php

/**
 * Events helper
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	helpers
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Nikita Savanaev <nsavanaev@pilotgroup.net>
 * */
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

if (!function_exists('events_button')) {
    function events_button($params)
    {
        $CI = &get_instance();
        $CI->load->model("events/models/Events_model");
        $settings = $CI->Events_model->getSettings();
        if (!empty($settings['is_active'])) {
            $count_images = $CI->Events_model->getImagesCount();
            if ($count_images > 0) {
                $events = array();
                $events['profile_id'] = intval($params['id_user']);
                $attrs = array();
                $attrs['where']['id_user'] = $CI->session->userdata('user_id');
                $attrs['where']['id_profile'] = $events['profile_id'];
                $data = $CI->Events_model->getEventsUser(null, 1, null, $attrs);
                $events['compared'] = empty($data) ? false : true;
                $CI->template_lite->assign('events', $events);
                $CI->template_lite->assign('events_button_rand', rand(100000, 999999));

                return $CI->template_lite->fetch('helper_events', 'user', 'events');
            }
        }

        return false;
    }
}

if (!function_exists('perfect_match')) {
    function perfect_match($params)
    {
        $CI = &get_instance();
        $CI->load->model('users/models/Users_model');

        $return = '';

        if ($CI->pg_module->is_module_installed('perfect_match')) {
            $CI->load->model('Perfect_match_model');
            $user_id = $CI->session->userdata('user_id');
            $search_data = $CI->Perfect_match_model->getUserParams($user_id);
            $params = $CI->Perfect_match_model->getCommonCriteria($search_data['full_criteria']);
            $users = $CI->Perfect_match_model->getUsersList(1, 3, null, $params);
        } else {
            $params = $CI->Users_model->get_common_criteria(null);
            $params['where']["user_logo !="] = '';
            $users = $CI->Users_model->get_users_list(1, 3, array("date_created" => "DESC"), $params);
        }

        if (!empty($users)) {
            $page_data = array('view_type' => 'gallery');
            $CI->view->assign('page_data', $page_data);

            $CI->view->assign('users', $users);

            $return = $CI->view->fetch('users_list_block', 'user', 'users');
        }

        return $return;
    }
}

if (!function_exists('events_search_form')) {
    function events_search_form($params)
    {
        $CI = &get_instance();

        //$CI->load->helper("lang_helper");
        $options = get_events_categories(true);
        $CI->view->assign('category_options', $options);

        $form_settings = array(
            'action'  => site_url() . "events/" . $params['action'] . "/" . $params['search_type'],
            'form_id' => 'events',
            'type'    => '',
        );
        $CI->view->assign('form_settings', $form_settings);

        $CI->view->assign('location_lang', l('location', 'events'));

        $html = $CI->view->fetch("helper_search_form", 'user', 'events');

        return $html;
    }
}

if(!function_exists('get_events_categories')) {
    function get_events_categories($select_str = false)
    {
        $options = ld('category', 'events');
        if($select_str) {
            array_unshift($options['option'], l('please_select', 'events'));
        }
        
        return $options;
    }
}

if (!function_exists('events_nav_side_links')) {
    function events_nav_side_links($params)
    {
        $CI = &get_instance();
        $CI->view->assign('search_type', $params['search_type']);

        $action = isset($params['action']) ? $params['action'] : 'search';
        $CI->view->assign('action', $action);
        
        $template = isset($params['template']) ? '_' . $params['template'] : '';

        return $CI->view->fetch("helper_events_nav_side_links" . $template, 'user', 'events');
    }
}

if (!function_exists('events_nav_top_links')) {
    function events_nav_top_links($params)
    {
        $CI = &get_instance();
        $CI->view->assign('view_type', $params['view_type']);

        return $CI->view->fetch("helper_events_nav_top_links", 'user', 'events');
    }
}

if (!function_exists('events_invite_block')) {
    function events_invite_block()
    {
        $CI = &get_instance();

        return $CI->view->fetch("helper_events_invite_block", 'user', 'events');
    }
}

if (!function_exists('events_album_photos_block')) {
    function events_album_photos_block($params)
    {
        if ($params['album_id']) {
            $CI = &get_instance();
            $CI->load->model("events/models/Events_model");

            $media = $CI->Events_model->getMedia($params['album_id']);
            $CI->view->assign('media', $media);
            $CI->view->assign('media_count', count($media));
            $CI->view->assign('album_id', $params['album_id']);

            return $CI->view->fetch("helper_images_block", 'user', 'events');
        }
    }
}

if (!function_exists('event_requests')) {
    function event_requests($attrs)
    {
        $CI = &get_instance();
        $CI->load->model('events/models/Events_model');
        $params = array(
            'where' => array(
                'fk_user_id' => $CI->session->userdata("user_id"),
                'status'     => 'pending',
                'is_invite'  => 1,
            ),
        );
        $events_count = $CI->Events_model->getEventsUserCount($params);
        $CI->view->assign('events_count', $events_count);

        return $CI->view->fetch('helper_event_requests_' . $attrs['template'], 'user', 'events');
    }
}

if (!function_exists('approve_users_block')) {
    function approve_users_block($attrs)
    {
        $CI = &get_instance();
        $CI->load->model('Events_model');

        $params = array();
        if(isset($attrs['count'])) {
            $params['limit']['count'] = $attrs['count'];
        }

        $approved_users = $CI->Events_model->getApprovedUsers($attrs['event_id'], $params);
        $CI->view->assign('approved_users', $approved_users);
        $CI->view->assign('approved_users_count', count($approved_users));
        
        $settings['participants_count'] = $CI->Events_model->getApprovedUsersCount($attrs['event_id']);
        $CI->view->assign('settings', $settings);
        
        $CI->view->assign('event_id', $attrs['event_id']);

        return $CI->view->fetch('helper_approve_users_block', 'user', 'events');
    }
}
