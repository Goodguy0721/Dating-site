<?php

namespace Pg\Modules\Perfect_match\Models;

/**
 * Perfect_match model
 *
 * @package 	PG_Dating
 * @subpackage 	Perfect_match
 *
 * @category	models
 *
 * @copyright 	Copyright (c) 2000-2015 PG Dating Pro - php dating software
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

define("PERFECT_MATCH_TABLE", DB_PREFIX . "perfect_match");

class Perfect_match_model extends \Model
{
    /**
     * Link to CodeIgniter object
     *
     * @var object
     */
    private $CI;

    /**
     * Link to database object
     *
     * @var object
     */
    private $DB;

    private $fields = array(
        'id_user',
        'looking_user_type',
        'looking_id_country',
        'looking_id_region',
        'looking_id_city',
        'age',
        'age_min',
        'age_max',
        'full_criteria',
        
        /* <custom_M> */
        'height',
        'height_min',
        'height_max',
        'looking_about',
        'looking_living_with',
        'looking_relationship_status',
        'looking_physical_appearance',
        'looking_eye_color',
        'looking_hair_color',
        'looking_body_type',
        'looking_ethnicity',
        'looking_religion',
        'looking_language',
        'looking_education',
        'looking_occupation',
        'looking_annual_income',
        'looking_drinking',
        'looking_smoking',
        'looking_have_children',
        'looking_want_children',
        'looking_have_pets',
        /* </custom_M> */
    );
    private $fields_all = array();
    private $linked_fields = array(
        'id_user',
        'looking_user_type',
        'looking_id_country',
        'looking_id_region',
        'looking_id_city',
        'age_min',
        'age_max',
    );
    private $linked_fields_all = array();

    public $form_editor_type = "users";

    public $perfect_match_form_gid = "perfect_match";

    /**
     * Constructor
     *
     * @return Perfect_match_model
     */
    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
        $this->DB = &$this->CI->db;
    }

    public function saveParams($user_id, $attrs = array(), $fields_type = 'linked')
    {
        $params = ($fields_type == 'linked') ? $this->getUserParams($user_id, true) : array('full_criteria' => array());
        $fields = ($fields_type == 'linked') ? $this->linked_fields_all : $this->fields_all;

        foreach ($fields as $field => $value) {
            if (isset($attrs[$value]) && $value != 'id_user') {
                $params[$value] = $attrs[$value];
                $params['full_criteria'][$value] = $attrs[$value];
            }
        }

        $fields_all = array_flip($this->fields_all);

        if (!empty($params['full_criteria'])) {
            foreach ($params['full_criteria'] as $field => $full_criteria) {
                if (!array_key_exists($field, $fields_all)) {
                    unset($params['full_criteria'][$field]);
                }
            }
        }

        if ($params) {
            $full_criteria = serialize($params['full_criteria']);
            unset($params['full_criteria']);
            $params_ins['full_criteria'] = $full_criteria;
            $params_ins['id_user'] = $user_id;
            $params_upd = array();
            foreach ($params as $field => $attr) {
                if (is_array($attr)) {
                    $arr_attr = implode(",", $attr);
                    $params_upd[] = "`{$field}`=" . $this->DB->escape($arr_attr);
                } else {
                    $params_upd[] = "`{$field}`=" . $this->DB->escape($attr);
                }
            }
            $sql = $this->DB->insert_string(PERFECT_MATCH_TABLE, $params_ins) . " ON DUPLICATE KEY UPDATE " . implode(',', $params_upd);
            $this->DB->query($sql);
        }
    }

    public function getFulltextData($id, $fields)
    {
        $return = array('main_fields' => array(), 'fe_fields' => array(), 'default_lang_id' => $this->CI->pg_language->get_default_lang_id(), 'object_lang_id' => 1);
        $this->setAdditionalFields($fields);
        $data = $this->getUserById($id);

        foreach ($fields as $field) {
            $return['fe_fields'][$field] = $data[$field];
        }

        return $return;
    }

    public function setAdditionalFields($fields)
    {
        if (!empty($fields)) {
            foreach ($fields as $field) {
                if (($key = array_search($field, $this->fields_all)) === false) {
                    $this->dop_fields[] = $field;
                }
            }
        } else {
            $this->CI->load->model('Field_editor_model');
            $fields_list = $this->CI->Field_editor_model->get_fields_list();

            if (!empty($fields_list)) {
                foreach ($fields_list as $fields => $field) {
                    if (($key = array_search($field['field_name'], $this->fields_all)) === false) {
                        $this->dop_fields[] = $field['field_name'];
                    }
                }
            }
        }
        $this->fields_all = (!empty($this->dop_fields)) ? array_merge($this->fields, $this->dop_fields) : $this->fields;
        $this->linked_fields_all = (!empty($this->dop_fields)) ? array_merge($this->linked_fields, $this->dop_fields) : $this->linked_fields;

        return;
    }

    public function getUserParams($user_id, $for_save = false)
    {
        $result = $this->getParams(array($user_id), $for_save);

        return !empty($result[$user_id]) ? $result[$user_id] : array();
    }

    private function getParams($users_ids, $for_save = false)
    {
        $users_perfect_match = $this->DB->select(implode(',', $this->fields))
            ->from(PERFECT_MATCH_TABLE)
            ->where_in('id_user', $users_ids)
            ->get()
            ->result_array();

        //get all fields from FE for current perfect match form
        $fields_all_cache = $this->fields_all;

        $this->CI->load->model('Field_editor_model');
        $this->CI->load->model('field_editor/models/Field_editor_forms_model');

        $form = $this->CI->Field_editor_forms_model->get_form_by_gid($this->perfect_match_form_gid, $this->form_editor_type);
        $fields_for_search = $this->CI->Field_editor_model->get_fields_names_for_search($form);
        $this->setAdditionalFields($fields_for_search);
        $fields_for_search_by_keys = array_flip($this->fields_all);

        $result = array();
        foreach ($users_perfect_match as $upm) {
            $user_id = $upm['id_user'];
            unset($upm['id_user']);

            if (!empty($upm['full_criteria'])) {
                $upm['full_criteria'] = unserialize($upm['full_criteria']);
                if (empty($upm['full_criteria'])) {
                    unset($upm['full_criteria']);
                    $upm['full_criteria'] = $upm;
                }
            } else {
                $upm['full_criteria'] = $upm;
            }

            //save only fields from current PM form
            $upm['full_criteria'] = array_intersect_key($upm['full_criteria'], $fields_for_search_by_keys);

            if (!$for_save) {
                $upm['user_type'] = isset($upm['looking_user_type']) ? $upm['looking_user_type'] : null;
                $upm['full_criteria']['user_type'] = isset($upm['full_criteria']['looking_user_type']) ? $upm['full_criteria']['looking_user_type'] : $upm['looking_user_type'];
                unset($upm['looking_user_type']);
                unset($upm['full_criteria']['looking_user_type']);
            }
            $result[$user_id] = $upm;
        }
        $this->fields_all = $fields_all_cache; //restore fields
        return $result;
    }

    public function validate($data, $type = 'save')
    {
        $return["errors"] = array();
        $return["data"] = array();
        if (!empty($data["id_user"])) {
            $return["data"]["id_user"] = intval($data["id_user"]);
        }
        if (!empty($data["looking_user_type"])) {
            $return["data"]["looking_user_type"] = $data["looking_user_type"];
        } elseif (!empty($data["user_type"])) {
            $return["data"]["looking_user_type"] = $data["user_type"];
        }

        if (!empty($data["age"])) {
            $return["data"]["age"] = intval($data["age"]);
        }
        if ($type == 'select' && !empty($return["data"]["looking_user_type"])) {
            $return["data"]["user_type"] = $return["data"]["looking_user_type"];
            unset($return["data"]["looking_user_type"]);
        }

        if (!empty($data["looking_id_country"])) {
            $return["data"]["looking_id_country"] = trim(strip_tags($data["looking_id_country"]));
        } elseif (!empty($data["id_country"])) {
            $return["data"]["looking_id_country"] = $data["id_country"];
        } /*else {
            $return["data"]["looking_id_country"] = "";
        }*/

        if (!empty($data["looking_id_region"])) {
            $return["data"]["looking_id_region"] = intval($data["looking_id_region"]);
        } elseif (isset($data["id_region"]) && (int) $data["id_region"] >= 0) {
            $return["data"]["looking_id_region"] = intval($data["id_region"]);
        }/* else {
            $return["data"]["looking_id_region"] = "";
        }*/

        if (!empty($data["looking_id_city"])) {
            $return["data"]["looking_id_city"] = intval($data["looking_id_city"]);
        } elseif (isset($data["id_city"]) && (int) $data["id_city"] >= 0) {
            $return["data"]["looking_id_city"] = intval($data["id_city"]);
        }/* else {
            $return["data"]["looking_id_city"] = "";
        }*/

        $age_min = $this->pg_module->get_module_config('users', 'age_min');
        $age_max = $this->pg_module->get_module_config('users', 'age_max');
        if (!empty($data['age_min'])) {
            $return["data"]["age_min"] = intval($data['age_min']);
            if ($return["data"]["age_min"] < $age_min || $return["data"]["age_min"] > $age_max) {
                $return["data"]["age_min"] = $age_min;
            }
        }
        if (!empty($data['age_max'])) {
            $return["data"]["age_max"] = intval($data['age_max']);
            if ($return["data"]["age_max"] < $age_min || $return["data"]["age_max"] > $age_max) {
                $return["data"]["age_max"] = $age_max;
            }
            if (!empty($return["data"]["age_min"]) && $return["data"]["age_min"] > $return["data"]["age_max"]) {
                $return["data"]["age_min"] = $age_min;
            }
        }
        
        /* <custom_M> */
        if (array_key_exists('looking_about', $data)) {
            $return['data']['looking_about'] = trim(strip_tags($data['looking_about']));
        }
        
        if (array_key_exists('looking_living_with', $data)) {
            //$return['data']['looking_living_with'] = trim(strip_tags($data['looking_living_with']));
            
            if (is_array($data["looking_living_with"])) {
                $looking_living_with = array();
                
                foreach ($data["looking_living_with"] as $type) {
                    $looking_living_with[] = '[' . $type . ']';
                }
                
                $return["data"]["looking_living_with"] = implode(',', $looking_living_with);
            } else {
                
            }
        }
        
        if (array_key_exists('looking_relationship_status', $data)) {
            $return['data']['looking_relationship_status'] = (int)$data['looking_relationship_status'];            
        }
        
        if (array_key_exists('height', $data)) {
            $return['data']['height'] = (int)$data['height'];            
        }
        
        if (array_key_exists('height_min', $data)) {
            $return['data']['height_min'] = (int)$data['height_min'];            
        }
        
        if (array_key_exists('height_max', $data)) {
            $return['data']['height_max'] = (int)$data['height_max'];            
        }
        
        if (array_key_exists('looking_physical_appearance', $data)) {
            $return['data']['looking_physical_appearance'] = (int)$data['looking_physical_appearance'];            
        }
        
        if (array_key_exists('looking_eye_color', $data)) {
            $return['data']['looking_eye_color'] = (int)$data['looking_eye_color'];            
        }
        
        if (array_key_exists('looking_hair_color', $data)) {
            $return['data']['looking_hair_color'] = (int)$data['looking_hair_color'];            
        }
        
        if (array_key_exists('looking_body_type', $data)) {
            $return['data']['looking_body_type'] = (int)$data['looking_body_type'];            
        }
        
        if (array_key_exists('looking_ethnicity', $data)) {
            $return['data']['looking_ethnicity'] = (int)$data['looking_ethnicity'];            
        }
        
        if (array_key_exists('looking_religion', $data)) {
            $return['data']['looking_religion'] = (int)$data['looking_religion'];            
        }
        
        if (array_key_exists('looking_language', $data)) {
            $return['data']['looking_language'] = (int)$data['looking_language'];            
        }
        
        if (array_key_exists('looking_education', $data)) {
            $return['data']['looking_education'] = (int)$data['looking_education'];            
        }
        
        if (array_key_exists('looking_occupation', $data)) {
            $return['data']['looking_occupation'] = (int)$data['looking_occupation'];            
        }
        
        if (array_key_exists('looking_annual_income', $data)) {
            $return['data']['looking_annual_income'] = (int)$data['looking_annual_income'];            
        }
        
        if (array_key_exists('looking_drinking', $data)) {
            $return['data']['looking_drinking'] = (int)$data['looking_drinking'];            
        }
        
        if (array_key_exists('looking_smoking', $data)) {
            $return['data']['looking_smoking'] = (int)$data['looking_smoking'];            
        }
        
        if (array_key_exists('looking_have_children', $data)) {
            $return['data']['looking_have_children'] = (int)$data['looking_have_children'];            
        }
        
        if (array_key_exists('looking_want_children', $data)) {
            $return['data']['looking_want_children'] = (int)$data['looking_want_children'];            
        }
        
        if (array_key_exists('looking_have_pets', $data)) {
            $return['data']['looking_have_pets'] = (int)$data['looking_have_pets'];            
        }
        /* </custom_M> */

        $fields_list = $this->CI->Field_editor_model->get_fields_list();

        if (!empty($fields_list)) {
            foreach ($data as $key => $value) {
                foreach ($fields_list as $fields => $field) {
                    if ($field['field_name'] == $key) {
                        $return["data"][$key] = $value;
                    }
                }
            }
        }

        return $return;
    }

    /**
     * $attrs array
     * $user_id integer user id
     *
     * @return void
     **/
    public function user_updated($attrs = array(), $user_id = null)
    {
        $validate = $this->validate($attrs);

        if (!$validate['errors']) {
            $id_user = $this->getUserById($user_id);

            $full_criteria = array_intersect_key($validate['data'], array_flip($this->linked_fields));

            if (!$id_user) {
                $this->load->model('Users_model');
                $user_info = $this->Users_model->get_user_by_id($user_id);

                $validate['data']['id_user'] = $user_id;
                $validate['data']['user_type'] = $user_info['user_type'];
                $validate['data']['age'] = $user_info['age'];
                $validate['data']['full_criteria'] = serialize($full_criteria);

                $this->DB->insert(PERFECT_MATCH_TABLE, $validate['data']);
            } elseif (!empty($validate['data'])) {
                $validate['data']['full_criteria'] = serialize($full_criteria);
                $this->DB->where('id_user', $user_id);
                $this->DB->update(PERFECT_MATCH_TABLE, $validate['data']);
            }
        }

        return;
    }

    /**
     * $user_id integer user id
     *
     * @return bool
     **/
    public function getUserById($user_id = null)
    {
        $this->DB->select();
        $this->DB->where('id_user', $user_id);
        $this->DB->from(PERFECT_MATCH_TABLE);
        $results = $this->DB->get()->result_array();
        if (!empty($results) && is_array($results)) {
            return true;
        }

        return false;
    }

    /**
     * $page integer page number
     * $items_on_page integer number records on page
     * $order_by column sort
     * $params array
     * $filter_object_ids array
     * $formatted bool
     * $safe_format bool
     * $lang_id integer id language
     *
     * @return bool
     **/
    public function getUsersList($page = null, $items_on_page = null, $order_by = array("id_user" => "DESC"), $params = array(), $filter_object_ids = array(), $formatted = true, $safe_format = false, $lang_id = '')
    {
        if (empty($params["fields"])) {
            $params["fields"] = array();
        }
        $this->setAdditionalFields($params["fields"]);

        $fields_all = array();
        foreach ($this->fields_all as $key => $value) {
            $fields_all[] = $value;
        }

        $this->DB->select(implode(", ", $this->fields_all));
        $this->DB->from(PERFECT_MATCH_TABLE);

        if (!empty($params["where"]) && is_array($params["where"]) && count($params["where"])) {
            foreach ($params["where"] as $field => $value) {
                $this->DB->where($field, $value);
            }
        }

        if (!empty($params["like"]) && is_array($params["like"]) && count($params["like"])) {
            foreach ($params["like"] as $field => $value) {
                $this->DB->like($field, $value);
            }
        }

        if (!empty($params["where_in"]) && is_array($params["where_in"]) && count($params["where_in"])) {
            foreach ($params["where_in"] as $field => $value) {
                $this->DB->where_in($field, $value);
            }
        }

        if (!empty($params["where_sql"]) && is_array($params["where_sql"]) && count($params["where_sql"])) {
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
                $id_user_array = array();
                foreach ($results as $key => $values) {
                    if (!empty($values['id_user'])) {
                        $id_user_array[] = $values['id_user'];
                        continue;
                    }
                }
                $this->CI->load->model('users/models/Users_model');

                if (!empty($order_by["id_user"])) {
                    $order_by = array("date_created" => $order_by["id_user"]);
                    unset($order_by["id_user"]);
                }

                $results = $this->CI->Users_model->get_users_list(null, null, $order_by, array(), $id_user_array);
            }

            return $results;
        }

        return array();
    }

    public function getUsersCount($params = array(), $filter_object_ids = null)
    {
        if (!empty($params["where"]) && is_array($params["where"]) && count($params["where"])) {
            foreach ($params["where"] as $field => $value) {
                $this->DB->where($field, $value);
            }
        }

        if (!empty($params["like"]) && is_array($params["like"]) && count($params["like"])) {
            foreach ($params["like"] as $field => $value) {
                $this->DB->like($field, $value);
            }
        }

        if (!empty($params["where_sql"]) && is_array($params["where_sql"]) && count($params["where_sql"])) {
            foreach ($params["where_sql"] as $value) {
                $this->DB->where($value, null, false);
            }
        }

        if (isset($params["where_in"]) && is_array($params["where_in"]) && count($params["where_in"])) {
            foreach ($params["where_in"] as $field => $value) {
                $this->DB->where_in($field, $value);
            }
        }

        if (!empty($filter_object_ids) && is_array($filter_object_ids) && count($filter_object_ids)) {
            $this->DB->where_in("id", $filter_object_ids);
        }
        $result = $this->DB->count_all_results(PERFECT_MATCH_TABLE);

        return $result;
    }

    public function getCommonCriteria($data)
    {
        if (!empty($data['age_min'])) {
            $criteria["where"]["age >="] = intval($data["age_min"]);
        }
        if (!empty($data['age_max'])) {
            $criteria["where"]["age <="] = intval($data["age_max"]);
        }
        if (!empty($data['looking_user_type']) && $data['looking_user_type'] != 'all') {
            $criteria["where"]["user_type"] = $data["looking_user_type"];
        } elseif (!empty($data['user_type']) && $data['user_type'] != 'all') {
            $criteria["where"]["user_type"] = $data["user_type"];
        }

        if ($this->session->userdata('auth_type') == 'user') {
            $criteria["where"]["id_user !="] = $this->session->userdata('user_id');
        }

        if (!empty($data["looking_id_country"])) {
            $criteria["where"]["id_country"] = $data["looking_id_country"];
        } elseif (!empty($data["id_country"])) {
            $criteria["where"]["id_country"] = $data["id_country"];
        }
        if (!empty($data["looking_id_region"])) {
            $criteria["where"]["id_region"] = $data["looking_id_region"];
        } elseif (!empty($data["id_region"])) {
            $criteria["where"]["id_region"] = $data["id_region"];
        }
        if (!empty($data["looking_id_city"])) {
            $criteria["where"]["id_city"] = $data["looking_id_city"];
        } elseif (!empty($data["id_city"])) {
            $criteria["where"]["id_city"] = $data["id_city"];
        }

        return $criteria;
    }

    public function callback_user_delete($id_user)
    {
        if (!empty($id_user)) {
            $this->DB->where('id_user', $id_user);
            $this->DB->delete(PERFECT_MATCH_TABLE);
        }
    }

    /**
     *  Seo settings
     *
     *  @param string $method
     *  @param integer $lang_id
     *
     *  @return void
     */
    public function getSeoSettings($method = '', $lang_id = '')
    {
        if (!empty($method)) {
            return $this->_getSeoSettings($method, $lang_id);
        } else {
            $actions = array('index');
            $return = array();
            foreach ($actions as $action) {
                $return[$action] = $this->_getSeoSettings($action, $lang_id);
            }

            return $return;
        }
    }

    private function _getSeoSettings($method, $lang_id = '')
    {
        switch ($method) {
            case 'index':
                return array(
                    "templates" => array(),
                    "url_vars"  => array(),
                ); break;
        }
    }

    public function requestSeoRewrite($var_name_from, $var_name_to, $value)
    {
        if ($var_name_from == $var_name_to) {
            return $value;
        }

        return $value;
    }

    /**
     *  Site map xml
     *
     *  @return array
     */
    public function getSitemapXmlUrls()
    {
        $this->CI->load->helper('seo');
        $return = array();

        return $return;
    }

    /**
     *  Banner pages
     *
     *  @return array
     */
    public function bannerAvailablePages()
    {
        $return[] = array("link" => "perfect_match/index", "name" => l('search_results', 'users'));

        return $return;
    }
}
