<?php

namespace Pg\Modules\Nearest_users\Controllers;

/**
 * Nearest users module
 *
 * @package 	PG_Dating
 *
 * @copyright 	Copyright (c) 2000-2015 PG_Dating - php dating software
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
use Pg\Libraries\View;

/**
 * Nearest users admin side controller
 *
 * @package 	PG_Dating
 * @subpackage 	Nearest users
 *
 * @category	controllers
 *
 * @copyright 	Copyright (c) 2000-2015 PG_Dating - php dating software
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
class Admin_Nearest_users extends \Controller
{
    /**
     * Constructor
     *
     * @return Admin_Nearest_users
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Nearest_users_model');
        $this->load->model('Menu_model');
        $this->Menu_model->set_menu_active_item('admin_menu', 'add_ons_items');
    }

    /**
     * Manage nearest users settings
     *
     *
     * @return void
     */
    public function index()
    {
        $data["action"] = site_url() . "admin/nearest_users/index";

        $this->view->setHeader(l('admin_header_nearest_users_settings', 'nearest_users'));

        if ($this->input->post('btn_save')) {
            $post_data['search_radius'] = $this->input->post('search_radius', true);
            $post_data['search_radius_unit'] = $this->input->post('search_radius_unit', true);
            $post_data['set_position_manual'] = $this->input->post('set_position_manual', true);

            $validate_data = $this->Nearest_users_model->validate($post_data);
            if (!empty($validate_data['errors'])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data['errors']);
            } else {
                if (!empty($validate_data['data'])) {
                    $this->Nearest_users_model->saveSettings($validate_data['data']);
                    $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_update_nearest_users', 'nearest_users'));

                    $url = site_url() . 'admin/nearest_users/';
                    redirect($url);
                }
            }
        }

        $lang_id = $this->session->userdata("lang_id");
        $distance_units = $this->pg_language->ds->get_reference('nearest_users', 'distance_units', $lang_id);
        $data = $this->Nearest_users_model->getSettings();

        $this->view->assign('distance_units', $distance_units);
        $this->view->assign('data', $data);

        $this->view->render('settings');
    }
}
