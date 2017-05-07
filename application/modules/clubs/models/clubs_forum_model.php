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

define('CLUBS_FORUM_TABLE', DB_PREFIX . 'clubs_forum');
define('CLUBS_FORUM_POSTS_TABLE', DB_PREFIX . 'clubs_forum_posts');

/**
 * Clubs forum model
 * 
 * @package     PG_Dating
 * @subpackage  Clubs
 * @category    models
 * @copyright   Copyright (c) 2000-2014 PG Dating Pro - php dating software
 * @author      Renat Gabdrakhmanov <renatgab@pilotgroup.net>
 */
class Clubs_forum_model extends \Model
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
        CLUBS_FORUM_TABLE => [
            'id',
            'club_id',
            'name',
            'description',
            'is_active',
            'date_added',
            'date_modified',
            'posts_count',
        ],
        CLUBS_FORUM_POSTS_TABLE => [
            'id',
            'club_id',
            'topic_id',
            'user_id',
            'message',
            'is_active',
            'date_added',
        ],
    ];

    /**
     * Settings for formatting clubs object
     * 
     * @var array
     */
    protected $format_settings = [];

    /**
     * Class constructor
     * 
     * @return Clubs_forum_model
     */
    public function __construct()
    {
        parent::__construct();
        $this->ci = & get_instance();
    }

    public function getObject($data = [])
    {
        $fields = $this->fields[CLUBS_FORUM_TABLE];
        $fields_str = implode(', ', $fields);

        $this->ci->db->select($fields_str)
            ->from(CLUBS_FORUM_TABLE);

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

    public function validate($id = null, $data = [])
    {
        $return = ['errors' => [], 'data' => []];

        if (array_key_exists('name', $data)) {
            $return['data']['name'] = trim(strip_tags($data['name']));
            if (empty($return['data']['name'])) {
                $return['errors'][] = l('error_empty_topic_name', self::MODULE_GID);
            }
        }

        if (array_key_exists('description', $data)) {
            $return['data']['description'] = trim(strip_tags($data['description']));
        }

        if (array_key_exists('club_id', $data)) {
            $return['data']['club_id'] = intval($data['club_id']);
            if (empty($return['data']['club_id'])) {
                $return['errors'][] = l('error_empty_club_id', self::MODULE_GID);
            }
        }

        if (array_key_exists('is_active', $data)) {
            $return['data']['is_active'] = intval($data['is_active']) ? 1 : 0;
        }

        if (array_key_exists('posts_count', $data)) {
            $return['data']['posts_count'] = intval($data['posts_count']);
        }

        return $return;
    }

    public function save($id = null, $save_raw = []) 
    {
        $save_raw['date_modified'] = date(self::DB_DATE_FORMAT);

        if (!is_null($id)) {
            $this->ci->db->where('id', $id);
            $this->ci->db->update(CLUBS_FORUM_TABLE, $save_raw);
        } else {
            $save_raw['date_added'] = date(self::DB_DATE_FORMAT);
            $this->ci->db->insert(CLUBS_FORUM_TABLE, $save_raw);
            $id = $this->ci->db->insert_id();
        }

        return $id;
    }

    public function getList($filters = [], $page = null, $items_on_page = null, $order_by = null)
    {
        $params = $this->_getCriteria($filters);
        return $this->_getList($page, $items_on_page, $order_by, $params);
    }

    public function getListByKey($filters = [], $page = null, $items_on_page = null, $order_by = null)
    {
        $return = [];

        $params = $this->_getCriteria($filters);
        $list   = $this->_getList($page, $items_on_page, $order_by, $params);
        foreach ($list as $key => $item) {
            $return[$item['id']] = $item;
        }
        return $return;
    }

    public function getCount($filters = [])
    {
        $params = $this->_getCriteria($filters);
        return $this->_getCount($params);
    }

    public function _getCriteria($filters)
    {
        $table = !empty($filters['table']) ? $filters['table'] : CLUBS_FORUM_TABLE;
        $filters = ['data' => $filters, 'table' => $table, 'type' => ''];

        $params = [
            'table' => $filters['table'],
        ];

        $fields = array_flip($this->fields[$params['table']]);
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
        $table = isset($params['table']) ? $params['table'] : CLUBS_FORUM_TABLE;
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
                if (in_array($field, $this->fields[$table])) {
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
        $table = isset($params['table']) ? $params['table'] : CLUBS_FORUM_TABLE;

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
            $this->ci->db->where('id', $id)->delete(CLUBS_FORUM_TABLE);
            $this->ci->db->where('topic_id', $id)->delete(CLUBS_FORUM_POSTS_TABLE);
        }

        return true;
    }

    public function callbackClubDelete($club_id)
    {
        $this->deleteByClubId($club_id);
        return true;
    }

    protected function deleteByClubId($club_id)
    {
        $this->ci->db->where('club_id', $club_id)->delete(CLUBS_FORUM_TABLE);
        $this->ci->db->where('club_id', $club_id)->delete(CLUBS_FORUM_POSTS_TABLE);
        return true;
    }

    // POSTS METHODS
    public function getPostsCount($filters = [])
    {
        $filters['table'] = CLUBS_FORUM_POSTS_TABLE;
        $params = $this->_getCriteria($filters);
        return $this->_getCount($params);
    }

    public function getPostsList($filters = [], $page = null, $items_on_page = null, $order_by = null)
    {
        $filters['table'] = CLUBS_FORUM_POSTS_TABLE;
        $params = $this->_getCriteria($filters, $page, $items_on_page, $order_by);
        return $this->_getList($page, $items_on_page, $order_by, $params);
    }

    public function formatPost($data)
    {
        return current($this->formatPostsArray([$data]));
    }

    public function formatPostsArray($data)
    { 
        $return = [];

        if (empty($data) || !is_array($data)) {
            return [];
        }

        $users_ids = [];
        foreach ($data as $key => $item) {
            $users_ids[] = $item['user_id'];
            $return[$key] = $item;
        }

        if (!empty($users_ids)) {
            $this->ci->load->model('Users_model');
            $users = $this->ci->Users_model->get_users_list_by_key(null, null, null, [], array_unique($users_ids));
            foreach ($return as $key => $item) {
                $return[$key]['user'] = array_key_exists($item['user_id'], $users) ? $users[$item['user_id']] : [];
            }
        }

        return $return;
    }

    public function getPostObject($data = [])
    {
        $fields = $this->fields[CLUBS_FORUM_POSTS_TABLE];
        $fields_str = implode(', ', $fields);

        $this->ci->db->select($fields_str)
            ->from(CLUBS_FORUM_POSTS_TABLE);

        foreach ($data as $field => $value) {
            $this->ci->db->where($field, $value);
        }
        
        $results = $this->ci->db->get()->result_array();

        if (!empty($results) && is_array($results)) {
            return $results[0];
        }

        return false;
    }

    public function deletePost($post_id) 
    {
        if (is_array($post_id)) {
            $this->ci->db->where_in('id', $post_id)->delete(CLUBS_FORUM_POSTS_TABLE);
        } else {
            $this->ci->db->where('id', $post_id)->delete(CLUBS_FORUM_POSTS_TABLE);
        }

        return true;
    }

    public function savePost($id = null, $save_raw = []) 
    {
        if (!is_null($id)) {
            $this->ci->db->where('id', $id);
            $this->ci->db->update(CLUBS_FORUM_POSTS_TABLE, $save_raw);
        } else {
            $save_raw['date_added'] = date(self::DB_DATE_FORMAT);
            $this->ci->db->insert(CLUBS_FORUM_POSTS_TABLE, $save_raw);
            $id = $this->ci->db->insert_id();
        }

        return $id;
    }
}
