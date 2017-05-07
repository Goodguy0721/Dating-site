<?php

namespace Pg\Modules\Banners\Controllers;

/**
 * Banners module
 *
 * @author      Pilot Group Ltd <http://www.pilotgroup.net/>
 */
use Pg\Libraries\View;

/**
 * Banners admin side controller
 *
 *
 *
 * @author      Pilot Group Ltd <http://www.pilotgroup.net/>
 */
class Admin_Banners extends \Controller
{
    /**
     * Allow edit config settings
     *
     * @var object
     */
    private $allow_config_add = false;

    /**
     * Controller
     *
     * @return Admin_Banners
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Banners_model');
        $this->load->model('Menu_model');
        $this->Menu_model->set_menu_active_item('admin_menu', 'banners_menu_item');
    }

    /*
     * BANNER FUNCTIONS
     */

    /**
     * Banners list
     *
     * Available views: admin and user
     *
     * @param string  $view_type view type
     * @param integer $page      page of results
     *
     * @return void
     */
    public function index($view_type = "admin", $page = 1)
    {
        if ($view_type == "admin") {
            $params["where"]["user_id"] = 0;
        } else {
            $params["where"]["user_id !="] = 0;
        }
        $_SESSION["banners_list"]["view_type"] = $view_type;

        $cnt_banners = $this->Banners_model->cnt_banners($params);

        $items_on_page = $this->pg_module->get_module_config('start', 'admin_items_per_page');
        $this->load->helper('sort_order');
        $page = get_exists_page_number($page, $cnt_banners, $items_on_page);

        $this->Banners_model->set_format_settings('get_user', true);
        $banners = $this->Banners_model->get_banners($page, $items_on_page, array("id" => "DESC"), $params);
        $this->Banners_model->set_format_settings('get_user', false);

        // get place objects for banner
        if ($banners) {
            $this->load->model('banners/models/Banner_place_model');
            foreach ($banners as $key => $banner_obj) {
                $banners[$key]['banner_place_obj'] = $banner_obj['banner_place_id'] ? $this->Banner_place_model->get($banner_obj['banner_place_id']) : null;

                $views_left = $banners[$key]['number_of_views'] - $banners[$key]['stat_views'];
                if ($views_left <= 0) {
                    $views_left = 0;
                }
                $banners[$key]['views_left'] = $views_left;

                $clicks_left = $banners[$key]['number_of_clicks'] - $banners[$key]['stat_clicks'];
                if ($clicks_left <= 0) {
                    $clicks_left = 0;
                }
                $banners[$key]['clicks_left'] = $clicks_left;
            }
        }

        $this->view->assign('banners', $banners);

        unset($params["where"]);
        $params["where"]["user_id"] = 0;
        $filter_data["admin"] = $this->Banners_model->cnt_banners($params);
        unset($params["where"]);
        $params["where"]["user_id !="] = 0;
        $filter_data["user"] = $this->Banners_model->cnt_banners($params);
        $this->view->assign('filter_data', $filter_data);

        $this->load->helper("navigation");

        $url = site_url() . "admin/banners/index/" . $view_type . "/";
        $pages_data = get_admin_pages_data($url, $cnt_banners, $items_on_page, $page, 'briefPage');
        $pages_data["view_type"] = $view_type;

        $pages_data['date_format'] = $this->pg_date->get_format('date_literal', 'st');
        $this->view->assign('page_data', $pages_data);

        $this->Menu_model->set_menu_active_item('admin_banners_menu', 'banners_list_item');
        $this->view->setHeader(l('admin_header_banners_list', 'banners'));
        $this->view->render('list_banners');
    }

    /**
     * Edit banner data
     *
     * @param integer $banner_id banner identifier
     * @param integer $approve   if turn on that need approve after saving
     *
     * @return void
     */
    public function edit($banner_id = null, $approve = 0)
    {
        if ($banner_id) {
            $this->Banners_model->set_format_settings('get_user', true);
            $data = $this->Banners_model->get($banner_id);
            $this->Banners_model->set_format_settings('get_user', false);
        } else {
            $data = array();
        }
        if ($this->input->post('btn_save')) {
            $post_data = array(
                "name"            => $this->input->post("name", true),
                "banner_type"     => $this->input->post("banner_type", true),
                "banner_place_id" => $this->input->post("banner_place_id", true),
                "banner_groups"   => (array) $this->input->post("banner_groups", true),
                "status"          => $this->input->post("status", true),
                "link"               => $this->input->post("link", true),
                "alt_text"           => $this->input->post("alt_text", true),
                "number_of_clicks"   => $this->input->post("number_of_clicks", true),
                "number_of_views"    => $this->input->post("number_of_views", true),
                "new_window"         => $this->input->post("new_window", true),
                "expiration_date"    => $this->input->post("expiration_date", true),
                "expiration_date_on" => $this->input->post("expiration_date_on", true),
                "html" => $this->input->post("html", false),
            );

            $validate_data = $this->Banners_model->validate_banner($banner_id, $post_data, 'banner_image_file');
            if (!empty($validate_data["errors"])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data["errors"]);
                $data = array_merge($data, $validate_data["data"]);
            } else {
                if ($this->input->post('banner_image_delete') && $banner_id && $data["banner_image"]) {
                    $this->load->model("Uploads_model");
                    $this->Uploads_model->delete_upload($this->News_model->upload_config_id, $data["prefix"], $data["banner_image"]);
                    $validate_data["data"]["banner_image"] = '';
                }
                if (empty($banner_id)) {
                    $validate_data["data"]["is_admin"] = 1;
                } else {
                    $validate_data["data"]["is_admin"] = $data["is_admin"];
                }
                
                $validate_data["data"]["approve"] = 1;

                $change = true;
                if ($data['name'] == $validate_data["data"]) {
                    $change = false;
                }

                $diff = array_diff($data['banner_groups'], $validate_data["data"]["banner_groups"]);
                if (empty($diff)) {
                    $change = false;
                }

                if (empty($validate_data["data"]["banner_groups"])) {
                    $change = false;
                }

                $banner_id = $this->Banners_model->save($banner_id, $validate_data["data"], 'banner_image_file', $change);

                if ($approve) {
                    $url = site_url() . 'admin/banners/approve/' . $banner_id . "/1";
                } else {
                    $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_update_banner_data', 'banners'));
                    $url = site_url() . 'admin/banners/index/' . $_SESSION["banners_list"]["view_type"];
                }

                redirect($url);
            }
        }

        // get banner places
        $this->load->model('banners/models/Banner_place_model');
        $places = $this->Banner_place_model->get_all_places();
        $this->view->assign('places', $places);

        if (!empty($banner_id) || !empty($validate_data["errors"])) {
            if (!empty($validate_data["errors"])) {
                $banner_groups = $validate_data["data"]["banner_groups"];
                $banner_data = $validate_data["data"];
            } else {
                $banner_data = $banner_groups = array();
            }

            $banner_place_block = $this->_get_groups($data["banner_place_id"], $banner_id, $banner_groups);
            $this->view->assign('banner_place_block', $banner_place_block);

            $banner_type_block = $this->_show_form($data["banner_type"], $banner_id, $banner_data);
            $this->view->assign('banner_type_block', $banner_type_block);
        }

        $this->view->assign('data', $data);

        $this->view->assign('banner_type_lang', ld('banner_type', 'banners'));

        $this->Menu_model->set_menu_active_item('admin_banners_menu', 'banners_list_item');
        $this->view->setHeader(l('admin_header_banners_list', 'banners'));
        $this->view->render('form_banner');
    }

    public function view($banner_id = null)
    {
        if ($banner_id) {
            $this->Banners_model->set_format_settings('get_user', true);
            $data = $this->Banners_model->get($banner_id);
            $this->Banners_model->set_format_settings('get_user', false);
        } else {
            $data = array();
        }

        // get banner places
        $this->load->model('banners/models/Banner_place_model');
        $places = $this->Banner_place_model->get_all_places();
        $this->view->assign('places', $places);

        if (!empty($banner_id) || !empty($validate_data["errors"])) {
            if (!empty($validate_data["errors"])) {
                $banner_groups = $validate_data["data"]["banner_groups"];
                $banner_data = $validate_data["data"];
            } else {
                $banner_data = $banner_groups = array();
            }

            $banner_place_block = $this->_get_groups($data["banner_place_id"], $banner_id, $banner_groups);
            $this->view->assign('banner_place_block', $banner_place_block);

            $banner_type_block = $this->_show_form($data["banner_type"], $banner_id, $banner_data);
            $this->view->assign('banner_type_block', $banner_type_block);
        }

        $this->view->assign('data', $data);

        $this->view->assign('banner_type_lang', ld('banner_type', 'banners'));

        $this->Menu_model->set_menu_active_item('admin_banners_menu', 'banners_list_item');
        $this->view->setHeader(l('admin_header_banners_list', 'banners'));
        $this->view->render('view_banner');
    }

    /**
     * Load available banner pages
     *
     * @param integer $place_id  place identifier
     * @param integer $banner_id banner identifier
     *
     * @return void
     */
    public function ajax_get_groups($place_id, $banner_id = null)
    {
        echo $this->_get_groups($place_id, $banner_id);
    }

    /**
     * Load banner's groups
     *
     * @param integer $place_id      [lace identifier
     * @param integer $banner_id     banner identifier
     * @param array   $banner_groups banner's groups
     *
     * @return void
     */
    protected function _get_groups($place_id, $banner_id = null, $banner_groups = array())
    {
        $this->load->model('banners/models/Banner_place_model');
        $place_groups_id = $this->Banner_place_model->get_place_group_ids($place_id);

        $this->load->model('banners/models/Banner_group_model');
        $groups = $this->Banner_group_model->get_groups($place_groups_id);

        $banner_id = (is_numeric($banner_id) and $banner_id > 0) ? intval($banner_id) : 0;
        if ($banner_id) {
            $banner = $this->Banners_model->get($banner_id);
            $banner_groups = (isset($banner['banner_groups']) and is_array($banner['banner_groups'])) ? $banner['banner_groups'] : array();
            $this->view->assign('groups_disabled', !$banner['is_admin'] && $banner['status']);
        }
        $this->view->assign('banner_groups', $banner_groups);

        $this->view->assign('groups', $groups);

        return $this->view->fetch('ajax_banner_groups');
    }

    /**
     * Load form of banner settings by ajax
     *
     * @param integer $banner_type banner type
     * @param integer $banner_id   banner identifier
     *
     * @return void
     */
    public function ajax_show_form($banner_type = null, $banner_id = null)
    {
        echo $this->_show_form($banner_type, $banner_id);
    }

    /**
     * Load form of banner settings
     *
     * @param integer $banner_type  banner type
     * @param integer $banner_id    banner identifier
     * @param array   $default_data default parameters
     *
     * @return void
     */
    protected function _show_form($banner_type = null, $banner_id = null, $default_data = array())
    {
        // get banner object if exist object_id
        $this->Banners_model->set_format_settings('get_user', true);
        $data = $banner_id ? $this->Banners_model->get($banner_id) : $default_data;
        $this->Banners_model->set_format_settings('get_user', false);
        $this->view->assign('data', $data);

        $page_data['date_format'] = $this->pg_date->get_format('date_literal', 'st');
        $this->view->assign('page_data', $page_data);

        if ($banner_type == '1') {
            return $this->view->fetch('ajax_banner_image_form');
        } elseif ($banner_type == '2') {
            return $this->view->fetch('ajax_banner_html_form');
        }
    }

    /**
     * Remove banner
     *
     * @param integer $id banner identifier
     *
     * @return void
     */
    public function delete($id = null)
    {
        $id = (is_numeric($id) and $id > 0) ? intval($id) : 0;
        if (!$id) {
            show_404();
        }

        $this->Banners_model->delete($id);
        $this->load->model('menu/models/Indicators_model');
        $this->Indicators_model->delete('new_banner_item', $id, true);
        $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_delete_banner', 'banners'));
        redirect(back_url('admin/banners/index/' . $_SESSION["banners_list"]["view_type"]));
    }

    /**
     * Activate/de-activate banner
     *
     * Available statuses:
     * 1 - activate banner
     * 0 - de-activate banner
     *
     * @param integer $banner_id banner identifier
     * @param integer $status    activity status
     *
     * @return void
     */
    public function activate($banner_id, $status)
    {
        $banner_id = intval($banner_id);
        $status = intval($status);

        if ($banner_id) {
            $this->Banners_model->save_banner_status($banner_id, $status);
            $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_update_banner_data', 'banners'));
        }
        redirect(site_url() . "admin/banners/index/" . $_SESSION["banners_list"]["view_type"]);
    }

    /**
     * Approve/decline banner
     *
     * Available statuses:
     * 1 - approve banner
     * 0 - decline banner
     *
     * @param integer $banner_id banner identifier
     * @param integer $status    activity status
     *
     * @return void
     */
    public function approve($banner_id, $status)
    {
        $banner_id = (int)$banner_id;
        $status = (int)$status;

        if ($banner_id) {
            if ($status > 0) {
                if (!$this->Banners_model->approve($banner_id)) {
                    redirect(site_url() . "admin/banners/edit/" . $banner_id . "/1");
                }
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_banner_approved', 'banners'));
            } else {
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_banner_declined', 'banners'));
            }
        }

        redirect(site_url() . "admin/banners/index/" . $_SESSION["banners_list"]["view_type"]);
    }

    //---------------------------------------------------------------------------------------------------------//

    /* BANNER PLACE FUNCTIONS */

    /**
     * Edit banner place data
     *
     * @param integer $id place identifier
     *
     * @return void
     */
    public function edit_place($id = null)
    {
        $this->load->model('banners/models/Banner_place_model');
        if ($id) {
            $data = $this->Banner_place_model->get($id);
        } else {
            $data = array();
        }

        if ($this->input->post('btn_save')) {
            $post_data = array(
                "name"               => $this->input->post("name", true),
                "keyword"            => $this->input->post("keyword", true),
                "width"              => $this->input->post("width", true),
                "height"             => $this->input->post("height", true),
                "places_in_rotation" => $this->input->post("places_in_rotation", true),
                "rotate_time"        => $this->input->post("rotate_time", true),
                "access"             => $this->input->post("access", true),
                "place_groups"       => $this->input->post("place_groups", true),
            );

            $validate_data = $this->Banner_place_model->validate_place($id, $post_data);          
            if (!empty($validate_data["errors"])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data["errors"]);
                $data = array_merge($data, $validate_data["data"]);
            } else {
                $place_id = $this->Banner_place_model->save_place($id, $validate_data["data"]);
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_update_place_data', 'banners'));
                redirect(site_url() . 'admin/banners/places_list');
            }
        }
        if ($id) {
            $data["place_groups"] = $this->Banner_place_model->get_place_group_ids($id);
        }
        $this->view->assign('data', $data);

        $this->load->model('banners/models/Banner_group_model');
        $groups = $this->Banner_group_model->get_all_groups();
        if (!empty($groups) && !empty($data["place_groups"])) {
            foreach ($groups as $k => $group) {
                if (in_array($group["id"], $data["place_groups"])) {
                    $groups[$k]["selected"] = true;
                }
            }
        }
        $this->view->assign('groups', $groups);

        $this->view->assign('place_access_lang', ld('place_access', 'banners'));

        $this->Menu_model->set_menu_active_item('admin_banners_menu', 'places_list_item');
        $this->view->setHeader(l('admin_header_places_list', 'banners'));
        $this->view->render('form_place');
    }

    /**
     * List of banners' places
     *
     * @return void
     */
    public function places_list()
    {
        $this->load->model('banners/models/Banner_place_model');
        $places = $this->Banner_place_model->get_all_places();
        $this->view->assign('places', $places);

        $this->view->assign('allow_config_add', $this->allow_config_add);

        $this->Menu_model->set_menu_active_item('admin_banners_menu', 'places_list_item');
        $this->view->setHeader(l('admin_header_places_list', 'banners'));
        $this->view->render('list_places');
    }

    /**
     * Remove banner place
     *
     * @param integer $id place identifier
     *
     * @return void
     */
    public function delete_place($id = null)
    {
        $id = (is_numeric($id) and $id > 0) ? intval($id) : 0;
        if (!$id) {
            show_404();
        }

        $this->load->model('banners/models/Banner_place_model');
        $this->Banner_place_model->delete($id);
        redirect(back_url('admin/banners/places_list/'));
    }

    /* BANNER GROUPS FUNCTIONS */

    /**
     * Edit banner group data
     *
     * @param integer $id group identifier
     *
     * @return void
     */
    public function edit_group($id = null)
    {
        $this->load->model('banners/models/Banner_group_model');
        if ($id) {
            $data = $this->Banner_group_model->get_group($id);
            foreach ($this->pg_language->languages as $lang_id => $lang_data) {
                $validate_lang[$lang_id] = l('banners_group_' . $data["gid"], 'banners', $lang_id);
            }
        } else {
            $data = array();
        }

        if ($this->input->post('btn_save')) {
            $post_data = array(
                "gid"    => $this->input->post("gid", true),
                "price"  => $this->input->post("price", true),
                "period" => $this->input->post("period", true),
            );
            $langs = $this->input->post("langs", true);

            $validate_data = $this->Banner_group_model->validateGroup($id, $post_data, $langs);
            if (!empty($validate_data["errors"])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data["errors"]);
                $validate_lang = $langs;
                $data = array_merge($data, $validate_data["data"]);
            } else {
                $group_id = $this->Banner_group_model->save($id, $validate_data["data"], $validate_data['langs']);
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_update_group_data', 'banners'));
                if (!empty($id)) {
                    redirect(site_url() . 'admin/banners/groups_list');
                } else {
                    redirect(site_url() . 'admin/banners/group_pages/' . $group_id);
                }
            }
        }

        $this->view->assign('languages', $this->pg_language->languages);
        $this->view->assign('languages_count', count($this->pg_language->languages));
        $this->view->assign('cur_lang', $this->pg_language->current_lang_id);
        if (!empty($validate_lang)) {
            $this->view->assign('validate_lang', $validate_lang);
        }

        if (!empty($data)) {
            $t = $this->Banner_group_model->format_group(array(0 => $data));
            $data = $t[0];
        }

        $this->view->assign('data', $data);

        $this->Menu_model->set_menu_active_item('admin_banners_menu', 'groups_list_item');
        $this->view->setHeader(l('admin_header_groups_list', 'banners'));
        $this->view->render('form_group');
    }

    /**
     * List of banners' groups
     *
     * @return void
     */
    public function groups_list()
    {
        $this->load->model('banners/models/Banner_group_model');
        $groups = $this->Banner_group_model->get_all_groups();
        $this->view->assign('groups', $groups);

        $this->view->assign('allow_config_add', $this->allow_config_add);

        $this->Menu_model->set_menu_active_item('admin_banners_menu', 'groups_list_item');
        $this->view->setHeader(l('admin_header_groups_list', 'banners'));
        $this->view->render('list_groups');
    }

    /**
     * Remove banner group
     *
     * @param integer $id group identifier
     *
     * @return void
     */
    public function delete_group($id = null)
    {
        if (!$id) {
            show_404();
        }
        $this->load->model('banners/models/Banner_group_model');
        $this->Banner_group_model->delete($id);
        redirect(site_url() . 'admin/banners/groups_list');
    }

    /**
     * List pages of banner group
     *
     * @param integer $group_id group identifier
     *
     * @return void
     */
    public function group_pages($group_id)
    {
        $this->load->model('banners/models/Banner_group_model');

        $group_data = $this->Banner_group_model->get_group($group_id);
        $this->view->assign('group_data', $group_data);

        $modules = $this->Banner_group_model->get_used_modules();
        if (!empty($modules)) {
            foreach ($modules as $k => $module) {
                $modules[$k]["module_data"] = $this->pg_module->get_module_by_gid($module["module_name"]);
            }
        }
        $this->view->assign('modules', $modules);

        $group_pages = $this->Banner_group_model->get_group_pages($group_id);
        $this->view->assign('group_pages', $group_pages);

        $this->Menu_model->set_menu_active_item('admin_banners_menu', 'groups_list_item');
        $this->view->setHeader(l('admin_header_groups_list', 'banners'));
        $this->view->setBackLink(site_url() . 'admin/banners/groups_list');
        $this->view->render('list_group_pages');
    }

    /**
     * Load pages for specific module by ajax
     *
     * @param integer $module_id module identifier
     *
     * @return void
     */
    public function ajax_get_modules_pages($module_id)
    {
        $this->load->model('banners/models/Banner_group_model');
        $pages = $this->Banner_group_model->get_module_pages($module_id);
        $this->view->assign('pages', $pages);
        $this->view->assign('module_id', $module_id);
        $this->view->render('ajax_module_pages');
    }

    /**
     * Save list pages of banner group by ajax
     *
     * @param integer $group_id group identifier
     *
     * @return void
     */
    public function ajax_save_group_pages($group_id)
    {
        $pages = $this->input->post("pages");
        $this->load->model('banners/models/Banner_group_model');
        $this->Banner_group_model->delete_all_pages($group_id);

        if (!empty($pages) && count($pages) > 0) {
            $saved_pages = array();
            foreach ($pages as $page) {
                $name = trim(strip_tags($page["name"]));
                $link = trim(strip_tags($page["link"]));
                if (isset($saved_pages[$link])) {
                    continue;
                }

                $saved_pages[$link] = $name;

                $page = array(
                    "name"     => $name,
                    "link"     => $link,
                    "group_id" => $group_id,
                );
                if (strlen($page["link"])) {
                    $this->Banner_group_model->add_page($page);
                }
            }
        }

        return;
    }

    //_---------------------------------------------------------------------------------------------------------//

    /**
     * Statistics of used banner
     *
     * Period types:
     * day   - statistics by day
     * week  - statistics by week
     * month - statistics by month
     * year  - statistics by year
     *
     * @param integer $banner_id banner identifier
     * @param string  $stat_type period type
     * @param integer $year      year value
     * @param integer $month     month value
     * @param integer $week      week value
     * @param integer $day       day value
     *
     * @return void
     */
    public function statistic($banner_id, $stat_type = "day", $year = '', $month = '', $week = '', $day = '')
    {
        $banner_id = (is_numeric($banner_id) and $banner_id > 0) ? intval($banner_id) : 0;
        if (!$banner_id) {
            show_404();
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
                $navigation["prev"] = site_url() . "admin/banners/statistic/" . $banner_id . "/year/" . ($year - 1);
                $navigation["next"] = site_url() . "admin/banners/statistic/" . $banner_id . "/year/" . ($year + 1);
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
                $navigation["prev"] = site_url() . "admin/banners/statistic/" . $banner_id . "/month/" . date("Y", $uts_prev) . "/" . date("m", $uts_prev);
                $navigation["next"] = site_url() . "admin/banners/statistic/" . $banner_id . "/month/" . date("Y", $uts_next) . "/" . date("m", $uts_next);
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
                $navigation["prev"] = site_url() . "admin/banners/statistic/" . $banner_id . "/week/" . date("Y", $first_weekday - 7 * 24 * 60 * 60) . "/0/" . date("W", $first_weekday - 7 * 24 * 60 * 60) . "/";
                $navigation["next"] = site_url() . "admin/banners/statistic/" . $banner_id . "/week/" . date("Y", $first_weekday + 7 * 24 * 60 * 60) . "/0/" . date("W", $first_weekday + 7 * 24 * 60 * 60) . "/";
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
                $navigation["prev"] = site_url() . "admin/banners/statistic/" . $banner_id . "/day/" . date("Y", $uts - 24 * 60 * 60) . "/" . date("m", $uts - 24 * 60 * 60) . "/0/" . date("d", $uts - 24 * 60 * 60);
                $navigation["next"] = site_url() . "admin/banners/statistic/" . $banner_id . "/day/" . date("Y", $uts + 24 * 60 * 60) . "/" . date("m", $uts + 24 * 60 * 60) . "/0/" . date("d", $uts + 24 * 60 * 60);
            break;
        }

        $this->view->assign('navigation', $navigation);
        $this->view->assign('stat_type', $stat_type);
        $this->view->assign('statistic', $statistic);

        $this->Menu_model->set_menu_active_item('admin_banners_menu', 'banners_list_item');
        $this->view->setHeader(l('admin_header_stat_list', 'banners') . $banner_data["name"]);
        $this->view->setBackLink(site_url() . 'admin/banners');
        $this->view->render('statistic');
    }

    /**
     * Update banner statistics
     *
     * @return void
     */
    public function update_hour_statistic()
    {
        $this->load->model('banners/models/Banners_stat_model');

        $this->Banners_stat_model->update_file_statistic();

        $date = date("Y-m-d");
        $this->Banners_stat_model->update_day_statistic($date);
        $this->Banners_stat_model->update_week_statistic($date);
        $this->Banners_stat_model->update_month_statistic($date);
        $this->Banners_stat_model->update_year_statistic($date);
        $this->system_messages->addMessage(View::MSG_SUCCESS, l('banner_statistic_update_success', 'banners'));
        redirect(site_url() . "admin/banners");
    }

    /**
     * Show banner preview
     *
     * @param integer $banner_id banner identifier
     *
     * @return void
     */
    public function preview($banner_id)
    {
        $this->load->model('banners/models/Banner_place_model');
        $banner_id = (is_numeric($banner_id) and $banner_id > 0) ? intval($banner_id) : 0;

        if (!$banner_id) {
            show_404();
        }

        // get banner
        $banner = $this->Banners_model->get($banner_id);
        if ($banner) {
            // get banner place
            $banner['banner_place_obj'] = $this->Banner_place_model->get($banner['banner_place_id']);
            $this->view->assign('banner', $banner);
        }

        echo $this->view->fetch('preview');
        exit();
    }

    /**
     * Edit module settings
     *
     * @return void
     */
    public function settings()
    {
        if ($this->input->post("btn_save")) {
            $post_data["period"] = $this->input->post("period");
            $post_data["moderation_send_mail"] = $this->input->post("moderation_send_mail");
            $post_data["admin_moderation_emails"] = $this->input->post("admin_moderation_emails");

            $validate_data = $this->Banners_model->validate_settings($post_data);
            if (!empty($validate_data["errors"])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data["errors"]);
                $data = $post_data;
            } else {
                foreach ($validate_data["data"] as $setting => $value) {
                    $this->pg_module->set_module_config("banners", $setting, $value);
                }
                $data = $validate_data["data"];

                $this->system_messages->addMessage(View::MSG_SUCCESS, l("success_settings_saved", "banners"));
            }
        } else {
            $data["period"] = $this->pg_module->get_module_config("banners", "period");
            $data["moderation_send_mail"] = $this->pg_module->get_module_config("banners", "moderation_send_mail");
            $data["admin_moderation_emails"] = $this->pg_module->get_module_config("banners", "admin_moderation_emails");
        }
        $this->view->assign('data', $data);

        $this->view->setHeader(l('admin_header_settings', 'banners'));
        $this->view->render('settings');
    }
}
