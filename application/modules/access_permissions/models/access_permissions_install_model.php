<?php

namespace Pg\modules\access_permissions\models;

use Pg\Libraries\Setup;
use Pg\Libraries\Acl;

use Pg\modules\access_permissions\models\Access_permissions_model as AccessPermissionsModel;

/**
 * Access_permissions module
 *
 * @copyright   Copyright (c) 2000-2016
 * @author  Pilot Group Ltd <http://www.pilotgroup.net/>
 */
class Access_permissions_install_model extends \Model
{
    protected $ci;

    /**
     * Module data Access_permissions object
     *
     * @var array
     */
    protected $modules_data;

    /**
     * Demo content Access_permissions object
     *
     * @var array
     */
    protected $demo_content;

    /**
     * Access permissions list
     *
     * @var array
     */
    protected $access_permissions;

    /**
     * Access Control List
     *
     * @var array
     */
    protected $acl_data;

    /**
     * Class constructor
     *
     * @return AccessPermissionsInstallModel
     */
    public function __construct()
    {
        parent::__construct();

        $this->ci = &get_instance();

        $this->modules_data = Setup::getModuleData(
                AccessPermissionsModel::MODULE_GID, Setup::TYPE_MODULES_DATA
        );
        $this->demo_content = Setup::getModuleData(
                AccessPermissionsModel::MODULE_GID, Setup::TYPE_DEMO_CONTENT
        );
        $this->access_permissions = Setup::getModuleData(
                AccessPermissionsModel::MODULE_GID, Setup::TYPE_ACCESS_PERMISSIONS
        );
        $this->acl_data = Setup::getModuleData(
                AccessPermissionsModel::MODULE_GID, Setup::TYPE_ACL
        );
    }

    /**
     * Install data of menu module
     *
     * @return void
     */
    public function install_menu()
    {
        $this->ci->load->helper('menu');
        foreach ($this->modules_data['menu'] as $gid => $menu_data) {
            $this->modules_data['menu'][$gid]['id'] = linked_install_set_menu($gid,
                $menu_data['action'], $menu_data['name']);
            linked_install_process_menu_items($this->modules_data['menu'],
                'create', $gid, 0, $this->modules_data['menu'][$gid]['items']);
        }
        if (!empty($this->modules_data['menu_indicators'])) {
            $this->ci->load->model('menu/models/Indicators_model');
            foreach ($this->modules_data['menu_indicators'] as $data) {
                $this->ci->Indicators_model->save_type(null, $data);
            }
        }
    }

    /**
     * Import languages of menu module
     *
     * @param array $langs_ids languages identifiers
     *
     * @return void
     */
    public function install_menu_lang_update($langs_ids = null)
    {
        if (empty($langs_ids)) {
            return false;
        }
        $langs_file = $this->ci->Install_model->language_file_read(
            AccessPermissionsModel::MODULE_GID, 'menu', $langs_ids
        );
        if (!$langs_file) {
            log_message('info', 'Empty menu langs data');
            return false;
        }
        $this->ci->load->helper('menu');
        foreach ($this->modules_data['menu'] as $gid => $menu_data) {
            linked_install_process_menu_items(
                $this->modules_data['menu'], 'update', $gid, 0,
                $this->modules_data['menu'][$gid]['items'], $gid, $langs_file
            );
        }
        if (!empty($this->modules_data['menu_indicators'])) {
            $langs_file = $this->ci->Install_model->language_file_read(
                AccessPermissionsModel::MODULE_GID, 'indicators', $langs_ids
            );
            if (!$langs_file) {
                log_message('info', '(resumes) Empty indicators langs data');
                return false;
            } else {
                $this->ci->load->model('menu/models/Indicators_model');
                $this->ci->Indicators_model->update_langs($this->modules_data['menu_indicators'], $langs_file, $langs_ids);
            }
        }
        return true;
    }

    /**
     * Export languages of menu module
     *
     * @param array $langs_ids languages identifiers
     *
     * @return array
     */
    public function install_menu_lang_export($langs_ids = null)
    {
        if (empty($langs_ids)) {
            return false;
        }
        $this->ci->load->helper('menu');
        $return = [];
        foreach ($this->modules_data['menu'] as $gid => $menu_data) {
            $temp   = linked_install_process_menu_items($this->modules_data['menu'],
                'export', $gid, 0, $this->modules_data['menu'][$gid]['items'],
                $gid, $langs_ids);
            $return = array_merge($return, $temp);
        }
        if (!empty($this->modules_data['menu_indicators'])) {
            $this->ci->load->model('menu/models/Indicators_model');
            $this->ci->Indicators_model->export_langs($this->modules_data['menu_indicators'], $langs_ids);
        }
        return ['menu' => $return];
    }

    /**
     * Uninstall data of menu module
     *
     * @return void
     */
    public function deinstallMenu()
    {
        $this->ci->load->helper('menu');
        foreach ($this->modules_data['menu'] as $gid => $menu_data) {
            if ($menu_data['action'] == 'create') {
                linked_install_set_menu($gid, 'delete');
            } else {
                linked_install_delete_menu_items($gid,
                    $this->modules_data['menu'][$gid]['items']);
            }
        }
        if (!empty($this->modules_data['menu_indicators'])) {
            $this->ci->load->model('menu/models/Indicators_model');
            foreach ($this->modules_data['menu_indicators'] as $data) {
                $this->ci->Indicators_model->delete_type($data['gid']);
            }
        }
    }

    /**
     * Install data of payments module
     *
     * @return void
     */
    public function install_payments()
    {
        $this->ci->load->model('Payments_model');
        foreach ($this->modules_data['payment_types'] as $payment_type) {
            $this->ci->Payments_model->save_payment_type(null, [
                'gid'=> $payment_type['gid'],
                'callback_module' => $payment_type['callback_module'],
                'callback_model'  => $payment_type['callback_model'],
                'callback_method' => $payment_type['callback_method'],
            ]);
        }
    }

    /**
     * Import data of payment module depended on language
     *
     * @param array $langs_ids languages identifiers
     *
     * @return boolean
     */
    public function install_payments_lang_update($langs_ids = null)
    {
        if (empty($langs_ids)) {
            return false;
        }
        $langs_file = $this->ci->Install_model->language_file_read(AccessPermissionsModel::MODULE_GID, 'payments', $langs_ids);
        if (!$langs_file) {
            log_message('info', 'Empty payments langs data (memberships)');
            return false;
        }
        $this->ci->load->model('Payments_model');
        $this->ci->Payments_model->update_langs($this->modules_data['payment_types'], $langs_file, $langs_ids);
        return true;
    }

    /**
     * Export data of payment module depended on language
     *
     * @param array $langs_ids languages identifiers
     *
     * @return array
     */
    public function install_payments_lang_export($langs_ids = null)
    {
        $this->ci->load->model('Payments_model');
        return [
            "payments" => $this->ci->Payments_model->export_langs($this->modules_data['payment_types'], $langs_ids)
        ];
    }

    /**
     * Uninstall data of payments module
     *
     * @return void
     */
    public function deinstall_payments()
    {
        $this->ci->load->model('Payments_model');
        foreach ($this->modules_data['payment_types'] as $payment_type) {
            $this->ci->Payments_model->delete_payment_type_by_gid($payment_type['gid']);
        }
    }

    /**
     * Install cronjob data
     *
     * @return void
     */
    public function install_cronjob()
    {
        $this->ci->load->model('Cronjob_model');
        foreach ($this->modules_data['cron_data'] as $cronjob) {
            $this->ci->Cronjob_model->save_cron(null, $cronjob);
        }
    }

    /**
     * Uninstall cronjob data
     *
     * @return void
     */
    public function deinstall_cronjob()
    {
        $this->ci->load->model('Cronjob_model');
        $this->ci->Cronjob_model->delete_cron_by_param([
            'where' => ['module' => AccessPermissionsModel::MODULE_GID]
        ]);
    }

    /**
     * Install module data
     *
     * @return void
     */
    public function _arbitrary_installing()
    {
        //$this->addACL();
        $this->installAccessPermissionsList();
        $this->installDemoContent();
    }

    /**
     * Deinstall module data
     *
     * @return void
     */
    public function _arbitrary_deinstalling()
    {
        $this->ci->load->model([
            AccessPermissionsModel::MODULE_GID . '/models/Access_permissions_groups_model',
            'Users_model'
        ]);
        $data = [
            'groups' => $this->ci->Access_permissions_groups_model->getPaidGroups(),
            'user_types' => $this->ci->Users_model->getUserTypes()
        ];
        $roles = [];
        foreach ($data['groups'] as $group) {
            $roles[] = 'user_' . $group['gid'];
            $this->ci->Access_permissions_groups_model->deleteSubscription($group['id']);
            foreach ($data['user_types'] as $type) {
                $roles[] = 'user_' . $group['gid'] . '_' . $type;
            }
        }
        if (!empty($roles)) {
            $this->ci->db->where_in('role', $roles);
            $this->ci->db->delete(PERMISSIONS_TABLE);
        }
        $this->ci->db->where('type', \BeatSwitch\Lock\Permissions\Restriction::TYPE);
        $this->ci->db->update(PERMISSIONS_TABLE,
            ['type' => \BeatSwitch\Lock\Permissions\Privilege::TYPE]);
    }

    /**
     * Add Access Control List
     *
     * @return void
     */
    protected function addACL()
    {
        if (!empty($this->acl_data)) {
            $action_view_page = new \Pg\Libraries\Acl\Action\ViewPage();
            foreach ($this->acl_data as $module => $data) {
                foreach ($data[AccessPermissionsModel::USER] as $method) {
                    $res_page = new \Pg\Libraries\Acl\Resource\Page(
                        ['module' => $module, 'controller' => $module, 'action' => $method]
                    );
                    $this->ci->acl->getManager()
                            ->role(AccessPermissionsModel::USER)
                            ->allow($action_view_page->getGid(), $res_page->getResourceType(), $res_page->getResourceId());

                }
            }
        }
    }

    /**
     * Install access permissions
     *
     * @return void
     */
    protected function installAccessPermissionsList()
    {
        if (empty($this->access_permissions)) {
            return false;
        } else {
            $this->ci->load->model(AccessPermissionsModel::MODULE_GID . '/models/Access_permissions_modules_model');
            foreach ($this->access_permissions as $value) {
                if ($this->ci->pg_module->is_module_installed($value['module_gid'])) {
                    $value['data'] = serialize($value['data']);
                    $this->ci->Access_permissions_modules_model->saveModules($value);
                }
            }
        }
    }

    /**
     * Install demo content
     *
     * @return boolean/void
     */
    protected function installDemoContent()
    {
        if (empty($this->demo_content)) {
            return false;
        } else {
            $this->ci->load->model([
                'Access_permissions_model',
                AccessPermissionsModel::MODULE_GID . '/models/Access_permissions_groups_model',
                AccessPermissionsModel::MODULE_GID . '/models/Access_permissions_settings_model'
            ]);
            $access = $this->ci->Access_permissions_model->roles[AccessPermissionsModel::USER];
            foreach ($this->demo_content['groups'] as $group) {
                foreach ($this->ci->pg_language->languages as $l) {
                    $group['name_' . $l['id']] = isset($group['name'][$l['code']]) ? $group['name'][$l['code']] : $group['name']['en'];
                    $group['description_' . $l['id']] = isset($group['description'][$l['code']]) ? $group['description'][$l['code']] : $group['description']['en'];
                }
                $validate_data = $this->ci->Access_permissions_groups_model->validateSubscription(null, $group);
                if (empty($validate_data['errors'])) {
                    $this->ci->Access_permissions_groups_model->saveSubscription(null, $validate_data['data']);
                    $this->ci->Access_permissions_groups_model->addPeriod($validate_data['data']['gid']);
                    $this->ci->Access_permissions_model->callbackGroupAdd($validate_data['data']['gid']);
                }
            }
            foreach ($this->demo_content['periods'] as $period) {
                $this->ci->Access_permissions_settings_model
                    ->getAccessData($access)->savePeriod(null, $period);
            }
            foreach ($this->demo_content['acl'] as $acl) {
                 if ($this->ci->pg_module->is_module_installed($acl['module'])) {
                    $validate_data = $this->ci->Access_permissions_settings_model
                    ->getAccessData($access)->validatePermissions($acl['permissions'], $acl['module'], true);
                    if (empty($validate_data["errors"])) {
                        foreach ($validate_data['data'] as $data) {
                            $this->ci->Access_permissions_settings_model->getAccessData($access)
                                ->savePermissions($data['attrs'], $data['params']);
                            if (isset($data['settings'])) {
                                $this->ci->Access_permissions_settings_model->getAccessData($access)
                                    ->savePermissionsSettings($data['settings']);
                            }
                        }
                    }
                }
            }
        }
    }
}
