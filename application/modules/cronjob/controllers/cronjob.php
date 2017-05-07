<?php

namespace Pg\Modules\Cronjob\Controllers;

/**
 * Cronjob user side controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Katya Kashkova <katya@pilotgroup.net>
 *
 * @version $Revision: 2 $ $Date: 2010-04-02 15:07:07 +0300 (Ср, 02 апр 2010) $ $Author: kkashkova $
 **/
class Cronjob extends \Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Cronjob_model');
    }

    public function index()
    {
        $this->Cronjob_model->scheduler();
    }
}
