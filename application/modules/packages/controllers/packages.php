<?php

namespace Pg\Modules\Packages\Controllers;

use Pg\Libraries\View;

use Pg\Libraries\EventDispatcher;
use Pg\Modules\Packages\Models\Events\EventPackages;

/**
 * Packages user side controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Mikhail Chernov <mchernov@pilotgroup.net>
 *
 * @version $Revision: 2 $ $Date: 2010-04-02 15:07:07 +0300 (Ср, 02 апр 2010) $ $Author: mchernov $
 **/
class Packages extends \Controller
{
    /**
     * link to CodeIgniter object
     *
     * @var object
     */

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model("Packages_model");
    }

    public function index()
    {
        $order_by["gid"] = "ASC";
        $param['where']['status'] = 1;
        $packages = $this->Packages_model->get_packages_list($param, null, $order_by);
        $this->view->assign('packages', $packages);

        $this->load->model('Menu_model');
        $this->Menu_model->breadcrumbs_set_parent('packages_item');
        $this->view->render('packages');
    }

    public function package($package_gid)
    {
        $user_id = $this->session->userdata('user_id');

        $this->load->model('users/models/Auth_model');
        $this->Auth_model->update_user_session_data($user_id);

        $data = $this->Packages_model->get_package_by_gid($package_gid);

        if (!$data['status']) {
            show_404();

            return;
        }

        if ($this->input->post('btn_system') || $this->input->post('btn_account')) {
            if ($this->input->post('btn_account')) {
                $return = $this->Packages_model->account_package_payment($data["id"], $user_id, $data["price"]);
                if ($return !== true) {
                    $this->system_messages->addMessage(View::MSG_ERROR, $return);
                } else {
                    $redirect = $this->session->userdata('service_redirect');
                    $this->session->set_userdata(array('service_redirect' => ''));
                    $this->load->model('users/models/Auth_model');
                    $this->Auth_model->update_user_session_data($user_id);
                    $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_services_apply', 'services'));
                    redirect($redirect);
                }
            } elseif ($this->input->post('btn_system')) {
                $system_gid = $this->input->post('system_gid', true);
                if (empty($system_gid)) {
                    $this->system_messages->addMessage(View::MSG_ERROR, l('error_select_payment_system', 'services'));
                } else {
                    $this->Packages_model->system_package_payment($system_gid, $user_id, $data["id"], $data["price"]);
                    $redirect = $this->session->userdata('service_redirect');
                    $this->session->set_userdata(array('service_redirect' => ''));
                    $this->load->model('users/models/Auth_model');
                    $this->Auth_model->update_user_session_data($user_id);
                }
            }
        }

        if ($data["pay_type"] == 1 || $data["pay_type"] == 2) {
            $this->load->model("Users_payments_model");
            $data["user_account"] = $this->Users_payments_model->get_user_account($user_id);
            if ($data["user_account"] <= 0 && $data["price"] > 0) {
                $data["disable_account_pay"] = true;
            } elseif ($data["price"] > $data["user_account"]) {
                $data["disable_account_pay"] = true;
            }
        }

        if ($data["pay_type"] == 2 || $data["pay_type"] == 3) {
            $this->load->model("payments/models/Payment_systems_model");
            $billing_systems = $this->Payment_systems_model->get_active_system_list();
            $this->view->assign('billing_systems', $billing_systems);
        }
        
        $event_handler = EventDispatcher::getInstance();
        $event = new EventPackages();
        $event_handler->dispatch('users_view_package_form', $event);

        $this->view->assign('is_module_installed', $this->pg_module->is_module_installed('users_payments'));
        $this->view->assign('data', $data);
        $this->view->assign('packages', array($data));

        $this->load->model('Menu_model');
        $this->Menu_model->breadcrumbs_set_parent('packages_item');
        $this->Menu_model->breadcrumbs_set_active($data['name']);
        $this->view->render('package_form');
    }

    public function my($page = 1)
    {
        $id_user = $this->session->userdata('user_id');
        $this->load->model('packages/models/Packages_users_model');

        $params['where']['id_user'] = $id_user;
        $user_packages_count = $this->Packages_users_model->get_user_packages_count($params);

        $items_on_page = 20;
        $this->load->helper('sort_order');
        $page = get_exists_page_number($page, $user_packages_count, $items_on_page);

        $user_packages = $this->Packages_users_model->get_user_packages_list(null, $params);
        $this->view->assign('user_packages', $user_packages);

        $this->load->helper("navigation");
        $page_data = get_user_pages_data(site_url() . 'packages/my/', $user_packages_count, $items_on_page, $page, 'briefPage');
        $this->view->assign('page_data', $page_data);

        $this->load->model('Menu_model');
        $this->Menu_model->breadcrumbs_set_parent('my_packages_item');

        $this->view->render('my_packages');
    }
}
