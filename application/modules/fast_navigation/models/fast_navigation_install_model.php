<?php

namespace Pg\Modules\Fast_navigation\Models;

use Pg\Modules\Fast_navigation\Models\Fast_navigation_model;

/**
 * Fast_navigation module
 *
 * @copyright	Copyright (c) 2000-2016
 * @author	Pilot Group Ltd <http://www.pilotgroup.net/>
 */

class Fast_navigation_install_model extends \Model
{
    /**
     * Link to CodeIgniter object
     *
     * @var object
     */
    protected $ci;

    /**
     * Cronjobs configuration
     */
    private $cronjobs = [
        [
            "name"     => "Update the list of words to search for admin",
            "module"   => Fast_navigation_model::MODULE_GID,
            "model"    => "Fast_navigation_model",
            "method"   => "updateFastNavigationCron",
            "cron_tab" => "0 * * * *",
            "status"   => "1",
        ],
    ];
    
    /**
     * Class constructor
     *
     * @return Fast_navigation_install_model
     */
    public function __construct()
    {
        parent::__construct();
        $this->ci = &get_instance();
    }

    /**
     * Install links to cronjobs
     *
     * @return void
     */
    public function install_cronjob()
    {
        $this->ci->load->model('Cronjob_model');
        foreach ((array) $this->cronjobs as $cron_data) {
            $validation_data = $this->ci->Cronjob_model->validate_cron(null, $cron_data);
            if (!empty($validation_data['errors'])) {
                continue;
            }
            $this->ci->Cronjob_model->save_cron(null, $validation_data['data']);
        }
    }

    /**
     * Uninstall links to cronjobs
     *
     * @return void
     */
    public function deinstall_cronjob()
    {
        $this->ci->load->model('Cronjob_model');
        $cron_data = [
            'where' => ['module' => Fast_navigation_model::MODULE_GID],
        ];
        $this->ci->Cronjob_model->delete_cron_by_param($cron_data);
    }

     /**
     * Install module data
     *
     * @return void
     */
    public function _arbitrary_installing()
    {
        $this->ci->load->model('Fast_navigation_model');
        $this->ci->Fast_navigation_model->dataСollection();
    }

}