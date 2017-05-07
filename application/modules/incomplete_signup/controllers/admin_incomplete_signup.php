<?php

namespace Pg\Modules\Incomplete_signup\Controllers;

use Pg\Libraries\View;

/**
 * Incomplete_signup admin side controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 **/
class Admin_Incomplete_signup extends \Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Menu_model');
        $this->Menu_model->set_menu_active_item('admin_menu', 'add_ons_items');
    }

    /**
     * Main page
     */
    public function index()
    {
        return $this->settings();
    }

    /**
     * Setting module
     *
     * @return template
     */
    public function settings()
    {
        $this->view->setHeader(l('admin_header_incomplete_signup_settings', 'incomplete_signup'));

        $data = array(
            'show_delay'        => $this->pg_module->get_module_config('incomplete_signup', 'show_delay'),
            'timeout_send_data' => $this->pg_module->get_module_config('incomplete_signup', 'timeout_send_data'),
        );

        if ($this->input->post('btn_save')) {
            $data['show_delay'] = $this->input->post('show_delay', true);
            $data['timeout_send_data'] = $this->input->post('timeout_send_data', true);

            foreach ($data as $setting => $value) {
                $this->pg_module->set_module_config('incomplete_signup', $setting, (int) $value);
            }

            $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_update', 'incomplete_signup'));
        }

        $this->view->setHeader(l('admin_header_incomplete_signup_settings', 'incomplete_signup'));

        $this->view->assign('settings_data', $data);
        $this->view->render('settings');
    }

    public function not_registered($filter = "not_registered", $user_type = 'all', $order = "date_created", $order_direction = "DESC", $page = 1)
    {
        $this->load->model('Users_model');
        $this->load->model('Incomplete_signup_model');

        $attrs = $search_param = $search_params = array();
        $current_settings = isset($_SESSION["users_unregistered_list"]) ? $_SESSION["users_unregistered_list"] : array();

        if (!isset($current_settings["filter"])) {
            $current_settings["filter"] = $filter;
        }
        if (!isset($current_settings["order"])) {
            $current_settings["order"] = $order;
        }
        if (!isset($current_settings["order_direction"])) {
            $current_settings["order_direction"] = $order_direction;
        }
        if (!isset($current_settings["page"])) {
            $current_settings["page"] = $page;
        }

        if ($this->input->post('btn_search', true)) {
            $user_type = $this->input->post('user_type', true);
            $current_settings["search_text"] = $this->input->post('val_text', true);
            $current_settings["search_type"] = $this->input->post('type_text', true);
        }

        $current_settings["user_type"] = $user_type;

        if (isset($current_settings["search_text"])) {
            $search_text_escape = $this->db->escape("%" . $current_settings["search_text"] . "%");
            if ($current_settings["search_type"] != 'all') {
                $attrs["where_sql"][] = $search_params["where_sql"][] = $current_settings["search_type"] . " LIKE " . $search_text_escape;
            } else {
                $attrs["where_sql"][] = $search_params["where_sql"][] = "(nickname LIKE " . $search_text_escape . " OR fname LIKE " . $search_text_escape . " OR sname LIKE " . $search_text_escape . " OR email LIKE " . $search_text_escape . ")";
            }
        }

        $search_param = array(
                        'text' => isset($current_settings["search_text"]) ? $current_settings["search_text"] : '',
                        'type' => isset($current_settings["search_type"]) ? $current_settings["search_type"] : '',
                );

        $this->load->model("users/models/Users_deleted_model");
        $filter_data["all"] = $this->Users_model->get_users_count();
        $search_attrs["where"]["approved"] = 0;
        $filter_data["not_active"] = $this->Users_model->get_users_count($search_attrs);
        $search_attrs["where"]["approved"] = 1;
        $filter_data["active"] = $this->Users_model->get_users_count($search_attrs);
        $search_attrs["where"]["confirm"] = 0;
        $filter_data["not_confirm"] = $this->Users_model->get_users_count($search_attrs);
        $filter_data["deleted"] = $this->Users_deleted_model->get_users_count($search_params);

        if ($user_type != 'all' && $user_type) {
            $attrs["where"]["user_type"] = $search_params["where"]["user_type"] = $user_type;
        }

        $delay = $this->pg_module->get_module_config('incomplete_signup', 'show_delay');
        $time = date("Y-m-d H:i:s", time() - $delay * 60);
        $attrs["where"]['date_created <'] = $search_params["where"]['date_created <'] = $time;

        $filter_data["not_registered"] = $this->Incomplete_signup_model->get_users_count($search_params);

        $this->view->assign('user_type', $user_type);
        $this->view->assign('search_param', $search_param);
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

        $this->load->model('Properties_model');
        $user_types = $this->Properties_model->get_property('user_type');
        $this->view->assign('user_types', $user_types);

        $users_count = $filter_data[$filter];

        if (!$page) {
            $page = $current_settings["page"];
        }
        $items_on_page = $this->pg_module->get_module_config('start', 'admin_items_per_page');
        $this->load->helper('sort_order');
        $page = get_exists_page_number($page, $users_count, $items_on_page);
        $current_settings["page"] = $page;

        $_SESSION["users_unregistered_list"] = $current_settings;

        $sort_links = array(
            "nickname"     => site_url() . "admin/incomplete_signup/not_registered/{$filter}/{$user_type}/nickname/" . (($order != 'nickname' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
            "email"        => site_url() . "admin/incomplete_signup/not_registered/{$filter}/{$user_type}/email/" . (($order != 'email' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
            "date_created" => site_url() . "admin/incomplete_signup/not_registered/{$filter}/{$user_type}/date_created/" . (($order != 'date_created' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
        );

        $this->view->assign('sort_links', $sort_links);

        if ($users_count > 0) {
            $results = $this->Incomplete_signup_model->get_unregistered_users($page, $items_on_page, array($order => $order_direction), $attrs);
            $users = $this->Users_model->format_users($results, false);

            foreach ($users as $key => $user) {
                $users[$key]['language'] = $this->pg_language->get_lang_code_by_id($user['lang_id']);
            }

            $this->view->assign('users', $users);
        }

        $this->load->helper("navigation");
        $url = site_url() . "admin/incomplete_signup/not_registered/{$filter}/{$user_type}/{$order}/{$order_direction}/";
        $page_data = get_admin_pages_data($url, $users_count, $items_on_page, $page, 'briefPage');
        $page_data["date_format"] = $this->pg_date->get_format('date_time_literal', 'st');
        $this->view->assign('page_data', $page_data);

        $this->view->setHeader(l('admin_header_users_list', 'users'));
        $this->Menu_model->set_menu_active_item('admin_menu', 'users_menu_item');
        $this->view->render('not_registered_list');
    }

    public function send_notification($user_id)
    {
        $this->load->model('Incomplete_signup_model');
        $this->load->model("notifications/models/Templates_model");

        $user_data = $this->Incomplete_signup_model->get_unregistered_user_by_id($user_id);
        $template_content = $this->Templates_model->compile_template('users_incomplete_signup', $user_data, $user_data['lang_id']);

        if ($this->input->post('btn_save')) {
            $post_data = array(
                                "email"   => $this->input->post('email', true),
                                "subject" => $this->input->post('subject', true),
                                "content" => $this->input->post('content', true),
                        );

            $template_data = $this->Templates_model->get_template_by_id($template_content['id_template']);

            $this->load->model('notifications/models/Sender_model');
            $this->Sender_model->send_letter($post_data["email"], $post_data["subject"], $post_data["content"], $template_data["content_type"]);

            $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_message_send', 'mailbox'));

            redirect(site_url() . "admin/incomplete_signup/not_registered");
        }

        $header_text = l('admin_header_notification', 'incomplete_signup');
        $this->view->setHeader($header_text);

        $this->view->assign('id_user', $user_id);
        $this->view->assign('email', $user_data['email']);
        $this->view->assign('template', $template_content);
        $this->Menu_model->set_menu_active_item('admin_menu', 'users_menu_item');

        $this->view->render('send_notification');
    }

    public function delete_select($user_id)
    {
        $this->load->model('Incomplete_signup_model');
        $user_data = $this->Incomplete_signup_model->delete_unregistered_user_by_id($user_id);

        $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_delete_user', 'users'));

        redirect(site_url() . "admin/incomplete_signup/not_registered");
    }
}
