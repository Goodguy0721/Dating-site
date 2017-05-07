<?php

namespace Pg\modules\access_permissions\models;

use Pg\Modules\Payments\Models\Payments_model as PaymentsModel;
use Pg\Libraries\Acl\Driver\DbDriver;
use Pg\libraries\EventDispatcher;
use Pg\modules\access_permissions\models\events\EventAccessPermissions;

define('PERMISSIONS_TABLE', DB_PREFIX . DbDriver::PERMISSIONS_TABLE);

/**
 * Access_permissions module
 *
 * @copyright   Copyright (c) 2000-2016
 * @author  Pilot Group Ltd <http://www.pilotgroup.net/>
 */

class Access_permissions_model extends \Model
{
    /**
     * Module GUID
     *
     * @var string
     */
    const MODULE_GID = 'access_permissions';

    /**
     * Payment type as write off from account
     *
     * @var string
     */
    const PAYMENT_TYPE_ACCOUNT = 'account';

    /**
     * Payment type as write off from account and direct payment
     *
     * @var string
     */
    const PAYMENT_TYPE_ACCOUNT_AND_DIRECT = 'account_and_direct';

    /**
     * Payment type as direct payment
     *
     * @var string
     */
    const PAYMENT_TYPE_DIRECT = 'direct';

    /**
     * Authorized user
     *
     * @var string
     */
    const USER = 'user';

    /**
     * Unauthorized user
     *
     * @var string
     */
    const GUEST = 'guest';

    /**
     * Period type in years
     *
     * @var string
     */
    const PERIOD_TYPE_YEARS = 'years';

    /**
     * Period type in months
     *
     * @var string
     */
    const PERIOD_TYPE_MONTHS = 'months';

    /**
     * Period type in days
     *
     * @var string
     */
    const PERIOD_TYPE_DAYS = 'days';

    /**
     * Period type in hours
     *
     * @var string
     */
    const PERIOD_TYPE_HOURS = 'hours';

    /**
     * Date format
     *
     * @var string
     */
    const DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * User Roles
     *
     * @var array
     */
    public $roles   = [
        self::GUEST => 1,
        self::USER => 2,
    ];

    protected $ci;

    /**
     * Class constructor
     *
     * @return AccessPermissionsModel
     */
    public function __construct()
    {
        parent::__construct();

        $this->ci = &get_instance();
    }

    /**
     * Return allowed period types as array
     *
     * @return array
     */
    public function getAllowedPeriodTypes()
    {
        return [
            self::PERIOD_TYPE_HOURS,
            self::PERIOD_TYPE_DAYS,
            self::PERIOD_TYPE_MONTHS,
            self::PERIOD_TYPE_YEARS,
        ];
    }

    /**
     * Return allowed payments types as array
     *
     * @return array
     */
    public function getAllowedPaymentTypes()
    {
        return [
            self::PAYMENT_TYPE_ACCOUNT,
            self::PAYMENT_TYPE_DIRECT,
            self::PAYMENT_TYPE_ACCOUNT_AND_DIRECT,
        ];
    }

    /**
     * Format subscriptions
     *
     * @param array $subscriptions
     * @param array $periods
     *
     * @return array
     */
    public function formatGroups(array $subscriptions, array $periods)
    {
        $this->ci->load->model(self::MODULE_GID . '/models/Access_permissions_users_model');
        $user_group = $this->ci->Access_permissions_users_model->getUserGroup([
            'where' => [
                'id_user' => $this->ci->session->userdata('user_id'),
                'is_active' => 1
            ]
        ], 'gid');
        foreach ($subscriptions as $gid => $subscription) {
            if (!$subscription['is_default']) {
                $subscriptions[$gid]['is_purchased'] = array_search($gid, $user_group);
                foreach ($periods as $key => $period) {
                    $subscriptions[$gid]['periods'][$key]['id'] = $period['id'];
                    $subscriptions[$gid]['periods'][$key]['period'] = $period['period'];
                    $subscriptions[$gid]['periods'][$key]['period_str'] = self::formatPeriod($period['period']);
                    $subscriptions[$gid]['periods'][$key]['price'] = $period[$gid . '_group'];
                    $subscriptions[$gid]['periods'][$key]['label'] = $period[$gid . '_label'];
                    $subscriptions[$gid]['periods'][$key]['special'] = $period[$gid . '_special'];
                }
            }
        }
        return $this->ci->Access_permissions_settings_model
            ->getAccessData($this->ci->session->userdata['auth_type'])
            ->permissionsGroup($subscriptions);
    }

    /**
     * Add column
     *
     * @param string $group_gid
     * @param integer $access
     *
     * @return void
     */
    public function callbackGroupAdd($group_gid)
    {
        if ($group_gid) {
            $this->ci->load->model(self::MODULE_GID . '/models/Access_permissions_settings_model');
            $this->Access_permissions_settings_model
                ->getAccessData($this->roles[self::USER])->addGroup($group_gid);
        }
    }

    /**
     * Delete column
     *
     * @param string $group_gid
     * @param integer $access
     *
     * @return void
     */
    public function callbackGroupDelete($group_gid)
    {
        if ($group_gid) {
            $this->ci->load->model(self::MODULE_GID . '/models/Access_permissions_settings_model');
            $this->ci->Access_permissions_settings_model
                ->getAccessData($this->roles[self::USER])->groupDelete($group_gid);
        }
    }

     /**
     * Add new user type
     *
     * @param string $gid user type GUID
     *
     * @return void
     */
    public function calbackAddUserType($gid = false)
    {
        if ($gid !== false) {
            $this->ci->load->model(self::MODULE_GID . '/models/Access_permissions_settings_model');
            $this->ci->Access_permissions_settings_model
                ->getAccessData($this->roles[self::USER])->addUserType($gid);
        }
    }

    /**
     * Format Ammount
     *
     * @param int $period
     *
     * @return string
     */
    public static function formatPeriod($period)
    {
        $num = $period % 100;
        if ($num > 19) {
            $num = $num % 10;
        }
        switch ($num) {
            case 1: {
                return $period . '&nbsp;' . l('field_format_day', self::MODULE_GID);
            }
            case 2: case 3: case 4: {
                return $period . '&nbsp;' . l('field_format_days', self::MODULE_GID);
            }
            default: {
                return $period . '&nbsp;' . l('field_days', self::MODULE_GID);
            }
        }
    }

    /**
     * Payment Data
     *
     * @param array $data
     *
     * @return array
     */
    public function getPaymenData(array $data)
    {
        $return = ['errors' => [], 'data' => []];
        $this->ci->load->model([
            self::MODULE_GID . '/models/Access_permissions_groups_model',
            self::MODULE_GID . '/models/Access_permissions_settings_model',
            PaymentsModel::MODULE_GID . '/models/Payment_systems_model',
            'Users_payments_model'
        ]);
        $return['data']['user_account'] = $this->ci->Users_payments_model->get_user_account($this->ci->session->userdata['user_id']);
        if (!empty($data['group_gid'])) {
            $return['data']['group'] = $this->ci->Access_permissions_groups_model->getGroupByGid($data['group_gid']);
            if (!empty($data['period_id'])) {
                $where = ['where' => ['is_default' => 0]];
                $period = $this->ci->Access_permissions_settings_model
                    ->getAccessData($this->ci->session->userdata['auth_type'])->getPeriodById($data['period_id'], $where);
                $return['data']['period'] = [
                    'id' => $period['id'],
                    'period' => $period['period'],
                    'period_str' => self::formatPeriod($period['period']),
                    'price' => $period[$data['group_gid'] . '_group'],
                    'label' => $period[$data['group_gid'] . '_label'],
                    'special' => $period[$data['group_gid'] . '_special'],
                 ];
                $return['data']['disable_account_pay'] = false;
                if ($data['pay_system_gid'] === 'account') {
                    if ($return['data']['user_account'] < $return['data']['period']['price']) {
                        $return['data']['disable_account_pay'] = true;
                        $return['data']['deficit'] = $return['data']['period']['price']-$return['data']['user_account'];
                    }
                }
            } else {
                $return['errors'][] = l('error_unknown_period', self::MODULE_GID);
            }
        } else {
            $return['errors'][] = l('error_unknown_group', self::MODULE_GID);
        }
        if (!empty($data['pay_system_gid'])) {
            if ($data['pay_system_gid'] != 'account') {
                $return['data']['pay_system'] = $this->ci->Payment_systems_model->get_system_by_gid($data['pay_system_gid']);
            } else {
                $return['data']['pay_system'] = [
                    'gid' => 'account',
                    'name' => l('btn_pay_account', 'services')
                ];
            }
        } else {
            $return['errors'][] = l('error_unknown_pay_system', self::MODULE_GID);
        }
        return $return;
    }



    /**
     * Group Payment
     *
     * @param array $data
     * @param integer $user_id
     *
     * @return array
     */
    public function groupPayment(array $data, $user_id)
    {
        $this->ci->load->model([
            self::MODULE_GID . '/models/Access_permissions_settings_model',
            'Users_payments_model', 'users/models/Auth_model'
         ]);
        $group_data = $this->ci->Access_permissions_settings_model
            ->getAccessData($this->ci->session->userdata['auth_type'])
            ->getGroupData($data['group_gid'], ['where' => ['id' => $data['period_id']]]);
        if ($data['pay_system_gid'] == 'account') {
            $result = $this->accountPayment($group_data, $user_id);
        } else {
            $result = $this->systemPayment($group_data, $user_id, $data['pay_system_gid']);
        }
        $this->ci->Auth_model->update_user_session_data($user_id);
        return $result;
    }

    /**
     * Payment from the user's account
     *
     * @param array $group_data
     * @param integer $user_id
     *
     * @return array
     */
    private function accountPayment(array $group_data, $user_id)
    {
        $message = l('field_membership_payment', self::MODULE_GID) . ': ' . $group_data["current_name"];
        $payment_result = $this->ci->Users_payments_model->write_off_user_account($user_id, $group_data['period'][$group_data['gid'] . '_group'], $message);
        if ($payment_result === true) {
            $is_apply = $this->applyGroup($group_data, $user_id);
            if ($is_apply === true) {
                $this->updateServicesByGroup($group_data, $user_id);
                $this->addEventPayment($group_data, $user_id);
                return [
                    'success' => [l('success_payment_completed', self::MODULE_GID)],
                    'data' => $group_data
                ];
            } else {
                return [
                    'error' => [l('error_system', self::MODULE_GID)]
                ];
            }
        } else {
            return [
                'error' => [l('error_system', self::MODULE_GID)]
            ];
        }
    }

    /**
     * Payment for by system
     *
     * @param array $group_data
     * @param integer $user_id
     * @params string $system_gid
     *
     * @return array
     */
    private function systemPayment(array $group_data, $user_id, $system_gid)
    {
        $this->ci->load->model(PaymentsModel::MODULE_GID . "/models/Payment_currency_model");
        $currency_gid = $this->ci->Payment_currency_model->default_currency["gid"];
        $payment_data = [
            'name' => l('field_membership_payment', self::MODULE_GID) . ': ' . $group_data["current_name"],
            'group_gid' => $group_data['gid'],
            'period_id' => $group_data['period']['id']
        ];
        $this->ci->load->helper('payments');
        return send_payment(self::MODULE_GID, $user_id, $group_data['period'][$group_data['gid'] . '_group'], $currency_gid, $system_gid, $payment_data, true);
    }

    /**
     * Update Services by group
     *
     * @param string $group
     *
     * @return void
     */
    private function updateServicesByGroup($group, $user_id)
    {
        $this->ci->load->model(self::MODULE_GID . '/models/Access_permissions_settings_model');
        $role = $this->ci->Access_permissions_settings_model
            ->getAccessData($this->ci->session->userdata['auth_type'])->getRole($group['gid']);
        $permissions = $this->ci->Access_permissions_settings_model
            ->getAccessData($this->ci->session->userdata['auth_type'])
            ->permissionsGroup([$group], $role)[$group['gid']]['access'];
        foreach ($permissions as $module => $data) {
            if (class_exists(NS_MODULES . $module . '\\models\\' . ucfirst($module) . 'Model') !== false) {
                $model = ucfirst($module) . '_model';
                $this->ci->load->model($model);
                foreach ($data['list'] as $access) {
                    if (!empty($access['data'])) {
                        $access['data'] = unserialize($access['data']);
                        foreach ($access['data'] as $action => $count) {
                            $method = 'set' . ucfirst($action) . 'Count';
                            $this->ci->{$model}->{$method}($user_id, $count);
                        }
                    }
                }
            }
        }
    }

    /**
     * Update group status
     *
     * @param array $data group data
     * @param integer $user_id
     *
     * @return void
     */
    public function applyGroup(array $data, $user_id)
    {
        $this->ci->load->model(self::MODULE_GID . '/models/Access_permissions_users_model');
        $user_group = current($this->ci->Access_permissions_users_model->getUserGroupList(null, [
            'where' => ['id_user' => $user_id, 'group_gid' => $data['gid']],
        ])) ?: [];
        $time = $this->getApplyTime($data, $user_group);
        $user_group_id = $user_group['id'] ?: null;
        $save_data = [
            'id_user' => $user_id,
            'group_gid' => $data['gid'],
            'id_period' => $data['period']['id'],
            'data' => serialize($data),
            'is_active' => 1,
            'date_expired' => $time['expired']
        ];
        if (!empty($time['activated'])) {
            $save_data['date_activated'] = $time['activated'];
        }
        return $this->ci->Access_permissions_users_model->saveUserGroup($user_group_id, $save_data);
    }

    /**
     * Get Apply Time
     *
     * @param array $data
     * @param array $user_group
     *
     * @return array
     */
    private function getApplyTime(array $data, array $user_group)
    {
        if (in_array($data['period']['period_type'], $this->getAllowedPeriodTypes())) {
            if (!empty($user_group)) {
                $date = new \DateTime($user_group['date_expired']);
                $date->add(date_interval_create_from_date_string($data['period']['period'] . ' ' . $data['period']['period_type']));
                $time['expired'] = $date->format(self::DATE_FORMAT);
                $time['activated'] = $user_group['date_activated'];
            } else {
                $tstamp = strtotime('+' . $data['period']['period'] . ' ' . $data['period']['period_type']);
                $time['expired'] = date(self::DATE_FORMAT, $tstamp);
                $time['activated'] = date(self::DATE_FORMAT);
            }
        } else {
            $time['expired'] = null;
            $time['activated'] = date(self::DATE_FORMAT);
        }
        return $time;
    }

    /**
     * Add Event Payment
     *
     * @param string $group_gid
     * @param integer $user_id
     * @param float $price
     *
     * @return void
     */
    private function addEventPayment($group, $user_id)
    {
        $event_handler = EventDispatcher::getInstance();
        $event = new EventAccessPermissions();
        $payment_data = [
            'payment_type_gid' => self::MODULE_GID,
            'payment_data' => [
                'group_gid' => $group['gid'],
                'period' => $group['period']['id'],
            ],
            'id_user' => $user_id,
            'period' => $group['period'][$group['gid'] . '_group'],
        ];
        $event->setData($payment_data);
        $event_handler->dispatch('users_buy_group', $event);
    }

    /**
     * Update group payment status
     *
     * callback method for payment module
     *
     * Expected status values: 1, 0, -1
     *
     * @param array   $payment        payment data
     * @param integer $status payment status
     *
     * @return void
     */
    public function paymentStatus($payment, $status)
    {
        if ($status != 1) {
            return;
        }
        $this->ci->load->model(self::MODULE_GID . '/models/Access_permissions_settings_model');
        $group = $this->ci->Access_permissions_settings_model
            ->getAccessData($this->ci->session->userdata['auth_type'])
            ->getGroupData($payment['payment_data']['group_gid'], ['where' => ['id' => $payment["payment_data"]['period_id']]]);
        $this->applyGroup($group, $payment["id_user"]);
    }

    /**
     * Format roles
     *
     * @param array $roles
     *
     * @return array
     */
    public function formatRoles($roles)
    {
        $this->ci->load->model(self::MODULE_GID . '/models/Access_permissions_settings_model');
        $type = $this->ci->Access_permissions_settings_model->getSubscriptionType(AccessPermissionsSettingsModel::TYPE);
        if ($type == 'user_types') {
            $user_type = $this->ci->session->userdata('user_type');
            foreach ($roles as $role) {
                if (!empty($role)) {
                    $data[] = $role . '_' . $user_type;
                }
            }
            return $data;
        } else {
            foreach ($roles as $role) {
                if (!empty($role)) {
                    $data[] = $role;
                }
            }
            return $data;
        }
    }

}
