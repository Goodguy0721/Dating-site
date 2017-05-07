<?php

namespace Pg\Modules\Winks\Controllers;

use Pg\Libraries\View;

/**
 * Winks admin side controller
 *
 * @package PG_DatingPro
 * @subpackage Winks
 *
 * @category	controllers
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * */
class Admin_Winks extends \Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Menu_model');
        $this->Menu_model->set_menu_active_item('admin_menu', 'winks_menu_item');
    }

    public function index()
    {
        $this->load->model('Winks_model');
        $winks = $this->Winks_model->get_list();
        $this->view->assign('winks', $winks);
        $this->view->setHeader(l('admin_header_winks_list', 'winks'));
        $this->view->render('list');
    }
}
