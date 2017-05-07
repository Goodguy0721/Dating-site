<?php

namespace Pg\Modules\Nearest_users\Controllers;

use Pg\Librarties\View;

/**
 * Nearest users module
 *
 * @package 	PG_Dating
 *
 * @copyright 	Copyright (c) 2000-2015 PG_Dating - php dating software
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */

/**
 * Nearest users user side controller
 *
 * @package 	PG_Dating
 * @subpackage 	application
 *
 * @category	modules
 *
 * @copyright 	Pilot Group <http://www.pilotgroup.net/>
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
class Nearest_users extends \Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Nearest_users_model');
        $this->load->model('Menu_model');
        if ('user' === $this->session->userdata('auth_type')) {
            $this->_user_id = intval($this->session->userdata('user_id'));
        }
    }

    /**
     * Nearest users page
     *
     *
     * @return void
     */
    public function index($order = 'default', $order_direction = 'DESC', $page = 1)
    {
        if ($this->input->post('search_nearest_btn')) {
            $circle['center_lat'] = $this->input->post('circle_center_lat', true);
            $circle['center_lon'] = $this->input->post('circle_center_lon', true);
            $circle['search_radius'] = $this->input->post('circle_radius', true);
            $data = $this->Nearest_users_model->getSearchData($circle);
        } else {
            $data = $this->session->userdata('nearest_users_search');
        }
        $hide_map = $this->session->userdata('hide_map');
        $this->view->assign('hide_map', $hide_map);

        $this->view->assign('block',  $this->searchListBlock($data, $order, $order_direction, $page));

        $this->Menu_model->breadcrumbs_set_parent('nearest_users_item');
        $this->view->render('form');
    }

    /**
     * Search users
     *
     * @var array
     * @var string
     * @var string
     * @var int
     *
     * @return void
     */
    private function searchListBlock($data = array(), $order = 'default', $order_direction = 'DESC', $page = 1)
    {
        $current_settings = $data ? $data : $this->Nearest_users_model->getSearchData();
        $this->session->set_userdata('nearest_users_search', $current_settings);

        $criteria = $this->Nearest_users_model->getSearchCriteria($current_settings);

        $search_url = site_url() . "nearest_users/index";
        $url = site_url() . "nearest_users/index/" . $order . "/" . $order_direction . "/";

        $order = trim(strip_tags($order));
        if (!$order) {
            $order = 'date_created';
        }

        $this->view->assign('order', $order);

        $order_direction = strtoupper(trim(strip_tags($order_direction)));
        if ($order_direction != 'DESC') {
            $order_direction = 'ASC';
        }

        $this->view->assign('order_direction', $order_direction);

        $items_count = $this->Users_model->get_users_count($criteria);

        $sort_data = array(
            'url'       => $search_url,
            'order'     => $order,
            'direction' => $order_direction,
            'links'     => array(
                'default'      => l('field_default_sorter', 'users'),
                'name'         => l('field_name', 'users'),
                'views_count'  => l('field_views_count', 'users'),
                'date_created' => l('field_date_created', 'users'),
            ),
        );

        $this->view->assign('sort_data', $sort_data);

        $users = array();
        $items_on_page = $this->pg_module->get_module_config('users', 'items_per_page');

        if ($items_count > 0) {
            $order_array = array();
            if ($order == 'default') {
                $order_array["up_in_search_end_date"] = 'DESC';
                $order_array["date_created"] = $order_direction;
            } else {
                if ($order == 'name') {
                    if ($this->pg_module->get_module_config('users', 'hide_user_names')) {
                        $order_array['nickname'] = $order_direction;
                    } else {
                        $order_array['fname'] = $order_direction;
                        $order_array['name'] = $order_direction;
                    }
                } else {
                    $order_array[$order] = $order_direction;
                }
            }

            if (!$page) {
                $page = 1;
            }
            $this->load->helper('sort_order');
            $page = get_exists_page_number($page, $items_count, $items_on_page);
            $lang_id = $this->pg_language->current_lang_id;
            $users = $this->Users_model->get_users_list($page, $items_on_page, $order_array, $criteria, array(), true, false, $lang_id);
            $this->view->assign('users', $users);
        }

        $this->showMap($users, $data);

        $this->load->helper('navigation');
        $page_data = get_user_pages_data($url, $items_count, $items_on_page, $page, 'briefPage');
        $page_data['date_format'] = $this->pg_date->get_format('date_literal', 'st');
        $page_data['date_time_format'] = $this->pg_date->get_format('date_time_literal', 'st');
        $page_data['view_type'] = (!empty($_SESSION['search_view_mode']) && $_SESSION['search_view_mode'] == 'list') ? 'list' : 'gallery';

        $this->view->assign('page_data', $page_data);

        return $this->view->fetch('users_list_block', 'user', 'users');
    }

    public function ajaxSearch($order = "default", $order_direction = "DESC", $page = 1)
    {
        $result = array('content' => '');

        $circle = array();
        $circle['center_lat'] = $this->input->post('circle_center_lat', true);
        $circle['center_lon'] = $this->input->post('circle_center_lon', true);
        $circle['search_radius'] = $this->input->post('circle_radius', true);

        if ($circle['center_lat'] && $circle['center_lon'] && $circle['search_radius']) {
            $data = $this->Nearest_users_model->getSearchData($circle);
        } else {
            $data = $this->session->userdata('nearest_users_search');
        }

        $result['content'] = $this->searchListBlock($data, $order, $order_direction, $page);
        $this->view->assign($result);
        $this->view->render();
    }

    /**
     * Show map
     *
     * @var array
     *
     * @return void
     */
    public function showMap($users = array(), $data = array())
    {
        $markers = $this->Nearest_users_model->getUsersMarkers($users);
        $settings = $this->Nearest_users_model->getCircleSettings();
        $params['settings']['radius_listener'] = 'radiusListener';
        $params['settings']['position_listener'] = 'positionListener';

        $this->view->assign('data', $settings);

        $lang_id = $this->session->userdata("lang_id");
        $units_lang = $this->pg_language->ds->get_reference('nearest_users', 'distance_units', $lang_id);

        $langs['search'] = l('search_people', 'start');
        $langs['radius'] = l('circle_radius', 'nearest_users');
        $langs['unit'] = $units_lang['option'][$settings['search_radius_unit']];

        $this->load->helper('geomap');
        $params['markers'] = $markers;
        $params['map_id'] = 'nearest_users_map_container';
        $params['settings']['circle_settings'] = $settings;
        $params['settings']['langs'] = $langs;
        $params['height'] = 400;
        $params['width'] = 'auto';
        $params['id_user'] = 0;
        $params['object_id'] = 0;
        if ($this->is_pjax == 1 && $data != array()) {
            //$params['only_load_content'] = 1;
        }
        $params['gid'] = 'nearest_view';

        $this->view->assign('map', show_default_map($params));
    }

    /**
     * Ajax hide map
     *
     * @var int
     *
     * @return void
     */
    public function ajaxSaveMapView($hide_map = 1)
    {
        if ($hide_map == 1) {
            $this->session->set_userdata("hide_map", 1);
        } elseif ($hide_map == 0) {
            $this->session->set_userdata("hide_map", 0);
        }
    }

    public function set_view_mode($view_mode)
    {
        if (in_array($view_mode, array('list', 'gallery'))) {
            $_SESSION['search_view_mode'] = $view_mode;
        }
    }
}
