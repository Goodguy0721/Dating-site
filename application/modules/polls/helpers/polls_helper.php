<?php

if (!function_exists('show_poll_place_block')) {
    function show_poll_place_block($params)
    {
        $CI = &get_instance();

        if (empty($params['id_poll'])) {
            $params['id_poll'] = 0;
        }

        $poll_block = show_poll_place($params['id_poll'], $params['one_poll_place']);
        if (!$poll_block) {
            return false;
        }

        $CI->view->assign('poll_block', $poll_block);

        return $CI->view->fetch('poll_place_block', 'user', 'polls');
    }
}

if (!function_exists('show_poll_place')) {

    /**
     * @param type $params['id_poll']
     * @param type $params['one_poll_place']
     *
     * @return html
     */
    function show_poll_place($id_poll, $one_poll_place)
    {
        $CI           = &get_instance();
        $CI->load->model('Polls_model');
        $id_user      = $CI->session->userdata('user_id');
        $denied_polls = $CI->Polls_model->get_denied_polls($id_user);
        // Show precise poll
        if ($id_poll) {
            // Check polls existence
            if ($CI->Polls_model->is_exists($id_poll)) {
                // If current user may pass the poll
                if (!in_array($id_poll, $denied_polls)) {
                    return get_form($id_poll, $one_poll_place);
                } else {
                    // Template will decide whether to show results or just a text message
                    if ($one_poll_place || $CI->Polls_model->show_results($id_poll)) {
                        return get_results($id_poll, $one_poll_place);
                    } // else show random poll
                }
            } //else show random poll
        } elseif ($one_poll_place) {
            return false;
        }
        // If we reached here, show random poll
        // Get language and user type
        $language  = $CI->pg_language->current_lang_id;
        $user_type = $CI->session->userdata("user_type");

        // Pick a random poll...
        $id_poll_rnd = $CI->Polls_model->get_random_id(null, $language, $user_type, $denied_polls);
        if ($id_poll_rnd) {
            return get_form($id_poll_rnd, $one_poll_place);
        } else {
            // ...or results
            $id_poll_rnd = $CI->Polls_model->get_random_id(true);
            if ($id_poll_rnd) {
                return get_results($id_poll_rnd, $one_poll_place);
            }
        }
    }
}

if (!function_exists('get_form')) {
    function get_form($id_poll, $one_poll_place)
    {
        if (is_null($id_poll)) {
            return false;
        }

        $CI = &get_instance();
        $CI->load->model('Polls_model');

        $poll     = $CI->Polls_model->get_poll_by_id($id_poll);
        $language = $CI->pg_language->current_lang_id;

        $user_type = $CI->session->userdata("user_type");
        $params    = array();
        if ($user_type) {
            $params['where_sql'][] = " ( poll_type = 0 or poll_type = '$user_type' or poll_type = 3 ) ";
        } else {
            $params['where_sql'][] = ' ( poll_type = 0 or poll_type = 4 ) ';
        }
        $id_user = $CI->session->userdata('user_id');
        if ($id_user) {
            $denied_polls = $CI->Polls_model->get_denied_polls($id_user);
            if ($denied_polls) {
                $params['where_not_in']['id'] = array_unique($denied_polls);
            }
        }
        $polls_count = $CI->Polls_model->get_polls_count($params);

        $CI->view->assign('one_poll_place', $one_poll_place);
        $CI->view->assign('poll_data', $poll);
        $CI->view->assign('cur_lang', $language);
        $CI->view->assign('polls_count', $polls_count);

        return $CI->view->fetch('poll_form', 'user', 'polls');
    }
}

if (!function_exists('get_results')) {
    function get_results($id_poll, $one_poll_place = false)
    {
        if (is_null($id_poll)) {
            return false;
        }

        $CI = &get_instance();
        $CI->load->model('Polls_model');

        $poll     = $CI->Polls_model->get_poll_by_id($id_poll);
        $language = $CI->pg_language->current_lang_id;

        $max_answers = $CI->pg_module->get_module_config('polls', 'max_answers');
        if (!$max_answers) {
            $max_answers = 10;
        }
        $max_results = 0;

        // Results sorting
        for ($i = 1; $i <= $max_answers; ++$i) {
            if (isset($poll['results'][$i])) {
                $max_results = $max_results + floor($poll['results'][$i]);
            }
        }

        if (1 == $poll['sorter']) {
            asort($poll['results']);
        } elseif (2 == $poll['sorter']) {
            arsort($poll['results']);
        }

        $user_type = $CI->session->userdata("user_type");
        $params    = array();
        if ($user_type) {
            $params['where_sql'][] = " ( poll_type = 0 or poll_type = '$user_type' or poll_type = 3 ) ";
        } else {
            $params['where_sql'][] = ' ( poll_type = 0 or poll_type = 4 ) ';
        }
        $id_user = $CI->session->userdata('user_id');
        if ($id_user) {
            $denied_polls = $CI->Polls_model->get_denied_polls($id_user);
            if ($denied_polls) {
                $params['where_not_in']['id'] = array_unique($denied_polls);
            }
        }
        $polls_count = $CI->Polls_model->get_polls_count($params);

        $CI->view->assign('one_poll_place', $one_poll_place);
        $CI->view->assign('polls_count', $polls_count);
        $CI->view->assign('poll_data', $poll);
        $CI->view->assign('poll_lang', $poll['language']);
        $CI->view->assign('cur_lang', $language);
        $CI->view->assign('max_results', $max_results);
        $CI->view->assign('max_answers', $max_answers);

        return $CI->view->fetch('poll_results', 'user', 'polls');
    }
}

if (!function_exists('show_poll_results_block')) {

    /**
     * Displays polls results (progressbars)
     *
     * @param type $id_poll
     *
     * @return boolean
     */
    function show_poll_results_block($id_poll)
    {
        $CI = &get_instance();

        if (is_null($id_poll)) {
            return false;
        }
        $poll = $CI->Polls_model->get_poll_by_id($id_poll);

        $poll['show_results'] = true;

        $max_answers = $CI->pg_module->get_module_config('polls', 'max_answers');
        if (!$max_answers) {
            $max_answers = 10;
        }
        $max_results = 0;

        // Results sorting
        for ($i = 1; $i <= $max_answers; ++$i) {
            if (isset($poll['results'][$i])) {
                $max_results = $max_results + floor($poll['results'][$i]);
            }
        }

        $CI->view->assign('poll_data', $poll);
        $CI->view->assign('max_results', $max_results);
        $CI->view->assign('max_answers', $max_answers);
        $poll_block = $CI->view->fetch('poll_results', null, 'polls');

        $CI->view->assign('poll_block', $poll_block);

        return $CI->view->fetch('poll_place_block', null, 'polls');
    }
}

if (!function_exists('admin_home_polls_block')) {
    function admin_home_polls_block()
    {
        $CI = &get_instance();

        $auth_type = $CI->session->userdata("auth_type");
        if ($auth_type != "admin") {
            return '';
        }

        $user_type = $CI->session->userdata("user_type");

        $show = true;

        if ($user_type == 'moderator') {
            $show    = false;
            $CI->load->model('Moderators_model');
            $methods = $CI->Moderators_model->get_module_methods('polls');
            if (is_array($methods) && !in_array('index', $methods)) {
                $show = true;
            } else {
                $permission_data = $CI->session->userdata("permission_data");
                if (isset($permission_data['polls']['index']) && $permission_data['polls']['index'] == 1) {
                    $show = true;
                }
            }
        }

        if (!$show) {
            return '';
        }

        $CI->load->model('Polls_model');

        $stat_polls["all"]     = $CI->Polls_model->get_polls_count(array());
        $params["where_sql"][] = "( ( use_expiration = 0 OR (use_expiration = 1 AND date_end >= '" . date('Y-m-d H:i:s') . "') )  AND date_start < '" . date('Y-m-d H:i:s') . "' )";
        $stat_polls["active"]  = $CI->Polls_model->get_polls_count($params);

        $CI->view->assign("stat_polls", $stat_polls);

        return $CI->view->fetch('helper_admin_home_block', null, 'polls');
    }
}
