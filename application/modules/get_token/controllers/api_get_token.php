<?php

namespace Pg\Modules\Get_token\Controllers;

/**
 * Users user side controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Mikhail Chernov <mchernov@pilotgroup.net>
 * */
class Api_get_token extends \Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model("users/models/Auth_model");
    }

    public function index()
    {
        $errors = array();
        $token = '';

        $data = array(
            "email"    => trim(strip_tags($this->input->get_post('email', true))),
            "password" => trim(strip_tags($this->input->get_post('password', true))),
        );

        if (empty($data['email']) && empty($data['password'])) {
            $token = $this->session->sess_create_token();
            $this->set_api_content('data', array('token' => $token));
        } else {
            $login_return = $this->Auth_model->login_by_email_password($data["email"], md5($data["password"]));
            if (!empty($login_return["errors"])) {
                $errors = $login_return["errors"];
                $this->set_api_content('errors', $errors);
                $token = $this->session->sess_destroy();
            } else {  
                $this->load->model("get_token/models/Get_token_model");
                $this->Get_token_model->mobileAuth($login_return['user_data']['id']); 
                $token = $this->session->sess_create_token();
                $this->set_api_content('data', array('token' => $token, 'user_data' => $login_return['user_data']));
            }
        }
    }
}
