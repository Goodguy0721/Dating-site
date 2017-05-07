<?php

/**
 * Store options model
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
if (!defined('TABLE_STORE_OPTIONS')) {
    define('TABLE_STORE_OPTIONS', DB_PREFIX . 'store_options');
}

class Store_options_model extends Model
{
    private $CI;
    private $DB;
    private $fields = array(
        'id',
        'type',
    );

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
        $this->DB = &$this->CI->db;
        $this->DB->memcache_tables(array(TABLE_STORE_OPTIONS));
    }

    public function get_option_by_id($id, $lang_id = '')
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
                ->from(TABLE_STORE_OPTIONS)
                ->where("id", $id)
                ->get()->result_array();
        if (empty($result)) {
            return false;
        } else {
            return $this->format_option($result[0]);
        }
    }

    public function get_options_list($page = null, $items_on_page = null, $order_by = null, $params = array())
    {
        $lang_id = $this->CI->pg_language->current_lang_id;
        $select_attrs = $this->fields;
        if ($lang_id) {
            $select_attrs[] = 'name_' . $lang_id . ' as name';
            $select_attrs[] = 'description_' . $lang_id . ' as description';
        }
        $this->DB->select(implode(", ", $select_attrs));
        $this->DB->from(TABLE_STORE_OPTIONS);

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
                if (in_array($field, $this->fields) || $field == 'fields') {
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
            $results = $this->format_options($results);

            return $results;
        }

        return array();
    }

    public function get_options_count($params = array(), $filter_object_ids = null)
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
        $result = $this->DB->count_all_results(TABLE_STORE_OPTIONS);

        return $result;
    }

    public function get_options($parent = 0, $lang_id = '')
    {
        if (!$lang_id) {
            $lang_id = $this->pg_language->current_lang_id;
        }

        $attrs = $this->attrs;
        if ($lang_id) {
            $attrs[] = 'lang_' . $lang_id;
        }

        $this->DB->select(implode(", ", $attrs))->from(TABLE_STORE_OPTIONS);
        $this->DB->where('parent', intval($parent));
        $this->DB->order_by("sorter ASC");

        $data = array();
        $results = $this->DB->get()->result_array();
        if (!empty($results) && is_array($results)) {
            foreach ($results as $r) {
                if ($lang_id) {
                    $r['name'] = $r["lang_" . $lang_id];
                }
                $r["statistics"] = unserialize($r["statistics"]);
                $data[] = $r;
            }
        }

        return $data;
    }

    public function get_type($id)
    {
        $result = $this->DB->select('type')
                ->from(TABLE_STORE_OPTIONS)
                ->where("id", $id)
                ->get()->result_array();
        $return = array();
        foreach ($result as $row) {
            $return = $row['type'];
        }

        return $return;
    }

    public function format_options($data)
    {
        foreach ($data as $key => $options) {
            if (!empty($options["type"])) {
                $options["multiselect"] = ($options["type"] == 'multi') ? 1 : 0;
            }
            $data[$key] = $options;
        }

        return $data;
    }

    public function format_option($data)
    {
        if ($data) {
            $return = $this->format_options(array(0 => $data));

            return $return[0];
        }

        return array();
    }

    public function validate($data = array())
    {
        $return = array("errors" => array(), "data" => array());

        $return["data"]["type"] = !empty($data["multiselect"]) ? 'multi' : 'one';

        $current_lang_id = $this->CI->pg_language->current_lang_id;
        foreach ($this->CI->pg_language->languages as $key => $value) {
            if (!empty($data['name_' . $value['id']])) {
                $return["data"]['name_' . $value['id']] = trim(strip_tags($data['name_' . $value['id']]));
            } else {
                $return["data"]['name_' . $value['id']] = trim(strip_tags($data['name_' . $current_lang_id]));
            }
            if (!empty($data['description_' . $value['id']])) {
                $return["data"]['description_' . $value['id']] = strip_tags($data['description_' . $value['id']]);
            } else {
                $return["data"]['description_' . $value['id']] = strip_tags($data['description_' . $current_lang_id]);
            }
        }

        return $return;
    }

    /**
     * Save option
     *
     * @param integer $id
     * @param array   $attrs
     *
     * @return integer
     */
    public function save_option($id = null, $attrs = array())
    {
        if (is_null($id)) {
            $this->DB->insert(TABLE_STORE_OPTIONS, $attrs);
            $id = $this->DB->insert_id();
        } else {
            $this->DB->where('id', $id);
            $this->DB->update(TABLE_STORE_OPTIONS, $attrs);
        }

        return $id;
    }

    /**
     *  Delete option
     *
     *  @param integer $id
     *
     *  @return void
     */
    public function delete_option($id = null)
    {
        if (isset($id)) {
            $this->DB->where('id', $id);
            $this->DB->delete(TABLE_STORE_OPTIONS);
        }

        return;
    }

    /**
     *  Add lang callback
     *
     *  @param boolean $lang_id
     *
     *  @return void
     */
    public function lang_dedicate_module_callback_add($lang_id = false)
    {
        if (!$lang_id) {
            return;
        }

        $this->CI->load->dbforge();

        $fields_n['name_' . $lang_id] = array('type' => 'VARCHAR', 'constraint' => '255', 'null' => false);
        $this->CI->dbforge->add_column(TABLE_STORE_OPTIONS, $fields_n);

        $default_lang_id = $this->CI->pg_language->get_default_lang_id();
        if ($lang_id != $default_lang_id) {
            $this->CI->db->set('name_' . $lang_id, 'name_' . $default_lang_id, false);
            $this->CI->db->update(TABLE_STORE_OPTIONS);
        }
        $fields_d['description_' . $lang_id] = array('type' => 'TEXT', 'null' => false);
        $this->CI->dbforge->add_column(TABLE_STORE_OPTIONS, $fields_d);
        if ($lang_id != $default_lang_id) {
            $this->CI->db->set('description_' . $lang_id, 'description_' . $default_lang_id, false);
            $this->CI->db->update(TABLE_STORE_OPTIONS);
        }
    }

    /**
     *  Delete lang callback
     *
     *  @param boolean $lang_id
     *
     *  @return void
     */
    public function lang_dedicate_module_callback_delete($lang_id = false)
    {
        if (!$lang_id) {
            return;
        }

        $this->CI->load->dbforge();

        $table_query = $this->CI->db->get(TABLE_STORE_OPTIONS);
        $fields_exists = $table_query->list_fields();

        $fields = array('name_' . $lang_id, 'description_' . $lang_id);
        foreach ($fields as $field_name) {
            if (!in_array($field_name, $fields_exists)) {
                continue;
            }
            $this->CI->dbforge->drop_column(TABLE_STORE_OPTIONS, $field_name);
        }
    }
}
