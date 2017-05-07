<?php

namespace Pg\Modules\Social_networking\Models;

/**
 * Social networking services model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * */
if (!defined('SOCIAL_NETWORKING_SERVICES_TABLE')) {
    define('SOCIAL_NETWORKING_SERVICES_TABLE', DB_PREFIX . 'social_networking_services');
}

class Social_networking_services_model extends \Model
{
    public $ci;
    public $db;
    public $fields_all = array(
        'id',
        'gid',
        'name',
        'authorize_url',
        'access_key_url',
        'app_enabled',
        'app_key',
        'app_secret',
        'oauth_enabled',
        'oauth_version',
        'oauth_status',
        'status',
        'date_add',
    );
    public $enabled = true;

    public function __construct()
    {
        parent::__construct();
        $this->ci = &get_instance();
        $this->db = &$this->ci->db;
        if (!extension_loaded('curl')) {
            $this->enabled = false;
        }
        $this->db->memcache_tables(array(SOCIAL_NETWORKING_SERVICES_TABLE));
    }

    public function get_services_list($order_by = null, $params = array(), $filter_object_ids = null)
    {
        $data = array();
        $select_attrs = $this->fields_all;

        $this->db->select(implode(", ", $select_attrs))->from(SOCIAL_NETWORKING_SERVICES_TABLE);

        if (isset($params["where"]) && is_array($params["where"]) && count($params["where"])) {
            foreach ($params["where"] as $field => $value) {
                $this->db->where($field, $value);
            }
        }

        if (isset($params["where_in"]) && is_array($params["where_in"]) && count($params["where_in"])) {
            foreach ($params["where_in"] as $field => $value) {
                $this->db->where_in($field, $value);
            }
        }

        if (isset($params["where_sql"]) && is_array($params["where_sql"]) && count($params["where_sql"])) {
            foreach ($params["where_sql"] as $value) {
                $this->db->where($value);
            }
        }

        if (isset($filter_object_ids) && is_array($filter_object_ids) && count($filter_object_ids)) {
            $this->db->where_in("id", $filter_object_ids);
        }

        if (is_array($order_by) && count($order_by) > 0) {
            foreach ($order_by as $field => $dir) {
                if (in_array($field, $this->fields_all)) {
                    $this->db->order_by($field . " " . $dir);
                }
            }
        }

        $results = $this->db->get()->result_array();

        if (!empty($results) && is_array($results)) {
            foreach ($results as $r) {
                $data[$r['id']] = $r;
            }
        }

        return $data;
    }

    public function get_service_by_id($service_id = false)
    {
        $data = array();
        $select_attrs = $this->fields_all;
        $result = $this->db->select(implode(", ", $select_attrs))->from(SOCIAL_NETWORKING_SERVICES_TABLE)->where("id", $service_id)->get()->result_array();
        if (!empty($result)) {
            $data = $result[0];
        }

        return $data;
    }

    public function get_service_by_gid($service_gid = false)
    {
        $data = array();
        $select_attrs = $this->fields_all;
        $result = $this->db->select(implode(", ", $select_attrs))
            ->from(SOCIAL_NETWORKING_SERVICES_TABLE)
            ->where("gid", $service_gid)
            ->get()->result_array();
        if (!empty($result)) {
            $data = $result[0];
        }

        return $data;
    }

    public function getServiceByGid($service_gid)
    {
        $data = array();
        $result = $this->db
                ->select(implode(', ', $this->fields_all))
                ->from(SOCIAL_NETWORKING_SERVICES_TABLE)
                ->where('gid', $service_gid)
                ->get()->result_array();
        if (!empty($result)) {
            $data = $result[0];
        }

        return $data;
    }

    public function validate_service($data = array())
    {
        $return = array("errors" => array(), "data" => array());

        if (isset($data["name"])) {
            $return["data"]["name"] = strip_tags($data["name"]);
            if (empty($return["data"]["name"])) {
                $return["errors"][] = l('error_title_invalid', 'social_networking');
            }
        }

        if (isset($data["gid"])) {
            $return["data"]["gid"] = strip_tags($data["gid"]);
            $return["data"]["gid"] = preg_replace("/[\n\t\s]{1,}/", "-", trim($return["data"]["gid"]));
            if (empty($return["data"]["gid"])) {
                $return["errors"][] = l('error_gid_invalid', 'social_networking');
            }
        } elseif (!$config_id) {
            $return["errors"][] = l('error_gid_invalid', 'social_networking');
        }

        if (isset($data["authorize_url"])) {
            $return["data"]["authorize_url"] = $data["authorize_url"];
        }

        if (isset($data["access_key_url"])) {
            $return["data"]["access_key_url"] = $data["access_key_url"];
        }

        if (isset($data["app_key"])) {
            $return["data"]["app_key"] = strip_tags($data["app_key"]);
        }

        if (isset($data["app_secret"])) {
            $return["data"]["app_secret"] = strip_tags($data["app_secret"]);
        }

        if (isset($data["app_enabled"])) {
            $return["data"]["app_enabled"] = $data["app_enabled"] ? 1 : 0;
        }

        if (isset($data["oauth_enabled"])) {
            $return["data"]["oauth_enabled"] = $data["oauth_enabled"] ? 1 : 0;
        }

        if (isset($data["oauth_version"])) {
            $return["data"]["oauth_version"] = $data["oauth_version"];
        }

        return $return;
    }

    public function delete_service($service_id = false)
    {
        $data = $this->get_service_by_id($service_id);
        if (empty($data)) {
            return false;
        }
        $this->db->where('id', $service_id);
        $this->db->delete(SOCIAL_NETWORKING_SERVICES_TABLE);

        return true;
    }

    public function save_service($service_id = false, $data = array())
    {
        if (is_null($service_id)) {
            $data["date_add"] = date("Y-m-d H:i:s");
            $this->db->insert(SOCIAL_NETWORKING_SERVICES_TABLE, $data);
            $service_id = $this->db->insert_id();
        } else {
            $this->db->where('id', $service_id);
            $this->db->update(SOCIAL_NETWORKING_SERVICES_TABLE, $data);
        }

        return $service_id;
    }
}
