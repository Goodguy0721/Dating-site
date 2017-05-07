<?php

namespace Pg\Modules\Subscriptions\Controllers;

use Pg\Libraries\View;

/**
 * Subscriptions admin side controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Katya Kashkova <katya@pilotgroup.net>
 *
 * @version $Revision: 2 $ $Date: 2010-04-02 15:07:07 +0300 (Ср, 02 апр 2010) $ $Author: kkashkova $
 **/
class Admin_Subscriptions extends \Controller
{
    /**
     * link to CodeIgniter object
     *
     * @var object
     */
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Menu_model');
        $this->Menu_model->set_menu_active_item('admin_menu', 'content_items');
    }

    public function index($order = "subscribe_type", $order_direction = "DESC", $page = 1)
    {
        $this->load->model('Subscriptions_model');
        $attrs = array();
        $current_settings = isset($_SESSION["subscriptions_list"]) ? $_SESSION["subscriptions_list"] : array();
        if (!isset($current_settings["order"])) {
            $current_settings["order"] = "subscribe_type";
        }
        if (!isset($current_settings["order_direction"])) {
            $current_settings["order_direction"] = "DESC";
        }
        if (!isset($current_settings["page"])) {
            $current_settings["page"] = 1;
        }

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

        $subscriptions_count = $this->Subscriptions_model->get_subscriptions_count();

        if (!$page) {
            $page = $current_settings["page"];
        }
        $items_on_page = $this->pg_module->get_module_config('start', 'admin_items_per_page');
        $this->load->helper('sort_order');
        $page = get_exists_page_number($page, $subscriptions_count, $items_on_page);
        $current_settings["page"] = $page;

        $_SESSION["subscriptions_list"] = $current_settings;

        $sort_links = array(
            "subscribe_type" => site_url() . "admin/subscriptions/index/subscribe_type/" . (($order != 'subscribe_type' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
        );

        $this->view->assign('sort_links', $sort_links);
        if ($subscriptions_count > 0) {
            $subscriptions = $this->Subscriptions_model->get_subscriptions_list($page, $items_on_page, array($order => $order_direction), $attrs);
            $this->view->assign('subscriptions', $subscriptions);
        }

        $this->load->helper("navigation");
        $url = site_url() . "admin/subscriptions/index/" . $order . "/" . $order_direction . "/";
        $page_data = get_admin_pages_data($url, $subscriptions_count, $items_on_page, $page, 'briefPage');
        $page_data["date_format"] = $this->pg_date->get_format('date_time_literal', 'st');
        $this->view->assign('page_data', $page_data);

        $this->view->setHeader(l('admin_header_subscriptions_list', 'subscriptions'));
        $this->view->render('list');
    }

    public function edit($id = null)
    {
        $this->load->model('Subscriptions_model');
        $data = ($id) ? $this->Subscriptions_model->get_subscription_by_id($id) : array();
        if ($this->input->post('btn_save')) {
            $post_data = array(
                "id_template"     => $this->input->post('id_template', true),
                "subscribe_type"  => $this->input->post('subscribe_type', true),
                "id_content_type" => $this->input->post('id_content_type', true),
                "scheduler_type"  => $this->input->post('scheduler_type', true),
            );
            if ($post_data['scheduler_type'] == 2) {
                $post_data['scheduler_date'] = $this->input->post('scheduler_date2', true);
                $post_data['scheduler_hours'] = $this->input->post('scheduler_hours2', true);
                $post_data['scheduler_minutes'] = $this->input->post('scheduler_minutes2', true);
            }
            if ($post_data['scheduler_type'] == 3) {
                $post_data['scheduler_date'] = $this->input->post('scheduler_date3', true);
                $post_data['scheduler_hours'] = $this->input->post('scheduler_hours3', true);
                $post_data['scheduler_minutes'] = $this->input->post('scheduler_minutes3', true);
                $post_data['scheduler_period'] = $this->input->post('scheduler_period', true);
            }
            $langs_data = $this->input->post('langs', true);

            $validate_data = $this->Subscriptions_model->validate_subscription($id, $post_data, $langs_data);

            if (!empty($validate_data["errors"])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data["errors"]);
            } else {
                $this->Subscriptions_model->save_subscription($id, $validate_data["data"], $validate_data["langs"]);
                $this->system_messages->addMessage(View::MSG_ERROR, ($id) ? l('success_update_subscription', 'subscriptions') : l('success_add_subscription', 'subscriptions'));
                $current_settings = $_SESSION["subscriptions_list"];
                $url = site_url() . "admin/subscriptions/index/" . $current_settings["order"] . "/" . $current_settings["order_direction"] . "/" . $current_settings["page"] . "";
                redirect($url);
            }
            $data = array_merge($data, $validate_data["data"]);
            $this->view->assign('validate_lang', $validate_data["langs"]);
        }

        if (!empty($data)) {
            $data = $this->Subscriptions_model->format_subscription($data, true);
        }
        $this->view->assign('data', $data);

        ///// Templates
        $this->load->model('notifications/models/Templates_model');
        $this->view->assign('templates', $this->Templates_model->get_templates_list());

        /// Content types
        $this->load->model('subscriptions/models/Subscriptions_types_model');
        $this->view->assign('content_types', $this->Subscriptions_types_model->get_subscriptions_types_list());

        //time
        $this->view->assign('hours', range(0, 24, 1));
        $this->view->assign('minutes', range(0, 50, 10));

        ///// languages
        $this->view->assign('languages', $this->pg_language->languages);
        $this->view->assign('languages_count', count($this->pg_language->languages));
        $this->view->assign('cur_lang', $this->pg_language->current_lang_id);

        $this->view->setHeader(l('admin_header_subscription_edit', 'subscriptions'));
        $this->view->render('edit_form');
    }

    public function delete($id)
    {
        if (!empty($id)) {
            $this->load->model('Subscriptions_model');
            $this->Subscriptions_model->delete_subscription($id);

            if ($id) {
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_delete_subscription', 'subscriptions'));
            } else {
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_delete_subscription', 'subscriptions'));
            }
        }
        $current_settings = $_SESSION["subscriptions_list"];
        $url = site_url() . "admin/subscriptions/index/" . $current_settings["order"] . "/" . $current_settings["order_direction"] . "/" . $current_settings["page"] . "";
        redirect($url);
    }

    public function send_subscription($id, $page = 1, $limit = 1000)
    {
        if (!$id) {
            return;
        }
        $this->load->model('Subscriptions_model');

        return $this->Subscriptions_model->send_subscription($id, $page, $limit);
    }

    public function ajax_send_subscription($id, $page = 1, $limit = 1000)
    {
        $result = $this->send_subscription($id, $page, $limit);
        exit(json_encode($result));
    }

    public function ajax_start_subscribe($id)
    {
        if (!$id) {
            return;
        }
        $this->load->model('subscriptions/models/Subscriptions_users_model');
        $this->view->assign('total_users', $this->Subscriptions_users_model->get_subscription_users_count($id));
        $this->view->assign('id_subscription', $id);
        $this->view->render('start_subscribe');
    }
}
