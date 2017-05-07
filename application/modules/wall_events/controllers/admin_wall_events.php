<?php

namespace Pg\Modules\Wall_events\Controllers;

use Pg\Libraries\View;

/**
 * Admin Wall events controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * */
class Admin_Wall_events extends \Controller
{
    private $other_settings = array(
        'wall_events' => array(
            array('var' => 'live_period', 'type' => 'text'),
            array('var' => 'events_max_count', 'type' => 'text'),
        ),
    );

    public function __construct()
    {
        parent::__construct();
        $this->load->model('wall_events/models/Wall_events_types_model');
        $this->load->model('Menu_model');
        $this->Menu_model->set_menu_active_item('admin_menu', 'add_ons_items');
    }

    public function index($page = 1)
    {
        $items_on_page = $this->pg_module->get_module_config('start', 'admin_items_per_page');
        $this->load->helper('sort_order');
        $wall_events_types_cnt = $this->Wall_events_types_model->get_wall_events_types_cnt();
        $page = get_exists_page_number($page, $wall_events_types_cnt, $items_on_page);

        $this->load->helper("navigation");
        $url = site_url() . "admin/wall_events/index/";
        $page_data = get_admin_pages_data($url, $wall_events_types_cnt, $items_on_page, $page, 'briefPage');
        $this->view->assign('page_data', $page_data);

        $wall_events_types = $this->Wall_events_types_model->get_wall_events_types(array(), $page, $items_on_page);
        $this->view->assign('wall_events_types', $wall_events_types);

        $_SESSION["wall_events_types"]["page"] = $page;

        $this->Menu_model->set_menu_active_item('admin_wall_events_menu', 'wall_events_list_item');
        $this->view->setHeader(l('admin_header_list', 'wall_events'));
        $this->view->render('wall_events_types');
    }

    public function ajax_activate_type()
    {
        $gid = $this->input->post('gid', true);
        $status = $this->input->post('status', true) ? '1' : '0';
        $this->Wall_events_types_model->save_wall_events_type($gid, array('status' => $status));
        $return['status'] = $status;
        $this->view->assign($return);
    }

    public function edit_type($gid)
    {
        if (empty($gid)) {
            show_404();
        }

        $wall_events_type = $this->Wall_events_types_model->get_wall_events_type($gid);
        if (empty($wall_events_type)) {
            show_404();
        }

        $data['page'] = isset($_SESSION['wall_events_types']['page']) && $_SESSION['wall_events_types']['page'] ? $_SESSION['wall_events_types']['page'] : 1;

        if ($this->input->post('btn_save')) {
            $post_data = array(
                'status'   => $this->input->post('status', true),
                'settings' => array(
                    'join_period' => $this->input->post('join_period', true),
                ),
            );

            $validate_data = $this->Wall_events_types_model->validate($gid, $post_data);
            if (!empty($validate_data['errors'])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data['errors']);
            } else {
                $result = $this->Wall_events_types_model->save_wall_events_type($gid, $validate_data['data']);
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_update_wall_events_data', 'wall_events'));
                redirect(site_url() . "admin/wall_events/index/{$page}");
            }

            $wall_events_type = array_merge($wall_events_type, $post_data);
        }

        $this->view->assign('wall_events_type', $wall_events_type);

        $this->view->setHeader(l('admin_header_wall_events_type_change', 'wall_events') . ': ' . l('wetype_' . $wall_events_type['gid'], 'wall_events'));
        $data['action'] = site_url() . "admin/wall_events/edit_type/{$gid}";
        $this->view->assign('data', $data);
        $this->view->render('form_wall_events_type');
    }

    public function save_type($gid)
    {
        $page = isset($_SESSION['wall_events_types']['page']) && $_SESSION['wall_events_types']['page'] ? $_SESSION['wall_events_types']['page'] : 1;

        if (!$gid) {
            redirect(site_url() . "admin/wall_events/index/{$page}");
        }

        $attrs['status'] = $this->input->post('status', true);
        $attrs['settings']['join_period'] = $this->input->post('join_period', true);

        $validate_data = $this->Wall_events_types_model->validate($gid, $attr);
        if (!empty($validate_data['errors'])) {
            $this->system_messages->addMessage(View::MSG_ERROR, $validate_data['errors']);
            redirect(site_url() . "admin/wall_events/edit_type/{$gid}");
        } else {
            $result = $this->Wall_events_types_model->save_wall_events_type($gid, $validate_data['data']);
            $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_update_wall_events_data', 'wall_events'));
            redirect(site_url() . "admin/wall_events/index/{$page}");
        }
    }

    /**
     * Method manage settings wall events
     * */
    public function settings()
    {
        $this->load->model('Menu_model');
        $this->Menu_model->set_menu_active_item('admin_menu', 'add_ons_items');

        $data = array(
            'live_period'      => $this->pg_module->get_module_config('wall_events', 'live_period'),
            'events_max_count' => $this->pg_module->get_module_config('wall_events', 'events_max_count'),
        );

        if ($this->input->post('btn_save')) {
            $data['live_period'] = $this->input->post('live_period', true);
            $data['events_max_count'] = $this->input->post('events_max_count', true);

            $this->load->model('Wall_events_model');
            $validate_data = $this->Wall_events_model->validateSettings($data);
            if (!empty($validate_data['errors'])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data['errors']);
            } else {
                foreach ($validate_data['data'] as $setting => $value) {
                    $this->pg_module->set_module_config('wall_events', $setting, (int) $value);
                }
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_update_wall_events_data', 'wall_events'));
                redirect(site_url() . 'admin/wall_events/settings');
            }
        }
        $this->view->setHeader(l('admin_header_settings', 'wall_events'));

        $this->view->assign('settings_data', $data);
        $this->view->render('settings');
    }
}
