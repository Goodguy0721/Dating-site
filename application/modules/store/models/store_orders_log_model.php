<?php

/**
 * Store orders log model
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
if (!defined('TABLE_STORE_ORDERS_LOG')) {
    define('TABLE_STORE_ORDERS_LOG', DB_PREFIX . 'store_orders_log');
}

class Store_orders_log_model extends Model
{
    const DB_DATE_FORMAT = 'Y-m-d H:i:s';
    const DB_DATE_FORMAT_SEARCH = 'Y-m-d H:i';
    const DB_DEFAULT_DATE = '0000-00-00 00:00:00';

    private $CI;
    private $DB;
    private $fields = array(
        'id_order',
        'status',
        'status_old',
        'status_new',
        'comment',
        'date_created',
    );

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
        $this->DB = &$this->CI->db;
        $this->DB->memcache_tables(array(TABLE_STORE_ORDERS_LOG));
    }

    public function get_log_by_id_order($order_id)
    {
        $result = $this->DB->select(implode(", ", $this->fields))
                ->from(TABLE_STORE_ORDERS_LOG)
                ->where("id_order", $order_id)
                ->get()->result_array();
        if (empty($result)) {
            return false;
        } else {
            return $this->format_order_log($result);
        }
    }

    public function get_orders_log_list($page = null, $items_on_page = null, $order_by = null, $params = array())
    {
        $this->DB->select(implode(", ", $this->fields));
        $this->DB->from(TABLE_STORE_ORDERS_LOG);

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
            return $this->format_orders_log($results);
        }

        return array();
    }

    private function get_last_status($order_id)
    {
        $result = $this->DB->select('status')
                ->from(TABLE_STORE_ORDERS_LOG)
                ->where("id_order", $order_id)
                ->order_by('date_created', 'DESC')
                ->limit(1)
                ->get()->result_array();
        if (empty($result)) {
            return false;
        } else {
            return l($result[0]['status'], 'store');
        }
    }

    public function validate_order_log($order_id, $data)
    {
        $return = array("errors" => array(), "data" => array());

        if (isset($order_id)) {
            $return["data"]["id_order"] = intval($order_id);
        }

        if (isset($data['status'])) {
            $return["data"]["status"] = strip_tags($data['status']);
            $return["data"]["status_new"] = l($return["data"]["status"], 'store');
            $return["data"]["status_old"] = $this->get_last_status($return["data"]["id_order"]);
        }

        if (isset($data['comment_log'])) {
            $return["data"]["comment"] = strip_tags($data['comment_log']);
        }

        return $return;
    }

    public function format_order_log($data)
    {
        if ($data) {
            $return = $this->format_orders_log($data);
            $resilt = $return[$data[0]['id_order']];

            return $resilt;
        }

        return array();
    }

    public function format_orders_log($data)
    {
        foreach ($data as $key => $log) {
            if (!empty($log['status'])) {
                $result[$log['id_order']][$key]['id'] = $log['id_order'];
                $result[$log['id_order']][$key]['date'] = $log['date_created'];
                $result[$log['id_order']][$key]['status'] = l($log['status'], 'store');
                $result[$log['id_order']][$key]['comment'] = $log['comment'];
            }
        }

        return $result;
    }

    public function save_order_log($params)
    {
        $params['date_created'] = date(self::DB_DATE_FORMAT);
        $this->DB->insert(TABLE_STORE_ORDERS_LOG, $params);
    }

    public function delete_order_log($id)
    {
        if (isset($id)) {
            $this->DB->where('id_order', $id);
            $this->DB->delete(TABLE_STORE_ORDERS_LOG);
        }
    }
}
