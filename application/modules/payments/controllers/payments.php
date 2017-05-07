<?php

namespace Pg\Modules\Payments\Controllers;

use Pg\Libraries\View;

/**
 * Payments user side controller
 *
 * @package PG_Dating
 * @subpackage application
 * @category	modules
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 **/
class Payments extends \Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function statistic($page = 1)
    {
        if ($this->session->userdata('auth_type') != 'user') {
            show_404();

            return;
        }
        $user_id = $this->session->userdata('user_id');
        $this->load->model("Payments_model");
        $params["where"]["id_user"] = $user_id;

        $payments_count = $this->Payments_model->get_payment_count($params);

        $items_on_page = $this->pg_module->get_module_config('payments', 'items_per_page');
        $this->load->helper('sort_order');
        $page = get_exists_page_number($page, $payments_count, $items_on_page);

        $payments = $this->Payments_model->get_payment_list($page, $items_on_page, array("date_add" => "DESC"), $params);
        $this->view->assign('payments', $payments);

        $this->load->helper("navigation");
        $page_data = get_user_pages_data(site_url() . "payments/statistic/", $payments_count, $items_on_page, $page, 'briefPage');
        $page_data["date_format"] = $this->pg_date->get_format('date_time_literal', 'st');
        $this->view->assign('page_data', $page_data);

        $this->load->model('Menu_model');
        $this->Menu_model->breadcrumbs_set_active(l('header_my_payments_statistic', 'payments'));

        $this->view->render('statistic');
    }

    /**
     * Process response from payment system (deprecated)
     *
     * @param string $system_gid system guid
     *
     * @return void
     */
    public function responce($system_gid)
    {
        $this->response($system_gid);
    }

    /**
     * Process response from payment system
     *
     * @param string $system_gid system guid
     *
     * @return void
     */
    public function response($system_gid)
    {
        if (empty($system_gid)) {
            return false;
        }

        $data = $_REQUEST;

        log_message('error', $system_gid . ': ' . json_encode($data), 'response', 'log');

        $this->load->helper('payments');
        $return = receive_payment($system_gid, $data);

        if ($return["type"] == "html") {
            if (!empty($return["errors"])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $return["errors"]);
            }
            if (!empty($return["info"])) {
                $this->system_messages->addMessage(View::MSG_SUCCESS, $return["info"]);
            }

            $this->load->model("Payments_model");
            $payment = $this->Payments_model->get_payment_by_id($return["data"]["id_payment"]);
            $temp = $this->Payments_model->format_payments(array(0 => $payment));
            $this->view->assign('payment', $temp[0]);

            $this->view->render('payment_return');
        }
    }

    public function form()
    {
        $data["payment_type_gid"] = $this->input->post('payment_type_gid', true);
        $data["amount"] = $this->input->post('amount', true);
        $data["id_user"] = $this->session->userdata('user_id');
        $data["currency_gid"] = $this->input->post('currency_gid', true);
        $data["system_gid"] = $this->input->post('system_gid', true);
        $data["payment_data"] = $this->input->post('payment_data', true);
        $this->load->model("payments/models/Payment_systems_model");
        $data["system"] = $this->Payment_systems_model->get_system_by_gid($data["system_gid"]);
        if($data["system"]["gid"] == 'offline'){
            $data["system"]["name"] = l('offline_system_payment', 'payments');
        }
        $this->Payment_systems_model->load_driver($data["system_gid"]);
        $data["map"] = $this->Payment_systems_model->get_html_data_map();

        $this->load->helper('seo');
        $this->load->model("Payments_model");
        if (!$this->Payments_model->validate_payment_form($data)) {
            redirect(rewrite_link('users', 'account', array('action' => 'payments_history')));
        }

        if ($this->input->post('btn_save')) {
            $data["payment_data"] = $_SESSION["saved_payment_data"];
            $map = $this->input->post('map', true);
            if (!empty($map)) {
                foreach ($map as $key => $value) {
                    $data["payment_data"][$key] = $value;
                }
                $this->load->helper('payments');
                $return = send_payment($data["payment_type_gid"], $data["id_user"], $data["amount"], $data["currency_gid"], $data["system_gid"], $data["payment_data"], "validate");
                $data["payment_data"] = $return["data"];

                if (!empty($return["info"])) {
                    $this->system_messages->addMessage(View::MSG_INFO, $return["info"]);
                }

                if (!empty($return["errors"])) {
                    $this->system_messages->addMessage(View::MSG_ERROR, $return["errors"]);
                } else {
                    $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_payment_send', 'payments'));
                    
                    $redirect = $this->session->userdata('service_redirect');
                    if($redirect) {
                        $this->session->set_userdata(array('service_redirect' => ''));
                        redirect($redirect, 'hard');                    
                    }
                    
                    redirect(rewrite_link('users', 'account', array('action' => 'payments_history')), 'hard');
                }
            }
        } else {
            ////// payment_data may content diff data from post
            $_SESSION["saved_payment_data"] = $data["payment_data"];
        }

        $this->load->model("payments/models/Payment_currency_model");
        $data["currency"] = $this->Payment_currency_model->get_currency_by_gid($data["currency_gid"]);

        $this->load->model('Menu_model');
        $this->Menu_model->breadcrumbs_set_active(l('header_my_payments_statistic', 'payments'));

        $this->view->assign('data', $data);
        $this->view->render('payment_form');
    }

    public function js($payment_id, $no_redirect = 0)
    {
        if (empty($no_redirect)) {
            $this->view->assign('need_redirect', 1);
        }

        $this->load->model("Payments_model");
        $payment = $this->Payments_model->get_payment_by_id($payment_id);

        $this->load->model("payments/models/Payment_systems_model");
        $payment['system'] = $this->Payment_systems_model->get_system_by_gid($payment["system_gid"]);

        $this->Payment_systems_model->load_driver($payment['system_gid']);

        $js = $this->Payment_systems_model->get_js($payment, $payment['system']);
        $this->view->assign('js', $js);

        $this->view->assign('data', $payment);

        $this->load->model('Menu_model');
        $this->Menu_model->breadcrumbs_set_active(l('header_my_payments_statistic', 'payments'));

        $this->view->render('payment_js');
    }

    /**
     * Change currency action
     *
     * @param integer $currency_id
     */
    public function change_currency($currency_id)
    {
        $currency_id = intval($currency_id);
        $this->session->set_userdata("currency_id", $currency_id);

        if (strpos($_SERVER["HTTP_REFERER"], site_url()) !== false) {
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            redirect();
        }
    }
}
