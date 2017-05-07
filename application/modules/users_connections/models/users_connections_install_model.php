<?php

namespace Pg\Modules\Users_connections\Models;

/**
 * Users connections install model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 **/
class Users_connections_install_model extends \Model
{
    public $ci;

    /**
     * Constructor
     *
     * @return Install object
     */
    public function __construct()
    {
        parent::__construct();
        $this->ci = &get_instance();
    }

    public function _arbitrary_installing()
    {
    }

    public function _arbitrary_deinstalling()
    {
    }
}
