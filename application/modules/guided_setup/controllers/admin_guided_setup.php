<?php

namespace Pg\modules\guided_setup\controllers;

/**
 * GuidedSetup module
 *
 * @package 	PG_Dating
 *
 * @copyright 	Copyright (c) 2000-2014 PG Dating Pro - php dating software
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */

/**
 * GuidedSetup admin side controller
 *
 * @package 	PG_Dating
 * @subpackage 	GuidedSetup
 *
 * @category	controllers
 *
 * @copyright 	Copyright (c) 2000-2014 PG Dating Pro - php dating software
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
class Admin_Guided_setup extends \Controller
{
    private $dating_site_block = '.post-content';
    
    /**
     * Constructor
     *
     * @return GuidedSetup
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Guided_setup_model');
    }
    
    public function ajaxGetMain() {
        $return = array('content' => '');
        
        $menu_id = $this->input->post('menu_id');
        $menu_gid = $this->input->post('menu_gid');
        $is_frame = $this->input->post('is_frame');

        $attrs['where']['is_active'] = 1;
        $attrs['where']['guided_menu_id'] = $menu_id;
        $navigation = $this->Guided_setup_model->getPages($attrs);
        $current_page = $navigation[0];

        $this->view->assign('navigation', $navigation);
        $this->view->assign('current_page', $current_page);
        $this->view->assign('menu_gid', $menu_gid);
        $this->view->assign('progress_bar', $this->calcProgressBar());
        
        if(!$is_frame) {
            $content = $this->getPageContent($current_page['link']);
            $this->view->assign('content', $content);
        }

        $return['content'] = $this->view->fetch('main_page', 'admin', 'guided_setup');
        $this->view->assign($return);
        $this->view->render();
        
    }
    
    public function ajaxGetGuidePage($page_id)
    {
        $attrs['where']['is_active'] = 1;
        $attrs['where']['id'] = $page_id;
        $result = $this->Guided_setup_model->getPages($attrs);
        $page = $result[0];
        $return = $this->getPageContent($page['link']);

        $this->view->assign($return);
        $this->view->render();
    }
    
    public function ajaxPageConfigure($page_id)
    {
        $page_id = intval($page_id);
        $data = array(
            'is_configured' => 1,
        );
        
        $this->Guided_setup_model->savePage($page_id, $data);

        $return['success'] = l('admin_success_configure', 'guided_setup');
        $return['progress_bar'] = $this->calcProgressBar();
        $this->view->assign($return);
        $this->view->render();
    }
    
    private function calcProgressBar()
    {
        $attrs['where']['is_active'] = 1;
        $attrs['where']['guided_menu_id'] = 1;
        $pages = $this->Guided_setup_model->getPages($attrs);

        $configured = array();
        foreach($pages as $page) {
            if($page['is_configured']) {
                $configured[] = $page;
            }
        }

        $percent = (count($configured)*100)/count($pages);
        
        $data = array(
            'text_setup_indicator' => sprintf(l('admin_text_setup_indicator', 'guided_setup'), $percent),
            'percent' => $percent,
        );

        return $data;
    }
    
    private function getPageContent($url)
    {
        $this->load->library("simple_html_dom");
        $html = file_get_html($url);
        $page = $html->find($this->dating_site_block);            
        
        return $this->formatContent($page[0]);
    }
    
    private function formatContent($content)
    {
        return $content;
    }
    
    

}
