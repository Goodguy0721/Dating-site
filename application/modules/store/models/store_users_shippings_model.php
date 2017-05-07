<?php

namespace Pg\Modules\Store\Models;

/**
 * Store users_shippings model
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
if (!defined('TABLE_STORE_USERS_SHIPPINGS')) {
    define('TABLE_STORE_USERS_SHIPPINGS', DB_PREFIX . 'store_users_shippings');
}

class Store_users_shippings_model extends \Model
{
    private $CI;
    private $DB;
    private $fields = array(
        'id',
        'id_user',
        'country',
        'region',
        'city',
        'address',
        'zip',
        'phone',
    );

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
        $this->DB = &$this->CI->db;
        $this->DB->memcache_tables(array(TABLE_STORE_USERS_SHIPPINGS));
    }

    public function get_address_by_id($address_id, $user_id, $formatted = true)
    {
        $result = $this->DB->select(implode(", ", $this->fields))
                ->from(TABLE_STORE_USERS_SHIPPINGS)
                ->where("id", $address_id)
                ->where('id_user', $user_id)
                ->get()->result_array();
        if (empty($result)) {
            return false;
        } elseif ($formatted) {
            return $this->format_address($result[0]);
        } else {
            return $result[0];
        }
    }

    public function get_address_by_user_id($user_id)
    {
        $result = $this->DB->select(implode(", ", $this->fields))
                ->from(TABLE_STORE_USERS_SHIPPINGS)
                ->where("id_user", $user_id)
                ->order_by("id DESC")
                ->get()->result_array();
        if (empty($result)) {
            return false;
        } else {
            return $this->format_addresses($result);
        }
    }

    public function validate_address($data)
    {
        $return = array("errors" => array(), "data" => array());

        $return["data"]["id_user"] = $this->session->userdata('user_id');

        if (isset($data["country"])) {
            $return["data"]["country"] = strip_tags($data["country"]);
        }

        if (isset($data["region"])) {
            $return["data"]["region"] = intval($data["region"]);
        }

        if (isset($data["city"])) {
            $return["data"]["city"] = intval($data["city"]);
        }

        if (isset($data["street_address"])) {
            $return["data"]["address"] = strip_tags($data["street_address"]);
        }

        if (isset($data["phone"])) {
            $return["data"]["phone"] = strip_tags($data["phone"]);
        }

        if (isset($data["zip"])) {
            $return["data"]["zip"] = strip_tags($data["zip"]);
        }

        if (empty($return["data"]["country"])) {
            $return["errors"]["country"] = l('error_specified_country', 'store');
        }
        if (empty($return["data"]["region"])) {
            $return["errors"]["region"] = l('error_specified_region', 'store');
        }
        if (empty($return["data"]["city"])) {
            $return["errors"]["city"] = l('error_specified_city', 'store');
        }
        if (empty($return["data"]["address"])) {
            $return["errors"]["address"] = l('error_specified_address', 'store');
        }

        return $return;
    }

    public function format_address($data)
    {
        if ($data) {
            $return = $this->format_addresses(array(0 => $data));

            return $return[0];
        }

        return array();
    }

    public function format_addresses($data)
    {
        foreach ($data as $key => $user) {
            $user_for_location[$key] = array(
                $user["country"],
                $user["region"],
                $user["city"],
                'country' => $user["country"],
                'region'  => $user["region"],
                'city'    => $user["city"],
            );
        }
        if (!empty($user_for_location)) {
            $this->CI->load->helper('countries');
            $lang_id = $this->CI->pg_language->current_lang_id;
            $user_locations = cities_output_format($user_for_location, $lang_id);
            $users_locations_data = get_location_data($user_for_location, 'city');

            foreach ($data as $key => $user) {
                $data[$key]['id'] = $user['id'];
                $data[$key]['user_name'] = $this->session->userdata('output_name');
                $data[$key]['country'] = (isset($users_locations_data['country'][$user['country']])) ? $users_locations_data['country'][$user['country']]['name'] : '';
                $data[$key]['country_code'] = $user['country'];
                $data[$key]['region'] = (isset($users_locations_data['region'][$user['region']])) ? $users_locations_data['region'][$user['region']]['name'] : '';
                $data[$key]['region-code'] = (isset($users_locations_data['region'][$user['region']])) ? $users_locations_data['region'][$user['region']]['code'] : '';
                $data[$key]['region_id'] = $user['region'];
                $data[$key]['city'] = (isset($users_locations_data['city'][$user['city']])) ? $users_locations_data['city'][$user['city']]['name'] : '';
                $data[$key]['city_id'] = $user['city'];
            }
        }

        return $data;
    }

    public function save_address($address_id = null, $attrs)
    {
        if (is_null($address_id)) {
            $this->DB->insert(TABLE_STORE_USERS_SHIPPINGS, $attrs);
            $address_id = $this->DB->insert_id();
        } else {
            $this->DB->where('id', $address_id);
            $this->DB->update(TABLE_STORE_USERS_SHIPPINGS, $attrs);
        }

        return $address_id;
    }

    public function delete_address($id, $user_id)
    {
        if (isset($id)) {
            $data = $this->get_address_by_id($id, $user_id, false);
            if ($data['id_user'] == $user_id) {
                $this->DB->where('id', $id);
                $this->DB->where('id_user', $user_id);
                $this->DB->delete(TABLE_STORE_USERS_SHIPPINGS);
            }
        }

        return;
    }
}
