<?php
/**
 * Store products model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Nikita Savanaev <nsavanaev@pilotgroup.net>
 * */
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
if (!defined('TABLE_STORE_PRODUCTS')) {
    define('TABLE_STORE_PRODUCTS', DB_PREFIX . 'store_products');
}

class Store_products_model extends Model
{
    const DB_DATE_FORMAT        = 'Y-m-d H:i:s';
    const DB_DATE_FORMAT_SEARCH = 'Y-m-d H:i';
    const DB_DEFAULT_DATE       = '0000-00-00 00:00:00';

    private $CI;
    private $DB;
    private $fields               = array(
        'id',
        'gid',
        'id_user',
        'status',
        'price',
        'price_reduced',
        'gid_currency',
        'price_sorting',
        'is_bestseller',
        'priority',
        'photo',
        'photo_count',
        'video',
        'video_image',
        'video_data',
        'date_created',
        'date_updated',
        'search_data',
    );
    public $fields_all            = array();
    public $fields_priority       = array('id', 'priority');
    public $file_config_gid       = "store";
    public $max_photo_count       = 5;
    public $form_editor_type      = "products";
    public $store_search_form_gid = "store_search";

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->CI         = &get_instance();
        $this->DB         = &$this->CI->db;
        $this->fields_all = $this->fields;
        $this->DB->memcache_tables(array(TABLE_STORE_PRODUCTS));
    }

    public function set_additional_fields($fields)
    {
        $this->dop_fields = $fields;
        $this->fields_all = (!empty($this->dop_fields)) ? array_merge($this->fields,
                $this->dop_fields) : $this->fields;

        return;
    }

    public function get_product_by_id($id, $lang_id = '', $is_formatted = true)
    {
        $option_fields = $this->get_fields_options($id);
        if (is_array($option_fields)) {
            $this->set_additional_fields($option_fields);
        }
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
                ->from(TABLE_STORE_PRODUCTS)
                ->where("id", $id)
                ->get()->result_array();
        if (empty($result)) {
            return false;
        } elseif ($is_formatted) {
            return $this->format_product($result[0]);
        } else {
            return $result[0];
        }
    }

    public function get_product_id_by_gid($gid, $lang_id = '')
    {
        $result = $this->DB->select('id')
                ->from(TABLE_STORE_PRODUCTS)
                ->where("gid", $gid)
                ->get()->result_array();
        if (empty($result)) {
            return false;
        } else {
            return $result[0]['id'];
        }
    }

    public function get_option_ids($id, $categories = array())
    {
        $options = array();
        $this->CI->load->model('store/models/Store_categories_model');
        if (empty($categories)) {
            $categories = $this->CI->Store_categories_model->get_categories_by_product_id($id);
        }
        $options = $this->CI->Store_categories_model->get_categories_options($categories);

        return $options;
    }

    private function get_fields_options($id)
    {
        $data   = $this->get_option_ids($id);
        $fields = array();
        foreach ($data as $key => $val) {
            if ($this->CI->db->field_exists('option_' . $val,
                    TABLE_STORE_PRODUCTS)) {
                $fields[] = 'option_' . $val;
            }
        }

        return $fields;
    }

    public function get_options($ids = array())
    {
        $params = array();
        $this->CI->load->model("store/models/Store_options_model");
        if (!empty($ids)) {
            $params['where_in']['id'] = $ids;
        }
        $options = $this->CI->Store_options_model->get_options_list(null, null,
            null, $params);

        return $options;
    }

    public function get_products_list($page = null, $items_on_page = null,
                                      $order_by = null, $params = array(),
                                      $filter_object_ids = array(),
                                      $formatted = true, $safe_format = false,
                                      $lang_id = '')
    {
        if (isset($params["fields"]) && is_array($params["fields"]) && count($params["fields"])) {
            $this->set_additional_fields($params["fields"]);
        }
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
        $options = $this->get_options();
        foreach ($options as $option) {
            $select_attrs[] = 'option_' . $option['id'];
        }
        $this->DB->select(implode(", ", $select_attrs));
        $this->DB->from(TABLE_STORE_PRODUCTS);

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
            if ($formatted) {
                $results = $this->format_products($results);
            }

            return $results;
        }

        return array();
    }

    public function get_products_count($params = array(),
                                       $filter_object_ids = null)
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
        $result = $this->DB->count_all_results(TABLE_STORE_PRODUCTS);

        return $result;
    }

    private function get_product_priority_by_id($product_id)
    {
        $result = $this->DB->select(implode(", ", $this->fields_priority))
                ->from(TABLE_STORE_PRODUCTS)
                ->where("id", $product_id)
                ->get()->result_array();
        if (empty($result)) {
            return false;
        } else {
            return $result[0];
        }
    }

    private function get_previous_priority($params = array())
    {
        $this->DB->select(implode(", ", $this->fields_priority));
        $this->DB->from(TABLE_STORE_PRODUCTS);

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
        $this->DB->limit(1);
        $results = $this->DB->get()->result_array();

        return $results[0];
    }

    public function get_last_priority($add = 0)
    {
        $result = $this->DB->select('MAX(priority) AS max_priority')->from(TABLE_STORE_PRODUCTS)->get()->result_array();
        foreach ($result as $row) {
            $return = intval($row['max_priority']) + $add;
        }

        return $return;
    }

    public function get_first_priority()
    {
        $result = $this->DB->select('MIN(priority) AS min_priority')->from(TABLE_STORE_PRODUCTS)->get()->result_array();

        return $result[0]['min_priority'];
    }

    public function get_images_product($id_product = null, $param = 'all',
                                       $id_img = 0)
    {
        $result = $this->DB->select('photo')->from(TABLE_STORE_PRODUCTS)->where("id",
                $id_product)->get()->result_array();
        $return = array();
        foreach ($result as $row) {
            if ($row['photo']) {
                $return = unserialize($row['photo']);
                if ($param == 'single') {
                    $return = array($return[$id_img]);
                }
            }
        }

        return $return;
    }

    public function get_photo_path($id_product, $size)
    {
        $media      = $this->get_images_product($id_product);
        $media_path = $this->format_media($id_product, $media, 'photo', $size);

        return $media_path;
    }

    public function get_video_path($id_product, $size)
    {
        $data         = $this->get_product_by_id($id_product);
        $format_video = $this->format_media($id_product, array($data));

        return $format_video;
    }

    public function get_photo_list($id_product = null, $param = 'all',
                                   $type = 'photo', $size = 'small')
    {
        $return            = array('content' => '');
        $images            = $this->get_images_product($id_product, $param);
        $format_images     = $this->format_media($id_product, $images, $type,
            $size);
        $tpl_data['media'] = $format_images;
        if ($this->CI->router->is_api_class) {
            return $tpl_data;
        } else {
            $this->CI->view->assign($tpl_data);
            $return['content'] = trim($this->CI->view->fetchFinal('helper_product_media',
                    'admin', 'store'));

            return $return;
        }
    }

    public function get_video_list($id_product = null)
    {
        $return            = array('content' => '');
        $data              = $this->get_product_by_id($id_product);
        $format_video      = $this->format_media($id_product, array($data));
        $tpl_data['media'] = $format_video;
        if ($this->CI->router->is_api_class) {
            return $tpl_data;
        } else {
            $this->CI->view->assign($tpl_data);
            $return['content'] = trim($this->CI->view->fetchFinal('helper_product_media',
                    'admin', 'store'));

            return $return;
        }
    }

    public function get_media_by_id($id, $id_product, $type)
    {
        if ($type == 'images') {
            $media = $this->get_images_product($id_product, 'single', $id);
        }

        return $media;
    }

    public function get_common_criteria($data)
    {
        $criteria["where"]["status"] = '1';
        if (!empty($data['ids'])) {
            $criteria["where_in"]["id"] = $data['ids'];
        }

        return $criteria;
    }

    public function get_products_fulltext_search($data)
    {
        $fe_criteria     = $common_criteria = array();
        if (!empty($data["search"])) {
            $data["search"]             = trim(strip_tags($data["search"]));
            $temp_criteria              = $this->return_fulltext($data["search"],
                'BOOLEAN MODE');
            $fe_criteria['fields'][]    = $temp_criteria['field'];
            $fe_criteria['where_sql'][] = $temp_criteria['where_sql'];
        }
        $common_criteria = $this->get_common_criteria($data);
        $criteria        = array_merge_recursive($fe_criteria, $common_criteria);

        return $criteria;
    }

    public function return_fulltext($text, $mode = null)
    {
        $word_count  = str_word_count($text);
        $arr_text    = explode(" ", $text);
        $word_count  = count($arr_text);
        $text        = ($word_count < 2 ? $text . "*" : $text);
        $escape_text = $this->DB->escape($text);
        $mode        = ($mode && $word_count < 2 ? " IN " . $mode : "");
        $return      = array(
            'field' => "MATCH (search_data) AGAINST (" . $escape_text . ") AS fields",
            'where_sql' => "MATCH (search_data) AGAINST (" . $escape_text . $mode . ")",
        );

        return $return;
    }

    public function get_options_selected($sel = array(), $option = array())
    {
        $data = array();
        $i    = 0;
        foreach ($option['option'] as $key => $value) {
            $data[$key]['name'] = $value;
            if (!empty($sel[$key])) {
                $data[$key]['sel'] = 1;
                $data[$key]['key'] = $i;
                ++$i;
            }
        }
        $option['show']   = !empty($sel) ? 1 : 0;
        $option['option'] = $data;

        return $option;
    }

    public function format_media($id_product, $media, $type = '',
                                 $size = 'small')
    {
        if (empty($media) || !is_array($media)) {
            return array();
        }
        $this->CI->load->model('Uploads_model');
        $this->CI->load->model('Video_uploads_model');
        foreach ($media as $key => &$item) {
            if ($type == 'photo') {
                if (isset($item)) {
                    $data[$key]["media"] = $this->CI->Uploads_model->format_upload($this->file_config_gid,
                        $id_product, $item);
                }
            } else {
                if (!empty($item["video_data"])) {
                    $item["video_data"] = unserialize($item["video_data"]);
                }
                if (!empty($item["video"])) {
                    $video                       = ($item["video"] == 'embed') ? $item['video_data']['data']
                            : $item['video'];
                    $data[$key]["video_content"] = $this->CI->Video_uploads_model->format_upload($this->file_config_gid,
                        $id_product, $video, $item['video_image'],
                        $item['video_data']['data']['upload_type']);
                }
            }
            $data[$key]["info"]["product_id"] = $id_product;
            $data[$key]["info"]["media_id"]   = $key;
            $data[$key]['info']["size"]       = $size;
            $data[$key]['info']["rand"]       = rand(100000, 999999);
            if ($this->CI->session->userdata("auth_type") != "user") {
                if ($type == 'photo') {
                    $data[$key]['info']['remove_url'] = site_url() . "admin/store/delete_photo/" . $id_product . "/" . $key;
                } else {
                    $data[$key]['info']['remove_url'] = site_url() . "admin/store/delete_video/" . $id_product . "/" . $key;
                }
            }
        }

        return $data;
    }

    public function format_products($data)
    {
        $this->CI->load->model('Uploads_model');
        $this->CI->load->model('store/models/Store_categories_model');
        foreach ($data as $key => $products) {
            if (!empty($products["price"])) {
                $products["price"] = (double) $products["price"];
            }
            if (!empty($products["price_reduced"])) {
                $products["price_reduced"] = (double) $products["price_reduced"];
            }
            if (!empty($products["id"])) {
                $products["postfix"] = $products["id"];
            }
            $categories = $this->CI->Store_categories_model->get_categories_by_product_id($products["id"]);
            $option_ids = $this->get_option_ids($products["id"], $categories);
            if (!empty($option_ids)) {
                $products['options'] = $this->get_options($option_ids);
            }

            if ($products["priority"] == $this->get_first_priority()) {
                $products["sort"]["first"] = 1;
            }
            if ($products["priority"] == $this->get_last_priority(0)) {
                $products["sort"]["last"] = 1;
            }

            $product_photo = unserialize($products["photo"]);

            if (!empty($product_photo)) {
                $products["photo"]              = $product_photo[0];
                $products["media"]["mediafile"] = $this->CI->Uploads_model->format_upload($this->file_config_gid,
                    $products["postfix"], $products["photo"]);
            }
            // seo data
            if (!empty($categories)) {
                $categories_gid = $this->CI->Store_categories_model->get_gid_by_ids($categories);
                if ($this->CI->uri->rsegments[2] == 'category') {
                    foreach ($categories_gid as $gid) {
                        $pos = strpos($this->CI->uri->segments[2], $gid);
                        if ($pos > 0) {
                            $category_name = $gid;
                        }
                    }
                    $products['category_name'] = isset($category_name) ? $category_name
                            : implode('_', $categories_gid);
                } elseif ($this->CI->uri->rsegments[2] == 'product') {
                    $products['category_name'] = implode('_', $categories_gid);
                    $category_gid              = $this->CI->uri->segments[2];
                    if (!empty($category_gid)) {
                        $category_name = $this->CI->Store_categories_model->get_name_by_gid($category_gid);
                        if (!empty($category_name)) {
                            $products['category'] = $category_name;
                        }
                    }
                } else {
                    $products['category_name'] = implode('_', $categories_gid);
                }
            }
            $data[$key] = $products;
        }

        return $data;
    }

    public function format_product($data)
    {
        if ($data) {
            $return = $this->format_products(array(0 => $data));

            return $return[0];
        }

        return array();
    }

    public function validate($product_id = null, $data = array())
    {
        $return = array("errors" => array(), "data" => array());
        if (isset($data["category_id"]) && is_array($data["category_id"])) {
            foreach ($data["category_id"] as $k => $category) {
                if (isset($data["category_id"][$k])) {
                    $return["data"]["category"]["category_id"][$k] = intval($category);
                }
            }
        }
        if (empty($return["data"]["category"]["category_id"])) {
            $return["errors"]["category_id"] = l('error_category_empty', 'store');
        }
        if (isset($data["gid"])) {
            $this->CI->config->load("reg_exps", true);
            $reg_exp                          = $this->CI->config->item("not_literal",
                "reg_exps");
            $temp_gid                         = $return["data"]["product"]["gid"]
                = strtolower(trim(strip_tags($data["gid"])));
            if (!empty($temp_gid)) {
                $return["data"]["product"]["gid"] = preg_replace($reg_exp, '_',
                    $return["data"]["product"]["gid"]);
                $return["data"]["product"]["gid"] = preg_replace("/[\-]{2,}/i",
                    '_', $return["data"]["product"]["gid"]);
                $return["data"]["product"]["gid"] = trim($return["data"]["product"]["gid"],
                    '_');
                if (empty($return["data"]["product"]["gid"])) {
                    $return["data"]["product"]["gid"] = md5($temp_gid);
                }

                $params                 = array();
                $params["where"]["gid"] = $return["data"]["product"]["gid"];
                if ($product_id) {
                    $params["where"]["id <>"] = $product_id;
                }
                $count = $this->get_products_count($params);
                if ($count > 0) {
                    $return["errors"][] = l('error_gid_already_exists', 'store');
                }
            } else {
                $return["errors"][] = l('error_content_gid_invalid', 'store');
            }
        }
        if (isset($data["price"])) {
            $return["data"]["product"]["price"]         = floatval($data["price"]);
            $return["data"]["product"]["price_sorting"] = floatval($data["price"]);
        }
        if (!$return["data"]["product"]["price"]) {
            $return["errors"]["price"] = l('error_price_incorrect', 'store');
        }
        if (!empty($data["price_reduced"]) && ($data["price"] > $data["price_reduced"])) {
            $return["data"]["product"]["price_reduced"] = floatval($data["price_reduced"]);
        } else {
            $return["data"]["product"]["price_reduced"] = floatval($data["price"]);
        }
        if (isset($data["is_bestseller"])) {
            $return["data"]["product"]["is_bestseller"] = intval($data["is_bestseller"]);
        }
        $current_lang_id = $this->CI->pg_language->current_lang_id;
        foreach ($this->CI->pg_language->languages as $key => $value) {
            if (!empty($data['name_' . $value['id']])) {
                $return["data"]["product"]['name_' . $value['id']] = trim(strip_tags($data['name_' . $value['id']]));
            } else {
                $return["data"]["product"]['name_' . $value['id']] = trim(strip_tags($data['name_' . $current_lang_id]));
            }
            if (!empty($data['description_' . $value['id']])) {
                $return["data"]["product"]['description_' . $value['id']] = strip_tags($data['description_' . $value['id']]);
            } else {
                $return["data"]["product"]['description_' . $value['id']] = strip_tags($data['description_' . $current_lang_id]);
            }
        }
        if (empty($return["data"]["product"]['name_' . $current_lang_id])) {
            $return["errors"]['name_' . $current_lang_id] = l('error_name_incorrect',
                'store');
        }
        $option_ids = $this->get_option_ids($product_id, $data["category_id"]);
        foreach ($option_ids as $key => $id) {
            if (is_array($data['option'][$id])) {
                $return["data"]["product"]['option_' . $id] = serialize($data['option'][$id]);
            } elseif (isset($data['option'][$id])) {
                $k                                          = $data['option'][$id];
                $return["data"]["product"]['option_' . $id] = serialize(array($k => $k));
            } else {
                $return["data"]["product"]['option_' . $id] = '';
            }
        }
        $return["data"]["product"]["search_data"] = $this->format_fulltext_field($return["data"]["product"],
            $option_ids);

        return $return;
    }

    public function validate_image($file_name = '')
    {
        $return = array('errors' => array(), 'data' => array(), 'form_error' => 0);
        if (!empty($file_name)) {
            if (isset($_FILES[$file_name]) && is_array($_FILES[$file_name]) && !($_FILES[$file_name]["error"])) {
                $this->CI->load->model("Uploads_model");
                $file_return = $this->CI->Uploads_model->validate_upload($this->file_config_gid,
                    $file_name);

                if (!empty($file_return["error"])) {
                    $return["errors"][] = (is_array($file_return["error"])) ? implode("<br>",
                            $file_return["error"]) : $file_return["error"];
                }
                $return["data"]['mime'] = $_FILES[$file_name]["type"];
            } elseif ($_FILES[$file_name]["error"]) {
                $return["errors"][] = $_FILES[$file_name]["error"];
            } else {
                $return["errors"][] = "empty file";
            }
        }

        return $return;
    }

    public function validate_video($data, $video_name = '')
    {
        $return = array("errors" => array(), "data" => array(), 'form_error' => 0);

        $embed_data = array();
        if (!empty($data["embed_code"])) {
            $this->load->library('VideoEmbed');
            $embed_data = $this->videoembed->get_video_data($data["embed_code"]);
            if ($embed_data !== false) {
                $embed_data["string_to_save"]  = $this->videoembed->get_string_from_video_data($embed_data);
                $embed_data['upload_type']     = 'embed';
                $this->CI->load->model('uploads/models/Uploads_model');
                $return["data"]["video_image"] = $this->CI->Uploads_model->generate_filename('.jpg');
                $return["data"]["video_data"]  = serialize(array('data' => $embed_data));
                $return["data"]["video"]       = 'embed';
            } else {
                $return["errors"][] = l('error_embed_wrong', 'media');
            }
        }

        if (!empty($video_name) && !$embed_data) {
            if (isset($_FILES[$video_name]) && is_array($_FILES[$video_name]) && !($_FILES[$video_name]["error"])) {
                $this->CI->load->model("Video_uploads_model");
                $video_return = $this->CI->Video_uploads_model->validate_upload($this->file_config_gid,
                    $video_name);
                if (!empty($video_return["error"])) {
                    $return["errors"][] = (is_array($video_return["error"])) ? implode("<br>",
                            $video_return["error"]) : $video_return["error"];
                }
                $return["data"]['mime'] = $_FILES[$video_name]["type"];
            } elseif (!empty($_FILES[$video_name]["error"])) {
                $return["errors"][] = $_FILES[$video_name]["error"];
            } else {
                $return["errors"][] = l('error_file_empty', 'media');
            }
        }

        return $return;
    }

    private function format_fulltext_field($data = array(), $option_ids)
    {
        $fields = array('' => $data['price_reduced']);
        foreach ($this->CI->pg_language->languages as $key => $value) {
            $fields[] = $data['name_' . $value['id']];
            $fields[] = $data['description_' . $value['id']];
        }
        foreach ($option_ids as $id) {
            $item_values = array_merge(
                array('id' => $id),
                $this->CI->pg_language->ds->get_reference('store',
                    'store_optoins_' . $id)
            );
            $options_sel = unserialize($data['option_' . $id]);
            foreach ($options_sel as $sel) {
                $fields[] = $item_values['option'][$sel];
            }
        }
        $result = implode(",", $fields);

        return $result;
    }

    /**
     * Save image object
     *
     * @param array $attrs
     *
     * @return bool
     */
    public function save_image_product($id, $file_name = "")
    {
        $return = array('errors' => '');
        if (!empty($file_name) && !empty($id) && isset($_FILES[$file_name]) && is_array($_FILES[$file_name])
            && is_uploaded_file($_FILES[$file_name]["tmp_name"])) {
            $this->CI->load->model("Uploads_model");
            $img_return = $this->CI->Uploads_model->upload($this->file_config_gid,
                $id, $file_name);
            if (empty($img_return["errors"])) {
                $photo_arr[] = $img_return["file"];
                $photo_arr   = array_merge($this->get_images_product($id),
                    $photo_arr);
                $photo_count = count($photo_arr);
                if ($photo_count <= $this->max_photo_count) {
                    $img_data["photo"]       = serialize($photo_arr);
                    $img_data["photo_count"] = $photo_count;
                    if (!empty($id)) {
                        $this->DB->where('id', $id);
                        $this->DB->update(TABLE_STORE_PRODUCTS, $img_data);
                    }
                    $return['id']   = $id;
                    $return['file'] = $img_return["file"];
                } else {
                    $return['errors'] = l('error_max_photo_count', 'store');
                }
            } else {
                $return['errors'] = $img_return["errors"];
            }
        }

        return $return;
    }

    /**
     * Save video object
     *
     * @param array $attrs
     *
     * @return bool
     */
    public function save_video_product($id, $data, $video_name = "")
    {
        $return = array('errors' => '');
        if ($data['video'] == 'embed') {
            $this->CI->load->model("Video_uploads_model");
            $embed_data['media_video_data']  = $data['video_data'];
            $embed_data['media_video_image'] = $data['video_image'];
            $embed_data['id_owner'] = $id;
            $embed_data = $this->CI->Video_uploads_model->upload_embed_video_image($this->file_config_gid,
                $embed_data);
        }
        if (!empty($video_name) && !empty($id) && isset($_FILES[$video_name]) && is_array($_FILES[$video_name])
            && is_uploaded_file($_FILES[$video_name]["tmp_name"])) {
            $this->CI->load->model("Video_uploads_model");
            $video_return = $this->CI->Video_uploads_model->upload($this->file_config_gid,
                $id, $video_name, $id, $video_data, 'generate');
            if (empty($video_return["errors"])) {
                $data["video"]  = $video_return["file"];
                $return['id']   = $id;
                $return['file'] = $video_return["file"];
            } else {
                $return['errors'] = $video_return["errors"];
            }
        }
        if ($data['video_data']) {
            $this->DB->where('id', $id);
            $this->DB->update(TABLE_STORE_PRODUCTS, $data);
        }

        return $return;
    }

    public function save_info_product($id = null, $attrs = array())
    {
        if (is_null($id)) {
            $attrs["date_created"] = $attrs["date_updated"] = date(self::DB_DATE_FORMAT);
            $this->DB->insert(TABLE_STORE_PRODUCTS, $attrs);
            $id = $this->DB->insert_id();
        } else {
            $attrs["date_updated"] = date(self::DB_DATE_FORMAT);
            $this->DB->where('id', $id);
            $this->DB->update(TABLE_STORE_PRODUCTS, $attrs);
        }

        return $id;
    }

    public function set_status_product($id, $status = 1, $category_ids = array())
    {
        $attrs["status"] = intval($status);
        $this->save_info_product($id, $attrs);
        $this->CI->load->model("store/models/Store_categories_model");
        foreach ($category_ids as $id) {
            $this->CI->Store_categories_model->set_product_active_count($id);
            $this->CI->Store_categories_model->set_product_inactive_count($id);
        }
    }

    public function set_sort_product($id, $direction, $settings)
    {
        $params = [];
        $data_priority = $this->get_product_priority_by_id($id);

        if ($settings['filter'] == 'bestsellers') {
            $params["where"]["is_bestseller"] = 1;
        }

        if ($direction == 'up') {
            $params["where_sql"][] = " priority<'" . $data_priority['priority'] . "' AND priority>0 ";
            $params["order_by"]["priority"] = " DESC ";
        } else {
            $params["where_sql"][] = " priority>'" . $data_priority['priority'] . "' ";
            $params["order_by"]["priority"] = " ASC ";
        }
        $params["where"]["status"] = 1;
        $data_previous_priority = $this->get_previous_priority($params);

        if (!empty($data_previous_priority['id'])) {
            $this->set_priority(
                $data_previous_priority['id'],
                ['priority' => $data_priority['priority']]
            );
            $this->set_priority(
                $data_priority['id'],
                ['priority' => $data_previous_priority['priority']]
            );
        }

        return;
    }

    private function set_priority($id = null, $attrs = array())
    {
        if (!empty($id)) {
            $this->DB->where('id', $id);
            $this->DB->update(TABLE_STORE_PRODUCTS, $attrs);
        }

        return;
    }

    public function delete_product($id = null, $category_ids = array())
    {
        $data = $this->get_product_by_id($id, false);
        if (!empty($data['id'])) {
            $this->CI->load->model("store/models/Store_categories_model");
            $this->CI->Store_categories_model->delete_product($id, $category_ids);
            $this->CI->load->model('store/models/Store_bestsellers_model');
            $this->CI->Store_bestsellers_model->delete_bestsellers($id);
            $this->DB->where('id', $id);
            $this->DB->delete(TABLE_STORE_PRODUCTS);
            /// delete store photo
            $this->CI->load->model("Uploads_model");
            if ($data['photo']) {
                $this->CI->Uploads_model->delete_upload($this->file_config_gid,
                    $id . "/", $data['photo']);
            }
        }

        return;
    }

    public function delete_photo($id_product, $id_media)
    {
        $data      = $this->get_images_product($id_product);
        $file_name = $data[$id_media];
        $photo     = array();
        foreach ($data as $key => $value) {
            if ($id_media != $key) {
                $photo[] = $value;
            }
        }
        $attrs['photo']       = serialize($photo);
        $attrs['photo_count'] = count($photo);
        $this->save_info_product($id_product, $attrs);
        /// delete store photo
        $this->CI->load->model("Uploads_model");
        $this->CI->Uploads_model->delete_upload($this->file_config_gid,
            $id_product . "/", $file_name);

        return;
    }

    public function delete_video($id_product)
    {
        $data = $this->get_product_by_id($id_product);
        if ($data['video'] != 'embed') {
            $this->CI->load->model('Video_uploads_model');
            $this->CI->Video_uploads_model->delete_upload($this->file_config_gid,
                $id_product, $data['video']);
        } else {
            $this->CI->load->model('Video_uploads_model');
            $this->CI->Video_uploads_model->delete_embed_video_image($this->file_config_gid,
                $id_product, $data['video_image']);
        }
        $attrs['video_data']  = $attrs['video_image'] = $attrs['video']       = '';
        $this->save_info_product($id_product, $attrs);

        return;
    }

    public function delete_bestseller($id)
    {
        $this->CI->load->model("store/models/Store_categories_model");
        $this->CI->load->model('store/models/Store_bestsellers_model');
        $this->save_info_product($id, array('is_bestseller' => 0));
        $this->CI->Store_bestsellers_model->delete_bestsellers($id);
        $category_ids = $this->CI->Store_categories_model->get_categories_by_product_id($id);
        foreach ($category_ids as $category_id) {
            $this->CI->Store_categories_model->set_product_bestsellers_count($category_id);
        }
    }

    ////// video callback
    public function video_callback($id, $status, $data, $errors)
    {
        if (isset($data["video"])) {
            $update["video"] = $data["video"];
        }
        if (isset($data["image"])) {
            $update["video_image"] = $data["image"];
        } else {
            $update["video_image"] = "";
        }

        if ($status == 'start') {
            $update["video_data"] = array();
        }

        if (!empty($data)) {
            $update["video_data"]["data"] = $data;
        }

        $update["video_data"]["status"] = $status;
        $update["video_data"]["errors"] = $errors;
        $update["video_data"]           = serialize($update["video_data"]);
        $this->save_video_product($id, $update);

        return;
    }

    public function lang_dedicate_module_callback_add($lang_id = false)
    {
        if (!$lang_id) {
            return;
        }

        $this->CI->load->dbforge();

        $fields_n['name_' . $lang_id] = array('type' => 'VARCHAR', 'constraint' => '255',
            'null' => false);
        $this->CI->dbforge->add_column(TABLE_STORE_PRODUCTS, $fields_n);

        $default_lang_id = $this->CI->pg_language->get_default_lang_id();
        if ($lang_id != $default_lang_id) {
            $this->CI->db->set('name_' . $lang_id, 'name_' . $default_lang_id,
                false);
            $this->CI->db->update(TABLE_STORE_PRODUCTS);
        }
        $fields_d['description_' . $lang_id] = array('type' => 'TEXT', 'null' => false);
        $this->CI->dbforge->add_column(TABLE_STORE_PRODUCTS, $fields_d);
        if ($lang_id != $default_lang_id) {
            $this->CI->db->set('description_' . $lang_id,
                'description_' . $default_lang_id, false);
            $this->CI->db->update(TABLE_STORE_PRODUCTS);
        }
    }

    public function lang_dedicate_module_callback_delete($lang_id = false)
    {
        if (!$lang_id) {
            return;
        }

        $this->CI->load->dbforge();

        $table_query   = $this->CI->db->get(TABLE_STORE_PRODUCTS);
        $fields_exists = $table_query->list_fields();

        $fields = array('name_' . $lang_id, 'description_' . $lang_id);
        foreach ($fields as $field_name) {
            if (!in_array($field_name, $fields_exists)) {
                continue;
            }
            $this->CI->dbforge->drop_column(TABLE_STORE_PRODUCTS, $field_name);
        }
    }

    public function option_dedicate_module_callback_add($option_id = false)
    {
        if (!$option_id) {
            return;
        }
        if ($this->CI->db->field_exists('option_' . $option_id,
                TABLE_STORE_PRODUCTS)) {
            return;
        }

        $this->CI->load->dbforge();
        $fields['option_' . $option_id] = array('type' => 'text', 'null' => false);
        $this->CI->dbforge->add_column(TABLE_STORE_PRODUCTS, $fields);
        $this->CI->db->set('option_' . $option_id, 'option_' . $option_id, false);
        $this->CI->db->update(TABLE_STORE_PRODUCTS);
    }

    public function option_dedicate_module_callback_delete($option_id = false)
    {
        if (!$option_id) {
            return;
        }
        if (!$this->CI->db->field_exists('option_' . $option_id,
                TABLE_STORE_PRODUCTS)) {
            return;
        }

        $this->CI->load->dbforge();

        $table_query   = $this->CI->db->get(TABLE_STORE_PRODUCTS);
        $fields_exists = $table_query->list_fields();

        $fields = array('option_' . $option_id);
        foreach ($fields as $field_name) {
            if (!in_array($field_name, $fields_exists)) {
                continue;
            }
            $this->CI->dbforge->drop_column(TABLE_STORE_PRODUCTS, $field_name);
        }
    }
}