<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

if (!function_exists("questions_list")) {
    /**
     * Show mark as spam button
     *
     * @param array $params
     *
     * @return html
     */
    function questions_list($params)
    {
        $CI = &get_instance();
        $CI->load->model('questions/models/Questions_model');
        $settings = $CI->Questions_model->getSettings();
        if ($settings['is_active'] != "1") {
            return '';
        }

        $compared = $CI->Questions_model->getCompared($params["user_id"]);
        $CI->view->assign('user_compared', $compared);

        if (empty($params["user_id"])) {
            return '';
        }

        $CI->view->assign('user_id', $params["user_id"]);

        if ($CI->session->userdata('auth_type') == 'user') {
            $user_id = $CI->session->userdata("user_id");
        } else {
            $user_id = 0;
        }

        if (!$user_id) {
            $CI->view->assign('is_user', 0);
        } else {
            $CI->view->assign('is_user', 1);
        }

        if (empty($params['view_type'])) {
            $params['view_type'] = 'button';
        }

        return $CI->view->fetch("helper_questions_" . $params['view_type'], "user", "questions");
    }
    
    function new_questions($params) {
        $CI = &get_instance();
        $CI->load->model('questions/models/Questions_model');
        
        $user_id = $CI->session->userdata('user_id');
        $new_questions = $CI->Questions_model->getNotificationsCount($user_id);
        
        $CI->view->assign('new_questions', $new_questions);
        return $CI->view->fetch("helper_new_questions_" . $params['template'], "user", "questions");
    }
}
