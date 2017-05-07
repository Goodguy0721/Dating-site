<?php

namespace Pg\Modules\Wall_events\Models;

/**
 * Wall events permissions model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * */
if (!defined('TABLE_WALL_EVENTS_PERMISSIONS')) {
    define('TABLE_WALL_EVENTS_PERMISSIONS', DB_PREFIX . 'wall_events_permissions');
}

class Wall_events_permissions_model extends \Model
{
    private $ci;
    private $fields = array(
        'id_user',
        'permissions',
    );
    private $fields_str;

    public function __construct()
    {
        parent::__construct();
        $this->ci = &get_instance();
        $this->fields_str = implode(', ', $this->fields);
    }

    public function get_user_permissions($id_user)
    {
        $event_perm = $this->getDefaultPermissions();
        $saved_perm = $this->ci->db->select('permissions')->from(TABLE_WALL_EVENTS_PERMISSIONS)->where('id_user', $id_user)->get()->row_array();
        if (!empty($saved_perm)) {
            $user_perm = unserialize($saved_perm['permissions']);
        } else {
            $user_perm = array();
        }
        $result = array();
        foreach ($event_perm as $e_gid => $perm) {
            foreach ($perm as $perm_name => $val) {
                $result[$e_gid][$perm_name] = isset($user_perm[$e_gid][$perm_name]) ? $user_perm[$e_gid][$perm_name] : $val;
            }
        }

        return $result;
    }

    public function get_user_feeds($id_user)
    {
        $perms = $this->get_user_permissions($id_user);
        $result = array();
        foreach ($perms as $e_gid => $perm) {
            if ($perm['feed']) {
                $result[] = $e_gid;
            }
        }

        return $result;
    }

    public function set_user_permissions($id_user, $permissions)
    {
        $attrs['id_user'] = $id_user;
        if (is_array($permissions)) {
            foreach ($permissions as &$perm) {
                array_map('intval', $perm);
            }
        }
        $attrs['permissions'] = is_array($permissions) ? serialize($permissions) : '';
        $sql = $this->ci->db->insert_string(TABLE_WALL_EVENTS_PERMISSIONS, $attrs) . " ON DUPLICATE KEY UPDATE `permissions`=" . $this->ci->db->escape($attrs['permissions']);
        $this->ci->db->query($sql);

        return $this->ci->db->affected_rows();
    }

    private function getDefaultPermissions()
    {
        $result = array();
        $this->ci->load->model('wall_events/models/Wall_events_types_model');
        $params['where']['status'] = '1';
        $events_types = $this->ci->Wall_events_types_model->get_wall_events_types($params);
        foreach ($events_types as $e_type) {
            $result[$e_type['gid']] = $e_type['settings']['permissions'];
        }

        return $result;
    }

    public function is_permissions_allowed($permissions, $id_wall, $id_poster)
    {
        if ($id_wall && $id_wall == $id_poster || $permissions == 3) {
            return true;
        }

        if ($permissions <= 0 || $permissions > 3) {
            return false;
        }

        $user_id = $this->ci->session->userdata('user_id');
        if ($user_id && $permissions >= 2) {
            return true;
        }

        if ($this->ci->pg_module->is_module_installed('friendlist')) {
            $this->ci->load->model('Friendlist_model');
            $is_friend = $this->ci->Friendlist_model->is_friend($id_wall, $user_id);
        } else {
            $is_friend = false;
        }
        if ($permissions == 1 && $is_friend) {
            return true;
        }

        return false;
    }
}
