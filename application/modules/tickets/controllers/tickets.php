<?php

namespace Pg\Modules\Tickets\Controllers;

use Pg\Libraries\View;

/**
 * Tickets user side controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Nikita Savanaev <nsavanev@pilotgroup.net>
 **/
class Tickets extends \Controller
{
    /**
     * Constructor
     *
     * @return Tickets
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model("Tickets_model");
    }

    public function index()
    {
        $reasons = $this->Tickets_model->get_reason_list();
        $this->view->assign('reasons', $reasons);
        $is_auth = ($this->session->userdata('auth_type') == 'user');
        $this->view->assign('is_auth', $is_auth);
        $settings = $this->Tickets_model->get_settings();
        $this->view->assign('settings', $settings);

        if ($this->input->post('btn_save')) {
            $post_data = array(
                "id_reason"    => $this->input->post('id_reason', true),
                "user_name"    => $this->input->post('user_name', true),
                "user_email"   => $this->input->post('user_email', true),
                "subject"      => $this->input->post('subject', true),
                "message"      => $this->input->post('message', true),
                "captcha_code" => $this->input->post('captcha_code', true),
            );
            $validate_data = $this->Tickets_model->validate_contact_form($post_data);

            if (!empty($validate_data["errors"])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data["errors"]);
                $data = $validate_data["data"];
            } else {
                $return = $this->Tickets_model->send_contact_form($validate_data["data"]);

                if (!empty($return["errors"])) {
                    $this->system_messages->addMessage(View::MSG_ERROR, $return["errors"]);
                } else {
                    $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_send_form', 'tickets'));
                }
                redirect(site_url() . "tickets");
            }
        }

        if (!$is_auth || !$settings['status_personal_communication']) {
            $this->load->plugin('captcha');
            $vals = array(
                'img_path'   => TEMPPATH . '/captcha/',
                'img_url'    => SITE_VIRTUAL_PATH . 'temp/captcha/',
                'font_path'  => BASEPATH . 'fonts/arial.ttf',
                'img_width'  => '200',
                'img_height' => '30',
                'expiration' => 7200,
            );

            $cap = create_captcha($vals);
            $data["captcha"] = $cap['image'];
            $_SESSION["captcha_word"] = $cap['word'];
            $this->view->assign('data', $data);
        }

        $this->view->assign('messages_block', $this->messagesListBlock());

        $this->load->model('Menu_model');
        $this->Menu_model->breadcrumbs_set_active(l('header_tickets_form', 'tickets'));

        $this->view->render('form');
    }

    public function messagesListBlock($order = "date_created", $count_items_on_page = null)
    {
        $return = array('content' => '');
        $id_user = $this->session->userdata('user_id');
        if ($id_user) {
            $where = array();
            $where['where']['id_user'] = $id_user;
            $order_direction = 'DESC';
            $items_on_page = $this->pg_module->get_module_config('tickets', 'load_messages');

            $user_messages = $this->Tickets_model->get_messages($where, 1, $items_on_page, $count_items_on_page, array($order => $order_direction));
            if (!empty($user_messages)) {
                $page_data["date_format"] = $this->pg_date->get_format('date_time_literal', 'st');
                $this->view->assign('page_data', $page_data);
                $this->view->assign('user_messages', $user_messages);

                $return['content'] = $this->view->fetch('list_messages', 'user', 'tickets');
            }
            $this->Tickets_model->read_messages($id_user, true);
        }

        return $return['content'];
    }

    public function ajax_get_messages($order = "date_created")
    {
        $count_items_on_page = null;
        if ($this->input->post('count_messages')) {
            $count_items_on_page = intval($this->input->post('count_messages', true));
        }
        $return['content'] = $this->messagesListBlock($order, $count_items_on_page);
        $this->view->assign($return);

        return;
    }

    public function ajax_send_messages()
    {
        $return = array('errors' => '', 'content' => '', 'success' => '');
        if ($this->input->post('message')) {
            $post_data = array(
                "message" => $this->input->post('message', true),
                "id_user" => $this->input->post('id_user', true),
            );
            $validate_data = $this->Tickets_model->validate_auth_contact_form($post_data);
            if (!empty($validate_data["errors"])) {
                $return['errors'] = $validate_data["errors"];
            } else {
                $format_message = $this->Tickets_model->format_message($validate_data["data"]);
                $this->Tickets_model->save_message($format_message);
                
                $this->load->model('menu/models/Indicators_model');
                $this->Indicators_model->add($this->Tickets_model->indicator_type, $this->session->userdata('user_id'));
                
                $this->Tickets_model->read_messages($validate_data["data"]['id_user'], true);
                $format_message['message']['output_name'] = $this->session->userdata('output_name');
                $format_message['message']['date_created'] = date($this->pg_date->get_format('date_time_literal', 'date'));
                $return['content'] = $format_message['message'];
                $data = $this->Tickets_model->send_contact_form($format_message['message']);
                if (!empty($data["errors"])) {
                    $return["errors"] = $data["errors"];
                } else {
                    $return["success"] = l('success_send_form', 'tickets');
                }
            }
        } else {
            $return['errors'] = l('error_message_incorrect', 'tickets');
        }
        $this->view->assign($return);

        return;
    }
}
