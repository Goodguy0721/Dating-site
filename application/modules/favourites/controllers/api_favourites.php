<?php

namespace Pg\Modules\Favourites\Controllers;

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
class Api_Favourites extends \Controller
{
    private $user_id;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Favourites_model');
        if ('user' === $this->session->userdata('auth_type')) {
            $this->user_id = intval($this->session->userdata('user_id'));
        }
    }

    public function favourites()
    {
        $action = trim(strip_tags($this->input->post('action', true)));
        if (!$action) {
            $action = 'view';
        }

        $items_count = $this->Favourites_model->get_list_count($this->user_id);

        if ($items_count) {
            $formatted = filter_input(INPUT_POST, 'formatted', FILTER_VALIDATE_BOOLEAN);
            $items_on_page = $this->pg_module->get_module_config('users', 'items_per_page');
            $this->load->helper('sort_order');
            $page = get_exists_page_number(filter_input(INPUT_POST, 'page', FILTER_VALIDATE_INT), $items_count, $items_on_page);
            $list = $this->Favourites_model->get_list($this->user_id, $page, $items_on_page, array('date_update' => 'DESC'), '', $formatted);
        }

        $this->set_api_content('data', $list);
    }

    public function count()
    {
        $count = $this->Favourites_model->get_list_count($this->user_id);
        $this->set_api_content('data', $count);
    }

    public function add()
    {
        $id_dest_user = intval($this->input->post('id_dest_user'));
        $result = $this->Favourites_model->add($this->user_id, intval($id_dest_user));
        $this->set_api_content('data', $result);
    }

    public function remove($id_dest_user)
    {
        $id_user = $this->session->userdata('user_id');
        $result = $this->Favourites_model->remove($id_user, intval($id_dest_user));
        $this->set_api_content('data', $result);
    }
}
