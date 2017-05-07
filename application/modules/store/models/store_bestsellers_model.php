<?php

/**
 * Store bestsellers model
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
if (!defined('TABLE_STORE_BESTSELLERS')) {
    define('TABLE_STORE_BESTSELLERS', DB_PREFIX . 'store_bestsellers');
}
if (!defined('TABLE_STORE_PRODUCTS')) {
    define('TABLE_STORE_PRODUCTS', DB_PREFIX . 'store_products');
}

class Store_bestsellers_model extends Model
{
    private $CI;
    private $DB;
    private $fields = array(
        'id_category',
        'id_product',
        'priority',
    );
    private $fields_prod = array(
        'id',
        'gid',
        'id_user',
        'status',
        'price',
        'price_reduced',
        'gid_currency',
        'price_sorting',
        'is_bestseller',
        'photo',
        'photo_count',
        'video',
        'video_image',
        'video_data',
        'date_created',
        'date_updated',
        'search_data',
    );

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
        $this->DB = &$this->CI->db;
        $this->DB->memcache_tables(array(TABLE_STORE_BESTSELLERS));
    }

    public function get_bestsellers_count($params = array(), $filter_object_ids = null)
    {
        $this->DB->select('distinct id');
        $this->DB->from(TABLE_STORE_PRODUCTS);
        if (isset($params["where"]) && is_array($params["where"]) && count($params["where"])) {
            if (!empty($params["where"]['id_category'])) {
                $this->db->join(TABLE_STORE_BESTSELLERS, TABLE_STORE_BESTSELLERS . '.id_product = ' . TABLE_STORE_PRODUCTS . '.id', 'left');
            }
            foreach ($params["where"] as $field => $value) {
                $this->DB->where($field, $value);
            }
        }
        if (isset($params["where_sql"]) && is_array($params["where_sql"]) && count($params["where_sql"])) {
            foreach ($params["where_sql"] as $value) {
                $this->DB->where($value, null, false);
            }
        }
        $results = $this->DB->get();

        return $results->num_rows();
    }

    public function get_bestsellers_list($page = null, $items_on_page = null, $params = array(), $order_by = null, $lang_id)
    {
        $select_attrs[] = 'name_' . $lang_id . ' as name';
        $options = $this->get_options();
        foreach ($options as $option) {
            $select_attrs[] = 'option_' . $option['id'];
        }
        $select_attrs = array_merge($select_attrs, $this->fields_prod);
        $this->DB->select('distinct id_product, ' . implode(", ", $select_attrs));
        $this->DB->from(TABLE_STORE_PRODUCTS);
        $this->db->join(TABLE_STORE_BESTSELLERS, TABLE_STORE_BESTSELLERS . '.id_product = ' . TABLE_STORE_PRODUCTS . '.id', 'left');
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
        if (is_array($order_by) && count($order_by) > 0) {
            foreach ($order_by as $field => $dir) {
                $this->DB->order_by(TABLE_STORE_BESTSELLERS . "." . $field . " " . $dir);
            }
        }
        if (!is_null($page)) {
            $page = intval($page) ? intval($page) : 1;
            $this->DB->limit($items_on_page, $items_on_page * ($page - 1));
        }
        $results = $this->DB->get()->result_array();
        if (!empty($results) && is_array($results)) {
            return $this->format($results);
        }

        return array();
    }

    public function format($data)
    {
        $this->CI->load->model('Uploads_model');
        foreach ($data as $key => $products) {
            if (!empty($products["price"])) {
                $products["price"] = (double) $products["price"];
            }
            if (!empty($products["price_reduced"])) {
                $products["price_reduced"] = (double) $products["price_reduced"];
            }
            $option_ids = $this->get_option_ids($products["id"]);
            if (!empty($option_ids)) {
                $products['options'] = $this->get_options($option_ids);
            }
            $product_photo = unserialize($products["photo"]);
            if (!empty($product_photo)) {
                $products["photo"] = $product_photo[0];
                $products["media"]["mediafile"] = $this->CI->Uploads_model->format_upload('store', $products["id"], $products["photo"]);
            }
            // seo data
            $categories = $this->CI->Store_categories_model->get_categories_by_product_id($products["id"]);
            if (!empty($categories)) {
                $categories_gid = $this->CI->Store_categories_model->get_gid_by_ids($categories);
                if ($this->CI->uri->rsegments[2] == 'category') {
                    foreach ($categories_gid as $gid) {
                        $pos = strpos($this->CI->uri->segments[2], $gid);
                        if ($pos > 0) {
                            $category_name = $gid;
                        }
                    }
                    $products['category_name'] = isset($category_name) ? $category_name : implode('_', $categories_gid);
                } else {
                    $products['category_name'] = implode('_', $categories_gid);
                }
            }
            $data[$key] = $products;
        }

        return $data;
    }

    public function save_bestsellers($product_id = null, $categories = array(), $status)
    {
        $this->delete_bestsellers($product_id);
        if ($status) {
            foreach ($categories["category_id"] as $category_id) {
                $attrs["id_product"] = $product_id;
                $attrs["id_category"] = $category_id;
                $attrs["priority"] = $this->get_last_priority();
                $this->DB->insert(TABLE_STORE_BESTSELLERS, $attrs);
                $this->DB->insert_id();
            }
        }
    }

    public function save_bestseller($attrs = array())
    {
        $this->delete_bestsellers($attrs['id_product']);
        $this->DB->insert(TABLE_STORE_BESTSELLERS, $attrs);

        return    $this->DB->insert_id();
    }

    public function get_last_priority()
    {
        $result = $this->DB->select('MAX(priority) AS max_priority')->from(TABLE_STORE_BESTSELLERS)->get()->result_array();
        $return = intval($result[0]['max_priority']) + 1;

        return $return;
    }

    public function resorting($data)
    {
        foreach ($data as $priority => $id) {
            $this->DB->where('id_product', $id);
            $this->DB->update(TABLE_STORE_BESTSELLERS, array('priority' => $priority));
        }

        return;
    }

    public function delete_bestsellers($product_id = null)
    {
        $this->DB->where('id_product', $product_id);
        $this->DB->delete(TABLE_STORE_BESTSELLERS);

        return;
    }

    private function get_options($ids = array())
    {
        $params = array();
        $this->CI->load->model("store/models/Store_options_model");
        if (!empty($ids)) {
            $params['where_in']['id'] = $ids;
        }
        $options = $this->CI->Store_options_model->get_options_list(null, null, null, $params);

        return $options;
    }

    private function get_option_ids($id)
    {
        $options = array();
        $this->CI->load->model('store/models/Store_categories_model');
        $categories = $this->CI->Store_categories_model->get_categories_by_product_id($id);
        $options = $this->CI->Store_categories_model->get_categories_options($categories);

        return $options;
    }

    private function get_fields_options($id)
    {
        $data = $this->get_option_ids($id);
        foreach ($data as $key => $val) {
            if ($this->CI->db->field_exists('option_' . $val, TABLE_STORE_PRODUCTS)) {
                $fields[] = 'option_' . $val;
            }
        }

        return $fields;
    }
}
