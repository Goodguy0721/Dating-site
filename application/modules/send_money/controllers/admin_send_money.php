<?php

namespace Pg\Modules\Send_money\Controllers;

use Pg\Libraries\View;

/**
 * Send money module
 *
 * @package 	PG_RealEstate
 *
 * @copyright 	Copyright (c) 2000-2014 PG Real Estate - php real estate listing software
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */

/**
 * Send money admin side controller
 *
 * @package 	PG_RealEstate
 * @subpackage 	Send money
 *
 * @category	controllers
 *
 * @copyright 	Copyright (c) 2000-2014 PG Real Estate - php real estate listing software
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
class Admin_Send_money extends \Controller
{
    /**
     * Controller
     *
     * @return Admin_Send_money
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Menu_model');
        $this->Menu_model->set_menu_active_item('admin_menu', 'add_ons_items');
    }

    /**
     * Manage module settings
     *
     * @return void
     */
    public function index()
    {
        $this->settings();
    }

    /**
     * Manage module settings
     *
     * @return void
     */
    public function settings()
    {
        $this->load->model('payments/models/Payment_currency_model');
        $base_currency = $this->Payment_currency_model->get_currency_default(true);
        $currencies    = array();
        array_push($currencies, '%');
        array_push($currencies, $base_currency['gid']);
        $this->load->model('Send_money_model');

        if ($this->input->post('btn_save')) {
            $this->load->model('Send_money_model');

            $post_data['use_fee']       = $this->input->post('use_fee', true);
            $post_data['fee_price']     = $this->input->post('fee_price', true);
            $post_data['fee_currency']  = $this->input->post('fee_currency', true);
            $post_data['to_whom']       = $this->input->post('to_whom', true);
            $post_data['transfer_type'] = $this->input->post('transfer_type', true);
            $post_data['currencies']    = array();
            $post_data['currencies']    = $currencies;
            if (!isset($post_data['fee_price']) || empty($post_data['fee_price'])) {
                $post_data['fee_price'] = $this->pg_module->get_module_config('send_money', 'fee_price');
            }
            if (!isset($post_data['fee_currency']) || empty($post_data['fee_currency'])) {
                $post_data['fee_currency'] = $this->pg_module->get_module_config('send_money', 'fee_currency');
            }
            $validate_data              = $this->Send_money_model->validateSettings($post_data);
            if (!empty($validate_data['errors'])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data['errors']);
            } else {
                foreach ($validate_data['data'] as $setting => $value) {
                    $this->pg_module->set_module_config('send_money', $setting, $value);
                }
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_settings_saved', 'send_money'));
            }
            $data = $validate_data['data'];
        } else {
            $data['use_fee']       = $this->pg_module->get_module_config('send_money', 'use_fee');
            $data['fee_price']     = $this->pg_module->get_module_config('send_money', 'fee_price');
            $data['to_whom']       = $this->pg_module->get_module_config('send_money', 'to_whom');
            $data['transfer_type'] = $this->pg_module->get_module_config('send_money', 'transfer_type');
            $data['fee_currency']  = $this->pg_module->get_module_config('send_money', 'fee_currency');
        }
        $data['transfer_types'] = $this->Send_money_model->getAllowedPaymentTypes();
        foreach ($data['transfer_types'] as $key => $transfer_type) {
            $data['transfer_types'][$transfer_type] = l('admin_settings_' . $transfer_type, 'send_money');
            unset($data['transfer_types'][$key]);
        }
        $data['currencies'] = array();
        $data['currencies'] = $currencies;

        $this->view->assign('data', $data);

        $this->view->setHeader(l('admin_header_settings', 'send_money'));
        $this->view->setBackLink(site_url() . 'admin/start/menu/add_ons_items');

        $this->Menu_model->set_menu_active_item('admin_send_money_menu', 'send_money_settings_item');

        $this->view->render('settings');
    }

    public function view($page = 1)
    {
        $this->load->model('Send_money_model');
        $this->load->model('Users_model');
        $fee_price    = $this->pg_module->get_module_config('send_money', 'fee_price');
        $username_arr = array();
        $use_fee      = $this->pg_module->get_module_config('send_money', 'use_fee');
        if ($use_fee == 'use') {
            $currency = $this->pg_module->get_module_config('send_money', 'fee_currency');
            if ($currency == '%') {
                $koef = (float) $fee_price / 100; //ToDo: May be $koef need store in config table?
            } else {
                $koef = 1;
            }
        }

        if (!$page) {
            $page = $current_settings["page"];
        }
        $transactions_count = $this->Send_money_model->getTransactionsCount();
        $items_on_page      = $this->pg_module->get_module_config('start', 'admin_items_per_page');
        $this->load->helper('sort_order');
        $page               = get_exists_page_number($page, $transactions_count, $items_on_page);

        $data      = $this->Send_money_model->getTransaction(null, $page, $items_on_page);
        $this->load->helper("navigation");
        $url       = site_url() . "admin/send_money/view/";
        $page_data = get_admin_pages_data($url, $transactions_count, $items_on_page, $page, 'briefPage');
        $this->view->assign('page_data', $page_data);

        $id_users_arr = array();

        foreach ($data as $value) {
            $id_users_arr[] = $value['id_user'];
            $id_users_arr[] = $value['id_sender'];
        }
        $users_arr = $this->Users_model->get_users_list(null, null, null, null, $id_users_arr);
        foreach ($users_arr as $value) {
            $username_arr[$value['id'] . '_name'] = $value['name'];
        }
        foreach ($data as $key => $value) {
            $data[$key]['user']         = $username_arr[$value['id_user'] . '_name'];
            $data[$key]['sender_user']  = $username_arr[$value['id_sender'] . '_name'];
            $transfer_fee               = $data[$key]['full_amount'] - $data[$key]['amount'];
            $data[$key]['transfer_fee'] = number_format($transfer_fee, 2, '.', '');
            $data[$key]['rand']         = mt_rand(0, 10000);
        }

        $this->view->assign('data', $data);
        $this->view->assign('count', $transactions_count);

        $this->view->setHeader(l('admin_header_view', 'send_money'));
        $this->view->setBackLink(site_url() . 'admin/start/menu/add_ons_items');

        $this->Menu_model->set_menu_active_item('admin_send_money_menu', 'send_money_view_item');
        $this->view->render('view');
    }
}
