<?php

namespace Pg\Modules\Moderators\Controllers;

/**
 * Moderators module
 *
 * @package 	PG_Core
 *
 * @copyright 	Copyright (c) 2000-2014 PG Core
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
use Pg\Libraries\View;

/**
 * Moderators admin side controller
 *
 * @package 	PG_Core
 * @subpackage 	Moderators
 *
 * @category 	controllers
 *
 * @copyright 	Copyright (c) 2000-2014 PG Core
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
class Admin_Moderators extends \Controller
{

    /**
     * Constructor
     *
     * @return Admin_Ausers
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model("Moderators_model");
        $this->load->model('Menu_model');
        $this->Menu_model->set_menu_active_item('admin_menu', 'ausers_item');
    }

    /**
     * Get moderator list
     *
     * @param string  $order           sorting data
     * @param string  $order_direction sorting direction
     * @param integer $page            page of results
     *
     * @return void
     */
    public function index($order = null, $order_direction = null, $page = null)
    {
        $attrs = array();
        $current_settings = isset($_SESSION["ausers_list"]) ? $_SESSION["ausers_list"] : array();
        if (!isset($current_settings["order"])) {
            $current_settings["order"] = "nickname";
        }
        if (!isset($current_settings["order_direction"])) {
            $current_settings["order_direction"] = "ASC";
        }
        if (!isset($current_settings["page"])) {
            $current_settings["page"] = 1;
        }

        $filter_data = array(
            "all" => $this->Moderators_model->get_users_count(),
            "not_active" => $this->Moderators_model->get_users_count(array("where" => array("status" => 0))),
            "active" => $this->Moderators_model->get_users_count(array("where" => array("status" => 1))),
            "admin" => $this->Moderators_model->get_users_count(array("where" => array("user_type" => 'admin'))),
            "moderator" => $this->Moderators_model->get_users_count(array("where" => array("user_type" => 'moderator'))),
        );
        $filter = $attrs["where"]['user_type'] = $this->Moderators_model->user_type;

        $this->view->assign('filter', $filter);
        $this->view->assign('filter_data', $filter_data);
        $current_settings["page"] = $page;

        if (!$order) {
            $order = $current_settings["order"];
        }
        $this->view->assign('order', $order);
        $current_settings["order"] = $order;

        if (!$order_direction) {
            $order_direction = $current_settings["order_direction"];
        }
        $this->view->assign('order_direction', $order_direction);
        $current_settings["order_direction"] = $order_direction;

        $users_count = $filter_data[$filter];

        if (!$page) {
            $page = $current_settings["page"];
        }
        $items_on_page = $this->pg_module->get_module_config('start', 'admin_items_per_page');
        $this->load->helper('sort_order');
        $page = get_exists_page_number($page, $users_count, $items_on_page);
        $current_settings["page"] = $page;

        $_SESSION["ausers_list"] = $current_settings;

        $sort_links = array(
            "name" => site_url() . "admin/moderators/index/name/" . (($order != 'name' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
            "nickname" => site_url() . "admin/moderators/index/nickname/" . (($order != 'nickname' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
            "email" => site_url() . "admin/moderators/index/email/" . (($order != 'email' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
            "date_created" => site_url() . "admin/moderators/index/date_created/" . (($order != 'date_created' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
        );

        $this->view->assign('sort_links', $sort_links);

        if ($users_count > 0) {
            $users = $this->Moderators_model->get_users_list($page, $items_on_page, array($order => $order_direction),
                                                             $attrs);
            $this->view->assign('users', $users);
        }
        $this->load->helper("navigation");
        $url = site_url() . "admin/moderators/index";
        $page_data = get_admin_pages_data($url, $users_count, $items_on_page, $page, 'briefPage');
        $page_data["date_format"] = $this->pg_date->get_format('date_time_literal', 'st');
        $this->view->assign('page_data', $page_data);

        $this->view->setHeader(l('admin_header_moderators_list', 'moderators'));
        $this->view->render('list');
    }

    /**
     * Edit moderator data
     *
     * @param integer $user_id moderator identifier
     *
     * @return void
     */
    public function edit($user_id = null)
    {
        if ($user_id) {
            $data = $this->Moderators_model->get_user_by_id($user_id);
        } else {
            $data["lang_id"] = $this->pg_language->current_lang_id;
        }
        if ($this->input->post('btn_save')) {
            $post_data = array(
                "name" => $this->input->post('name', true),
                "nickname" => $this->input->post('nickname', true),
                "password" => $this->input->post('password', true),
                "repassword" => $this->input->post('repassword', true),
                "update_password" => intval($this->input->post('update_password')),
                "email" => $this->input->post('email', true),
                "description" => $this->input->post('description', true),
                "user_type" => $this->input->post('user_type', true),
                "permission_data" => $this->input->post('permission_data', true),
                "lang_id" => intval($this->input->post('lang_id')),
            );
            $validate_data = $this->Moderators_model->validate_user($user_id, $post_data);
            if (!empty($validate_data["errors"])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data["errors"]);
                $validate_data["data"]['permission_data'] = unserialize($validate_data["data"]['permission_data']);
                $data = array_merge($data, $validate_data["data"]);
            } else {
                $data = $validate_data["data"];

                $new_user_id = $this->Moderators_model->save_user($user_id, $data);

                if ($user_id) {
                    $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_update_user', 'moderators'));
                } else {
                    $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_add_user', 'moderators'));
                }

                $current_settings = $_SESSION["ausers_list"];
                $url = site_url() . "admin/moderators/edit/" . $new_user_id;
                redirect($url);
            }
        }
        $methods = $this->Moderators_model->get_methods();

        if (!empty($data["permission_data"])) {
            foreach ($methods as $module => $module_data) {
                if (!isset($module_data["main"]["method"])) {
                    continue;
                }

                $current_method = $module_data["main"]["method"];

                if (isset($data["permission_data"][$module][$current_method])) {
                    $methods[$module]["main"]["checked"] = 1;
                }

                if (!empty($module_data["methods"])) {
                    foreach ($module_data["methods"] as $key => $method) {
                        $current_method = $method["method"];
                        if (isset($data["permission_data"][$module][$current_method])) {
                            $methods[$module]["methods"][$key]["checked"] = 1;
                        }
                    }
                }
            }
        }

        $this->view->assign('methods', $methods);
        $this->view->assign('langs', $this->pg_language->languages);
        $this->view->assign('data', $data);

        $this->view->setHeader(l('admin_header_moderators_edit', 'moderators'));
        $this->view->render('edit_form');
    }

    /**
     * Remove moderator
     *
     * @param integer $user_id moderator identifier
     *
     * @return void
     */
    public function delete($user_id)
    {
        if (!empty($user_id)) {
            $this->Moderators_model->delete_user($user_id);
            $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_delete_user', 'moderators'));
        }
        $current_settings = $_SESSION["ausers_list"];
        $url = site_url() . "admin/moderators/index";
        redirect($url);
    }

    /**
     * Activate/de-activate moderator
     *
     * @param integer $user_id moderator identifier
     * @param integer $status  activity status
     *
     * @return void
     */
    public function activate($user_id, $status = 0)
    {
        if (!empty($user_id)) {
            $this->Moderators_model->activate_user($user_id, $status);
            if ($status) {
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_activate_user', 'moderators'));
            } else {
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_deactivate_user', 'moderators'));
            }
        }
        $current_settings = $_SESSION["ausers_list"];
        $url = site_url() . "admin/moderators/index";
        redirect($url);
    }

    /**
     * Log off from account
     *
     * @return void
     */
    public function logoff()
    {
        $this->session->sess_destroy();
        redirect();
    }

}
