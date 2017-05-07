<?php

namespace Pg\Modules\Associations\Models;

/**
 * Associations main model
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
if (!defined('TABLE_ASSOCIATIONS')) {
    define('TABLE_ASSOCIATIONS', DB_PREFIX . 'associations');
}
if (!defined('TABLE_ASSOCIATIONS_USERS')) {
    define('TABLE_ASSOCIATIONS_USERS', DB_PREFIX . 'associations_users');
}

class Associations_model extends \Model
{
    const DB_DATE_FORMAT = 'Y-m-d H:i:s';
    const DB_DATE_FORMAT_SEARCH = 'Y-m-d H:i';
    const DB_DEFAULT_DATE = '0000-00-00 00:00:00';

    private $CI;
    private $DB;
    public $upload_gid = 'associations';
    private $_ds_gid = 'chat_message';
    private $_modules_category = array(
        'communication',
        'action',
    );
    private $_fields = array(
        'id',
        'id_user',
        'img',
        'date_created',
        'is_active',
    );
    private $_fields_users = array(
        'id',
        'id_user',
        'id_profile',
        'img',
        'answer',
        'date_created',
    );

    /**
     *  Constructor
     *
     *  @return Associations_model
     */
    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
        $this->DB = &$this->CI->db;
    }

    /**
     *  Language table field
     *
     *  @param integer $lang_id
     *
     *  @return void
     */
    private function fields_all($lang_id = null)
    {
        $fields = array();
        if (is_null($lang_id)) {
            $lang_ids = $this->CI->pg_language->languages;
            foreach ($lang_ids as $lang) {
                $fields[] = 'name_' . $lang['id'];
                $fields[] = 'view_name_' . $lang['id'];
            }
        } else {
            $default_lang_id = $this->CI->pg_language->current_lang_id;
            $fields[] = 'name_' . $default_lang_id;
            $fields[] = 'view_name_' . $default_lang_id;
        }

        return $fields;
    }

    /**
     *  Language users table field
     *
     *  @param integer $lang_id
     *
     *  @return void
     */
    private function fields_users_all($lang_id = null)
    {
        $fields = array();
        if (is_null($lang_id)) {
            $lang_ids = $this->CI->pg_language->languages;
            foreach ($lang_ids as $lang) {
                $fields[] = 'name_' . $lang['id'];
            }
        } else {
            $default_lang_id = $this->CI->pg_language->current_lang_id;
            $fields[] = 'name_' . $default_lang_id;
        }

        return $fields;
    }

    /**
     *  Settings
     *
     *  @return array
     */
    public function getSettings()
    {
        $data = array(
            'is_active'    => $this->CI->pg_module->get_module_config('associations', 'is_active'),
            'chat_message' => $this->CI->pg_module->get_module_config('associations', 'chat_message'),
            'chat_more'    => $this->CI->pg_module->get_module_config('associations', 'chat_more'),
        );

        return $this->formatSettings($data);
    }

    /**
     *  Include module
     *
     *  @param integer $select
     *
     *  @return array
     */
    public function getActionModules($select = null)
    {
        $return = array();
        $data = ($this->CI->session->userdata("auth_type") == "user") ? array($select) : $this->getModules();
        foreach ($data as $value) {
            if (!empty($value) && $value !== 'associations') {
                $this->CI->load->model($value . '/models/' . ucfirst($value . '_model'));
                if (method_exists($this->CI->{ucfirst($value . '_model')}, 'moduleCategoryAction')) {
                    $return[$value] = $this->CI->{ucfirst($value . '_model')}->moduleCategoryAction();
                    $return[$value]['selected'] = ($select == $value) ? 1 : 0;
                }
            }
        }

        return $return;
    }

    /**
     *  Load modules
     *
     *  @return array
     */
    private function getModules()
    {
        $result = array();
        $modules = $this->CI->pg_module->get_modules();
        foreach ($modules as $module) {
            if (in_array($module['category'], $this->_modules_category)) {
                $result[] = $module['module_gid'];
            }
        }

        return $result;
    }

    /**
     *  Description action
     *
     *  @param string $setting_gid
     *
     *  @return array
     */
    private function getMessageFields($setting_gid)
    {
        foreach ($this->CI->pg_language->languages as $lid => $lang) {
            $r = $this->CI->pg_language->ds->get_reference('associations', $this->_ds_gid, $lid);
            $lang_data[$lid] = $r["option"][$setting_gid];
        }

        return $lang_data;
    }

    /**
     *  Image association
     *
     *  @param integer $lang_id
     *  @param array $data
     *
     *  @return array
     */
    public function getImage($data = array(), $lang_id = null)
    {
        $fields = $this->fields_all($lang_id);
        $select = array_merge($fields, $this->_fields);
        $this->DB->select(implode(", ", $select));
        $this->DB->from(TABLE_ASSOCIATIONS);
        foreach ($data as $field => $value) {
            $this->DB->where($field, $value);
        }
        $result = $this->DB->get()->result_array();

        return empty($result) ? false : $this->formatImage($result[0]);
    }

    /**
     *  List images
     *
     *  @param integer $page
     *  @param integer $limits
     *  @param integer $order_by
     *  @param array $params
     *  @param integer $lang_id
     *  @param array $user_data
     *
     *  @return array
     */
    public function getListImages($page = null, $limits = null, $order_by = null, $params = array(), $lang_id = null, $user_data = array())
    {
        $fields = $this->fields_all($lang_id);
        $select = array_merge($fields, $this->_fields);

        $this->DB->select(implode(", ", $select));
        $this->DB->from(TABLE_ASSOCIATIONS);

        if (isset($params['where']) && is_array($params['where']) && count($params['where'])) {
            foreach ($params['where'] as $field => $value) {
                $this->DB->where($field, $value);
            }
        }

        if (isset($params['where_in']) && is_array($params['where_in']) && count($params['where_in'])) {
            foreach ($params['where_in'] as $field => $value) {
                $this->DB->where_in($field, $value);
            }
        }

        if (isset($params['where_not_in']) && is_array($params['where_not_in']) && count($params['where_not_in'])) {
            foreach ($params['where_not_in'] as $field => $value) {
                $this->DB->where_not_in($field, $value);
            }
        }

        if (isset($params['where_sql']) && is_array($params['where_sql']) && count($params['where_sql'])) {
            foreach ($params['where_sql'] as $value) {
                $this->DB->where($value, null, false);
            }
        }

        if (is_array($order_by) && count($order_by) > 0) {
            foreach ($order_by as $field => $dir) {
                if (in_array($field, $this->_fields)) {
                    $this->DB->order_by($field . ' ' . $dir);
                }
            }
        } elseif ($order_by) {
            $this->DB->order_by($order_by);
        }

        if (!is_null($page)) {
            $page = intval($page) ? intval($page) : 1;
            $this->DB->limit($limits, $limits * ($page - 1));
        }

        if (isset($params['limit']['count'])) {
            if (isset($params['limit']['from'])) {
                $this->DB->limit($params['limit']['count'], $params['limit']['from']);
            } else {
                $this->DB->limit($params['limit']['count']);
            }
        }

        $results = $this->DB->get()->result_array();
        if (!empty($results) && is_array($results)) {
            return $this->formatImages($results, $user_data);
        }

        return array();
    }

    /**
     *  Count of images in the table
     *
     *  @param array $params
     *
     *  @return void
     */
    public function getImagesCount($params = array())
    {
        $this->DB->select('COUNT(id) AS cnt');
        $this->DB->from(TABLE_ASSOCIATIONS);

        if (isset($params['where']) && is_array($params['where']) && count($params['where'])) {
            foreach ($params['where'] as $field => $value) {
                $this->DB->where($field, $value);
            }
        }

        if (isset($params['where_in']) && is_array($params['where_in']) && count($params['where_in'])) {
            foreach ($params['where_in'] as $field => $value) {
                $this->DB->where_in($field, $value);
            }
        }

        if (isset($params['where_not_in']) && is_array($params['where_not_in']) && count($params['where_not_in'])) {
            foreach ($params['where_not_in'] as $field => $value) {
                $this->DB->where_not_in($field, $value);
            }
        }

        if (isset($params['where_sql']) && is_array($params['where_sql']) && count($params['where_sql'])) {
            foreach ($params['where_sql'] as $value) {
                $this->DB->where($value, null, false);
            }
        }

        $results = $this->DB->get()->result_array();
        if (!empty($results) && is_array($results)) {
            return intval($results[0]['cnt']);
        }

        return 0;
    }

    /**
     *  User associations
     *
     *  @param integer $user_id
     *
     *  @return array
     */
    public function getAssociationsUser($page = null, $limits = null, $order_by = null, $params = array(), $lang_id = null, $user_data = array())
    {
        $fields = $this->fields_users_all($lang_id);
        $select = array_merge($fields, $this->_fields_users);

        $this->DB->select(implode(", ", $select));
        $this->DB->from(TABLE_ASSOCIATIONS_USERS);

        if (isset($params['where']) && is_array($params['where']) && count($params['where'])) {
            foreach ($params['where'] as $field => $value) {
                $this->DB->where($field, $value);
            }
        }

        if (isset($params['where_in']) && is_array($params['where_in']) && count($params['where_in'])) {
            foreach ($params['where_in'] as $field => $value) {
                $this->DB->where_in($field, $value);
            }
        }

        if (isset($params['where_not_in']) && is_array($params['where_not_in']) && count($params['where_not_in'])) {
            foreach ($params['where_not_in'] as $field => $value) {
                $this->DB->where_not_in($field, $value);
            }
        }

        if (isset($params['where_sql']) && is_array($params['where_sql']) && count($params['where_sql'])) {
            foreach ($params['where_sql'] as $value) {
                $this->DB->where($value, null, false);
            }
        }

        if (is_array($order_by) && count($order_by) > 0) {
            foreach ($order_by as $field => $dir) {
                if (in_array($field, $this->_fields)) {
                    $this->DB->order_by($field . ' ' . $dir);
                }
            }
        } elseif ($order_by) {
            $this->DB->order_by($order_by);
        }

        if (!is_null($page)) {
            $page = intval($page) ? intval($page) : 1;
            $this->DB->limit($limits, $limits * ($page - 1));
        }

        $result = $this->DB->get()->result_array();

        return empty($result) ? false : $this->formatAssociationsUser($result);
    }

    public function getAssociationsUserCount($params = array())
    {
        $this->DB->select('COUNT(id) AS cnt');
        $this->DB->from(TABLE_ASSOCIATIONS_USERS);

        if (isset($params['where']) && is_array($params['where']) && count($params['where'])) {
            foreach ($params['where'] as $field => $value) {
                $this->DB->where($field, $value);
            }
        }

        if (isset($params['where_in']) && is_array($params['where_in']) && count($params['where_in'])) {
            foreach ($params['where_in'] as $field => $value) {
                $this->DB->where_in($field, $value);
            }
        }

        if (isset($params['where_not_in']) && is_array($params['where_not_in']) && count($params['where_not_in'])) {
            foreach ($params['where_not_in'] as $field => $value) {
                $this->DB->where_not_in($field, $value);
            }
        }

        if (isset($params['where_sql']) && is_array($params['where_sql']) && count($params['where_sql'])) {
            foreach ($params['where_sql'] as $value) {
                $this->DB->where($value, null, false);
            }
        }

        $results = $this->DB->get()->result_array();
        if (!empty($results) && is_array($results)) {
            return intval($results[0]['cnt']);
        }

        return 0;
    }

    /**
     *  Validate uses association
     *
     *  @param integer $profile_id
     *  @param array $data
     *
     *  @return void
     */
    public function validateAssociationUser($profile_id = null, $data = array())
    {
        $return = array("errors" => array(), "data" => array());

        if (!is_null($profile_id)) {
            $return["data"]["id_profile"] = intval($profile_id);
            $return["data"]["id_user"] = $this->CI->session->userdata('user_id');
            if (empty($return["data"]["id_profile"])) {
                $return["errors"][] = l('error_system', 'associations');
            } else {
                $params = array(
                    'where' => array(
                        'id_user'    => $return["data"]["id_user"],
                        'id_profile' => $return["data"]["id_profile"],
                    ),
                );
                $count_user = $this->getAssociationsUserCount($params);
                if ($count_user > 0) {
                    $return["errors"][] = l('error_already_sent', 'associations');
                }
            }
        }

        $lang_ids = $this->CI->pg_language->languages;
        foreach ($lang_ids as $lang) {
            $return["data"]['name_' . $lang['id']] = $data['view_name_' . $lang['id']];
        }

        if (isset($data['img'])) {
            $return["data"]["img"] = $data['id'] . '_' . $data['img'];
            if (empty($return["data"]["img"])) {
                $return["errors"][] = l('error_system', 'associations');
            }
        }

        return $return;
    }

    /**
     *  Validate settings
     *
     *  @param array $data
     *
     *  @return array
     */
    public function validateSettings($data)
    {
        $return = array("errors" => array(), "data" => array());

        if (isset($data['is_active'])) {
            $return["data"]["is_active"] = intval($data["is_active"]);
        }

        if (isset($data['chat_message'])) {
            foreach ($this->CI->pg_language->languages as $key => $value) {
                $return['ds'][$value['id']] = strip_tags($data['chat_message'][$value['id']]);
            }
        }

        if (isset($data['chat_more'])) {
            $return["data"]["chat_more"] = trim(strip_tags($data["chat_more"]));
        }

        return $return;
    }

    /**
     *  Validation Association
     *
     *  @param array $data
     *
     *  @return array
     */
    public function validateImage($id = null, $data = array())
    {
        $return = array("errors" => array(), "data" => array());

        $current_lang_id = $this->CI->pg_language->current_lang_id;
        foreach ($this->CI->pg_language->languages as $key => $value) {
            if (!empty($data['name_' . $value['id']])) {
                $return["data"]['name_' . $value['id']] = trim(strip_tags($data['name_' . $value['id']]));
            } else {
                $return["data"]['name_' . $value['id']] = trim(strip_tags($data['name_' . $current_lang_id]));
            }
            if (!empty($data['view_name_' . $value['id']])) {
                $return["data"]['view_name_' . $value['id']] = trim(strip_tags($data['view_name_' . $value['id']]));
            } else {
                $return["data"]['view_name_' . $value['id']] = trim(strip_tags($data['view_name_' . $current_lang_id]));
            }
        }

        if (empty($return["data"]['name_' . $current_lang_id])) {
            $return["errors"]['name_' . $current_lang_id] = l('error_name_incorrect', 'associations');
        }

        if (empty($return["data"]['view_name_' . $current_lang_id])) {
            $return["errors"]['view_name_' . $current_lang_id] = l('error_view_name_incorrect', 'associations');
        }

        if (isset($_FILES[$this->upload_gid]) && is_array($_FILES[$this->upload_gid]) && is_uploaded_file($_FILES[$this->upload_gid]["tmp_name"])) {
            $this->CI->load->model("Uploads_model");
            $img_return = $this->CI->Uploads_model->validate_upload($this->upload_gid, $this->upload_gid);
            if (!empty($img_return["error"])) {
                $return["errors"][] = implode("<br>", $img_return["error"]);
            }
        }

        if (is_null($id) && !is_uploaded_file($_FILES[$this->upload_gid]["tmp_name"])) {
            $return["errors"][] = l('error_upload_file', 'associations');
        }

        return $return;
    }

    /**
     *  Validation answer
     *
     *  @param array $data
     *
     *  @return array
     */
    public function validateAnswer($data = array())
    {
        $return = array("errors" => array(), "data" => array());

        if (!isset($data['id'])) {
            $return["errors"][] = l('error_no_association', 'associations');
        }

        if (isset($data['answer'])) {
            $return["data"]['answer'] = strip_tags($data['answer']);
            if (empty($return["data"]['answer'])) {
                $return["errors"][] = l('error_system', 'associations');
            }
        }

        return $return;
    }

    /**
     *  Format settings
     *
     *  @param array $data
     *
     *  @return array
     */
    private function formatSettings($data = array())
    {
        $data['is_active'] = intval($data['is_active']);
        $data['chat_message'] = $this->getMessageFields($data['chat_message']);
        $data['chat_more'] = $this->getActionModules($data['chat_more']);

        return $data;
    }

    /**
     *  Formatting Association
     *
     *  @param array $data
     *
     *  @return array
     */
    public function formatImage($data = array())
    {
        $this->CI->load->model("Uploads_model");
        $data['image'] = $this->CI->Uploads_model->format_upload($this->upload_gid, $data['id'], $data['img']);

        return $data;
    }

    /**
     *  Formatting Associations
     *
     *  @param array $data
     *
     *  @return array
     */
    public function formatImages($data = array(), $user_data = array())
    {
        $this->CI->load->model("Uploads_model");
        $lang_id = $this->CI->pg_language->current_lang_id;
        foreach ($data as $key => $value) {
            $data[$key]['name'] = $this->formatTextAssociation($value['name_' . $lang_id], $user_data);
            $data[$key]['view_name'] = $this->formatTextAssociation($value['view_name_' . $lang_id], $user_data);
            $data[$key]['image'] = $this->CI->Uploads_model->format_upload($this->upload_gid, $value['id'], $value['img']);
        }

        return $data;
    }

    /**
     *  Replace text association
     *
     *  @param string $data
     *
     *  @return string
     */
    public function formatTextAssociation($data = '', $user_data = array())
    {
        if (!empty($data)) {
            $auth = $this->CI->session->userdata("auth_type");
            if ($auth != 'admin') {
                $template = array('%username%', '%fname%', '%sname%');

                return str_replace($template, $user_data, $data);
            }
        }

        return $data;
    }

    /**
     *  Format user associations
     *
     *  @param array $data
     *
     *  @return array
     */
    private function formatAssociationsUser($data = array())
    {
        $this->CI->load->model('Users_model');
        $this->CI->load->model("Uploads_model");
        $current_lang_id = $this->CI->pg_language->current_lang_id;
        $user_id = $this->CI->session->userdata('user_id');
        $profile = $this->CI->Users_model->get_user_by_id($user_id, true);
        $users = array();
        foreach ($data as $key => $value) {
            $users['user_ids'][$key] = $value['id_user'];
            $users['profile_ids'][$key] = ($value['id_profile'] != $user_id) ? $value['id_profile'] : $user_id;
        }
        $users['user'] = $this->CI->Users_model->get_users_list_by_key(null, null, null, null, $users['user_ids']);
        $users['profile'] = $this->CI->Users_model->get_users_list_by_key(null, null, null, null, $users['profile_ids']);
        foreach ($data as $key => $value) {
            $data[$key]['user'] = $users['user'][$users['user_ids'][$key]];
            $data[$key]['profile'] = $users['profile'][$users['profile_ids'][$key]];
            $data[$key]['name'] = $this->formatTextAssociation($data[$key]['name_' . $current_lang_id], array($data[$key]['profile']['nickname'], $data[$key]['profile']['fname'], $data[$key]['profile']['sname']));
            if (!empty($data[$key]['answer'])) {
                $data[$key]['answer'] = l('field_answer_' . $data[$key]['answer'], 'associations');
            }
            $img_data = explode('_', $value['img']);
            $data[$key]['image'] = $this->CI->Uploads_model->format_upload($this->upload_gid, $img_data[0] . '/copy', $img_data[1]);
        }
        $result = array(
            'profile' => $profile,
            'list'    => $data,
        );

        return $result;
    }

    /**
     *  Add/Create Association
     *
     *  @param integer $id
     *  @param array $attrs
     *
     *  @return integer
     */
    public function saveImages($id = null, $attrs = array())
    {
        if (is_null($id)) {
            $attrs["is_active"] = 1;
            $attrs["date_created"] = date(self::DB_DATE_FORMAT);
            $this->DB->insert(TABLE_ASSOCIATIONS, $attrs);
            $id = $this->DB->insert_id();
        } else {
            $this->DB->where('id', $id);
            $this->DB->update(TABLE_ASSOCIATIONS, $attrs);
        }

        if (!empty($id) && isset($_FILES[$this->upload_gid]['tmp_name'])) {
            $this->CI->load->model('Uploads_model');
            $params = array('id' => $id);
            $upload_data = $this->getImage($params);
            if (!empty($upload_data['img'])) {
                $this->CI->Uploads_model->delete_upload($this->upload_gid, $id . "/", $upload_data['img']);
            }
            if (isset($_FILES[$this->upload_gid]['tmp_name']) && is_uploaded_file($_FILES[$this->upload_gid]['tmp_name'])) {
                $img_return = $this->CI->Uploads_model->upload($this->upload_gid, $id . '/', $this->upload_gid);
            }
            if (!empty($img_return) && empty($img_return['errors'])) {
                $data = array('img' => $img_return["file"]);
                $this->DB->where('id', $id);
                $this->DB->update(TABLE_ASSOCIATIONS, $data);
            }
        }

        return $id;
    }

    /**
     *  Add user association
     *
     *  @param array $attrs
     *
     *  @return array
     */
    public function saveAssociationUser($id = null, $attrs = array())
    {
        if (is_null($id)) {
            $attrs["date_created"] = date(self::DB_DATE_FORMAT);
            $this->DB->insert(TABLE_ASSOCIATIONS_USERS, $attrs);
            $id = $this->DB->insert_id();

            $result = array();
            if (isset($id)) {
                $img_data = explode('_', $attrs['img']);
                $this->CI->load->model('Uploads_model');
                $img_path_copy = $this->CI->Uploads_model->get_media_path($this->upload_gid, $img_data[0] . '/copy/');
                if (!file_exists($img_path_copy . $img_data[1])) {
                    $img_path = $this->CI->Uploads_model->get_media_path($this->upload_gid, $img_data[0]);
                    $this->CI->Uploads_model->upload_exist($this->upload_gid, $img_data[0] . '/copy/', $img_path . $img_data[1]);
                }
                if (empty($attrs['answer'])) {
                    $this->CI->load->model('menu/models/Indicators_model');
                    $this->CI->Indicators_model->add('new_association_item', $id, $attrs['id_profile']);
                }
                $result['success'] = l('success_sent', 'associations');
            } else {
                $result['errors'] = l('error_system', 'associations');
            }
        } else {
            $this->DB->where('id', $id);
            $this->DB->update(TABLE_ASSOCIATIONS_USERS, $attrs);

            $this->CI->load->model('menu/models/Indicators_model');
            $this->CI->Indicators_model->delete('new_association_item', array($id), false);

            $result['success'] = l('success_sent', 'associations');
        }

        return $result;
    }

    /**
     *  Save message
     *
     *  @param array $lang_data
     *
     *  @return array
     */
    public function setMessageFields($lang_data)
    {
        foreach ($lang_data as $lid => $string) {
            $option = $this->CI->pg_language->ds->get_reference('associations', $this->_ds_gid, $lid);
            $option["option"]["text"] = $string;
            $this->CI->pg_language->ds->set_module_reference('associations', $this->_ds_gid, $option, $lid);
        }

        return $lang_data;
    }

    /**
     *  Save settings
     *
     *  @param array $data
     *
     *  @return void
     */
    public function setSettings($data)
    {
        foreach ($data as $setting => $value) {
            $this->CI->pg_module->set_module_config('associations', $setting, $value);
        }

        $this->CI->load->model('Menu_model');
        $menu_data = $this->CI->Menu_model->get_menu_item_by_gid('user_associations_item');
        $item_id = !empty($menu_data) ? $menu_data['id'] : null;
        $this->CI->Menu_model->activate_menu_item($item_id, $data['is_active']);

        return;
    }

    /**
     *  Delete Image
     *
     *  @param integer $id
     *
     *  @return void
     */
    public function delete($id = null)
    {
        if (!is_null($id)) {
            $params = array('id' => $id);
            $upload_data = $this->getImage($params);
            if (!empty($upload_data)) {
                $this->CI->Uploads_model->delete_upload($this->upload_gid, $id . "/", $upload_data['img']);
                $this->DB->where('id', $id);
                $this->DB->delete(TABLE_ASSOCIATIONS);
            }
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

    /**
     *  Seo settings
     *
     *  @param string $method
     *  @param integer $lang_id
     *
     *  @return array
     */
    private function _getSeoSettings($method, $lang_id = '')
    {
        switch ($method) {
            case 'index':
                return array(
                    "templates" => array(),
                    "url_vars"  => array(),
                );
                break;
        }
    }

    /**
     *  Seo rewrite
     *
     *  @param string $var_name_from
     *  @param string $var_name_to
     *  @param string $value
     *
     *  @return string
     */
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
     *  Site map url
     *
     *  @return array
     */
    public function getSitemapUrls()
    {
        $this->CI->load->helper('seo');
        $auth = $this->CI->session->userdata("auth_type");
        $block = array();

        $block[] = array(
            "name"      => l('header_main_sections', 'associations'),
            "link"      => rewrite_link('associations', 'index'),
            "clickable" => ($auth == "user"),
            "items"     => array(),
        );

        return $block;
    }

    /**
     *  Banner pages
     *
     *  @return array
     */
    public function bannerAvailablePages()
    {
        $return[] = array("link" => "associations/index", "name" => l('header_main', 'associations'));

        return $return;
    }

    /**
     *  Callback languages add
     *
     *  @param integer $lang_id
     *
     *  @return void
     */
    public function langDedicateModuleCallbackAdd($lang_id = false)
    {
        if (!$lang_id) {
            return;
        }

        $this->CI->load->dbforge();
        $fields_n = array();
        $fields_n['name_' . $lang_id] = array('type' => 'VARCHAR', 'constraint' => '255', 'null' => false);
        $this->CI->dbforge->add_column(TABLE_ASSOCIATIONS, $fields_n);
        $this->CI->dbforge->add_column(TABLE_ASSOCIATIONS_USERS, $fields_n);

        $default_lang_id = $this->CI->pg_language->get_default_lang_id();
        if ($lang_id != $default_lang_id) {
            $this->CI->db->set('name_' . $lang_id, 'name_' . $default_lang_id, false);
            $this->CI->db->update(TABLE_ASSOCIATIONS);
            $this->CI->db->set('name_' . $lang_id, 'name_' . $default_lang_id, false);
            $this->CI->db->update(TABLE_ASSOCIATIONS_USERS);
        }

        $fields_vn = array();
        $fields_vn['view_name_' . $lang_id] = array('type' => 'VARCHAR', 'constraint' => '255', 'null' => false);
        $this->CI->dbforge->add_column(TABLE_ASSOCIATIONS, $fields_vn);

        if ($lang_id != $default_lang_id) {
            $this->CI->db->set('view_name_' . $lang_id, 'view_name_' . $default_lang_id, false);
            $this->CI->db->update(TABLE_ASSOCIATIONS);
        }
    }

    /**
     *  Callback languages delete
     *
     *  @param integer $lang_id
     *
     *  @return void
     */
    public function langDedicateModuleCallbackDelete($lang_id = false)
    {
        if (!$lang_id) {
            return;
        }

        $this->CI->load->dbforge();

        $table_query = $this->CI->db->get(TABLE_ASSOCIATIONS);
        $fields_exists = $table_query->list_fields();

        $fields = array('name_' . $lang_id, 'view_name_' . $lang_id);
        foreach ($fields as $field_name) {
            if (!in_array($field_name, $fields_exists)) {
                continue;
            }
            $this->CI->dbforge->drop_column(TABLE_ASSOCIATIONS, $field_name);
        }

        $table_query_users = $this->CI->db->get(TABLE_ASSOCIATIONS_USERS);
        $fields_exists_users = $table_query_users->list_fields();

        $fields_users = array('name_' . $lang_id);
        foreach ($fields_users as $field_user_name) {
            if (!in_array($field_user_name, $fields_exists_users)) {
                continue;
            }
            $this->CI->dbforge->drop_column(TABLE_ASSOCIATIONS_USERS, $field_user_name);
        }
    }

    /**
     *  Module category action
     *
     *  @return array
     */
    public function moduleCategoryAction()
    {
        $action = array(
            'name'   => l('link_add', 'associations'),
            'helper' => 'button',
        );

        return $action;
    }
    
    public function getNewAssociations() {
        $this->CI->load->model('menu/models/Indicators_model');
        
        $user_id = $this->session->userdata('user_id');
        $indicators = $this->CI->Indicators_model->get('user', $user_id);
        $new_associations_count = isset($indicators['new_association_item']) ? $indicators['new_association_item'] : 0;
        
        $data = array(
            'count' => $new_associations_count,
            'gid' => 'user_associations_item'
        );
        
        return $data;
    }
    
    public function backend_get_new_associations()
    {
        return $this->getNewAssociations();
    }
}
