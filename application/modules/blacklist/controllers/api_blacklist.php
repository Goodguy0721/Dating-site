<?php

namespace Pg\Modules\Blacklist\Controllers;

/**
 * Users lists API controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * */
class Api_Blacklist extends \Controller
{
    private $_user_id;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Blacklist_model');
        if ('user' === $this->session->userdata('auth_type')) {
            $this->_user_id = intval($this->session->userdata('user_id'));
        }
    }

    public function blacklist()
    {
        $action = trim(strip_tags($this->input->post('action', true)));
        if (!$action) {
            $action = 'view';
        }
        $formatted = filter_input(INPUT_POST, 'formatted');

        $order_by['date_update'] = 'DESC';

        $items_count = $this->Blacklist_model->get_list_count($this->_user_id);
        $items_on_page = $this->pg_module->get_module_config('users', 'items_per_page');
        $this->load->helper('sort_order');
        $page = get_exists_page_number(filter_input(INPUT_POST, 'page', FILTER_VALIDATE_INT), $items_count, $items_on_page);

        if ($items_count) {
            $list = $this->Blacklist_model->get_list($this->_user_id, $page, $items_on_page, $order_by, '', $formatted);
        }

        $this->set_api_content('data', $list);
    }

    public function count()
    {
        $count = $this->Blacklist_model->get_list_count($this->_user_id);
        $this->set_api_content('data', $count);
    }

    public function add($id_dest_user)
    {
        if(!isset($id_dest_user)) {
            $id_dest_user = $this->input->post('id_dest_user');
        }
        
        $result = $this->Blacklist_model->add($this->_user_id, intval($id_dest_user));
        $this->set_api_content('data', $result);
        $this->set_api_content("messages", l('success_blacklist_add', 'blacklist'));
        
        return;
    }

    public function remove($id_dest_user)
    {
        $id_user = $this->session->userdata('user_id');
        $result = $this->Blacklist_model->remove($id_user, intval($id_dest_user));
        $this->set_api_content('data', $result);
        $this->set_api_content("messages", l('success_blacklist_remove', 'blacklist'));
        
        return;
    }
    
    public function getBlacklistAction($id_dest_user) {
        if (!isset($id_dest_user) || empty($id_dest_user)) {
            return;
        }

        if ($this->session->userdata('auth_type') != 'user') {
            return;
        }

        $user_id = $this->session->userdata('user_id');
        if (!$user_id || $user_id == $id_dest_user) {
            return;
        }

        if (in_array($id_dest_user, $this->Blacklist_model->get_list_users_ids($user_id))) {
            $action = 'remove';
        } else {
            $action = 'add';
        }

        $this->view->assign('action', $action);
    }
}
