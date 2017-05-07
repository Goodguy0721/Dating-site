<?php

namespace Pg\Modules\Store\Models;

/**
 * Store main model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Nikita Savanaev <nsavanaev@pilotgroup.net>
 **/
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Store_model extends \Model
{
    const MODULE_GID = 'store';
    
    const EVENT_ORDER_CHANGED = 'store_order_changed';
    
    const TYPE_STORE_ORDER = 'store_order';
    
    const STATUS_ORDER_ADDED = 'order_added';
    const STATUS_ORDER_SAVED = 'order_saved';
    const STATUS_ORDER_DELETED = 'order_deleted';
    
    public $dashboard_events = [
        self::EVENT_ORDER_CHANGED,
    ];

    const DB_DATE_FORMAT = 'Y-m-d H:i:s';
    const DB_DATE_FORMAT_SEARCH = 'Y-m-d H:i';
    const DB_DEFAULT_DATE = '0000-00-00 00:00:00';

    protected $CI;
    protected $DB;

    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
        $this->DB = &$this->CI->db;
    }

    public function validate_settings($data)
    {
        $return = array("errors" => array(), "data" => array());

        if (isset($data['products_per_page'])) {
            $return["data"]["products_per_page"] = intval($data["products_per_page"]);
            if ($return["data"]["products_per_page"] <= 0) {
                $return["errors"][] = l("error_products_per_page_incorrect", "store");
            }
        }
        if (isset($data['products_featured_items'])) {
            $return["data"]["products_featured_items"] = intval($data["products_featured_items"]);
            if ($return["data"]["products_featured_items"] <= 0) {
                $return["errors"][] = l("error_products_featured_items_incorrect", "store");
            }
        }
        if (isset($data['products_similar_items'])) {
            $return["data"]["products_similar_items"] = intval($data["products_similar_items"]);
            if ($return["data"]["products_similar_items"] <= 0) {
                $return["errors"][] = l("error_products_similar_items_incorrect", "store");
            }
        }
        if (isset($data['products_block_items'])) {
            $return["data"]["products_block_items"] = intval($data["products_block_items"]);
            if ($return["data"]["products_block_items"] <= 0) {
                $return["errors"][] = l("error_products_block_items_incorrect", "store");
            }
        }
        if (isset($data['use_store'])) {
            $return["data"]["use_store"] = $data["use_store"] ? 1 : 0;
        }
        if (isset($data['shipping_page_gid'])) {
            $return["data"]["shipping_page_gid"] = trim(strip_tags($data["shipping_page_gid"]));
            if (empty($return["data"]["shipping_page_gid"])) {
                $return["errors"][] = l("error_shipping_page_gid_incorrect", "store");
            }
        }

        return $return;
    }

    public function get_settings()
    {
        $data = array(
            "products_per_page"       => $this->CI->pg_module->get_module_config('store', 'products_per_page'),
            "products_featured_items" => $this->CI->pg_module->get_module_config('store', 'products_featured_items'),
            "products_similar_items"  => $this->CI->pg_module->get_module_config('store', 'products_similar_items'),
            "products_block_items"    => $this->CI->pg_module->get_module_config('store', 'products_block_items'),
            "use_store"               => $this->CI->pg_module->get_module_config('store', 'use_store'),
            "shipping_page_gid"       => $this->CI->pg_module->get_module_config('store', 'shipping_page_gid'),
        );

        return $data;
    }

    public function set_settings($data)
    {
        foreach ($data as $setting => $value) {
            $this->CI->pg_module->set_module_config('store', $setting, $value);
        }

        return;
    }

    public function get_seo_settings($method = '', $lang_id = '')
    {
        if (!empty($method)) {
            return $this->_get_seo_settings($method, $lang_id);
        } else {
            $actions = array('product', 'cart', 'category', 'index', 'order_list', 'preorder', 'order', 'shipping_address');
            $return = array();
            foreach ($actions as $action) {
                $return[$action] = $this->_get_seo_settings($action, $lang_id);
            }

            return $return;
        }
    }

    private function _get_seo_settings($method, $lang_id = '')
    {
        switch ($method) {
            case 'index':
                return array(
                    "templates" => array(),
                    "url_vars"  => array(),
                ); break;
            case 'search':
                return array(
                    "templates" => array(),
                    "url_vars"  => array(),
                ); break;
            case 'product':
                return array(
                    "templates" => array("name"),
                    "url_vars"  => array(
                        "id" => array("id" => 'literal', "gid" => 'literal'),
                    ),
                    "optional" => array(
                        array("id" => 'literal', "category_name" => "literal"),
                    ),
                ); break;
            case 'cart':
                return array(
                    "templates" => array(),
                    "url_vars"  => array(),
                ); break;
            case 'category':
                return array(
                    "templates" => array("name"),
                    "url_vars"  => array(
                        "id" => array("id" => 'literal', "gid" => 'literal'),
                    ),
                ); break;
            case 'order_list':
                return array(
                    "templates" => array(),
                    "url_vars"  => array(),
                ); break;
            case 'preorder':
                return array(
                    "templates" => array(),
                    "url_vars"  => array(
                        "id" => array("id" => 'literal', "code" => 'literal'),
                    ),
                ); break;
            case 'order':
                return array(
                    "templates" => array(),
                    "url_vars"  => array(
                        "id" => array("id" => 'literal', "code" => 'literal'),
                    ),
                ); break;
            case 'shipping_address':
                return array(
                    "templates" => array(),
                    "url_vars"  => array(
                        "id" => array("id" => 'literal'),
                    ),
                ); break;
        }
    }

    public function request_seo_rewrite($var_name_from, $var_name_to, $value)
    {
        if ($var_name_from == $var_name_to) {
            return $value;
        }
        switch ($this->CI->uri->rsegments[2]) {
            case 'category':
                if ($var_name_from == "gid" && $var_name_to == "id") {
                    $this->CI->load->model('store/models/Store_categories_model');
                    $page_data = $this->CI->Store_categories_model->get_category_by_gid($value);
                    if (!empty($page_data)) {
                        $value = $page_data["id"];
                    }
                }
            break;
            case 'product':
                if ($var_name_from == "gid" && $var_name_to == "id") {
                    $this->CI->load->model('store/models/Store_products_model');
                    $value = $this->CI->Store_products_model->get_product_id_by_gid($value);
                }
            break;
            case 'preorder':
                if ($var_name_from == "code" && $var_name_to == "id") {
                    $this->CI->load->model('store/models/Store_orders_model');
                    $value = $this->CI->Store_orders_model->get_order_id_by_code($value);
                }
            break;
            case 'order':
                if ($var_name_from == "code" && $var_name_to == "id") {
                    $this->CI->load->model('store/models/Store_orders_model');
                    $value = $this->CI->Store_orders_model->get_order_id_by_code($value);
                }
            break;
        }

        return $value;
    }

    public function get_sitemap_xml_urls()
    {
        $this->CI->load->helper('seo');
        $return = array();

        return $return;
    }

    public function get_sitemap_urls()
    {
        $this->CI->load->helper('seo');
        $auth = $this->CI->session->userdata("auth_type");
        $block = array();

        $block[] = array(
            "name"      => l('header_main_sections', 'store'),
            "link"      => rewrite_link('store', 'index'),
            "clickable" => ($auth == "user"),
            "items"     => array(
                array(
                    "name"      => l('cart', 'store'),
                    "link"      => rewrite_link('store', 'cart'),
                    "clickable" => ($auth == "user"),
                ),
                array(
                    "name"      => l('header_product_search', 'store'),
                    "link"      => rewrite_link('store', 'search'),
                    "clickable" => ($auth == "user"),
                ),
                array(
                    "name"      => l('header_order_list', 'store'),
                    "link"      => rewrite_link('store', 'order_list'),
                    "clickable" => ($auth == "user"),
                ),
            ),
        );

        return $block;
    }

    public function _banner_available_pages()
    {
        $return[] = array("link" => "store/product", "name" => l('field_products', 'store'));
        $return[] = array("link" => "store/cart", "name" => l('cart', 'store'));
        $return[] = array("link" => "store/category", "name" => l('header_product_categories', 'store'));
        $return[] = array("link" => "store/index", "name" => l('header_main_sections', 'store'));

        return $return;
    }

    /**
     *  Module category action
     *
     *  @return array
     */
    public function moduleCategoryAction()
    {
        $action = array(
            'name'   => l('btn_buy_gift', 'store'),
            'helper' => 'btn_store',
        );

        return $action;
    }
    
    public function formatDashboardRecords($data) 
    {        
        $this->CI->load->model('store/models/Store_orders_model');
        $data = $this->CI->Store_orders_model->format_orders($data);

        foreach ($data as $key => $value) {
            $this->CI->view->assign('data', $value);                
            $data[$key]['content'] = $this->CI->view->fetch('dashboard', 'admin', 'store');
        }
        
        return $data;
    }
    
    public function getDashboardData($order_id, $status) 
    {
        if ($status != self::STATUS_ORDER_ADDED && $status != self::STATUS_ORDER_SAVED) {
            return false;
        }
        
        $this->CI->load->model('store/models/Store_orders_model');
        $order_data = $this->CI->Store_orders_model->get_order_by_id($order_id, false);
        $order_data['dashboard_header'] = 'header_store_order';
        $order_data['dashboard_action_link'] = 'admin/store/orders';
        $order_data['dashboard_new_object'] = $status == self::STATUS_ORDER_ADDED;
        
        return $order_data;
    }
}
