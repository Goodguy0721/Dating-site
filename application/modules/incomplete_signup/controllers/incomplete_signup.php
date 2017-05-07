<?php

namespace Pg\Modules\Incomplete_signup\Controllers;

/**
 * Incomplete signup user side controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 **/
class Incomplete_signup extends \Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function ajax_get_register_form_data()
    {
        $this->load->model('Incomplete_signup_model');
        $post_data = $this->input->post('data_fields', true);
        if ($post_data['email']) {
            $this->load->model('Users_model');
            $validate_data = $this->Users_model->validate('', $post_data);

            $validate_data['data']["lang_id"] = $this->session->userdata("lang_id");
            if (!$validate_data['data']["lang_id"]) {
                $validate_data['data']["lang_id"] = $this->pg_language->get_default_lang_id();
            }

            $validate_data['data']['ip'] = $this->input->ip_address();

            $unreg_data_id = $this->Incomplete_signup_model->check_email_exists($validate_data['data']['email']);
            $this->Incomplete_signup_model->save_unregistered_user($unreg_data_id, $validate_data['data']);
        }
        exit;
    }
}
