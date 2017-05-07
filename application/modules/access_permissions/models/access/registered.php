<?php

namespace Pg\modules\access_permissions\models\access;

use Pg\modules\access_permissions\models\Access_permissions_model as AccessPermissionsModel;
use Pg\modules\users\models\Users_model as UsersModel;

/**
 * Access_permissions module
 *
 * @copyright   Copyright (c) 2000-2016
 * @author  Pilot Group Ltd <http://www.pilotgroup.net/>
 */

class Registered extends AccessAbstract
{

    /**
     * Role type
     *
     * @var string
     */
    public  $role_type = 'registered';

    /**
     * Class constructor
     *
     * @return Registered
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get Access Role
     *
     * @param string $gid
     *
     * @return string
     */
    public function getRole($gid)
    {
        return ($gid != 'default') ? 'user_' . $gid : AccessPermissionsModel::USER;
    }

    /**
     * Get Field Periods Table
     *
     * @param string $gid
     *
     * @return string
     */
    public function getField($gid)
    {
        return $gid . '_group';
    }

    /**
     * Return filtered groups objects from data source as array
     *
     * @param array   $params       filters data
     *
     * @return array
     */
    public function getGroupsList(array $params)
    {
        $this->ci->load->model(AccessPermissionsModel::MODULE_GID . '/models/Access_Permissions_Groups_Model');
        $groups = $this->ci->Access_Permissions_Groups_Model->getGroupsList($params);
        foreach ($groups as $key => $group) {
            $groups[$key]['periods'] = $this->ci->Access_Permissions_Groups_Model->getPeriodsList([$group['gid'] . '_group', $group['gid'] . '_label', $group['gid'] . '_special']);
        }
        return $groups;
    }

    /**
     * Group data
     *
     * @param string $group_gid
     * @param array $params
     *
     * @return array
     */
    public function getGroupData($group_gid, array $params)
    {
        $this->ci->load->model(AccessPermissionsModel::MODULE_GID . '/models/Access_Permissions_Groups_Model');
        $result = $this->ci->Access_Permissions_Groups_Model->getGroupByGid($group_gid);
        $field = $this->getField($group_gid);
        $result['period'] = current($this->ci->Access_Permissions_Groups_Model->getPeriodsList([$field, $group_gid . '_label', $group_gid . '_special'], $params));
        return $result;
    }

    /**
     * Group info
     *
     * @param array $data
     *
     * @return array
     */
    public function getGroup(array $data)
    {
        $this->ci->load->model(AccessPermissionsModel::MODULE_GID . '/models/Access_Permissions_Groups_Model');
        $result = $this->ci->Access_Permissions_Groups_Model->getGroupByGid($data['group_gid']);
        if (!empty($result)) {
            return $this->formatGroupPeriods([$data['group_gid'] => $result], $data);
        }
        return [];
    }

    /**
     * Format groups period
     *
     * @param array $group
     * @param array $data
     *
     * @return array
     */
    private function formatGroupPeriods($group, $data)
    {
        $role = 'user_' . $data['group_gid'];
        $result = $this->permissionsGroup($group, $role)[$data['group_gid']];
        $result['name'] = $result['name_' . $this->ci->pg_language->current_lang_id];
        $result['description'] = $result['description_' . $this->ci->pg_language->current_lang_id];
        $add_fields = [$data['group_gid'] . '_group'];
        $periods = $this->ci->Access_Permissions_Groups_Model->getPeriodsList($add_fields, []);
        foreach ($periods as $key => $period) {
            $result['periods'][$key]['id'] = $period['id'];
            $result['periods'][$key]['price'] = $period[$result['gid'] . '_group'];
            $result['periods'][$key]['period'] = $period['period'];
            $result['periods'][$key]['period_str'] = AccessPermissionsModel::formatPeriod($period['period']);
            $result['periods'][$key]['is_selected'] = 0;
            if ($period['id'] == $data['period_id']) {
                $result['periods'][$key]['is_selected'] = 1;
            }
        }
        return $result;
    }

    /**
     * Group permissions
     *
     * @param array $subscriptions
     * @param string $role
     *
     * @return array
     */
    public function permissionsGroup($groups, $role = null)
    {
        $modules = $this->ci->Access_permissions_modules_model->getModulesList([
            'where_in' => ['access' => [$this->ci->Access_permissions_model->roles[$this->session->userdata['auth_type']]]]
        ]);
        foreach ($groups as $group) {
            $roles = is_null($role) ? [$this->getRole($group['gid'])] : [$role];
            foreach ($modules as $module) {
                $postfix = isset($module['method']) ? '_' . $module['method'] : '';
                $text_escape = $this->db->escape('%' . $module['module_gid'] . '_' . $module['module_gid'] . $postfix . '%');
                $params = [
                    'where_in' => ['role' => $roles],
                    'where_sql' =>['(resource_type LIKE ' . $text_escape . ')']
                ];
                $access = $this->getAccessObject($params);
                $groups[$group['gid']]['access'][$module['module_gid']]['name'] = $module['name'];
                $groups[$group['gid']]['access'][$module['module_gid']]['description'] = $module['description'];
                $groups[$group['gid']]['access'][$module['module_gid']]['list'] = self::formatPermissionsGroup($group['gid'], $access);
                $groups[$group['gid']]['access'][$module['module_gid']]['is_available'] = (!empty($groups[$group['gid']]['access'][$module['module_gid']]['list']) && $groups[$group['gid']]['access'][$module['module_gid']]['list'][0]['type'] == 'privilege') ? 1 : 0;
            }
        }
        return $groups;
    }

    /**
     *Format permissions group
     *
     * @param string $group_gid
     * @param array $data
     *
     * @return array
     */
    protected static function formatPermissionsGroup($group_gid, $data)
    {
        $result = [];
        foreach ($data as $value) {
            if ($value['role'] == 'user_' . $group_gid) {
                $result[] = $value;
            } elseif ($value['role'] == 'user') {
                $result[] = $value;
            }
        }
        return $result;
    }

    /**
     * Permissions list
     *
     * @param array $module
     * @param boolean $format
     *
     * @return array
     */
    public function permissionsList($module, $format = false)
    {
        $groups = $this->ci->Users_model->getUserTypesGroups()[UsersModel::GROUPS];
        $roles = ['guest'];
        foreach ($groups as $groups_data) {
            $roles[] = ($groups_data['gid'] != 'default') ? 'user_' . $groups_data['gid'] : 'user';
        }
        $postfix = isset($module['method']) ? '_' . $module['method'] : '';
        $text_escape = $this->db->escape('%' . $module['module_gid'] . '_' . $module['module_gid'] . $postfix . '%');
        $params = [
            'where_in' => ['role' => $roles],
            'where_sql' =>['(resource_type LIKE ' . $text_escape . ')']
        ];
        $return['permissions'] = $this->getAccessObject($params);
        return ($format === true) ? $this->formatPermissions($return, $module) : $return;
    }

    /**
     * Validate permissions
     *
     * @param array $data
     * @param array $module
     * @param boolean $is_check
     *
     * @return array
     */
    public function validatePermissions(array $data, array $module, $is_check = false)
    {
        $result = ['errors' => [], 'data' => []];
        if (!empty($data)) {
            $groups = $this->ci->Users_model->getUserTypesGroups()[UsersModel::GROUPS];
            foreach ($data['list'] as $resource_type => $value)  {
                $text_escape = $this->db->escape('%' . $resource_type . '%');
                foreach ($groups as $groups_data) {
                    $role = ($groups_data['gid'] != 'default') ? 'user_' . $groups_data['gid'] : AccessPermissionsModel::USER;
                    $result['data'][$groups_data['gid']]['attrs']['type'] = ($value[$groups_data['gid']]['status'] == 1) ? self::PRIVILEGE : self::RESTRICTION;
                    $result['data'][$groups_data['gid']]['params']['where_sql'] = ['(resource_type LIKE ' . $text_escape . ')'];
                    $result['data'][$groups_data['gid']]['params']['where_in']['role'] = [$role];
                    if (isset($value[$groups_data['gid']]['count'])) {
                        $result['data'][$groups_data['gid']]['settings'] = self::formatPermissionSettings(
                            $value[$groups_data['gid']]['count'], $role, $module, $result['data'][$groups_data['gid']]['attrs']['type']
                        );
                    }
                    if ($is_check === true) {
                        $access[$role] = $this->getAccessObject($result['data'][$groups_data['gid']]['params']);
                        if (empty($access[$role])) {
                            $role2 = AccessPermissionsModel::USER;
                            $result['data'][$groups_data['gid']]['params']['where_sql'] = ['(resource_type LIKE ' . $text_escape . ')'];
                            $result['data'][$groups_data['gid']]['params']['where_in']['role'] = [$role2];
                            $access[$role2] = $this->getAccessObject($result['data'][$groups_data['gid']]['params']);
                        }
                        $check = $this->checkAccess($access, $role, $result['data'][$groups_data['gid']]['attrs']['type']);
                        if ($check === false) {
                            unset($result['data'][$groups_data['gid']]);
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Format permission settings
     *
     * @param array $data
     * @param string $role
     * @param string $module
     * @param string $type
     *
     * @return array
     */
    protected static function formatPermissionSettings($data, $role, $module, $type)
    {
        $result = [];
         foreach ($data as $method => $count) {
            $result[$method]['attrs']['type'] = $type;
            $result[$method]['attrs']['data'] = serialize([$method => intval($count)]);
            $result[$method]['params']['where_in'] = [
                'role' => [$role],
                'resource_type' => [$module['module_gid'] . '_' . $module['module_gid'] . '_' . $method]
            ];
        }
        return $result;
    }

    /**
     * Check access
     *
     * @param array $access
     * @param array $role
     * @param string $type
     *
     * @return boolean
     */
    public function checkAccess($access, $role, $type)
    {
        if (empty($access[$role])) {
            foreach ($access[AccessPermissionsModel::USER] as $value) {
                $this->savePermissions([
                    'action' => $value['action'],
                    'role' => $role,
                    'type' => $type,
                    'resource_type' => $value['resource_type'],
                    'data' => serialize($value['data']),
                ]);
            }
            return false;
        } else {
            return true;
        }
    }

    /**
     * Permissions format
     *
     * @param array $data
     * @param array $module
     *
     * @return array
     */
    public function formatPermissions(array $data, array $module)
    {
        $result = [];
        if (!empty($data['permissions'])) {
            foreach ($data['permissions'] as $permission) {
                $role_data = explode('_', $permission['role']);
                if (count($role_data) > 1) {
                    array_shift($role_data);
                    $group =  implode('_', $role_data);
                } else {
                    $group = 'default';
                }
                $result[$group]['status'] = $this->getPermissionStatus($permission, $module);
                $result[$group]['permissions'][] = $permission;
            }
        }
        return $result;
    }

    /**
     * Permission status
     *
     * @param array  $permission
     * @param array  $module
     *
     * @return string
     */
    private function getPermissionStatus($permission, $module)
    {
        if (!isset($this->permissions_status[$permission['role']][$module['module_gid']])) {
            if (!empty($permission['data'])) {
                $data = unserialize($permission['data']);
                if (empty($data)) {
                    return $this->access_type[$permission['type']];
                }
                $count = current($data);
                 if ($count > 0 && $permission['type'] == self::PRIVILEGE) {
                     $this->permissions_status[$permission['role']][$module['module_gid']] = self::INCOMPLETE;
                     return self::INCOMPLETE;
                 } else if ($count== 0 && $permission['type'] == self::PRIVILEGE) {
                     return $this->access_type[self::PRIVILEGE];
                 } else {
                     return $this->access_type[self::RESTRICTION];
                 }
            } else {
                return $this->access_type[$permission['type']];
            }
        } else {
            return self::INCOMPLETE;
        }
    }

    /**
     * Save permissions
     *
     * @param array $attrs
     * @param array $params
     *
     * @return void
     */
    public function savePermissions(array $attrs, $params = null)
    {
        if (is_null($params)) {
            $this->ci->db->insert(PERMISSIONS_TABLE, $attrs);
        } else {;
            if (isset($params['where_in'])) {
                foreach ($params['where_in'] as $field => $value) {
                    $this->ci->db->where_in($field, $value);
                }
            }
            if (isset($params['where_sql'])) {
                foreach ($params['where_sql'] as $value) {
                    $this->ci->db->where($value, null, false);
                }
            }
            $this->ci->db->update(PERMISSIONS_TABLE, $attrs);
        }
    }

    /**
     * Save permissions settings
     *
     * @param array $settings
     *
     * return void
     */
    public function savePermissionsSettings(array $settings)
    {
        foreach ($settings as $data) {
            $this->savePermissions($data['attrs'], $data['params']);
        }
    }

    /**
     * Add group
     *
     * @param string $group
     *
     * @return void
     */
    public function addGroup($group)
    {
        $user_acl = $this->getAccessObject(['where' => ['role' => AccessPermissionsModel::USER]]);
        foreach ($user_acl as $key => $acl) {
            $user_acl[$key]['id'] = null;
            $user_acl[$key]['role'] = $acl['role'] . '_' . $group;
        }
        $this->ci->db->insert_batch(PERMISSIONS_TABLE, $user_acl);
    }

    /**
     * Group delete
     *
     * @param string $group
     *
     * @return void
     */
    public function groupDelete($group)
    {
        $this->ci->db->where('role', 'user_' . $group);
        $this->ci->db->delete(PERMISSIONS_TABLE);
    }

    /**
     * Change group
     *
     * @return void
     */
    public function changeGroups($types = [], $groups_data = [])
    {
        $user_types = $types?: $this->ci->Users_model->getUserTypes();
        $groups = $groups_data?: $this->ci->Users_model->getUserTypesGroups()[UsersModel::GROUPS];
        foreach ($groups as $groups_data) {
            foreach ($user_types as $type) {
                $postfix[] = ($groups_data['gid'] != 'default') ? 'user_' . $groups_data['gid'] . '_' . $type : 'user' . '_' . $type;
                if ($groups_data['gid'] != 'default') {
                    $fields[] = $groups_data['gid'] . '_' . $type . '_group';
                }
            }
        }
        $this->ci->db->where_in('role', $postfix);
        $this->ci->db->delete(PERMISSIONS_TABLE);
        $this->changeFieldsPeriodTable($fields);
    }

    /**
     * Change fields period
     *
     * @param array $fields
     *
     * @return void
     */
    private function changeFieldsPeriodTable($fields)
    {
        $this->ci->load->dbforge();
        $fields_exists = $this->ci->db->list_fields(GROUP_PERIOD_TABLE);
        foreach ($fields as $field) {
             if (!in_array($field, $fields_exists)) {
                continue;
            }
            $this->ci->dbforge->drop_column(GROUP_PERIOD_TABLE, $field);
        }
    }

    /**
     * Price groups
     *
     * @return array
     */
    public function getPriceGroups()
    {
        $this->ci->load->model(AccessPermissionsModel::MODULE_GID . '/models/Access_Permissions_Groups_Model');
        $add_fields = [];
        $params = [];
        $groups = $this->ci->Users_model->getUserTypesGroups()[UsersModel::GROUPS];
        foreach ($groups as $group)  {
            if ($group['gid'] != 'default') {
                $add_fields[] = $group['gid'] . '_group';
                $add_fields[] = $group['gid'] . '_label';
                $add_fields[] = $group['gid'] . '_special';
            }
        }
        $periods = $this->ci->Access_Permissions_Groups_Model->getPeriodsList($add_fields, $params);
        foreach ($periods as $key => $period) {
            $periods[$key]['period_str'] = AccessPermissionsModel::formatPeriod($period['period']);
        }
        return $periods;
    }

    /**
     * Period by ID
     *
     * @param integer $id
     * @param array $where
     *
     * @return array
     */
    public function getPeriodById($id, $where)
    {
        $groups = $this->ci->Users_model->getUserTypesGroups($where)[UsersModel::GROUPS];
        foreach ($groups as $group)  {
            $fields[] = $group['gid'] . '_group';
            $fields[] = $group['gid'] . '_label';
            $fields[] = $group['gid'] . '_special';
        }
        if (!is_null($id)) {
            $params = ['where' => ['id' => $id]];
            $this->ci->load->model([
                    AccessPermissionsModel::MODULE_GID . '/models/Access_Permissions_Groups_Model'
             ]);
            return current($this->ci->Access_Permissions_Groups_Model->getPeriodsList($fields, $params));
        }
    }

    /**
     * Validate period
     *
     * @param array $data
     *
     * @return array
     */
    public function validatePeriod($data)
    {
        $result = ['errors' => [], 'data' => []];
        if ($data['period']) {
            $result['data']['period'] = ($data['period'] > 0) ? intval($data['period']) : 1;
        } else {
            $result['errors'][] = l('error_empty_period', AccessPermissionsModel::MODULE_GID);
        }
        $where = ['where' => ['is_default' => 0]];
        $groups = $this->ci->Users_model->getUserTypesGroups($where)[UsersModel::GROUPS];
        foreach ($groups as $group)  {
            if ($data[$group['gid'] .  '_group']) {
                $result['data'][$group['gid'] . '_group'] = ($data[$group['gid'] . '_group'] > 0) ? intval($data[$group['gid'] . '_group']) : 1;
            } else {
                $result['errors'][] = str_replace("[group]", $group['name_' . $this->ci->pg_language->current_lang_id], l('error_empty_price_period', AccessPermissionsModel::MODULE_GID));
            }
        }
        return $result;
    }

    /**
     * Save period
     *
     * @param integer $id
     * @param array $attrs
     *
     * @return integer
     */
    public function savePeriod($id, $attrs)
    {
        if (!$id) {
            $this->ci->db->insert(GROUP_PERIOD_TABLE, $attrs);
            $id = $this->ci->db->insert_id();
        } else {
            $this->ci->db->where('id', $id);
            $this->ci->db->update(GROUP_PERIOD_TABLE, $attrs);
        }
        return $id;
    }

    /**
     * Delete period
     *
     * @param integer $id
     *
     * @return void
     */
    public function deletePeriod($id)
    {
        $this->ci->db->where('id', $id);
        $this->ci->db->delete(GROUP_PERIOD_TABLE);
    }

    /**
     * Add user type
     *
     * @return void
     */
    public function addUserType(){}

}
