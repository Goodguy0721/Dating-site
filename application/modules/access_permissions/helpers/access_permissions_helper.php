<?php

/**
 * Access_permissions module
 *
 * @package PG_Dating
 * @copyright   Copyright (c) 2000-2016 PG Dating Pro - php dating software
 * @author  Pilot Group Ltd <http://www.pilotgroup.net/>
 */

namespace Pg\modules\access_permissions\helpers {

    use Pg\modules\access_permissions\models\Access_permissions_model as AccessPermissionsModel;

    if (!function_exists('subscriptionType')) {

        /**
         *  Block subscription type
         *
         * @param array $params
         *
         * @return string
         */
        function subscriptionType($params = [])
        {
            if ($params['role'] != 'guest') {
                $ci = &get_instance();
                $ci->load->model(AccessPermissionsModel::MODULE_GID . '/models/Access_permissions_groups_model');
                $groups = $ci->Access_permissions_groups_model->formatGroups(
                    $ci->Users_model->getUserTypesGroups()
                );
                $ci->view->assign('role_data', $params);
                $ci->view->assign('groups', $groups);
                return $ci->view->fetch('helper_subscription_type', 'admin', AccessPermissionsModel::MODULE_GID);
            }
            return false;
        }
    }

    if (!function_exists('permissions')) {

        /**
         * Block access rule
         *
         * @param array $params
         *
         * @return string
         */
        function permissions($params = [])
        {
            $ci = &get_instance();
            $ci->load->model([
                AccessPermissionsModel::MODULE_GID . '/models/Access_permissions_modules_model',
                AccessPermissionsModel::MODULE_GID . '/models/Access_permissions_settings_model',
            ]);
            $role = trim(strip_tags($params['role']));
            $user_type = isset($params['type']) ? trim(strip_tags($params['type'])) : null;
            $access = !empty($role) ? [$ci->Access_permissions_model->roles[$role]] : $ci->Access_permissions_model->roles;
            $access_sections = $ci->Access_permissions_modules_model->getModulesList(['where_in' => ['access' => $access]]);
            $ci->Access_permissions_settings_model->getAccessData(
                    $ci->Access_permissions_model->roles[$params['role']]
                )->user_type = $user_type;
            foreach ($access_sections as $key => $module) {
                $module['user_type'] = $user_type;
                $access_sections[$key]['status_access'] = $ci->Access_permissions_settings_model
                    ->getAccessData($module['access'])
                    ->permissionsList($module, true);
            }
            $ci->view->assign('user_type', $user_type);
            $ci->view->assign('access_sections', $access_sections);
            return $ci->view->fetch('helper_permissions', 'admin', AccessPermissionsModel::MODULE_GID);
        }
    }

    if (!function_exists('validityPeriods')) {

        /**
         *  Block validity periods
         *
         * @param array $params
         *
         * @return string
         */
        function validityPeriods($params = [])
        {
            if ($params['role'] != AccessPermissionsModel::GUEST) {
                $ci = &get_instance();
                $user_type = !empty($params['type']) ? $params['type'] : '';
                $periods = $ci->Access_permissions_settings_model->getAccessData(
                        $ci->Access_permissions_model->roles[$params['role']]
                    )->getPriceGroups($user_type);
                $ci->view->assign('periods', $periods);
                return $ci->view->fetch('helper_validity_periods', 'admin', AccessPermissionsModel::MODULE_GID);
            }
            return false;
        }
    }

    if (!function_exists('selectionPeriod')) {

        /**
         *  Block validity periods
         *
         * @param array $params
         *
         * @return string
         */
        function selectionPeriod($params = [])
        {
            $ci = &get_instance();
            $periods = $params['periods'];
            $width = 100 / count($periods);
            $selected = false;
            foreach ($periods as $id => $period) {
                $periods[$id]['width'] = $width;
                $periods[$id]['class'] = ($selected === false) ? 'selected' : 'empty';
                if ($period['is_selected']) {
                    $selected = true;
                }
            }
            $ci->load->model('payments/models/Payment_systems_model');
            $billing_systems = $ci->Payment_systems_model->get_active_system_list();
            $ci->view->assign('billing_systems', $billing_systems);
            $ci->view->assign('periods', $periods);
            return $ci->view->fetch('helper_selection_period', 'user', AccessPermissionsModel::MODULE_GID);
        }
    }

    if (!function_exists('getUserGroup')) {

        /**
         *  Get user group
         *
         * @param array $params
         *
         * @return string
         */
        function getUserGroup($params = [])
        {
            $ci = &get_instance();
            $ci->load->model([
                AccessPermissionsModel::MODULE_GID . '/models/Access_permissions_users_model',
                AccessPermissionsModel::MODULE_GID . '/models/Access_permissions_groups_model'
            ]);
            $group = $ci->Access_permissions_users_model->getUserGroup([
                'where' => [
                    'id_user' => $params['id_user'],
                    'is_active' => 1
                ]
            ], 'current_name');
            return !empty($group) ? implode(', ', $group) :
                $ci->Access_permissions_groups_model->getGroupByGid(
                    \Pg\modules\access_permissions\models\AccessPermissionsGroupsModel::DEFAULT_GROUP, true
                )['current_name'];
        }
    }

    if (!function_exists('isCount')) {

        /**
         *  Is modules
         *
         * @param array $params
         *
         * @return array
         */
        function isCount($params = [])
        {
            $ci = &get_instance();
            $ci->load->model(AccessPermissionsModel::MODULE_GID . '/models/Access_permissions_modules_model');
            $access = isset($params['data']['access']) ? $params['data']['access'] : $ci->Access_permissions_model->roles[AccessPermissionsModel::USER];
            $role = $ci->Access_permissions_settings_model->getAccessData($access)->getRole($params['group_gid']);
            $where  = ['where' => ['module_gid' => $params['data']['module_gid']]];
            if (!empty($params['data']['method'])) {
                $where['where']['method'] = $params['data']['method'];
            }
            $module = current($ci->Access_permissions_modules_model->getModulesList($where));
            $return = [];
            if (isset($module['data'])) {
                foreach ($params['permissions'] as $permission) {
                    foreach ($module['data'] as $method => $data) {
                        if ($permission['resource_type'] == $module['module_gid'] . '_' . $module['module_gid'] . '_' . $method
                            && $permission['role'] == $role) {
                            $return[$method]['name']  = $data['name'];
                            $return[$method]['count'] = unserialize($permission['data'])[$method] ? : '';
                        }
                    }
                }
            }
            return $return;
        }
    }

    if (!function_exists('jsData')) {

        /**
         *  JavaScript Data
         *
         * @param array $params
         *
         * @return string
         */
        function jsData($params = [])
        {
            $ci = &get_instance();
            $ci->view->assign('properties', $params);
            return $ci->view->fetch('helper_js_data', 'user', AccessPermissionsModel::MODULE_GID);
        }
    }

    if (!function_exists('additionalMenu')) {

        /**
         *  Additional Menu
         *
         * @param array $params
         *
         * @return string
         */
        function additionalMenu($params = [])
        {
            $ci = &get_instance();
            $ci->load->model('Properties_model');
            $types = $ci->pg_language->ds->get_reference(
                $ci->Properties_model->module_gid,
                'user_type',
                $ci->pg_language->current_lang_id
             )['option'];
            $user_type = !empty($params['type']) ? $params['type'] : null;
            $ci->view->assign('user_type', $user_type);
            $ci->view->assign('types', $types);
            return $ci->view->fetch('additional_menu', 'admin', AccessPermissionsModel::MODULE_GID);
        }
    }

}

namespace {

    if (!function_exists('subscriptionType')) {

        function subscriptionType($params)
        {
            return Pg\modules\access_permissions\helpers\subscriptionType($params);
        }
    }
    if (!function_exists('permissions')) {

        function permissions($params)
        {
            return Pg\modules\access_permissions\helpers\permissions($params);
        }
    }
    if (!function_exists('validityPeriods')) {

        function validityPeriods($params)
        {
            return Pg\modules\access_permissions\helpers\validityPeriods($params);
        }
    }
    if (!function_exists('selectionPeriod')) {

        function selectionPeriod($params)
        {
            return Pg\modules\access_permissions\helpers\selectionPeriod($params);
        }
    }
    if (!function_exists('getUserGroup')) {

        function getUserGroup($params)
        {
            return Pg\modules\access_permissions\helpers\getUserGroup($params);
        }
    }
    if (!function_exists('isCount')) {

        function isCount($params)
        {
            return Pg\modules\access_permissions\helpers\isCount($params);
        }
    }
    if (!function_exists('jsData')) {

        function jsData($params)
        {
            return Pg\modules\access_permissions\helpers\jsData($params);
        }
    }

    if (!function_exists('additionalMenu')) {

        function additionalMenu($params)
        {
            return Pg\modules\access_permissions\helpers\additionalMenu($params);
        }
    }

}
