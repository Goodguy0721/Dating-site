<?php

namespace Pg\Modules\Network\Controllers;

/**
 * Network admin side controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * */
class Api_Network extends \Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function getStat()
    {
        $this->load->model("network/models/Network_users_model");
        $this->set_api_content('data', array($this->Network_users_model->getStat()));
    }
}
