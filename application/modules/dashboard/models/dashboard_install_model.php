<?php

namespace Pg\Modules\Dashboard\Models;

use Pg\Libraries\Setup;

class Dashboard_install_model extends \Model
{
    /**
     * Link to CodeIgniter object
     * 
     * @var object
     */
    protected $ci;

    protected $modules_data;

    /**
     * Constructor
     *
     * @return Listings_install_model
     */
    public function __construct()
    {
        parent::__construct();
        $this->ci = & get_instance();
        $this->modules_data = Setup::getModuleData('dashboard', Setup::TYPE_MODULES_DATA);
    }
    
    /**
     * Install data of cronjobs module
     * 
     * @return void
     */
    public function install_cronjob()
    {
        // add lift up cronjob
        $this->ci->load->model('Cronjob_model');
        foreach ((array) $this->modules_data['cronjobs'] as $cron_data) {
            $validation_data = $this->ci->Cronjob_model->validate_cron(null, $cron_data);
            if (!empty($validation_data['errors'])) {
                continue;
            }
            $this->ci->Cronjob_model->save_cron(null, $validation_data['data']);
        }
    }

    /**
     * Uninstall data of cronjobs module
     * 
     * @return void
     */
    public function deinstall_cronjob()
    {
        $this->ci->load->model('Cronjob_model');
        $cron_data = array();
        $cron_data["where"]["module"] = "listings";
        $this->ci->Cronjob_model->delete_cron_by_param($cron_data);
    }
    
    public function _arbitrary_installing()
    {
        
    }
    
    public function _arbitrary_deinstalling()
    {
        
    }
}
