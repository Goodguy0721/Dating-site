<?php

namespace Pg\Modules\Events\Controllers;

/**
 * Api Events controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Alexey Chulkov <nsavanaev@pilotgroup.net>
 **/
class Api_Events extends \Controller
{
    /**
     * Constructor
     *
     * @return Api_Events
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('events/models/Events_model');
    }
}
