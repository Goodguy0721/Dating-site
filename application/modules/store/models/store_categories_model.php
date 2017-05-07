<?php

/**
 * Store categories model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Nikita Savanaev <nsavanaev@pilotgroup.net>
 **/
if (!defined('TABLE_STORE_CATEGORIES')) {
    define('TABLE_STORE_CATEGORIES', DB_PREFIX . 'store_categories');
}
if (!defined('TABLE_STORE_PRODUCTS_CATEGORIES')) {
    define('TABLE_STORE_PRODUCTS_CATEGORIES', DB_PREFIX . 'store_products_categories');
}

class Store_categories_model extends Model
{
    private $CI;
    private $DB;
    private $fields = array(
        'id',
        'gid',
        'id_parent',
        'status',
        'product_active_count',
        'product_inactive_count',
        'bestsellers_count',
        'priority',
        'options_data',
    );
    public $fields_all = array();
    public $fields_priority = array('id', 'priority');

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
        $this->DB = &$this->CI->db;
        $this->fields_all = $this->fields;
        $this->DB->memcache_tables(array(TABLE_STORE_CATEGORIES));
    }

    /**
     * Add additional fields
     *
     * @param array $fields
     */
    public function set_additional_fields($fields)
    {
        $this->dop_fields = $fields;
        $this->fields_all = (!empty($this->dop_fields)) ? array_merge($this->fields, $this->dop_fields) : $this->fields;

        return;
    }

    /**
     * Get category by id
     *
     * @param intval $id
     * @param intval $lang_id
     *
     * @return boolean|array
     */
    public function get_category_by_id($id, $lang_id = null)
    {
        $select_attrs = $this->fields_all;
        if (isset($lang_id)) {
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
                ->from(TABLE_STORE_CATEGORIES)
                ->where("id", $id)
                ->get()->result_array();
        if (empty($result)) {
            return false;
        } else {
            return $result[0];
        }
    }

    /**
     * Get category by gid
     *
     * @param string  $gid
     * @param integer $lang_id
     *
     * @return boolean|array
     */
    public function get_category_by_gid($gid, $lang_id = null)
    {
        $select_attrs = $this->fields_all;
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
                ->from(TABLE_STORE_CATEGORIES)
                ->where("gid", $gid)
                ->get()->result_array();
        if (empty($result)) {
            return false;
        } else {
            return $result[0];
        }
    }

    /**
     * Get category name by ids
     *
     * @param array $ids
     *
     * @return boolean|array
     */
    public function get_name_by_ids($ids = array())
    {
        $lang_id = $this->CI->pg_language->current_lang_id;
        $result = $this->DB->select('name_' . $lang_id)
                ->from(TABLE_STORE_CATEGORIES)
                ->where_in("id", $ids)
                ->get()->result_array();
        if (empty($result)) {
            return false;
        } else {
            return $result[0];
        }
    }

    public function get_gid_by_ids($ids = array())
    {
        $result = $this->DB->select('gid')
                ->from(TABLE_STORE_CATEGORIES)
                ->where_in("id", $ids)
                ->get()->result_array();
        $return = array();
        foreach ($result as $row) {
            $return[] = $row['gid'];
        }

        return $return;
    }

    public function get_name_by_gid($gid)
    {
        $lang_id = $this->CI->pg_language->current_lang_id;
        $result = $this->DB->select('name_' . $lang_id . ' as name')
                ->from(TABLE_STORE_CATEGORIES)
                ->where("gid", $gid)
                ->get()->result_array();
        if (empty($result)) {
            return false;
        } else {
            return $result[0]['name'];
        }
    }

    public function get_name_by_product_id($product_id)
    {
        $data = array();
        $ids = $this->get_categories_by_product_id($product_id);
        if (!empty($ids)) {
            $data = trim(implode(' ', $this->get_name_by_ids($ids)), ',');
        }

        return $data;
    }

    public function get_categories_list($page = null, $items_on_page = null, $order_by = null, $params = array(), $filter_object_ids = array(), $formatted = true, $safe_format = false, $lang_id = '')
    {
        if (isset($params["fields"]) && is_array($params["fields"]) && count($params["fields"])) {
            $this->set_additional_fields($params["fields"]);
        }
        $select_attrs = $this->fields_all;
        if ($lang_id) {
            $select_attrs[] = 'name_' . $lang_id . ' as name';
            $select_attrs[] = 'description_' . $lang_id . ' as description';
        } else {
            $default_lang_id = $this->CI->pg_language->current_lang_id;
            $select_attrs[] = 'name_' . $default_lang_id . ' as name';
            $select_attrs[] = 'description_' . $default_lang_id . ' as description';
        }
        $this->DB->select(implode(", ", $select_attrs));
        $this->DB->from(TABLE_STORE_CATEGORIES);

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
                $results = $this->format_categories($results, $safe_format);
            }

            return $results;
        }

        return array();
    }

    public function get_all_categories($lang_id = null)
    {
        return $this->get_categories_list(null, null, null, array(), array(), true, false, $lang_id);
    }

    public function get_categories_by_product_id($product_id = null)
    {
        $result = $this->DB->select('id_category')
                ->from(TABLE_STORE_PRODUCTS_CATEGORIES)
                ->where("id_product", $product_id)
                ->get()->result_array();
        $return = array();
        foreach ($result as $row) {
            $return[] = $row['id_category'];
        }

        return $return;
    }

    public function get_category_id_by_priority($priority)
    {
        $result = $this->DB->select('id')
                ->from(TABLE_STORE_CATEGORIES)
                ->where("priority", $priority)
                ->get()->result_array();
        if (empty($result)) {
            return false;
        } else {
            return $result[0];
        }
    }

    public function get_products_by_category_id($id_category = null)
    {
        $result = $this->DB->select('id_product')
                ->from(TABLE_STORE_PRODUCTS_CATEGORIES)
                ->where("id_category", $id_category)
                ->get()->result_array();
        $return = array();
        foreach ($result as $row) {
            $return[] = $row['id_product'];
        }

        return $return;
    }

    public function get_products_by_category_ids($category_ids, $items_on_page = null, $params = array(), $rand = false)
    {
        if (empty($category_ids) || !is_array($category_ids)) {
            return false;
        }
        $this->DB->select('id_product');
        $this->DB->from(TABLE_STORE_PRODUCTS_CATEGORIES);
        $this->DB->where_in("id_category", $category_ids);
        if (isset($params["where_sql"]) && is_array($params["where_sql"]) && count($params["where_sql"])) {
            foreach ($params["where_sql"] as $value) {
                $this->DB->where($value, null, false);
            }
        }
        if ($rand === true) {
            $this->DB->order_by('id_product', 'RANDOM');
        }
        if (!is_null($items_on_page)) {
            $this->DB->limit($items_on_page, 0);
        }
        $results = $this->DB->get()->result_array();
        foreach ($results as $row) {
            $return[] = $row['id_product'];
        }

        return $return;
    }

    public function get_categories_count($params)
    {
        if (isset($params["where"]) && is_array($params["where"]) && count($params["where"])) {
            foreach ($params["where"] as $field => $value) {
                $this->DB->where($field, $value);
            }
        }
        $result = $this->DB->count_all_results(TABLE_STORE_CATEGORIES);

        return $result;
    }

    public function get_product_count($id_category, $params = array())
    {
        $product_ids = $this->get_products_by_category_id($id_category);
        $params["where_in"]["id"] = $product_ids;
        $this->CI->load->model('Store_products_model');
        $result = $this->CI->Store_products_model->get_products_count($params);

        return $result;
    }

    /**
     * Get priority
     *
     * @return intval
     */
    public function get_priority($params = array())
    {
        $this->DB->select('MIN(priority) AS min_priority');
        $this->DB->from(TABLE_STORE_CATEGORIES);
        if (isset($params["where"]) && is_array($params["where"]) && count($params["where"])) {
            foreach ($params["where"] as $field => $value) {
                $this->DB->where($field, $value);
            }
        }
        $result = $this->DB->get()->result_array();
        foreach ($result as $row) {
            $return = intval($row['min_priority']);
        }

        return $return;
    }

    /**
     * Get last priority
     *
     * @param intval $add
     *
     * @return intval
     */
    public function get_last_priority($add = 0)
    {
        $result = $this->DB->select('MAX(priority) AS max_priority')->from(TABLE_STORE_CATEGORIES)->get()->result_array();
        foreach ($result as $row) {
            $return = intval($row['max_priority']) + $add;
        }

        return $return;
    }

    /**
     * Get category priority
     *
     * @param intval $category_id
     *
     * @return boolean|array
     */
    private function get_category_priority_by_id($category_id)
    {
        $result = $this->DB->select(implode(", ", $this->fields_priority))
                ->from(TABLE_STORE_CATEGORIES)
                ->where("id", $category_id)
                ->get()->result_array();
        if (empty($result)) {
            return false;
        } else {
            return $result[0];
        }
    }

    /**
     * Get previous priority
     *
     * @param array $params
     *
     * @return array
     */
    private function get_previous_priority($params = array())
    {
        $this->DB->select(implode(", ", $this->fields_priority));
        $this->DB->from(TABLE_STORE_CATEGORIES);

        if (isset($params["where"]) && is_array($params["where"]) && count($params["where"])) {
            foreach ($params["where"] as $field => $value) {
                $this->DB->where($field, $value);
            }
        }

        if (isset($params["where_sql"]) && is_array($params["where_sql"]) && count($params["where_sql"])) {
            foreach ($params["where_sql"] as $value) {
                $this->DB->where($value, null, false);
            }
        }
        if (isset($params["order_by"]) && is_array($params["order_by"]) && count($params["order_by"])) {
            foreach ($params["order_by"] as $field => $dir) {
                $this->DB->order_by($field . " " . $dir);
            }
        }
        $results = $this->DB->get()->result_array();

        return $results[0];
    }

    public function get_categories_options($ids = array())
    {
        $data = array();
        if (!empty($ids)) {
            foreach ($ids as $id) {
                $options[] = $this->get_category_options($id);
            }
            foreach ($options as $option) {
                $data = array_unique(array_merge($option, $data));
            }
        }

        return $data;
    }

    public function get_category_options($id)
    {
        $result = $this->DB->select('options_data')
                ->from(TABLE_STORE_CATEGORIES)
                ->where("id", $id)
                ->get()->result_array();
        $return = array();
        foreach ($result as $row) {
            if (!empty($row['options_data'])) {
                $return = unserialize($row['options_data']);
            }
        }

        return $return;
    }

    private function get_parent_menu_item()
    {
        $this->CI->load->model('Menu_model');
        $parent_menu = $this->CI->Menu_model->get_menu_item_by_gid('user-menu-store');

        return $parent_menu;
    }

    public function validate($id, $data = array())
    {
        $return = array("errors" => array(), "data" => array());
        $current_lang_id = $this->CI->pg_language->current_lang_id;
        foreach ($this->CI->pg_language->languages as $key => $value) {
            if (!empty($data['name_' . $value['id']])) {
                $return["data"]['name_' . $value['id']] = trim(strip_tags($data['name_' . $value['id']]));
            } else {
                $return["data"]['name_' . $value['id']] = trim(strip_tags($data['name_' . $current_lang_id]));
            }
            if (!empty($data['description_' . $value['id']])) {
                $return["data"]['description_' . $value['id']] = trim(strip_tags($data['description_' . $value['id']]));
            } else {
                $return["data"]['description_' . $value['id']] = trim(strip_tags($data['description_' . $current_lang_id]));
            }
        }
        if (empty($return["data"]['name_' . $current_lang_id])) {
            $return["errors"]['name_' . $current_lang_id] = l('error_name_incorrect', 'store');
        }
        if (isset($data["gid"])) {
            $this->CI->config->load("reg_exps", true);
            $reg_exp = $this->CI->config->item("not_literal", "reg_exps");
            $temp_gid = $return["data"]["gid"] = strtolower(trim(strip_tags($data["gid"])));
            if (!empty($temp_gid)) {
                $return["data"]["gid"] = preg_replace($reg_exp, '_', $return["data"]["gid"]);
                $return["data"]["gid"] = preg_replace("/[\-]{2,}/i", '_', $return["data"]["gid"]);
                $return["data"]["gid"] = trim($return["data"]["gid"], '_');
                if (empty($return["data"]["gid"])) {
                    $return["data"]["gid"] = substr(md5($temp_gid), 10);
                }

                $params = array();
                $params["where"]["gid"] = $return["data"]["gid"];
                if ($id) {
                    $params["where"]["id <>"] = $id;
                }
                $count = $this->get_categories_count($params);
                if ($count > 0) {
                    $return["errors"][] = l('error_gid_already_exists', 'store');
                }
            } else {
                $return["errors"][] = l('error_content_gid_invalid', 'store');
            }
        }

        return $return;
    }

    public function save_category($id = null, $attrs = array())
    {
        if (is_null($id)) {
            $this->DB->insert(TABLE_STORE_CATEGORIES, $attrs);
            $id = $this->DB->insert_id();
        } else {
            $this->DB->where('id', $id);
            $this->DB->update(TABLE_STORE_CATEGORIES, $attrs);
        }

        return $id;
    }

    private function set_user_menu_item($id, $attrs)
    {
        $this->CI->load->model('Menu_model');
        $menu_data = $this->CI->Menu_model->get_menu_item_by_gid('user_store_category_' . $id);
        $item_id = !empty($menu_data) ? $menu_data['id'] : null;
        $parent_menu = $this->get_parent_menu_item();
        $data['menu_id'] = $parent_menu['menu_id'];
        $data['parent_id'] = $parent_menu['id'];
        $data['gid'] = 'user_store_category_' . $id;
        if (!empty($attrs['status'])) {
            $data['status'] = $attrs['status'];
        }
        if (!empty($attrs['priority'])) {
            $data['sorter'] = $attrs['priority'];
        }
        $data['link'] = 'store/category/' . $id;
        $langs_arr = array();
        foreach ($this->CI->pg_language->languages as $value) {
            if (!empty($attrs['name_' . $value['id']])) {
                $langs_arr[] = $attrs['name_' . $value['id']];
            }
        }
        $this->CI->Menu_model->save_menu_item($item_id, $data, $langs_arr);
    }

    public function set_sort_categories($id, $direction = null)
    {
        $data_priority = $this->get_category_priority_by_id($id);
        $params = array();
        if ($direction === 'up') {
            $params["where_sql"][] = " priority<'" . $data_priority['priority'] . "' AND priority>0 ";
            $params["order_by"]["priority"] = " DESC ";
        } else {
            $params["where_sql"][] = " priority>'" . $data_priority['priority'] . "' ";
            $params["order_by"]["priority"] = " ASC ";
        }
        $data_previous_priority = $this->get_previous_priority($params);
        if (!empty($data_previous_priority['id'])) {
            $attrs['priority'] = $data_priority['priority'];
            $this->set_priority($data_previous_priority['id'], $attrs);
            $attrs['priority'] = $data_previous_priority['priority'];
            $this->set_priority($data_priority['id'], $attrs);
        }

        return;
    }

    private function set_priority($id = null, $attrs = array())
    {
        if (!empty($id)) {
            $this->DB->where('id', $id);
            $this->DB->update(TABLE_STORE_CATEGORIES, $attrs);
        }

        return;
    }

    public function set_product_active_count($id_category = null)
    {
        $params["where"]["status"] = 1;
        $attrs["product_active_count"] = $this->get_product_count($id_category, $params);
        $this->DB->where('id', $id_category);
        $this->DB->update(TABLE_STORE_CATEGORIES, $attrs);
    }

    public function set_status_category($id, $status = 1)
    {
        $attrs["status"] = intval($status);
        $this->save_category($id, $attrs);
    }

    public function set_product_inactive_count($id_category = null)
    {
        $params["where"]["status"] = 0;
        $attrs["product_inactive_count"] = $this->get_product_count($id_category, $params);
        $this->DB->where('id', $id_category);
        $this->DB->update(TABLE_STORE_CATEGORIES, $attrs);
    }

    public function set_product_bestsellers_count($id_category = null)
    {
        $params["where"]["is_bestseller"] = 1;
        $attrs["bestsellers_count"] = $this->get_product_count($id_category, $params);
        $this->DB->where('id', $id_category);
        $this->DB->update(TABLE_STORE_CATEGORIES, $attrs);
    }

    public function set_status_option_category($id = null, $attrs = array())
    {
        if (!empty($id)) {
            $this->DB->where('id', $id);
            $this->DB->update(TABLE_STORE_CATEGORIES, $attrs);
        }

        return;
    }

    public function format_categories($data, $safe_format = false)
    {
        foreach ($data as $key => &$category) {
            if ($category["priority"] == 1) {
                $category["sort"]["first"] = 1;
            }
            if ($category["priority"] == $this->get_last_priority(0)) {
                $category["sort"]["last"] = 1;
            }
            $category["product_all_count"] = $category["product_active_count"] + $category["product_inactive_count"];
        }
        if ($safe_format) {
            foreach ($data as $key => $category) {
                $data[$key] = array_intersect_key($data[$key], array_flip($this->safe_fields));
            }
        }

        return $data;
    }

    public function delete_product($product_id = null, $category_ids = array())
    {
        $this->DB->where('id_product', $product_id);
        $this->DB->delete(TABLE_STORE_PRODUCTS_CATEGORIES);
        $this->CI->load->model('Store_products_model');
        $product = $this->CI->Store_products_model->get_product_by_id($product_id);
        $categories = $this->get_categories_list(null, null, null, array(), $category_ids);
        foreach ($categories as $category) {
            if ($product['status'] == 1) {
                $attrs['product_active_count'] = $category['product_active_count'] > 0 ? ($category['product_active_count'] - 1) : 0;
            } else {
                $attrs['product_inactive_count'] = $category['product_inactive_count'] > 0 ? ($category['product_inactive_count'] - 1) : 0;
            }
            if ($product['is_bestseller'] == 1) {
                $attrs['bestsellers_count'] = $category['bestsellers_count'] > 0 ? ($category['bestsellers_count'] - 1) : 0;
            }
            $this->save_category($category['id'], $attrs);
        }
    }

    public function delete_product_by_category($category_id = null)
    {
        $this->DB->where('id_category', $category_id);
        $this->DB->delete(TABLE_STORE_PRODUCTS_CATEGORIES);
    }

    public function set_categories_by_product_id($product_id = null, $categories = array())
    {
        $this->DB->where('id_product', $product_id);
        $this->DB->delete(TABLE_STORE_PRODUCTS_CATEGORIES);
        foreach ($categories["category_id"] as $category_id) {
            $attrs["id_product"] = $product_id;
            $attrs["id_category"] = $category_id;
            $this->DB->insert(TABLE_STORE_PRODUCTS_CATEGORIES, $attrs);
            $this->DB->insert_id();
            $this->set_product_active_count($category_id);
            $this->set_product_inactive_count($category_id);
            $this->set_product_bestsellers_count($category_id);
        }
    }

    public function set_category_by_product_id($attrs = array())
    {
        $this->DB->where('id_product', $attrs['id_product']);
        $this->DB->delete(TABLE_STORE_PRODUCTS_CATEGORIES);
        $this->DB->insert(TABLE_STORE_PRODUCTS_CATEGORIES, $attrs);

        return    $this->DB->insert_id();
    }

    public function delete_category($id)
    {
        $this->DB->where('id', $id);
        $this->DB->delete(TABLE_STORE_CATEGORIES);
    }

    public function lang_dedicate_module_callback_add($lang_id = false)
    {
        if (!$lang_id) {
            return;
        }

        $this->CI->load->dbforge();

        $fields_n['name_' . $lang_id] = array('type' => 'VARCHAR', 'constraint' => '255', 'null' => false);
        $this->CI->dbforge->add_column(TABLE_STORE_CATEGORIES, $fields_n);

        $default_lang_id = $this->CI->pg_language->get_default_lang_id();
        if ($lang_id != $default_lang_id) {
            $this->CI->db->set('name_' . $lang_id, 'name_' . $default_lang_id, false);
            $this->CI->db->update(TABLE_STORE_CATEGORIES);
        }
        $fields_d['description_' . $lang_id] = array('type' => 'TEXT', 'null' => false);
        $this->CI->dbforge->add_column(TABLE_STORE_CATEGORIES, $fields_d);
        if ($lang_id != $default_lang_id) {
            $this->CI->db->set('description_' . $lang_id, 'description_' . $default_lang_id, false);
            $this->CI->db->update(TABLE_STORE_CATEGORIES);
        }
    }

    public function lang_dedicate_module_callback_delete($lang_id = false)
    {
        if (!$lang_id) {
            return;
        }

        $this->CI->load->dbforge();

        $table_query = $this->CI->db->get(TABLE_STORE_CATEGORIES);
        $fields_exists = $table_query->list_fields();

        $fields = array('name_' . $lang_id, 'description_' . $lang_id);
        foreach ($fields as $field_name) {
            if (!in_array($field_name, $fields_exists)) {
                continue;
            }
            $this->CI->dbforge->drop_column(TABLE_STORE_CATEGORIES, $field_name);
        }
    }
}
