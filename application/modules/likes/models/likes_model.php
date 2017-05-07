<?php

namespace Pg\Modules\Likes\Models;

use Pg\Libraries\EventDispatcher;
use Pg\Modules\Likes\Models\Events\EventLikes;

/**
 * Likes model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Alexander Batukhtin <abatukhtin@pilotgroup.net>
 * */
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
if (!defined('LIKES_TABLE')) {
    define('LIKES_TABLE', DB_PREFIX . 'likes');
}
if (!defined('LIKES_COUNT_TABLE')) {
    define('LIKES_COUNT_TABLE', DB_PREFIX . 'likes_count');
}

class Likes_model extends \Model
{
    /**
     * link to CodeIgniter object
     *
     * @var object
     */
    protected $CI;

    /**
     * link to DataBase object
     *
     * @var object
     */
    protected $DB;
    private $_id_likes = array();

    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
        $this->DB = &$this->CI->db;
    }

    /**
     * Remembers used like id
     *
     * @param string $id_like
     *
     * @return \Likes_model
     */
    public function remember_gid($id_like)
    {
        $this->_id_likes[] = $id_like;

        return $this;
    }

    /**
     * Recalls used likes id
     *
     * @return array
     */
    public function recall_gids()
    {
        return array_unique($this->_id_likes);
    }

    /**
     * Clears remembered likes id
     *
     * @return \Likes_model
     */
    public function clear_gids()
    {
        $this->_id_likes = array();

        return $this;
    }

    /**
     * Like action
     *
     * @param int    $id_user
     * @param string $id_like
     * @param bool   $action
     *
     * @throws Exception
     *
     * @return \Likes_model
     */
    public function like($id_user, $id_like, $action = 'like')
    {
        if (empty($id_like) || empty($id_user) || !is_string($action)) {
            log_message('error', 'Error while liking');
            throw new \Exception('Error while liking');
        } elseif ('like' === $action) {
            $this->addLike($id_user);
            $this->add($id_user, $id_like);
        } elseif ('unlike' === $action) {
            $this->delete($id_user, $id_like);
        }
        $this->update_count($id_like);

        return $this;
    }

    public function addLike($id = null)
    {
        if ($id) {
            $event_handler = EventDispatcher::getInstance();
            $event = new EventLikes();
            $event_data = array();
            $event_data['id'] = $id;
            $event_data['action'] = 'likes_add_like';
            $event_data['module'] = 'likes';
            $event->setData($event_data);
            $event_handler->dispatch('likes_add_like', $event);
        }
    }

    public function bonusCounterCallback($counter = array())
    {
        $event_handler = EventDispatcher::getInstance();
        $event = new EventLikes();
        $event->setData($counter);
        $event_handler->dispatch('bonus_counter', $event);
    }

    public function bonusActionCallback($data = array())
    {
        $counter = array();
        if (!empty($data)) {
            $counter = $data['counter'];
            $action = $data['action'];
            $counter['count'] = $counter['count'] + 1;
            $counter['is_new_counter'] = $data['is_new_counter'];
            $counter['repetition'] = $data['bonus']['repetition'];
            $this->bonusCounterCallback($counter);
        }
    }

    /**
     * Add like
     *
     * @param int    $id_user
     * @param string $id_like
     *
     * @throws Exception
     *
     * @return \Likes_model
     */
    private function add($id_user, $id_like)
    {
        if (empty($id_like) || empty($id_user)) {
            log_message('error', 'Error while adding like');
            throw new \Exception('Error while adding like');
        }
        $this->DB->insert(LIKES_TABLE, array(
            'id_like' => $id_like,
            'id_user' => $id_user,
        ));

        return $this;
    }

    /**
     * Delete like
     *
     * @param int    $id_user
     * @param string $id_like
     *
     * @throws Exception
     *
     * @return \Likes_model
     */
    private function delete($id_user, $id_like)
    {
        if (empty($id_like) || empty($id_user)) {
            log_message('error', 'Error while removing like');
            throw new \Exception('Error while removing like');
        }
        $this->DB->where('id_like', $id_like)
                ->where('id_user', $id_user)
                ->delete(LIKES_TABLE);

        return $this;
    }

    /**
     * Get likes count
     *
     * @param string|array $id_like
     *
     * @return array
     */
    public function get_count($id_like = null)
    {
        $this->DB->select('id_like, count')->from(LIKES_COUNT_TABLE);
        if (is_array($id_like)) {
            $this->DB->where_in('id_like', $id_like);
        } elseif (isset($id_like)) {
            $this->DB->where('id_like', $id_like);
        }
        $count = array();
        foreach ($this->DB->get()->result_array() as $like) {
            $count[$like['id_like']] = $like['count'];
        }

        return $count;
    }

    /**
     * Get likes count
     *
     * @param string|array $id_likes
     *
     * @return array
     */
    public function get_count_slow($id_likes)
    {
        if (empty($id_likes)) {
            log_message('ERROR', 'Error while counting');
            throw new \Exception('Error while counting');
        } elseif (!is_array($id_likes)) {
            $id_likes = array($id_likes);
        }
        $result = $this->DB->select('id_like, COUNT(id_like) AS count')
                        ->from(LIKES_TABLE)->group_by('id_like')
                        ->where_in('id_like', $id_likes)
                        ->get()->result_array();
        if (0 === count($result)) {
            $count = array_fill_keys($id_likes, 0);
        } else {
            $count = array();
            foreach ($result as $like) {
                $count[$like['id_like']] = $like['count'];
            }
        }

        return $count;
    }

    /**
     * Update likes count
     *
     * @param string|array $id_likes
     *
     * @return \Likes_model
     */
    public function update_count($id_likes = null)
    {
        $counts = $this->get_count_slow($id_likes);
        if (0 === count($counts)) {
            return $this;
        }
        $query = 'INSERT INTO ' . LIKES_COUNT_TABLE . ' (id_like, count) VALUES ';
        $values = '';
        foreach ($counts as $id_like => $count) {
            $values .= "('$id_like', '$count'),";
        }
        $query .= substr($values, 0, -1) . ' ON DUPLICATE KEY UPDATE `count` = VALUES (count)';
        $this->DB->query($query);

        return $this;
    }

    /**
     * Get likes
     *
     * @param int          $id_user
     * @param string|array $filter  likes id
     *
     * @throws Exception
     *
     * @return array
     */
    public function get_likes_by_user($id_user, $filter = null)
    {
        if (empty($id_user)) {
            log_message('ERROR', 'Error while getting likes');
            throw new \Exception('Error while getting likes');
        }
        $this->DB->select('id_like')
                ->from(LIKES_TABLE)
                ->where('id_user', $id_user);
        if ($filter) {
            if (is_array($filter)) {
                $this->DB->where_in('id_like', $filter);
            } else {
                $this->DB->where('id_like', $filter);
            }
        }
        $results = $this->DB->get()->result_array();
        if (!$results) {
            return array();
        }
        $id_likes = array();
        foreach ($results as $result) {
            $id_likes[] = $result['id_like'];
        }

        return array_unique($id_likes);
    }

    /**
     * Get users
     *
     * @param string $id_like
     * @param int    $limit
     *
     * @throws Exception
     *
     * @return array
     */
    public function get_users_by_like($id_like, $limit = 5)
    {
        if (empty($id_like)) {
            log_message('error', 'Error while getting users');
            throw new \Exception('Error while getting users');
        }
        $results = $this->DB->select('id_user')
                        ->from(LIKES_TABLE)
                        ->where('id_like', $id_like)
                        ->limit($limit, 0)
                        ->get()->result_array();
        if (!$results) {
            return array();
        }
        $id_users = array();
        foreach ($results as $result) {
            $id_users[] = $result['id_user'];
        }
        $this->CI->load->model('Users_model');
        $default_user = $this->CI->Users_model->format_default_user(1);
        $users = $this->CI->Users_model->get_users_list(null, null, null, array(), $id_users, true, true);
        foreach ($id_users as $key => $id) {
            if (!$users[$key]["id"]) {
                $users[$key] = $default_user;
            }
        }

        return $users;
    }
}
