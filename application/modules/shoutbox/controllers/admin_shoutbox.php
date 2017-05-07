<?php

namespace Pg\Modules\Shoutbox\Controllers;

use Pg\Libraries\View;

/**
 * Admin shoutbox controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Dmitry Popenov
 *
 * @version $Revision: 2 $ $Date: 2013-01-30 10:07:07 +0400 $
 **/
class Admin_Shoutbox extends \Controller
{
    private $_settings = array(
        array('var' => 'status', 'type' => 'checkbox'),
        array('var' => 'message_max_chars', 'type' => 'text'),
        array('var' => 'message_max_counts', 'type' => 'text'),
    );
    /**
     * Controller
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Menu_model');
        $this->Menu_model->set_menu_active_item('admin_menu', 'add_ons_items');
        $this->load->model('Shoutbox_model');
    }

    /*
     * SHOUTBOX FUNCTIONS
     */

    public function index($order = 'date_created', $order_direction = 'DESC', $page = 1)
    {
        if (!$order) {
            $order = 'date_created';
        }
        if (!$order_direction) {
            $order_direction = 'DESC';
        }
        if (!$page) {
            $page = 1;
        }
        $page = $page < 0 ? 1 : $page;
        $page = floor($page);

        // Грузим настройки
        $current_settings = isset($_SESSION["smessages_list"]) ? $_SESSION["smessages_list"] : array();
        if (!isset($current_settings["order"])) {
            $current_settings["order"] = $order;
        }
        if (!isset($current_settings["order_direction"])) {
            $current_settings["order_direction"] = $order_direction;
        }
        if (!isset($current_settings["page"])) {
            $current_settings["page"] = $page;
        }

        $items_on_page = $this->pg_module->get_module_config('start', 'admin_items_per_page');
        $this->load->helper('sort_order');
        $messages_cnt = $this->Shoutbox_model->get_messages_cnt();
        $page = get_exists_page_number($page, $messages_cnt, $items_on_page);
        $current_settings["page"] = $page;

        // Сохраняем настройки
        $_SESSION["smessages_list"] = $current_settings;

        // Ссылки для сортировки ASC DESC
        $sort_links = array(
            "message"      => site_url() . "admin/shoutbox/index/message/" . (($order != 'message' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
            "date_created" => site_url() . "admin/shoutbox/index/date_created/" . (($order != 'date_created' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
        );
        $this->view->assign('sort_links', $sort_links);

        if ($messages_cnt > 0) {
            $messages = $this->Shoutbox_model->get_messages($page, $items_on_page, array($order => $order_direction));

            $this->load->helper("navigation");
            $url = site_url() . "admin/shoutbox/index/" . $order . "/" . $order_direction . "/";
            $pages_data = get_admin_pages_data($url, $messages_cnt, $items_on_page, $page, 'briefPage');
            $page_data["date_format"] = $this->pg_date->get_format('date_time_literal', 'st');
            $this->view->assign('page_data', $pages_data);

            $this->view->assign('order', $order);
            $this->view->assign('order_direction', $order_direction);
            $this->view->assign('page', $page);
            $this->view->assign('messages', $messages);
        }
        $this->view->setHeader(l('admin_header_list', 'shoutbox'));
        $this->view->render('index');
    }

    public function settings()
    {
        if ($this->input->post('btn_save')) {
            $post_data = $this->input->post('settings', true);

            $this->load->model('Shoutbox_model');
            $validate_data = $this->Shoutbox_model->validateSettings($post_data);
            if (!empty($validate_data['errors'])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data['errors']);
            } else {
                foreach ($validate_data['data'] as $var => $value) {
                    $this->pg_module->set_module_config('shoutbox', $var, $value);
                }
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_update_data', 'shoutbox'));
                redirect(site_url() . 'admin/shoutbox/settings');
            }

            foreach ($this->_settings as $key => $var) {
                if (!isset($post_data[$var['var']])) {
                    continue;
                }
                $this->_settings[$key]['value'] = $post_data[$var['var']];
            }
        }

        foreach ($this->_settings as $var) {
            $field_type = 'text';
            $field_values = array();
            if (is_array($var)) {
                $field_type = $var['type'];
                $field_values = !empty($var['values']) ? $var['values'] : array();
                if (isset($var['value'])) {
                    $vars_value = $var['value'];
                } else {
                    $vars_value = $this->pg_module->get_module_config('shoutbox', $var['var']);
                }
                $var = $var['var'];
            } else {
                $vars_value = $this->pg_module->get_module_config('shoutbox', $var);
            }

            $vars_value_name = '';
            $settings_data['vars'][] = array(
                "field"        => $var,
                "field_name"   => l("shoutbox_" . $var . "_field", "shoutbox"),
                "value"        => $vars_value,
                "value_name"   => $vars_value_name,
                'field_type'   => $field_type,
                'field_values' => $field_values,
            );
        }
        $this->view->assign("settings_data", $settings_data);
        $this->view->setHeader(l('admin_header_settings', 'shoutbox'));
        $this->view->render('settings');
    }

    public function delete($message_id)
    {
        if (!empty($message_id)) {
            $this->Shoutbox_model->delete_message_by_id($message_id);
            $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_delete_shoutbox', 'shoutbox'));
        }
        $cur_set = $_SESSION["smessages_list"];
        $url = site_url() . "admin/shoutbox/index/" . (isset($cur_set["order"]) ? $cur_set["order"] : 'date_created') . "/" . (isset($cur_set["order_direction"]) ? $cur_set["order_direction"] : 'DESC') . "/" . (isset($cur_set["page"]) ? $cur_set["page"] : 1) . "";
        redirect($url);
    }

    public function messages_delete()
    {
        $errors = false;
        $messages = array();
        if (!$ids) {
            $ids = $this->input->post("ids");
        }

        if (!empty($ids)) {
            foreach ((array) $ids as $id) {
                $no_error = $this->Shoutbox_model->delete_message_by_id($id);
                if (!$no_error) {
                    $errors = true;
                }
            }
            if ($errors) {
                $this->system_messages->addMessage(View::MSG_ERROR, $messages);
            } else {
                $this->system_messages->addMessage(View::MSG_SUCCESS, l("success_delete_shoutbox_s", "shoutbox"));
            }
        }
        $cur_set = $_SESSION["smessages_list"];
        $url = site_url() . "admin/shoutbox/index/" . (isset($cur_set["order"]) ? $cur_set["order"] : 'date_created') . "/" . (isset($cur_set["order_direction"]) ? $cur_set["order_direction"] : 'DESC') . "/" . (isset($cur_set["page"]) ? $cur_set["page"] : 1) . "";
        redirect($url);
    }
}
