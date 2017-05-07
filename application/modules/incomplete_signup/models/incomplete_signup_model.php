<?php

/**
 * Incomplete_signup main model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 **/
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

define('UNREGISTERED_TABLE', DB_PREFIX . 'unregistered');

class Incomplete_signup_model extends Model
{
    const DB_DATE_FORMAT = 'Y-m-d H:i:s';
    const DB_DATE_FORMAT_SEARCH = 'Y-m-d H:i';
    const DB_DEFAULT_DATE = '0000-00-00 00:00:00';

    public $CI;
    public $DB;

    public $fields = array(
        'id',
        'email',
        'nickname',
        'user_type',
        'looking_user_type',
        'fname',
        'sname',
        'lang_id',
        'group_id',
        'user_logo',
        'id_country',
        'id_region',
        'id_city',
        'birth_date',
        'ip',
        'date_created',
    );

    public $fields_all = array();

    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
        $this->DB = &$this->CI->db;
        $this->fields_all = $this->fields;
    }

    public function get_users_count($params = array(), $filter_object_ids = null)
    {
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

        if (isset($filter_object_ids) && is_array($filter_object_ids) && count($filter_object_ids)) {
            $this->DB->where_in("id", $filter_object_ids);
        }
        $result = $this->DB->count_all_results(UNREGISTERED_TABLE);

        return $result;
    }

    public function get_unregistered_users($page = null, $items_on_page = null, $order_by = null, $params = array(), $filter_object_ids = array(), $formatted = true, $safe_format = false, $lang_id = '')
    {
        $this->DB->from(UNREGISTERED_TABLE);

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
                if (in_array($field, $this->fields_all) || $field == 'fields') {
                    $this->DB->order_by($field . " " . $dir);
                }
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

    public function get_unregistered_user_by_id($user_id)
    {
        $result = $this->DB->select(implode(", ", $this->fields_all))
                ->from(UNREGISTERED_TABLE)
                ->where("id", $user_id)
                ->get()->result_array();
        if (empty($result)) {
            return false;
        } else {
            return $result[0];
        }
    }

    public function check_email_exists($email)
    {
        $this->DB->where('email', $email);
        $this->DB->select('id');

        $query = $this->DB->get(UNREGISTERED_TABLE);
        if ($query->num_rows() > 0) {
            return $query->row()->id;
        }

        return false;
    }

    public function save_unregistered_user($id, $attrs = array())
    {
        $data = array();
        foreach ($this->fields as $field) {
            if ($attrs[$field]) {
                $data[$field] = $attrs[$field];
            }
        }

        if (!$id) {
            if (empty($data["date_created"])) {
                $data["date_created"] = date(self::DB_DATE_FORMAT);
            }
            $this->DB->insert(UNREGISTERED_TABLE, $data);
            $user_id = $this->DB->insert_id();
        } else {
            $this->DB->where('id', $id);
            $this->DB->update(UNREGISTERED_TABLE, $data);
        }
    }

    public function delete_unregistered_user_by_email($email)
    {
        $this->DB->where('email', $email);
        $this->db->delete(UNREGISTERED_TABLE);
    }

    public function delete_unregistered_user_by_id($id)
    {
        $this->DB->where('id', $id);
        $this->db->delete(UNREGISTERED_TABLE);
    }
}
