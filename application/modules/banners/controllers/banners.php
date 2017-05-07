<?php

namespace Pg\Modules\Banners\Controllers;

/**
 * Banners module
 *
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
use Pg\Libraries\View;

/**
 * Banners user side controller
 *
 *
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
class Banners extends \Controller
{
    /**
     * Class controller
     *
     * @return Banners
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Banners_model');
        $this->load->model('Menu_model');
    }

    /**
     * Go to banner page
     *
     * Redirect user to the url defined in banner object.
     *
     * @param integer $banner_id banner identifier
     *
     * @return void
     */
    public function go($banner_id = null)
    {
        $banner_id = (is_numeric($banner_id) and $banner_id > 0) ? intval($banner_id) : 0;
        if (!$banner_id or !($banner_obj = $this->Banners_model->get($banner_id))) {
            show_404();
        }

        // add info to statistic
        $this->load->model('banners/models/Banners_stat_model');
        $this->Banners_stat_model->add_hit($banner_id);

        $stat = $this->Banners_model->get_banner_overall_stat($banner_id);
        $this->Banners_model->save_banner_clicks($banner_id, $stat["stat_clicks"] + 1);

        $url = isset($banner_obj['link']) ? prep_url($banner_obj['link']) : '';
        if ($url) {
            redirect($url);
        }
    }

    /**
     * List of my banners
     *
     * @param integer $page page of results
     *
     * @return void
     */
    public function my($page = 1)
    {
        if ($this->session->userdata("auth_type") != "user") {
            show_404();

            return;
        }
        $params["where"]["user_id"] = $this->session->userdata("user_id");
        $cnt_banners = $this->Banners_model->cnt_banners($params);

        $items_on_page = $this->pg_module->get_module_config('banners', 'items_per_page');
        $this->load->helper('sort_order');
        $page = get_exists_page_number($page, $cnt_banners, $items_on_page);

        $banners = $this->Banners_model->get_banners($page, $items_on_page, array("id" => "DESC"), $params);
        // get place objects for banner
        if ($banners) {
            $this->load->model('banners/models/Banner_place_model');
            foreach ($banners as $key => $banner) {
                $banners[$key]['banner_place_obj'] = $banner['banner_place_id'] ? $this->Banner_place_model->get($banner['banner_place_id']) : null;
            }
        }
        $this->view->assign('banners', $banners);

        $this->load->helper("navigation");
        $page_data = get_user_pages_data(site_url() . "banners/my/", $cnt_banners, $items_on_page, $page, 'briefPage');
        $page_data['date_format'] = $this->pg_date->get_format('date_literal', 'st');
        $this->view->assign('page_data', $page_data);

        $user_type = $this->session->userdata("user_type");
        $this->Menu_model->breadcrumbs_set_active(l('header_my_banners', 'banners'), site_url() . 'users/account/banners');

        $this->view->render('my_list');
    }

    /**
     * Edit banner data
     *
     * Create new banner.
     *
     * POST:
     *
     * @param string  $name            banner name
     * @param integer $banner_place_id banner place
     * @param string  $link            banner link
     * @param string  $alt_text        alt value
     * @param string  $expiration_data expired date
     *
     * @return void
     */
    public function edit()
    {
        if ($this->session->userdata("auth_type") != "user") {
            show_404();

            return;
        }

        if ($this->input->post('btn_save')) {
            $post_data = array(
                "name"             => $this->input->post("name", true),
                "banner_type"      => 1,
                "banner_place_id"  => $this->input->post("banner_place_id", true),
                "not_in_rotation"  => 0,
                "status"           => 0,
                "link"             => $this->input->post("link", true),
                "alt_text"         => $this->input->post("alt_text", true),
                "number_of_clicks" => 0,
                "number_of_views"  => 0,
                "stat_clicks"      => 0,
                "stat_views"       => 0,
                "new_window"       => 1,
                "expiration_date"  => $this->input->post("expiration_date", true),
            );

            $validate_data = $this->Banners_model->validate_banner(null, $post_data, 'banner_image_file');
            if (!empty($validate_data["errors"])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data["errors"]);
                $this->view->assign('data', $validate_data["data"]);
            } else {
                $banner_id = $this->Banners_model->saveUserBanner(null, $validate_data["data"], 'banner_image_file');

                $send_admin_mail = $this->pg_module->get_module_config("banners", "moderation_send_mail");
                if ($send_admin_mail) {
                    $emails = $this->pg_module->get_module_config("banners", "admin_moderation_emails");
                    if ($emails) {
                        $this->load->model('Notifications_model');
                        $this->Notifications_model->send_notification($emails, 'banner_need_moderate', array());
                    }
                }

                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_update_banner_data', 'banners'));
                redirect(site_url() . 'users/account/banners/my');
            }
        }

        // get banner places
        $this->load->model('banners/models/Banner_place_model');
        $places = $this->Banner_place_model->get_all_places(1);
        $this->view->assign('places', $places);

        $this->Menu_model->breadcrumbs_set_active(l('link_add_banner', 'banners'));
        $this->view->render('my_form');
    }

    /**
     * Activate banner
     *
     * @param integer $banner_id banner identifier
     *
     * @return void
     */
    public function activate($banner_id)
    {
        if ($this->session->userdata("auth_type") != "user") {
            show_404();
            return;
        }
        $user_type = $this->session->userdata('user_type');

        $services_available = $this->pg_module->is_module_active('services');

        if ($services_available) {
            $this->load->model('Services_model');
            $service_data = $this->Services_model->get_service_by_gid('banner_service');
            if (empty($service_data) || !$service_data["status"]) {
                $this->system_messages->addMessage(View::MSG_ERROR, l('error_empty_service_activate_service', 'banners'));
            }
            $this->view->assign('service_data', $service_data);
        } else {
            $this->system_messages->addMessage(View::MSG_ERROR, l('error_empty_service_activate_service', 'banners'));
            redirect(site_url() . 'users/account/banners');
        }

        $banner = $this->Banners_model->get($banner_id);
        $info = $this->Banners_model->get_user_activate_info($banner_id);

        $this->load->model('banners/models/Banner_place_model');
        $place = $this->Banner_place_model->get($banner["banner_place_id"]);
        $place['places_in_rotation'] = intval($place['places_in_rotation']);

        $this->load->model('banners/models/Banner_group_model');
        $groups = $this->Banner_group_model->get_all_groups_key_id();

        $gids = array_keys($groups);
        $fill_places = $this->Banner_group_model->get_fill_positions($gids, $banner["banner_place_id"], $banner['id']);
        foreach ($groups as $k => $group) {
            $groups[$k]["free_positions"] = $place['places_in_rotation'];
            if (!empty($fill_places[$group["id"]])) {
                $groups[$k]["free_positions"] = $groups[$k]["free_positions"] - $fill_places[$group["id"]];
                if ($groups[$k]["free_positions"] < 0) {
                    $groups[$k]["free_positions"] = 0;
                }
            }
        }

        if ($this->input->post('btn_activate')) {
            $used_position = $this->input->post('used_position', true);
            $validate = array("positions" => array(), "sum" => 0);
            if ($used_position) {
            foreach ((array) $used_position as $group_id => $used_position) {
                $used_position = intval($used_position);
                if ($used_position > $groups[$group_id]["free_positions"]) {
                    $used_position = $groups[$group_id]["free_positions"];
                }
                if ($used_position < 0) {
                    $used_position = 0;
                }
                if ($used_position) {
                    $validate["positions"][$group_id] = $used_position;
                    $validate["sum"] += $used_position * $groups[$group_id]["price"];
                }
            }
            }
            if ($validate["sum"] <= 0) {
                $this->system_messages->addMessage(View::MSG_ERROR, l('error_empty_activate_banner_sum', 'banners'));
                $info = $validate;
            } else {
                $base_currency_gid = $this->pg_module->get_module_config("start", "base_currency");
                $this->load->model('payments/models/Payment_currency_model');
                $base_currency = $this->Payment_currency_model->get_currency_default(true);

                $this->Banners_model->set_user_activate_info($banner_id, $validate);
                $this->session->set_userdata('service_redirect', site_url() . 'users/account/banners');
                $this->load->helper('payments');
                $this->load->helper('start');

                post_location_request(site_url() . 'services/form/banner_service', array('price' => $validate["sum"], 'id_banner_payment' => $banner_id));
            }
        }
        foreach ($groups as $k => $group) {
            $groups[$k]["user_positions"] = (!empty($info["positions"][$group["id"]])) ? $info["positions"][$group["id"]] : 0;
            if (in_array($group["id"], $banner['banner_groups'])) {
                $groups[$k]['status'] = 1;
            } else {
                $groups[$k]['status'] = 0;
            }
        }
        $this->view->assign('groups', $groups);

        $period = $this->pg_module->get_module_config("banners", "period");
        $this->view->assign('period', $period);

        if ($this->pg_module->is_module_active('payments')) {
            $this->load->model('payments/models/Payment_currency_model');
            $base_currency = $this->Payment_currency_model->get_currency_default(true);
            $this->view->assign('base_currency', $base_currency);
        }

        $this->view->setBackLink(site_url() . "users/account/banners");

        $this->Menu_model->breadcrumbs_set_active(l('header_my_banners', 'banners'), site_url() . 'users/account/banners');
        $this->Menu_model->breadcrumbs_set_active(l('header_my_banner_activate', 'banners'), site_url() . 'banners/activate/' . $banner_id);

        $this->view->render('my_activate');
    }

    /**
     * Remove banner
     *
     * @param integer $banner_id banner identifier
     *
     * @return void
     */
    public function delete($banner_id)
    {
        if ($this->session->userdata("auth_type") != "user") {
            show_404();

            return;
        }
        $banner = $this->Banners_model->get($banner_id);

        if ($this->session->userdata('user_id') != $banner["user_id"]) {
            show_404();

            return;
        }
        $this->Banners_model->delete($banner_id);
        $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_delete_banner', 'banners'));
        redirect(site_url() . 'users/account/banners');
    }

    /**
     * Banner statistics
     *
     * @param integer $banner_id banner identifier
     * @param integer $year      year value
     * @param integer $month     month value
     * @param integer $day       day value
     *
     * @return void
     */
    public function statistic($banner_id, $stat_type = "day", $year = '', $month = '', $week = '', $day = '')
    {
        $banner = $this->Banners_model->get($banner_id);

        if ($this->session->userdata("auth_type") != "user" || $this->session->userdata('user_id') != $banner["user_id"]) {
            show_404();

            return;
        }

        $this->load->model('banners/models/Banners_stat_model');

        $banner_data = $this->Banners_model->get($banner_id);
        $this->view->assign('banner_data', $banner_data);

        if (!$stat_type) {
            $stat_type = "day";
        }
        switch ($stat_type) {
            case "year":
                if (!$year) {
                    $year = date("Y");
                }
                $statistic = $this->Banners_stat_model->get_year_statistic($banner_id, $year);
                $navigation["current"] = $year;
                $navigation["prev"] = site_url() . "banners/statistic/" . $banner_id . "/year/" . ($year - 1);
                $navigation["next"] = site_url() . "banners/statistic/" . $banner_id . "/year/" . ($year + 1);
            break;
            case "month":
                if (!$year || !$month) {
                    $year = date("Y");
                    $month = intval(date("m"));
                }
                $statistic = $this->Banners_stat_model->get_month_statistic($banner_id, $year, $month);
                $uts = mktime(0, 0, 0, $month, 1, $year);
                $uts_prev = mktime(0, 0, 0, $month - 1, 1, $year);
                $uts_next = mktime(0, 0, 0, $month + 1, 1, $year);
                $navigation["current"] = strftime("%b %Y", $uts);
                $navigation["prev"] = site_url() . "banners/statistic/" . $banner_id . "/month/" . date("Y", $uts_prev) . "/" . date("m", $uts_prev);
                $navigation["next"] = site_url() . "banners/statistic/" . $banner_id . "/month/" . date("Y", $uts_next) . "/" . date("m", $uts_next);
            break;
            case "week":
                if (!$year || !$week) {
                    $uts = time();
                    $first_weekday = $this->Banners_stat_model->get_first_weekday_uts($uts);
                    $year = date("Y", $first_weekday);
                    $week = date("W", $first_weekday);
                } else {
                    $first_weekday = strtotime($year . '0104 +' . ($week - 1)  . ' weeks');
                    $first_weekday = $this->Banners_stat_model->get_first_weekday_uts($first_weekday);
                }
                $navigation["current"] = strftime("%d %b %Y", $first_weekday) . " - " . strftime("%d %b %Y", $first_weekday + 6 * 24 * 60 * 60);
                $navigation["prev"] = site_url() . "banners/statistic/" . $banner_id . "/week/" . date("Y", $first_weekday - 7 * 24 * 60 * 60) . "/0/" . date("W", $first_weekday - 7 * 24 * 60 * 60) . "/";
                $navigation["next"] = site_url() . "banners/statistic/" . $banner_id . "/week/" . date("Y", $first_weekday + 7 * 24 * 60 * 60) . "/0/" . date("W", $first_weekday + 7 * 24 * 60 * 60) . "/";
                $statistic = $this->Banners_stat_model->get_week_statistic($banner_id, $year, $week);
            break;
            case "day":
                if (!$year || !$month || !$day) {
                    $year = date("Y");
                    $month = intval(date("m"));
                    $day = intval(date("d"));
                }
                $statistic = $this->Banners_stat_model->get_day_statistic($banner_id, $year, $month, $day);

                $uts = mktime(4, 0, 0, $month, $day, $year);
                $navigation["current"] = strftime("%d %b %Y", $uts);
                $navigation["prev"] = site_url() . "banners/statistic/" . $banner_id . "/day/" . date("Y", $uts - 24 * 60 * 60) . "/" . date("m", $uts - 24 * 60 * 60) . "/0/" . date("d", $uts - 24 * 60 * 60);
                $navigation["next"] = site_url() . "banners/statistic/" . $banner_id . "/day/" . date("Y", $uts + 24 * 60 * 60) . "/" . date("m", $uts + 24 * 60 * 60) . "/0/" . date("d", $uts + 24 * 60 * 60);
            break;
        }

        $this->view->assign('navigation', $navigation);
        $this->view->assign('stat_type', $stat_type);
        $this->view->assign('statistic', $statistic);

        $this->Menu_model->breadcrumbs_set_active(l('header_my_banners', 'banners'), site_url() . 'users/account/banners');
        $this->Menu_model->breadcrumbs_set_active(l('header_my_banner_statistic', 'banners'), site_url() . 'banners/statistic/' . $banner_id);

        $this->view->render('my_statistic');
    }

    /**
     * Update statistics
     *
     * @return void
     */
    public function update_statistic()
    {
        $this->load->model('banners/models/Banners_stat_model');
        $date = date("Y-m-d");
        $this->Banners_stat_model->update_file_statistic();
        $this->Banners_stat_model->update_day_statistic($date);
        $this->Banners_stat_model->update_week_statistic($date);
        $this->Banners_stat_model->update_month_statistic($date);
        $this->Banners_stat_model->update_year_statistic($date);

        return;
    }

}
