<?php

/**
 * Associations helper
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

if (!function_exists('button')) {
    function button($params)
    {
        $CI = &get_instance();

        $CI->load->model("associations/models/Associations_model");
        $settings = $CI->Associations_model->getSettings();
        if (empty($settings['is_active'])) {
            return false;
        }

        $count_images = $CI->Associations_model->getImagesCount();
        if ($count_images <= 0) {
            return false;
        }

        $associations = array();
        $associations['profile_id'] = intval($params['id_user']);

        $attrs = array();
        $attrs['where']['id_user'] = $CI->session->userdata('user_id');
        $attrs['where']['id_profile'] = $associations['profile_id'];

        $data = $CI->Associations_model->getAssociationsUser(null, 1, null, $attrs);
        $associations['compared'] = empty($data) ? false : true;
        $CI->view->assign('associations', $associations);

        $CI->view->assign('associations_button_rand', rand(100000, 999999));

        if (empty($params['view_type'])) {
            $params['view_type'] = 'button';
        }

        return $CI->view->fetch('helper_associations_' . $params['view_type'], 'user', 'associations');
    }
}

if (!function_exists('perfect_match')) {
    function perfect_match($params = array())
    {
        $CI = &get_instance();
        $CI->load->model('Users_model');

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

if (!function_exists('new_associations')) {
    function new_associations($params) 
    {
        $CI = &get_instance();
        $CI->load->model("associations/models/Associations_model");

        $associations_data = $CI->Associations_model->getNewAssociations();

        $CI->view->assign('count', $associations_data['count']);
        $CI->view->assign('gid', $associations_data['gid']);
        
        return $CI->view->fetch('helper_new_associations_' . $params['template'], 'user', 'associations');
    }
}
