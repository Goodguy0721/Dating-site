<?php

/**
 * Store cart model
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
if (!defined('TABLE_STORE_CART')) {
    define('TABLE_STORE_CART', DB_PREFIX . 'store_cart');
}
if (!defined('TABLE_STORE_CART_PRODUCTS')) {
    define('TABLE_STORE_CART_PRODUCTS', DB_PREFIX . 'store_cart_products');
}

class Store_cart_model extends Model
{
    private $CI;
    private $DB;
    private $fields = array(
        'id',
        'id_user',
        'products_count',
        'total',
        'gid_currency',
    );
    private $fields_prod = array(
        'id',
        'id_cart',
        'id_recipient',
        'id_product',
        'price',
        'gid_currency',
        'count',
        'data',
    );
    private $id_cart;

    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
        $this->DB = &$this->CI->db;
        $this->DB->memcache_tables(array(TABLE_STORE_CART, TABLE_STORE_CART_PRODUCTS));
        $this->id_cart = $this->get_id_cart();
    }

    private function get_id_cart()
    {
        $user_id = $this->session->userdata('user_id');
        $result = $this->DB->select("id")->from(TABLE_STORE_CART)->where("id_user", $user_id)->get()->result_array();
        foreach ($result as $row) {
            $return = $row['id'];
        }
        if (!isset($return)) {
            $return = $this->set_cart();
        }

        return $return;
    }

    public function get_cart()
    {
        $user_id = $this->session->userdata('user_id');
        $result = $this->DB->select(implode(", ", $this->fields))
                ->from(TABLE_STORE_CART)
                ->where("id_user", $user_id)
                ->get()->result_array();

        return $this->format_cart($result[0]);
    }

    public function get_products_by_cart($page = null, $items_on_page = null)
    {
        $page = intval($page) ? intval($page) : 1;
        $result = $this->DB->select(implode(", ", $this->fields_prod))
                ->from(TABLE_STORE_CART_PRODUCTS)
                ->where("id_cart", $this->id_cart)
                ->limit($items_on_page, $items_on_page * ($page - 1))
                ->get()->result_array();

        return $this->format_cart_items($result);
    }

    public function get_items_cart($ids = array())
    {
        if (!empty($ids)) {
            $params['where_in']['id'] = $ids;
            $cart_items = $this->get_cart_products($params, false);

            return $this->format_cart_items($cart_items);
        } else {
            return false;
        }
    }

    private function get_cart_products($params = array(), $formatted = true)
    {
        $results = array();
        $this->DB->select(implode(", ", $this->fields_prod));
        $this->DB->from(TABLE_STORE_CART_PRODUCTS);
        if (isset($params["where"]) && is_array($params["where"])) {
            foreach ($params["where"] as $field => $value) {
                if (in_array($field, $this->fields_prod)) {
                    $this->DB->where($field, $value);
                }
            }
        }
        if (isset($params["where_in"]) && is_array($params["where_in"])) {
            foreach ($params["where_in"] as $field => $value) {
                $this->DB->where_in($field, $value);
            }
        }
        $results = $this->DB->get()->result_array();
        if ($formatted) {
            return $this->format_cart_items($results);
        } else {
            return $results;
        }
    }

    public function unique_products_count($params = array())
    {
        return $this->DB->where('id_cart', $this->id_cart, false)->count_all_results(TABLE_STORE_CART_PRODUCTS);
    }

    public function format_cart_items($data)
    {
        $lang_id = $this->CI->pg_language->current_lang_id;
        $this->CI->load->model('Store_products_model');
        foreach ($data as $key => $item) {
            $item['data'] = unserialize($item['data']);
            $item['price'] = (double) $item['price'];
            $item['product'] = $this->CI->Store_products_model->get_product_by_id($item['id_product'], $lang_id);
            $data[$key] = $item;
        }

        return $data;
    }

    public function format_cart_item($data)
    {
        if ($data) {
            $return = $this->format_cart_items(array(0 => $data));

            return $return[0];
        }

        return array();
    }

    public function format_carts($data)
    {
        foreach ($data as $key => $item) {
            $item['total'] = (double) $item['total'];
            $data[$key] = $item;
        }

        return $data;
    }

    public function format_cart($data)
    {
        if ($data) {
            $return = $this->format_carts(array(0 => $data));

            return $return[0];
        }

        return array();
    }

    public function validate($product_data, $data)
    {
        $return = array("errors" => array(), "data" => array());
        foreach ($data["option"] as $key => $option) {
            $data["option"][$key] = serialize(array($option => $option));
        }
        if (!empty($data["option"])) {
            $data['data'] = serialize($data["option"]);
        }
        $params['where']['id_recipient'] = $data['id_recipient'];
        $params['where']['id_product'] = $data['id_product'];
        $params['where']['data'] = $data['data'];
        $cart_data = $this->get_cart_products($params);
        $return["data"]['id'] = !empty($cart_data['id']) ? $cart_data['id'] : null;
        $return["data"]['id_cart'] = $this->id_cart;
        $return["data"]['count'] = (isset($cart_data['count']) ? intval($cart_data['count']) : 0) + (!empty($data['count']) ? intval($data['count']) : 1);
        $this->CI->load->model('payments/models/Payment_currency_model');
        $base_currency = $this->CI->Payment_currency_model->get_currency_default(true);
        $return["data"]['gid_currency'] = $base_currency['gid'];
        if (isset($data["id_recipient"])) {
            $return["data"]["id_recipient"] = intval($data["id_recipient"]);
        }
        if (isset($data["id_product"])) {
            $return["data"]["id_product"] = intval($data["id_product"]);
        }
        if (isset($product_data["price_reduced"])) {
            $return["data"]["price"] = floatval($product_data["price_reduced"]);
        }
        if (!empty($data["option"])) {
            $return["data"]["data"] = $data['data'];
        }

        return $return;
    }

    public function add_to_cart($cart_data)
    {
        $return = array("errors" => array(), "success" => array());
        if (is_null($cart_data['id'])) {
            $this->DB->insert(TABLE_STORE_CART_PRODUCTS, $cart_data);
            $id_cart_data = $this->DB->insert_id();
        } else {
            $this->DB->where('id', $cart_data['id']);
            $this->DB->update(TABLE_STORE_CART_PRODUCTS, $cart_data);
        }
        $result = $this->recalculation_cart();
        if (empty($result)) {
            $return["errors"][] = l('error_add_to_cart', 'store');

            return $return;
        } else {
            return $result;
        }
    }

    private function set_cart($id = null, $attrs = array())
    {
        if (is_null($id)) {
            $attrs['id_user'] = $this->session->userdata('user_id');
            $this->DB->insert(TABLE_STORE_CART, $attrs);
            $id = $this->DB->insert_id();
        } else {
            $this->DB->where('id', $id);
            $this->DB->update(TABLE_STORE_CART, $attrs);
        }

        return $id;
    }

    private function recalculation_cart()
    {
        $data = array('total' => 0, 'products_count' => 0, 'gid_currency' => '');
        $params['where']['id_cart'] = $this->id_cart;
        $cart_data = $this->get_cart_products($params, false);
        foreach ($cart_data as $key => $value) {
            $data['total'] += $value['count'] * $value['price'];
            $data['products_count'] += $value['count'];
            $data['gid_currency'] = $value['gid_currency'];
        }
        $attrs = array(
            'products_count' => $data['products_count'],
            'total'          => $data['total'],
            'gid_currency'   => $data['gid_currency'],
        );
        $this->set_cart($this->id_cart, $attrs);

        return $attrs;
    }

    public function delete_cart_products($ids = array())
    {
        if (!empty($ids)) {
            $this->DB->where_in('id', $ids);
            $this->DB->delete(TABLE_STORE_CART_PRODUCTS);

            return $this->recalculation_cart();
        }
    }
}
