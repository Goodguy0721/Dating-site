<?php

/**
 * Store shippings model
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
if (!defined('TABLE_STORE_SHIPPINGS')) {
    define('TABLE_STORE_SHIPPINGS', DB_PREFIX . 'store_shippings');
}
if (!defined('TABLE_STORE_SHIPPINGS_COUNTRIES')) {
    define('TABLE_STORE_SHIPPINGS_COUNTRIES', DB_PREFIX . 'store_shippings_countries');
}

class Store_shippings_model extends Model
{
    private $CI;
    private $DB;
    private $fields = array(
        'id',
        'price',
        'gid_currency',
        'status',
    );
    private $fields_location = array(
        'id_shippings',
        'id_country',
        'id_region',
        'id_city',
    );

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
        $this->DB = &$this->CI->db;
        $this->DB->memcache_tables(array(TABLE_STORE_SHIPPINGS, TABLE_STORE_SHIPPINGS_COUNTRIES));
    }

    public function get_shipping_by_id($id, $formatted = true, $safe_format = false, $lang_id = '')
    {
        $select_attrs = $this->fields;
        if ($lang_id) {
            $select_attrs[] = 'name_' . $lang_id . ' as name';
            $select_attrs[] = 'description_' . $lang_id . ' as description';
        } else {
            $default_lang_ids = $this->CI->pg_language->languages;
            foreach ($default_lang_ids as $value) {
                $select_attrs[] = 'name_' . $value['id'];
                $select_attrs[] = 'description_' . $value['id'];
            }
        }
        $result = $this->DB->select(implode(", ", $select_attrs))
                ->from(TABLE_STORE_SHIPPINGS)
                ->where("id", $id)
                ->get()->result_array();
        if (empty($result)) {
            return false;
        } elseif ($formatted) {
            return $this->format_shipping($result[0], $safe_format);
        } else {
            return $result[0];
        }
    }

    public function get_shippings_count($params = array(), $filter_object_ids = null)
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
        $result = $this->DB->count_all_results(TABLE_STORE_SHIPPINGS);

        return $result;
    }

    public function get_shippings_list($page = null, $items_on_page = null, $order_by = null, $params = array(), $filter_object_ids = array(), $formatted = true, $safe_format = false, $lang_id = '')
    {
        $select_attrs = $this->fields;
        if ($lang_id) {
            $select_attrs[] = 'name_' . $lang_id . ' as name';
            $select_attrs[] = 'description_' . $lang_id . ' as description';
        } else {
            $default_lang_ids = $this->CI->pg_language->languages;
            foreach ($default_lang_ids as $value) {
                $select_attrs[] = 'name_' . $value['id'];
                $select_attrs[] = 'description_' . $value['id'];
            }
        }
        $this->DB->select(implode(", ", $select_attrs));
        $this->DB->from(TABLE_STORE_SHIPPINGS);

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
            if ($formatted) {
                $results = $this->format_shippings($results, $safe_format);
            }

            return $results;
        }

        return array();
    }

    public function get_shippings_by_location($page = null, $items_on_page = null, $order_by = null, $params = array(), $filter_object_ids = array(), $formatted = true, $safe_format = false, $lang_id = '')
    {
        $this->DB->select("id_shippings");
        $this->DB->from(TABLE_STORE_SHIPPINGS_COUNTRIES);

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
            foreach ($results as $row) {
                $return[] = $row['id_shippings'];
            }

            return $return;
        }

        return array();
    }

    public function get_countries_by_shipping_id($shipping_id = null)
    {
        $this->CI->load->model('Countries_model');
        $lang_id = $this->CI->pg_language->current_lang_id;
        $countries = $this->CI->Countries_model->get_countries(array(), array(), array(), $lang_id);
        $results = array();
        if (isset($shipping_id)) {
            $results = $this->DB->select("id_country")
                ->from(TABLE_STORE_SHIPPINGS_COUNTRIES)
                ->where("id_shippings", $shipping_id)
                ->get()->result_array();
        }
        foreach ($countries as $key => $country) {
            $countries[$key]['checked'] = '';
            foreach ($results as $result) {
                if (in_array($country['code'], $result)) {
                    $countries[$key]['checked'] = 'checked';
                }
            }
        }

        return $countries;
    }

    public function get_locations_by_shipping_id($shipping_id = null)
    {
        $result = $this->DB->select(implode(", ", $this->fields_location))
                ->from(TABLE_STORE_SHIPPINGS_COUNTRIES)
                ->where("id_shippings", $shipping_id)
                ->get()->result_array();
        foreach ($result as $k => $row) {
            if (!empty($row['id_country'])) {
                $return[$k][$shipping_id]['id_country'] = $row['id_country'];
                if (!empty($row['id_region'])) {
                    $return[$k][$shipping_id]['id_region'] = $row['id_region'];
                    if (!empty($row['id_city'])) {
                        $return[$k][$shipping_id]['id_city'] = $row['id_city'];
                    }
                }
            }
        }

        return $return;
    }

    private function format_shipping($data, $safe_format = false)
    {
        if ($data) {
            $return = array_values($this->format_shippings(array(0 => $data), $safe_format));

            return $return[0];
        }

        return array();
    }

    private function format_shippings($data, $safe_format = false)
    {
        foreach ($data as $key => $shippings) {
            if (!empty($shippings["price"])) {
                $shippings["price"] = (double) $shippings["price"];
            }
            $return[$shippings["id"]] = $shippings;
        }

        if ($safe_format) {
            foreach ($return as $key => $shippings) {
                $return[$key] = array_intersect_key($return[$key], array_flip($this->safe_fields));
            }
        }

        return $return;
    }

    public function validate($data = array())
    {
        $return = array("errors" => array(), "data" => array());

        if (!empty($data["country_id"])) {
            foreach ($data["country_id"] as $key => $country) {
                $return["data"]["locations"][$key]["id_country"] = !empty($data["country_id"][$key]) ? $data["country_id"][$key] : 0;
                $return["data"]["locations"][$key]["id_region"] = !empty($data["region_id"][$key]) ? $data["region_id"][$key] : 0;
                $return["data"]["locations"][$key]["id_city"] = !empty($data["city_id"][$key]) ? $data["city_id"][$key] : 0;
            }
        } else {
            $return["errors"]["country_id"] = l('error_specified_country', 'store');
        }

        if (isset($data["price"])) {
            $return["data"]["info"]["price"] = floatval($data["price"]);
            if ($return["data"]["info"]["price"] < 0) {
                $return["errors"][] = l('error_price_incorrect', 'store');
            }
        }

        $current_lang_id = $this->CI->pg_language->current_lang_id;
        foreach ($this->CI->pg_language->languages as $key => $value) {
            if (!empty($data['name_' . $value['id']])) {
                $return["data"]["info"]['name_' . $value['id']] = trim(strip_tags($data['name_' . $value['id']]));
            } else {
                $return["data"]["info"]['name_' . $value['id']] = trim(strip_tags($data['name_' . $current_lang_id]));
            }
            if (!empty($data['description_' . $value['id']])) {
                $return["data"]["info"]['description_' . $value['id']] = strip_tags($data['description_' . $value['id']]);
            } else {
                $return["data"]["info"]['description_' . $value['id']] = strip_tags($data['description_' . $current_lang_id]);
            }
        }
        if (empty($data['name_' . $current_lang_id])) {
            $return['errors']['name_' . $current_lang_id] = l('error_name_incorrect', 'store');
        }

        return $return;
    }

    public function set_status_shipping($id, $status = 1)
    {
        $attrs["info"]["status"] = intval($status);
        $this->save_shipping($id, $attrs);
    }

    public function save_shipping($id = null, $attrs = array())
    {
        if (is_null($id)) {
            $attrs["info"]["status"] = 1;
            $this->DB->insert(TABLE_STORE_SHIPPINGS, $attrs["info"]);
            $id = $this->DB->insert_id();
        } else {
            $this->DB->where('id', $id);
            $this->DB->update(TABLE_STORE_SHIPPINGS, $attrs["info"]);
        }
        if (!empty($attrs["locations"])) {
            $this->save_shipping_location($id, $attrs["locations"]);
        }

        return $id;
    }

    public function save_shipping_location($shipping_id = null, $locations = array())
    {
        if (isset($shipping_id)) {
            $this->delete_shipping_location($shipping_id);
            foreach ($locations as $key => $location) {
                $attrs = $location;
                $attrs["id_shippings"] = $shipping_id;
                $this->DB->insert(TABLE_STORE_SHIPPINGS_COUNTRIES, $attrs);
                $this->DB->insert_id();
            }
        }
    }

    private function delete_shipping_location($id = null)
    {
        if (isset($id)) {
            $this->DB->where('id_shippings', $id);
            $this->DB->delete(TABLE_STORE_SHIPPINGS_COUNTRIES);
        }

        return;
    }

    public function delete_shipping($id = null)
    {
        if (isset($id)) {
            $this->DB->where('id', $id);
            $this->DB->delete(TABLE_STORE_SHIPPINGS);
            $this->delete_shipping_location($id);
        }

        return;
    }

    public function lang_dedicate_module_callback_add($lang_id = false)
    {
        if (!$lang_id) {
            return;
        }

        $this->CI->load->dbforge();

        $fields_n['name_' . $lang_id] = array('type' => 'VARCHAR', 'constraint' => '255', 'null' => false);
        $this->CI->dbforge->add_column(TABLE_STORE_SHIPPINGS, $fields_n);

        $default_lang_id = $this->CI->pg_language->get_default_lang_id();
        if ($lang_id != $default_lang_id) {
            $this->CI->db->set('name_' . $lang_id, 'name_' . $default_lang_id, false);
            $this->CI->db->update(TABLE_STORE_SHIPPINGS);
        }
        $fields_d['description_' . $lang_id] = array('type' => 'TEXT', 'null' => false);
        $this->CI->dbforge->add_column(TABLE_STORE_SHIPPINGS, $fields_d);
        if ($lang_id != $default_lang_id) {
            $this->CI->db->set('description_' . $lang_id, 'description_' . $default_lang_id, false);
            $this->CI->db->update(TABLE_STORE_SHIPPINGS);
        }
    }

    public function lang_dedicate_module_callback_delete($lang_id = false)
    {
        if (!$lang_id) {
            return;
        }

        $this->CI->load->dbforge();

        $table_query = $this->CI->db->get(TABLE_STORE_SHIPPINGS);
        $fields_exists = $table_query->list_fields();

        $fields = array('name_' . $lang_id, 'description_' . $lang_id);
        foreach ($fields as $field_name) {
            if (!in_array($field_name, $fields_exists)) {
                continue;
            }
            $this->CI->dbforge->drop_column(TABLE_STORE_SHIPPINGS, $field_name);
        }
    }
}
