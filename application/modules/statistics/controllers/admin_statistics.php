<?php

namespace Pg\Modules\Statistics\Controllers;

/**
 * Statistics module
 *
 * @package 	PG_Dating
 *
 * @copyright 	Copyright (c) 2000-2014 PG Dating Pro - php dating software
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
use Pg\Libraries\View;
use Pg\Modules\Statistics\Models\Statistics;

/**
 * Statistics admin side controller
 *
 * @package 	PG_Dating
 * @subpackage 	Statistics
 *
 * @category	controllers
 *
 * @copyright 	Copyright (c) 2000-2014 PG Dating Pro - php dating software
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
class Admin_Statistics extends \Controller
{

    /**
     * Constructor
     *
     * @return Statistics_start
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Menu_model');
        $this->Menu_model->set_menu_active_item('admin_menu', 'system-items');
    }

    /**
     * Display index page
     *
     * @return void
     */
    public function index($filter = "all")
    {
        $this->load->model("statistics/models/systems/Statistics_users_model");
        $this->load->model("Statistics_model");

        $this->Statistics_model->parse_statistics();

        if (!in_array($filter, array("all", "used"))) {
            $filter = "all";
        }

        $current_settings = isset($_SESSION["systems_list"]) ? $_SESSION["systems_list"] : array();
        if (!isset($current_settings["filter"])) {
            $current_settings["filter"] = $filter;
        }
        $_SESSION["systems_list"] = $current_settings;

        $filter_data["all"] = $this->Statistics_model->getSystemsCount();
        $params = array();
        $params["where"]["status"] = 1;
        $filter_data["used"] = $this->Statistics_model->getSystemsCount($params);

        $this->view->assign('filter', $filter);
        $this->view->assign('filter_data', $filter_data);

        $systems_count = $filter_data[$filter];
        $systems = [];

        if ($systems_count > 0) {
            switch ($filter) {
                case "all": $params = array();
                    break;
                case "used": $params["where"]["status"] = 1;
                    break;
            }

            $order_by["name"] = "ASC";
            $systems = $this->Statistics_model->getSystemsList($params, null, null, $order_by);
            $this->view->assign('systems', $systems);
        }
        $this->view->assign('systems', $systems);
        $this->view->setHeader(l('admin_header_list', 'statistics'));
        $this->view->render('index');
    }

    /**
     *  Install modules for statistics
     *
     *   @return void
     */
    public function install()
    {
        $this->load->model("Statistics_model");
        $payments = array();

        $path = MODULEPATH . 'statistics/models/systems/';
        if (!is_dir($path)) {
            return;
        }
        $params = array();
        $order_by["name"] = "ASC";
        $systems_installed = $this->Statistics_model->getSystemsInstalled();

        $allFiles = scandir($path);
        $files = array_diff($allFiles, array('.', '..'));
        foreach ($files as $file) {
            $result = explode('.', $file);
            if (!empty($result[1])) {
                $system_gid = explode('_', $result[0]);
                if (!in_array($system_gid[1], $systems_installed)) {
                    $systems[$system_gid[1]]['name'] = $system_gid[1];
                    $systems[$system_gid[1]]['gid'] = $system_gid[1];
                    $this->load->model("statistics/models/systems/" . $result[0]);
                    $systems[$system_gid[1]]['activities'] = $this->{$result[0]}->get_system_data();
                    $systems[$system_gid[1]]['events'] = $this->{$result[0]}->get_events_list(true);
                }
            }
        }

        $filter_data["all"] = $this->Statistics_model->getSystemsCount();
        $params = array();
        $params["where"]["status"] = 1;
        $filter_data["used"] = $this->Statistics_model->getSystemsCount($params);
        $this->view->assign('filter_data', $filter_data);
        $this->view->assign('systems', $systems);
        $this->view->render('list_install');
    }

    public function install_system($gid = null)
    {
        if (!empty($gid)) {
            $system_model_name = "statistics_" . $gid . "_model";
            $this->load->model("statistics/models/systems/" . $system_model_name);
            if ($this->{$system_model_name}->install_system()) {
                redirect(site_url() . "admin/statistics/index/used");
            }
            redirect(site_url() . "admin/statistics/install");
        }
        redirect(site_url() . "admin/statistics/install");
    }

    public function activate($system_id, $status = 0)
    {
        $this->load->model("Statistics_model");
        if (!empty($system_id)) {
            $this->Statistics_model->activate_system($system_id, $status);
            if ($status) {
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_activate_system', 'statistics'));
            } else {
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_deactivate_system', 'statistics'));
            }
        }
        $cur_set = $_SESSION["systems_list"];
        $url = site_url() . "admin/statistics/index/{$cur_set["filter"]}";
        redirect($url);
    }

    public function uninstall($system_id)
    {
        if (empty($system_id)) {
            
        }

        $this->load->model("Statistics_model");
        $system = $this->Statistics_model->getSystemById($system_id);

        $gid = $system["module"];
        $url = site_url() . "admin/statistics/uninstall_system/{$gid}";
        redirect($url);
    }

    public function uninstall_system($gid = null)
    {
        if (!empty($gid)) {
            $system_model_name = "statistics_" . $gid . "_model";
            $this->load->model("statistics/models/systems/" . $system_model_name);
            if ($this->{$system_model_name}->uninstall_system()) {
                redirect(site_url() . "admin/statistics/index/used");
            }
            redirect(site_url() . "admin/statistics/install");
        }
        redirect(site_url() . "admin/statistics/index/used");
    }

    public function reset($system_id)
    {
        if (empty($system_id)) {
            
        }

        $this->load->model("Statistics_model");
        $system = $this->Statistics_model->getSystemById($system_id);

        $this->load->model("statistics/models/systems/" . $system['model']);
        if ($this->{$system['model']}->reset_system_statistics()) {
            redirect(site_url() . "admin/statistics/index/used");
        }
        redirect(site_url() . "admin/statistics/install");
    }

    public function activate_event($system_id, $event_gid, $status = 0)
    {
        $this->load->model("Statistics_model");
        if (!empty($system_id) && !empty($event_gid)) {
            $this->Statistics_model->activate_event($system_id, $event_gid, $status);
            if ($status) {
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_activate_event', 'statistics'));
            } else {
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_deactivate_event', 'statistics'));
            }
        }
        $cur_set = $_SESSION["systems_list"];
        $url = site_url() . "admin/statistics/index/{$cur_set["filter"]}";
        redirect($url);
    }

}
