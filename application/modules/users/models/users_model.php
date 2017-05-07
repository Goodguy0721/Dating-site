<?php

namespace Pg\Modules\Users\Models;

use Pg\Libraries\EventDispatcher;
use Pg\Modules\Users\Models\Events\EventUsers;
/**
 * Users main model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category    modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Katya Kashkova <katya@pilotgroup.net>
 *
 * @version $Revision: 2 $ $Date: 2010-04-02 15:07:07 +0300 (Ср, 02 апр 2010) $ $Author: kkashkova $
 * */
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

if (!defined('USERS_TABLE')) {
    define('USERS_TABLE', DB_PREFIX . 'users');
}
if (!defined('USERS_SITE_VISIT_TABLE')) {
    define('USERS_SITE_VISIT_TABLE', DB_PREFIX . 'users_site_visit');
}

class Users_model extends \Model
{
    const DB_DATE_FORMAT        = 'Y-m-d H:i:s';
    const DB_DATE_SIMPLE_FORMAT = 'Y-m-d';
    const DB_DATE_FORMAT_SEARCH = 'Y-m-d H:i';
    const DB_DEFAULT_DATE       = '0000-00-00 00:00:00';
    const HIDDEN_DATE_FORMAT    = '%Y-%m-%d';
    const GROUPS = 'all';

    /**
     * Available user types
     *
     * @var array
     */
    private $user_types        = [];
    private $user_types_groups = [
        'types' => [],
        'groups' => [
            self::GROUPS => [],
        ],
    ];

    const MODULE_GID         = 'users';
    const EVENT_USER_CHANGED = 'users_changed';
    const STATUS_ADDED       = 'user_added';
    const STATUS_SAVED       = 'user_saved';
    const STATUS_DELETED     = 'user_deleted';
    const STATUS_APPROVED    = 'user_approved';
    const TYPE_USER  = 'user';

    public $dashboard_events      = [
        self::EVENT_USER_CHANGED,
    ];
    public $CI;
    public $DB;
    public $fields = [
        'account',
        'activated_end_date',
        'activity',
        'address',
        'approved',
        'birth_date',
        'confirm',
        'date_created',
        'date_last_activity',
        'date_modified',
        'email',
        'featured_end_date',
        'fname',
        'group_id',
        'hide_on_site_end_date',
        'highlight_in_search_end_date',
        'id',
        'id_country',
        'id_region',
        'id_city',
        'id_seo_settings',
        'lang_id',
        'leader_bid',
        'leader_text',
        'leader_write_date',
        'logo_comments_count',
        'nickname',
        'online_status',
        'password',
        'phone',
        'profile_completion',
        'postal_code',
        'search_field',
        'show_adult',
        'site_status',
        'sname',
        'user_logo',
        'user_logo_moderation',
        'user_open_id',
        'user_type',
        'up_in_search_end_date',
        'views_count',
        'lat',
        'lon',
        'roles',
        'age',

        /* <custom_M> */
        'headline',
        'about_me',
        'relationship_status',
        'physical_appearance',
        'eye_color',
        'hair_color',
        'body_type',
        'religion',
        'language',
        'education',
        'political_beliefs',
        'occupation',
        'annual_income',
        'drinking',
        'smoking',
        'have_children',
        'want_children',
        'have_pets',
        'looking_for',
        'looking_distance',
        'listening_music',
        'astrological_sign',
        'living_with',
        'ethnicity',
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
        'looking_political_beliefs',
        'looking_occupation',
        'looking_annual_income',
        'looking_drinking',
        'looking_smoking',
        'looking_have_children',
        'looking_want_children',
        'looking_have_pets',

        'custom_status',
        'is_verified',
        'is_subscribed',
        /* </custom_M> */
    ];
    public $fields_al = [];
    public $safe_fields = [
        'age',
        'date_last_activity',
        'id',
        'is_activated',
        'is_featured',
        'is_hide_on_site',
        'is_highlight_in_search',
        'is_up_in_search',
        'lang_id',
        'link',
        'logo_comments_count',
        'media',
        'nickname',
        'online_status',
        'output_name',
        'site_status',
        'statuses',
        'user_logo',
        'user_type',
        'user_type_str',
        'views_count',
    ];
    public $fields_register       = [
        'age',
        'email',
        'nickname',
        'password',
        'user_type',
        'id_country',
        'id_region',
        'id_city',
        'postal_code',
    ];
    public $fields_not_editable   = [
        //'fname',
        //'sname',
        //'birth_date',
        //'user_type',
    ];
    public $fields_for_activation = [
        'birth_date',
        'id_region',
        'id_country',
        'nickname',
        'user_logo',
        'user_type',
        'confirm',
    ];
    public $fields_completion     = [
        'email',
        'nickname',
        'user_type',
        'fname',
        'sname',
        'user_logo',
        'id_country',
        'id_region',
        'id_city',
        'birth_date',
        'age',
        'living_with',
        'looking_living_with',
        'ethnicity',
        'looking_ethnicity',
        'relationship_status',
        'looking_relationship_status',
        'height',
        'height_min',
        'height_max',
        'headline',
        'about_me',
        'looking_about',
        'physical_appearance',
        'looking_physical_appearance',
        'eye_color',
        'looking_eye_color',
        'hair_color',
        'looking_hair_color',
        'body_type',
        'looking_body_type',
        'religion',
        'looking_religion',
        'language',
        'looking_language',
        'education',
        'looking_education',
        'political_beliefs',
        'looking_political_beliefs',
        'occupation',
        'looking_occupation',
        'annual_income',
        'looking_annual_income',
        'drinking',
        'looking_drinking',
        'smoking',
        'looking_smoking',
        'have_children',
        'looking_have_children',
        'want_children',
        'looking_want_children',
        'have_pets',
        'looking_have_pets',
        'looking_for',
        'looking_distance',
        'listening_music',
        'astrological_sign',
    ];

    public $services_buy_gids     = [
        'users_featured',
        'admin_approve',
        'hide_on_site',
        'highlight_in_search',
        'up_in_search',
    ];
    public $profile_before;
    public $profile_after;

    /**
     * Ratings data properties
     *
     * @var array
     */
    protected $fields_ratings = [
        "rating_count",
        "rating_sorter",
        "rating_value",
        "rating_type",
    ];
    public $upload_config_id = "user-logo";
    public $moderation_type = ['user_logo', 'user_data'];
    public $form_editor_type = self::MODULE_GID;
    public $advanced_search_form_gid = "advanced_search";
    private $dop_fields = [];
    private $demo_user = [
        'email'       => 'basil@mail.com',
        'nickname'    => 'Basil',
        'password'    => '123456',
        'birth_date'  => '1989-08-30',
        'region_name' => 'United Kingdom, London',
        'id_country'  => 'GB',
        'id_region'   => '4',
        'id_city'     => '4',
        'user_type'   => 'male',
    ];

    public $dictionaries = [
        'height',
        'looking_for',
        'distance',
        'listening_music',
        'astrological_sign',
    ];

    public $multiselect = [
        'relationship_status',
        'physical_appearance',
        'eye_color',
        'hair_color',
        'body_type',
        'ethnicity',
        'religion',
        'language',
        'education',
        'political_beliefs',
        'occupation',
        'annual_income',
        'drinking',
        'smoking',
        'have_children',
        'want_children',
        'have_pets',
    ];

    /**
     * Constructor
     *
     * @return users object
     */
    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
        $this->DB = &$this->CI->db;
        if ($this->CI->pg_module->is_module_installed('network')) {
            $net_fields   = \Pg\Modules\Network\Models\Network_users_model::getUsersFields();
            $this->fields = array_merge($this->fields, $net_fields);
        }
        if ($this->CI->pg_module->is_module_installed("ratings")) {
            $this->fields = array_merge($this->fields, $this->fields_ratings);
        }

        if ($this->CI->pg_module->is_module_installed('perfect_match')) {
            $this->fields[]                       = 'looking_user_type';
            $this->fields[]                       = 'age_min';
            $this->fields[]                       = 'age_max';
            $this->demo_user['looking_user_type'] = 'female';
            $this->fields_for_activation[]        = 'looking_user_type';
            $this->fields_register[]              = 'looking_user_type';
            $this->safe_fields[]                  = 'looking_user_type';
            $this->safe_fields[]                  = 'looking_user_type_str';
        }
        $this->fields_all = $this->fields;

        $this->dictionaries = array_merge($this->dictionaries, $this->multiselect);
    }

    /**
     * Get user types
     *
     * @return array
     */
    public function getUserTypes($group = null)
    {
        $this->CI->load->model('users/models/Users_types_model');
        // TODO: cache
        // TODO: remove one property
        $this->user_types                 = array_column($this->CI->Users_types_model->getTypes(),
            'name');
        $this->user_types_groups['types'] = $this->user_types;
        if ($group) {
            return $this->user_types_groups['groups'][$group];
        }
        return $this->user_types_groups['types'];
    }

    public function getUserTypesGroups($params = [], $langs_ids = null)
    {
        $this->user_types_groups['types'] = $this->user_types;
        $this->CI->load->model('users/models/Groups_model');

        $groups = $this->CI->Groups_model->getGroupsList($params, $langs_ids);

        foreach ($groups as $group) {
            $this->user_types_groups['groups'][self::GROUPS][$group['gid']] = $group;
        }

        return $this->user_types_groups['groups'];
    }

    /**
     * Return available user types with names
     *
     * @return array
     */
    public function getUserTypesNames()
    {
        $user_types = [];
        foreach ($this->getUserTypes() as $user_type) {
            $user_types[$user_type] = l($user_type, 'users');
        }

        return $user_types;
    }

    public function set_additional_fields($fields)
    {
        $this->dop_fields = $fields;
        $this->fields_all = (!empty($this->dop_fields)) ? array_merge($this->fields,
                $this->dop_fields) : $this->fields;
        return;
    }

    public function get_user_by_id($user_id, $formatted = false, $safe_format = false)
    {
        $result = $this->DB->select(implode(", ", $this->fields_all))
                ->from(USERS_TABLE)
                ->where("id", $user_id)
                ->get()->result_array();
        if (empty($result)) {
            return false;
        } elseif ($formatted) {
            return $this->format_user($result[0], $safe_format);
        } else {
            return $result[0];
        }
    }

    private function getUser(array $data)
    {
        $this->DB->select(implode(", ", $this->fields_all))
            ->from(USERS_TABLE);
        foreach ($data as $field => $value) {
            $this->DB->where($field, $value);
        }
        $result = $this->DB->get()->result_array();

        if (empty($result)) {
            return false;
        }

        // TODO: check acl

        return $result[0];
    }

    public function get_user_by_login_password($login, $password)
    {
        return $this->getUser(array('nickname' => $login, 'password' => $password));
    }

    public function get_user_by_login($login)
    {
        return $this->getUser(array('nickname' => $login));
    }

    public function get_user_by_email_password($email, $password)
    {
        return $this->getUser(array('email' => $email, 'password' => $password));
    }

    public function get_user_by_email($email)
    {
        return $this->getUser(array('email' => $email));
    }

    public function get_user_by_confirm_code($code)
    {
        return $this->getUser(array('confirm_code' => $code));
    }

    public function get_user_by_open_id($open_id)
    {
        return $this->getUser(array('user_open_id' => $open_id));
    }

    public function get_all_users_id()
    {
        $result = $this->DB->select('id')
                ->from(USERS_TABLE)
                ->get()->result_array();
        $return = [];
        foreach ($result as $row) {
            $return[] = $row['id'];
        }

        return $return;
    }

    public function get_users_list($page = null, $items_on_page = null, $order_by = null, $params = [], $filter_object_ids = [], $formatted = true, $safe_format
    = false, $lang_id = '')
    {
        if (isset($params["fields"]) && is_array($params["fields"]) && count($params["fields"])) {
            $this->set_additional_fields($params["fields"]);
        }

        $this->DB->select(implode(", ", $this->fields_all));
        $this->DB->from(USERS_TABLE);

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
                if (in_array($field, $this->fields_all) || $field == 'fields' || $field == 'relavation') {
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
                $results = $this->format_users($results, $safe_format);
            }

            return $results;
        }

        return [];
    }

    public function get_users_list_by_key($page = null, $items_on_page = null, $order_by = null, $params = [], $filter_object_ids = [], $formatted = true, $safe_format
    = false)
    {
        $list = $this->get_users_list($page, $items_on_page, $order_by, $params,
            $filter_object_ids, $formatted, $safe_format);
        if (!empty($list)) {
            foreach ($list as $l) {
                $data[$l["id"]] = $l;
            }

            return $data;
        } else {
            return array();
        }
    }

    public function get_featured_users($count = 20, $user_type = 0)
    {
        $params['where']['featured_end_date !='] = self::DB_DEFAULT_DATE;
        $params['where']['user_logo !='] = '';
        if ($user_type) {
            $params['where']['user_type'] = $user_type;
        }
        $order_by['featured_end_date'] = 'DESC';

        return $this->get_users_list(1, $count, $order_by, $params);
    }

    public function get_online_users($count = 20, $user_type = 0, $params = [])
    {
        $params['where']['confirm']  = '1';
        $params['where']['approved'] = '1';
        $params['where']['activity'] = '1';
        $params['where']['online_status'] = '1';
        if ($user_type) {
            $params['where']['user_type'] = $user_type;
        }
        $order_by['date_last_activity'] = 'DESC';

        if (!empty($params['order_by'])) {
            $order_by = [$params['order_by']['field'] => $params['order_by']['direction']];
        }

        return $this->get_users_list(1, $count, $order_by, $params);
    }

    public function get_active_users($count = 20, $user_type = 0, $params = [])
    {
        $params['where']['confirm']  = '1';
        $params['where']['approved'] = '1';
        $params['where']['activity'] = '1';
        if ($user_type) {
            $params['where']['user_type'] = $user_type;
        }
        $order_by['date_last_activity'] = 'DESC';

        if (!empty($params['order_by'])) {
            $order_by = [$params['order_by']['field'] => $params['order_by']['direction']];
        }

        return $this->get_users_list(1, $count, $order_by, $params);
    }

    public function get_new_users($count = 20, $user_type = 0)
    {
        $params['where']['confirm']  = '1';
        $params['where']['approved'] = '1';
        $params['where']['activity'] = '1';
        if ($user_type) {
            $params['where']['user_type'] = $user_type;
        }
        $order_by['date_created'] = 'DESC';

        return $this->get_users_list(1, $count, $order_by, $params, [], true);
    }

    public function get_users_count($params = [], $filter_object_ids = null)
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
        $result = $this->DB->count_all_results(USERS_TABLE);

        return $result;
    }

    public function get_active_users_list($page = null, $items_on_page = null, $order_by = null, $params = [], $filter_object_ids = null, $formatted = true)
    {
        $params["where"]['approved'] = 1;
        $params["where"]['confirm']  = 1;

        return $this->get_users_list($page, $items_on_page, $order_by, $params, $filter_object_ids, $formatted);
    }

    public function get_active_users_count($params = [], $filter_object_ids = null)
    {
        $params["where"]['approved'] = 1;
        $params["where"]['confirm']  = 1;

        return $this->get_users_count($params, $filter_object_ids);
    }

    public function get_active_users_types_count($params = [], $filter_object_ids = null)
    {
        $params["where"]['approved'] = 1;
        $params["where"]['confirm']  = 1;
        if (isset($params["where"]) && is_array($params["where"]) && count($params["where"])) {
            $this->DB->where($params["where"]);
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
        $result = $this->DB->select('user_type, COUNT(user_type) AS count')
                ->from(USERS_TABLE)
                ->group_by('user_type')
                ->get()->result_array();

        return $result;
    }

    public function update_user_status($user_id, $attrs)
    {
        $this->DB->where('id_contact', $user_id);
        $this->DB->update(IM_CONTACT_LIST_TABLE, $attrs);

        return true;
    }

    public function simply_update_user($user_id, $attrs)
    {
        $fe_settings = $this->config->item('editor_type', 'field_editor');
        $fe_prefix   = !empty($fe_settings['users']['field_prefix']) ? $fe_settings['users']['field_prefix'] : '';
        foreach ((array) $this->fields_not_editable as $field_not_editable) {
            unset($attrs[$field_not_editable],
                $attrs[$fe_prefix . $field_not_editable]);
        }
        $this->DB->where('id', $user_id);
        $this->DB->update(USERS_TABLE, $attrs);

        return $this->DB->affected_rows();
    }

    public function save_user($user_id = null, $attrs = [], $file_name = "", $use_icon_moderation
    = true, $load_image = false)
    {
        $is_new_user = is_null($user_id);

        if (!$is_new_user && !$load_image) {
            $this->profile_before = $this->get_user_by_id($user_id);
        }

        if ($is_new_user) {
            if (isset($attrs['roles'])) {
                $attrs['roles'] = $this->rolesEncode($attrs['roles']);
            }
            if (empty($attrs["date_created"])) {
                $attrs["date_created"]  = $attrs["date_modified"] = date(self::DB_DATE_FORMAT);
            }
            if ($this->CI->pg_module->is_module_installed('network')) {
                $this->CI->load->model('network/models/Network_users_model');
                $attrs = $this->CI->Network_users_model->pre_create($attrs);
            }
            $this->DB->insert(USERS_TABLE, $attrs);
            $user_id = $this->DB->insert_id();
        } else {
            $attrs['date_modified'] = date(self::DB_DATE_FORMAT);
            if (!empty($attrs["birth_date"])) {
                $attrs["birth_date"] = date(self::DB_DATE_FORMAT,
                    strtotime($attrs["birth_date"]));
            }
            $fe_settings = $this->config->item('editor_type', 'field_editor');
            $fe_prefix   = !empty($fe_settings['users']['field_prefix']) ? $fe_settings['users']['field_prefix'] : '';
            foreach ((array) $this->fields_not_editable as $field_not_editable) {
                unset($attrs[$field_not_editable],
                    $attrs[$fe_prefix . $field_not_editable]);
            }
            if ($this->CI->pg_module->is_module_installed('network')) {
                $this->CI->load->model('network/models/Network_users_model');
                $this->CI->Network_users_model->user_updated($user_id, $attrs);
            }
            $this->DB->where('id', $user_id);
            $this->DB->update(USERS_TABLE, $attrs);
        }

        //$this->update_age(array($user_id));

        if (!empty($file_name) && !empty($user_id)) {
            $this->CI->load->model('Uploads_model');
            if (isset($_FILES[$file_name]['tmp_name']) && is_uploaded_file($_FILES[$file_name]['tmp_name'])) {
                $img_return = $this->CI->Uploads_model->upload($this->upload_config_id,
                    $user_id, $file_name);
            } elseif (file_exists($file_name)) {
                $img_return = $this->CI->Uploads_model->upload_exist($this->upload_config_id,
                    $user_id, $file_name);
            }

            if (!empty($img_return) && empty($img_return['errors'])) {
                $this->CI->load->model('Moderation_model');
                $mstatus = intval($this->CI->Moderation_model->get_moderation_type_status($this->moderation_type[0]));

                if ($mstatus != 2 && $use_icon_moderation) {
                    $this->CI->Moderation_model->add_moderation_item($this->moderation_type[0], $user_id);
                    if ($mstatus == 1) {
                        $img_data['user_logo'] = $img_return['file'];
                        $img_data['user_logo_moderation'] = '';
                    } else {
                        $img_data['user_logo'] = $img_return['file'];
                        $img_data['user_logo_moderation'] = $img_return['file'];
                    }
                } else {
                    $img_data['user_logo'] = $img_return['file'];
                }

                $mtype = $this->CI->Moderation_model->get_moderation_type($this->moderation_type[0]);
                if ($mtype['mtype'] > 0) {
                    $this->load->model('menu/models/Indicators_model');
                    $this->Indicators_model->add('new_moderation_item', $user_id);
                }

                $this->save_user($user_id, $img_data, '', true, true);

                // Update user logo
                //$this->CI->load->model('Uploads_model');
                //$user_logo = $this->CI->Uploads_model->format_upload($this->upload_config_id, $user_id, $img_data['user_logo']);
                //$this->CI->session->set_userdata('logo', $user_logo['thumbs']['small']);
            }
        }

        setcookie('available_activation', '', (time() - 31500000), '/' . SITE_SUBFOLDER, COOKIE_SITE_SERVER, 0);

        $available_activation = $this->check_available_user_activation($user_id);
        if (!$available_activation['status']) {
            $this->DB->set('activity', 0)->where('id', $user_id)->update(USERS_TABLE);
        }

        $this->update_profile_completion($user_id);

        $this->CI->load->model('Field_editor_model');
        $this->CI->Field_editor_model->initialize($this->form_editor_type);
        $fields_for_select = $this->CI->Field_editor_model->get_fields_for_select();
        $this->CI->Field_editor_model->update_fulltext_field($user_id);

        if ($this->CI->pg_module->is_module_installed('perfect_match')) {
            $this->CI->load->model('Perfect_match_model');
            $this->CI->Perfect_match_model->user_updated($attrs, $user_id);
        }

        if ($this->CI->pg_module->get_module_config('referral_links',
                'is_active') && isset($attrs['fk_referral_id'])) {
            $this->CI->load->model('Referral_links_model');
            $this->CI->Referral_links_model->updateReferral($attrs, $user_id);
        }

        if ($this->CI->pg_module->is_module_installed('nearest_users')) {
            $this->CI->load->model('Nearest_users_model');
            $search_settings = $this->CI->Nearest_users_model->getSearchData();
            if (!empty($attrs['lat']) || !empty($attrs['lon'])) {
                $search_settings['lon'] = $attrs['lon'];
                $search_settings['lat'] = $attrs['lat'];
                $this->CI->session->set_userdata('nearest_users_search',
                    $search_settings);
            }
        }

        if (!$is_new_user && !$load_image) {
            $this->profile_after = $this->get_user_by_id($user_id);
            $this->updateUserProfile($user_id);
        }

        $this->sendEvent(self::EVENT_USER_CHANGED,
            [
            'id' => $user_id,
            'type' => self::TYPE_USER,
            'status' => $is_new_user ? self::STATUS_ADDED : self::STATUS_SAVED,
        ]);

        return $user_id;
    }

    protected function sendEvent($event_gid, $event_data)
    {
        $event_data['module'] = Users_model::MODULE_GID;
        $event_data['action'] = $event_gid;

        $event = new EventUsers();
        $event->setData($event_data);

        $event_handler = EventDispatcher::getInstance();
        $event_handler->dispatch($event_gid, $event);
    }

    public function check_available_user_activation($user_id)
    {
        $user             = $this->get_user_by_id($user_id);
        $result['status'] = 0;
        $result['fields'] = array();
        if ($user) {
            if (!empty($user['net_is_incomer'])) {
                $result['status'] = 1;
            } else {
                foreach ($this->fields_for_activation as $attr) {
                    if (!(isset($user[$attr]) && $user[$attr])) {
                        $result['fields'][] = $attr;
                    }

                    if ($attr == 'user_logo' && isset($user['user_logo_moderation']) && $user['user_logo_moderation']) {
                        $result['fields'][] = 'user_logo_moderation';
                    }
                }

                $result['status'] = ($result['fields']) ? 0 : 1;
            }
        }

        return $result;
    }

    public function set_user_confirm($user_id, $status = 1)
    {
        $attrs["confirm"] = intval($status);

        return is_null($user_id) ? false : $this->save_user($user_id, $attrs);
    }

    public function set_user_approve($user_id, $status = 1)
    {
        $attrs["approved"] = intval($status);

        if (is_null($user_id)) {
            return false;
        }

        $this->save_user($user_id, $attrs);

        $this->sendEvent(self::EVENT_USER_CHANGED,
            [
            'id' => $user_id,
            'type' => self::TYPE_USER,
            'status' => self::STATUS_APPROVED,
        ]);

        return $user_id;
    }

    public function set_user_activity($user_id, $status = 1)
    {
        $attrs["activity"] = intval($status);

        return is_null($user_id) ? false : $this->save_user($user_id, $attrs);
    }

    public function activate_user($user_id, $status = 1)
    {
        return $this->set_user_approve($user_id, $status);
    }

    public function validate($user_id = null, $data = [], $file_name = "", $section_gid = null, $type = 'select')
    {
        $return = ["errors" => [], "data" => []];

        $auth = $this->CI->session->userdata("auth_type");

        if (isset($data["roles"])) {
            $return["data"]["roles"] = $data["roles"];
        }

        if (isset($data["user_logo"])) {
            $return["data"]["user_logo"] = strip_tags($data["user_logo"]);
        }

        if (isset($data["user_logo_moderation"])) {
            $return["data"]["user_logo_moderation"] = strip_tags($data["user_logo_moderation"]);
        }
        $this->CI->load->model('Moderation_model');

        if (isset($data["fk_referral_id"]) && is_numeric($data["fk_referral_id"]) && $this->CI->pg_module->get_module_config('referral_links', 'is_active')) {
            $return["data"]["fk_referral_id"] = intval(strip_tags($data["fk_referral_id"]));
            $is_user  = $this->get_user_by_id($return["data"]["fk_referral_id"]);
            if (!$is_user) {
                unset($return["data"]["fk_referral_id"]);
            }
        }

        if (isset($data["fname"])) {
            $return["data"]["fname"] = strip_tags($data["fname"]);
            $bw_count = $this->CI->Moderation_badwords_model->check_badwords($this->moderation_type[1],
                $return["data"]["fname"]);
            if ($bw_count) {
                $return['errors']['fname'] = l('error_badwords_fname', 'users');
            }
        }

        if (isset($data["sname"])) {
            $return["data"]["sname"] = strip_tags($data["sname"]);
            $bw_count = $this->CI->Moderation_badwords_model->check_badwords($this->moderation_type[1], $return["data"]["sname"]);
            if ($bw_count) {
                $return['errors']['sname'] = l('error_badwords_sname', self::MODULE_GID);
            }
        }

        if (isset($data["user_type"])) {
            if ($type == 'select') {
                if (is_array($data["user_type"])) {
                    $user_type = in_array("undefined", $data["user_type"]) ? array_slice($data["user_type"], 1) : $data["user_type"];
                    $return["data"]["user_type"] = count($user_type) > 1 ? 0 : $user_type[0];
                } else {
                    $return["data"]["user_type"] = $data["user_type"];
                }
            } else {
                $return["data"]["user_type"] = $data["user_type"];
            }
            $return["success"]["user_type"] = "";
        }

        if ($this->CI->pg_module->is_module_installed('perfect_match')) {
            if (isset($data["looking_user_type"])) {
                $return["data"]["looking_user_type"]    = $data["looking_user_type"];
                $return["success"]["looking_user_type"] = "";
            }

            $age_min = $this->pg_module->get_module_config('users', 'age_min');
            $age_max = $this->pg_module->get_module_config('users', 'age_max');
            if (isset($data['age_min'])) {
                $return["data"]["age_min"] = intval($data['age_min']);
                if ($return["data"]["age_min"] < $age_min || $return["data"]["age_min"] > $age_max) {
                    $return["data"]["age_min"] = $age_min;
                }
            }
            if (isset($data['age_max'])) {
                $return["data"]["age_max"] = intval($data['age_max']);
                if ($return["data"]["age_max"] < $age_min || $return["data"]["age_max"] > $age_max) {
                    $return["data"]["age_max"] = $age_max;
                }
                if (!empty($return["data"]["age_min"]) && $return["data"]["age_min"] > $return["data"]["age_max"]) {
                    $return["data"]["age_min"] = $age_min;
                }
            }
        }

        $this->CI->config->load('reg_exps', true);
        if (isset($data["nickname"])) {
            $login_expr = $this->CI->config->item('nickname', 'reg_exps');
            $return["data"]["nickname"] = strip_tags($data["nickname"]);
            if (empty($return["data"]["nickname"]) || !preg_match($login_expr, $return["data"]["nickname"])) {
                $return["errors"]["nickname"] = l('error_nickname_incorrect', self::MODULE_GID);
            }
            $params = [];
            $params["where"]["nickname"] = $return["data"]["nickname"];
            if ($user_id) {
                $params["where"]["id <>"] = $user_id;
            }
            $count = $this->get_users_count($params);
            if ($count > 0) {
                $return["errors"]["nickname"] = l('error_nickname_already_exists', self::MODULE_GID);
            }
            if (empty($return["errors"]["nickname"])) {
                $return["success"]["nickname"] = "";
            }
            $bw_count = $this->CI->Moderation_badwords_model->check_badwords($this->moderation_type[1], $return["data"]["nickname"]);
            if ($bw_count) {
                $return['errors']['nickname'] = l('error_badwords_nickname', self::MODULE_GID);
            }
        }

        if (isset($data["user_open_id"])) {
            $return["data"]["user_open_id"] = trim($data["user_open_id"]);
        }

        if (isset($data["id_country"])) {
            $return["data"]["id_country"] = $data["id_country"];
        }

        if (isset($data["id_region"])) {
            $return["data"]["id_region"] = intval($data["id_region"]);
        }

        if (isset($data["id_city"])) {
            $return["data"]["id_city"] = intval($data["id_city"]);
        }

        if (isset($data['lat'])) {
            $return['data']['lat'] = floatval($data['lat']);
        }

        if (isset($data['lon'])) {
            $return['data']['lon'] = floatval($data['lon']);
        }

        if (isset($data["phone"])) {
            $return["data"]["phone"] = trim(strip_tags($data["phone"]));
        }

        if (isset($data["address"])) {
            $return["data"]["address"] = trim(strip_tags($data["address"]));
        }

        if (isset($data["birth_date"])) {
            if (!empty($data["birth_date"])) {
                $return["data"]["birth_date"] = trim(strip_tags($data["birth_date"]));

                $datetime = date_create($return["data"]["birth_date"]);
                if ($datetime) {
                    $user_age = $datetime->diff(date_create('today'))->y;
                } else {
                    $user_age = 0;
                }

                if ($this->CI->pg_module->is_module_installed('perfect_match')) {
                    if ($user_age < $age_min) {
                        $return["errors"]["birth_date"] = str_replace("[age]", $age_min, l("error_user_too_young", self::MODULE_GID));
                    } elseif ($user_age > $age_max) {
                        $return["errors"]["birth_date"] = str_replace("[age]", $age_max, l("error_user_too_old", self::MODULE_GID));
                    } else {
                        $return["success"]["birth_date"] = "";
                    }
                } else {
                    $return["success"]["birth_date"] = "";
                }
            }

            if (empty($return["data"]["birth_date"])) {
                $return["errors"]["birth_date"] = str_replace("[age]", $age_min, l("error_user_too_young", self::MODULE_GID));
            }

            $data['age'] = $user_age;
        }

        if (isset($data["age"])) {
            $return["data"]["age"] = intval($data["age"]);
            if ($this->CI->pg_module->is_module_installed('perfect_match')) {
                if ($return["data"]["age"] < $age_min) {
                    $return["errors"]["age"] = str_replace("[age]", $age_min, l("error_user_too_young", self::MODULE_GID));
                } elseif ($return["data"]["age"] > $age_max) {
                    $return["errors"]["age"] = str_replace("[age]", $age_max, l("error_user_too_old", self::MODULE_GID));
                } else {
                    $return["success"]["age"] = "";
                }
            } else {
                $return["success"]["age"] = "";
            }
        }

        if (isset($data["show_adult"])) {
            $return["data"]["show_adult"] = intval($data["show_adult"]);
        }

        if (isset($data["profile_completion"])) {
            $return["data"]["profile_completion"] = intval($data["profile_completion"]);
        }

        if (isset($data["postal_code"])) {
            $return["data"]["postal_code"] = trim(strip_tags($data["postal_code"]));
        }

        if (empty($user_id) && !isset($data["group_id"])) {
            $this->CI->load->model('users/models/Groups_model');
            $return["data"]["group_id"] = $this->CI->Groups_model->getDefaultGroupId();
        } elseif (isset($data["group_id"])) {
            $return["data"]["group_id"] = intval($data["group_id"]);
        }

        if (isset($data["email"])) {
            $email_expr              = $this->CI->config->item('email', 'reg_exps');
            $return["data"]["email"] = strip_tags($data["email"]);
            if (empty($return["data"]["email"]) || !preg_match($email_expr, $return["data"]["email"])) {
                $return["errors"]["email"] = l('error_email_incorrect', self::MODULE_GID);
            } else {
                unset($params);
                $params["where"]["email"] = $return["data"]["email"];
                if ($user_id) {
                    $params["where"]["id <>"] = $user_id;
                }
                $count = $this->get_users_count($params);
                if ($count > 0) {
                    $return["errors"]["email"] = l('error_email_already_exists', self::MODULE_GID);
                }
            }
            if (empty($return["errors"]["email"])) {
                $return["success"]["email"] = "";
            }
        }
        if (isset($data["password"])) {
            if (empty($data["password"])) {
                $return["errors"]["password"] = l('error_password_empty', self::MODULE_GID);
            } elseif ($this->pg_module->get_module_config(self::MODULE_GID, 'use_repassword') && $data["password"] != $data["repassword"]) {
                $return["errors"]["repassword"] = l('error_pass_repass_not_equal', self::MODULE_GID);
            } else {
                $password_expr    = $this->CI->config->item('password', 'reg_exps');
                $data["password"] = trim(strip_tags($data["password"]));
                if (!preg_match($password_expr, $data["password"])) {
                    $return["errors"]["password"] = l('error_password_incorrect', self::MODULE_GID);
                } else {
                    $return["data"]["password"] = $data["password"];
                }
            }
        }

        if(isset($data["captcha_confirmation"])) {
            if (empty($data["captcha_confirmation"]) || $data["captcha_confirmation"] != $this->CI->session->userdata('captcha_word')) {
                $return["errors"]['captcha_confirmation'] = l('error_invalid_captcha', self::MODULE_GID);
            }
        }

        if (empty($data["confirmation"]) && empty($user_id) && $auth !== 'admin') {
            $return["errors"]['confirmation'] = l('error_no_confirmation', self::MODULE_GID);
        }

        if (!empty($file_name) && isset($_FILES[$file_name]) && is_array($_FILES[$file_name]) && is_uploaded_file($_FILES[$file_name]["tmp_name"])) {
            $this->CI->load->model("Uploads_model");
            $img_return = $this->CI->Uploads_model->validate_upload($this->upload_config_id,
                $file_name);
            if (!empty($img_return["error"])) {
                $return["errors"][] = implode("<br>", $img_return["error"]);
            }
        }

        if (isset($data["confirm"])) {
            $return["data"]["confirm"] = intval($data["confirm"]);
        }

        if (isset($data["approved"])) {
            $return["data"]["approved"] = intval($data["approved"]);
        }

        if (isset($data["activity"])) {
            $return["data"]["activity"] = intval($data["activity"]);
        }

        /* <custom_M> */
        if (array_key_exists('headline', $data)) {
            $return['data']['headline'] = trim(strip_tags($data['headline']));
        }

        if (array_key_exists('about_me', $data)) {
            $return['data']['about_me'] = trim(strip_tags($data['about_me']));
        }

        if (array_key_exists('looking_about', $data)) {
            $return['data']['looking_about'] = trim(strip_tags($data['looking_about']));
        }

        if (array_key_exists('living_with', $data)) {
            //$return['data']['living_with'] = trim(strip_tags($data['living_with']));

            if (is_array($data["living_with"])) {
                $living_with = array();

                foreach ($data["living_with"] as $type) {
                    $living_with[] = '[' . $type . ']';
                }

                $return["data"]["living_with"] = implode(',', $living_with);
            } else {

            }
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

        if (array_key_exists('height', $data)) {
            $return['data']['height'] = (int)$data['height'];
        }

        if (array_key_exists('height_min', $data)) {
            $return['data']['height_min'] = (int)$data['height_min'];
        }

        if (array_key_exists('height_max', $data)) {
            $return['data']['height_max'] = (int)$data['height_max'];
        }

        foreach ($this->multiselect as $multiselect) {
            if (array_key_exists($multiselect, $data)) {
                if (is_array($data[$multiselect])) {
                    $return['data'][$multiselect] = $this->arr_to_dec($data[$multiselect]);
                } else {
                    $return['data'][$multiselect] = $this->arr_to_dec([(int)$data[$multiselect]]);
                }
            }

            if (array_key_exists('looking_' . $multiselect, $data)) {
                if (is_array($data['looking_' . $multiselect])) {
                    $return['data']['looking_' . $multiselect] = $this->arr_to_dec($data['looking_' . $multiselect]);
                } else {
                    $return['data']['looking_' . $multiselect] = $this->arr_to_dec([(int)$data['looking_' . $multiselect]]);
                }
            }
        }

        if (array_key_exists('looking_for', $data)) {
            if (is_array($data['looking_for'])) {
                $return['data']['looking_for'] = $this->arr_to_dec($data['looking_for']);
            } else {
                $return['data']['looking_for'] = $this->arr_to_dec([(int)$data['looking_for']]);
            }
        }

        if (array_key_exists('looking_distance', $data)) {
            $return['data']['looking_distance'] = (int)$data['looking_distance'];
        }

        if (array_key_exists('listening_music', $data)) {
            if (is_array($data['listening_music'])) {
                $return['data']['listening_music'] = $this->arr_to_dec($data['listening_music']);
            } else {
                $return['data']['listening_music'] = $this->arr_to_dec([(int)$data['listening_music']]);
            }
        }

        if (array_key_exists('astrological_sign', $data)) {
            $return['data']['astrological_sign'] = (int)$data['astrological_sign'];
        }

        if (array_key_exists('custom_status', $data)) {
            $return['data']['custom_status'] = trim(strip_tags($data['custom_status']));
        }

        if (array_key_exists('is_subscribed', $data)) {
            $return['data']['is_subscribed'] = $data['is_subscribed'] ? 1 : 0;
        }
        /* </custom_M> */

        if (!is_null($section_gid)) {
            $this->CI->load->model('Field_editor_model');
            $params = array();
            if (!empty($section_gid)) {
                $params["where"]["section_gid"] = $section_gid;
            }
            if ($type == 'save') {
                $validate_data = $this->CI->Field_editor_model->validate_fields_for_save($params, $data);
            } else {
                $validate_data = $this->CI->Field_editor_model->validate_fields_for_select($params, $data);
            }
            $return["data"] = array_merge($return["data"],
                $validate_data["data"]);
            if (!empty($validate_data["errors"])) {
                $return["errors"] = array_merge($return["errors"],
                    $validate_data["errors"]);
            }
        }

        if (!$user_id) {
            foreach ($this->fields_register as $field) {
                if (!(isset($return["data"][$field]) && $return["data"][$field])) {
                    $return["errors"][] = l('error_empty_fields', self::MODULE_GID);
                    break;
                }
            }
        }

        return $return;
    }

    /**
     * Deletes user
     *
     * @param int $user_id
     */
    public function delete_user($user_id, $callbacks_gid = [])
    {
        $auth = $this->CI->session->userdata("auth_type");
        if ($auth !== 'admin') {
            $callbacks_gid = array('users_delete', 'media_user', 'media_gallery');
        }
        // callback
        $this->setCallbacks($user_id, $callbacks_gid);

        $this->sendEvent(self::EVENT_USER_CHANGED,
            [
            'id' => $user_id,
            'type' => self::TYPE_USER,
            'status' => self::STATUS_DELETED,
        ]);
        $this->CI->load->model("users/models/Auth_model");
        $this->CI->Auth_model->update_user_session_data($user_id);
        return;
    }

    public function set_user_output_name(&$user)
    {
        $controller = $this->CI->router->fetch_class(true);
        if (substr($controller, 0, 6) != "admin_") {
            $hide_user_names = $this->pg_module->get_module_config('users',
                'hide_user_names');
        } else {
            $hide_user_names = 0;
        }
        if ($hide_user_names && !(!empty($user['id']) && $this->session->userdata('user_id') == $user['id'])) {
            $user['fname'] = $user['sname'] = '';
        }
        if (!(empty($user['fname']) && empty($user['sname'])) && !$hide_user_names) {
            $user['output_name'] = trim($user['fname'] . ' ' . $user['sname']);
        } else {
            $user['output_name'] = isset($user["nickname"]) ? $user["nickname"] : '';
        }

        return $user['output_name'];
    }

    public function format_users($data, $safe_format = false, $lang_id = '')
    {
        $user_for_location = [];

        $this->CI->load->model(['Uploads_model', 'Properties_model']);
        $this->CI->load->model('users/models/Users_statuses_model');
        $user_types   = $this->CI->Properties_model->get_property('user_type',
            $lang_id);
        $pm_installed = $this->pg_module->is_module_installed('perfect_match');

        $please_me_ask = 'Please ask me';
        $no_preference = 'No preference';

        /* New user date */
        $date_new = time() - 7*24*60*60;

        foreach ($data as $key => $user) {
            if (!empty($user["account"])) {
                $user["account"] = (double) $user["account"];
            }

            if (!empty($user["id"])) {
                $user["postfix"]                = $user["id"];
                $user_for_location[$user["id"]] = array(
                    'country' => $user["id_country"],
                    'region'  => $user["id_region"],
                    'city'    => $user["id_city"],
                );
            }

            $cur_time = time();
            $user['is_activated'] = 0;
            if (!empty($user['activated_end_date'])) {
                $user['unix_activated_end_date'] = intval(strtotime($user['activated_end_date']));
                if ($user['unix_activated_end_date'] > $cur_time) {
                    $user['is_activated'] = 1;
                }
            }
            $user['is_featured'] = 0;
            if (!empty($user['featured_end_date'])) {
                $user['unix_featured_end_date'] = intval(strtotime($user['featured_end_date']));
                if ($user['unix_featured_end_date'] > $cur_time) {
                    $user['is_featured'] = 1;
                }
            }
            $user['is_hide_on_site'] = 0;
            if (!empty($user['hide_on_site_end_date'])) {
                $user['unix_hide_on_site_end_date'] = intval(strtotime($user['hide_on_site_end_date']));
                if ($user['unix_hide_on_site_end_date'] > $cur_time) {
                    $user['is_hide_on_site'] = 1;
                }
            }
            $user['is_highlight_in_search'] = 0;
            if (!empty($user['highlight_in_search_end_date'])) {
                $user['unix_highlight_in_search_end_date'] = intval(strtotime($user['highlight_in_search_end_date']));
                if ($user['unix_highlight_in_search_end_date'] > $cur_time) {
                    $user['is_highlight_in_search'] = 1;
                }
            }
            $user['is_up_in_search'] = 0;
            if (!empty($user['up_in_search_end_date'])) {
                $user['unix_up_in_search_end_date'] = intval(strtotime($user['up_in_search_end_date']));
                if ($user['unix_up_in_search_end_date'] > $cur_time) {
                    $user['is_up_in_search'] = 1;
                }
            }

            if (!empty($user['user_type'])) {
                $user['user_type_str'] = !empty($user_types['option'][$user['user_type']]) ? $user_types['option'][$user['user_type']] : '';
            }

            if (isset($user['looking_user_type'])) {
                if (!empty($user['looking_user_type'])) {
                    $user['looking_user_type_str'] = !empty($user_types['option'][$user['looking_user_type']]) ? $user_types['option'][$user['looking_user_type']] : '';
                } else {
                    if ($pm_installed) {
                        $user['looking_user_type_str'] = !empty($user_types['option'][$user['looking_user_type']]) ? $user_types['option'][$user['looking_user_type']] : '';
                    }
                }
            }

            $this->set_user_output_name($user);

            $user['is_new'] = isset($user['date_created']) && strtotime($user['date_created']) > $date_new;

            if ($pm_installed) {
                $age_min = $this->pg_module->get_module_config('users',
                    'age_min');
                $age_max = $this->pg_module->get_module_config('users',
                    'age_max');

                if (isset($user['age_min']) && ($user['age_min'] < $age_min || $user['age_min'] > $age_max)) {
                    $user['age_min'] = $age_min;
                }
                if (isset($user['age_max'])) {
                    if ($user['age_max'] < $age_min || $user['age_max'] > $age_max) {
                        $user['age_max'] = $age_max;
                    }
                    if (!empty($user['age_min']) && $user['age_min'] > $user['age_max']) {
                        $user['age_min'] = $age_min;
                    }
                }
            }

            if (isset($user['roles'])) {
                $user['roles'] = $this->rolesDecode($user['roles']);
            }

            if (!empty($user["user_logo"])) {
                $user["media"]["user_logo"] = $this->CI->Uploads_model->format_upload($this->upload_config_id, $user["postfix"], $user["user_logo"]);
            } else {
                $user["media"]["user_logo"] = $this->CI->Uploads_model->format_default_upload($this->upload_config_id);
            }
            if (!empty($user["user_logo_moderation"])) {
                $user["media"]["user_logo_moderation"] = $this->CI->Uploads_model->format_upload($this->upload_config_id, $user["postfix"], $user["user_logo_moderation"]);
            } else {
                $user["media"]["user_logo_moderation"] = $this->CI->Uploads_model->format_default_upload($this->upload_config_id);
            }

            if (isset($user['online_status']) && isset($user['site_status'])) {
                $user['statuses'] = $this->CI->Users_statuses_model->format_status($user['online_status'], $user['site_status']);
            }

            if (!empty($user["birth_date"])) {
                // TODO: birth_date_raw used in birthdays
                $user["birth_date_raw"] = $user["birth_date"];
                $page_data['date_format']  = $this->pg_date->get_format('date_literal', 'st');
                $user['birth_date_tstamp'] = strtotime($user["birth_date"]);
                $user["birth_date_hidden"] = strftime(self::HIDDEN_DATE_FORMAT, strtotime($user["birth_date"]));
            } else {
                $user["birth_date_raw"] = "0000-00-00 00:00:00";
            }

            if (isset($user['rating_sorter'])) {
                $user['rating_sorter'] = round($user['rating_sorter'], 1);
            }
            if (isset($user['rating_value'])) {
                $user['rating_value'] = round($user['rating_value'], 1);
            }

            /* <custom_M> */
            $user['headline_str'] = nl2br($user['headline']);
            $user['about_me_str'] = nl2br($user['about_me']);
            $user['looking_about_str'] = nl2br($user['looking_about']);

            if (!empty($user['age_min']) && !empty($user['age_max'])) {
                $user['age_min_age_max_str'] = 'From ' . $user['age_min'] . ' to ' . $user['age_max'];
            } elseif (!empty($user['age_min'])) {
                $user['age_min_age_max_str'] = 'From ' . $user['age_min'];
            } elseif (!empty($user['age_max'])) {
                $user['age_min_age_max_str'] = 'To ' . $user['age_max'];
            } else {
                $user['age_min_age_max_str'] = $no_preference;
            }

            if (!empty($user['height'])) {
                $user['height_str'] = ld_option('height', 'users', $user['height']);
            } else {
                $user['height_str'] = $please_me_ask;
            }

            if (!empty($user['height_min']) && !empty($user['height_max'])) {
                $user['height_min_height_max_str'] = 'From ' . ld_option('height', 'users', $user['height_min']) . ' to ' . ld_option('height', 'users', $user['height_max']);
            } elseif (!empty($user['height_min'])) {
                $user['height_min_height_max_str'] = 'From ' . ld_option('height', 'users', $user['height_min']);
            } elseif (!empty($user['height_max'])) {
                $user['height_min_height_max_str'] = 'To ' . ld_option('height', 'users', $user['height_max']);
            } else {
                $user['height_min_height_max_str'] = $no_preference;
            }

            if (!empty($user['living_with'])) {
                //$user['living_with_str'] = ld_option('living_with', 'users', $user['living_with']);

                $living_with_types = $this->CI->Properties_model->get_property('living_with', $lang_id);

                $living_with_str = preg_replace('/\[|\]/i', '', $user['living_with']);
                $living_with_array = explode(',', $living_with_str);

                $living_with_array_formatted = array();
                foreach ($living_with_array as $living_with) {
                    $living_with_array_formatted[] = $living_with_types['option'][$living_with];
                }

                $user['living_with_str'] = implode(',', $living_with_array_formatted);
                $user['living_with'] = $living_with_array;
            } else {
                $user['living_with_str'] = $please_me_ask;
                $user['living_with'] = [];
            }

            if (!empty($user['looking_living_with'])) {
                //$user['looking_living_with_str'] = ld_option('looking_living_with', 'users', $user['looking_living_with']);

                $looking_living_with_types = $this->CI->Properties_model->get_property('living_with', $lang_id);

                $looking_living_with_str = preg_replace('/\[|\]/i', '', $user['looking_living_with']);
                $looking_living_with_array = explode(',', $looking_living_with_str);

                $looking_living_with_array_formatted = array();
                foreach ($looking_living_with_array as $looking_living_with) {
                    $looking_living_with_array_formatted[] = $looking_living_with_types['option'][$looking_living_with];
                }

                $user['looking_living_with_str'] = implode(',', $looking_living_with_array_formatted);
                $user['looking_living_with'] = $looking_living_with_array;
            } else {
                $user['looking_living_with_str'] = $no_preference;
                $user['looking_living_with'] = [];
            }

            foreach ($this->multiselect as $multiselect) {
                if (isset($user[$multiselect])) {
                    $user[$multiselect . '_arr'] = $this->dec_to_arr($user[$multiselect]);
                    $user[$multiselect . '_str'] = '';
                    foreach ($user[$multiselect . '_arr'] as $value) {
                        $user[$multiselect . '_str'] .= ', ' . ld_option($multiselect, 'users', $value);
                    }
                    $user[$multiselect . '_str'] = trim($user[$multiselect . '_str'], ' ,');
                }

                if (empty($user[$multiselect . '_str'])) {
                    $user[$multiselect . '_str'] = $please_me_ask;
                }

                if (isset($user['looking_' . $multiselect])) {
                    $user['looking_' . $multiselect . '_arr'] = $this->dec_to_arr($user['looking_' . $multiselect]);
                    $user['looking_' . $multiselect . '_str'] = '';
                    foreach ($user['looking_' . $multiselect . '_arr'] as $looking_value) {
                        $user['looking_' . $multiselect . '_str'] .= ', ' . ld_option($multiselect, 'users', $looking_value);
                    }
                    $user['looking_' . $multiselect . '_str'] = trim($user['looking_' . $multiselect . '_str'], ' ,');
                }

                if (empty($user['looking_' . $multiselect . '_str'])) {
                    $user['looking_' . $multiselect . '_str'] = $no_preference;
                }
            }

            if (isset($user['looking_for'])) {
                $looking_for_values = $this->dec_to_arr($user['looking_for']);
                $user['looking_for_str'] = '';
                foreach ($looking_for_values as $looking_for_value) {
                    $user['looking_for_str'] .= ', ' . ld_option('looking_for', 'users', $looking_for_value);
                }
                $user['looking_for_str'] = trim($user['looking_for_str'], ' ,');
            }

            if (empty($user['looking_for_str'])) {
                $user['looking_for_str'] = $please_me_ask;
            }

            if (!empty($user['looking_distance'])) {
                $user['looking_distance_str'] = ld_option('distance', 'users', $user['looking_distance']);
            } else {
                $user['looking_distance_str'] = 'Ask distance';
            }

            if (isset($user['listening_music'])) {
                $listening_music_values = $this->dec_to_arr($user['listening_music']);
                $user['listening_music_str'] = '';
                foreach ($listening_music_values as $listening_music_value) {
                    $user['listening_music_str'] .= ', ' . ld_option('listening_music', 'users', $listening_music_value);
                }
                $user['listening_music_str'] = trim($user['listening_music_str'], ' ,');
            }

            if (empty($user['listening_music_str'])) {
                $user['listening_music_str'] = $please_me_ask;
            }


            if (!empty($user['astrological_sign'])) {
                $user['astrological_sign_str'] = ld_option('astrological_sign', 'users', $user['astrological_sign']);
            } else {
                $user['astrological_sign_str'] = $please_me_ask;
            }

            if (!empty($user['is_subscribed'])) {
                $user['is_subscribed_str'] = l('option_checkbox_yes', 'start');
            } else {
                $user['is_subscribed_str'] = l('option_checkbox_no', 'start');
            }
            /* </custom_M> */

            $data[$key] = $user;
        }

        if (!empty($user_for_location)) {
            $this->CI->load->helper('countries');
            $user_locations = cities_output_format($user_for_location,
                $lang_id);
            $users_locations_data = get_location_data($user_for_location, 'city');
            foreach ($data as $key => $user) {
                $data[$key]['location']    = (isset($user_locations[$user["id"]])) ? $user_locations[$user["id"]] : '';
                $data[$key]['country']     = (isset($users_locations_data['country'][$user['id_country']])) ? $users_locations_data['country'][$user['id_country']]['name'] : '';
                $data[$key]['region']      = (isset($users_locations_data['region'][$user['id_region']])) ? $users_locations_data['region'][$user['id_region']]['name'] : '';
                $data[$key]['region-code'] = (isset($users_locations_data['region'][$user['id_region']])) ? $users_locations_data['region'][$user['id_region']]['code'] : '';
                $data[$key]['city']        = (isset($users_locations_data['city'][$user['id_city']])) ? $users_locations_data['city'][$user['id_city']]['name'] : '';
            }
        }

        if ($safe_format) {
            foreach ($data as $key => $user) {
                $data[$key] = array_intersect_key($data[$key],
                    array_flip($this->safe_fields));
            }
        }

        $this->CI->load->helper('date_format');
        $date_formats = $this->pg_date->get_format('date_literal', 'st');

        $this->CI->load->helper('seo');

        if (SOCIAL_MODE && $this->CI->pg_module->is_module_installed('wall_events')) {
            $section_code = 'wall';
            $section_name = l('filter_section_wall', 'users');
        } else {
            $section_code = 'profile';
            $section_name = l('filter_section_profile', 'users');
        }

        // seo data
        foreach ($data as $key => $user) {
            if (isset($user['user_type'])) {
                $user['type-code'] = $user['user_type'];
            }
            if (isset($user['user_type_str'])) {
                $user['type-name'] = $user['user_type_str'];
            }
            if (isset($user['looking_user_type']) && $pm_installed) {
                $user['looking-code'] = $user['looking_user_type'];
            }
            if (isset($user['looking_user_type_str'])) {
                $user['looking-name'] = $user['looking_user_type_str'];
            }
            if (isset($user['output_name'])) {
                $user['name'] = $user['output_name'];
            }
            if (isset($user['fname'])) {
                $user['first-name'] = $user['fname'];
            }
            if (isset($user['sname'])) {
                $user['second-name'] = $user['sname'];
            }
            if (isset($user['date_created'])) {
                $user['registered-date'] = tpl_date_format($user['date_created'],
                    $date_formats);
            }
            if (isset($user['birth_date'])) {
                $user['birth-date'] = tpl_date_format($user['birth_date'],
                    $date_formats);
            }
            if (isset($user['online_status'])) {
                $user['online-status-code'] = $user['online_status'] ? 'online' : 'offline';
            }
            if (isset($user['online_status'])) {
                $user['online-status-name'] = l('status_online_' . $user['online_status'],
                    'users');
            }
            $user['section-code'] = $section_code;
            $user['section-name'] = $section_name;
            $user['link'] = rewrite_link(self::MODULE_GID, 'view', $user);
            $data[$key] = $user;
        }

        return $data;
    }

    public function format_default_user($id = null, $lang_id = '', $module = '')
    {
        $this->CI->load->model('Uploads_model');
        $auth = $this->CI->session->userdata("auth_type");

        if (($auth === 'admin' || $module === 'mailbox') && $id != 0) {
            $this->CI->load->model('users/models/Users_deleted_model');
            $data = $this->CI->Users_deleted_model->get_user_by_user_id($id,
                true);
            if (empty($data)) {
                $data['output_name'] = '';
            }
        } elseif ($auth == 'admin' && $id == 0) {
            $data["nickname"]    = $data["output_name"] = 'Administrator';
        } else {
            $data["postfix"]            = $id ? $id : '';
            $data['link']               = site_url() . "users/untitled";
            $data["output_name"]        = $id ? l('user_deleted', 'users',
                    $lang_id) : l('guest', 'users', $lang_id);
            $data["media"]["user_logo"] = $this->CI->Uploads_model->format_default_upload($this->upload_config_id);
        }

        if ($this->CI->pg_module->is_module_installed('wall_events')) {
            $section_code = 'wall';
            $section_name = l('filter_section_wall', 'users');
        } else {
            $section_code = 'profile';
            $section_name = l('filter_section_profile', 'users');
        }

        // seo data
        $data['type-code']          = 'unknown';
        $data['type-name']          = 'unknown';
        $data['looking-code']       = 'unknown';
        $data['looking-name']       = 'unknown';
        $data['name']               = $data['output_name'];
        $data['first-name']         = 'unknown';
        $data['second-name']        = 'unknown';
        $data['registered-date']    = 'unknown';
        $data['birth-date']         = 'unknown';
        $data['online-status-code'] = 'offline';
        $data['online-status-name'] = l('status_online_0', 'users');
        $data['section-code']       = $section_code;
        $data['section-name']       = $section_name;

        return $data;
    }

    public function format_user($data, $safe_format = false, $lang_id = '')
    {
        if ($data) {
            $return = $this->format_users(array(0 => $data), $safe_format,
                $lang_id);

            return $return[0];
        }

        return array();
    }

    // seo
    public function get_seo_settings($method = '', $lang_id = '')
    {
        if (!empty($method)) {
            return $this->getSeoSettings($method, $lang_id);
        } else {
            $actions = array('account', 'account_delete', 'settings',
                'login_form',
                'restore', 'profile', 'view', 'registration', 'confirm',
                'search',
                'perfect_match', 'my_visits', 'my_guests');
            $return  = array();
            foreach ($actions as $action) {
                $return[$action] = $this->getSeoSettings($action, $lang_id);
            }

            return $return;
        }
    }

    private function getSeoSettings($method, $lang_id = '')
    {
        if ($method == "account") {
            return array(
                "templates"   => array('nickname', 'fname', 'sname', 'output_name'),
                "url_vars"    => array(),
                'url_postfix' => array(
                    'action' => array('action' => 'literal'),
                    'page'   => array('page'   => 'numeric'),
                ),
                'optional' => array(),
            );
        } elseif ($method == "account_delete") {
            return array(
                "templates"   => array(),
                "url_vars"    => array(),
                'url_postfix' => array(),
                'optional'    => array(),
            );
        } elseif ($method == "settings") {
            return array(
                "templates"   => array(),
                "url_vars"    => array(),
                'url_postfix' => array(),
                'optional'    => array(),
            );
        } elseif ($method == "login_form") {
            return array(
                "templates"   => array(),
                "url_vars"    => array(),
                'url_postfix' => array(),
                'optional'    => array(),
            );
        } elseif ($method == "restore") {
            return array(
                "templates"   => array(),
                "url_vars"    => array(),
                'url_postfix' => array(),
                'optional'    => array(),
            );
        } elseif ($method == "profile") {
            return array(
                "templates" => array('nickname', 'first-name', 'second-name',
                    'type-code',
                    'type-name', 'looking-code', 'looking-name', 'name',
                    'location',
                    'country', 'region', 'region-code', 'city', 'age',
                    'birth-date',
                    'registered-date', 'online-status-code', 'online-status-name',
                    'section-code', 'section-name', 'fname', 'sname',
                    'user_type',
                    'output_name'),
                "url_vars"    => array(),
                "url_postfix" => array(
                    'section-code' => array('section-code' => 'literal',
                        'section-name' => 'literal'),
                    'subsection-code' => array('subsection-code' => 'literal',
                        'subsection-name' => 'literal'),
                ),
                'optional' => array(),
            );
        } elseif ($method == "view") {
            return array(
                "templates" => array('nickname', 'first-name', 'second-name',
                    'type-code',
                    'type-name', 'looking-code', 'looking-name', 'name',
                    'location',
                    'country', 'region', 'region-code', 'city', 'age',
                    'birth-date',
                    'registered-date', 'online-status-code', 'online-status-name',
                    'section-code', 'section-name', 'fname', 'sname',
                    'user_type',
                    'output_name'),
                "url_vars"  => array(
                    "id" => array("id" => 'literal', "nickname" => 'literal'),
                ),
                "url_postfix" => array(
                    'section-code' => array('section-code' => 'literal',
                        'section-name' => 'literal'),
                    'subsection-code' => array('subsection-code' => 'literal',
                        'subsection-name' => 'literal'),
                ),
                "optional" => array(
                    array(
                        "type-code"          => "literal",
                        "type-name"          => "literal",
                        "looking-code"       => "literal",
                        "looking-name"       => "literal",
                        "name"               => "literal",
                        "first-name"         => "literal",
                        "second-name"        => "literal",
                        "location"           => "literal",
                        "country"            => "literal",
                        "region"             => "literal",
                        "region-code"        => "literal",
                        "city"               => "literal",
                        "age"                => "literal",
                        "birth-date"         => "literal",
                        "registered-date"    => "literal",
                        "online-status-code" => "literal",
                        "online-status-name" => "literal",
                    ),
                ),
            );
        } elseif ($method == "registration") {
            return array(
                "templates"   => array(),
                "url_vars"    => array(),
                'url_postfix' => array(),
                'optional'    => array(),
            );
        } elseif ($method == "confirm") {
            return array(
                "templates"   => array(),
                "url_vars"    => array(),
                'url_postfix' => array(
                    'code' => array('code' => 'literal'),
                ),
                'optional' => array(),
            );
        } elseif ($method == "search") {
            return array(
                "templates"   => array(),
                "url_vars"    => array(),
                'url_postfix' => array(
                    'order'           => array('order'           => 'literal'),
                    'order_direction' => array('order_direction' => 'literal'),
                    'page'            => array('page'            => 'numeric'),
                ),
                'optional' => array(),
            );
        } elseif ($method == "my_visits") {
            return array(
                "templates"   => array(),
                "url_vars"    => array(),
                'url_postfix' => array(
                    'period' => array('period' => 'literal'),
                    'page'   => array('page'   => 'numeric'),
                ),
                'optional' => array(),
            );
        } elseif ($method == "my_guests") {
            return array(
                "templates"   => array(),
                "url_vars"    => array(),
                'url_postfix' => array(
                    'period' => array('period' => 'literal'),
                    'page'   => array('page'   => 'numeric'),
                ),
                'optional' => array(),
            );
        }
    }

    public function request_seo_rewrite($var_name_from, $var_name_to, $value)
    {
        $user_data = array();

        if ($var_name_from == $var_name_to) {
            return $value;
        }

        if ($var_name_from == "nickname" && $var_name_to == "id") {
            $user_data = $this->get_user_by_login($value);

            return $user_data["id"];
        }

        if ($var_name_from == 'section-name' && $var_name_to == "section-code") {
            switch ($this->CI->uri->rsegments[2]) {
                case 'profile':
                    $sections = array('view', 'gallery', 'personal',
                        'subscriptions');

                    if ($this->CI->pg_module->is_module_installed('wall_events')) {
                        array_unshift($sections, 'wall');
                    }

                    $search_value = false;

                    $langs = $this->CI->pg_language->languages;
                    foreach ($sections as $section) {
                        foreach ($langs as $lid => $lang_data) {
                            if ($value == l('filter_section_' . $section,
                                    'users', $lid)) {
                                $search_value = $section;
                                break;
                            }
                        }
                        if ($search_value) {
                            break;
                        }
                    }

                    if (!$search_value) {
                        $this->CI->load->model('Field_editor_model');
                        $this->CI->Field_editor_model->initialize($this->form_editor_type);
                        $sections = $this->CI->Field_editor_model->get_section_list(array(),
                            array(), array_keys($langs));
                        foreach ($sections as $section) {
                            foreach ($langs as $lid => $lang_data) {
                                if ($value == $section['name_' . $lid]) {
                                    $search_value = $section['gid'];
                                    break;
                                }
                            }
                            if ($search_value) {
                                break;
                            }
                        }
                    }

                    if (!$search_value) {
                        $search_value = current($sections);
                    }

                    $value    = $search_value;
                    break;
                case 'view':
                    $sections = array('profile', 'gallery');

                    if ($this->CI->pg_module->is_module_installed('wall_events')) {
                        $sections[] = 'wall';
                    }

                    $search_value = false;

                    $langs = $this->CI->pg_language->languages;
                    foreach ($sections as $section) {
                        foreach ($langs as $lid => $lang_data) {
                            if ($value == l('filter_section_' . $section,
                                    'users', $lid)) {
                                $search_value = $section;
                                break;
                            }
                        }
                        if ($search_value) {
                            break;
                        }
                    }

                    if (!$search_value) {
                        $search_value = current($sections);
                    }

                    $value = $search_value;
                    break;
            }

            return $value;
        }

        show_404();
    }

    public function get_sitemap_xml_urls()
    {
        $this->CI->load->helper('seo');

        $lang_canonical = true;

        if ($this->CI->pg_module->is_module_installed('seo')) {
            $lang_canonical = $this->CI->pg_module->get_module_config('seo',
                'lang_canonical');
        }

        if ($lang_canonical) {
            $default_lang_id         = $this->CI->pg_language->get_default_lang_id();
            $default_lang_code       = $this->CI->pg_language->get_lang_code_by_id($default_lang_id);
            $langs[$default_lang_id] = $default_lang_code;
        } else {
            foreach ($this->CI->pg_language->languages as $lang_id => $lang_data) {
                $langs[$lang_id] = $lang_data['code'];
            }
        }

        $return = array();

        $user_settings = $this->pg_seo->get_settings('user', 'users',
            'login_form');
        if (!$user_settings['noindex']) {
            $return[] = array(
                "url" => rewrite_link('users', 'login_form', array(), false,
                    null, $lang_canonical),
                "priority" => 0.4,
            );
        }

        $user_settings = $this->pg_seo->get_settings('user', 'users',
            'login_form');
        if (!$user_settings['noindex']) {
            $return[] = array(
                "url" => rewrite_link('users', 'registration', array(), false,
                    null, $lang_canonical),
                "priority" => 0.4,
            );
        }

        $user_settings = $this->pg_seo->get_settings('user', 'users',
            'login_form');
        if (!$user_settings['noindex']) {
            $return[] = array(
                "url" => rewrite_link('users', 'search', array(), false, null,
                    $lang_canonical),
                "priority" => 0.5,
            );
        }

        $user_settings = $this->pg_seo->get_settings('user', 'users', 'view');
        if (!$user_settings['noindex']) {
            $criteria = array(
                "where" => array(
                    "approved"                => '1',
                    "confirm"                 => '1',
                    "activity"                => '1',
                    "hide_on_site_end_date <" => date(self::DB_DATE_FORMAT_SEARCH),
                ),
            );

            $order_array = array(
                "up_in_search_end_date" => 'DESC',
                "id"                    => 'DESC',
            );

            $users = $this->get_users_list(1, 1000, $order_array, $criteria);
            foreach ($users as $user) {
                $return[] = array(
                    'url' => rewrite_link('users', 'view', $user, false, null,
                        $lang_canonical),
                    'priority' => 0.6,
                );
            }
        }

        return $return;
    }

    public function get_sitemap_urls()
    {
        $this->CI->load->helper('seo');
        $auth  = $this->CI->session->userdata("auth_type");
        $block = array();

        $users_block = array(
            "name" => l('header_profile', 'users'),
            "link" => rewrite_link('users', 'profile',
                array('section-code' => 'view', 'section-name' => l('filter_section_view',
                    'users'))),
            "clickable" => ($auth == "user"),
            "items" => array(
                array(
                    "name" => l('header_login', 'users'),
                    "link" => rewrite_link('users', 'login_form'),
                    "clickable" => !($auth == "user"),
                ),
                array(
                    "name" => l('link_edit_account', 'users'),
                    "link" => rewrite_link('users', 'account'),
                    "clickable" => ($auth == "user"),
                ),
                array(
                    "name" => l('link_edit_profile', 'users'),
                    "link" => rewrite_link('users', 'profile'),
                    "clickable" => ($auth == "user"),
                ),
                array(
                    "name" => l('header_account_settings', 'users'),
                    "link" => rewrite_link('users', 'settings'),
                    "clickable" => ($auth == "user"),
                ),
                array(
                    "name" => l('header_my_visits', 'users'),
                    "link" => rewrite_link('users', 'my_visits'),
                    "clickable" => ($auth == "user"),
                ),
                array(
                    "name" => l('header_my_guests', 'users'),
                    "link" => rewrite_link('users', 'my_guests'),
                    "clickable" => ($auth == "user"),
                ),
                array(
                    "name" => l('header_find_people', 'users'),
                    "link" => rewrite_link('users', 'search'),
                    "clickable" => ($auth == "user"),
                ),
            ),
        );

        if ($this->CI->pg_module->is_module_installed('banners')) {
            $users_block['items'][] = array(
                "name" => l('header_my_banners', 'banners'),
                "link" => site_url() . 'users/account/banners',
                "clickable" => ($auth == "user"),
            );
        }
        $users_block['items'][] = array(
            "name" => l('link_logout', 'users'),
            "link" => site_url() . "users/logout",
            "clickable" => ($auth == "user"),
        );

        $block[] = $users_block;

        return $block;
    }

    // banners callback method
    public function _banner_available_pages()
    {
        $return[] = array("link" => "users/profile", "name" => l('header_profile',
                'users'));
        $return[] = array("link" => "users/login_form", "name" => l('header_login',
                'users'));
        $return[] = array("link" => "users/registration", "name" => l('header_register',
                'users'));
        $return[] = array("link" => "users/account", "name" => l('link_edit_account',
                'users'));
        $return[] = array("link" => "users/search", "name" => l('header_find_people',
                'users'));
        $return[] = array("link" => "users/ajax_search", "name" => l('header_find_people',
                'users') . '(ajax)');
        $return[] = array("link" => "users/view", "name" => l('header_view_profile',
                'users'));

        return $return;
    }

    public function _dynamic_block_last_registered($params, $view = '', $width = 100)
    {
        $count = $params["count"];
        if ($params["with_logo"] == 'yes') {
            $attrs["where"]["user_logo !="] = '';
        } else {
            $attrs = array();
        }
        $users = $this->get_active_users_list(1, $count,
            array("date_created" => "DESC"), $attrs);
        $this->CI->view->assign('users', $users);

        return $this->CI->view->fetch('helper_last_registered', 'user', 'users');
    }

    // moderation functions
    public function _moder_get_list($object_ids)
    {
        $params["where_in"]["id"] = $object_ids;

        $users = $this->get_users_list(null, null, null, $params);

        if (!empty($users)) {
            foreach ($users as $user) {
                $return[$user["id"]] = $user;
            }

            return $return;
        } else {
            return [];
        }
    }

    public function _moder_set_status($object_id, $status)
    {
        $user = $this->get_user_by_id($object_id);
        $backup_user = [];
        switch ($status) {
            case 0:
                if ($user['user_logo_moderation']) {
                    $backup_user['user_logo_moderation'] = '';
                } else {
                    $backup_user['user_logo'] = '';
                }
                break;
            case 1:
                if ($user['user_logo_moderation']) {
                    $backup_user['user_logo'] = $user['user_logo_moderation'];
                    $backup_user['user_logo_moderation'] = '';
                }
                break;
        }
        $this->save_user($object_id, $backup_user);
    }

    public function add_contact($id_user, $id_contact)
    {
        $this->CI->load->model('Linker_model');
        $this->CI->Linker_model->add_link('users_contacts', $id_user,
            $id_contact);
        $this->CI->Linker_model->add_link('users_contacts', $id_contact,
            $id_user);

        return;
    }

    public function delete_contact($id_user, $id_contact)
    {
        $this->CI->load->model('Linker_model');
        $params["where_in"]['id_link_2'] = $params["where_in"]['id_link_1'] = array(
            $id_user, $id_contact);
        $this->CI->Linker_model->delete_links('users_contacts', $params);

        return;
    }

    public function delete_user_contacts($id_user)
    {
        $this->CI->load->model('Linker_model');
        $params["where"]['id_link_2'] = $params["where"]['id_link_1'] = $id_user;
        $this->CI->Linker_model->delete_links('users_contacts', $params);

        return;
    }

    public function get_fulltext_data($id, $fields)
    {
        $return                            = array('main_fields' => array(),
            'fe_fields' => array(),
            'default_lang_id' => $this->CI->pg_language->get_default_lang_id(),
            'object_lang_id' => 1);
        $this->set_additional_fields($fields);
        $data                              = $this->get_user_by_id($id);
        $hide_user_names                   = $this->pg_module->get_module_config('users',
            'hide_user_names');
        $return['object_lang_id']          = $data["lang_id"];
        $return['main_fields']             = array(
            'fname'       => $hide_user_names ? '' : $data['fname'],
            'sname'       => $hide_user_names ? '' : $data['sname'],
            'nickname'    => $data['nickname'],
            'phone'       => $data['phone'],
            'address'     => $data['address'],
            'postal_code' => $data['postal_code'],
            'birth_date'  => $data['birth_date'],
        );
        $user_for_location[$data["id"]]    = array($data["id_country"], $data["id_region"],
            $data["id_city"]);
        $this->CI->load->helper('countries');
        $user_locations                    = cities_output_format($user_for_location);
        $return['main_fields']["location"] = (isset($user_locations[$data["id"]])) ? $user_locations[$data["id"]] : '';

        foreach ($fields as $field) {
            $return['fe_fields'][$field] = $data[$field];
        }

        return $return;
    }

    public function update_age($filter_object_ids = array())
    {
        if (is_array($filter_object_ids) && count($filter_object_ids)) {
            $this->DB->where_in('id', $filter_object_ids);
        }
        $now_date = date('Y-m-d');
        $this->DB->set('age', "FLOOR(DATEDIFF('$now_date', birth_date) / 365)",
                false)
            ->update(USERS_TABLE);

        return $this->DB->affected_rows();
    }

    public function update_profile_completion($filter_object_ids = array())
    {
        $filter_object_ids = (array) $filter_object_ids;
        $table_fields      = $this->DB->list_fields(USERS_TABLE);
        $this->CI->load->model('Field_editor_model');
        $this->CI->Field_editor_model->initialize($this->form_editor_type);
        $fe_fields         = $this->CI->Field_editor_model->get_fields_list();

        $fields_completion = $this->fields_completion;
        if ($this->CI->pg_module->is_module_installed('perfect_match')) {
            $fields_completion[] = 'looking_user_type';
            $fields_completion[] = 'age_min';
            $fields_completion[] = 'age_max';
        }
        foreach ($fe_fields as $fe_field) {
            if (in_array($fe_field['field_name'], $table_fields)) {
                $fields_completion[] = $fe_field['field_name'];
            }
        }

        $fields_count = count($fields_completion);
        $fields_sql   = array();
        foreach ($fields_completion as $field) {
            $fields_sql[] = "IF(ISNULL({$field}), 0 , ({$field}>''))";
        }

        if (count($filter_object_ids)) {
            $this->DB->where_in("id", $filter_object_ids);
        }
        if ($fields_sql) {
            $this->DB->set('profile_completion',
                "ROUND((" . implode('+', $fields_sql) . ")/{$fields_count}*100)",
                false)->update(USERS_TABLE);
        }
    }

    public function service_set_user_activate_in_search($id_user, $period = null)
    {
        $user = $this->get_user_by_id($id_user);
        if (strtotime($user["activated_end_date"]) > time()) {
            $uts = strtotime($user["activated_end_date"]);
        } else {
            $uts = time();
        }
        if (!is_null($period)) {
            $data['activated_end_date'] = date(self::DB_DATE_FORMAT,
                $uts + $period * 60 * 60 * 24);
        } else {
            $data['activated_end_date'] = self::DB_DEFAULT_DATE;
        }
        $data['activity'] = '1';
        $this->save_user($id_user, $data);

        return $data['activated_end_date'];
    }

    public function service_set_users_featured($id_user, $period = '')
    {
        $user = $this->get_user_by_id($id_user);
        if (strtotime($user["featured_end_date"]) > time()) {
            $uts = strtotime($user["featured_end_date"]);
        } else {
            $uts = time();
        }
        if (!is_null($period)) {
            $data['featured_end_date'] = date(self::DB_DATE_FORMAT,
                $uts + $period * 60 * 60 * 24);
        } else {
            $data['featured_end_date'] = self::DB_DEFAULT_DATE;
        }
        $this->save_user($id_user, $data);

        return $data['featured_end_date'];
    }

    public function is_user_featured($user_id)
    {
        $this->DB->select('featured_end_date')
            ->from(USERS_TABLE)
            ->where('id', $user_id)
            ->where('featured_end_date >', date(self::DB_DATE_FORMAT_SEARCH))
            ->where('UNIX_TIMESTAMP(featured_end_date) >', '86400');
        $results = $this->DB->get()->result_array();
        $return  = array('is_featured' => 0);
        if (isset($results[0]['featured_end_date'])) {
            $return['is_featured']       = 1;
            $return['featured_end_date'] = $results[0]['featured_end_date'];
        }

        return $return;
    }

    public function is_user_activated($user_id)
    {
        $this->DB->select('activated_end_date, (UNIX_TIMESTAMP(activated_end_date) - UNIX_TIMESTAMP())/86400 AS period')
            ->from(USERS_TABLE)
            ->where('id', $user_id)
            ->where('activated_end_date >', date(self::DB_DATE_FORMAT_SEARCH))
            ->where('UNIX_TIMESTAMP(activated_end_date) >', '86400');
        $results = $this->DB->get()->row_array();
        $return  = array('is_activity' => 0, 'period' => null);
        if (isset($results['activated_end_date'])) {
            $return['is_activity']        = 1;
            $return['activated_end_date'] = $results['activated_end_date'];
            $return['period']             = $results['period'];
        }

        return $return;
    }

    public function service_set_admin_approve($user_id)
    {
        $data['approved'] = '1';
        $this->save_user($user_id, $data);

        return;
    }

    public function service_set_hide_on_site($id_user, $period = '')
    {
        $user = $this->get_user_by_id($id_user);
        if (strtotime($user["hide_on_site_end_date"]) > 86400) {
            $uts = strtotime($user["hide_on_site_end_date"]);
        } else {
            $uts = time();
        }
        if ($period) {
            $data['hide_on_site_end_date'] = date(self::DB_DATE_FORMAT,
                $uts + $period * 60 * 60 * 24);
        } else {
            $data['hide_on_site_end_date'] = self::DB_DEFAULT_DATE;
        }
        $this->save_user($id_user, $data);

        return $data['hide_on_site_end_date'];
    }

    public function service_set_highlight_in_search($id_user, $period = '')
    {
        $user = $this->get_user_by_id($id_user);
        if (strtotime($user["highlight_in_search_end_date"]) > time()) {
            $uts = strtotime($user["highlight_in_search_end_date"]);
        } else {
            $uts = time();
        }
        if ($period) {
            $data['highlight_in_search_end_date'] = date(self::DB_DATE_FORMAT,
                $uts + $period * 60 * 60 * 24);
        } else {
            $data['highlight_in_search_end_date'] = self::DB_DEFAULT_DATE;
        }
        $this->save_user($id_user, $data);

        return $data['highlight_in_search_end_date'];
    }

    public function service_set_up_in_search($id_user, $period = '')
    {
        $user = $this->get_user_by_id($id_user);
        if (strtotime($user["up_in_search_end_date"]) > 86400) {
            $uts = strtotime($user["up_in_search_end_date"]);
        } else {
            $uts = time();
        }
        if ($period) {
            $data['up_in_search_end_date'] = date(self::DB_DATE_FORMAT,
                $uts + $period * 60 * 60 * 24);
        } else {
            $data['up_in_search_end_date'] = self::DB_DEFAULT_DATE;
        }
        $this->save_user($id_user, $data);

        return $data['up_in_search_end_date'];
    }

    public function service_cron_user_activate_in_search()
    {
        $this->DB->select('COUNT(*) AS cnt')->from(USERS_TABLE)
            ->where('activated_end_date <', date(self::DB_DATE_FORMAT_SEARCH))
            ->where('UNIX_TIMESTAMP(activated_end_date) >', '86400');
        $results = $this->DB->get()->result_array();
        $clean   = 0;
        if (!empty($results) && is_array($results) && $results[0]["cnt"] > 0) {
            $data["activated_end_date"] = self::DB_DEFAULT_DATE;
            $data["activity"]           = '0';
            $this->DB->where('activated_end_date <',
                    date(self::DB_DATE_FORMAT_SEARCH))
                ->where('UNIX_TIMESTAMP(activated_end_date) >', '86400')
                ->update(USERS_TABLE, $data);
            $clean                      = $results[0]["cnt"];
        }
        echo "Make clean(Users activated): " . $clean . " Users was deactivated";
    }

    public function service_cron_users_featured()
    {
        $this->DB->select('COUNT(*) AS cnt')->from(USERS_TABLE)
            ->where('featured_end_date <', date(self::DB_DATE_FORMAT_SEARCH))
            ->where('UNIX_TIMESTAMP(featured_end_date) >', '86400');
        $results = $this->DB->get()->result_array();
        $clean   = 0;
        if (!empty($results) && is_array($results) && $results[0]["cnt"] > 0) {
            $data["featured_end_date"] = self::DB_DEFAULT_DATE;
            $this->DB->where('featured_end_date <',
                    date(self::DB_DATE_FORMAT_SEARCH))
                ->where('UNIX_TIMESTAMP(featured_end_date) >', '86400')
                ->update(USERS_TABLE, $data);
            $clean                     = $results[0]["cnt"];
        }
        echo "Make clean(Users featured): " . $clean . " Users was removed";
    }

    public function service_cron_hide_on_site()
    {
        $this->DB->select('COUNT(*) AS cnt')->from(USERS_TABLE)
            ->where('hide_on_site_end_date <', date(self::DB_DATE_FORMAT_SEARCH))
            ->where('UNIX_TIMESTAMP(hide_on_site_end_date) >', '86400');
        $results = $this->DB->get()->result_array();
        $clean   = 0;
        if (!empty($results) && is_array($results) && $results[0]["cnt"] > 0) {
            $data["hide_on_site_end_date"] = self::DB_DEFAULT_DATE;
            $this->DB->where('hide_on_site_end_date <',
                    date(self::DB_DATE_FORMAT_SEARCH))
                ->where('UNIX_TIMESTAMP(hide_on_site_end_date) >', '86400')
                ->update(USERS_TABLE, $data);
            $clean                         = $results[0]["cnt"];
        }
        echo "Make clean(hide on site): " . $clean . " Users was removed";
    }

    public function service_cron_highlight_in_search()
    {
        $this->DB->select('COUNT(*) AS cnt')->from(USERS_TABLE)
            ->where('highlight_in_search_end_date <',
                date(self::DB_DATE_FORMAT_SEARCH))
            ->where('UNIX_TIMESTAMP(highlight_in_search_end_date) >', '86400');
        $results = $this->DB->get()->result_array();
        $clean   = 0;
        if (!empty($results) && is_array($results) && $results[0]["cnt"] > 0) {
            $data["highlight_in_search_end_date"] = self::DB_DEFAULT_DATE;
            $this->DB->where('highlight_in_search_end_date <',
                    date(self::DB_DATE_FORMAT_SEARCH))
                ->where('UNIX_TIMESTAMP(highlight_in_search_end_date) >',
                    '86400')
                ->update(USERS_TABLE, $data);
            $clean                                = $results[0]["cnt"];
        }
        echo "Make clean(highlight in search): " . $clean . " Users was removed";
    }

    public function service_cron_up_in_search()
    {
        $this->DB->select('COUNT(*) AS cnt')->from(USERS_TABLE)
            ->where('up_in_search_end_date <', date(self::DB_DATE_FORMAT_SEARCH))
            ->where('UNIX_TIMESTAMP(up_in_search_end_date) >', '86400');
        $results = $this->DB->get()->result_array();
        $clean   = 0;
        if (!empty($results) && is_array($results) && $results[0]["cnt"] > 0) {
            $data["up_in_search_end_date"] = self::DB_DEFAULT_DATE;
            $this->DB->where('up_in_search_end_date <',
                    date(self::DB_DATE_FORMAT_SEARCH))
                ->where('UNIX_TIMESTAMP(up_in_search_end_date) >', '86400')
                ->update(USERS_TABLE, $data);
            $clean                         = $results[0]["cnt"];
        }
        echo "Make clean(up in search): " . $clean . " Users was removed";
    }

    public function service_cron_region_leader()
    {
        $this->CI->load->model('Services_model');
        $service_data = $this->CI->Services_model->get_service_by_gid('region_leader');
        $write_period = $service_data["data_admin_array"]["write_off_period"] * 60 * 60;
        $write_amount = $service_data["data_admin_array"]["write_off_amount"];

        $this->DB->select('COUNT(*) AS cnt')->from(USERS_TABLE)->where("leader_bid > 0 AND ( UNIX_TIMESTAMP()-UNIX_TIMESTAMP(leader_write_date)>'" . $write_period . "' )");
        $results = $this->DB->get()->result_array();
        $clean   = 0;
        if (!empty($results) && is_array($results) && $results[0]["cnt"] > 0) {
            $this->DB->set('leader_bid', 'leader_bid-' . $write_amount, false);
            $this->DB->set('leader_write_date', date(self::DB_DATE_FORMAT));
            $this->DB->where("leader_bid > 0 AND ( UNIX_TIMESTAMP()-UNIX_TIMESTAMP(leader_write_date)>'" . $write_period . "' )");
            $this->DB->update(USERS_TABLE);
            $clean = $results[0]["cnt"];
        }

        $this->DB->where("leader_bid < 0");
        $this->DB->update(USERS_TABLE, array('leader_bid' => 0));

        echo "Reculc bids(Users region leader): " . $clean . " users was updated";
    }

    public function get_common_criteria($data)
    {
        $criteria["where"]["approved"]                = '1';
        $criteria["where"]["confirm"]                 = '1';
        $criteria["where"]["activity"]                = '1';
        $criteria["where"]["hide_on_site_end_date <"] = date(self::DB_DATE_FORMAT_SEARCH);

        if (!empty($data['age_min'])) {
            $criteria["where"]["age >="] = intval($data["age_min"]);
        }
        if (!empty($data['age_max'])) {
            $criteria["where"]["age <="] = intval($data["age_max"]);
        }
        if (!empty($data['user_type'])) {
            if (is_array($data["user_type"])) {
                $criteria["where_in"]["user_type"] = $data["user_type"];
            } else {
                $criteria["where"]["user_type"] = $data["user_type"];
            }
        }
        if (!empty($data['looking_user_type'])) {
            $criteria["where"]["looking_user_type"] = $data["looking_user_type"];
        }
        if (!empty($data['online_status']) && $data['online_status']) {
            $criteria["where"]["online_status"] = 1;
        }

        if ($this->session->userdata('auth_type') == 'user') {
            $criteria["where"]["id !="] = $this->session->userdata('user_id');
        }

        if (!empty($data["id_country"])) {
            $data["id_country"]              = $data["id_country"];
            $criteria["where"]["id_country"] = $data["id_country"];
        }
        if (!empty($data["id_region"])) {
            $data["id_region"]              = intval($data["id_region"]);
            $criteria["where"]["id_region"] = $data["id_region"];
        }
        if (!empty($data["id_city"])) {
            $data["id_city"]              = intval($data["id_city"]);
            $criteria["where"]["id_city"] = $data["id_city"];
        }

        /* <custom_M> */
        if (!empty($data['looking_distance']) && $this->CI->session->userdata('auth_type') == 'user') {
            $user = $this->get_user_by_id($this->CI->session->userdata('user_id'));
            if (!empty($user['lat']) && !empty($user['lon'])) {
                $criteria['where']["(POW((69.1*(lon - " . $user['lon'] . ")*cos(" . $user['lat'] . "/57.3)),\"2\")+POW((69.1*(lat - " . $user['lat'] . ")),\"2\")) <="] =  $data['looking_distance']*1000;
            }
        }

        if (!empty($data['with_photo'])) {
            if ($data['with_photo'] > 0) {
                $criteria["where"]["user_logo !="] = '';
            } else {
                $criteria["where"]["user_logo"] = '';
            }
        }

        if (!empty($data['living_with']) && !is_array($data['living_with'])) {
            $criteria["where_sql"]["living_with"] =  ' `living_with` LIKE "%[' . $data["living_with"] . ']%"';
        } elseif (!empty($data['living_with']) && is_array($data['living_with'])) {
            $criteria["where_sql"]["living_with"] = '(`living_with` LIKE "%[' . array_shift($data["living_with"]) . ']%" ';
            foreach ($data['living_with'] as $living_with) {
                $criteria["where_sql"]["living_with"] .=  ' OR `living_with` LIKE "%[' . $living_with . ']%"';
            }
            $criteria["where_sql"]["living_with"] .= ')';
        }
        /* </custom_M> */

        return $criteria;
    }

    public function get_advanced_search_criteria($data)
    {
        $criteria = array();
        if ($this->pg_module->get_module_config('users', 'hide_user_names')) {
            unset($data['fname'], $data['sname']);
        }

        if (!empty($data['fname'])) {
            $criteria["like"]["fname"] = trim(strip_tags($data["fname"]));
        }
        if (!empty($data['sname'])) {
            $criteria["like"]["sname"] = trim(strip_tags($data["sname"]));
        }
        if (!empty($data['nickname'])) {
            $criteria["like"]["nickname"] = trim(strip_tags($data["nickname"]));
        }

        if (!empty($data["id"])) {
            if (is_array($data["id"])) {
                $criteria["where_in"]["id"] = $data["id"];
            } else {
                $criteria["where"]["id"] = $data["id"];
            }
        }

        foreach ($this->multiselect as $multiselect) {
            if (!empty($data[$multiselect]) && !in_array('', $data[$multiselect])) {
                $criteria["where_sql"][] = $multiselect . " & " . $this->arr_to_dec($data[$multiselect]) . " != 0";
            }
        }

        if (!empty($data['looking_for']) && !in_array('', $data['looking_for'])) {
            $criteria["where_sql"][] = 'looking_for' . " & " . $this->arr_to_dec($data['looking_for']) . " != 0";
        }

        if (!empty($data['keyword'])) {
            $criteria["where_sql"][] = 'search_field' . " AGAINST (" . $data['keyword'] . ")";
        }

        return $criteria;
    }

    public function get_default_search_data()
    {
        $this->CI->load->model('Properties_model');
        $user_type  = $this->CI->session->userdata('user_type');
        $user_types = $this->CI->Properties_model->get_property('user_type');
        if ($user_type !== false && isset($user_types['option'][$user_type])) {
            unset($user_types['option'][$user_type]);
        }
        if (!empty($user_types['option'])) {
            $data['user_type'] = array_shift(array_keys($user_types['option']));
        }
        $data['age_min'] = $this->pg_module->get_module_config('users',
            'age_min');
        $data['age_max'] = $this->pg_module->get_module_config('users',
            'age_max');

        $this->CI->load->model('Field_editor_model');
        $this->CI->load->model('field_editor/models/Field_editor_forms_model');
        $this->CI->Field_editor_model->initialize($this->form_editor_type);
        $form = $this->CI->Field_editor_forms_model->get_form_by_gid($this->advanced_search_form_gid,
            $this->form_editor_type);
        $form = $this->CI->Field_editor_forms_model->format_output_form($form,
            array(), true);
        if ($form['field_data']) {
            foreach ($form['field_data'] as $field_data) {
                if (isset($field_data['field_content']['value'])) {
                    $data[$field_data['field_content']['field_name']] = $field_data['field_content']['value'];
                }
            }
        }

        return $data;
    }

    public function get_minimum_search_data()
    {
        $data['age_min'] = $this->pg_module->get_module_config('users',
            'age_min');
        $data['age_max'] = $this->pg_module->get_module_config('users',
            'age_max');

        return $data;
    }

    public function update_online_status($user_id, $status)
    {
        $status = intval($status);
        if ($status) {
            $this->DB->set('date_last_activity', date(self::DB_DATE_FORMAT))
                ->where('id', $user_id)
                ->update(USERS_TABLE);
        }

        if ($user_id == $this->CI->session->userdata('user_id')) {
            $user_site_status = $this->CI->session->userdata('site_status');
        } else {
            $user             = $this->get_user_by_id($user_id);
            $user_site_status = !empty($user['site_status']) ? $user['site_status'] : $status;
        }
        if (!$status) {
            $user_site_status = 0;
        }

        $this->DB->set('online_status', $status)->where('id', $user_id)->update(USERS_TABLE);

        if ($this->DB->affected_rows()) {
            $this->CI->load->model('users/models/Users_statuses_model');
            $site_statuses = $this->CI->Users_statuses_model->statuses;
            $event_status  = isset($site_statuses[$user_site_status]) ? $site_statuses[$user_site_status] : 0;
            if ($event_status) {
                $this->CI->Users_statuses_model->execute_callbacks($event_status,
                    $user_id);
            }
        }
        // Network event
        if ($this->CI->pg_module->is_module_installed('network')) {
            $this->CI->load->model('network/models/Network_events_model');
            $this->CI->Network_events_model->emit($status ? 'active' : 'inactive',
                array(
                'id_user' => $user_id,
            ));
        }
    }

    public function cron_set_offline_status()
    {
        $where['online_status']         = '1';
        $where['date_last_activity <']  = date(self::DB_DATE_FORMAT_SEARCH,
            time() - 600);
        $where['date_last_activity !='] = self::DB_DEFAULT_DATE;
        $users                          = $this->get_users_list(1, 10000, null,
            array('where' => $where), null, false);
        $users_ids                      = array();
        foreach ($users as $user) {
            $users_ids[] = (int) $user['id'];
        }
        if ($users_ids) {
            $this->DB->where_in('id', $users_ids)->set('online_status', '0')->update(USERS_TABLE);

            // Network event
            if ($this->CI->pg_module->is_module_installed('network')) {
                $this->CI->load->model('network/models/Network_events_model');
                foreach ($users_ids as $user_id) {
                    $this->CI->Network_events_model->emit('inactive',
                        array(
                        'id_user' => $user_id,
                    ));
                }
            }

            $this->CI->load->model('users/models/Users_statuses_model');
            $this->CI->Users_statuses_model->execute_callbacks(0, $users_ids);
        }
    }
    /* SERVICES */

    /**
     * Check service is available
     *
     * @param integer $id_user      user identifier
     * @param string  $template_gid template guid
     *
     * @return array
     */
    private function serviceAvailableDefaultAction($id_user, $template_gid)
    {
        $return['available']         = 0;
        $return['content']           = '';
        $return['content_buy_block'] = false;
        $services_available          = false;

        if ($this->pg_module->is_module_installed('services')) {
            $this->CI->load->model('services/models/Services_users_model');
            $service_access     = $this->CI->Services_users_model->is_service_access($id_user,
                $template_gid);
            $services_available = (bool) $service_access['use_status'];
        }

        $return['services_available'] = $services_available;

        if ($services_available) {
            $return['content_buy_block'] = true;
        } else {
            $return['content']   = l('service_not_found', 'services');
            $return['available'] = 1;
        }

        return $return;
    }

    public function service_available_hide_on_site_action($id_user)
    {
        return $this->serviceAvailableDefaultAction($id_user,
                'hide_on_site_template');
    }

    public function service_available_highlight_in_search_action($id_user)
    {
        return $this->serviceAvailableDefaultAction($id_user,
                'highlight_in_search_template');
    }

    public function service_available_up_in_search_action($id_user)
    {
        return $this->serviceAvailableDefaultAction($id_user,
                'up_in_search_template');
    }

    public function service_available_ability_delete_action($id_user)
    {
        $result = $this->serviceAvailableDefaultAction($id_user,
            'ability_delete_template');
        if ($result['services_available']) {
            $result['content_buy_block'] = true;
            $result['content']           = $this->CI->Services_users_model->available_service_block($id_user,
                'ability_delete_template');
        } else {
            $result['content_buy_block'] = false;
            $result['content']           = '<script>locationHref("' . site_url() . 'users/account_delete")</script>';
        }
        $result['available'] = 0;

        return $result;
    }

    public function service_available_user_activate_in_search_action($id_user)
    {
        $return['available']         = 0;
        $return['content']           = '';
        $return['content_buy_block'] = false;

        $activated = $this->is_user_activated($id_user);
        $this->CI->load->model('Services_model');
        $service   = $this->CI->Services_model->get_service_by_gid('user_activate_in_search');
        if (!empty($service['status']) && !$activated['is_activity']) {
            $return['content_buy_block'] = true;
        } else {
            $this->service_set_user_activate_in_search($id_user, 0);
            $return['available'] = 1;
        }

        return $return;
    }

    public function service_available_users_featured_action($id_user)
    {
        $return['available']         = 0;
        $return['content']           = '';
        $return['content_buy_block'] = false;

        $this->CI->load->model('Services_model');
        $services_params                          = array();
        $services_params['where']['template_gid'] = 'users_featured_template';
        $services_params['where']["status"]       = 1;
        $services_count                           = $this->CI->Services_model->get_service_count($services_params);
        if ($services_count) {
            $return['content_buy_block'] = true;
        } else {
            $return['content']   = l('service_not_found', 'services');
            $return['available'] = 1;
        }

        return $return;
    }

    public function service_available_admin_approve_action($id_user)
    {
        $return['available']         = 0;
        $return['content']           = '';
        $return['content_buy_block'] = false;

        $this->CI->load->model('Services_model');
        $services_params                    = array();
        $services_params['where']['gid']    = 'admin_approve';
        $services_params['where']["status"] = 1;
        $services_count                     = $this->CI->Services_model->get_service_count($services_params);
        if ($services_count) {
            $return['content_buy_block'] = true;
        } else {
            $this->service_set_admin_approve($id_user);
            $return['available'] = 1;
        }

        return $return;
    }

    public function service_validate_user_activate_in_search($user_id, $data, $service_data
    = array(), $price = '')
    {
        return $this->serviceValidate('user_activate_in_search', $user_id,
                $data, $service_data, $price);
    }

    public function service_validate_users_featured($user_id, $data, $service_data
    = array(), $price = '')
    {
        return $this->serviceValidate('users_featured', $user_id, $data,
                $service_data, $price);
    }

    public function service_validate_admin_approve($user_id, $data, $service_data
    = array(), $price = '')
    {
        return $this->serviceValidate('admin_approve', $user_id, $data,
                $service_data, $price);
    }

    public function service_validate_hide_on_site($user_id, $data, $service_data
    = array(), $price = '')
    {
        return $this->serviceValidate('hide_on_site', $user_id, $data,
                $service_data, $price);
    }

    public function service_validate_highlight_in_search($user_id, $data, $service_data
    = array(), $price = '')
    {
        return $this->serviceValidate('highlight_in_search', $user_id, $data,
                $service_data, $price);
    }

    public function service_validate_up_in_search($user_id, $data, $service_data
    = array(), $price = '')
    {
        return $this->serviceValidate('up_in_search', $user_id, $data,
                $service_data, $price);
    }

    public function service_validate_ability_delete($user_id, $data, $service_data
    = array(), $price = '')
    {
        return $this->serviceValidate('ability_delete', $user_id, $data,
                $service_data, $price);
    }

    public function service_buy_region_leader($id_user, $price, $service, $template, $payment_data, $users_package_id
    = 0, $count = 1)
    {
        $user = $this->get_user_by_id($id_user);
        if ($user["leader_bid"] > 0) {
            $bid = $user["leader_bid"] + $price;
        } else {
            $bid = $price;
        }
        $text = $payment_data['user_data']['text'];

        $data = array(
            'leader_bid'        => $bid,
            'leader_text'       => $text,
            'leader_write_date' => date(self::DB_DATE_FORMAT),
        );
        $this->save_user($id_user, $data);

        $this->service_buy($id_user, $price, $service, $template, $payment_data,
            $users_package_id, 0, 0);

        $return['status']  = 1;
        $return['message'] = l('success_service_activating', 'services');

        return $return;
    }

    public function service_validate_region_leader($user_id, $data, $service_data, $price)
    {
        $return = array("errors" => array(), "data" => $data);
        if ($service_data["data_admin_array"]["min_bid"] > floatval($price)) {
            $return["errors"][] = l('error_service_leader_min_bid_error',
                'users');
        }
        if (empty($data["text"])) {
            $return["errors"][] = l('error_leader_text_is_empty', 'users');
        }

        return $return;
    }

    public function service_buy($id_user, $price, $service, $template, $payment_data, $users_package_id
    = 0, $count = 1, $status = 1)
    {
        $service_data = array(
            'id_user'             => $id_user,
            'service_gid'         => $service['gid'],
            'template_gid'        => $template['gid'],
            'service'             => $service,
            'template'            => $template,
            'payment_data'        => $payment_data,
            'id_users_package'    => $users_package_id,
            'id_users_membership' => !empty($payment_data['id_users_membership']) ? (int) $payment_data['id_users_membership'] : 0,
            'status'              => $status,
            'count'               => $count,
        );

        $this->CI->load->model('services/models/Services_users_model');

        return $this->CI->Services_users_model->save_service(null, $service_data);
    }

    private function serviceValidate($service_gid, $user_id, $data, $service_data
    = array(), $price = '')
    {
        $return = array("errors" => array(), "data" => $data);

        return $return;
    }

    public function service_activate_ability_delete($id_user, $id_user_service, $is_ajax
    = 0)
    {
        $id_user_service = intval($id_user_service);
        $return          = array('status' => 0, 'message' => '');

        $this->CI->load->model('services/models/Services_users_model');
        $user_service = $this->CI->Services_users_model->get_user_service_by_id($id_user,
            $id_user_service);
        if (empty($user_service) || !$user_service["status"]) {
            $return['status']  = 0;
            $return['message'] = l('error_service_activating', 'services');
        } else {
            $this->delete_user($id_user);
            --$user_service['count'];
            if ($user_service['count'] < 1) {
                $user_service["status"] = 0;
            }
            $this->CI->Services_users_model->save_service($id_user_service,
                $user_service);
            $return['status']  = 1;
            $return['message'] = l('success_service_activating', 'services');

            $auth_type = $this->CI->session->userdata("auth_type");
            if ($auth_type != 'admin') {
                $this->CI->load->model("users/models/Auth_model");
                $this->CI->Auth_model->logoff();
                if (!$is_ajax) {
                    redirect();
                }
            }
        }

        return $return;
    }

    public function service_activate_admin_approve($id_user, $id_user_service)
    {
        $id_user_service = intval($id_user_service);
        $return          = array('status' => 0, 'message' => '');

        $this->CI->load->model('services/models/Services_users_model');
        $user_service = $this->CI->Services_users_model->get_user_service_by_id($id_user,
            $id_user_service);
        if (empty($user_service) || !$user_service["status"] || $user_service['count'] < 1) {
            $return['status']  = 0;
            $return['message'] = l('error_service_activating', 'services');
        } else {
            $this->service_set_admin_approve($id_user);
            --$user_service['count'];
            if ($user_service['count'] < 1) {
                $user_service["status"] = 0;
            }
            $this->CI->Services_users_model->save_service($id_user_service,
                $user_service);
            $return['status']  = 1;
            $return['message'] = l('success_service_activating', 'services');
        }

        return $return;
    }

    public function service_activate_hide_on_site($id_user, $id_user_service)
    {
        $id_user_service = intval($id_user_service);
        $return          = array('status' => 0, 'message' => '');

        $this->CI->load->model('services/models/Services_users_model');
        $user_service = $this->CI->Services_users_model->get_user_service_by_id($id_user,
            $id_user_service);
        if (empty($user_service) || !$user_service["status"] || $user_service['count'] < 1) {
            $return['status']  = 0;
            $return['message'] = l('error_service_activating', 'services');
        } else {
            $user_service['date_expired'] = $this->service_set_hide_on_site($id_user,
                $user_service['service']['data_admin']['period']);
            --$user_service['count'];
            if ($user_service['count'] < 1) {
                $user_service["status"] = 0;
            }
            $this->CI->Services_users_model->save_service($id_user_service,
                $user_service);
            $return['status']  = 1;
            $return['message'] = l('success_service_activating', 'services');
        }

        return $return;
    }

    public function service_activate_highlight_in_search($id_user, $id_user_service)
    {
        $id_user_service = intval($id_user_service);
        $return          = array('status' => 0, 'message' => '');

        $this->CI->load->model('services/models/Services_users_model');
        $user_service = $this->CI->Services_users_model->get_user_service_by_id($id_user,
            $id_user_service);
        if (empty($user_service) || !$user_service["status"] || $user_service['count'] < 1) {
            $return['status']  = 0;
            $return['message'] = l('error_service_activating', 'services');
        } else {
            $user_service['date_expired'] = $this->service_set_highlight_in_search($id_user,
                $user_service['service']['data_admin']['period']);
            --$user_service['count'];
            if ($user_service['count'] < 1) {
                $user_service["status"] = 0;
            }
            $this->CI->Services_users_model->save_service($id_user_service,
                $user_service);
            $return['status']  = 1;
            $return['message'] = l('success_service_activating', 'services');
        }

        return $return;
    }

    public function service_activate_up_in_search($id_user, $id_user_service, $admin
    = false)
    {
        $id_user_service = intval($id_user_service);
        $return          = array('status' => 0, 'message' => '');

        $this->CI->load->model('services/models/Services_users_model');
        $user_service = $this->CI->Services_users_model->get_user_service_by_id($id_user,
            $id_user_service);
        if (empty($user_service) || !$user_service["status"]) {
            $return['status']  = 0;
            $return['message'] = l('error_service_activating', 'services');
        } else {
            $user_service['date_expired'] = $this->service_set_up_in_search($id_user,
                $user_service['service']['data_admin']['period']);
            --$user_service['count'];
            if ($user_service['count'] < 1) {
                $user_service["status"] = 0;
            }
            $this->CI->Services_users_model->save_service($id_user_service,
                $user_service);
            $return['status']  = 1;
            $return['message'] = l('success_service_activating', 'services');
        }

        return $return;
    }

    public function service_activate_users_featured($id_user, $id_user_service)
    {
        $id_user_service = intval($id_user_service);
        $return          = array('status' => 0, 'message' => '');

        $this->CI->load->model('services/models/Services_users_model');
        $user_service = $this->CI->Services_users_model->get_user_service_by_id($id_user,
            $id_user_service);
        if (empty($user_service) || !$user_service["status"]) {
            $return['status']  = 0;
            $return['message'] = l('error_service_activating', 'services');
        } else {
            $user_service['date_expired'] = $this->service_set_users_featured($id_user,
                $user_service['service']['data_admin']['period']);
            --$user_service['count'];
            if ($user_service['count'] < 1) {
                $user_service["status"] = 0;
            }
            $this->CI->Services_users_model->save_service($id_user_service,
                $user_service);

            $this->userTopEvent($id_user);

            $return['status']  = 1;
            $return['message'] = l('success_service_activating', 'services');
        }

        return $return;
    }

    private function userTopEvent($id = null)
    {
        if ($this->pg_module->is_module_installed('ratings')) {
            if ($id) {
                $event_handler = EventDispatcher::getInstance();
                $event = new \Pg\Modules\Ratings\Models\Events\EventRatings();
                $event_data = [];
                $event_data['id']     = $id;
                $event_data['action'] = 'ratings_top_user';
                $event_data['module'] = 'ratings';
                $event->setData($event_data);
                $event_handler->dispatch('ratings_top_user', $event);
            }
        }
        return;
    }

    public function service_activate_user_activate_in_search($id_user, $id_user_service)
    {
        $id_user_service = intval($id_user_service);
        $return          = array('status' => 0, 'message' => '');

        $this->CI->load->model('services/models/Services_users_model');
        $user_service = $this->CI->Services_users_model->get_user_service_by_id($id_user,
            $id_user_service);
        if (empty($user_service) || !$user_service["status"]) {
            $return['status']  = 0;
            $return['message'] = l('error_service_activating', 'services');
        } else {
            $user_service['date_expired'] = $this->service_set_user_activate_in_search($id_user,
                $user_service['service']['data_admin']['period']);
            --$user_service['count'];
            if ($user_service['count'] < 1) {
                $user_service["status"] = 0;
            }
            $this->CI->Services_users_model->save_service($id_user_service,
                $user_service);
            $return['status']  = 1;
            $return['message'] = l('success_service_activating', 'services');
        }

        return $return;
    }

    public function service_status_highlight_in_search($user_data)
    {
        $result['status']       = false;
        $result['service']      = array();
        $result['user_service'] = array();
        if (empty($user_data['is_highlight_in_search']) && !empty($user_data['confirm']) && $user_data['approved'] && $user_data['activity'] && $this->pg_module->is_module_installed('services')) {
            $this->CI->load->model('Services_model');
            $this->CI->load->model('services/models/Services_users_model');
            $result['service']              = $this->CI->Services_model->format_service($this->CI->Services_model->get_service_by_gid('highlight_in_search'));
            $result['name']                 = $result['service']['name'];
            $result['description']          = $result['service']['description'];
            $params                         = array();
            $params['where']['service_gid'] = 'highlight_in_search';
            $params['where']['id_user']     = $user_data['id'];
            $params['where']['status']      = 1;
            $params['where']['count >']     = 0;
            $result['user_service']         = $this->CI->Services_users_model->get_services_list($params);
            if ($result['service']['status'] || $result['user_service']) {
                $result['status'] = true;
            }
        }

        return $result;
    }

    public function service_status_up_in_search($user_data)
    {
        $result['status']       = false;
        $result['service']      = array();
        $result['user_service'] = array();
        if (empty($user_data['is_up_in_search']) && !empty($user_data['confirm']) && $user_data['approved'] && $user_data['activity'] && $this->pg_module->is_module_installed('services')) {
            $this->CI->load->model('Services_model');
            $this->CI->load->model('services/models/Services_users_model');
            $result['service']              = $this->CI->Services_model->format_service($this->CI->Services_model->get_service_by_gid('up_in_search'));
            $result['name']                 = $result['service']['name'];
            $result['description']          = $result['service']['description'];
            $params                         = array();
            $params['where']['service_gid'] = 'up_in_search';
            $params['where']['id_user']     = $user_data['id'];
            $params['where']['status']      = 1;
            $params['where']['count >']     = 0;
            $result['user_service']         = $this->CI->Services_users_model->get_services_list($params);
            if ($result['service']['status'] || $result['user_service']) {
                $result['status'] = true;
            }
        }

        return $result;
    }

    public function service_status_hide_on_site($user_data)
    {
        $this->CI->load->model('Services_model');
        $result['service'] = $this->CI->Services_model->format_service($this->CI->Services_model->get_service_by_gid('hide_on_site'));

        $result['status']       = false;
        $result['user_service'] = array();

        if (!empty($user_data['id']) && $user_data['is_hide_on_site'] == 0 && $this->pg_module->is_module_installed('services')) {
            $this->CI->load->model('services/models/Services_users_model');
            $result['name']                 = $result['service']['name'];
            $result['description']          = $result['service']['description'];
            $params                         = array();
            $params['where']['service_gid'] = 'hide_on_site';
            $params['where']['id_user']     = $user_data['id'];
            $params['where']['status']      = 1;
            $params['where']['count >']     = 0;
            $result['user_service']         = $this->CI->Services_users_model->get_services_list($params);
            if ($result['service']['status'] || $result['user_service']) {
                $result['status'] = true;
            }
        }

        return $result;
    }

    public function service_status_users_featured($user_data)
    {
        $result['status']       = false;
        $result['service']      = array();
        $result['user_service'] = array();
        if (!empty($user_data['confirm']) && $user_data['approved'] && $this->pg_module->is_module_installed('services')) {
            $this->CI->load->model('Services_model');
            $this->CI->load->model('services/models/Services_users_model');
            $result['service']              = $this->CI->Services_model->format_service($this->CI->Services_model->get_service_by_gid('users_featured'));
            $result['name']                 = $result['service']['name'];
            $result['description']          = $result['service']['description'];
            $params                         = array();
            $params['where']['service_gid'] = 'users_featured';
            $params['where']['id_user']     = $user_data['id'];
            $params['where']['status']      = 1;
            $params['where']['count >']     = 0;
            $result['user_service']         = $this->CI->Services_users_model->get_services_list($params);
            if ($result['service']['status'] || $result['user_service']) {
                $result['status'] = true;
            }
        }

        return $result;
    }

    public function service_status_activate_in_search($user_data)
    {
        $result['status']       = false;
        $result['service']      = array();
        $result['user_service'] = array();
        if (!empty($user_data['confirm']) && $user_data['approved'] && $this->pg_module->is_module_installed('services')) {
            $this->CI->load->model('Services_model');
            $this->CI->load->model('services/models/Services_users_model');
            $result['service']              = $this->CI->Services_model->format_service($this->CI->Services_model->get_service_by_gid('user_activate_in_search'));
            $result['name']                 = $result['service']['name'];
            $result['description']          = $result['service']['description'];
            $params                         = array();
            $params['where']['service_gid'] = 'user_activate_in_search';
            $params['where']['id_user']     = $user_data['id'];
            $params['where']['status']      = 1;
            $params['where']['count >']     = 0;
            $result['user_service']         = $this->CI->Services_users_model->get_services_list($params);
            if ($result['service']['status'] || $result['user_service']) {
                $result['status'] = true;
            }
        }

        return $result;
    }

    public function services_status($user_data)
    {
        if ($this->pg_module->is_module_installed('services')) {
            $this->CI->load->model('Services_model');
            $this->CI->Services_model->cache_all_services();
        }
        $result['highlight_in_search']     = $this->service_status_highlight_in_search($user_data);
        $result['up_in_search']            = $this->service_status_up_in_search($user_data);
        $result['hide_on_site']            = $this->service_status_hide_on_site($user_data);
        $result['users_featured']          = $this->service_status_users_featured($user_data);
        $result['user_activate_in_search'] = $this->service_status_activate_in_search($user_data);

        return $result;
    }

    public function _dynamic_block_get_registration_login_form($params, $view, $width
    = 100)
    {
        $data['rand']   = rand(1, 999999);
        $data['params'] = $params;
        $data['view']   = $view;
        $data['width']  = $width;
        $this->CI->view->assign('dynamic_block_registration_login_form_data',
            $data);

        return $this->CI->view->fetch('dynamic_block_registration_login_form',
                'user', 'users');
    }

    private function dynamicBlockGetUsers($type, $params, $view, $width)
    {
        $user_type = !empty($params['user_type']) ? $params['user_type'] : 0;
        switch ($type) {
            case 'active':
                $users = $this->get_active_users(intval($params['count']),
                    $user_type);
                break;
            case 'featured':
                $users = $this->get_featured_users(intval($params['count']),
                    $user_type);
                break;
            case 'new':
            default:
                $users = $this->get_new_users(intval($params['count']),
                    $user_type);
                break;
        }

        if (!empty($params['title_' . $this->CI->pg_language->current_lang_id])) {
            $title = $params['title_' . $this->CI->pg_language->current_lang_id];
        } elseif (!empty($params['title_' . $this->CI->pg_language->current_lang['code']])) {
            $title = $params['title_' . $this->CI->pg_language->current_lang['code']];
        } else {
            $title = '';
        }

        $this->CI->view->assign('dynamic_block_users', $users);
        $this->CI->view->assign('dynamic_block_users_params', $params);
        $this->CI->view->assign('dynamic_block_users_view', $view);
        $this->CI->view->assign('dynamic_block_users_width', $width);
        $this->CI->view->assign('dynamic_block_users_title', $title);

        return $this->CI->view->fetch('dynamic_block_users', 'user', 'users');
    }

    public function _dynamic_block_get_new_users($params, $view, $width = 100)
    {
        return $this->dynamicBlockGetUsers('new', $params, $view, $width);
    }

    public function _dynamic_block_get_active_users($params, $view, $width = 100)
    {
        return $this->dynamicBlockGetUsers('active', $params, $view, $width);
    }

    public function _dynamic_block_get_featured_users($params, $view, $width = 100)
    {
        return $this->dynamicBlockGetUsers('featured', $params, $view,
                $width = 100);
    }

    public function _dynamic_block_get_auth_links($params, $view, $width = 100)
    {
        $data['rand']   = rand(1, 999999);
        $data['params'] = $params;
        $this->CI->view->assign('dynamic_block_auth_links_data', $data);

        return $this->CI->view->fetch('dynamic_block_auth_links', 'user',
                'users');
    }

    public function _dynamic_block_get_lang_select($params, $view, $width = 100)
    {
        $data['rand']    = rand(1, 999999);
        $data['params']  = $params;
        $data['lang_id'] = $this->CI->session->userdata("lang_id");
        if (!$data['lang_id']) {
            $data['lang_id'] = $this->CI->pg_language->get_default_lang_id();
        }
        $data['languages'] = array();
        foreach ($this->CI->pg_language->languages as $lang) {
            if ($lang['status']) {
                $data['languages'][$lang['id']] = $lang['name'];
            }
        }
        $data['count_active'] = count($data['languages']);

        $this->CI->view->assign('dynamic_block_lang_select_data', $data);

        return $this->CI->view->fetch('dynamic_block_lang_select', 'user',
                'users');
    }

    public function comments_count_callback($count, $id)
    {
        $attrs['logo_comments_count'] = $count;
        $this->save_user($id, $attrs);
    }

    public function comments_object_callback($id = 0)
    {
        return array();
    }

    /**
     * Callback for spam module
     *
     * @param string  $action   action name
     * @param integer $user_ids user identifiers
     *
     * @return string
     */
    public function spam_callback($action, $data)
    {
        switch ($action) {
            case "ban":
                $this->save_user((int) $data, array("banned" => 1));

                return "banned";
            case "unban":
                $this->save_user((int) $data, array("banned" => 0));

                return "unbanned";
            case "delete":
                $this->delete_user((int) $data);

                return "removed";
            case 'get_content':
                if (empty($data)) {
                    return array();
                }
                $new_data = array();
                $return   = array();
                foreach ($data as $id) {
                    if (($this->get_users_count(array('where_in' => array(
                                'id' => $id)))) == 0) {
                        $return[$id]["content"]["view"] = $return[$id]["content"]["list"]
                            = "<span class='spam_object_delete'>" . l("error_is_deleted_users_object",
                                "spam") . "</span>";
                        $return[$id]["user_content"]    = l("author_unknown",
                            "spam");
                    } else {
                        $new_data[] = $id;
                    }
                }
                $users = $this->get_users_list(null, null, null, null,
                    (array) $new_data);
                foreach ($users as $user) {
                    $return[$user['id']]["content"]["list"] = $return[$user['id']]["content"]["view"]
                        = '<a href="' . site_url() . 'admin/users/edit/personal/' . $user['id'] . '">' . $user['output_name'] . '</a>, ' . $user['user_type_str'];
                    $return[$user['id']]["user_content"]    = $user['output_name'];
                }

                return $return;
            case 'get_subpost':
                return array();
            case 'get_link':
                if (empty($data)) {
                    return array();
                }
                $users  = $this->get_users_list(null, null, null, null,
                    (array) $data);
                $return = array();
                foreach ($users as $user) {
                    $return[$user['id']] = site_url() . 'admin/users/edit/personal/' . $user['id'];
                }

                return $return;
            case 'get_deletelink':
                return array();
            case 'get_object':
                if (empty($data)) {
                    return array();
                }
                $users = $this->get_users_list_by_key(null, null, null, null,
                    (array) $data);

                return $users;
        }
    }

    private function setCallbacks($id_user, $callbacks_gid)
    {
        $data = $this->get_user_by_id($id_user);
        if (in_array('users_delete', $callbacks_gid) || empty($data['id'])) {
            $this->callback_user_delete($id_user, '', $callbacks_gid);
        } else {
            $this->CI->load->model('users/models/Users_delete_callbacks_model');
            $this->CI->Users_delete_callbacks_model->execute_callbacks($id_user,
                $callbacks_gid);
        }
    }

    public function callback_user_delete($id_user, $callback_type, $callbacks_gid)
    {
        $data = $this->get_user_by_id($id_user);

        if (!empty($data['id'])) {
            $this->DB->where('id', $id_user);
            $this->DB->delete(USERS_TABLE);
            if ($this->CI->pg_module->is_module_installed('network') && empty($data['net_is_incomer'])) {
                $this->CI->load->model('network/models/Network_users_model');
                $this->CI->Network_users_model->user_deleted($data);
            }

            // delete avatar
            $this->CI->load->model("Uploads_model");
            if ($data['user_logo_moderation']) {
                $this->CI->Uploads_model->delete_upload($this->upload_config_id,
                    $id_user . "/", $data['user_logo_moderation']);
            }
            if ($data['user_logo']) {
                $this->CI->Uploads_model->delete_upload($this->upload_config_id,
                    $id_user . "/", $data['user_logo']);
            }

            // delete moderation
            $this->CI->load->model('Moderation_model');
            $this->CI->Moderation_model->delete_moderation_item_by_obj($this->moderation_type[0],
                $id_user);

            // delete links
            $this->delete_user_contacts($id_user);

            // delete user connections
            if ($this->pg_module->is_module_installed('users_connections')) {
                $this->CI->load->model('Users_connections_model');
                $this->CI->Users_connections_model->delete_user_connections($id_user);
            }

            // delete im messages
            if ($this->pg_module->is_module_installed('im')) {
                $this->CI->load->model('im/models/Im_messages_model');
                $this->CI->Im_messages_model->delete_message_by_user_id($id_user);
            }

            // delete perfect_match
            if ($this->pg_module->is_module_installed('perfect_match')) {
                $this->CI->load->model('Perfect_match_model');
                $this->CI->Perfect_match_model->callback_user_delete($id_user);
            }
        } else {
            $data['id'] = $id_user;
        }
        // save deleted user
        $this->CI->load->model('users/models/Users_deleted_model');
        $data['callbacks'] = $callbacks_gid;
        $this->CI->Users_deleted_model->save_deleted_user($data);

        return;
    }

    public function clear_user_content_cron()
    {
        $this->CI->load->model('users/models/Users_deleted_model');
        $this->CI->load->model('users/models/Users_delete_callbacks_model');
        $user_ids = $this->CI->Users_deleted_model->get_all_users_id(0);
        foreach ($user_ids as $id_user) {
            $callbacks_gid = $this->CI->Users_deleted_model->get_user_callback_gid($id_user,
                0);
            $this->CI->Users_delete_callbacks_model->execute_callbacks($id_user,
                $callbacks_gid);
            $this->CI->Users_deleted_model->set_status_deleted($id_user, 1);
        }
    }

    public function handler_active($data)
    {
        return $this->update_online_status($data['id_user'], 1);
    }

    public function handler_inactive($data)
    {
        return $this->update_online_status($data['id_user'], 0);
    }

    /**
     * Return available user types
     *
     * @param boolean $only_code only type code
     * @param integer $lang_id   language identifier
     *
     * @return array
     */
    public function get_user_types($only_code = false, $lang_id = null)
    {
        if (empty($lang_id)) {
            $lang_id = $this->CI->pg_language->current_lang_id;
        }

        $this->CI->load->model('Properties_model');
        $user_types = $this->CI->Properties_model->get_property('user_type',
            $lang_id);

        if ($only_code) {
            return array_keys($user_types['option']);
        }

        return $user_types;
    }

    /**
     * Return user type by default
     *
     * @return string
     */
    public function get_user_type_default()
    {
        $user_types = $this->get_user_types(true);
        if (empty($user_types)) {
            return '';
        }

        return current($user_types);
    }

    public function lang_dedicate_module_callback_add($lang_id = false)
    {
        return true;
    }

    public function lang_dedicate_module_callback_delete($lang_id = false)
    {
        if (!$lang_id) {
            return false;
        }
        $attrs = array('lang_id' => $this->CI->pg_language->get_default_lang_id());
        $this->DB->where('lang_id', $lang_id)
            ->update(USERS_TABLE, $attrs);

        return true;
    }

    /**
     * Install user fields of ratings
     *
     * @param array $fields fields data
     *
     * @return void
     */
    public function install_ratings_fields($fields = array())
    {
        if (empty($fields)) {
            return;
        }
        $this->CI->load->dbforge();
        $table_fields = $this->CI->db->list_fields(USERS_TABLE);
        foreach ((array) $fields as $field_name => $field_data) {
            if (!in_array($field_name, $table_fields)) {
                $this->CI->dbforge->add_column(USERS_TABLE,
                    array($field_name => $field_data));
            }
        }
    }

    /**
     * Uninstall fields of ratings
     *
     * @param array $fields fields data
     *
     * @return void
     */
    public function deinstall_ratings_fields($fields = array())
    {
        if (empty($fields)) {
            return;
        }
        $this->CI->load->dbforge();
        $table_fields = $this->CI->db->list_fields(USERS_TABLE);
        foreach ($fields as $field_name) {
            if (in_array($field_name, $table_fields)) {
                $this->CI->dbforge->drop_column(USERS_TABLE, $field_name);
            }
        }
    }

    /**
     * Process event of ratings module
     *
     * @param string $action action name
     * @param array  $data   ratings data
     *
     * @return mixed
     */
    public function callback_ratings($action, $data)
    {
        switch ($action) {
            case 'update':
                $user_data['rating_type']   = $data['type_gid'];
                $user_data['rating_sorter'] = $data['rating_sorter'];
                $user_data['rating_value']  = $data['rating_value'];
                $user_data['rating_count']  = $data['rating_count'];
                $this->save_user($data['id_object'], $user_data);
                break;
            case 'get_object':
                if (empty($data)) {
                    return array();
                }
                $users = $this->get_users_list_by_key(null, null, null, null,
                    (array) $data);

                return $users;
                break;
        }
    }

    public function get_rating_object_by_id($id, $formatted = false, $safe_format
    = false)
    {
        $result = $this->DB->select(implode(", ", $this->fields_all))
                ->from(USERS_TABLE)
                ->where("id", $id)
                ->get()->result_array();
        if (empty($result)) {
            return false;
        } elseif ($formatted) {
            return $this->format_user($result[0], $safe_format);
        } else {
            return $result[0];
        }
    }

    public function re_format_users($data, $safe_format = false, $lang_id = '')
    {
        $table_fields      = $this->DB->list_fields(USERS_TABLE);
        $this->CI->load->model('Field_editor_model');
        $this->CI->Field_editor_model->initialize($this->form_editor_type);
        $fe_fields         = $this->CI->Field_editor_model->get_fields_list();
        $fields_additional = array();
        foreach ($fe_fields as $fe_field) {
            if (in_array($fe_field['field_name'], $table_fields)) {
                $fields_additional[] = $fe_field['field_name'];
            }
        }
        if (!empty($data)) {
            $fields_additional[] = 'id';
            foreach ($data as $user) {
                $ids[] = $user['id'];
            }
            $this->DB->select(implode(", ", $fields_additional));
            $this->DB->from(USERS_TABLE);
            $this->DB->where_in("id", $ids);

            $results            = $this->DB->get()->result_array();
            $additional_results = array();
            foreach ($results as $key => $value) {
                $additional_results[$value['id']] = $value;
            }
            foreach ($data as $key => $user) {
                if (isset($additional_results[$data[$key]['id']])) {
                    $data[$key] = array_merge($user,
                        $additional_results[$data[$key]['id']]);
                }
            }
        }

        return $data;
    }

    /**
     *  Demo user
     *
     *  @param array $user
     *
     *  @return array
     */
    public function demoUser(array $user)
    {
        return array_merge($user, $this->demo_user);
    }

    public function getVisit($user_id)
    {
        $this->DB->select();
        $this->DB->from(USERS_SITE_VISIT_TABLE);
        $this->DB->where("user_id", $user_id);
        $this->DB->where("date", date(self::DB_DATE_SIMPLE_FORMAT));
        $result = $this->DB->get()->result_array();
        if (empty($result)) {
            return false;
        } else {
            return $result[0];
        }
    }

    public function setVisit($user_id)
    {
        $attrs            = array();
        $attrs['date']    = date(self::DB_DATE_SIMPLE_FORMAT);
        $attrs['user_id'] = $user_id;
        if (!$this->getVisit($user_id)) {
            $this->DB->insert(USERS_SITE_VISIT_TABLE, $attrs);
            $this->userSiteVisitEvent($user_id);
        }
    }

    public function userSiteVisitEvent($id = null)
    {
        if ($id) {
            $event_handler  = EventDispatcher::getInstance();
            $event = new EventUsers();
            $event_data = [];
            $event_data['id']     = $id;
            $event_data['action'] = 'users_site_visit';
            $event_data['module'] = self::MODULE_GID;
            $event->setData($event_data);
            $event_handler->dispatch('users_site_visit', $event);
        }
    }

    public function bonusCounterCallback($counter = [])
    {
        $event_handler = EventDispatcher::getInstance();
        $event  = new EventUsers();
        $event->setData($counter);
        $event_handler->dispatch('bonus_counter', $event);
    }

    public function bonusActionCallback($data = [])
    {
        $counter = [];
        if (!empty($data)) {
            $counter = $data['counter'];
            $action = $data['action'];
            $counter['is_new_counter'] = $data['is_new_counter'];
            $counter['repetition']  = $data['bonus']['repetition'];
            if ($action['action'] == 'users_site_visit') {
                if (isset($counter['date_modified'])) {
                    $last_date = strtotime(date_format(date_create($counter['date_modified']), 'Y-m-d'));
                    $cur_date  = strtotime(date('Y-m-d'));
                    $gap_time  = $cur_date - $last_date;
                    if ($gap_time > 86400) {
                        $counter['count'] = 1;
                    } else {
                        $counter['count'] = $counter['count'] + 1;
                    }
                } else {
                    $counter['count'] = 1;
                }
            }
            if ($action['action'] == 'users_add_location') {
                $counter['count'] = 1;
            }
            if ($action['action'] == 'users_add_profile_logo') {
                $counter['count'] = 1;
            }
            if ($action['action'] == 'users_update_user_profile') {
                $user = $this->get_user_by_id($counter['id_user']);
                $counter['count'] = $user['profile_completion'];
            }

            $this->bonusCounterCallback($counter);
        }
    }

    public function updateUserProfile($id = null)
    {
        if ($id) {
            $user_before  = $this->profile_before;
            $user_after = $this->profile_after;
            $this->profile_before = null;
            $this->profile_after  = null;

            if ($user_after['user_logo'] != $user_before['user_logo']) {
                $this->addProfileLogo($id);
            }

            if ($user_after['id_country'] && $user_after['id_region']) {
                $this->addLocation($id);
            }
            $event_handler = EventDispatcher::getInstance();
            $event = new EventUsers();
            $event_data = [];
            $event_data['id']  = $id;
            $event_data['action'] = 'users_update_user_profile';
            $event_data['module'] = self::MODULE_GID;
            $event->setData($event_data);
            $event_handler->dispatch('users_update_user_profile', $event);
        }
    }

    public function addProfileLogo($id = null)
    {
        $event_handler        = EventDispatcher::getInstance();
        $event= new EventUsers();
        $event_data = [];
        $event_data['id'] = $id;
        $event_data['action'] = 'users_add_profile_logo';
        $event_data['module'] = self::MODULE_GID;
        $event->setData($event_data);
        $event_handler->dispatch('users_add_profile_logo', $event);
    }

    public function addLocation($id = null)
    {
        $event_handler        = EventDispatcher::getInstance();
        $event = new EventUsers();
        $event_data = [];
        $event_data['id']     = $id;
        $event_data['action'] = 'users_add_location';
        $event_data['module'] = self::MODULE_GID;
        $event->setData($event_data);
        $event_handler->dispatch('users_add_location', $event);
    }

    public function formatDashboardRecords($data)
    {
        $format_data = $this->format_users($data);

        foreach ($format_data as $key => $value) {
            $this->CI->view->assign('data', $value);
            $format_data[$key]['type']['module'] = self::MODULE_GID;
            $format_data[$key]['content'] = $this->CI->view->fetch('dashboard', 'admin', 'users');
        }

        return $format_data;
    }

    public function getDashboardData($user_id, $status)
    {
        $is_processed = false;

        $use_email_confirmation = (bool) $this->pg_module->get_module_config('users',  'user_confirm');
        $use_user_approve = (int) $this->pg_module->get_module_config('users',  'user_approve');

        if ($status == self::STATUS_ADDED && ($use_email_confirmation || $use_user_approve == 1)) {
            $is_processed = true;
        } elseif ($status == self::STATUS_SAVED && isset($this->profile_before['status']) && $this->profile_before['status'] == 1 && isset($this->profile_after['status']) && $this->profile_after['status'] == 0) {
            $is_processed = true;
        }

        if (!$is_processed) {
            return false;
        }

        if (!empty($this->profile_after)) {
            $data = $this->profile_after;
        } else {
            $data = $this->get_user_by_id($user_id);
        }

        $data['dashboard_header']      = 'header_moderation_user_status';
        $data['dashboard_action_link'] = 'admin/users/index/not_active/';
        $data['dashboard_action_name'] = 'link_user_action';
        return $data;
    }

    public function getDashboardOptions($user_id)
    {
        $data = !empty($this->profile_after) ? $this->profile_after : $this->get_user_by_id($user_id);
        return [
            'dashboard_header' => 'header_moderation_user_logo',
            'dashboard_action_link' => 'admin/moderation/index/',
            'dashboard_action_name' => 'link_user_action',
            'fname' => $data['fname'],
            'sname' => $data['sname'],
            'nickname' => $data['nickname'],
        ];

    }

    /**
     * Add roles to user
     * @param mixed $user User id or array of user data including roles
     * @param array $new_roles
     * @return array
     */
    public function addRoles($user, array $new_roles)
    {
        if (is_int($user)) {
            $db_user = $this->get_user_by_id($user);
            $user    = $this->save_user($db_user['id'],
                [
                'roles' => array_unique(array_merge($this->rolesDecode($db_user['roles']),
                        $new_roles))
            ]);
        } else {
            $user['roles'] = array_unique(array_merge($this->rolesDecode($user['roles']),
                    $new_roles));
        }
        return $user;
    }

    /**
     * Get user roles
     * @param int $user_id
     * @return array
     */
    public function getUserRoles($user_id)
    {
        return ($roles = $this->get_user_by_id($user_id)['roles']) ? $this->rolesDecode($roles) : ['guest'];
    }

    /**
     * Decode roles
     * @param string $roles
     * @return array
     * @throws \InvalidArgumentException
     */
    private function rolesDecode($roles)
    {
        if (is_string($roles)) {
            $roles = explode(',', $roles);
        } elseif (!is_array($roles)) {
            throw new \InvalidArgumentException('(users) Wrong roles format');
        }
        return $roles;
    }

    /**
     * Encode roles
     * @param array $roles
     * @return string
     * @throws \InvalidArgumentException
     */
    private function rolesEncode($roles)
    {
        if (is_array($roles)) {
            $roles = implode(',', $roles);
        } elseif (!is_string($roles)) {
            throw new \InvalidArgumentException('(users) Wrong roles format');
        }
        return $roles;
    }

    public function registerUser($data)
    {
        $user_id = $this->Users_model->save_user(null, $data, 'user_icon');

        $event_handler = EventDispatcher::getInstance();
        $event         = new EventUsers();
        $event->setSearchFrom(intval($user_id));
        $event_handler->dispatch('user_register', $event);

        return $user_id;
    }

    public function save_aviary_file($user_id, $file_url)
    {
        $user = $this->get_user_by_id($user_id);
        if (!$user || !$user['user_logo']) {
            return false;
        }

        if (function_exists('curl_init')) {
            $defaults = array(
                CURLOPT_URL => $file_url,
                CURLOPT_HEADER => 0,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 4,
                CURLOPT_SSL_VERIFYPEER => false,
            );
            $ch = curl_init();
            curl_setopt_array($ch, $defaults);
            $image_data = curl_exec($ch);
            curl_close($ch);
        } else {
            $image_data = file_get_contents($file_url);
        }

        $path = TEMPPATH . 'user-logo';
        if (!is_dir($path)) {
            @mkdir($path, 0777, true);
        }

        $filename = $user['user_logo'];
        file_put_contents($path . '/' . $filename, $image_data);

        $this->CI->load->model('Uploads_model');
        $this->CI->Uploads_model->delete_upload('user-logo', $user["id"], $filename);

        $this->CI->Uploads_model->delete_path('user-logo', $user["id"]);

        $img_return = $this->CI->Uploads_model->upload_exist('user-logo', $user['id'], $path . '/' . $filename);
        if (empty($img_return["errors"])) {
            $this->CI->load->model('Moderation_model');
            $mstatus = intval($this->CI->Moderation_model->get_moderation_type_status($this->moderation_type[0]));
            if ($mstatus != 2) {
                $this->CI->Moderation_model->add_moderation_item($this->moderation_type[0], $user_id);
                if ($mstatus == 1) {
                    $img_data['user_logo'] = $img_return['file'];
                    $img_data['user_logo_moderation'] = '';
                } else {
                    $img_data['user_logo'] = $img_return['file'];
                    $img_data['user_logo_moderation'] = $img_return['file'];
                }
            } else {
                $img_data['user_logo'] = $img_return['file'];
            }

            $mtype = $this->CI->Moderation_model->get_moderation_type($this->moderation_type[0]);
            if ($mtype['mtype'] > 0) {
                $this->load->model('menu/models/Indicators_model');
                $this->Indicators_model->add('new_moderation_item', $user_id);
            }

            $this->save_user($user_id, $img_data, '', true, true);
        }

        @unlink($path . '/' . $filename);

        $user = $this->get_user_by_id($user_id, true);

        $this->CI->session->set_userdata('logo', $user['media']['user_logo']['thumbs']['small']);

        return json_encode($user['media']['user_logo']);
    }

    public function save_aviary($url, $data)
    {
        if (empty($data['user_id'])) {
            show_404();
        }

        $data['user_id'] = (int)$data['user_id'];
        if (!$data['user_id']) {
            show_404();
        }

        return $this->save_aviary_file($data['user_id'], $url);
    }

    protected function arr_to_dec($data)
    {
        $data = (array) $data;
        if (empty($data)) {
            return 0;
        }
        $binary_string = "";
        $max = max($data);
        for ($i = 0; $i <= $max; ++$i) {
            $binary_string = ((in_array($i, $data)) ? "1" : "0") . $binary_string;
        }

        return bindec($binary_string);
    }

    protected function dec_to_arr($dec)
    {
        $data = array();
        $binary_string = decbin($dec);
        $arr = str_split($binary_string);
        $max = count($arr) - 1;
        for ($i = 0; $i <= $max; ++$i) {
            if ($arr[$max - $i] == 1) {
                $data[] = $i;
            }
        }

        return $data;
    }

    public function cronMatchesNotify()
    {
        $this->CI->load->model('Notifications_model');
        $this->CI->load->helper('users');

        $new_users = [];

        $this->DB
            ->select(implode(", ", $this->fields_all))
            ->from(USERS_TABLE)
            ->where('date_created >= ', date('Y-m-d', strtotime('-7 days')))
            ->limit(10);

        $results = $this->DB->get()->result_array();
        if (!empty($results) && is_array($results)) {
            $data = $this->format_users($results);
            foreach ($data as $user) {
                $new_users[] = $user;
            }
        }

        $this->DB
            ->select(implode(",", $this->fields_all))
            ->from(USERS_TABLE)
            ->where('is_subscribed', 1);

        $results = $this->DB->get()->result_array();
        if (!empty($results) && is_array($results)) {
            $data = $this->format_users($results, false);
            foreach ($data as $nuser) {
                $match_users = [];

                $relevation = $this->Users_model->getRelevation($nuser);

                $this->DB
                    ->select(implode(", ", array_merge($this->fields_all, [$relevation . ' AS relevation'])))
                    ->from(USERS_TABLE)
                    ->where('user_type', $nuser['looking_user_type'])
                    ->having('relevation > ', 70)
                    ->limit(10)
                    ->order_by('relevation DESC');
                $results = $this->DB->get()->result_array();

                if (!empty($results) && is_array($results)) {
                    $data = $this->format_users($results, false, $nuser['lang_id']);
                    foreach ($data as $user) {
                        $match_users[] = $user;
                    }
                }

                if (count($new_users) > 0 || count($match_users) > 0) {
                    // send notifications
                    $this->CI->Notifications_model->send_notification(
                        $nuser['email'], 'matches', [
                            'fname' => $nuser['fname'],
                            'sname' => $nuser['sname'],
                            'new_users' => print_users_list($new_users),
                            'match_users' => print_users_list($match_users),
                        ], '', $nuser['lang_id']);
                }
            }
        }
    }

    public function getRelevation($user)
    {
        $relevation = '';
        $living_with_criteria = '';
        $total = 0;

        if (!empty($user['looking_living_with'])) {
            $living_with_criteria .= '(`living_with` LIKE "%' . array_shift($user['looking_living_with']) . '%" ';
            foreach ($user['looking_living_with'] as $living_with) {
                $living_with_criteria .=  ' OR `living_with` LIKE "%[' . $living_with . ']%"';
            }
            $living_with_criteria .= ')';
        }

        $total++;

        if ($living_with_criteria) {
            $relevation .= 'CASE WHEN ' . $living_with_criteria . ' THEN 1 ELSE 0 END';
        } else {
            $relevation .= '1';
        }

        $relevation .= ' + ';
        $total++;

        if ($user['age_min'] && $user['age_max']) {
            $relevation .= 'CASE WHEN age >= ' . $user['age_min'] . ' AND age <= ' . $user['age_max'] . ' THEN 1 ELSE 0 END';
        } elseif ($user['age_min']) {
            $relevation .= 'CASE WHEN age >= ' . $user['age_min'] . ' THEN 1 ELSE 0 END';
        } elseif ($user['age_max']) {
            $relevation .= 'CASE WHEN age <= ' . $user['age_max'] . ' THEN 1 ELSE 0 END';
        } else {
            $relevation .= '1';
        }

        $relevation .= ' + ';
        $total++;

        if ($user['height_min'] && $user['height_max']) {
            $relevation .= 'CASE WHEN height >= ' . $user['height_min'] . ' AND height <= ' . $user['height_max'] . ' THEN 1 ELSE 0 END';
        } elseif ($user['height_min']) {
            $relevation .= 'CASE WHEN height >= ' . $user['height_min'] . ' THEN 1 ELSE 0 END';
        } elseif ($user['height_max']) {
            $relevation .= 'CASE WHEN height <= ' . $user['height_max'] . ' THEN 1 ELSE 0 END';
        } else {
            $relevation .= '1';
        }

        foreach ($this->multiselect as $multiselect) {
            $relevation .= ' + ';
            $total++;
            if (!empty($user['loooking_' . $multiselect])) {
                $relevation .= 'CASE WHEN ' . $multiselect . " & " . $this->arr_to_dec($user['loooking_' . $multiselect]) . " != 0  THEN 1 ELSE 0 END";
            } else {
                $relevation .= '1';
            }
        }

        return 'ROUND(100*(' . $relevation . ')/' . $total . ')';
    }

    public function getReverseRelevation($user)
    {
        $relevation = '';
        $living_with_criteria = '';
        $total = 0;

        if (!empty($user['living_with'])) {
            $living_with_criteria .= '(`looking_living_with` LIKE "%' . array_shift($user['living_with']) . '%" ';
            foreach ($user['living_with'] as $living_with) {
                $living_with_criteria .=  ' OR `looking_living_with` LIKE "%[' . $living_with . ']%"';
            }
            $living_with_criteria .= ')';
        }

        $total++;

        if ($living_with_criteria) {
            $relevation .= 'CASE WHEN ' . $living_with_criteria . ' THEN 1 ELSE 0 END';
        } else {
            $relevation .= '1';
        }

        $relevation .= ' + ';
        $total++;

        if ($user['age']) {
            $relevation .= 'CASE WHEN age_min <= ' . $user['age'] . ' AND (age_max >= ' . $user['age'] . ' OR age_max = 0) THEN 1 ELSE 0 END';
        } else {
            $relevation .= '1';
        }

        $relevation .= ' + ';
        $total++;

        if ($user['height']) {
            $relevation .= 'CASE WHEN height_min <= ' . $user['height'] . ' AND (height_max >= ' . $user['height_max'] . ' OR height_max = 0) THEN 1 ELSE 0 END';
        } else {
            $relevation .= '1';
        }

        foreach ($this->multiselect as $multiselect) {
            $relevation .= ' + ';
            $total++;
            if (!empty($user[$multiselect])) {
                $relevation .= 'CASE WHEN looking_' . $multiselect . ' = 0 OR looking_' . $multiselect . " & " . $this->arr_to_dec($user[$multiselect]) . ' != 0  THEN 1 ELSE 0 END';
            } else {
                $relevation .= '1';
            }
        }

        return 'ROUND(100*(' . $relevation . ')/' . $total . ')';
    }
}
