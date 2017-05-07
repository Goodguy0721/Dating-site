<?php

/**
 * Banners module
 *
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
/**
 * Banners management
 *
 * @subpackage 	Banners
 *
 * @category	helpers
 *
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
if (!function_exists('banner_initialize')) {

    /**
     * Return banners initialization code
     *
     * @return string
     */
    function banner_initialize()
    {
        $ci = &get_instance();
        $banner_html = $ci->view->fetch('show_banner_setup', 'user', 'banners');

        return $banner_html;
    }

}

if (!function_exists('show_banner_place')) {

    /**
     * Return banners for place
     *
     * @param integer $place_id place identifier
     *
     * @return string
     */
    function show_banner_place($place_id)
    {
        $ci = &get_instance();
        
        if ($ci->session->userdata('auth_type') != 'user') {            
            return;
        }

        if (func_num_args() == 1 && is_array($place_id)) {
            $params = $place_id;
            $place_id = isset($params['place_id']) ? $params['place_id'] : 0;
        }

        $ci->load->model('banners/models/Banner_place_model');
        if (!is_numeric($place_id)) {
            $place = $ci->Banner_place_model->get_by_keyword($place_id);
            $place_id = $place['id'];
        } else {
            $place_id = (is_numeric($place_id) and $place_id > 0) ? intval($place_id) : 0;
            $place = $ci->Banner_place_model->get($place_id);
        }
        if (!is_array($place) or !$place) {
            return;
        }
        $place['places_in_rotation'] = intval($place['places_in_rotation']);

        $ci->uri->_fetch_uri_string();
        $uri = $ci->uri->ruri_string();
        $uri = trim(substr($uri, 1));

        if (empty($uri) || count(explode('/', $uri)) <= 3) {
            $uri = $ci->router->fetch_class(true) . '/' . $ci->router->fetch_method();
        }

        if (SOCIAL_MODE) {
            if ($place['keyword'] == 'bottom-banner' && trim($uri, '/') == 'start/index') {
                return '';
            }
        }
        
        $ci->load->model('banners/models/Banner_group_model');
        $group_id = $ci->Banner_group_model->get_group_id_by_page_link($uri);
        if (!$group_id) {
            $group_ids = $ci->Banner_group_model->search_groups_id_by_page_link($uri);
        } else {
            $group_ids[] = $group_id;
        }

        $ci->load->model('Banners_model');
        $banners = $ci->Banners_model->show_rotation_banners($group_ids, $place_id, $place['places_in_rotation']);

        // don't show banner place without banners
        if (empty($banners)) {
            return;
        }

        $ci->view->assign('place', $place);
        $ci->view->assign('banners', $banners);

        // show template from banners module default user theme
        $banner_html = $ci->view->fetch('show_banner_place', 'user', 'banners');

        return $banner_html;
    }

}

if (!function_exists('admin_home_banners_block')) {

    /**
     * Return banners information block for admin homepage
     *
     * @return string
     */
    function admin_home_banners_block()
    {
        $ci = &get_instance();

        $auth_type = $ci->session->userdata("auth_type");
        if ($auth_type != "admin") {
            return '';
        }

        $user_type = $ci->session->userdata("user_type");

        $show = true;

        if ($user_type == 'moderator') {
            $show = false;
            $ci->load->model('Moderators_model');
            $methods = $ci->Moderators_model->get_module_methods('banners');
            if (is_array($methods) && !in_array('index', $methods)) {
                $show = true;
            } else {
                $permission_data = $ci->session->userdata("permission_data");
                if (isset($permission_data['banners']['index']) && $permission_data['banners']['index'] == 1) {
                    $show = true;
                }
            }
        }

        if (!$show) {
            return '';
        }

        $ci->load->model('Banners_model');
        $stat_banners['users'] = $ci->Banners_model->cnt_banners(array("where" => array('user_id !=' => 0, "approve" => 0)));

        $ci->view->assign("stat_banners", $stat_banners);

        return $ci->view->fetch('helper_admin_home_block', 'admin', 'banners');
    }

}

if (!function_exists('my_banners')) {
    function my_banners($params)
    {
        $CI = &get_instance();

        $auth_type = $CI->session->userdata("auth_type");
        if ($auth_type != "user") {
            return '';
        }

        $CI->load->model('Banners_model');

        if (isset($params['page'])) {
            $page = intval($params['page']);
        }

        $page = max($page, 1);

        $params["where"]["user_id"] = $CI->session->userdata("user_id");
        $cnt_banners = $CI->Banners_model->cnt_banners($params);

        $items_on_page = $CI->pg_module->get_module_config('banners', 'items_per_page');
        $CI->load->helper('sort_order');
        $page = get_exists_page_number($page, $cnt_banners, $items_on_page);

        $banners = $CI->Banners_model->get_banners($page, $items_on_page, array("id" => "DESC"), $params);
        // get place objects for banner
        if ($banners) {
            $CI->load->model('banners/models/Banner_place_model');
            foreach ($banners as $key => $banner) {
                $banners[$key]['banner_place_obj'] = $banner['banner_place_id'] ? $CI->Banner_place_model->get($banner['banner_place_id']) : null;
            }
        }
        $CI->view->assign('banners', $banners);

        $CI->load->helper("navigation");
        $page_data = get_user_pages_data(site_url() . "users/account/banners/", $cnt_banners, $items_on_page, $page, 'briefPage');
        $page_data['date_format'] = $CI->pg_date->get_format('date_literal', 'st');
        $CI->view->assign('page_data', $page_data);

        $CI->Menu_model->breadcrumbs_set_parent('my_banners_item');

        $CI->view->render('my_list_block', 'user', 'banners');
    }
}
