<?php

namespace Pg\Modules\Tickets\Controllers;

/**
 * Tickets api controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Nikita Savanaev <nsavanev@pilotgroup.net>
 **/
class Api_Tickets extends \Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Tickets_model');
    }

    /**
     * Get reasons list
     */
    public function get_reasons()
    {
        $reasons = $this->Tickets_model->get_reason_list();
        $this->set_api_content('data', array('reasons' => $reasons));
    }

    /**
     * Send tickets form
     *
     * @param int    $id_reason
     * @param string $user_name
     * @param string $user_email
     * @param string $subject
     * @param string $message
     *
     * @todo Add antispam
     */
    public function send_form()
    {
        $post_data = array(
            'id_reason'  => $this->input->post('id_reason', true),
            'user_name'  => $this->input->post('user_name', true),
            'user_email' => $this->input->post('user_email', true),
            'subject'    => $this->input->post('subject', true),
            'message'    => $this->input->post('message', true),
        );
        $validate_data = $this->Tickets_model->validate_contact_form($post_data);

        if (!empty($validate_data['errors'])) {
            $this->set_api_content('errors', $validate_data['errors']);
            $this->set_api_content('data', array('post_data' => $post_data));
        } else {
            $return = $this->Tickets_model->send_contact_form($validate_data['data']);
            if (!empty($return['errors'])) {
                $this->set_api_content('errors', $return['errors']);
            } else {
                $this->set_api_content('messages', l('success_send_form', 'tickets'));
            }
        }
    }

    public function get_messages($order = "date_created")
    {
        $id_user = $this->session->userdata('user_id');
        if ($id_user) {
            if ($this->input->post('count_messages')) {
                $count_items_on_page = intval($this->input->post('count_messages', true));
            }
            $where = array();
            $where['where']['id_user'] = $id_user;
            if (!$order_direction) {
                $order_direction = 'DESC';
            }
            $items_on_page = $this->pg_module->get_module_config('tickets', 'load_messages');
            $user_messages = $this->Tickets_model->get_messages($where, 1, $items_on_page, $count_items_on_page, array($order => $order_direction));
            $this->Tickets_model->read_messages($id_user, true);
        }
        $this->set_api_content('data', $user_messages);
    }

    public function send_messages()
    {
        if ($this->input->get('message')) {
            $post_data = array(
                "message" => $this->input->get('message', true),
                "id_user" => $this->session->userdata('user_id'),
            );
            $validate_data = $this->Tickets_model->validate_auth_contact_form($post_data);
            if (!empty($validate_data["errors"])) {
                $this->set_api_content('errors', $validate_data["errors"]);
                $this->set_api_content('data', array('post_data' => $post_data));
            } else {
                $format_message = $this->Tickets_model->format_message($validate_data["data"]);
                $this->Tickets_model->save_message($format_message);
                $this->Tickets_model->read_messages($validate_data["data"]['id_user'], true);
                $format_message['message']['output_name'] = $this->session->userdata('output_name');
                $return = $format_message['message'];
                $data = $this->Tickets_model->send_contact_form($format_message['message']);
                if (!empty($data["errors"])) {
                    $this->set_api_content('errors', $data["errors"]);
                }
            }
        } else {
            $this->set_api_content('errors', l('error_message_incorrect', 'tickets'));
        }
        $this->set_api_content('data', $return);
    }
}
