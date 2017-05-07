<?php

namespace Pg\modules\access_permissions\models;

use Pg\modules\access_permissions\models\Access_permissions_model as AccessPermissionsModel;

/**
 * Access_permissions module
 *
 * @copyright   Copyright (c) 2000-2016
 * @author  Pilot Group Ltd <http://www.pilotgroup.net/>
 */
define('ACCESS_PERMISSIONS_MODULES_TABLE',
    DB_PREFIX . 'access_permissions_modules');

class Access_permissions_modules_model extends \Model
{

    /**
     * Modules object properties
     *
     * @var array
     */
    private $fields = [
        'id',
        'module_gid',
        'method',
        'access',
        'data'
    ];

    protected $ci;

    /**
     * Class constructor
     *
     * @return Access_permissions_settings_model
     */
    public function __construct()
    {
        parent::__construct();

        $this->ci = &get_instance();
    }

    /**
     * Return Module by id
     *
     * @param type $id
     *
     * @return boolean/array
     */
    public function getModuleById($id = null)
    {
        if (!is_null($id)) {
            return $this->getModulesObject('id', $id);
        }
        return false;
    }

    /**
     * Return Modules object
     *
     * @param string $field_name  field name
     * @param mixed  $field_value field value
     *
     * @return boolean/array
     */
    public function getModulesObject($field_name, $field_value)
    {
        $results = $this->ci->db->select(implode(', ', $this->fields))
            ->from(ACCESS_PERMISSIONS_MODULES_TABLE)
            ->where($field_name, $field_value)
            ->get()
            ->result_array();
        if (!empty($results) && is_array($results)) {
            return $results[0];
        }
        return false;
    }

    /**
     *  Modules List
     *
     * @param array $params
     * @param integer $page
     * @param integer $limits
     * @param string/array $order_by
     *
     * @return array
     */
    public function getModulesList(array $params, $page = null, $limits = null, $order_by = null)
    {
        $this->ci->db->select(implode(', ', $this->fields));
        $this->ci->db->from(ACCESS_PERMISSIONS_MODULES_TABLE);

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
        if (is_array($order_by) && count($order_by) > 0) {
            foreach ($order_by as $field => $dir) {
                if (in_array($field, $this->fields)) {
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
            return $this->formatModulesList($results);
        }
        return [];
    }

    /**
     * Get permission ID
     *
     * @param array $where
     *
     * @return integer
     */
    public function getPermissionId(array $where)
    {
        $this->ci->db->select('id');
        $this->ci->db->from(ACCESS_PERMISSIONS_MODULES_TABLE);
        foreach ($where as $field => $value) {
            if (!empty($value)) {
                $this->ci->db->where($field, $value);
            }
        }
        return current($this->ci->db->get()->result_array())['id'];
    }

    /**
     * Save modules
     *
     * @param array $attrs
     * @param integer $id
     *
     * @return integer
     */
    public function saveModules(array $attrs, $id = null)
    {
        if (is_null($id)) {
            $this->ci->db->insert(ACCESS_PERMISSIONS_MODULES_TABLE, $attrs);
            $id = $this->ci->db->insert_id();
        } else {
            $this->ci->db->where('id', $id);
            $this->ci->db->update(ACCESS_PERMISSIONS_MODULES_TABLE, $attrs);
        }
        return $id;
    }

    /**
     * Validate modules data
     *
     * @param array $data
     * @param integer $id
     *
     * @return array
     */
    public function validateModules(array $data, $id = null)
    {
        $return         = ['errors' => [], 'data' => []];
        $modules_object = !is_null($id) ? $this->getModuleById($id) : [];
        if (!empty($modules_object['module_gid'])) {
            $return['data']['module_gid'] = $modules_object['module_gid'];
        } else {
            if (!empty($data['module_gid'])) {
                $return['data']['module_gid'] = $data['module_gid'];
            } else {
                $return['errors'][] = l('error_gid', AccessPermissionsModel::MODULE_GID);
            }
        }
        if (!empty($data['access'])) {
            $return['data']['access'] = $data['access'];
        } else {
            $return['errors'][] = l('error_unknown_user_type', AccessPermissionsModel::MODULE_GID);
        }
        if (!empty($data['data'])) {
            $data['data'] = serialize($data['data']);
        }
        $return['data']['controller'] = !empty($data['controller']) ? trim($data['controller']) : $modules_object['controllet'];
        $return['data']['method']     = !empty($data['method']) ? trim($data['method']) : $modules_object['method'];
        return $return;
    }

    /**
     * Format module data
     *
     * @param array $module
     *
     * @return array
     */
    public function formatModule(array $module)
    {
        return current(
            $this->formatModulesList([$module])
        );
    }

    /**
     * Format modules data
     *
     * @param array $modules
     *
     * @return array
     */
    public function formatModulesList(array $modules)
    {
        foreach ($modules as $module) {
            $module['data'] = unserialize($module['data']);
            if (!empty($module['data'])) {
                $module['data'] = $this->formatAdditionalSettings($module);
                $module['status'] = 'incomplete';
            }
            if (empty($module['method'])) {
                $module['name'] = l('field_permission_' . $module['module_gid'], AccessPermissionsModel::MODULE_GID);
                $module['description'] = l('field_permission_' . $module['module_gid'] . '_description', AccessPermissionsModel::MODULE_GID);
            } else {
                $module['name'] = l('field_permission_' . $module['module_gid'] . '_' . $module['method'], AccessPermissionsModel::MODULE_GID);
                $module['description'] = l('field_permission_' . $module['module_gid'] . '_' . $module['method'] . '_description', AccessPermissionsModel::MODULE_GID);
            }
            $result[$module['id']] = $module;
        }

        return $result;
    }

    /**
     * Format Additional Settings
     *
     * @param array $data
     *
     * @return array
     */
    private function formatAdditionalSettings(array $data)
    {
        if (isset($data['data']['all'])) {
            $return = [
                [
                    'name' => l('field_additional_permission_' . $data['module_gid'] . '_all', AccessPermissionsModel::MODULE_GID),
                    'count' => $data['data']['all'],
                ]
            ];
        } else {
            foreach ($data['data'] as $key =>  $item) {
                $return[$key]['name'] = l('field_additional_permission_' . $data['module_gid'] . '_' . $key, AccessPermissionsModel::MODULE_GID);
                $return[$key]['count'] = $item;
            }
        }
        return $return;
    }
}
