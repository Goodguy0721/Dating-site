<?php

/**
 * Access_permissions module
 *
 * @package	PG_Dating
 * @copyright	Copyright (c) 2000-2016 PG Dating Pro - php dating software
 * @author	Pilot Group Ltd <http://www.pilotgroup.net/>
 */

use Pg\modules\access_permissions\models\AccessPermissionsModel;

/**
 * Access_permissions api side controller
 *
 * @package	PG_Dating
 * @subpackage	Access_permissions
 * @category	controllers
 * @copyright	Copyright (c) 2000-2016 PG Dating Pro - php dating software
 * @author	Pilot Group Ltd <http://www.pilotgroup.net/>
 */

class ApiAccessPermissions extends \Controller
{

    /**
     * Controller
     *
     * @return Access_permissions_start
     */
    public function __construct()
    {
        parent::__construct();
    }

}