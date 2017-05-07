<?php

namespace Pg\Modules\Associations\Controllers;

/**
 * Api Associations controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Nikita Savanaev <nsavanaev@pilotgroup.net>
 * */
class Api_Associations extends \Controller
{
    /**
     * Constructor
     *
     * @return Api_Associations
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('associations/models/Associations_model');
    }
}
