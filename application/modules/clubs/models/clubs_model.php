<?php

/**
 * Clubs module
 *
 * @package     PG_Dating
 * @copyright   Copyright (c) 2000-2014 PG Dating Pro - php dating software
 * @author      Renat Gabdrakhmanov <renatgab@pilotgroup.net>
 */
 
namespace Pg\Modules\Clubs\Models;

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

define('CLUBS_TABLE', DB_PREFIX . 'clubs');

/**
 * Clubs main model
 * 
 * @package     PG_Dating
 * @subpackage  Clubs
 * @category    models
 * @copyright   Copyright (c) 2000-2014 PG Dating Pro - php dating software
 * @author      Renat Gabdrakhmanov <renatgab@pilotgroup.net>
 */
class Clubs_model extends \Model
{
    const DB_DATE_FORMAT = 'Y-m-d H:i:s';
    const DB_DATE_FORMAT_SEARCH = 'Y-m-d H:i';
    const DB_DEFAULT_DATE = '0000-00-00 00:00:00';
    /**
     * Module GUID
     * 
     * @var string
     */
    const MODULE_GID = 'clubs';
    /**
     * Link to CodeIgniter object
     * 
     * @var object
     */
    protected $ci;

    protected $fields = [
        'id', 
        'date_created', 
        'date_modified', 
        'title', 
        'description', 
        'is_active', 
        'category_gid', 
        'image', 
        'country_code', 
        'region_id', 
        'city_id', 
        'lat', 
        'lon', 
        'address',
        'users_count',
        'photos_count',
        'videos_count',
        'search_field',
    ];

    /**
     * Settings for formatting clubs object
     * 
     * @var array
     */
    protected $format_settings = [
        'get_media'  => false,
    ];

    public $upload_config_gid = 'club-logo';
    public $form_editor_type = 'clubs';
    public $search_form_gid = 'advanced_search';
    protected $fields_all = [];
    protected $dop_fields = [];

    /**
     * Class constructor
     * 
     * @return Clubs_model
     */
    public function __construct()
    {
        parent::__construct();
        $this->ci = & get_instance();
        $this->db->memcache_tables(['CLUBS_TABLE']);

        $this->fields_all = $this->fields;
    }

    public function setAdditionalFields($fields)
    {
        $this->dop_fields = $fields;
        $this->fields_all = (!empty($this->dop_fields)) ? array_merge($this->fields,
                $this->dop_fields) : $this->fields;
        return;
    }

    public function getObject($data = [])
    {
        $fields = $this->fields_all;
        $fields_str = implode(', ', $fields);

        $this->ci->db->select($fields_str)
            ->from(CLUBS_TABLE);

        foreach ($data as $field => $value) {
            $this->ci->db->where($field, $value);
        }
        
        $results = $this->ci->db->get()->result_array();

        if (!empty($results) && is_array($results)) {
            return $results[0];
        }

        return false;
    }

    public function setFormatSettings($name, $value = false)
    {
        if (!is_array($name)) {
            $name = [$name => $value];
        }
        foreach ($name as $key => $item) {
            $this->format_settings[$key] = $item;
        }
    }

    public function format($data)
    {
        return current($this->formatArray([$data]));
    }

    public function formatArray($data)
    { 
        $return = [];

        if (empty($data) || !is_array($data)) {
            return [];
        }

        $user_id = 0;
        if ($this->ci->session->userdata('auth_type') == 'user') {
            $user_id = $this->ci->session->userdata('user_id');
        }

        $this->ci->load->model('Uploads_model');
        $this->ci->load->model('clubs/models/Clubs_users_model');
        $for_location = $for_media = [];
        $categories = ld('categories', self::MODULE_GID);

        foreach ($data as $key => $item) {
            $for_location[$item['id']] = array(
                'country' => $item['country_code'],
                'region'  => $item['region_id'],
                'city'    => $item['city_id'],
            );

            if ($this->format_settings['get_media']) {
                $for_media[] = $item['id'];
            }

            if (!empty($item['image'])) {
                $item['mediafile'] = $this->ci->Uploads_model->format_upload($this->upload_config_gid, $item['id'], $item['image']);
            } else {
                $item['mediafile'] = $this->ci->Uploads_model->format_default_upload($this->upload_config_gid);
            }

            $item['category_str'] = array_key_exists($item['category_gid'], $categories['option']) ? $categories['option'][$item['category_gid']] : '';

            $item['is_joined'] = 0;
            if ($user_id) {
                $is_joined = $this->ci->Clubs_users_model->getCount([
                    'user_id'   => $user_id,
                    'club_id'  => $item['id'],
                ]);

                if ($is_joined) {
                    $item['is_joined'] = 1;
                }
            }

            $return[$key] = $item;
        }
        
        if (!empty($for_location)) {
            $this->ci->load->helper('countries');
            $locations = cities_output_format($for_location);
            $locations_data = get_location_data($for_location, 'city');
            foreach ($return as $key => $item) {
                $return[$key]['location']    = (isset($locations[$item['id']])) ? $locations[$item['id']] : '';
                $return[$key]['country']     = (isset($locations_data['country'][$item['country_code']])) ? $locations_data['country'][$item['country_code']]['name'] : '';
                $return[$key]['region']      = (isset($locations_data['region'][$item['region_id']])) ? $locations_data['region'][$item['region_id']]['name'] : '';
                $return[$key]['region-code'] = (isset($locations_data['region'][$item['region_id']])) ? $locations_data['region'][$item['region_id']]['code'] : '';
                $return[$key]['city']        = (isset($locations_data['city'][$item['city_id']])) ? $locations_data['city'][$item['city_id']]['name'] : '';
            }
        }

        return $return;
    }

    public function validate($id, $data = [], $file_name = '', $sections = []) 
    {
        $return = ['errors' => [], 'data' => []];

        $current_object = $this->getObject(['id' => $id]);

        if (array_key_exists('category_gid', $data)) {
            $return['data']['category_gid'] = trim(strip_tags($data['category_gid']));
            if (empty($return['data']['category_gid'])) {
                $return['errors'][] = l('error_empty_category', self::MODULE_GID);
            }        
        }  

        if (array_key_exists('title', $data)) {
            $return['data']['title'] = trim(strip_tags($data['title']));
            if (empty($return['data']['title'])) {
                $return['errors'][] = l('error_empty_title', self::MODULE_GID);
            }
        }

        if (array_key_exists('description', $data)) {
            // $return['data']['description'] = trim($data['description']);
            $return['data']['description'] = trim(strip_tags($data['description']));
            if (empty($return['data']['description'])) {
                $return['errors'][] = l('error_empty_description', self::MODULE_GID);
            }
        }

        if (array_key_exists('country_code', $data)) {
            $return['data']['country_code'] = trim(strip_tags($data['country_code']));
        }

        if (array_key_exists('region_id', $data)) {
            $return['data']['region_id'] = intval($data['region_id']);
        }

        if (array_key_exists('city_id', $data)) {
            $return['data']['city_id'] = intval($data['city_id']);
        }

        if (array_key_exists('lat', $data)) {
            $return['data']['lat'] = floatval($data['lat']);
        }

        if (array_key_exists('lon', $data)) {
            $return['data']['lon'] = floatval($data['lon']);
        }

        if (array_key_exists('address', $data)) {
            $return['data']['address'] = trim(strip_tags($data['address']));
        }

        if (array_key_exists('date_created', $data)) {
            $return['data']['date_created'] = trim(strip_tags($data['date_created']));
        }

        if (array_key_exists('date_modified', $data)) {
            $return['data']['date_modified'] = trim(strip_tags($data['date_modified']));
        }

        if (array_key_exists('is_active', $data)) {
            $return['data']['is_active'] = intval($data['is_active']) ? 1 : 0;
        }

        if (array_key_exists('users_count', $data)) {
            $return['data']['users_count'] = intval($data['users_count']);
        }

        if (array_key_exists('photos_count', $data)) {
            $return['data']['photos_count'] = intval($data['photos_count']);
        }

        if (array_key_exists('videos_count', $data)) {
            $return['data']['videos_count'] = intval($data['videos_count']);
        }

        if (!empty($file_name) && isset($_FILES[$file_name]) && is_array($_FILES[$file_name]) && is_uploaded_file($_FILES[$file_name]['tmp_name'])) {
            $this->ci->load->model('Uploads_model');
            $img_return = $this->ci->Uploads_model->validate_upload($this->upload_config_gid, $file_name);
            if (!empty($img_return['error'])) {
                $return['errors'][] = implode('<br>', $img_return['error']);
            }
        }

        if (!empty($sections)) {
            $this->ci->load->model('Field_editor_model');
            $params = [];
            $params['where_in']['section_gid'] = $sections;
            $validate_data = $this->ci->Field_editor_model->validate_fields_for_select($params, $data);
            $return['data'] = array_merge($return['data'], $validate_data['data']);
            if (!empty($validate_data['errors'])) {
                $return['errors'] = array_merge($return['errors'],
                    $validate_data['errors']);
            }
        }

        return $return;
    }

    public function save($id = null, $save_raw = [], $file_name = '') 
    {
        $current_object = $this->getObject(['id' => $id]);

        $save_raw['date_modified'] = date(self::DB_DATE_FORMAT);

        if (!is_null($id)) {
            $this->ci->db->where('id', $id);
            $this->ci->db->update(CLUBS_TABLE, $save_raw);
        } else {
            $save_raw['date_created'] = date(self::DB_DATE_FORMAT);
            $this->ci->db->insert(CLUBS_TABLE, $save_raw);
            $id = $this->ci->db->insert_id();
        }

        if (!empty($id) && !empty($file_name)) {
            $this->ci->load->model('Uploads_model');
            if (isset($_FILES[$file_name]['tmp_name']) && is_uploaded_file($_FILES[$file_name]['tmp_name'])) {
                $img_return = $this->ci->Uploads_model->upload($this->upload_config_gid, $id, $file_name);
            } elseif (file_exists($file_name)) {
                $img_return = $this->ci->Uploads_model->upload_exist($this->upload_config_gid,
                    $id, $file_name);
            }

            if (!empty($img_return) && empty($img_return['errors'])) {
                $data = ['image' => $img_return['file']];
                $this->ci->db->where('id', $id);
                $this->ci->db->update(CLUBS_TABLE, $data);
            }
        }

        $this->ci->load->model('Field_editor_model');
        $this->ci->Field_editor_model->initialize($this->form_editor_type);
        $this->ci->Field_editor_model->update_fulltext_field($id);

        return $id;
    }

    public function getList($filters = [], $page = null, $items_on_page = null, $order_by = null)
    {
        $params = $this->_getCriteria($filters);
        return $this->_getList($page, $items_on_page, $order_by, $params);
    }

    public function getCount($filters = [])
    {
        $params = $this->_getCriteria($filters);
        return $this->_getCount($params);
    }

    public function _getCriteria($filters)
    {
        $filters = ['data' => $filters, 'table' => CLUBS_TABLE, 'type' => ''];

        $params = [];

        $params['table'] = !empty($filters['table']) ? $filters['table'] : CLUBS_TABLE;

        $fields = array_flip($this->fields_all);
        foreach ($filters['data'] as $filter_name => $filter_data) {
            if (!is_array($filter_data)) {
                $filter_data = trim($filter_data);
            }
            switch ($filter_name) {
                case 'id_country': {
                    if (empty($filter_data)) {
                        break;
                    }
                    $params = array_merge_recursive($params, ['where' => ['country_code' => $filter_data]]);
                    break;
                }
                case 'id_region': {
                    if (empty($filter_data)) {
                        break;
                    }
                    $params = array_merge_recursive($params, ['where' => ['region_id' => $filter_data]]);
                    break;
                }
                case 'id_city': {
                    if (empty($filter_data)) {
                        break;
                    }
                    $params = array_merge_recursive($params, ['where' => ['city_id' => $filter_data]]);
                    break;
                }
                case 'where': 
                case 'where_in': 
                case 'where_sql':{
                    if (empty($filter_data) || !is_array($filter_data)) {
                        break;
                    }
                    $params[$filter_name] = array_merge_recursive((array)$params[$filter_name], $filter_data);
                    break;
                }
                default: {
                    if (isset($fields[$filter_name])) {
                        if (is_array($filter_data)) {
                            $params = array_merge_recursive($params, ['where_in' => [$filter_name => $filter_data]]);
                        } else {
                            $params = array_merge_recursive($params, ['where' => [$filter_name => $filter_data]]);
                        }
                    }
                    break;
                }
            }
        }

        return $params;
    }

    protected function _getList($page = null, $limits = null, $order_by = null, $params = [])
    {   
        $table = CLUBS_TABLE;
        $fields = $this->fields_all;
        
        $fields_str = implode(', ', $fields);

        if (isset($params['table']) && $params['table'] != $table) {
            $table = $params['table'];
            $fields_str = $table . '.' . implode(', ' . $table . '.', $fields);
        }

        $this->ci->db->select($fields_str);
        $this->ci->db->from($table);

        if (isset($params['where']) && is_array($params['where']) && count($params['where'])) {
            foreach ($params['where'] as $field => $value) {
                $this->ci->db->where($field, $value);
            }
        }

        if (isset($params['where_in']) && is_array($params['where_in']) && count($params['where_in'])) {
            foreach ($params['where_in'] as $field => $value) {
                $this->ci->db->where_in($field, $value);
            }
        }

        if (isset($params['where_not_in']) && is_array($params['where_not_in']) && count($params['where_not_in'])) {
            foreach ($params['where_not_in'] as $field => $value) {
                $this->ci->db->where_not_in($field, $value);
            }
        }

        if (isset($params['where_sql'])) {
            if (!is_array($params['where_sql'])) {
                $params['where_sql'] = array($params['where_sql']);
            }
            foreach ($params['where_sql'] as $value) {
                $this->ci->db->where($value, null, false);
            }
        }

        if (is_array($order_by) && count($order_by) > 0) {
            foreach ($order_by as $field => $dir) {
                if (in_array($field, $this->fields_all)) {
                    $this->ci->db->order_by($field . ' ' . $dir);
                }
            }
        } elseif ($order_by) {
            $this->ci->db->order_by($order_by);
        }

        if (!is_null($page)) {
            $page = intval($page) ? intval($page) : 1;
            $this->ci->db->limit($limits, $limits * ($page - 1));
        }

        $results = $this->ci->db->get()->result_array();
        if (!empty($results) && is_array($results)) {
            return $results;
        }
        return [];
    }

    protected function _getCount($params = null)
    {
        $table = isset($params['table']) ? $params['table'] : CLUBS_TABLE;

        $this->ci->db->select('COUNT(*) AS cnt');
        $this->ci->db->from($table);

        if (isset($params['where']) && is_array($params['where']) && count($params['where'])) {
            foreach ($params['where'] as $field => $value) {
                $this->ci->db->where($field, $value);
            }
        }

        if (isset($params['where_in']) && is_array($params['where_in']) && count($params['where_in'])) {
            foreach ($params['where_in'] as $field => $value) {
                $this->ci->db->where_in($field, $value);
            }
        }

        if (isset($params['where_not_in']) && is_array($params['where_not_in']) && count($params['where_not_in'])) {
            foreach ($params['where_not_in'] as $field => $value) {
                $this->ci->db->where_not_in($field, $value);
            }
        }

        if (isset($params['where_sql']) && is_array($params['where_sql']) && count($params['where_sql'])) {
            foreach ($params['where_sql'] as $value) {
                $this->ci->db->where($value, null, false);
            }
        }

        $results = $this->ci->db->get()->result_array();
        if (!empty($results) && is_array($results)) {
            return intval($results[0]['cnt']);
        }
        return 0;
    }

    public function delete($id) 
    {
        if (is_array($id)) {
            foreach ($id as $i) {
                $this->delete($i);
            }
        } else {
            $current_object = $this->getObject(['id' => $id]);

            $this->ci->db->where('id', $id);
            $this->ci->db->delete(CLUBS_TABLE);

            $this->ci->load->model('Uploads_model');
            if (!empty($current_object['image'])) {
                $this->ci->Uploads_model->delete_upload($this->upload_config_gid, $id, $current_object['image']);
            }

            $this->ci->load->model('clubs/models/Clubs_media_model');
            $this->ci->Clubs_media_model->callbackClubDelete($id);

            $this->ci->load->model('clubs/models/Clubs_forum_model');
            $this->ci->Clubs_forum_model->callbackClubDelete($id);

            $this->ci->load->model('clubs/models/Clubs_users_model');
            $this->ci->Clubs_users_model->callbackClubDelete($id);
        }

        return true;
    }

    public function setStatus($club_id, $status = 0) 
    {
        $save_data = [
            'is_active' => intval($status) ? 1 : 0,
        ];

        if (is_null($club_id)) {
            return false;
        }

        $this->save($club_id, $save_data);

        return $club_id;
    }

    public function getFulltextData($id, $fields) 
    {
        $return = [
            'main_fields' => [],
            'fe_fields' => [],
            'default_lang_id' => $this->ci->pg_language->get_default_lang_id(),
            'object_lang_id' => 1
        ];

        $this->setAdditionalFields($fields);
        $current_object = $this->getObject(['id' => $id]);
        $current_object = $this->format($current_object);
        $return['main_fields']    = array(
            'title'     => $current_object['title'],
            'category'  => $current_object['category_str'], 
            'location'  => $current_object['location'],
            'address'   => $current_object['address'],
        );

        foreach ($fields as $field) {
            $return['fe_fields'][$field] = $current_object[$field];
        }

        return $return;
    }

    // seo
    public function getSeoSettings($method = '', $lang_id = '')
    {
        if (!empty($method)) {
            return $this->_getSeoSettings($method, $lang_id);
        } else {
            $actions = array('index', 'view', 'media', 'forum', 'topic');
            $return  = array();
            foreach ($actions as $action) {
                $return[$action] = $this->_getSeoSettings($action, $lang_id);
            }

            return $return;
        }
    }

    private function _getSeoSettings($method, $lang_id = '')
    {
        if ($method == 'view') {
            return array(
                'templates' => array('title',
                    'location',
                    'country', 'region', 'region-code', 'city',
                    'category_str'),
                'url_vars'  => array(
                    'id' => array('id' => 'literal'),
                ),
                'url_postfix' => array(),
                'optional' => array(
                    array(
                        'title'               => 'literal',
                        'location'           => 'literal',
                        'country'            => 'literal',
                        'region'             => 'literal',
                        'region-code'        => 'literal',
                        'city'               => 'literal',
                    ),
                ),
            );
        } elseif ($method == 'index') {
            return array(
                'templates'   => array(),
                'url_vars'    => array(),
                'url_postfix' => array(
                    'order'           => array('order'           => 'literal'),
                    'order_direction' => array('order_direction' => 'literal'),
                    'page'            => array('page'            => 'numeric'),
                ),
                'optional' => array(),
            );
        } elseif ($method == 'media') {
            return array(
                'templates' => [],
                'url_vars'  => [
                    'id' => ['id' => 'literal'],
                ],
                'url_postfix' => [],
                'optional' => [],
            );
        } elseif ($method == 'forum') {
            return array(
                'templates' => [],
                'url_vars'  => [
                    'id' => ['id' => 'literal'],
                ],
                'url_postfix' => [],
                'optional' => [],
            );
        } elseif ($method == 'topic') {
            return array(
                'templates' => [],
                'url_vars'  => [
                    'id' => ['id' => 'literal'],
                ],
                'url_postfix' => [],
                'optional' => [],
            );
        }
    }

    public function requestSeoRewrite($var_name_from, $var_name_to, $value)
    {
        $user_data = array();

        if ($var_name_from == $var_name_to) {
            return $value;
        }

        show_404();
    }

    public function getSitemapXmlUrls()
    {
        return [];
    }

    public function getSitemapUrls()
    {
        return [];
    }

    /*public function bannerAvailablePages()
    {
        return [
            ['link' => 'clubs/index', 'name' => l('header_main', 'clubs')],
            ['link' => 'clubs/my', 'name' => l('header_my', 'clubs')],
        ];
    }*/

    public function searchCriteria($filters)
    {
        $return = [
            'is_active' => 1,
        ];

        if (!empty($filters['category_gid'])) {
            $return['category_gid'] = $filters['category_gid'];
        }
        if (!empty($filters['users_count'])) {
            if (!empty($filters['users_count']['min'])) {
                $return['where']['users_count >='] = intval($filters['users_count']['min']);
            }

            if (!empty($filters['users_count']['max'])) {
                $return['where']['users_count <='] = intval($filters['users_count']['max']);
            }
        }

        if (!empty($filters['country_code'])) {
            $return['country_code'] = $filters['country_code'];

            if (!empty($filters['region_id'])) {
                $return['region_id'] = $filters['region_id'];

                if (!empty($filters['city_id'])) {
                    $return['city_id'] = $filters['city_id'];
                }
            }
        }

        $this->ci->load->model('field_editor/models/Field_editor_forms_model');
        $fe_criteria = $this->ci->Field_editor_forms_model->get_search_criteria($this->search_form_gid, $filters, $this->form_editor_type, false);
        if (!empty($filters['keyword'])) {
            $filters['keyword'] = trim(strip_tags($filters['keyword']));
            $this->ci->load->model('Field_editor_model');
            $this->ci->Field_editor_model->initialize($this->form_editor_type);
            if (strlen($filters['keyword']) > 3) {
                $temp_criteria              = $this->ci->Field_editor_model->return_fulltext_criteria($filters['keyword'], 'BOOLEAN MODE');
                $fe_criteria['fields'][]    = $temp_criteria['user']['field'];
                $fe_criteria['where_sql'][] = $temp_criteria['user']['where_sql'];
            } else {
                $search_text_escape         = $this->db->escape($filters['keyword'] . '%');
                $fe_criteria['where_sql'][] = "(title LIKE " . $search_text_escape . ")";
            }
        }
        $return = array_merge_recursive($return, $fe_criteria);

        return $return;
    }

}
