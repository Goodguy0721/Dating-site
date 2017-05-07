<?php

namespace Pg\Modules\Store\Controllers;

/**
 * Api store controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Nikita Savanaev <nsavanaev@pilotgroup.net>
 **/
class Api_Store extends \Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Store_model');
    }
}
