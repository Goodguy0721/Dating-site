<?php

namespace Pg\Modules\Fast_navigation\Controllers;

use Pg\Modules\Fast_navigation\Models\Fast_navigation_model;

/**
 * Fast navigation module
 *
 * @package	PG_Dating
 * @copyright	Copyright (c) 2000-2016 PG Dating Pro - php dating software
 * @author	Pilot Group Ltd <http://www.pilotgroup.net/>
 */

class Admin_Fast_navigation extends \Controller
{

    /**
     * Constructor
     *
     * @return Admin_Fast_navigation
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model("Fast_navigation_model");
    }

     /**
     * Search
     *
     * @return void
     */
    public function search()
    {
        $search_data = $this->Fast_navigation_model->validSearchData($this->input->post('keyword', true));
        if (empty($search_data['errors'])) {
            $search_data['data'] = $this->Fast_navigation_model->getRootWords($search_data);
            $return  = $this->Fast_navigation_model->getSearchResult($search_data['data']);   
            $this->view->assign('data', $return);
        } else {
             $this->view->assign('error', $search_data['errors']);
        }
        $result['html'] = $this->view->fetch('list');
        $this->view->assign($result);
        $this->view->render();
    }
}