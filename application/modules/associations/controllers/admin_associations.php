<?php

namespace Pg\Modules\Associations\Controllers;

use Pg\Libraries\View;

/**
 * Admin Associations controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Nikita Savanaev <nsavanaev@pilotgroup.net>
 * */
class Admin_Associations extends \Controller
{
    /**
     * Controller
     *
     * @return Admin_Associations
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['Associations_model', 'Menu_model']);
        $this->Menu_model->set_menu_active_item('admin_menu', 'add_ons_items');
    }

    /**
     *  List of pictures
     *
     *  @param integer $page
     *
     *  @return void
     */
    public function index($order = null, $order_direction = null, $page = null)
    {
        $this->_list('index', $page, $order, $order_direction);
    }

    /**
     *  List of pictures of users
     *
     *  @param integer $page
     *
     *  @return void
     */
    public function users($order = 'date_created', $order_direction = "ASC", $page = null)
    {
        $this->_list('users', $page, $order, $order_direction);
    }

    /**
     *  List of associations
     *
     *  @param string $type
     *  @param integer $page
     *
     *  @return void
     */
    private function _list($type = 'index', $page = null, $order = null, $order_direction = null)
    {
        $associations_settings = isset($_SESSION['associations']) ? $_SESSION['associations'] : array();
        if (!isset($associations_settings['type'])) {
            $associations_settings['type'] = 'index';
        }
        if (!isset($associations_settings['order'])) {
            $associations_settings['order'] = 'date_created';
        }
        if (!isset($associations_settings['order_direction'])) {
            $associations_settings['order_direction'] = 'DESC';
        }
        if (!isset($associations_settings['page'])) {
            $associations_settings['page'] = 1;
        }

        $order = strval($order);
        $order_direction = strval($order_direction);
        $sort_links = array(
            'name'         => site_url() . 'admin/associations/' . $type . '/' . (($order != 'name' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
            'date_created' => site_url() . 'admin/associations/' . $type . '/' . (($order != 'date_created' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
        );
        $this->view->assign('sort_links', $sort_links);
        switch ($order) {
            case 'date_created':
                $order_array = array('id' => $order_direction);
                break;
            default:
                $order_array = array($order => $order_direction);
                break;
        }
        $page = intval($page);

        $this->load->helper('sort_order');
        $items_on_page = $this->pg_module->get_module_config('start', 'admin_items_per_page');

        if (!$order) {
            $order = $associations_settings['order'];
        }
        $this->view->assign('order', $order);
        $associations_settings['order'] = $order;

        if (!$order_direction) {
            $order_direction = $associations_settings['order_direction'];
        }
        $this->view->assign('order_direction', $order_direction);
        $associations_settings['order_direction'] = $order_direction;
        $associations_settings["page"] = $page;
        $associations_settings["type"] = $type;

        $_SESSION['associations'] = $associations_settings;

        $params = array();
        switch ($type) {
            case 'index':
                $associations_count = $this->Associations_model->getImagesCount();
                $page = get_exists_page_number($page, $associations_count, $items_on_page);
                $associations_settings['page'] = $page;
                if ($associations_count > 0) {
                    $data = $this->Associations_model->getListImages($page, $items_on_page, $order_array, $params);
                    $this->view->assign('associations', $data);
                }
                break;
            case 'users':
                $this->Associations_model->getListImages();
                break;
        }

        $this->load->helper('navigation');
        $page_data = get_admin_pages_data(site_url() . 'admin/associations/' . $type . '/' . $order . '/' . $order_direction . '/', $associations_count, $items_on_page, $page, 'briefPage');
        $this->view->assign('page_data', $page_data);

        $this->load->library('pg_date');
        $date_format = $this->pg_date->get_format('date_time_literal', 'st');
        $this->view->assign('date_format', $date_format);

        $this->view->setHeader(l('admin_header_configuration', 'associations'));
        $this->view->setBackLink(site_url() . "admin/start/menu/add_ons_items");
        $this->view->render('list');
    }

    /**
     * Settings module
     *
     * @return string
     */
    public function settings()
    {
        $data = $this->Associations_model->getSettings();
        $langs = $this->pg_language->languages;
        $lang_id = $this->pg_language->current_lang_id;
        if ($this->input->post('btn_save')) {
            $post_data = array(
                'is_active'    => $this->input->post('is_active', true),
                'chat_message' => $this->input->post('chat_message', true),
                'chat_more'    => $this->input->post('chat_more', true),
            );
            $validate_data = $this->Associations_model->validateSettings($post_data);
            if (!empty($validate_data["errors"])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data['errors']);
            } else {
                $this->Associations_model->setSettings($validate_data["data"]);
                $this->Associations_model->setMessageFields($validate_data["ds"]);
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_settings_save', 'associations'));
                $this->view->setRedirect(site_url() . "admin/associations/settings");
            }
        }
        $this->view->assign('data', $data);
        $this->view->assign('langs', $langs);
        $this->view->assign('current_lang_id', $lang_id);
        $this->view->setHeader(l('admin_header_settings', 'associations'));
        $this->view->setBackLink(site_url() . "admin/start/menu/add_ons_items");
        $this->view->render('settings');
    }

    /**
     *  Add/Edit association
     *
     *  @param integer $id
     *
     *  @return void
     */
    public function edit($id = null)
    {
        if (is_null($id)) {
            $data = array();
            $this->view->setHeader(l('admin_header_create', 'associations'));
        } else {
            $params = array();
            $params['id'] = intval($id);
            $data = $this->Associations_model->getImage($params);
            if (empty($data['id'])) {
                $this->view->setRedirect(site_url() . 'admin/associations');
            }
            $this->view->setHeader(l('admin_header_edit', 'associations'));
        }
        $langs = $this->pg_language->languages;
        $lang_id = $this->pg_language->current_lang_id;
        if ($this->input->post('save')) {
            $post_data = array();
            foreach ($langs as $value) {
                $post_data['name_' . $value['id']] = $this->input->post('name_' . $value['id'], true);
                $post_data['view_name_' . $value['id']] = $this->input->post('view_name_' . $value['id'], true);
            }
            $validate_data = $this->Associations_model->validateImage($id, $post_data);
            if (!empty($validate_data['errors'])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data['errors']);
            } else {
                $id = $this->Associations_model->saveImages($id, $validate_data['data']);

                if (!empty($data)) {
                    $message = l('success_item_updated', 'associations');
                } else {
                    $message = l('success_item_created', 'associations');
                }

                $this->system_messages->addMessage(View::MSG_INFO, $message);
                $this->view->setRedirect(site_url() . 'admin/associations');

                return;
            }
            $data = array_merge($data, $post_data);
        }
        $this->view->assign('association', $data);
        $this->view->assign('langs', $langs);
        $this->view->assign('current_lang_id', $lang_id);
        $this->view->render('edit');
    }

    /**
     *  Delete association
     *
     *  @param integer $id
     *
     *  @return void
     */
    public function delete($id = null)
    {
        $this->_delete(array($id));
    }

    /**
     *  Change the status of the association
     *
     *  @param integer $id
     *
     *  @return void
     */
    public function activate($id = null, $is_active = null)
    {
        if (!is_null($id)) {
            $attrs = array();
            $attrs['is_active'] = !empty($is_active) ? 1 : 0;
            $this->Associations_model->saveImages($id, $attrs);
            if ($is_active) {
                $this->system_messages->addMessage(View::MSG_INFO, l('success_activate', 'associations'));
            } else {
                $this->system_messages->addMessage(View::MSG_INFO, l('success_deactivate', 'associations'));
            }
        }
        $cur_set = $_SESSION["associations"];
        $url = site_url() . "admin/associations/{$cur_set["type"]}/{$cur_set["order"]}/{$cur_set["order_direction"]}/{$cur_set["page"]}";
        $this->view->setRedirect($url);
    }

    /**
     *  Delete associations
     *
     *  @param array $id
     *
     *  @return void
     */
    private function _delete($ids = array())
    {
        if (!empty($ids)) {
            foreach ($ids as $id) {
                $this->Associations_model->delete($id);
            }
            $this->system_messages->addMessage(View::MSG_INFO, l('success_item_delete', 'associations'));
        }
        $cur_set = $_SESSION["associations"];
        $url = site_url() . "admin/associations/{$cur_set["type"]}/{$cur_set["order"]}/{$cur_set["order_direction"]}/{$cur_set["page"]}";
        $this->view->setRedirect($url);
    }

    /**
     *  Ajax delete association
     *
     *  @param integer $id
     *
     *  @return void
     */
    public function deleteSelect()
    {
        $post_data = $this->input->post('ids');
        $this->_delete($post_data);
    }
}
