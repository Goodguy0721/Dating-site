<?php

/**
 * Access_permissions module
 *
 * @package PG_Dating
 * @copyright   Copyright (c) 2000-2016 PG Dating Pro - php dating software
 * @author  Pilot Group Ltd <http://www.pilotgroup.net/>
 */

use Pg\libraries\View;
use Pg\modules\access_permissions\models\Access_permissions_model as AccessPermissionsModel;

/**
 * Access_permissions user side controller
 *
 * @package PG_Dating
 * @subpackage  Access_permissions
 * @category    controllers
 * @copyright   Copyright (c) 2000-2016 PG Dating Pro - php dating software
 * @author  Pilot Group Ltd <http://www.pilotgroup.net/>
 */

class Access_permissions extends \Controller
{

    /**
     * Controller
     *
     * @return Access_permissions_start
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Access_permissions_model');
        $this->load->model([
            AccessPermissionsModel::MODULE_GID . '/models/Access_permissions_settings_model',
            AccessPermissionsModel::MODULE_GID . '/models/Access_permissions_modules_model',
        ]);
        $this->view->assign('header_type', AccessPermissionsModel::MODULE_GID);
    }

    /**
     * Display index page
     *
     * @return void
     */
    public function index()
    {
        $this->load->model([
            AccessPermissionsModel::MODULE_GID . '/models/Access_permissions_groups_model',
            AccessPermissionsModel::MODULE_GID . '/models/Access_permissions_users_model',
            'payments/models/Payment_systems_model',
        ]);
        $groups = $this->Access_permissions_groups_model->formatGroups(
            $this->Users_model->getUserTypesGroups(['where' => ['is_active' => 1]])
        );
        $periods = $this->Access_permissions_settings_model->getAccessData(
            $this->Access_permissions_model->roles[AccessPermissionsModel::USER]
        )->getPriceGroups($this->session->userdata['user_type']);
        $format_groups = $this->Access_permissions_model->formatGroups($groups, $periods);
        $billing_systems = $this->Payment_systems_model->get_active_system_list();
        $this->view->assign('billing_systems', $billing_systems);
        $this->view->assign('groups', $format_groups);
        $this->view->render('index');
    }

    /**
     * Group Page
     *
     * @param string $gid
     * @param integer $period
     *
     * @return array
     */
    public function groupPage($gid, $period)
    {
        if (!$gid) {
            $this->system_messages->addMessage(View::MSG_ERROR, l('error_unknown_group', AccessPermissionsModel::MODULE_GID));
            $this->view->setRedirect(site_url() . AccessPermissionsModel::MODULE_GID);
        } else {
            $group = $this->Access_permissions_settings_model->getAccessData(
                $this->Access_permissions_model->roles[AccessPermissionsModel::USER]
            )->getGroup(['group_gid' => $gid, 'period_id' => $period]);
            if (empty($group)) {
                $this->system_messages->addMessage(View::MSG_ERROR, l('error_group_missing', AccessPermissionsModel::MODULE_GID));
                $this->view->setRedirect(site_url() . AccessPermissionsModel::MODULE_GID);
            }
            $this->view->assign('group', $group);
        }
        $this->Menu_model->breadcrumbs_set_parent('access_permissions_menu_item');
        $this->Menu_model->breadcrumbs_set_active($group['name']);
        $this->view->render('group_page');
    }

    /**
     * Group
     *
     * @return array
     */
    public function group()
    {
         if ($this->input->post('send')) {
            $post_data = [
                'group_gid' => filter_input(INPUT_POST, 'group_gid', FILTER_SANITIZE_STRING),
                'period_id' => filter_input(INPUT_POST, 'period_id', FILTER_VALIDATE_INT),
            ];
            $group = $this->Access_permissions_settings_model->getAccessData(
                $this->Access_permissions_model->roles[AccessPermissionsModel::USER]
            )->getGroup($post_data);
            $this->view->assign('group', $group);
            $result['html'] = $this->view->fetch('group');
        } else {
            $result = [
                'error' => [l('error_system', AccessPermissionsModel::MODULE_GID)]
            ];
        }
        $this->view->assign($result);
        $this->view->render();
    }

    /**
     * Payment Form
     *
     * @return array
     */
    public function paymentForm()
    {
        if ($this->input->post('send')) {
             $post_data = [
                'group_gid' => filter_input(INPUT_POST, 'group_gid', FILTER_SANITIZE_STRING),
                'period_id' => filter_input(INPUT_POST, 'period_id', FILTER_VALIDATE_INT),
                'pay_system_gid' => filter_input(INPUT_POST, 'pay_system_gid', FILTER_SANITIZE_STRING),
            ];
             $result = $this->Access_permissions_model->getPaymenData($post_data);
             if (!empty($result['errors'])) {
                 $result['error'] = $result['errors'];
             } else {
                $this->view->assign('payment_data', $result['data']);
                if ($result['data']['disable_account_pay'] === true) {
                    $this->load->model('payments/models/Payment_systems_model');
                    $billing_systems = $this->Payment_systems_model->get_active_system_list();
                    $this->view->assign('billing_systems', $billing_systems);
                    $result['html'] = $this->view->fetch('deficit_funds', null, 'users_payments');
                } else {
                    $result['html'] = $this->view->fetch('payment_form');
                }
             }
         } else {
            $result= [
                'error' => [l('error_system', AccessPermissionsModel::MODULE_GID)]
            ];
        }
        $this->view->assign($result);
        $this->view->render();
    }

    /**
     * Purchase
     *
     * @return boolean/array
     */
    public function payment()
    {
        if ($this->input->post('send')) {
            $user_id = $this->session->userdata('user_id');
            $post_data = [
                'group_gid' => filter_input(INPUT_POST, 'group_gid', FILTER_SANITIZE_STRING),
                'period_id' => filter_input(INPUT_POST, 'period_id', FILTER_VALIDATE_INT),
                'pay_system_gid' => filter_input(INPUT_POST, 'pay_system_gid', FILTER_SANITIZE_STRING),
            ];
            $result = $this->Access_permissions_model->groupPayment($post_data, $user_id);
            $this->view->assign('result', $result);
             $result['html'] = $this->view->fetch('payment');
        } else {
            $result= [
                'error' => [l('error_system', AccessPermissionsModel::MODULE_GID)]
            ];
        }
        $this->view->assign($result);
        $this->view->render();
    }

}
