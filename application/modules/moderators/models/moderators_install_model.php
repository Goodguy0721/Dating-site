<?php

namespace Pg\Modules\Moderators\Models;

/**
 * Moderators install model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Nikita Savanaev <nsavanaev@pilotgroup.net>
 * */
class Moderators_install_model extends \Model
{

    protected $ci;

    /**
     * Constructor
     *
     * @return Install object
     */
    public function __construct()
    {
        parent::__construct();
        $this->ci = &get_instance();
        //// load langs
        $this->ci->load->model('Install_model');
    }

    public function _arbitrary_deinstalling()
    {
        $this->ci->load->model('Moderators_model');
        $users_id = $this->ci->Moderators_model->delete_user();
    }

}
