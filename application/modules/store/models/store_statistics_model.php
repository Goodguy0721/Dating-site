<?php

/**
 * Store statistics model
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
if (!defined('TABLE_STORE_STATISTICS_PRODUCT')) {
    define('TABLE_STORE_STATISTICS_PRODUCT', DB_PREFIX . 'store_statistics_products');
}
if (!defined('TABLE_STORE_STATISTICS_RECIPIENTS')) {
    define('TABLE_STORE_STATISTICS_RECIPIENTS', DB_PREFIX . 'store_statistics_recipients');
}

class Store_statistics_model extends Model
{
    private $CI;
    private $DB;
    private $fields_rec = array(
        'id',
        'id_user',
        'id_recipient',
        'total_orders',
        'total_price',
        'total_count',
    );
    private $fields_prod = array(
        'id',
        'id_product',
        'orders_products',
    );

    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
        $this->DB = &$this->CI->db;
        $this->DB->memcache_tables(array(TABLE_STORE_STATISTICS_PRODUCT, TABLE_STORE_STATISTICS_RECIPIENTS));
    }

    public function get_statistics_by_product($id_product)
    {
        $result = $this->DB->select('orders_products')
                ->from(TABLE_STORE_STATISTICS_PRODUCT)
                ->where("id_product", $id_product)
                ->get()->result_array();
        if (empty($result)) {
            return false;
        } else {
            return $this->format_statistics_product($result[0]);
        }
    }

    public function get_statistics_by_recipient($id_recipient)
    {
        $result = $this->DB->select(implode(", ", $this->fields_rec))
                ->from(TABLE_STORE_STATISTICS_RECIPIENTS)
                ->where("id_recipient", $id_recipient)
                ->get()->result_array();
        if (empty($result)) {
            return false;
        } else {
            return $result[0];
        }
    }

    public function get_statistics_by_user($id_user)
    {
        $result = $this->DB->select(implode(", ", $this->fields_rec))
                ->from(TABLE_STORE_STATISTICS_RECIPIENTS)
                ->where("id_user", $id_user)
                ->get()->result_array();
        if (empty($result)) {
            return false;
        } else {
            return $result[0];
        }
    }

    public function get_list_statistics($page = null, $items_on_page = null, $order_by = null, $params = array(), $filter_object_ids = array())
    {
        $this->DB->select(implode(", ", $this->fields_rec));
        $this->DB->from(TABLE_STORE_STATISTICS_RECIPIENTS);

        if (isset($params["where"]) && is_array($params["where"]) && count($params["where"])) {
            foreach ($params["where"] as $field => $value) {
                $this->DB->where($field, $value);
            }
        }

        if (isset($params["like"]) && is_array($params["like"]) && count($params["like"])) {
            foreach ($params["like"] as $field => $value) {
                $this->DB->like($field, $value);
            }
        }

        if (isset($params["where_in"]) && is_array($params["where_in"]) && count($params["where_in"])) {
            foreach ($params["where_in"] as $field => $value) {
                $this->DB->where_in($field, $value);
            }
        }

        if (isset($params["where_sql"]) && is_array($params["where_sql"]) && count($params["where_sql"])) {
            foreach ($params["where_sql"] as $value) {
                $this->DB->where($value, null, false);
            }
        }

        if (!empty($filter_object_ids) && is_array($filter_object_ids)) {
            $this->DB->where_in("id", $filter_object_ids);
        }

        if (is_array($order_by) && count($order_by) > 0) {
            foreach ($order_by as $field => $dir) {
                $this->DB->order_by($field . " " . $dir);
            }
        }

        if (!is_null($page)) {
            $page = intval($page) ? intval($page) : 1;
            $this->DB->limit($items_on_page, $items_on_page * ($page - 1));
        }
        $results = $this->DB->get()->result_array();
        if (!empty($results) && is_array($results)) {
            return $results;
        }

        return array();
    }

    public function format_statistics_product($data)
    {
        return unserialize($data['orders_products']);
    }

    public function save_product($data, $count)
    {
        for ($i = 0; $i < $count; ++$i) {
            $products[] = $data[$i]['id_product'];
        }
        foreach ($data as $val) {
            $list = $products;
            $key = array_search($val['id_product'], $list);
            if ($key !== false) {
                unset($list[$key]);
            }
            if ($this->get_statistics_by_product($val['id_product'])) {
                $attrs['orders_products'] = serialize(array_unique(array_merge($list, $this->get_statistics_by_product($val['id_product']))));
                $this->DB->where('id_product', $val['id_product']);
                $this->DB->update(TABLE_STORE_STATISTICS_PRODUCT, $attrs);
            } else {
                $attrs['id_product'] = $val['id_product'];
                $attrs['orders_products'] = serialize($list);
                $this->DB->insert(TABLE_STORE_STATISTICS_PRODUCT, $attrs);
                $this->DB->insert_id();
            }
            unset($list);
            unset($attrs);
        }
    }

    public function save_recipient($data)
    {
        $recipient = $this->get_statistics_by_recipient($data['id_user']);
        if (!empty($recipient) && $recipient['id_user'] == $data['id_customer']) {
            $params['total_orders'] = $recipient['total_orders'] + 1;
            $params['total_price'] = $recipient['total_price'] + $data['total'];
            $params['total_count'] = $recipient['total_count'] + $data['products_count'];
            $this->DB->where('id', $recipient['id']);
            $this->DB->update(TABLE_STORE_STATISTICS_RECIPIENTS, $params);
        } else {
            $params['id_user'] = $data['id_customer'];
            $params['id_recipient'] = $data['id_user'];
            $params['total_orders'] = 1;
            $params['total_price'] = $data['total'];
            $params['total_count'] = $data['products_count'];
            $this->DB->insert(TABLE_STORE_STATISTICS_RECIPIENTS, $params);
        }
    }
}
