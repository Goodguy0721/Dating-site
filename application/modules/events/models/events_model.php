<?php

namespace Pg\Modules\Events\Models;

/**
 * Events main model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Alexey Chulkov <achulkov@pilotgroup.net>
 * */
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
if (!defined('TABLE_EVENTS')) {
    define('TABLE_EVENTS', DB_PREFIX . 'events');
}
if (!defined('TABLE_EVENTS_USERS')) {
    define('TABLE_EVENTS_USERS', DB_PREFIX . 'events_users');
}

class Events_model extends \Model
{
    const DB_DATE_FORMAT = 'Y-m-d H:i:s';
    const DB_DATE_FORMAT_SEARCH = 'Y-m-d H:i';
    const DATE_FORMAT = 'Y-m-d';
    const TIME_FORMAT = 'H:i';
    const DB_DEFAULT_DATE = '0000-00-00';
    const DB_DEFAULT_TIME = '00:00:00';

    private $CI;
    private $DB;
    public $upload_gid = 'event-logo';
    //public $upload_album_gid = 'events-album-photo';
    public $album_type = 'events_type';
    private $_ds_gid = 'chat_message';
    private $_modules_category = array(
        'communication',
        'action',
    );
    private $_fields = array(
        'id',
        'fk_user_id',
        'category',
        'country_code',
        'fk_region_id',
        'fk_city_id',
        'address',
        'lat',
        'lon',
        'venue',
        'img',
        'max_participants',
        'date_started',
        'date_ended',
        'deadline_date',
        'is_active',
        'is_admin',
        'status',
        'date_created',
        'album_id',
        'search_data',
        'event_settings',
        'comments_count',
    );
    private $_fields_participants = array(
        'id',
        'fk_event_id',
        'fk_user_id',
        'is_invite',
        'is_new',
        'status',
        'response_date',
    );
    private $_fields_event_settings = array(
        'event_settings',
        'max_participants',
        'is_admin',
        'fk_user_id'
    );
    public $wall_events = array(
        'event_add' => array(
            'gid'                 => 'event_add',
            'status'              => '1',
            'module'              => 'events',
            'model'               => 'events_model',
            'method_format_event' => 'formatWallEvents',
            'settings'            => array(
                'join_period' => 10, // minutes, do not use
                'permissions' => array(
                    'permissions' => 3, // permissions 0 - only for me, 1 - for me and friends, 2 - for registered, 3 - for all
                    'feed'        => 1, // show friends events in user feed
                ),
            ),
        ),
    );
    public $moderation_type = array('event_data');
    public $participant_statuses = array(
        'approve_gid' => 'approved',
        'pending_gid' => 'pending',
        'decline_gid' => 'declined',
    );

    /**
     *  Constructor
     *
     *  @return Events_model
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
    private function _fields_all($lang_id = null)
    {
        $fields = array();
        if (is_null($lang_id)) {
            $lang_ids = $this->CI->pg_language->languages;
            foreach ($lang_ids as $lang) {
                $fields[] = 'name_' . $lang['id'];
                //$fields[] = 'annotation_' . $lang['id'];
                $fields[] = 'description_' . $lang['id'];
            }
        } else {
            $fields[] = 'name_' . $lang_id . ' as name';
            //$fields[] = 'annotation_' . $default_lang_id;
            $fields[] = 'description_' . $lang_id . ' as description';
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
    private function _fields_users_all($lang_id = null)
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
            'is_active' => $this->CI->pg_module->get_module_config('events', 'is_active'),
        );

        return $this->_formatSettings($data);
    }

    public function getEventSettins($event_id)
    {
        $this->DB->select(implode(', ', $this->_fields_event_settings));
        $this->DB->where('id', $event_id);
        $this->DB->from(TABLE_EVENTS);
        $result = $this->DB->get()->result_array();

        return $this->formatEventSettings($result[0]);
    }

    private function formatEventSettings($data = array()) {
        $return = array();

        if(isset($data['event_settings'])) {
            $return['event_settings'] = unserialize($data['event_settings']);
        }

        if(isset($data['max_participants'])) {
            $return['max_participants'] = $data['max_participants'];
        }

        $user_id = $this->CI->session->userdata('user_id');
        $return['is_owner'] = ($user_id == $data['fk_user_id'] && !$data['is_admin']) ? true : false;

        return $return;
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
        $data = ($this->CI->session->userdata("auth_type") == "user") ? array($select) : $this->_getModules();
        foreach ($data as $value) {
            if (!empty($value) && $value !== 'events') {
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
    private function _getModules()
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
    private function _getMessageFields($setting_gid)
    {
        foreach ($this->CI->pg_language->languages as $lid => $lang) {
            $r = $this->CI->pg_language->ds->get_reference('events', $this->_ds_gid, $lid);
            $lang_data[$lid] = $r["option"][$setting_gid];
        }

        return $lang_data;
    }

    public function getEventById($event_id, $lang_id = null)
    {
        $fields = array_merge($this->_fields_all($lang_id), $this->_fields);
        $result = $this->DB->select(implode(", ", $fields))
                ->from(TABLE_EVENTS)
                ->where("id", $event_id)
                ->get()->result_array();
        if (empty($result)) {
            return false;
        } else {
            return $this->formatEvent($result[0]);
        }
    }

    public function getEventParticipants($event_id)
    {
        $this->DB->select('fk_user_id');
        $this->DB->where('fk_event_id', $event_id);
        $this->DB->from(TABLE_EVENTS_USERS);

        $result = $this->DB->get()->result_array();

        return $result;
    }

    /**
     *  Image event
     *
     *  @param integer $lang_id
     *  @param array $data
     *
     *  @return array
     */
    public function getEvent($data = array(), $lang_id = null)
    {
        $fields = $this->_fields_all($lang_id);
        $select = array_merge($fields, $this->_fields);
        $this->DB->select(implode(", ", $select));
        $this->DB->from(TABLE_EVENTS);
        foreach ($data as $field => $value) {
            $this->DB->where($field, $value);
        }
        $result = $this->DB->get()->result_array();

        return empty($result) ? false : $this->formatEvent($result[0]);
    }

    public function getEventUser($user_id, $is_admin = false)
    {
        $this->CI->load->model("Users_model");
        if (!$is_admin) {
            $user_create = $this->Users_model->get_user_by_id($user_id, true);
        } else {
            $this->CI->load->model("Ausers_model");
            $user_create = $this->CI->Ausers_model->get_user_by_id($user_id);
        }

        return $user_create;
    }

    /**
     *  Image event
     *
     *  @param integer $lang_id
     *  @param array $data
     *
     *  @return array
     */
    public function getImage($data = array(), $lang_id = null)
    {
        $fields = $this->_fields_all($lang_id);
        $select = array_merge($fields, $this->_fields);
        $this->DB->select(implode(", ", $select));
        $this->DB->from(TABLE_EVENTS);
        foreach ($data as $field => $value) {
            $this->DB->where($field, $value);
        }
        $result = $this->DB->get()->result_array();

        return empty($result) ? false : $this->formatImage($result[0]);
    }

    /**
     *  List events
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
    public function getListEvents($page = null, $limits = null, $order_by = null, $params = array(), $lang_id = null, $format_users = false)
    {
        $fields = $this->_fields_all($lang_id);
        $select = array_merge($fields, $this->_fields);

        if (isset($params["fields"]) && is_array($params["fields"]) && count($params["fields"])) {
            $this->setAdditionalFields($params["fields"]);
        }

        $this->DB->select(implode(", ", $select));
        $this->DB->from(TABLE_EVENTS);

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
                $this->DB->order_by($field . ' ' . $dir);
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
            return $this->formatEvents($results, $format_users);
        }

        return array();
    }

    /**
     *  List events
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
    public function getListParticipants($page = null, $limits = null, $order_by = null, $params = array(), $lang_id = null, $user_data = array())
    {
        $select = $this->_fields_participants;

        $this->DB->select(implode(", ", $select));
        $this->DB->from(TABLE_EVENTS_USERS);

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
                if (in_array($field, $this->_fields_participants)) {
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
            return $this->formatParticipants($results, $user_data);
        }

        return array();
    }

    public function getListParticipantsIds($event_id)
    {
        $return = array();
        $this->DB->select('fk_user_id');
        $this->DB->where('fk_event_id', $event_id);
        $this->DB->from(TABLE_EVENTS_USERS);
        $results = $this->DB->get()->result_array();
        foreach ($results as $res) {
            $return[] = $res['fk_user_id'];
        }

        return $return;
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
        $fields = $this->_fields_all($lang_id);
        $select = array_merge($fields, $this->_fields);

        $this->DB->select(implode(", ", $select));
        $this->DB->from(TABLE_EVENTS);

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

    public function get_minimum_search_data()
    {
        $data = array();

        return $data;
    }

    /**
     *  Count of events in the table
     *
     *  @param array $params
     *
     *  @return void
     */
    public function getEventsCount($params = array())
    {
        $this->DB->select('COUNT(id) AS cnt');
        $this->DB->from(TABLE_EVENTS);

        if (isset($params["fields"]) && is_array($params["fields"]) && count($params["fields"])) {
            $this->setAdditionalFields($params["fields"]);
        }

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
     *  Count of images in the table
     *
     *  @param array $params
     *
     *  @return void
     */
    public function getParticipantsCount($params = array())
    {
        $this->DB->select('COUNT(id) AS cnt');
        $this->DB->from(TABLE_EVENTS_USERS);

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
    
    public function getParticipant($id) {
        $this->DB->select(implode(", ", $this->_fields_participants));
        $this->DB->where('id', $id);
        $this->DB->from(TABLE_EVENTS_USERS);
        
        return $this->DB->get()->row_array();
    }

    public function getParticipantByID($event_id, $user_id)
    {
        $this->DB->select('id, status');
        $this->DB->where('fk_event_id', $event_id);
        $this->DB->where('fk_user_id', $user_id);
        $this->DB->from(TABLE_EVENTS_USERS);

        $result = $this->DB->get()->row_array();

        return $result;
    }

    /**
     *  User events
     *
     *  @param integer $user_id
     *
     *  @return array
     */
    public function getEventsUser($page = null, $limits = null, $order_by = null, $params = array(), $lang_id = null, $user_data = array())
    {
        $fields = $this->_fields_users_all($lang_id);

        $this->DB->select(implode(", ", $this->_fields_participants));
        $this->DB->from(TABLE_EVENTS_USERS);

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

        return empty($result) ? false : $this->_formatEventsUser($result);
    }

    public function getEventsIdsByUser($where)
    {
        $events_ids = array();

        $this->DB->select('fk_event_id');
        $this->DB->where($where);
        $this->DB->from(TABLE_EVENTS_USERS);
        $results = $this->DB->get()->result_array();
        if (!empty($results)) {
            foreach ($results as $row) {
                $events_ids[] = $row['fk_event_id'];
            }
        }

        return $events_ids;
    }

    public function getEventsUserCount($params = array())
    {
        $this->DB->select('COUNT(id) AS cnt');
        $this->DB->from(TABLE_EVENTS_USERS);

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

    public function getApprovedUsers($event_id, $params = array())
    {
        $params['where']['fk_event_id'] = $event_id;
        $params['where']['status'] = 'approved';

        return $this->getListParticipants(null, null, null, $params);
    }

    public function getApprovedUsersCount($event_id, $params = array())
    {
        $params['where']['fk_event_id'] = $event_id;
        $params['where']['status'] = 'approved';

        return $this->getParticipantsCount($params);
    }

    /**
     *  Validate uses event
     *
     *  @param integer $profile_id
     *  @param array $data
     *
     *  @return void
     */
    public function validateEventUser($profile_id = null, $data = array())
    {
        $return = array("errors" => array(), "data" => array());

        if (!is_null($profile_id)) {
            $return["data"]["id_profile"] = intval($profile_id);
            $return["data"]["id_user"] = $this->CI->session->userdata('user_id');
            if (empty($return["data"]["id_profile"])) {
                $return["errors"]["id_profile"] = l('error_system', 'events');
            }
        }

        $lang_ids = $this->CI->pg_language->languages;
        foreach ($lang_ids as $lang) {
            $return["data"]['name_' . $lang['id']] = $data['view_name_' . $lang['id']];
        }

        if (isset($data['img'])) {
            $return["data"]["img"] = $data['id'] . '_' . $data['img'];
            if (empty($return["data"]["img"])) {
                $return["errors"]["id_profile"] = l('error_system', 'events');
            }
        }

        return $return;
    }

    /**
     *  Validation Event
     *
     *  @param array $data
     *
     *  @return array
     */
    public function validateEvent($data = array())
    {
        $return = array("errors" => array(), "data" => array());

        $this->CI->load->model('Moderation_model');
        $current_lang_id = $this->CI->pg_language->current_lang_id;
        foreach ($this->CI->pg_language->languages as $key => $value) {
            if (!empty($data['name_' . $value['id']])) {
                $return["data"]['name_' . $value['id']] = trim(strip_tags($data['name_' . $value['id']]));
            } else {
                $return["data"]['name_' . $value['id']] = trim(strip_tags($data['name_' . $current_lang_id]));
            }
            $bw_count = $this->CI->Moderation_badwords_model->check_badwords($this->moderation_type[0], $return["data"]['name_' . $value['id']]);
            if ($bw_count) {
                $return['errors']['name'] = l('error_badwords_name', 'events');
            }

//            if (!empty($data['annotation_' . $value['id']])) {
//                $return["data"]['annotation_' . $value['id']] = trim(strip_tags($data['annotation_' . $value['id']]));
//            } else {
//                $return["data"]['annotation_' . $value['id']] = trim(strip_tags($data['annotation_' . $current_lang_id]));
//            }
//            $bw_count = $this->CI->Moderation_badwords_model->check_badwords($this->moderation_type[0], $return["data"]['annotation_' . $value['id']]);
//            if ($bw_count) {
//                $return['errors']['annotation'] = l('error_badwords_annotation', 'events');
//            }

            if (!empty($data['description_' . $value['id']])) {
                $return["data"]['description_' . $value['id']] = trim(strip_tags($data['description_' . $value['id']]));
            } else {
                $return["data"]['description_' . $value['id']] = trim(strip_tags($data['description_' . $current_lang_id]));
            }
            $bw_count = $this->CI->Moderation_badwords_model->check_badwords($this->moderation_type[0], $return["data"]['description_' . $value['id']]);
            if ($bw_count) {
                $return['errors']['description'] = l('error_badwords_description', 'events');
            }
        }

        if (empty($return["data"]['name_' . $current_lang_id])) {
            $return["errors"]['name'] = l('error_name_incorrect', 'events');
        }

//        if (empty($return["data"]['annotation_' . $current_lang_id])) {
//            $return["errors"]['annotation'] = l('error_annotation_incorrect', 'events');
//        }

        if (empty($return["data"]['description_' . $current_lang_id])) {
            $return["errors"]['description'] = l('error_description_incorrect', 'events');
        }
        /*
          if (!empty($data['fk_user_id'])) {
          $return["data"]["fk_user_id"] = intval($data["fk_user_id"]);
          } else {
          $return["errors"]['fk_user_id'] = l('error_fk_user_id_incorrect', 'events');
          } */

        if (!empty($data['category'])) {
            $return["data"]["category"] = intval($data["category"]);
        } else {
            $return["errors"]['category'] = l('error_category_incorrect', 'events');
        }

        if (!empty($data['country_code'])) {
            $return["data"]["country_code"] = $data["country_code"];
        } else {
            $return["errors"]['country_code'] = l('error_country_code_incorrect', 'events');
        }
        if (!empty($data['fk_region_id'])) {
            $return["data"]["fk_region_id"] = intval($data["fk_region_id"]);
        } else {
            $return["errors"]['fk_region_id'] = l('error_fk_region_id_incorrect', 'events');
        }
        if (!empty($data['fk_city_id'])) {
            $return["data"]["fk_city_id"] = intval($data["fk_city_id"]);
        } else {
            $return["errors"]['fk_city_id'] = l('error_fk_city_id_incorrect', 'events');
        }

        if (!empty($data['address'])) {
            $return["data"]['address'] = trim(strip_tags($data['address']));
            $bw_count = $this->CI->Moderation_badwords_model->check_badwords($this->moderation_type[0], $return["data"]['address']);
            if ($bw_count) {
                $return['errors']['address'] = l('error_badwords_address', 'events');
            }
        } else {
            $return["errors"]['address'] = l('error_address_incorrect', 'events');
        }

        if (!empty($data['venue'])) {
            $return["data"]['venue'] = trim(strip_tags($data['venue']));
            $bw_count = $this->CI->Moderation_badwords_model->check_badwords($this->moderation_type[0], $return["data"]['venue']);
            if ($bw_count) {
                $return['errors']['venue'] = l('error_badwords_venue', 'events');
            }
        } else {
            $return["errors"]['venue'] = l('error_venue_incorrect', 'events');
        }

        //start datetime
        if(!empty($data['date_started'])) {
            $return["data"]["date_started"] =  trim(strip_tags($data['date_started']));
        } elseif(!empty($data["alt_date_started"])) {
            $return["data"]["date_started"] =  trim(strip_tags($data['alt_date_started']));
        } else {
            $return["data"]["date_started"] = self::DB_DEFAULT_DATE;
            $return["errors"]['date_started'] = l('error_date_started_incorrect', 'events');            
        }

        if (!empty($data['time_started'])) {
            $return["additional_data"]["time_started"] = trim(strip_tags($data['time_started']));
        } else {
            $return["additional_data"]["time_started"] = self::DB_DEFAULT_TIME;
        }

        $return["data"]['date_started'] = $return['data']['date_started'] . ' ' . $return['additional_data']['time_started'];

        //end datetime
        if(!empty($data['date_ended'])) {
            $return["data"]["date_ended"] = trim(strip_tags($data["date_ended"]));
        } elseif(!empty($data["alt_date_ended"])) {
            $return["data"]["date_ended"] =  trim(strip_tags($data['alt_date_ended']));
        } else {
            $return["data"]["date_ended"] = self::DB_DEFAULT_DATE;
            $return["errors"]['date_ended'] = l('error_date_ended_incorrect', 'events');            
        }

        if (!empty($data['time_ended'])) {
            $return["additional_data"]["time_ended"] = trim(strip_tags($data['time_ended']));
        } else {
            $return["additional_data"]["time_ended"] = self::DB_DEFAULT_TIME;
        }

        $return['data']['date_ended'] = $return['data']['date_ended'] . ' ' . $return['additional_data']['time_ended'];

        //deadline datetime
        if(!empty($data['deadline_date'])) {
            $return["data"]["deadline_date"] =  trim(strip_tags($data['deadline_date']));
        } elseif(!empty($data["alt_deadline_date"])) {
            $return["data"]["deadline_date"] = trim(strip_tags($data["alt_deadline_date"]));
        } else {
            $return["data"]['deadline_date'] = $return["data"]["date_started"];
        }

        if (!empty($data['deadline_time'])) {
            $return["additional_data"]["deadline_time"] = trim(strip_tags($data['deadline_time']));
        } else {
            $return["additional_data"]["deadline_time"] = $return["additional_data"]["time_started"];
        }

        $return['data']['deadline_date'] = $return['data']['deadline_date'] . ' ' . $return['additional_data']['deadline_time'];


        if (!empty($data['max_participants']) && $data['max_participants'] > 0) {
            $return["data"]["max_participants"] = intval($data["max_participants"]);
        } else {
            $return["data"]["max_participants"] = 0;
        }

        if (isset($_FILES[$this->upload_gid]) && is_array($_FILES[$this->upload_gid]) && is_uploaded_file($_FILES[$this->upload_gid]["tmp_name"])) {
            $this->CI->load->model("Uploads_model");
            $img_return = $this->CI->Uploads_model->validate_upload($this->upload_gid, $this->upload_gid);
            if (!empty($img_return["error"])) {
                $return["errors"][] = implode("<br>", $img_return["error"]);
            }
        }

        $return['data']['search_data'] = $this->formatFulltextField($return['data']);
        $return['data']['event_settings'] = serialize($data['event_settings']);

        return $return;
    }

    public function validateNewParticipants($event_id = null, $users_ids = array())
    {
        $return = array("errors" => array(), "data" => array());

        if (!empty($users_ids)) {
            $event = $this->getEventById($event_id);
            $users_count = count($users_ids);

            $params = array(
                'status'      => 'approved',
                'fk_event_id' => $event_id,
            );

            $participants_count = $this->getParticipantsCount($params);

            if ($participants_count <= ($event['max_participants'] - $users_count)) {
                $return['errors']['max_participants_limit'] = l('error_max_participants_limit', 'events');
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

        return $return;
    }

    /**
     *  Validation Image
     *
     *  @param array $data
     *
     *  @return array
     */
    public function validateImage($id = null, $data = array())
    {
        $return = array("errors" => array(), "data" => array());

        if (isset($_FILES[$this->upload_gid]) && is_array($_FILES[$this->upload_gid]) && is_uploaded_file($_FILES[$this->upload_gid]["tmp_name"])) {
            $this->CI->load->model("Uploads_model");
            $img_return = $this->CI->Uploads_model->validate_upload($this->upload_gid, $this->upload_gid);
            if (!empty($img_return["error"])) {
                $return["errors"][] = implode("<br>", $img_return["error"]);
            }
        }

        if (is_null($id) && !is_uploaded_file($_FILES[$this->upload_gid]["tmp_name"])) {
            $return["errors"][] = l('error_upload_file', 'events');
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
            $return["errors"][] = l('error_no_event', 'events');
        }

        if (isset($data['answer'])) {
            $return["data"]['answer'] = strip_tags($data['answer']);
            if (empty($return["data"]['answer'])) {
                $return["errors"][] = l('error_system', 'events');
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
    private function _formatSettings($data = array())
    {
        $data['is_active'] = intval($data['is_active']);

        return $data;
    }

    /**
     *  Formatting Event
     *
     *  @param array $data
     *
     *  @return array
     */
    public function formatEvent($data = array())
    {
        $lang_id = $this->CI->pg_language->current_lang_id;
        $user_id = $this->CI->session->userdata('user_id');

        $this->CI->load->model("Uploads_model");
        $data['image'] = $this->CI->Uploads_model->format_upload($this->upload_gid, $data['id'], $data['img']);

        if (!empty($data["image"])) {
            $data["image"] = $this->CI->Uploads_model->format_upload($this->upload_gid, $data['id'], $data['img']);
        } else {
            $data["image"] = $this->CI->Uploads_model->format_default_upload($this->upload_gid);
        }

        $event_settings = isset($data["event_settings"]) ? (array) unserialize($data["event_settings"]) : array();
//        unset($data["event_settings"]);
//        foreach ($event_settings as $name => $value) {
//            $data[$name] = $value;
//        }
        $data['settings'] = $event_settings;

        $date_started = strtotime($data['date_started']);
        $data['date_started'] = date(self::DATE_FORMAT, $date_started);
        $data['time_started'] = date(self::TIME_FORMAT, $date_started);

        $date_ended = strtotime($data['date_ended']);
        $data['date_ended'] = date(self::DATE_FORMAT, $date_ended);
        $data['time_ended'] = date(self::TIME_FORMAT, $date_ended);

        $date_deadline = strtotime($data['deadline_date']);
        $data['deadline_date'] = date(self::DATE_FORMAT, $date_deadline);
        $data['deadline_time'] = date(self::TIME_FORMAT, $date_deadline);

        $event_for_location[$data["id"]] = array(
            'country' => $data["country_code"],
            'region'  => $data["fk_region_id"],
            'city'    => $data["fk_city_id"],
        );

        $data['is_owner'] = $this->isOwnerEvent($user_id, $data);
        $data['name'] = isset($data['name']) ? $data['name'] : $data['name_' . $lang_id];
        $data['description'] = isset($data['description']) ? $data['description'] : $data['description_' . $lang_id];
        $data['admin_link'] = site_url() . 'admin/events/edit_main/' . $data['id'];

        if (!empty($event_for_location)) {
            $this->CI->load->helper('countries');
            $event_locations = cities_output_format($event_for_location, $lang_id);
            $events_locations_data = get_location_data($event_for_location, 'city');
            $data['location'] = (isset($event_locations[$data["id"]])) ? $event_locations[$data["id"]] : '';
            $data['country'] = (isset($events_locations_data['country'][$data['country_code']])) ? $events_locations_data['country'][$data['country_code']]['name'] : '';
            $data['region'] = (isset($events_locations_data['region'][$data['fk_region_id']])) ? $events_locations_data['region'][$data['fk_region_id']]['name'] : '';
            $data['region-code'] = (isset($events_locations_data['region'][$data['fk_region_id']])) ? $events_locations_data['region'][$data['fk_region_id']]['code'] : '';
            $data['city'] = (isset($events_locations_data['city'][$data['fk_city_id']])) ? $events_locations_data['city'][$data['fk_city_id']]['name'] : '';
        }

        return $data;
    }
    
    public function isOwnerEvent($user_id = null, $event_data = array()) {
        $auth_type = $this->CI->session->userdata("auth_type");
        if(!$user_id) {
            $user_id = $this->CI->session->userdata('user_id');
        }
        
        switch($auth_type) {
            case 'user':
               if($user_id == $event_data['fk_user_id'] && !$event_data['is_admin']) {
                   return true;
               }
            break;
            case 'admin':
               if($user_id == $event_data['fk_user_id'] && $event_data['is_admin']) {
                   return true;
               }
        }
        
        return false;
    }

    /**
     *  Formatting Image
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
     *  Formatting Events
     *
     *  @param array $data
     *
     *  @return array
     */
    public function formatEvents($data = array(), $format_users = false)
    {
        
        foreach ($data as $key => $value) {
            $data[$key] = $this->formatEvent($value);
            if ($format_users) {
                $data[$key]['user'] = $this->getEventUser($value['fk_user_id'], $value['is_admin']);
            }
        }

        return $data;
    }

    /**
     *  Formatting Participants
     *
     *  @param array $data
     *
     *  @return array
     */
    public function formatParticipants($data = array(), $user_data = array())
    {
        $this->CI->load->model("Users_model");
        $lang_id = $this->CI->pg_language->current_lang_id;
        foreach ($data as $key => $value) {
            $data[$key] = $value;
            $data[$key]['user'] = $this->CI->Users_model->get_user_by_id($value['fk_user_id'], true);
        }

        return $data;
    }

    /**
     *  Replace text event
     *
     *  @param string $data
     *
     *  @return string
     */
    public function formatTextEvent($data = '', $user_data = array())
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
     *  Format user events
     *
     *  @param array $data
     *
     *  @return array
     */
    private function _formatEventsUser($data = array())
    {
        $events_ids = array();
        foreach ($data as $event) {
            $events_ids[] = $event['fk_event_id'];
        }

        $params = array(
            'where_in' => array(
                'id' => $events_ids,
            ),
        );

        $events = $this->getListEvents(null, null, null, $params);

        return $events;
    }

    /**
     *  Add/Create Event
     *
     *  @param integer $id
     *  @param array $attrs
     *
     *  @return integer
     */
    public function saveEvent($id = null, $attrs = array(), $moderation = false)
    {
        if (!$id) {
            $attrs["date_created"] = date(self::DB_DATE_FORMAT);
            $this->DB->insert(TABLE_EVENTS, $attrs);
            $id = $this->DB->insert_id();

            //wall event
            if ($attrs['status'] && !($this->CI->session->userdata("auth_type") == 'admin')) {
                $this->createWallEvent($this->wall_events['event_add']['gid'], $id);
            }
        } else {
            $this->DB->where('id', $id);
            $this->DB->update(TABLE_EVENTS, $attrs);
        }

        return $id;
    }

    public function saveAllParticipants($event_id, $userdata = array(), $user_settings = array())
    {
        $default_settings = array(
            'is_invite'   => 0,
            'status'      => 'pending',
            'fk_event_id' => $event_id,
        );

        $settings = array_merge($default_settings, $user_settings);

        $save_data = array();
        foreach ($userdata as $key => $participant) {
            $save_data[$key] = $settings;
            $save_data[$key]['fk_user_id'] = $participant;
        }

        $this->DB->insert_batch(TABLE_EVENTS_USERS, $save_data);
    }

    /**
     *  Add/Create Participant
     *
     *  @param integer $id
     *  @param array $data
     *
     *  @return integer
     */
    public function saveParticipant($event_id, $user_id, $data = array())
    {
        $participant = $this->getParticipantByID($event_id, $user_id);
        if (empty($participant)) {
            $attrs = array(
                'fk_event_id'   => intval($event_id),
                'fk_user_id'    => intval($user_id),
                'is_invite'     => isset($data['is_invite']) ? $data['is_invite'] : 0,
                'status'        => isset($data['status']) ? $data['status'] : 'pending',
                'response_date' => date(self::DB_DATE_FORMAT),
            );

            $this->DB->insert(TABLE_EVENTS_USERS, $attrs);
            $this->DB->insert_id();
        }

        return;
    }

    public function saveParticipantStatus($event_id, $user_id, $data = array())
    {
        $this->DB->where('fk_event_id', $event_id);
        $this->DB->where('fk_user_id', $user_id);
        $this->DB->update(TABLE_EVENTS_USERS, $data);
    }

    public function saveParticipants($event_id, $users_ids = array(), $status = 'pending', $is_invite = 1)
    {
        $return = array("errors" => array(), "data" => array());

        $settings = array(
            'is_invite'     => $is_invite,
            'status'        => $status,
            'response_date' => date(self::DB_DATE_FORMAT),
            'fk_event_id'   => $event_id,
        );

        $save_data = array();
        foreach ($users_ids as $key => $participant) {
            $save_data[$key] = $settings;
            $save_data[$key]['fk_user_id'] = $participant;
        }

        $this->DB->insert_batch(TABLE_EVENTS_USERS, $save_data);
    }

    /**
     *  Add/Create Participant
     *
     *  @param integer $id
     *  @param array $attrs
     *
     *  @return integer
     */
    public function existParticipant($attrs = array())
    {
        $this->DB->select('fk_user_id');
        $this->DB->where_in('fk_user_id', $attrs['fk_user_id']);
        $this->DB->where('fk_event_id', $attrs['fk_event_id']);
        $this->DB->from(TABLE_EVENTS_USERS);

        $result = $this->DB->get()->result_array();

        return $result;
    }

    /**
     *  Add/Create Participant
     *
     *  @param integer $id
     *  @param array $attrs
     *
     *  @return integer
     */
    public function saveParticipantData($id, $attrs = array())
    {
        if ($id) {
            $this->DB->where('id', $id);
            $this->DB->update(TABLE_EVENTS_USERS, $attrs);

            return true;
        } else {
            return false;
        }
    }

    public function getMedia($album_id)
    {
        $result = array();

        if ($album_id != 0) {
            $this->CI->load->model('media/models/Media_model');
            $this->CI->load->model('media/models/Media_album_model');
            $this->CI->Media_model->initialize($this->CI->Events_model->album_type);

            $media_ids = $this->CI->Media_album_model->get_media_ids_in_album($album_id);
            if (!empty($media_ids)) {
                $result = $this->CI->Media_model->get_media(''/* $exists_page */, ''/* $items_on_page */, ''/* $order_by */, ''/* $where */, $media_ids);
            }
        }

        return $result;
    }

    public function saveMedia($data, $type = 'photo')
    {
        $this->CI->load->model('media/models/Media_model');
        $this->CI->load->model('media/models/Media_album_model');
        $this->CI->load->model('media/models/Album_types_model');
        $this->CI->Media_model->initialize($this->album_type);

        $album_type = $this->CI->Album_types_model->get_album_type_by_gid($this->album_type);
        $user_owner = $data['fk_user_id'];
        if($data['is_admin']) {
            $user_owner = 0;
        }
        $file_data = array(
            'type_id'     => $album_type['id'],
            'id_user'     => $user_owner,
            'id_owner'    => $user_owner,
            'id_parent'   => '0',
            'permissions' => '4',
            'status'      => '1',
        );

        switch ($type) {
            case 'photo':
                $file_data['upload_gid'] = $this->CI->Media_model->file_config_gid;
                $result = $this->CI->Media_model->save_image(null, $file_data, 'multiUpload', false, false);
                break;
            case 'video':
                $file_data['upload_gid'] = $this->CI->Media_model->video_config_gid;
                $result = $this->CI->Media_model->save_video(null, $file_data, 'multiUpload', false, false);
        }

        if(empty($result['errors'])) {
            $add_status = $this->CI->Media_album_model->add_media_in_album($result['id'], $data['album_id']);
        }
        
        return $result;
    }

    public function saveEventLogo($event_id)
    {
        $img_return = array('file' => '', 'errors' => '');
        
        $delete_icon = $this->input->post('event_icon_delete', true);

        if (!empty($event_id) && ($delete_icon || (isset($_FILES[$this->upload_gid]['tmp_name']) && !empty($_FILES[$this->upload_gid]['tmp_name'])))) {
            $this->CI->load->model('Uploads_model');
            $params = array('id' => $event_id);
            $upload_data = $this->getImage($params);
            
            if (!empty($upload_data['img']) || $delete_icon) {
                $this->CI->Uploads_model->delete_upload($this->upload_gid, $event_id . "/", $upload_data['img']);
            }
            if (isset($_FILES[$this->upload_gid]['tmp_name']) && is_uploaded_file($_FILES[$this->upload_gid]['tmp_name'])) {
                $img_return = $this->CI->Uploads_model->upload($this->upload_gid, $event_id, $this->upload_gid);
            }

            if (!empty($img_return) && empty($img_return['errors'])) {
                $data = array('img' => $img_return["file"]);
                $this->DB->where('id', $event_id);
                $this->DB->update(TABLE_EVENTS, $data);
            }
        }

        return $img_return;
    }
    
    public function deleteEventLogo($event_id = null)
    {
        if (!is_null($event_id)) {
            $upload_data = $this->getImage(['id' => $event_id]);
            if (!empty($upload_data['img'])) {
                $this->CI->load->model('Uploads_model');
                $this->CI->Uploads_model->delete_upload($this->upload_gid, $event_id . "/", $upload_data['img']);
                $this->DB->where('id', $event_id);
                $this->DB->update(TABLE_EVENTS, ['img' => '']);
            }
            return true;
        }
        return false;
    }

    /**
     *  Add user event
     *
     *  @param array $attrs
     *
     *  @return array
     */
    public function saveEventUser($id = null, $attrs = array())
    {
        if (is_null($id)) {
            $attrs["date_created"] = date(self::DB_DATE_FORMAT);
            $this->DB->insert(TABLE_EVENTS_USERS, $attrs);
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

                $this->CI->load->model('menu/models/Indicators_model');
                $this->CI->Indicators_model->add('new_event_item', $id, $attrs['id_profile']);

                $result['success'] = l('success_sent', 'events');
            } else {
                $result['errors'] = l('error_system', 'events');
            }
        } else {
            $this->DB->where('id', $id);
            $this->DB->update(TABLE_EVENTS_USERS, $attrs);

            $this->CI->load->model('menu/models/Indicators_model');
            $this->CI->Indicators_model->delete('new_event_item', array($id), false);

            $result['success'] = l('success_sent', 'events');
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
            $option = $this->CI->pg_language->ds->get_reference('events', $this->_ds_gid, $lid);
            $option["option"]["text"] = $string;
            $this->CI->pg_language->ds->set_module_reference('events', $this->_ds_gid, $option, $lid);
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
            $this->CI->pg_module->set_module_config('events', $setting, $value);
        }
    }

    /**
     *  Delete Event
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

                $this->load->model('media/models/Media_model');
                $this->load->model('media/models/Media_album_model');
                $this->load->model('media/models/Albums_model');
                $this->CI->Media_model->initialize('events_type');

                $params2 = array();
                $params2['id'] = intval($id);
                $event = $this->getEvent($params2);
                $album_id = $event['album_id'];
                $media_ids = $this->CI->Media_album_model->get_media_ids_in_album($album_id);
                if ($media_ids) {
                    foreach ($media_ids as $id_image) {
                        $this->CI->Media_model->delete_media($id_image);
                    }
                }
                $this->CI->Albums_model->delete_album($album_id);
            }
            $this->DB->where('id', $id);
            $this->DB->delete(TABLE_EVENTS);

            $this->DB->where('fk_event_id', $id);
            $this->DB->delete(TABLE_EVENTS_USERS);
        }
    }

    /**
     *  Delete Participant
     *
     *  @param integer $id
     *
     *  @return void
     */
    public function deleteParticipant($id = null)
    {
        if (!is_null($id)) {
            $params = array('id' => $id);
            $this->DB->where('id', $id);
            $this->DB->delete(TABLE_EVENTS_USERS);
        }
    }
    
    /**
     *  Delete Participant by User id
     *
     *  @param integer $id
     *
     *  @return void
     */
    public function deleteParticipantByUser($event_id, $user_id)
    {
        $this->DB->where('fk_event_id', $event_id);
        $this->DB->where('fk_user_id', $user_id);
        $this->DB->delete(TABLE_EVENTS_USERS);
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
            "name"      => l('header_main_sections', 'events'),
            "link"      => rewrite_link('events', 'index'),
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
        $return[] = array("link" => "events/search", "name" => l('header_main', 'events'));

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
        $this->CI->dbforge->add_column(TABLE_EVENTS, $fields_n);

        $default_lang_id = $this->CI->pg_language->get_default_lang_id();
        if ($lang_id != $default_lang_id) {
            $this->CI->db->set('name_' . $lang_id, 'name_' . $default_lang_id, false);
            $this->CI->db->update(TABLE_EVENTS);
        }

//        $fields_a = array();
//        $fields_a['annotation_' . $lang_id] = array('type' => 'TEXT', 'null' => FALSE);
//        $this->CI->dbforge->add_column(TABLE_EVENTS, $fields_a);
//
//        if ($lang_id != $default_lang_id) {
//            $this->CI->db->set('annotation_' . $lang_id, 'annotation_' . $default_lang_id, false);
//            $this->CI->db->update(TABLE_EVENTS);
//        }

        $fields_d = array();
        $fields_d['description_' . $lang_id] = array('type' => 'TEXT', 'null' => false);
        $this->CI->dbforge->add_column(TABLE_EVENTS, $fields_d);

        if ($lang_id != $default_lang_id) {
            $this->CI->db->set('description_' . $lang_id, 'description_' . $default_lang_id, false);
            $this->CI->db->update(TABLE_EVENTS);
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

        $table_query = $this->CI->db->get(TABLE_EVENTS);
        $fields_exists = $table_query->list_fields();

        $fields = array('name_' . $lang_id, 'view_name_' . $lang_id);
        foreach ($fields as $field_name) {
            if (!in_array($field_name, $fields_exists)) {
                continue;
            }
            $this->CI->dbforge->drop_column(TABLE_EVENTS, $field_name);
        }

        $table_query_users = $this->CI->db->get(TABLE_EVENTS_USERS);
        $fields_exists_users = $table_query_users->list_fields();

        $fields_users = array('name_' . $lang_id);
        foreach ($fields_users as $field_user_name) {
            if (!in_array($field_user_name, $fields_exists_users)) {
                continue;
            }
            $this->CI->dbforge->drop_column(TABLE_EVENTS_USERS, $field_user_name);
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
            'name'   => l('link_add', 'events'),
            'helper' => 'button',
        );

        return $action;
    }

    // seo
    public function get_seo_settings($method = '', $lang_id = '')
    {
        if (!empty($method)) {
            return $this->getSeoSettings($method, $lang_id);
        } else {
            $actions = array('account', 'account_delete', 'settings');
            $return = array();
            foreach ($actions as $action) {
                $return[$action] = $this->getSeoSettings($action, $lang_id);
            }

            return $return;
        }
    }

    public function request_seo_rewrite($var_name_from, $var_name_to, $value)
    {
        show_404();
    }

    public function get_sitemap_xml_urls()
    {
        $this->CI->load->helper('seo');

        return $return;
    }

    public function get_sitemap_urls()
    {
        $block = array();

        return $block;
    }

    private function formatFulltextField($data = array())
    {
        foreach ($this->CI->pg_language->languages as $key => $value) {
            $fields[] = $data['name_' . $value['id']];
            //$fields[] = $data['annotation_' . $value['id']];
            $fields[] = $data['description_' . $value['id']];
        }

        $result = implode(",", $fields);

        return $result;
    }

    public function getProductsFulltextSearch($data)
    {
        $criteria = array();
        if (!empty($data["search"])) {
            $data["search"] = trim(strip_tags($data["search"]));
            $temp_criteria = $this->returnFulltext($data["search"], 'BOOLEAN MODE');
            $criteria['fields'][] = $temp_criteria['field'];
            $criteria['where_sql'][] = $temp_criteria['where_sql'];
        }

        return $criteria;
    }

    public function returnFulltext($text, $mode = null)
    {
        $word_count = str_word_count($text);
        $arr_text = explode(" ", $text);
        $word_count    = count($arr_text);
        $text = ($word_count < 2 ? $text . "*" : $text);
        $escape_text = $this->DB->escape($text);
        $mode = ($mode && $word_count < 2 ? " IN " . $mode : "");
        $return = array(
            'field'     => "MATCH (search_data) AGAINST (" . $escape_text . ") AS fields",
            'where_sql' => "MATCH (search_data) AGAINST (" . $escape_text . $mode . ")",
        );

        return $return;
    }

    public function setAdditionalFields($fields)
    {
        $this->_fields = (!empty($fields)) ? array_merge($this->_fields, $fields) : $this->_fields;

        return;
    }

    public function getSearchCriteria($data)
    {
        $criteria = array();

        $criteria['where']['status'] = 1;
        $criteria['where']['is_active'] = 1;

        //search_text, category, country_code, fk_region_id, fk_city_id, date_started, date_ended

        if (isset($data['search']) && !empty($data['search'])) {
            $criteria = $this->getProductsFulltextSearch($data);
        }

        if (isset($data['category']) && !empty($data['category'])) {
            $criteria['where']['category'] = intval($data['category']);
        }

        if (!empty($data['date_started_from'])) {
            $criteria['where_sql'][] = "deadline_date >= '" . $data['date_started_from'] . "'";
        }

        if (!empty($data['date_started_to'])) {
           // $criteria['where_sql']['date_ended'] = "date_ended <= '" . $data['date_started_to'] . "'";
            $criteria['where_sql'][] = "deadline_date <= '" . $data['date_started_to'] . "'";
        }

        if (!empty($data["id_country"])) {
            $data["id_country"] = $data["id_country"];
            $criteria["where"]["country_code"] = $data["id_country"];
        }
        if (!empty($data["id_region"])) {
            $data["id_region"] = intval($data["id_region"]);
            $criteria["where"]["fk_region_id"] = $data["id_region"];
        }
        if (!empty($data["id_city"])) {
            $data["id_city"] = intval($data["id_city"]);
            $criteria["where"]["fk_city_id"] = $data["id_city"];
        }

        return $criteria;
    }

    public function getAdvancedCriteria($search_type, $data)
    {
        $criteria = array('empty' => false);
        $user_id = $this->CI->session->userdata('user_id');

        switch ($search_type) {
            case 'upcoming':
                if (empty($data['date_started_from'])) {
                    $criteria['where_sql'][] = 'deadline_date >= NOW()';
                }
                break;
            case 'archive':
                if (empty($data['date_started_to'])) {
                    $criteria['where_sql'][] = 'date_ended < NOW()';
                }
                break;
            case 'my_events':
                $criteria['where']['fk_user_id'] = $user_id;
                $criteria['where']['is_admin'] = 0;
                break;
            case 'will_go':
                $user_events_params = array(
                    'fk_user_id' => $user_id,
                    'status'     => 'approved',
                );

                $event_ids = $this->getEventsIdsByUser($user_events_params);
                if (!empty($event_ids)) {
                    $criteria['where_sql']['id'] = 'id IN (' . implode(", ", $event_ids) . ')';

                    if (empty($data['date_started_to'])) {
                        $criteria['where_sql']['date_ended'] = 'date_ended > NOW()';
                    }
                } else {
                    $criteria['empty'] = true;
                }

                break;
            case 'requests':
                $user_events_params = array(
                    'fk_user_id' => $user_id,
                    'status'     => 'pending',
                    'is_invite'  => 1,
                );

                $event_ids = $this->getEventsIdsByUser($user_events_params);

                if (!empty($event_ids)) {
                    $criteria['where_sql']['id'] = 'id IN (' . implode(", ", $event_ids) . ')';

                    if (empty($data['date_started_to'])) {
                        $criteria['where_sql']['date_ended'] = 'date_ended > NOW()';
                    }
                } else {
                    $criteria['empty'] = true;
                }
                break;

        }

        return $criteria;
    }

    public function sendNotificationJoin($user_from, $event_id) {
        $lang_id = $this->CI->pg_language->current_lang_id;
        $event = $this->getEventById($event_id, $lang_id);

        $this->CI->load->model("Users_model");
        $user_data = $this->CI->Users_model->get_user_by_id($user_from, true);

        $creator_data = $this->getEventUser($event['fk_user_id'], $event['is_admin']);

        $this->CI->load->model('Notifications_model');
        $data = array(
            'creator_nickname' => $creator_data['output_name'],
            'user_nickname' => $user_data['output_name'],
            'event_link' => site_url() . 'event/view/' . $event['id'],
            'event_name' => $event['name']
        );
        
        $this->CI->Notifications_model->send_notification($creator_data['email'], 'event_user_entered', $data);
    }

    public function sendNotificationExclude($user_to, $event_id) {
        $lang_id = $this->CI->pg_language->current_lang_id;
        $event = $this->getEventById($event_id, $lang_id);

        $this->CI->load->model("Users_model");
        $user_data = $this->CI->Users_model->get_user_by_id($user_to, true);

        $this->CI->load->model('Notifications_model');
        $data = array(
            'user_nickname' => $user_data['output_name'],
            'event_link' => site_url() . 'event/view/' . $event['id'],
            'event_name' => $event['name']
        );

        $this->CI->Notifications_model->send_notification($user_data['email'], 'event_user_excluded', $data);
    }

    /**
     * Update comments count
     *
     * Callback method of comments module
     *
     * @param integer $count comments count
     * @param integer $id    object identifier
     *
     * @return void
     */
    public function comments_count_callback($count, $id = 0)
    {
        if ($id) {
            $this->DB->where('id', $id);
        }
        $data['comments_count'] = $count;
        $this->DB->update(TABLE_EVENTS, $data);
    }

    /**
     * Generate comment text
     *
     * Callback method of comments module
     *
     * @param integer $id object identifier
     *
     * @return void
     */
    public function comments_object_callback($id = 0)
    {
        $return = array();
        $return["body"] = "<a href='" . site_url() . "admin/events/edit_main/" . $id . "'>" . site_url() . "admin/events/edit_main/" . $id . "</a>";
        $return["author"] =  "admin";

        return $return;
    }

    public function comments_avatar_count_callback($count, $id = 0)
    {
        if ($id) {
            $this->DB->where('id', $id);
        }
        $data['avatar_comments_count'] = $count;
        $this->DB->update(TABLE_EVENTS, $data);
    }

    public function comments_avatar_object_callback($id = 0)
    {
        $return = array();
        $return["body"] = "<a href='" . site_url() . "admin/events/edit_main/" . $id . "'>" . site_url() . "admin/events/edit_main/" . $id . "</a>";
        $return["author"] =  "admin";

        return $return;
    }

    // moderation functions
    public function moderGetList($object_ids)
    {
        $params["where_in"]["id"] = $object_ids;
        $events = $this->getListEvents(null, null, null, $params);

        if (!empty($events)) {
            foreach ($events as $event) {
                $return[$event["id"]] = $event;
            }

            return $return;
        } else {
            return array();
        }
    }

    public function moderSetStatus($object_id, $status)
    {
        $new_attrs = array();
        switch ($status) {
            case 0:
                $new_attrs['status'] = -1;
                $new_attrs['is_active'] = 0;
                break;
            case 1:
                $new_attrs['status'] = 1;
                $new_attrs['is_active'] = 1;
                break;
        }
        $this->saveEvent($object_id, $new_attrs);
        if ($status) {
            $this->createWallEvent($this->wall_events['event_add']['gid'], $object_id);
        }
    }

    public function moderMarkAdult($object_id)
    {
        $this->markAdult($object_id);
    }

    public function markAdult($object_id)
    {
        $attrs['is_adult'] = 1;
        $this->saveEvent($object_id, $attrs);

        $this->ci->load->model('menu/models/Indicators_model');
        $this->ci->Indicators_model->delete('new_moderation_item', $media_id, true);
    }

    public function createWallEvent($gid, $id_object)
    {
        if (!$id_object) {
            return;
        }

        $event = $this->getEventById($id_object);
        $id_poster = $event['fk_user_id'];
        $id_wall = $event['fk_user_id'];
        $this->CI->load->helper('wall_events_default');
        $result = add_wall_event($gid, $id_wall, $id_poster, array(), $id_object);
    }

    public function formatWallEvents($wall_events)
    {
        $formatted_events = array();
        $users_ids = array();
        $events_ids = array();

        foreach ($wall_events as $key => $e) {
            $users_ids[$e['id_poster']] = $e['id_poster'];
            $events_ids[$e['id_object']] = $e['id_object'];
        }

        $this->CI->load->model('Users_model');
        if ($users_ids) {
            $users = $this->CI->Users_model->get_users_list_by_key(null, null, null, array(), $users_ids);
        }

        if ($events_ids) {
            $params['where_in']['id'] = $events_ids;
            $events = $this->getListEvents(null, null, null, $params);

            if(!empty($events)) {
                foreach ($events as $event) {
                    $formatted[$event['id']] = $event;
                }                
            }

        }

        foreach ($wall_events as $key => $e) {
            if(isset($formatted[$e['id_object']])) {
                $this->CI->view->assign('user', $users[$e['id_poster']]);
                $this->CI->view->assign('event', $formatted[$e['id_object']]);
                $this->CI->view->assign('wall_event', $e);
                $e['html'] = $this->CI->view->fetch('wall_events_events', null, 'events');
                $formatted_events[$key] = $e;                
            }

        }

        return $formatted_events;
    }

    public function backend_get_request_notifications()
    {
        $user_id = $this->CI->session->userdata("user_id");

        $params['where']['fk_user_id'] = $user_id;
        $params['where']['status'] = 'pending';
        $params['where']['is_invite'] = '1';
        //$params['where']['is_new'] = '1';
        $requests = $this->getEventsUser(null, null, null, $params);

        //$this->CI->load->helper('seo_helper');
        $result = array();
        if (!empty($requests)) {
            foreach ($requests as $request) {
                $link = rewrite_link('events', 'view', $request['id']);
                $result[] = array(
                    'title'     => l('notify_request_title', 'events'),
                    'text'      => str_replace('[event]', "<a href=\"{$link}\">{$request['name']}</a>", l('notify_request_text', 'events')),
                    'id'        => $request['id'],
                    'comment'   => '',
                    'user_id'   => '',
                    'user_name' => '',
                    'user_icon' => '',
                    'user_link' => '',
                    'is_new'    => $request['is_new'],
                );
            }
        }

        $this->DB->set('is_new', '0')->where($params['where'])->update(TABLE_EVENTS_USERS);

        return array('events' => $result);
    }
    
    public function sendDeleteEventMessage($event, $is_admin = false) {
        $this->CI->load->model("Mailbox_model");

        $participants = $this->getApprovedUsers($event['id']);
        if(!empty($participants)) {
            $message = l('message_event_delete_content', 'events');
            $user_id = $this->session->userdata('user_id');
            $message_data = array(
                'subject' => l('message_event_delete_subject', 'events'),
                'id_user' => $user_id,
                'id_from_user' => $user_id,
            );

            foreach($participants as $user) {
                $message = str_replace(array('[nickname]', '[event_name]'), array($user['user']['fname'], $event['name']), $message);

                $message_data['message'] = $message;
                $message_data['id_to_user'] = $user['fk_user_id'];

                if(!$is_admin) {
                    $message_id = $this->CI->Mailbox_model->save_message(null, $message_data);
                    $this->CI->Mailbox_model->send_message($message_id);                   
                } else {

                }

            }            
        }
    }
    
    public function isFreeSpots($max_spots, $current_count) {
        
        if($max_spots == 0) {
            return true;
        }

        if($max_spots >= ($current_count + 1)) {
            $count = $max_spots - $current_count;
            return $count;
        }
            
        return false;
    }
    
    public function comments_get_author_module_object($obj_id = null) 
    {
        return $this->isObjectOwner($obj_id);
    }
    
    public function isObjectOwner($obj_id = null, $user_id = null)
    {
        if($obj_id) {
            if(!$user_id) {
                $user_id = $this->session->userdata('user_id');
            }
            
            $object = $this->getEventById($obj_id);
            if($object['fk_user_id'] == $user_id && !$object['is_admin']) {
                return true;
            }
        }
        
        return false;
    } 
}
