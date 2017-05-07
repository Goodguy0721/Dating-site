<?php

/**
 * Perfect_match module
 *
 * @package 	PG_Dating
 *
 * @copyright 	Copyright (c) 2000-2014 PG Dating Pro - php dating software
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

if (!function_exists('perfect_match_form')) {
    function perfect_match_form()
    {
        $CI = &get_instance();

        $CI->load->model('Perfect_match_model');
        $CI->load->model('Field_editor_model');

        $form_settings = array(
            'type'         => 'advanced',
            'form_id'      => 'user_advanced',
            'use_advanced' => false,
            'action'       => site_url() . 'perfect_match/search',
            'object'       => 'user',
            'form_url'     => 'perfect_match/search',
            'search_url'   => 'perfect_match/ajaxSearch',
            'count_url'    => 'perfect_match/ajaxSearchCounts',
        );

        $user_id = $CI->session->userdata('user_id');
        $auth_type = $CI->session->userdata('auth_type');

        $validate_settings = array();

        $CI->Field_editor_model->initialize($CI->Perfect_match_model->form_editor_type);
        $fields_for_select = $CI->Field_editor_model->get_fields_for_select();
        $CI->Perfect_match_model->setAdditionalFields($fields_for_select);

        if ($CI->session->userdata("perfect_match_full")) {
            $current_settings = $CI->session->userdata("perfect_match_full");
        } else {
            $current_settings = $CI->Perfect_match_model->getUserParams($user_id);
        }

        $validate_settings = $CI->Perfect_match_model->validate($current_settings, 'select');

        $CI->load->model('Properties_model');
        $user_types = $CI->Properties_model->get_property('user_type');

        $CI->view->assign('user_types', $user_types);

        $min_age = $CI->pg_module->get_module_config('users', 'age_min');
        $max_age = $CI->pg_module->get_module_config('users', 'age_max');
        for ($i = $min_age; $i <= $max_age; ++$i) {
            $age_range[$i] = $i;
        }
        $CI->view->assign('age_range', $age_range);

        $sb_selected = "";
        if (!empty($validate_settings['data']['user_type']) && array_key_exists($validate_settings['data']['user_type'], $user_types['option'])) {
            $sb_selected = $validate_settings['data']['user_type'];
        }
        $CI->view->assign('sb_selected', $sb_selected);

        $sb_option["all"] = l('filter_all', 'users');
        foreach ($user_types['option'] as $key => $value) {
            $sb_option[$key] = $value;
        }
        $CI->view->assign("sb_option", $sb_option);

        $form = $CI->Field_editor_forms_model->get_form_by_gid($CI->Perfect_match_model->perfect_match_form_gid, $CI->Perfect_match_model->form_editor_type);
        $form = $CI->Field_editor_forms_model->format_output_form($form, $validate_settings['data']);

        if (!empty($form['field_data'])) {
            foreach ($form['field_data'] as $key => $field_data) {
                if (!empty($field_data['section']['fields'])) {
                    $form_settings["use_advanced"] = true;
                    break;
                } elseif (!empty($field_data['field'])) {
                    $form_settings["use_advanced"] = true;
                    break;
                } else {
                    unset($form['field_data'][$key]);
                }
            }

            $CI->view->assign('advanced_form', $form['field_data']);
        }

        $CI->view->assign('data', !empty($validate_settings["data"]) ? $validate_settings["data"] : array());
        $CI->view->assign('form_settings', $form_settings);
        $html = $CI->view->fetch("helper_search_form", 'user', 'users');

        return $html;
    }
}

if (!function_exists('search_field_block')) {
    function search_field_block($params = array())
    {
        $CI = &get_instance();

        $CI->view->assign('field', $params['field']);
        $CI->view->assign('field_name', $params['field_name']);
        $html = $CI->view->fetch("helper_search_field_block", 'user', 'users');

        return $html;
    }
}
