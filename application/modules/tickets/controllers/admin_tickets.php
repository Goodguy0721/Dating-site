<?php

namespace Pg\Modules\Tickets\Controllers;

use Pg\Libraries\View;

/**
 * Tickets admin side controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Nikita Savanaev <nsavanev@pilotgroup.net>
 **/
class Admin_Tickets extends \Controller
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
        $this->load->model("Tickets_model");

        $this->load->model('Menu_model');
        $this->Menu_model->set_menu_active_item('admin_menu', 'tickets_menu_item');
    }

    public function index($is_new = 'all', $order = "date_created", $order_direction = "DESC", $page = 1, $object_ids = null)
    {
        $attrs = $search_params = array();
        $current_settings = isset($_SESSION["responder_list"]) ? $_SESSION["responder_list"] : array();
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
            $is_new = $this->input->post('is_new', true);
            $current_settings["search_type"] = $this->input->post('type_text', true);
            $current_settings["search_text"] = $this->input->post('val_text', true);
        }
        $current_settings["is_new"] = $is_new;
        if (!empty($current_settings["search_text"])) {
            if ($current_settings["search_type"] != 'all') {
                $params["where"][$current_settings["search_type"] . " LIKE"] = "%" . $current_settings["search_text"] . "%";
            } else {
                $search_text_escape = $this->db->escape("%" . $current_settings["search_text"] . "%");
                $params["where_sql"][] = "(nickname LIKE " . $search_text_escape . " OR fname LIKE " . $search_text_escape . " OR sname LIKE " . $search_text_escape . " OR email LIKE " . $search_text_escape . ")";
            }
            $this->load->model('Users_model');
            $user_ids = $this->Users_model->get_users_list(null, null, null, $params);
            foreach ($user_ids as $user_id) {
                $object_ids = explode(',', $user_id['id']);
            }
        }
        if ($is_new != 'all') {
            $status_messages = ($is_new === "is_not_new") ? 0 : 1;
            $attrs["where"]["is_new"] = $search_params["where"]["is_new"] = intval($status_messages);
        }

        $search_param = array(
                            'text' => !empty($current_settings["search_text"]) ? $current_settings["search_text"] : '',
                            'type' => !empty($current_settings["search_type"]) ? $current_settings["search_type"] : '',
                        );

        if ((!empty($current_settings["search_text"]) && $object_ids != null) || empty($current_settings["search_text"])) {
            $filter_data["all"] = $this->Tickets_model->get_responder_count($search_params, $object_ids);
            $search_params["where"]["is_new"] = 0;
            $filter_data["is_not_new"] = $this->Tickets_model->get_responder_count($search_params, $object_ids);
            $search_params["where"]["is_new"] = 1;
            $filter_data["is_new"] = $this->Tickets_model->get_responder_count($search_params, $object_ids);
        }

        $current_settings["is_new"] = $is_new;

        $this->view->assign('search_param', $search_param);
        $this->view->assign('is_new', $is_new);
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

        $responder_count = $filter_data[$is_new];

        if (!$page) {
            $page = $current_settings["page"];
        }
        $items_on_page = $this->pg_module->get_module_config('start', 'admin_items_per_page');
        $this->load->helper('sort_order');
        $page = get_exists_page_number($page, $responder_count, $items_on_page);
        $current_settings["page"] = $page;

        $_SESSION["responder_list"] = $current_settings;

        $sort_links = array(
            "nickname"     => site_url() . "admin/tickets/index/{$is_new}/nickname/" . (($order != 'nickname' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
            "date_created" => site_url() . "admin/tickets/index/{$is_new}/date_created/" . (($order != 'date_created' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
        );
        $this->view->assign('sort_links', $sort_links);

        if ($responder_count > 0) {
            $responder = $this->Tickets_model->get_responder_list($page, $items_on_page, array($order => $order_direction), $attrs, $object_ids);
            $this->view->assign('responder', $responder);
        }

        $this->load->helper("navigation");
        $url = site_url() . "admin/tickets/index/{$is_new}/{$order}/{$order_direction}/";
        $page_data = get_admin_pages_data($url, $responder_count, $items_on_page, $page, 'briefPage');
        $page_data["date_format"] = $this->pg_date->get_format('date_time_literal', 'st');
        $this->view->assign('page_data', $page_data);

        $this->Menu_model->set_menu_active_item('admin_tickets_menu', 'responder_list_item');
        $this->view->setHeader(l('admin_header_responder_list', 'tickets'));
        $this->view->render('contacts');
    }

    public function reasons()
    {
        $attrs = $search_params = array();
        $reasons_count = $this->Tickets_model->get_reason_count();

        if ($reasons_count > 0) {
            $reasons = $this->Tickets_model->get_reason_list();
            $this->view->assign('reasons', $reasons);
        }
        $this->load->helper("navigation");
        $url = site_url() . "admin/tickets/reasons";
        $page_data = get_admin_pages_data($url, $reasons_count, ($reasons_count ? $reasons_count : 10), 1, 'briefPage');
        $this->view->assign('page_data', $page_data);

        $this->Menu_model->set_menu_active_item('admin_tickets_menu', 'reasons_list_item');
        $this->view->setHeader(l('admin_header_reasons_list', 'tickets'));
        $this->view->render('list');
    }

    public function edit($id = null)
    {
        if ($id) {
            $data = $this->Tickets_model->get_reason_by_id($id);
        } else {
            $data = array();
        }

        if ($this->input->post('btn_save')) {
            $post_data = array(
                "mails" => $this->input->post('mails', true),
            );

            $name = $this->input->post('name', true);

            $validate_data = $this->Tickets_model->validate_reason($id, $post_data, $name);
            if (!empty($validate_data["errors"])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data["errors"]);
            } else {
                $flag_add = empty($id) ? true : false;
                $data["id"] = $id = $this->Tickets_model->save_reason($id, $validate_data["data"], $validate_data["langs"]);

                if (!$flag_add) {
                    $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_update_reason', 'tickets'));
                } else {
                    $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_add_reason', 'tickets'));
                }

                redirect(site_url() . "admin/tickets");
            }
            $data = array_merge($data, $validate_data["data"]);
            $this->view->assign('validate_lang', $validate_data["langs"]);
            $temp = $this->Tickets_model->format_reasons(array($data));
            $data = $temp[0];
        }

        $this->view->assign('data', $data);
        $this->view->assign('languages', $this->pg_language->languages);
        $this->view->assign('languages_count', count($this->pg_language->languages));
        $this->view->assign('cur_lang', $this->pg_language->current_lang_id);

        $this->Menu_model->set_menu_active_item('admin_tickets_menu', 'reasons_list_item');
        $this->view->setHeader(l('admin_header_reasons_list', 'tickets'));
        $this->view->render('edit');
    }

    public function delete($id)
    {
        if (!empty($id)) {
            $this->Tickets_model->delete_reason($id);
            $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_delete_reason', 'tickets'));
        }
        redirect(site_url() . "admin/tickets");
    }

    public function settings()
    {
        $data = $this->Tickets_model->get_settings();

        if ($this->input->post('btn_save')) {
            $post_data = array(
                "default_alert_email"           => $this->input->post('default_alert_email', true),
                "load_messages"                 => $this->input->post('load_messages', true),
                "status_personal_communication" => $this->input->post('status_personal_communication', true),
            );

            $validate_data = $this->Tickets_model->validate_settings($post_data);
            if (!empty($validate_data["errors"])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data["errors"]);
            } else {
                $this->Tickets_model->set_settings($validate_data["data"]);
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_settings_save', 'tickets'));
                redirect(site_url() . "admin/tickets/settings");
            }
        }

        $this->view->assign('data', $data);
        $this->Menu_model->set_menu_active_item('admin_tickets_menu', 'settings_list_item');
        $this->view->setHeader(l('admin_header_settings', 'tickets'));
        $this->view->render('settings');
    }

    public function answer($id_user = null, $page = 1, $order = "date_created", $order_direction = "DESC")
    {
        $id_user = $id_user ? intval($id_user) : intval($this->input->post('id_user'));
        $header_text = l('admin_header_responder_list', 'tickets');
        if ($id_user) {
            if ($this->input->post('btn_save')) {
                $post_data = array(
                    "message" => $this->input->post('message', true),
                    "id_user" => $id_user ,
                );
                $validate_data = $this->Tickets_model->validate_auth_contact_form($post_data);
                if (!empty($validate_data["errors"])) {
                    $this->system_messages->addMessage(View::MSG_ERROR, $validate_data["errors"]);
                } else {
                    $format_message = $this->Tickets_model->format_message($validate_data["data"], 1);
                    $format_message['message']['notified'] = 1;
                    $this->Tickets_model->save_message($format_message);
                    $this->Tickets_model->read_messages($validate_data["data"]["id_user"]);
                    $this->load->model('Users_model');
                    $user = $this->Users_model->get_user_by_id($validate_data["data"]["id_user"]);
                    $format_message['message']['output_name'] = $this->Users_model->set_user_output_name($user);
                    $format_message['message']['email'] = $user["email"];
                    $format_message['message']['lang_id'] = $user["lang_id"];
                    $return = $this->Tickets_model->send_contact_form($format_message['message']);
                    if (!empty($return["errors"])) {
                        $this->system_messages->addMessage(View::MSG_ERROR, $return["errors"]);
                    } else {
                        $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_send_form', 'tickets'));
                    }
                    redirect(site_url() . "admin/tickets/answer/{$validate_data["data"]["id_user"]}");
                }
            }
            $user = $this->Users_model->get_user_by_id($id_user);
            $header_text .= ':&nbsp;' . $this->Users_model->set_user_output_name($user);
            $search_params = array();
            $current_settings = isset($_SESSION["answer_list"]) ? $_SESSION["answer_list"] : array();
            if (!isset($current_settings["order"])) {
                $current_settings["order"] = "date_created";
            }
            if (!isset($current_settings["order_direction"])) {
                $current_settings["order_direction"] = "DESC";
            }
            if (!$order_direction) {
                $order_direction = $current_settings["order_direction"];
            }
            $this->view->assign('order_direction', $order_direction);
            $current_settings["order_direction"] = $order_direction;

            $search_params["where"]["id_user"] = $id_user;

            $this->load->helper('sort_order');
            $items_on_page = $this->pg_module->get_module_config('start', 'admin_items_per_page');
            $count_messages = $this->Tickets_model->get_count_messages($id_user);
            $page = get_exists_page_number($page, $count_messages, $items_on_page);
            $current_settings["page"] = $page;

            if (!$order) {
                $order = $current_settings["order"];
            }
            $this->view->assign('order', $order);
            $current_settings["order"] = $order;

            $_SESSION["answer_list"] = $current_settings;

            if ($count_messages > 0) {
                $user_messages = $this->Tickets_model->get_messages($search_params, $page, $items_on_page, null, array($order => $order_direction));
                $this->Tickets_model->read_messages($id_user);
                $this->view->assign('user_messages', $user_messages);
            }

            $sort_links = array(
                "date_created" => site_url() . "admin/tickets/answer/{$id_user}/{$page}/date_created/" . (($order != 'date_created' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
            );
            $this->view->assign('sort_links', $sort_links);

            $this->load->helper("navigation");
            $url = site_url() . "admin/tickets/answer/{$id_user}/";
            $page_data = get_admin_pages_data($url, $count_messages, $items_on_page, $page, 'briefPage');
            $page_data["date_format"] = $this->pg_date->get_format('date_time_literal', 'st');
            $this->view->assign('page_data', $page_data);
            $this->view->assign('id_user', $id_user);
        } else {
            $settings = $this->Tickets_model->get_settings();
            if (!$settings["status_personal_communication"]) {
                $this->system_messages->addMessage(View::MSG_ERROR, l('error_please_activate', 'tickets'));
                redirect(site_url() . "admin/tickets/settings");
            }
        }

        $this->Menu_model->set_menu_active_item('admin_tickets_menu', 'responder_list_item');
        $this->view->setHeader($header_text);
        $this->view->render('list_messages');
    }

    public function remove_contact($id_user = null)
    {
        $param = array();
        $user_ids = $this->input->post('user');
        if (!empty($id_user)) {
            $param["where"] = array('id_user' => $id_user);
        }
        if (!empty($user_ids)) {
            $param["where_in"]["id_user"] = $user_ids;
        }
        if (!empty($param)) {
            $this->Tickets_model->remove_contact($param);
            $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_remove_contact', 'tickets'));
        }
        $url = site_url() . "admin/tickets/index";
        redirect($url);
    }

    public function remove_message($id_user = null, $id = null)
    {
        if (!empty($id)) {
            $param["where"] = array('id' => $id, 'id_user' => $id_user);
            $this->Tickets_model->remove_message($param);
            $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_remove_message', 'tickets'));
        }
        $url = site_url() . "admin/tickets/answer/{$id_user}/";
        redirect($url);
    }

    public function ajax_get_users_data()
    {
        $return = array();
        $params = array();

        $search_string = trim(strip_tags($this->input->post('search', true)));
        if (!empty($search_string)) {
            $search_string_escape = $this->db->escape("%" . $search_string . "%");
            $params["where_sql"][] = "(nickname LIKE " . $search_string_escape . " OR fname LIKE " . $search_string_escape . " OR sname LIKE " . $search_string_escape . ")";
        }

        $selected = $this->input->post('selected', true);
        if (!empty($selected)) {
            $params["where_sql"][] = "id NOT IN (" . implode($selected) . ")";
        }

        $page = intval($this->input->post('page', true));
        if (!$page) {
            $page = 1;
        }

        $items_on_page = 100;
        $this->load->model('Users_model');
        $items = $this->Users_model->get_users_list_by_key($page, $items_on_page, array("nickname" => "asc"), $params, array(), true, true);

        $return["all"] = $this->Users_model->get_users_count($params);
        $return["items"] = $items;
        $return["current_page"] = $page;
        $return["pages"] = ceil($return["all"] / $items_on_page);

        $this->view->assign($return);

        return;
    }
}
