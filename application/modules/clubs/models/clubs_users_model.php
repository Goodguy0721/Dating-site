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

define('CLUBS_USERS_TABLE', DB_PREFIX . 'clubs_users');

/**
 * Clubs users model
 * 
 * @package     PG_Dating
 * @subpackage  Clubs
 * @category    models
 * @copyright   Copyright (c) 2000-2014 PG Dating Pro - php dating software
 * @author      Renat Gabdrakhmanov <renatgab@pilotgroup.net>
 */
class Clubs_users_model extends \Model
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
        CLUBS_USERS_TABLE => [
            'id',
            'user_id',
            'club_id',
            'date_added',
            'date_modified',
        ],
    ];

    /**
     * Settings for formatting clubs users object
     * 
     * @var array
     */
    protected $format_settings = [];
    protected $cache = [];
    protected $cache_by_club = [];
    /**
     * Class constructor
     * 
     * @return Clubs_model
     */
    public function __construct()
    {
        parent::__construct();
        $this->ci = & get_instance();
        $this->ci->db->memcache_tables(['CLUBS_USERS_TABLE']);
    }

    public function getObject($data = [])
    {
        $fields = $this->fields[CLUBS_USERS_TABLE];
        $fields_str = implode(', ', $fields);

        $this->ci->db->select($fields_str)
            ->from(CLUBS_USERS_TABLE);

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

        foreach ($data as $key => $item) {
            
            $return[$key] = $item;
        }

        return $return;
    }

    public function validate($id, $data = []) 
    {
        $return = ['errors' => [], 'data' => []];

        $current_object = $this->getObject(['id' => $id]);

        if (array_key_exists('user_id', $data)) {
            $return['data']['user_id'] = intval($data['user_id']);
        }

        if (array_key_exists('club_id', $data)) {
            $return['data']['club_id'] = intval($data['club_id']);
        }

        return $return;
    }

    public function save($id = null, $save_raw = []) 
    {
        $current_object = $this->getObject(['id' => $id]);

        $save_raw['date_modified'] = date(self::DB_DATE_FORMAT);

        if (!is_null($id)) {
            $this->ci->db->where('id', $id);
            $this->ci->db->update(CLUBS_USERS_TABLE, $save_raw);
        } else {
            $save_raw['date_added'] = date(self::DB_DATE_FORMAT);
            $this->ci->db->insert(CLUBS_USERS_TABLE, $save_raw);
            $id = $this->ci->db->insert_id();
        }

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
        $filters = ['data' => $filters, 'table' => CLUBS_USERS_TABLE, 'type' => ''];

        $params = [];

        $params['table'] = !empty($filters['table']) ? $filters['table'] : CLUBS_USERS_TABLE;

        $fields = array_flip($this->fields[CLUBS_USERS_TABLE]);
        foreach ($filters['data'] as $filter_name => $filter_data) {
            if (!is_array($filter_data)) {
                $filter_data = trim($filter_data);
            }
            switch ($filter_name) {
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
        $table = CLUBS_USERS_TABLE;
        $fields = $this->fields[$table];
        
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
                if (in_array($field, $fields)) {
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
        $table = isset($params['table']) ? $params['table'] : CLUBS_USERS_TABLE;

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

    public function joinToClub($user_id, $club_id)
    {
        $save_data = [
            'user_id'  => $user_id,
            'club_id' => $club_id,
        ];

        $this->save(null, $save_data);

        $this->ci->load->model('Clubs_model');
        $users_count = $this->ci->Clubs_users_model->getCount(['club_id' => $club_id]);
        $this->ci->Clubs_model->save($club_id, ['users_count' => $users_count]);

        return true;
    }

    public function leaveClub($user_id, $club_id)
    {
        $this->ci->db->where('user_id', $user_id);
        $this->ci->db->where('club_id', $club_id);
        $this->ci->db->delete(CLUBS_USERS_TABLE);

        $this->ci->load->model('Clubs_model');
        $users_count = $this->getCount(['club_id' => $club_id]);
        $this->ci->Clubs_model->save($club_id, ['users_count' => $users_count]);

        return true;
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
            $this->ci->db->delete(CLUBS_USERS_TABLE);
        }

        return true;
    }

    public function callbackClubDelete($club_id)
    {
        $this->ci->db->where('club_id', $club_id)->delete(CLUBS_USERS_TABLE);
        return $this->ci->db->affected_rows();
    }

    public function getUsersIds($user_id = null)
    {
        $return = [0];

        if (!$user_id) {
            $user_id = $this->ci->session->userdata('user_id');
        }

        if (!isset($this->cache[$user_id])) {
            $list = $this->getList(['user_id' => $user_id]);
            $clubs_ids = [];
            foreach ($list as $key => $item) {
                $clubs_ids[] = $item['club_id'];
            }

            if (!empty($clubs_ids)) {
                $list = $this->getList(['club_id' => array_unique($clubs_ids)]);
                foreach ($list as $key => $item) {
                    $return[] = $item['user_id'];
                }
            }

            $this->cache[$user_id] = array_unique($return);
        }

        return $this->cache[$user_id];
    }

    public function getUsersIdsByClubId($club_id)
    {
        $return = [];

        if (!isset($this->cache_by_club[$club_id])) {
            $list = $this->getList(['club_id' => $club_id]);
            $user_ids = [];
            foreach ($list as $key => $item) {
                $user_ids[] = $item['user_id'];
            }

            if (!empty($user_ids)) {
                $list = $this->getList(['club_id' => array_unique($clubs_ids)]);
                foreach ($list as $key => $item) {
                    $return[] = $item['user_id'];
                }
            }

            $this->cache_by_club[$club_id] = array_unique($return);
        }

        return $this->cache_by_club[$club_id];
    }
}
