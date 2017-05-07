<?php

namespace Pg\Modules\Store\Controllers;

use Pg\Libraries\View;

/**
 * Admin store controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Nikita Savanaev <nsavanaev@pilotgroup.net>
 * */

class Admin_Store extends \Controller 
{

    /**
     * Controller
     *
     * @return Admin_Store
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model([
            'store/models/Store_model',
            'store/models/Store_products_model',
            'Menu_model'
         ]);
        $this->Menu_model->set_menu_active_item('admin_menu', 'add_ons_items');
    }

    /**
     * Index page
     *
     * @return void
     */
    public function index()
    {
        $menu_data = $this->Menu_model->get_menu_by_gid('admin_menu');
        $menu_item = $this->Menu_model->get_menu_item_by_gid('store_menu_item', $menu_data["id"]);

        $user_type = $this->session->userdata("user_type");
        if ($user_type == "admin") {
            $menu_data["check_permissions"] = false;
            $permissions = array();
        } else {
            $menu_data["check_permissions"] = true;
            $permissions = $this->session->userdata("permission_data");
        }
        $sections = $this->Menu_model->get_menu_active_items_list($menu_data["id"], $menu_data["check_permissions"], array(), $menu_item["id"], $permissions);

        $this->view->setHeader(l('admin_header_main_sections', 'store'));
        $this->view->assign("options", $sections);
        $this->view->render('menu_list', 'admin', 'start');
    }

    /**
     * Return products
     *
     * @param string  $filter
     * @param string  $order
     * @param string  $order_direction
     * @param integer $page
     *
     * @return string
     */
    public function products($filter = "all", $order = "priority", $order_direction = "ASC", $page = 1)
    {
        $attrs  = $search_params = [];
        $lang_id = $this->pg_language->current_lang_id;
        $current_settings = isset($_SESSION["products_list"]) ? $_SESSION["products_list"] : array();
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
            $current_settings["search_category"] = $this->input->post('category', true);
            $current_settings["search_text"]     = $this->input->post('val_text', true);
            $current_settings["price"]["min"]    = $this->input->post('price_min', true);
            $current_settings["price"]["max"]    = $this->input->post('price_max', true);
        }
        if (!empty($current_settings["search_text"])) {
            $search_text_escape = $this->db->escape("%" . $current_settings["search_text"] . "%");
            $attrs["where_sql"][] = $search_params["where_sql"][] = "(name_" . $lang_id . " LIKE " . $search_text_escape . ")";
        }
        $this->load->model('store/models/Store_categories_model');
        if (!empty($current_settings["search_category"])) {
            $product_ids                     = $this->Store_categories_model->get_products_by_category_id(intval($current_settings["search_category"]));
            $attrs["where_in"]["id"]         = $search_params["where_in"]["id"] = array(0);
            if (!empty($product_ids)) {
                $attrs["where_in"]["id"]         = $search_params["where_in"]["id"] = $product_ids;
            }
        }
        if (!empty($current_settings["price"]["min"])) {
            $attrs["where_sql"][]         = $search_params["where_sql"][] = "price_reduced >= '" . floatval($current_settings["price"]["min"]) . "'";
        }
        if (!empty($current_settings["price"]["max"])) {
            $attrs["where_sql"][]         = $search_params["where_sql"][] = "price_reduced <= '" . floatval($current_settings["price"]["max"]) . "'";
        }
        $search_param = array(
            'text'        => isset($current_settings["search_text"]) ? $current_settings["search_text"] : '',
            'category_id' => isset($current_settings["search_category"]) ? $current_settings["search_category"] : '',
            'price'       => isset($current_settings["price"]) ? $current_settings["price"] : '',
        );
        $filter_data["all"]                      = $this->Store_products_model->get_products_count($search_params);
        $search_params["where"]["status"]        = 0;
        $filter_data["inactive"]                 = $this->Store_products_model->get_products_count($search_params);
        $search_params["where"]["status"]        = 1;
        $filter_data["active"]                   = $this->Store_products_model->get_products_count($search_params);
        unset($search_params["where"]["status"]);
        $search_params["where"]["is_bestseller"] = 1;
        $filter_data["bestsellers"]              = $this->Store_products_model->get_products_count($search_params);

        switch ($filter) {
            case 'active' : $attrs["where"]['status']        = 1;
                break;
            case 'inactive' : $attrs["where"]['status']        = 0;
                break;
            case 'bestsellers' : $attrs["where"]['is_bestseller'] = 1;
                break;
            case 'all' : break;
            default: $filter                          = $current_settings["filter"];
        }
        $current_settings["filter"] = $filter;

        $cat_attrs['where']['status'] = 1;
        $categories = $this->Store_categories_model->get_categories_list(null, null, null, $cat_attrs, array(), false, false, $lang_id);
        $this->view->assign('categories', $categories);

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

        $products_count = $filter_data[$filter];

        if (!$page) {
            $page = $current_settings["page"];
        }
        $items_on_page            = $this->pg_module->get_module_config('start', 'admin_items_per_page');
        $this->load->helper('sort_order');
        $page                     = get_exists_page_number($page, $products_count, $items_on_page);
        $current_settings["page"] = $page;

        $_SESSION["products_list"] = $current_settings;

        $sort_links = array(
            "name"          => site_url() . "admin/store/products/{$filter}/name/" . (($order != 'name' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
            "price_reduced" => site_url() . "admin/store/products/{$filter}/price_reduced/" . (($order != 'price_reduced' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
            "date_modified" => site_url() . "admin/store/products/{$filter}/date_updated/" . (($order != 'date_updated' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
        );
        $this->view->assign('sort_links', $sort_links);
        if ($products_count > 0) {
            $products = $this->Store_products_model->get_products_list($page, $items_on_page, array($order => $order_direction), $attrs, array(), true, false, $lang_id);
            $this->view->assign("products", $products);
        }
        $this->load->helper("navigation");
        $url                      = site_url() . "admin/store/products/{$filter}/{$order}/{$order_direction}/";
        $page_data                = get_admin_pages_data($url, $products_count, $items_on_page, $page, 'briefPage');
        $page_data["date_format"] = $this->pg_date->get_format('date_time_literal', 'st');
        $this->view->assign('page_data', $page_data);
        $this->view->setBackLink(site_url() . 'admin/store/index');
        $this->view->setHeader(l('admin_header_products', 'store'));
        $this->view->render('products');
    }

    /**
     * Add/edit product
     *
     * @param string  $section
     * @param integer $id
     *
     * @return string
     */
    public function product($section = 'description', $id = null)
    {
        $langs   = $this->pg_language->languages;
        $product = array();
        if (!empty($id)) {
            $product = $this->Store_products_model->get_product_by_id($id);
        }
        if ($this->input->post('btn_save')) {
            $post_data = array(
                'category_id'   => $this->input->post('category_id', true),
                'gid'           => $this->input->post('gid', true),
                'price'         => $this->input->post('price', true),
                'price_reduced' => $this->input->post('price_reduced', true),
                'is_bestseller' => $this->input->post('is_bestseller', true),
                'option'        => $this->input->post('option', true),
            );
            foreach ($langs as $value) {
                $post_data['name_' . $value['id']]        = $this->input->post('name_' . $value['id'], true);
                $post_data['description_' . $value['id']] = $this->input->post('description_' . $value['id'], true);
            }
            $validate_data = $this->Store_products_model->validate($id, $post_data);
            if (!empty($validate_data["errors"])) {
                $errors  = $validate_data["errors"];
                $product = array_merge($product, $validate_data["data"]["product"]);
            } else {
                $this->load->model('store/models/Store_categories_model');
                $this->load->model('store/models/Store_bestsellers_model');
                $save_data = $validate_data["data"]["product"];
                if (!$id) {
                    $save_data["status"]       = 1;
                    $save_data["priority"]     = $this->Store_products_model->get_last_priority(1);
                    $this->load->model('payments/models/Payment_currency_model');
                    $base_currency             = $this->Payment_currency_model->get_currency_default(true);
                    $save_data["gid_currency"] = $base_currency["gid"];
                }
                $id_product = $this->Store_products_model->save_info_product($id, $save_data);
                $this->Store_categories_model->set_categories_by_product_id($id_product, $validate_data["data"]["category"]);
                $this->Store_bestsellers_model->save_bestsellers($id_product, $validate_data["data"]["category"], $save_data["is_bestseller"]);
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_update_service_data', 'services'));
                redirect(site_url() . "admin/store/product/media/" . $id_product);
            }
        }
        if (!empty($errors)) {
            $this->system_messages->addMessage(View::MSG_ERROR, $errors);
        }
        
        $this->load->model('Uploads_model');
        $photo_config = $this->Uploads_model->get_config('store');
        $photo_config['max_count'] = $this->Store_products_model->max_photo_count;
        $this->view->assign('photo_config', $photo_config);
        
        $this->load->model('Video_uploads_model');
        $video_config = $this->Video_uploads_model->get_config('store');
        $this->view->assign('video_config', $video_config);

        $this->view->assign('current_lang_id', $this->pg_language->current_lang_id);
        $this->view->assign('current_lang_code', $this->pg_language->get_lang_code_by_id($this->pg_language->current_lang_id));
        $this->view->assign('langs', $langs);
        $this->view->assign('back_url', site_url() . 'admin/store/products');
        $this->view->assign('section', trim(strip_tags($section)));
        $this->view->assign('product', $product);

        $this->view->setBackLink(site_url() . 'admin/store/products');
        $this->view->setHeader(l('admin_header_product', 'store'));

        $this->view->render('product_edit');
    }

    /**
     * Status product
     *
     * @param integer $id
     * @param integer $status
     *
     * @return void
     */
    public function status_product($id = null, $status = 0)
    {
        if (!empty($id)) {
            $this->load->model("store/models/Store_categories_model");
            $category_ids = $this->Store_categories_model->get_categories_by_product_id($id);
            $this->Store_products_model->set_status_product($id, $status, $category_ids);
            if ($status) {
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_activate_product', 'store'));
            } else {
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_deactivate_product', 'store'));
            }
        }
        $cur_set = $_SESSION["products_list"];
        $url     = site_url() . "admin/store/products/{$cur_set["filter"]}/{$cur_set["order"]}/{$cur_set["order_direction"]}/{$cur_set["page"]}";
        redirect($url);
    }

    /**
     * Status category
     *
     * @param integer $id
     * @param integer $status
     *
     * @return void
     */
    public function status_category($id = null, $status = 0)
    {
        if (!empty($id)) {
            $this->load->model("store/models/Store_categories_model");
            $this->Store_categories_model->set_status_category($id, $status);
            if ($status) {
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_activate_category', 'store'));
            } else {
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_deactivate_category', 'store'));
            }
        }
        $cur_set = $_SESSION["categories_list"];
        $url     = site_url() . "admin/store/product_categories/{$cur_set["page"]}";
        redirect($url);
    }

    /**
     * Sort product
     *
     * @param integer $id
     * @param string  $direction
     *
     * @return void
     */
    public function sort_product($id = null, $direction = '')
    {
        $cur_set = $_SESSION["products_list"];
        if (!is_null($id) && !empty($direction)) {
            $this->Store_products_model->set_sort_product($id, $direction, $cur_set);
            if ($direction == 'up') {
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_direction_up_product', 'store'));
            } elseif ($direction == 'down') {
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_direction_down_product', 'store'));
            }
        }
        $url = site_url() . "admin/store/products/{$cur_set["filter"]}/{$cur_set["order"]}/{$cur_set["order_direction"]}/{$cur_set["page"]}";
        redirect($url);
    }

    /**
     * Sort category
     *
     * @param integer $id
     * @param string  $direction
     *
     * @return void
     */
    public function sort_categories($id = null, $direction = '')
    {
        $this->load->model('store/models/Store_categories_model');
        if (!empty($id) && !empty($direction)) {
            $this->Store_categories_model->set_sort_categories($id, $direction);
            if ($direction == 'up') {
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_direction_up_category', 'store'));
            } elseif ($direction == 'down') {
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_direction_down_category', 'store'));
            }
        }
        $cur_set = $_SESSION["categories_list"];
        $url     = site_url() . "admin/store/product_categories/{$cur_set["page"]}";
        redirect($url);
    }

    /**
     * Delete product
     *
     * @param integer $id
     *
     * @return void
     */
    public function delete_product($id = null)
    {
        if (!empty($id)) {
            $this->load->model('store/models/Store_categories_model');
            $categories = $this->Store_categories_model->get_categories_by_product_id($id);
            $this->Store_products_model->delete_product($id, $categories);
            $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_delete_products', 'store'));
        }
        $cur_set = $_SESSION["products_list"];
        $url     = site_url() . "admin/store/products/{$cur_set["filter"]}/{$cur_set["order"]}/{$cur_set["order_direction"]}/{$cur_set["page"]}";
        redirect($url);
    }

    /**
     * Delete proto
     *
     * @param integer $id_product
     * @param integer $id_media
     *
     * @return void
     */
    public function delete_photo($id_product, $id_media)
    {
        if (!empty($id_product)) {
            $this->Store_products_model->delete_photo($id_product, $id_media);
            $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_delete_photo', 'store'));
        }
        $url = site_url() . "admin/store/product/media/" . $id_product;
        redirect($url);
    }

    /**
     * Delete video
     *
     * @param integer $id_product
     * @param integer $id_media
     *
     * @return void
     */
    public function delete_video($id_product, $id_media)
    {
        if (!empty($id_product)) {
            $this->Store_products_model->delete_video($id_product, $id_media);
            $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_delete_photo', 'store'));
        }
        $url = site_url() . "admin/store/product/media/" . $id_product;
        redirect($url);
    }

    /**
     * Delete product by ajax
     *
     *
     * @return void
     */
    public function ajax_delete_products()
    {
        $product_ids = $_POST['product_ids'];
        if (!empty($product_ids)) {
            $this->load->model('store/models/Store_categories_model');
            foreach ($product_ids as $product_id) {
                $categories = $this->Store_categories_model->get_categories_by_product_id($product_id);
                $this->Store_products_model->delete_product($product_id);
            }
            exit(l('success_delete_products', 'store'));
        }
    }

    /**
     * Return product categories
     *
     * @param integer $page
     * @param string  $order
     * @param string  $order_direction
     *
     * @return string
     */
    public function product_categories($page = 1, $order = "priority", $order_direction = "ASC")
    {
        $current_settings = isset($_SESSION["categories_list"]) ? $_SESSION["categories_list"] : array();
        if (!isset($current_settings["page"])) {
            $current_settings["page"] = $page;
        }

        $this->load->model('store/models/Store_categories_model');
        $lang_id = $this->pg_language->current_lang_id;

        $categories_count         = $this->Store_categories_model->get_categories_count(array());
        $current_settings["page"] = $page;
        if (!$page) {
            $page = $current_settings["page"];
        }
        $items_on_page               = $this->pg_module->get_module_config('start', 'admin_items_per_page');
        $categories                  = $this->Store_categories_model->get_categories_list($page, $items_on_page, array($order => $order_direction), array(), array(), true, false, $lang_id);
        $categories                  = $this->bestsellers_active_by_category($categories);
      
        $this->load->helper('sort_order');
        $page                        = get_exists_page_number($page, $categories_count, $items_on_page);
        $current_settings["page"]    = $page;
        $_SESSION["categories_list"] = $current_settings;
        $this->load->helper("navigation");
        $url                         = site_url() . "admin/store/product_categories/";
        $page_data                   = get_admin_pages_data($url, $categories_count, $items_on_page, $page, 'briefPage');
        $page_data["date_format"]    = $this->pg_date->get_format('date_time_literal', 'st');

        $this->view->assign('page_data', $page_data);
        $this->view->assign('categories', $categories);

        $this->view->setBackLink(site_url() . 'admin/store/index');
        $this->view->setHeader(l('admin_header_product_categories', 'store'));

        $this->view->render('categories');
    }

    /**
     * Return category
     *
     * @param integer $parent
     * @param integer $page
     *
     * @return string
     */
    public function category($parent = null, $page = 1)
    {
        if (isset($parent)) {
            $this->load->model('store/models/Store_options_model');
            $lang_id       = $this->pg_language->current_lang_id;
            $items_on_page = $this->pg_module->get_module_config('start', 'admin_items_per_page');
            $this->load->helper('sort_order');
            $options_count = $this->Store_options_model->get_options_count();
            $page          = get_exists_page_number($page, $options_count, $items_on_page);
            $options       = $this->Store_options_model->get_options_list($page, $items_on_page, array(), array(), $lang_id);

            $this->load->model('store/models/Store_categories_model');
            $category_options = $this->Store_categories_model->get_category_options($parent);
            foreach ($options as $option) {
                if (!empty($category_options[$option['id']])) {
                    $option['selected'] = 1;
                }
                $options_data[] = $option;
            }
            $this->view->assign('options', $options_data);
            $this->view->assign('parent', $parent);
            $this->load->helper("navigation");

            $url                      = site_url() . "admin/store/category/{$parent}/";
            $page_data                = get_admin_pages_data($url, $options_count, $items_on_page, $page, 'briefPage');
            $page_data["date_format"] = $this->pg_date->get_format('date_time_literal', 'st');
            $this->view->assign('page_data', $page_data);
        }

        $this->view->setBackLink(site_url() . 'admin/store/product_categories/');
        $this->view->setHeader(l('admin_header_options', 'store'));

        $this->view->render('category_options');
    }

    /**
     * Delete category
     *
     * @param integer $id
     * @param integer $page
     *
     * @return void
     */
    public function delete_category($id = null, $page = 1)
    {
        if (isset($id)) {
            $this->load->model('store/models/Store_categories_model');
            $products = $this->Store_categories_model->get_products_by_category_id($id);
            if (empty($products)) {
                $this->Store_categories_model->delete_category($id);
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_delete_category', 'store'));
            } else {
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('error_delete_category', 'store'));
            }
        }
        redirect(site_url() . "admin/store/product_categories/" . $page);
    }

    /**
     * Edit category
     *
     * @param integer $id
     *
     * @return string
     */
    public function edit_category($id = null)
    {
        $this->load->model('store/models/Store_categories_model');
        $langs    = $this->pg_language->languages;
        $category = array();
        if (isset($id)) {
            $category = $this->Store_categories_model->get_category_by_id(intval($id));
        }
        if ($this->input->post('btn_save')) {
            foreach ($langs as $key => $value) {
                $post_data['gid']                         = $this->input->post('gid', true);
                $post_data['name_' . $value['id']]        = $this->input->post('name_' . $value['id'], true);
                $post_data['description_' . $value['id']] = $this->input->post('description_' . $value['id'], true);
            }
            $validate_data = $this->Store_categories_model->validate($id, $post_data);
            if (!empty($validate_data["errors"])) {
                $errors   = $validate_data["errors"];
                $category = array_merge($category, $validate_data["data"]);
            } else {
                $save_data = $validate_data["data"];
                if (!$id) {
                    $save_data["status"]   = 1;
                    $save_data["priority"] = $this->Store_categories_model->get_last_priority(1);
                }
                $id_category = $this->Store_categories_model->save_category($id, $save_data);
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_update_service_data', 'services'));
                redirect(site_url() . "admin/store/product_categories/");
            }
        }
        if (!empty($errors)) {
            $this->system_messages->addMessage(View::MSG_ERROR, $errors);
        }

        $this->view->assign('current_lang_id', $this->pg_language->current_lang_id);
        $this->view->assign('current_lang_code', $this->pg_language->get_lang_code_by_id($this->pg_language->current_lang_id));
        $this->view->assign('langs', $langs);
        $this->view->assign('back_url', site_url() . 'admin/store/product_categories');
        $this->view->assign('category', $category);

        $this->view->setBackLink(site_url() . 'admin/store/product_categories');
        $this->view->setHeader(l('admin_header_product_categories', 'store'));

        $this->view->render('category_edit');
    }

    /**
     * Status option
     *
     * @param integer $parent
     * @param integer $id
     * @param integer $status
     *
     * @return void
     */
    public function status_option_category($parent = null, $id = null, $status = 0)
    {
        if (!empty($parent) && !empty($id)) {
            $this->load->model("store/models/Store_categories_model");
            $options = $this->Store_categories_model->get_category_options($parent);
            $options = $options ? $options : array();
            if ($status) {
                $option[$id] = $id;
                $options     = $option + $options;
                $msg         = l('success_activate_option', 'store');
            } else {
                unset($options[$id]);
                $msg = l('success_deactivate_option', 'store');
            }
            $attrs['options_data'] = serialize($options);
            $this->Store_categories_model->set_status_option_category($parent, $attrs);
            $this->system_messages->addMessage(View::MSG_SUCCESS, $msg);
        }
        $url = site_url() . "admin/store/category/" . $parent;
        redirect($url);
    }

    /**
     * Options
     *
     * @param integer $page
     *
     * @return string
     */
    public function options($page = 1)
    {
        $current_settings = isset($_SESSION["options_list"]) ? $_SESSION["options_list"] : array();
        if (!isset($current_settings["page"])) {
            $current_settings["page"] = $page;
        }

        $this->load->model('store/models/Store_options_model');
        $lang_id = $this->pg_language->current_lang_id;

        $options_count            = $this->Store_options_model->get_options_count();
        $current_settings["page"] = $page;
        if (!$page) {
            $page = $current_settings["page"];
        }
        $items_on_page            = $this->pg_module->get_module_config('start', 'admin_items_per_page');
        $this->load->helper('sort_order');
        $page                     = get_exists_page_number($page, $options_count, $items_on_page);
        $current_settings["page"] = $page;
        $_SESSION["options_list"] = $current_settings;
        $this->load->helper("navigation");
        $url                      = site_url() . "admin/store/options/";
        $page_data                = get_admin_pages_data($url, $options_count, $items_on_page, $page, 'briefPage');
        $page_data["date_format"] = $this->pg_date->get_format('date_time_literal', 'st');

        $options = $this->Store_options_model->get_options_list($page, $items_on_page, array(), array(), $lang_id);
        $this->view->assign('options', $options);

        $this->view->assign('page_data', $page_data);
        $this->view->assign('options', $options);

        $this->view->setBackLink(site_url() . 'admin/store/index/');
        $this->view->setHeader(l('admin_header_options', 'store'));

        $this->view->render('options');
    }

    /**
     * Options edit
     *
     * @param integer $id
     *
     * @return string
     */
    public function options_edit($id = null)
    {
        $this->load->model('store/models/Store_options_model');
        $langs  = $this->pg_language->languages;
        $option = array();
        if (!empty($id)) {
            $option = $this->Store_options_model->get_option_by_id($id);
        }
        if ($this->input->post('btn_save')) {
            $post_data = array(
                'multiselect' => $this->input->post('multiselect', true),
            );
            foreach ($langs as $key => $value) {
                $post_data['name_' . $value['id']]        = $this->input->post('name_' . $value['id'], true);
                $post_data['description_' . $value['id']] = $this->input->post('description_' . $value['id'], true);
            }
            $validate_data = $this->Store_options_model->validate($post_data);
            if (!empty($validate_data["errors"])) {
                $errors = $validate_data["errors"];
                $option = array_merge($option, $validate_data["data"]);
            } else {
                $save_data = $validate_data["data"];

                $id_option = $this->Store_options_model->save_option($id, $save_data);
                $this->Store_products_model->option_dedicate_module_callback_add($id_option);
                redirect(site_url() . "admin/store/options/");
            }
        }
        if (!empty($errors)) {
            $this->system_messages->addMessage(View::MSG_ERROR, $errors);
        }

        $this->view->assign('current_lang_id', $this->pg_language->current_lang_id);
        $this->view->assign('current_lang_code', $this->pg_language->get_lang_code_by_id($this->pg_language->current_lang_id));
        $this->view->assign('langs', $langs);
        $this->view->assign('back_url', site_url() . 'admin/store/options');
        $this->view->assign('option', $option);

        $this->view->setBackLink(site_url() . 'admin/store/options');
        $this->view->setHeader(l('admin_header_options', 'store'));

        $this->view->render('options_edit');
    }

    /**
     * Delete option
     *
     * @param integer $id
     * @param integer $page
     *
     * @return void
     */
    public function delete_option($id = null, $page = 1)
    {
        if (isset($id)) {
            $this->load->model('store/models/Store_options_model');
            $this->Store_options_model->delete_option($id);
            $gid = 'store_optoins_' . $id;
            $this->pg_language->ds->set_module_reference('store', $gid, array());
            $this->Store_products_model->option_dedicate_module_callback_delete($id);
            $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_delete_option', 'store'));
        }
        redirect(site_url() . "admin/store/options/" . $page);
    }

    /**
     * Option fields
     *
     * @param integer $parent
     * @param integer $lang_id
     *
     * @return string
     */
    public function option_fields($parent = 0, $lang_id = null)
    {
        $this->view->assign('langs', $this->pg_language->languages);
        if (!$lang_id) {
            $lang_id = $this->pg_language->current_lang_id;
        }
        $this->view->assign('current_lang_id', $lang_id);
        $this->load->model('store/models/Store_options_model');
        $options = $this->pg_language->ds->get_reference('store', 'store_optoins_' . $parent, $lang_id);

        $this->pg_theme->add_js('admin-multilevel-sorter.js');
        if ($parent) {
            $c = $this->Store_options_model->get_option_by_id($parent);
            $this->view->setHeader(l('admin_header_option_ds', 'store') . ':&nbsp;' . $c['name_' . $lang_id]);
            $this->view->setBackLink(site_url() . "admin/store/options/0/" . $lang_id);
        } else {
            $this->view->setHeader(l('admin_header_options', 'store'));
            $this->view->setBackLink(site_url() . "admin/store/options");
        }
        $this->view->assign('options', $options);
        $this->view->assign('parent', $parent);
        $this->view->render('options_ds');
    }

    /**
     * Option fields edit
     *
     * @param integer $parent
     * @param integer $lang_id
     * @param integer $option_gid
     *
     * @return string
     */
    public function option_fields_edit($parent = 0, $lang_id = null, $option_gid = 0)
    {
        if (!$lang_id || !array_key_exists($lang_id, $this->pg_language->languages)) {
            $lang_id = $this->pg_language->current_lang_id;
        }
        $ds_gid = 'store_optoins_' . $parent;
        $option = $this->pg_language->ds->get_reference('store', $ds_gid, $lang_id);
        if ($option_gid) {
            $add_flag = false;
            foreach ($this->pg_language->languages as $lid => $lang) {
                $r               = $this->pg_language->ds->get_reference('store', $ds_gid, $lid);
                $lang_data[$lid] = $r["option"][$option_gid];
            }
        } else {
            $option_gid = "";
            $lang_data  = array();
            $add_flag   = true;
        }

        if ($this->input->post('btn_save')) {
            $lang_data = $this->input->post('lang_data', true);

            if (empty($option_gid)) {
                if (!empty($option["option"])) {
                    $array_keys = array_keys($option["option"]);
                } else {
                    $array_keys = array(0);
                }
                $option_gid = max($array_keys) + 1;
            }

            foreach ($lang_data as $lid => $string) {
                $lang_data[$lid] = trim(strip_tags($string));
                if (empty($lang_data[$lid])) {
                    $this->system_messages->addMessage(View::MSG_ERROR, l('error_option_name_required', 'properties') . ': ' . $this->pg_language->languages[$lid]['name']);
                    $is_err = true;
                    continue;
                } elseif (!array_key_exists($lid, $this->pg_language->languages)) {
                    continue;
                }
            }
            if (!$is_err) {
                foreach ($lang_data as $lid => $string) {
                    $option                        = $this->pg_language->ds->get_reference('store', $ds_gid, $lid);
                    $option["option"][$option_gid] = $string;
                    $this->pg_language->ds->set_module_reference('store', $ds_gid, $option, $lid);
                }

                if ($add_flag) {
                    $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_added_property_item', 'properties'));
                } else {
                    $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_updated_property_item', 'properties'));
                }

                $url = site_url() . "admin/store/option_fields/" . $parent . "/" . $lang_id;
                redirect($url);
            }
        }
        $this->load->model('store/models/Store_options_model');
        $get_option  = $this->Store_options_model->get_option_by_id($parent);
        $option_name = l('link_edit_ds', 'store') . ' - ' . $get_option['name_' . $lang_id];

        $this->view->assign('lang_data', $lang_data);
        $this->view->assign('langs', $this->pg_language->languages);
        $this->view->assign('current_lang_id', $lang_id);
        $this->view->assign('current_module_gid', 'store');
        $this->view->assign('current_gid', $parent);
        $this->view->assign('option_gid', $option_gid);
        $this->view->assign('add_flag', $add_flag);

        $this->view->setHeader($option_name);

        $this->view->render('options_ds_edit');
    }

    /**
     *  Shippings
     *
     *  @param string $filter
     *  @param integer $page
     *
     *  @return string
     */
    public function shipping($filter = "all", $page = 1)
    {
        $attrs            = $search_params    = $search_param     = array();
        $lang_id          = $this->pg_language->current_lang_id;
        $current_settings = isset($_SESSION["shippings_list"]) ? $_SESSION["shippings_list"] : array();
        if (!isset($current_settings["filter"])) {
            $current_settings["filter"] = $filter;
        }
        if (!isset($current_settings["page"])) {
            $current_settings["page"] = $page;
        }

        $this->load->model('store/models/Store_shippings_model');
        $filter_data["all"]               = $this->Store_shippings_model->get_shippings_count($search_params);
        $search_params["where"]["status"] = 0;
        $filter_data["inactive"]          = $this->Store_shippings_model->get_shippings_count($search_params);
        $search_params["where"]["status"] = 1;
        $filter_data["active"]            = $this->Store_shippings_model->get_shippings_count($search_params);

        switch ($filter) {
            case 'active' : $attrs["where"]['status'] = 1;
                break;
            case 'inactive' : $attrs["where"]['status'] = 0;
                break;
            case 'all' : break;
            default: $filter                   = $current_settings["filter"];
        }
        $current_settings["filter"] = $filter;

        $this->view->assign('search_param', $search_param);
        $this->view->assign('filter', $filter);
        $this->view->assign('filter_data', $filter_data);
        $current_settings["page"] = $page;

        $shippings_count = $filter_data[$filter];

        if (!$page) {
            $page = $current_settings["page"];
        }
        $items_on_page            = $this->pg_module->get_module_config('start', 'admin_items_per_page');
        $this->load->helper('sort_order');
        $page                     = get_exists_page_number($page, $shippings_count, $items_on_page);
        $current_settings["page"] = $page;

        $_SESSION["shippings_list"] = $current_settings;

        if ($shippings_count > 0) {
            $shippings = $this->Store_shippings_model->get_shippings_list($page, $items_on_page, array(), $attrs, array(), true, false, $lang_id);
            $this->view->assign("shippings", $shippings);
        }
        $this->load->helper("navigation");
        $url                      = site_url() . "admin/store/shipping/{$filter}/";
        $page_data                = get_admin_pages_data($url, $shippings_count, $items_on_page, $page, 'briefPage');
        $page_data["date_format"] = $this->pg_date->get_format('date_time_literal', 'st');
        $this->view->assign('page_data', $page_data);

        $this->view->setBackLink(site_url() . 'admin/store/index/');
        $this->view->setHeader(l('admin_header_shipping', 'store'));

        $this->view->render('shippings');
    }

    /**
     *  Status shipping
     *
     *  @param integer $id
     *  @param integer $status
     *
     *  @return void
     */
    public function status_shipping($id = null, $status = 0)
    {
        if (!empty($id)) {
            $this->load->model("store/models/Store_shippings_model");
            $this->Store_shippings_model->set_status_shipping($id, $status);
            if ($status) {
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_activate_shipping', 'store'));
            } else {
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_deactivate_shipping', 'store'));
            }
        }
        $cur_set = $_SESSION["shippings_list"];
        $url     = site_url() . "admin/store/shipping/{$cur_set["filter"]}/{$cur_set["page"]}";
        redirect($url);
    }

    /**
     *  Delete shipping
     *
     *  @param integer $id
     *
     *  @return void
     */
    public function delete_shipping($id = null)
    {
        if (!empty($id)) {
            $this->load->model("store/models/Store_shippings_model");
            $this->Store_shippings_model->delete_shipping($id);
            $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_delete_shipping', 'store'));
        }
        $cur_set = $_SESSION["shippings_list"];
        $url     = site_url() . "admin/store/shipping/{$cur_set["filter"]}/{$cur_set["page"]}";
        redirect($url);
    }

    /**
     *  Delete shippings by ajax
     *
     *
     *  @return void
     */
    public function ajax_delete_shippings()
    {
        $shipping_ids = $_POST['shipping_ids'];
        if (!empty($shipping_ids)) {
            $this->load->model("store/models/Store_shippings_model");
            foreach ($shipping_ids as $shipping_id) {
                $this->Store_shippings_model->delete_shipping($shipping_id);
            }
            exit(l('success_delete_shippings', 'store'));
        }
    }

    /**
     *  Shipping edit
     *
     *  @param integer $id
     *
     *  @return void
     */
    public function shipping_edit($id = null)
    {
        $this->load->model('store/models/Store_shippings_model');
        $langs    = $this->pg_language->languages;
        $shipping = array();
        if (isset($id)) {
            $shipping = $this->Store_shippings_model->get_shipping_by_id(intval($id));
            $header   = l('admin_header_shipping_change', 'store');
        } else {
            $header = l('admin_header_shipping_add', 'store');
        }
        if ($this->input->post('btn_save')) {
            $post_data = array(
                'country_id' => $this->input->post('country_id', true),
                'region_id'  => $this->input->post('region_id', true),
                'city_id'    => $this->input->post('city_id', true),
                'price'      => $this->input->post('price', true),
            );
            foreach ($langs as $key => $value) {
                $post_data['name_' . $value['id']] = $this->input->post('name_' . $value['id'], true);
            }
            foreach ($langs as $key => $value) {
                $post_data['description_' . $value['id']] = $this->input->post('description_' . $value['id'], true);
            }
            $validate_data = $this->Store_shippings_model->validate($post_data);
            if (!empty($validate_data["errors"])) {
                $errors   = $validate_data["errors"];
                $shipping = array_merge($shipping, $post_data);
            } else {
                $save_data                         = $validate_data["data"];
                $this->load->model('payments/models/Payment_currency_model');
                $base_currency                     = $this->Payment_currency_model->get_currency_default(true);
                $save_data["info"]["gid_currency"] = $base_currency["gid"];
                $id_shipping                       = $this->Store_shippings_model->save_shipping($id, $save_data);
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_update_service_data', 'services'));
                redirect(site_url() . "admin/store/shipping/");
            }
        }
        if (!empty($errors)) {
            $this->system_messages->addMessage(View::MSG_ERROR, $errors);
        }

        $this->view->assign('current_lang_id', $this->pg_language->current_lang_id);
        $this->view->assign('current_lang_code', $this->pg_language->get_lang_code_by_id($this->pg_language->current_lang_id));
        $this->view->assign('langs', $langs);
        $this->view->assign('back_url', site_url() . 'admin/store/shipping');
        $this->view->assign('shipping', $shipping);

        $this->view->setBackLink(site_url() . 'admin/store/shipping');
        $this->view->setHeader($header);

        $this->view->render('shipping_edit');
    }

    /**
     *  Orders list
     *
     *  @param string $filter
     *  @param string $order
     *  @param string $order_direction
     *  @param integer $page
     *
     *  @return string
     */
    public function orders($filter = "active", $order = "date_updated", $order_direction = "DESC", $page = 1)
    {
        $attrs            = $search_params    = $search_param     = array();
        $lang_id          = $this->pg_language->current_lang_id;
        $current_settings = isset($_SESSION["orders_list"]) ? $_SESSION["orders_list"] : array();
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
            $current_settings["status"] = $this->input->post('status', true);
        } elseif ($this->input->post('btn_reset', true)) {
            unset($current_settings["status"]);
        }
        $this->load->model('store/models/Store_orders_model');
        if (!empty($current_settings["status"])) {
            $search_param['status'] = $attrs["where"]["status"] = $search_params["where"]["status"] = $current_settings["status"];
        }
        $attrs["where"]['is_formed'] = $search_params["where"]["is_formed"]  = 1;
        $search_params["where"]["is_archive"] = 0;
        $filter_data["active"] = $this->Store_orders_model->get_orders_count($search_params);

        $search_params["where_sql"][] = " status NOT IN ('status_delivered', 'status_rejected_administrator') AND is_archive='0' ";
        $filter_data["opened"] = $this->Store_orders_model->get_orders_count($search_params);
        unset($search_params["where_sql"]);

        $search_params["where_in"]["status"]  = array('status_rejected_recipient', 'status_canceled_sender', 'status_rejected_administrator');
        $filter_data["closed"] = $this->Store_orders_model->get_orders_count($search_params);
        unset($search_params["where_in"]);

        $search_params["where"]["is_archive"] = 1;
        $filter_data["archive"] = $this->Store_orders_model->get_orders_count($search_params);

        switch ($filter) {
            case 'active' : $attrs["where"]["is_archive"] = 0;
                break;
            case 'opened' : $attrs["where_sql"][]         = " status NOT IN ('status_delivered', 'status_rejected_administrator') AND is_archive='0' ";
                break;
            case 'archive' : $attrs["where"]["is_archive"] = 1;
                break;
            case 'closed' : $attrs["where_in"]["status"]  = array('status_rejected_recipient', 'status_canceled_sender', 'status_rejected_administrator');
                break;
            default: $filter                       = $current_settings["filter"];
        }
        $current_settings["filter"] = $filter;

        $statuses = $this->get_statuses();
        $this->view->assign('statuses', $statuses);

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

        $orders_count = $filter_data[$filter];

        if (!$page) {
            $page = $current_settings["page"];
        }
        $items_on_page            = $this->pg_module->get_module_config('start', 'admin_items_per_page');
        $this->load->helper('sort_order');
        $page                     = get_exists_page_number($page, $orders_count, $items_on_page);
        $current_settings["page"] = $page;

        $_SESSION["orders_list"] = $current_settings;

        $sort_links = array(
            "customer_name" => site_url() . "admin/store/orders/{$filter}/customer_name/" . (($order != 'customer_name' xor $order_direction == 'DESC') ? 'ASC' : 'DESC') . "/",
        );
        $this->view->assign('sort_links', $sort_links);

        if ($orders_count > 0) {
            $orders = $this->Store_orders_model->get_orders_list($page, $items_on_page, array($order => $order_direction), $attrs, array(), true, false, $lang_id);
            $this->view->assign("orders", $orders);
        }
        $this->load->helper("navigation");
        $url                      = site_url() . "admin/store/orders/{$filter}/{$order}/{$order_direction}/";
        $page_data                = get_admin_pages_data($url, $orders_count, $items_on_page, $page, 'briefPage');
        $page_data["date_format"] = $this->pg_date->get_format('date_time_literal', 'st');
        $this->view->assign('page_data', $page_data);
        $this->view->setBackLink(site_url() . 'admin/store/index');
        $this->view->setHeader(l('admin_header_orders', 'store'));
        $this->view->render('orders');
    }

    /**
     *  Order view
     *
     *  @param integer $id
     *
     *  @return string
     */
    public function order($id = null)
    {
        if (isset($id)) {
            $langs = $this->pg_language->languages;
            $this->load->model('store/models/Store_orders_model');
            $this->load->model('store/models/Store_orders_log_model');
            $order = array();
            if (!empty($id)) {
                $order = $this->Store_orders_model->get_order_by_id($id);
            }
            if ($this->input->post('btn_save')) {
                $post_data      = array(
                    'status'      => $this->input->post('status', true),
                    'comment_log' => $this->input->post('comment_log', true),
                );
                $validate_order = $this->Store_orders_model->validate_order($id, $post_data);
                if (!empty($validate_order["errors"])) {
                    $errors = $validate_order["errors"];
                    $order  = array_merge($order, $validate_order["data"]);
                } else {
                    $save_data    = $validate_order["data"];
                    $this->Store_orders_model->save_order($save_data);
                    $validate_log = $this->Store_orders_log_model->validate_order_log($id, $post_data);
                    if (!empty($validate_log["errors"])) {
                        $this->system_messages->addMessage(View::MSG_ERROR, $validate_order["errors"]);
                    } else {
                        $this->Store_orders_log_model->save_order_log($validate_log['data']);
                    }
                    $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_update_service_data', 'services'));
                    redirect(site_url() . "admin/store/order/" . $id);
                }
            } elseif ($this->input->post('btn_save_history')) {
                $save_data['id']         = $id;
                $save_data['is_archive'] = 1;
                $this->Store_orders_model->save_order($save_data);
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_update_service_data', 'services'));
                redirect(site_url() . "admin/store/order/" . $id);
            } elseif ($this->input->post('btn_cancel')) {
                redirect(site_url() . "admin/store/order");
            }
            if (!empty($errors)) {
                $this->system_messages->addMessage(View::MSG_ERROR, $errors);
            }
            $statuses = $this->get_statuses();
            $this->view->assign('statuses', $statuses);

            $order_log = $this->Store_orders_log_model->get_log_by_id_order($id);
            $this->view->assign('order_log', $order_log);

            $page_data["date_format"] = $this->pg_date->get_format('date_time_literal', 'st');
            $this->view->assign('page_data', $page_data);
            $form_settings            = array('action' => site_url() . "admin/store/order/" . $id);
            $this->view->assign('form_settings', $form_settings);
            $this->view->assign('current_lang_id', $this->pg_language->current_lang_id);
            $this->view->assign('current_lang_code', $this->pg_language->get_lang_code_by_id($this->pg_language->current_lang_id));
            $this->view->assign('langs', $langs);
            $this->view->assign('back_url', site_url() . 'admin/store/orders');
            $this->view->assign('order', $order);

            $this->view->setBackLink(site_url() . 'admin/store/orders');
            $this->view->setHeader(l('admin_header_order', 'store'));

            $this->view->render('order_edit');
        } else {
            redirect(site_url() . "admin/store/orders/");
        }
    }

    /**
     *  Bestsellers list
     *
     *  @param integer $page
     *  @param string $filter
     *  @param string $order
     *  @param string $order_direction
     *
     *  @return string
     */
    public function bestsellers($page = 1, $filter = "all", $order = "priority", $order_direction = "ASC")
    {
        $search_param     = array();
        $lang_id          = $this->pg_language->current_lang_id;
        $current_settings = isset($_SESSION["bestsellers_list"]) ? $_SESSION["bestsellers_list"] : array();
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
            $current_settings["search_category"] = $this->input->post('category', true);
            $current_settings["search_text"]     = $this->input->post('val_text', true);            
        } elseif ($this->input->post('btn_reset', true)) {
            $current_settings["search_category"] = $current_settings["search_text"]     = '';
        }
        if (isset($current_settings["search_text"])) {
            $search_text_escape   = $this->db->escape("%" . $current_settings["search_text"] . "%");
            $attrs["where_sql"][] = "(name_" . $lang_id . " LIKE " . $search_text_escape . ")";
            $search_param         = array(
                'text' => $current_settings["search_text"]
            );
        }
        if (!empty($current_settings["search_category"])) {
            $attrs["where"]["id_category"] = intval($current_settings["search_category"]);
            $search_param                  = array(
                'category_id' => $current_settings["search_category"]
            );
        }
        $attrs["where"]["status"]        = 1;
        $attrs["where"]["is_bestseller"] = 1;
        $this->view->assign('search_param', $search_param);
        $this->load->model('store/models/Store_bestsellers_model');
        $bestsellers_count               = $this->Store_bestsellers_model->get_bestsellers_count($attrs);
        $items_on_page                   = $this->pg_module->get_module_config('start', 'admin_items_per_page');
        //load categories
        $this->load->model('store/models/Store_categories_model');
        $categories                      = $this->Store_categories_model->get_categories_list(null, null, null, array(), array(), false, false, $lang_id);
        $this->view->assign('categories', $categories);

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

        if (!$page) {
            $page = $current_settings["page"];
        }
        $this->load->helper('sort_order');
        $page                         = get_exists_page_number($page, $bestsellers_count, $items_on_page);
        $current_settings["page"]     = $page;
        $_SESSION["bestsellers_list"] = $current_settings;
        if ($bestsellers_count > 0) {
            $attrs["where"]["status"]        = 1;
            $attrs["where"]["is_bestseller"] = 1;
            $bestsellers                     = $this->Store_bestsellers_model->get_bestsellers_list($page, $items_on_page, $attrs, array($order => $order_direction), $lang_id);
            $this->view->assign("bestsellers", $bestsellers);
        }
        $this->load->helper("navigation");
        $url = site_url() . "admin/store/bestsellers/";

        $page_data                = get_admin_pages_data($url, $bestsellers_count, $items_on_page, $page, 'briefPage');
        $page_data["date_format"] = $this->pg_date->get_format('date_time_literal', 'st');
        $this->view->assign('page_data', $page_data);

        $this->view->setBackLink(site_url() . 'admin/store/index/');
        $this->view->setHeader(l('admin_header_bestsellers', 'store'));
        $this->view->render('bestsellers');
    }

    /**
     *  Return categories
     *
     *  @param array $categories
     *
     *  @return void
     */
    private function bestsellers_active_by_category($categories)
    {
        $this->load->model('store/models/Store_bestsellers_model');
        foreach ($categories as $key => $category) {
            $attrs["where"]["id_category"]                = $category["id"];
            $categories[$key]["bestsellers_active_count"] = $this->Store_bestsellers_model->get_bestsellers_count($attrs);
        }

        return $categories;
    }

    /**
     *  Bestsellers delete
     *
     *  @param integer $product_id
     *
     *  @return void
     */
    public function bestsellers_delete($product_id = null)
    {
        if (isset($product_id)) {
            $this->Store_products_model->delete_bestseller($product_id);
            $result["success"] = l('success_bestseller_delete', 'store');
            $this->system_messages->addMessage(View::MSG_SUCCESS, $result);
        } else {
            $result["error"] = l('error_bestseller_delete', 'store');
            $this->system_messages->addMessage(View::MSG_ERROR, $result);
        }
        redirect(site_url() . "admin/store/bestsellers/");
    }

    /**
     *  Store settings
     *
     *
     *  @return string
     */
    public function settings()
    {
        $data = $this->Store_model->get_settings();

        if ($this->input->post('btn_save')) {
            $post_data = array(
                "products_per_page"       => $this->input->post('products_per_page', true),
                "products_featured_items" => $this->input->post('products_featured_items', true),
                "products_similar_items"  => $this->input->post('products_similar_items', true),
                "products_block_items"    => $this->input->post('products_block_items', true),
                "use_store"               => $this->input->post('use_store', true),
                "shipping_page_gid"       => $this->input->post('shipping_page_gid', true),
            );

            $validate_data = $this->Store_model->validate_settings($post_data);
            if (!empty($validate_data["errors"])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data["errors"]);
            } else {
                $this->Store_model->set_settings($validate_data["data"]);
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_settings_save', 'store'));
                redirect(site_url() . "admin/store/settings");
            }

            $data = array_merge($data, $post_data);
        }

        $this->view->assign('data', $data);
        $this->view->setBackLink(site_url() . 'admin/store/index/');
        $this->view->setHeader(l('admin_header_settings', 'store'));
        $this->view->render('settings');
    }

    /**
     *  Return statuses
     *
     *
     *  @return array
     */
    private function get_statuses()
    {
        $statuses = $this->Store_orders_model->order_status4editing;

        return $statuses;
    }

    /**
     *  Session data
     *
     *  @param array $data
     *
     *  @return array
     */
    private function _set_session_data($data)
    {
        if (!empty($data)) {
            foreach ($data as $key => $val) {
                $_SESSION[$key] = $val;
            }
            $return['status'] = true;
        } else {
            $return['status'] = false;
        }

        return $return;
    }

    /**
     *  Return category select by ajax
     *
     *  @param integer $id_product
     *
     *  @return void
     */
    public function ajax_category_select($id_product = null)
    {
        $this->load->model('store/models/Store_categories_model');
        $lang_id                      = $this->pg_language->current_lang_id;
        $cat_attrs['where']['status'] = 1;
        $categories                   = $this->Store_categories_model->get_categories_list(null, null, null, $cat_attrs, array(), false, false, $lang_id);
        $category_ids                 = array();
        if (!empty($id_product)) {
            $category_ids = $this->Store_categories_model->get_categories_by_product_id(intval($id_product));
        }
        foreach ($categories as $key => $category) {
            $categories[$key]['checked'] = '';
            if (in_array($category['id'], $category_ids)) {
                $categories[$key]['checked'] = 'checked';
            }
        }
        $this->view->assign('categories', $categories);

        return $this->view->render('ajax_categories_select_block');
    }

    /**
     *  Return country select by ajax
     *
     *  @param integer $id_shipping
     *
     *  @return void
     */
    public function ajax_country_select($id_shipping = null)
    {
        $this->load->model('store/models/Store_shippings_model');
        $countries = array();
        $countries = $this->Store_shippings_model->get_countries_by_shipping_id(intval($id_shipping));
        $this->view->assign('countries', $countries);
        $this->view->assign('id_shipping', $id_shipping);

        return $this->view->render('ajax_countries_select_block');
    }

    /**
     *  Join lication by ajax
     *
     *  @param integer $id_shipping
     *
     *  @return void
     */
    public function ajax_join_location($id_shipping = 0)
    {
        $countries = $this->input->post('countries', true);
        if (!empty($countries)) {
            foreach ($countries as $key => $country) {
                $data["locations"][$key][$id_shipping]['id_country'] = $country;
            }
        }
        $data['id_shipping'] = intval($id_shipping);
        $this->view->assign('country_helper_data', $data);
        exit($this->view->fetch('helper_countries_select'));
    }

    /**
     *  Return location form by ajax
     *
     *  @param integer $id_shipping
     *
     *  @return void
     */
    public function ajax_get_location_form($id_shipping = null)
    {
        $this->view->render('ajax_country_form');
    }

    /**
     *  Return gid shipping by ajax
     *
     *
     *  @return void
     */
    public function ajax_gid_shipping()
    {
        $this->load->model('content/models/Content_model');
        $guides = array_unique($this->Content_model->get_gid_list());
        $this->view->assign('guides', $guides);

        return $this->view->render('ajax_guides_select_block');
    }

    /**
     *  Save media by ajax
     *
     *  @param string $type
     *  @param integer $id_product
     *
     *  @return void
     */
    public function ajax_save_product_media($type = 'images', $id_product)
    {
        $return = array('errors' => array(), 'warnings' => array(), 'name' => '');
        if ($type == 'images') {
            $validate_data                       = $this->Store_products_model->validate_image('multiUpload');
            $validate_data['data']["upload_gid"] = $this->Store_products_model->file_config_gid;
            if (empty($validate_data["errors"])) {
                $save_data = $this->Store_products_model->save_image_product($id_product, 'multiUpload');
            }
        } else {
            $post_data     = array("embed_code" => $this->input->post("embed_code", true));
            $validate_data = $this->Store_products_model->validate_video($post_data, 'videofile');
            if (empty($validate_data["errors"])) {
                $videofile = (!empty($validate_data['data']['video']) && $validate_data['data']['video'] === 'embed') ? '' : 'videofile';
                $save_data = $this->Store_products_model->save_video_product($id_product, $validate_data["data"], $videofile);
            }
        }
        if (!empty($validate_data['errors'])) {
            $return['errors'] = $validate_data['errors'];
        }
        if (empty($save_data['errors'])) {
            if (!empty($save_data['file'])) {
                $return['name'] = $save_data['file'];
            }
        } else {
            $return['errors'][] = $save_data['errors'];
        }
        $this->view->assign($return);

        return;
    }

    /**
     *  Return product photos by ajax
     *
     *  @param integer $product_id
     *  @param string $product_id
     *  @param string $product_id
     *
     *  @return void
     */
    public function ajax_get_product_photos($product_id, $param = 'all', $size = 'small')
    {
        $this->view->assign($this->Store_products_model->get_photo_list($product_id, $param, 'photo', $size));
    }

    /**
     *  Return product video by ajax
     *
     *  @param integer $product_id
     *
     *  @return void
     */
    public function ajax_get_product_video($product_id = null)
    {
        $this->view->assign($this->Store_products_model->get_video_list($product_id));
    }

    /**
     *  Return product media by ajax
     *
     *  @param integer $type
     *  @param integer $product_id
     *  @param integer $media_id
     *
     *  @return void
     */
    public function ajax_view_product_media($type, $product_id, $media_id)
    {
        $media_data               = array();
        $media_data['media_id']   = intval($media_id);
        $media_data['product_id'] = intval($product_id);
        $media_data['type']       = trim(strip_tags($type));
        $media_ids                = $this->Store_products_model->get_images_product($media_data['product_id']);
        $media_data['count']      = count($media_ids);
        $selections               = array();
        $this->load->model('Uploads_model');
        $upload_config            = $this->Uploads_model->get_config($this->Store_products_model->file_config_gid);
        foreach ($upload_config['thumbs'] as $thumb_config) {
            $selections[$thumb_config['prefix']] = array(
                'width'  => $thumb_config['width'],
                'height' => $thumb_config['height'],
            );
        }
        $this->view->assign('selections', $selections);
        $this->view->assign('media_data', $media_data);
        $this->view->output($this->view->fetchFinal('view_media'));
        $this->view->render();
    }

    /**
     *  Return media content by ajax
     *
     *  @param integer $media_id
     *  @param integer $product_id
     *  @param string $product_id
     *
     *  @return void
     */
    public function ajax_get_media_content($media_id, $product_id, $media_type = 'images')
    {
        $return           = array('content' => '', 'position_info' => '', 'media_type' => '');
        $media_id         = intval($media_id);
        $product_id       = intval($product_id);
        $place            = trim(strip_tags($this->input->post('place', true)));
        $without_position = intval($this->input->post('without_position'));

        $media = $this->Media_model->get_media_by_id($media_id, $product_id, $media_type);

        $return['content'] = $this->view->fetchFinal('media_content');
        if (!$without_position) {
            $return['position_info'] = $this->Media_model->get_media_position_info($media_id, $product_id);
        }
        $return['media_type'] = $media['upload_gid'];
        $this->view->assign($return);

        return;
    }

    /**
     *  Bestsellers save sorting  by ajax
     *
     *
     *  @return void
     */
    public function ajax_bestsellers_save_sorting()
    {
        $return = array("errors" => "", "success" => "");
        $sorter = $this->input->post("sorter");
        foreach ($sorter as $parent_str => $items_data) {
            foreach ($items_data as $item_str => $sort_index) {
                $option_gid               = str_replace("item_", "", $item_str);
                $sorter_data[$sort_index] = $option_gid;
            }
        }
        if (!empty($sorter_data)) {
            $this->load->model('store/models/Store_bestsellers_model');
            $this->Store_bestsellers_model->resorting($sorter_data);
            $return['success'][] = l('success_status', 'store');
        } else {
            $return['errors'][] = l('error_system', 'store');
        }
        $this->view->assign($return);

        return;
    }

    /**
     *  Save sorter by ajax
     *
     *  @param integer $parent
     *
     *  @return void
     */
    public function ajax_ds_item_save_sorter($parent)
    {
        $return = array("errors" => "", "success" => "");
        $gid    = 'store_optoins_' . $parent;
        $sorter = $this->input->post("sorter");
        foreach ($sorter as $parent_str => $items_data) {
            foreach ($items_data as $item_str => $sort_index) {
                $option_gid               = str_replace("item_", "", $item_str);
                $sorter_data[$sort_index] = $option_gid;
            }
        }

        if (empty($sorter_data)) {
            $return['errors'][] = l('error_system', 'store');
        } else {
            $this->pg_language->ds->set_reference_sorter('store', $gid, $sorter_data);
            $return['success'][] = l('success_status', 'store');
        }
        $this->view->assign($return);

        return;
    }
    
    /**
     *  Delete ds item by ajax
     *
     *  @param integer $parent
     *  @param integer $item
     *
     *  @return void
     */
    public function ds_item_delete($parent, $item, $lang_id)
    {
        $gid = 'store_optoins_' . $parent;
        if ($gid && $item) {
            foreach ($this->pg_language->languages as $lid => $lang) {
                $reference = $this->pg_language->ds->get_reference('store', $gid, $lid);
                if (isset($reference["option"][$item])) {
                    unset($reference["option"][$item]);
                    $this->pg_language->ds->set_module_reference('store', $gid, $reference, $lid);
                }
            }
        }

        redirect(site_url() . 'admin/store/option_fields/' . $parent . '/' . $lang_id);
    }

    /**
     *  Delete ds item by ajax
     *
     *  @param integer $parent
     *  @param integer $item
     *
     *  @return void
     */
    public function ajax_ds_item_delete($parent, $item)
    {
        $gid = 'store_optoins_' . $parent;
        if ($gid && $item) {
            foreach ($this->pg_language->languages as $lid => $lang) {
                $reference = $this->pg_language->ds->get_reference('store', $gid, $lid);
                if (isset($reference["option"][$item])) {
                    unset($reference["option"][$item]);
                    $this->pg_language->ds->set_module_reference('store', $gid, $reference, $lid);
                }
            }
        }

        return;
    }

    /**
     *  Return options by ajax
     *
     *
     *  @return void
     */
    public function ajax_load_options()
    {
        $category_ids = array_unique($this->input->post('ids', true));
        $this->load->model('store/models/Store_categories_model');
        $options_ids  = $this->Store_categories_model->get_categories_options($category_ids);
        if (!empty($options_ids)) {
            $options = $this->Store_products_model->get_options($options_ids);
            $this->view->assign('options', $options);
            exit($this->view->fetchFinal('ajax_load_options', 'admin'));
        }

        return false;
    }

    /**
     *  Move to archive by ajax
     *
     *
     *  @return void
     */
    public function ajax_move2archive()
    {
        $order_ids = $this->input->post('order_ids', true);
        if (!empty($order_ids)) {
            $this->load->model('store/models/Store_orders_model');
            $options_ids         = $this->Store_orders_model->set_order_archive($order_ids);
            $return['success'][] = l('success_move2archive', 'store');
        } else {
            $return['errors'][] = l('error_system', 'store');
        }
        $this->view->assign($return);

        return;
    }

    /**
     *  Order status by ajax
     *
     *
     *  @return void
     */
    public function ajax_change_status_orders()
    {
        $return    = array('status' => 0, 'errors' => '');
        $order_ids = $this->input->post('order_ids', true);
        $post_data = array(
            'status' => $this->input->post('status', true),
        );
        if (!empty($order_ids)) {
            $this->load->model('store/models/Store_orders_model');
            $this->load->model('store/models/Store_orders_log_model');
            foreach ($order_ids as $id) {
                $validate_order = $this->Store_orders_model->validate_order($id, $post_data);
                if (!empty($validate_order["errors"])) {
                    $return['errors'][] = $validate_order["errors"];
                    $order              = array_merge($order, $validate_order["data"]);
                } else {
                    $save_data    = $validate_order["data"];
                    $this->Store_orders_model->save_order($save_data);
                    $validate_log = $this->Store_orders_log_model->validate_order_log($id, $post_data);
                    if (!empty($validate_log["errors"])) {
                        $return['errors'][] = $validate_order["errors"];
                    } else {
                        $this->Store_orders_log_model->save_order_log($validate_log['data']);
                        $return["status"] = 1;
                    }
                    $return["success"][] = l('success_update_service_data', 'services');
                }
            }
        }
        $this->view->assign($return);

        return;
    }

    /**
     *  Update session data by ajax
     *
     *
     *  @return void
     */
    public function ajax_set_session_data()
    {
        $post_data = array();
        foreach ($_POST as $key => $valye) {
            $post_data[$key] = $_POST[$key];
        }
        $return = $this->_set_session_data($post_data);
        $this->view->assign($return);

        return;
    }

}
