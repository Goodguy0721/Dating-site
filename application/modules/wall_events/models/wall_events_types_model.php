<?php

namespace Pg\Modules\Wall_events\Models;

/**
 * Wall events types model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * */
if (!defined('TABLE_WALL_EVENTS_TYPES')) {
    define('TABLE_WALL_EVENTS_TYPES', DB_PREFIX . 'wall_events_types');
}

class Wall_events_types_model extends \Model
{
    private $ci;
    private $fields_types = array(
        'gid',
        'module',
        'model',
        'method_format_event',
        'status',
        'settings',
        'date_add',
        'date_update',
    );
    private $fields_types_str;
    private $default_types_params = array(
        'join_period' => 30,
    );

    public function __construct()
    {
        parent::__construct();
        $this->ci = &get_instance();
        $this->fields_types_str = implode(', ', $this->fields_types);

        $this->ci->db->memcache_tables(array(TABLE_WALL_EVENTS_TYPES));
    }

    public function add_wall_events_type($attrs = array())
    {
        if (empty($attrs['gid'])) {
            return false;
        }
        $wall_events_type = $this->get_wall_events_type($attrs['gid']);
        if (!empty($wall_events_type)) {
            return false;
        }
        $this->setPreparedTypesParams($attrs);
        $this->ci->db->insert(TABLE_WALL_EVENTS_TYPES, $attrs);

        return $this->ci->db->affected_rows();
    }

    public function delete_wall_events_type($gid)
    {
        /* $this->CI->load->model('Wall_events_model');
          $this->CI->Wall_events_model->delete_wall_events_by_gid($gid); */
        $this->ci->pg_language->pages->delete_string('wall_events', 'wetype_' . $gid);
        $this->ci->db->where('gid', $gid)->delete(TABLE_WALL_EVENTS_TYPES);

        return $this->ci->db->affected_rows();
    }

    public function get_wall_events_type($gid)
    {
        $result = $this->ci->db->select($this->fields_types_str)->from(TABLE_WALL_EVENTS_TYPES)->where('gid', $gid)->get()->row_array();
        if (!empty($result)) {
            $this->getPreparedTypesParams($result);
        }

        return $result;
    }

    public function get_wall_events_types_gids($params = array())
    {
        if (!empty($params['where']) && is_array($params['where'])) {
            $this->ci->db->where($params['where']);
        }
        $result = $this->ci->db->select('gid')->from(TABLE_WALL_EVENTS_TYPES)->get()->result_array();
        $return = array();
        foreach ($result as $gid) {
            $return[$gid['gid']] = $gid['gid'];
        }

        return $return;
    }

    public function get_wall_events_types($params = array(), $page = null, $items_on_page = null)
    {
        $this->ci->db->select($this->fields_types_str)->from(TABLE_WALL_EVENTS_TYPES);
        if (!empty($params['where']) && is_array($params['where'])) {
            $this->ci->db->where($params['where']);
        }
        if ($page && $items_on_page) {
            $this->ci->db->limit($items_on_page, $items_on_page * ($page - 1));
        }
        $result = $this->ci->db->get()->result_array();
        $result_format = array();
        foreach ($result as $key => $type) {
            $result_format[$type['gid']] = $type;
            $this->getPreparedTypesParams($result_format[$type['gid']]);
        }

        return $result_format;
    }

    public function get_wall_events_types_cnt()
    {
        $count = $this->ci->db->count_all(TABLE_WALL_EVENTS_TYPES);

        return $count;
    }

    public function save_wall_events_type($gid, $attrs = array())
    {
        $this->setPreparedTypesParams($attrs, $gid);
        $attrs['date_update'] = date("Y-m-d H:i:s");
        $this->ci->db->where('gid', $gid)->update(TABLE_WALL_EVENTS_TYPES, $attrs);

        return $this->ci->db->affected_rows();
    }

    private function setPreparedTypesParams(&$params, $gid = '')
    {
        if (!isset($params['settings']) || !is_array($params['settings'])) {
            $params['settings'] = array();
        }
        if ($gid) {
            $wall_events_type = $this->get_wall_events_type($gid);
            $params['settings'] = array_merge($wall_events_type['settings'], $params['settings']);
        }
        foreach ($this->default_types_params as $key => $value) {
            if (!isset($params['settings'][$key])) {
                $params['settings'][$key] = $value;
            }
            if ($params['settings'][$key] === false) {
                $params['settings'][$key] = 0;
            }
        }
        $params['settings'] = serialize($params['settings']);
    }

    private function getPreparedTypesParams(&$params)
    {
        if ($params) {
            $settings = unserialize($params['settings']);
            if (!is_array($settings)) {
                $settings = array();
            }
            $params['settings'] = array_merge($this->default_types_params, $settings);
        }
    }

    public function update_langs($wetypes, $langs_file)
    {
        foreach ($wetypes as $wetype) {
            $this->ci->pg_language->pages->set_string_langs('wall_events', 'wetype_' . $wetype, $langs_file['wetype_' . $wetype], array_keys($langs_file['wetype_' . $wetype]));
        }
    }

    public function export_langs($wetypes, $langs_ids)
    {
        $gids = array();
        foreach ($wetypes as $wetype) {
            $gids[] = 'wetype_' . $wetype;
        }

        return $this->ci->pg_language->export_langs('wall_events', $gids, $langs_ids);
    }

    public function validate($gid, $data)
    {
        $return = array('errors' => array(), 'data' => array());

        if ($gid) {
            $wall_events_type = $this->get_wall_events_type($gid);
        } else {
            $wall_events_type['settings'] = array();
        }

        if (isset($data['status'])) {
            $return['data']['status'] = $data['status'] ? 1 : 0;
        }

        if (isset($data['settings'])) {
            $return['data']['settings'] = $wall_events_type['settings'];

            if (isset($data['settings']['join_period'])) {
                $return['data']['settings']['join_period'] = intval($data['settings']['join_period']);
                if ($return['data']['settings']['join_period'] <= 0) {
                    $return['errors'][] = l('error_join_period', 'wall_events');
                }
            }
        }

        return $return;
    }
}
