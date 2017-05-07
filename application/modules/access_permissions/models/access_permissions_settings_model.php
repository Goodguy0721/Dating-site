<?php

namespace Pg\modules\access_permissions\models;

use Pg\modules\access_permissions\models\Access_permissions_model as AccessPermissionsModel;

/**
 * Access_permissions module
 *
 * @copyright   Copyright (c) 2000-2016
 * @author  Pilot Group Ltd <http://www.pilotgroup.net/>
 */


class Access_permissions_settings_model extends \Model
{

    /**
     * Subscription type
     *
     * @var string
     */
    const TYPE = 'subscription_type';

     /**
     * Module settings
     *
     * @var array
     */
    private $settings = [];

    /**
     * Admin top menu settings
     *
     * @var array
     */
    public $admin_settings_menu = [
        'all_users' => 'admin_access_menu',
        'user_types' => 'admin_user_type_access_menu',
    ];

    /*
     * Path to the folder 'access'
     *
     * @var string
     */
    public $path = 'access/';

    /**
     *Site access type
     *
     * @var string
     */
    public $access_type = '';

    /**
     * Link to Access Data object
     *
     * @var object
     */
    public $access_data;

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
     * Get module settings
     *
     * @param string $gid
    *
     * @return boolean/string/array
     */
    public function getModuleSettings($gid = null)
    {
        if (!is_null($gid)) {
            return isset($this->settings[$gid]) ? $this->settings[$gid] : $this->ci->pg_module->get_module_config(AccessPermissionsModel::MODULE_GID, $gid);
        }
        return false;
    }

    /**
     * Subscription type
     *
     * @return string
     */
    public function getSubscriptionType()
    {
        $subscription_type = json_decode($this->getModuleSettings('subscription_type'), true);
        $key = array_search(1, array_column($subscription_type, 'data'));
        return $subscription_type[$key]['type'];
    }

    /**
     * Save module settings
     *
     * @param array $data
     *
     * @return void
     */
    public function saveModuleSettings(array $data)
    {
        if (!empty($data)) {
            $this->ci->pg_module->set_module_config(AccessPermissionsModel::MODULE_GID, $data['gid'], $data['value']);
            $this->settings[$data['gid']] = $data['value'];
        }
    }

    /**
     * Save Subscription Type
     *
     * @param array $data
     *
     * @return void
     */
    public function saveSubscriptionType(array $data)
    {
        $settings = json_decode($this->getModuleSettings(self::TYPE), true);
        foreach ($settings as $key => $value) {
            $settings[$key]['data'] = ($value['type'] == $data['type']) ? $data['data'] : 0;
        }
        $this->saveModuleSettings([
            'gid' => self::TYPE,
            'value' => json_encode($settings)
        ]);
    }

    /**
     * Get type menu
     *
     * @return array
     */
    public function menuType()
    {
        return json_decode($this->getModuleSettings(self::TYPE));
    }

    /**
     * Access data
     *
     * @param integer $access
     *
     * @return object
     */
    public function getAccessData($access = 1)
    {
        if ($this->access_data) {
            return $this->access_data;
        } else {
            if ($access == 1) {
                $this->ci->load->model(AccessPermissionsModel::MODULE_GID . '/models/' . $this->path . 'Guests');
                $this->access_data = $this->ci->Guests;
            } else {
                if (empty($this->access_type)) {
                    $settings = json_decode($this->getModuleSettings(self::TYPE), true);
                    foreach ($settings as $value) {
                        if ($value['data'] == 1) {
                            $this->access_type = $value['type'];
                        }
                    }
                }
                if ($this->access_type == 'user_types') {
                    $this->ci->load->model(AccessPermissionsModel::MODULE_GID . '/models/' . $this->path . 'User_Types', 'User_Types');
                    $this->ci->User_Types->user_type = $this->getUserType();
                    $this->access_data = $this->ci->User_Types;
                } else {
                    $this->ci->load->model(AccessPermissionsModel::MODULE_GID . '/models/' . $this->path . 'Registered', 'Registered');
                    $this->access_data = $this->ci->Registered;
                }
            }
            return $this->access_data;
        }
    }

    /**
     * Get user type
     * @return boolean/string
     */
    private function getUserType()
    {
        if ($this->session->userdata['auth_type'] == 'user') {
            return $this->session->userdata['user_type'];
        }
        return false;
    }

}
