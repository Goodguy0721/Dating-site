<?php

namespace Pg\modules\access_permissions\models;

use Pg\modules\users\models\Users_model as UsersModel;
use Pg\modules\users\models\Groups_model as GroupsModel;

use Pg\modules\access_permissions\models\Access_permissions_model as AccessPermissionsModel;

/**
 * Access_permissions module
 *
 * @copyright   Copyright (c) 2000-2016
 * @author  Pilot Group Ltd <http://www.pilotgroup.net/>
 */

define('GROUP_PERIOD_TABLE', DB_PREFIX . 'access_permissions_group_period');

class Access_permissions_groups_model extends GroupsModel
{

    /**
     * Default group gid
     *
     * @var string
     */
    const DEFAULT_GROUP = 'default';

    /**
     * Period attributes
     *
     * @var array
     */
    protected $fields = [
        'id',
        'period',
        'period_type',
        'pay_type',
    ];

    protected $ci;

    /**
     * Class constructor
     *
     * @return Access_permissions_groups_model
     */
    public function __construct()
    {
        parent::__construct();

        $this->ci = &get_instance();
    }

     /**
     * Get Subscription (Users group)
     *
     * @param string $gid
     * @param boolean   $format
     *
     * @return array
     */
    public function getGroupByGid($gid, $format = true)
    {
        $this->ci->load->model('Users_model');
        $result = $this->ci->Users_model->getUserTypesGroups([
            'where' => ['gid' => $gid]
        ]);
        if ($format === true) {
            return $this->formatGroups($result)[$gid];
        }
        return $result[$gid];
    }

    /**
     * Get Subscription (Users group)
     *
     * @param integer $id
     * @param array   $langs_ids
     *
     * @return array
     */
    public function getGroupById($id, $langs_ids = null)
    {
        $this->ci->load->model('Users_model');
        return $this->ci->Users_model->getUserTypesGroups(
            ['where' => ['id' => $id]], $langs_ids
         );
    }

    /**
     * Get active paid group
     *
     * @return array
     */
    public function getActivePaidGroups()
    {
        $this->ci->load->model('Users_model');
        return $this->formatGroupsForServices(
            $this->ci->Users_model->getUserTypesGroups([
                'where' => [
                        'is_active' => 1,
                        'is_default' => 0
                    ]
            ])[UsersModel::GROUPS],
            $this->getPeriodsList()
         );
    }

        /**
     * Get  paid group
     *
     * @return array
     */
    public function getPaidGroups()
    {
        return $this->formatGroups(
            $this->ci->Users_model->getUserTypesGroups([
                'where' => [
                        'is_default' => 0
                    ]
            ])
         );
    }

    /**
     * Get count active paid group
     *
     * @return array
     */
    public function getCountActivePaidGroups()
    {
        return $this->getGroupsCount([
                'where' => [
                        'is_active' => 1,
                        'is_default' => 0
                    ]
            ]);
    }

    /**
     * Validation Subscription(Users Group)
     *
     * @param array $data
     *
     * @return array
     */
    public function validateSubscription($id, $data)
    {
        if (isset($data['gid']) && $data['gid'] == self::DEFAULT_GROUP) {
            $data['is_active'] = 1;
        }
        return $this->validateGroup($id, $data);
    }

    /**
     * Save Subscription(Users Group)
     *
     * @param type $id
     * @param type $data
     *
     * @return integer
     */
    public function saveSubscription($id, $data)
    {
        return $this->saveGroup($id, $data);
    }

    /**
     * Format groups for services
     *
     * @param array $groups
     * @param array $periods
     *
     * @return array
     */
    private function formatGroupsForServices(array $groups, array $periods)
    {
        $result = [];
        foreach ($groups as $group) {
            foreach ($periods as $period) {
                $result['short'][$group['gid'] . '_' . $period['id']] =
                    $group['name_' . $this->ci->pg_language->current_lang_id] . ' ' .
                    AccessPermissionsModel::formatPeriod($period['period']);
                $result['full'][$group['gid'] . '_' . $period['id']]['group_gid'] = $group['gid'];
                $result['full'][$group['gid'] . '_' . $period['id']]['period']['id'] = $period['id'];
                $result['full'][$group['gid'] . '_' . $period['id']]['period']['count'] = $period['period'];
                $result['full'][$group['gid'] . '_' . $period['id']]['period']['price'] = $period[$group['gid'] . '_group'];
                $result['full'][$group['gid'] . '_' . $period['id']]['period']['type'] = $period['period_type'];
                $result['full'][$group['gid'] . '_' . $period['id']]['period']['label'] = $period[$group['gid'] . '_label'];
                $result['full'][$group['gid'] . '_' . $period['id']]['period']['special'] = $period[$group['gid'] . '_special'];
            }
        }
        return $result;
    }

    /**
     * Formating Subscriptions Data
     *
     * @param array $groups_data
     *
     * @return array
     */
    public function formatGroups(array $groups_data)
    {
        $format_groups = $groups_data[UsersModel::GROUPS];
        foreach ($format_groups as $gid => $group) {
            $format_groups[$gid]['current_name']        = $group['name_' . $this->ci->pg_language->current_lang_id];
            $format_groups[$gid]['current_description'] = $group['description_' . $this->ci->pg_language->current_lang_id];
        }
        return $format_groups;
    }

    /**
     * Delete Subscription(Users Group)
     *
     * @param integer $id
     *
     * @return array
     */
    public function deleteSubscription($id)
    {
        if ($id != $this->getDefaultGroupId()) {
            $result = [
                'is_delete' => 1,
                'success' => [l('success_delete_group', AccessPermissionsModel::MODULE_GID)]
            ];
            $this->deleteGroup($id);
        } else {
            $result = [
               'is_delete' => 0,
                'error' => [l('error_default_group_not_deleted', AccessPermissionsModel::MODULE_GID)]
            ];
        }
        return $result;
    }

    /**
     * Change Status Subscription (active/inactive)
     *
     * @param array $data
     *
     * @return array
     */
    public function changeStatusSubscription($data)
    {
        if ($data['status'] == 0 && ($data['id'] == $this->getDefaultGroupId())) {
            $result['error'][] = l('error_default_group_not_is_active', AccessPermissionsModel::MODULE_GID);
        } else {
            $this->saveGroup($data['id'], ['is_active' => $data['status']]);
            $result['success'] = l('success_status_set', AccessPermissionsModel::MODULE_GID);
        }
        return $result;
    }

    /**
     * Add Period
     *
     * @param string $group_gid
     *
     * @return void
     */
    public function addPeriod($group_gid)
    {
        $subscription_type = json_decode($this->ci->Access_permissions_settings_model->getModuleSettings('subscription_type'), true);
        $key = array_search(1, array_column($subscription_type, 'data'));
        $this->addPeriodColumn($group_gid);
        if ($subscription_type[$key]['type'] == 'user_types') {
            $types = $this->ci->Users_model->getUserTypes();
            foreach ($types as $type) {
                $this->addPeriodColumn($group_gid . '_' . $type);
            }
        }
    }

    /**
     * Add period column
     *
     * @param string $prefix
     *
     * @return boolean
     */
    private function addPeriodColumn($prefix)
    {
        $this->ci->load->dbforge();
        return $this->ci->dbforge->add_column(GROUP_PERIOD_TABLE, [
                $prefix . '_group' => ['type' => 'INT', 'constraint' => '10', 'null' => false, 'default' => 1],
                $prefix . '_label' => ['type' => 'varchar', 'constraint' => '255', 'null' => false, 'default' => ''],
                $prefix . '_special' => ['type' => 'tinyint', 'constraint' => '1', 'null' => false, 'default' => 0],
            ]
        );
    }

     /**
     * Add period column
     *
     * @param string $prefix
     *
     * @return boolean
     */
    private function deletePeriodColumn($prefix)
    {
        $this->ci->load->dbforge();
        $this->ci->dbforge->drop_column(GROUP_PERIOD_TABLE, $prefix . '_group');
        $this->ci->dbforge->drop_column(GROUP_PERIOD_TABLE, $prefix . '_label');
        $this->ci->dbforge->drop_column(GROUP_PERIOD_TABLE, $prefix . '_special');
        return true;
    }

    /**
     * Delete Period
     *
     * @param string $group_gid
     *
     * @return void
     */
    public function deletePeriod($group_gid)
    {
        $subscription_type = json_decode($this->ci->Access_permissions_settings_model->getModuleSettings('subscription_type'), true);
        $key = array_search(1, array_column($subscription_type, 'data'));
        $this->deletePeriodColumn($group_gid);
        if ($subscription_type[$key]['type'] == 'user_types') {
            $types = $this->ci->Users_model->getUserTypes();
            foreach ($types as $type) {
                $this->deletePeriodColumn($group_gid . '_' . $type);
            }
        }
    }

    /**
     * Periods list
     *
     * @param array  $add_fields
     * @param array $params
     *
     * @return array
     */
    public function getPeriodsList($add_fields = [], $params = [])
    {
        $fields = !empty($add_fields) ? implode(', ', array_merge($this->fields, $add_fields)) : '*';
        $this->ci->db->select($fields);
        $this->ci->db->from(GROUP_PERIOD_TABLE);
        if (isset($params['where']) && is_array($params['where']) && count($params['where'])) {
            foreach ($params['where'] as $field => $value) {
                $this->ci->db->where($field, $value);
            }
        }
        $this->ci->db->order_by('priority ASC, period ASC');
        return $this->ci->db->get()->result_array();
    }

    /**
     * Get period price
     *
     * @param array $data
     *
     * @return float
     */
    public function getPeriodPrice($data)
    {
        $fields = $this->ci->Access_permissions_settings_model
            ->getAccessData($this->ci->Access_permissions_model->roles[AccessPermissionsModel::USER])
            ->getField($data['group']);
        return current($this->getPeriodsList([$fields], [
            'where' => ['id' => $data['period']]
        ]))[$fields];
    }

}
