<?php

namespace Pg\Modules\Users\Controllers;

use Pg\Libraries\View;

/**
 * Users admin side controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * */
class Admin_Users extends \Controller
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['Users_model', 'Menu_model']);
    }

    public function index($filter = null, $user_type = null, $order = null, $order_direction = null, $page = null)
    {
        $attrs = $search_params = [];
        $current_settings = isset($_SESSION["users_list"]) ? $_SESSION["users_list"] : [];

        if (!isset($current_settings["filter"])) {
            $current_settings["filter"] = 'all';
            $current_settings["last_active"] = array('from' => '', 'to' => '');
        }
        if (!isset($current_settings["user_type"])) {
            $current_settings["user_type"] = 'all';
        }
        if (!isset($current_settings["order"])) {
            $current_settings["order"] = 'nickname';
        }
        if (!isset($current_settings["order_direction"])) {
            $current_settings["order_direction"] = 'DESC';
        }
        if (!isset($current_settings["page"])) {
            $current_settings["page"] = 1;
        }
        if ($this->uri->segment(4) === false) {
            $current_settings["search_text"] = '';
            $current_settings["search_type"] = 'all';
            $current_settings["last_active"] = array('from' => '', 'to' => '');
        }
        if ($this->input->post('btn_search', true)) {
            $user_type                               = $this->input->post('user_type',
                true);
            $current_settings["search_type"]         = $this->input->post('type_text',
                true);
            $current_settings["search_text"]         = $this->input->post('val_text',
                true);
            $current_settings["last_active"]["from"] = $this->input->post('last_active_from',
                true);
            $current_settings["last_active"]["to"]   = $this->input->post('last_active_to',
                true);
        }
        $current_settings["user_type"] = $user_type;
        if ($current_settings["search_text"]) {
            $search_text_escape = $this->db->escape("%" . $current_settings["search_text"] . "%");
            if ($current_settings["search_type"] != 'all') {
                $attrs["where_sql"][]         = $search_params["where_sql"][] = $current_settings["search_type"] . " LIKE " . $search_text_escape;
            } else {
                $attrs["where_sql"][]         = $search_params["where_sql"][] = "(nickname LIKE " . $search_text_escape . " OR fname LIKE " . $search_text_escape . " OR sname LIKE " . $search_text_escape . " OR email LIKE " . $search_text_escape . ")";
            }
        }

        if (!empty($current_settings["last_active"]["from"])) {
            $attrs["where_sql"][]         = $search_params["where_sql"][] = "date_last_activity >= '" . $current_settings["last_active"]["from"] . "'";
        }
        if (!empty($current_settings["last_active"]["to"])) {
            $attrs["where_sql"][]         = $search_params["where_sql"][] = "date_last_activity <= '" . $current_settings["last_active"]["to"] . " 23:59:59'";
        }

        if ($user_type != 'all' && $user_type) {
            $attrs["where"]["user_type"]         = $search_params["where"]["user_type"]
                = $user_type;
        }

        $search_param = array(
            'text' => $current_settings["search_text"],
            'type' => $current_settings["search_type"],
            'last_active' => $current_settings["last_active"],
        );

        $filter_data["all"]                 = $this->Users_model->get_users_count($search_params);
        $search_params["where"]["approved"] = 0;
        $filter_data["not_active"]          = $this->Users_model->get_users_count($search_params);
        $search_params["where"]["approved"] = 1;
        $filter_data["active"]              = $this->Users_model->get_users_count($search_params);
        $search_params["where"]["confirm"]  = 0;
        $filter_data["not_confirm"]         = $this->Users_model->get_users_count($search_params);
        $this->load->model("users/models/Users_deleted_model");
        $filter_data["deleted"]             = $this->Users_deleted_model->get_users_count();

        if (!$filter) {
            $filter = $current_settings["filter"];
        }


        switch ($filter) {
            case 'active' : $attrs["where"]['approved'] = 1;
                break;
            case 'not_active' : $attrs["where"]['approved'] = 0;

                break;
            case 'not_confirm' : $attrs["where"]['confirm '] = 0;
                break;
            case 'all' : break;
            default: $filter                     = $current_settings["filter"];
        }

        $current_settings["filter"] = $filter;

        $this->load->model('Properties_model');
        $user_types = $this->Properties_model->get_property('user_type');
        $this->view->assign('user_types', $user_types);

        $this->view->assign('search_param', $search_param);
        $this->view->assign('user_type', $user_type);
        $this->view->assign('filter', $filter);
        $this->view->assign('filter_data', $filter_data);
        $current_settings["page"] = $page;

        if (!$order) {
            $order = $current_settings["order"];
        }
        $this->view->assign('order', $order);
        $current_settings["order"] = $order;

        if (!$order_direction) {
            $order_direction = $current_settings["order_direction"];
        }
        $this->view->assign('order_direction', $order_direction);
        $current_settings["order_direction"] = $order_direction;

        $users_count = $filter_data[$filter];

        if (!$page) {
            $page = $current_settings["page"];
        }
        $items_on_page            = $this->pg_module->get_module_config('start',
            'admin_items_per_page');
        $this->load->helper('sort_order');
        $page                     = get_exists_page_number($page, $users_count,
            $items_on_page);
        $current_settings["page"] = $page;

        $_SESSION["users_list"] = $current_settings;

        $sort_links = array(
            "nickname" => site_url() . "admin/users/index/{$filter}/{$user_type}/nickname/" . (($order
            != 'nickname' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
            "email" => site_url() . "admin/users/index/{$filter}/{$user_type}/email/" . (($order
            != 'email' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
            "account" => site_url() . "admin/users/index/{$filter}/{$user_type}/account/" . (($order
            != 'account' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
            "date_created" => site_url() . "admin/users/index/{$filter}/{$user_type}/date_created/" . (($order
            != 'date_created' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
        );

        $this->view->assign('sort_links', $sort_links);

        if ($users_count > 0) {
            $users = $this->Users_model->get_users_list($page, $items_on_page,
                array($order => $order_direction), $attrs);
            $this->view->assign('users', $users);
        }

        $this->load->helper("navigation");
        $url                      = site_url() . "admin/users/index/{$filter}/{$user_type}/{$order}/{$order_direction}/";
        $page_data                = get_admin_pages_data($url, $users_count,
            $items_on_page, $page, 'briefPage');
        $page_data["date_format"] = $this->pg_date->get_format('date_time_literal',
            'st');
        $this->view->assign('page_data', $page_data);

        $this->load->model("users/models/Groups_model");
        $groups = $this->Groups_model->getGroupsList();
        $this->view->assign('groups', $groups);

        $this->Menu_model->set_menu_active_item('admin_menu', 'users_menu_item');

        $this->view->setHeader(l('admin_header_users_list', 'users'));
        $this->view->render('list');
    }

    public function edit($section = 'personal', $user_id = null)
    {
        $this->Users_model->fields_not_editable = array();
        if (!$section) {
            $section = 'personal';
        }
        $this->load->model('Field_editor_model');
        $this->Field_editor_model->initialize($this->Users_model->form_editor_type);
        $sections   = $this->Field_editor_model->get_section_list();
        $this->view->assign('sections', $sections);
        $this->load->model('Properties_model');
        $user_types = $this->Properties_model->get_property('user_type');
        if ($section == 'personal') {
            $age_min   = $this->pg_module->get_module_config('users', 'age_min');
            $age_max   = $this->pg_module->get_module_config('users', 'age_max');
            $age_range = range($age_min, $age_max);
            $this->view->assign('age_min', $age_min);
            $this->view->assign('age_max', $age_max);
            $this->view->assign('age_range', $age_range);
            $this->view->assign('user_types', $user_types);
        } else {
            $fe_section = $this->Field_editor_model->get_section_by_gid($section);
            if (!empty($fe_section)) {
                $fields_for_select = $this->Field_editor_model->get_fields_for_select($fe_section['gid']);
                $this->Users_model->set_additional_fields($fields_for_select);
            }
        }

        if ($user_id) {
            $data = $this->Users_model->get_user_by_id($user_id);
            if (!empty($data['net_is_incomer'])) {
                redirect(site_url() . 'admin/users');
            }
        } else {
            $data["lang_id"] = $this->pg_language->current_lang_id;
        }

        $pm_installed = $this->pg_module->is_module_installed('perfect_match');

        $use_repassword = $this->pg_module->get_module_config('users',
            'use_repassword');
        if ($this->input->post('btn_save')) {
            $post_data        = array();
            $validate_section = null;

            switch ($section) {
                case 'personal':
                    $post_data = array(
                        'email' => $this->input->post('email', true),
                        'confirm' => $this->input->post('confirm', true),
                        'nickname' => $this->input->post('nickname', true),
                        'fname' => $this->input->post('fname', true),
                        'sname' => $this->input->post('sname', true),
                        'id_country' => $this->input->post('id_country', true),
                        'id_region' => $this->input->post('id_region', true),
                        'id_city' => $this->input->post('id_city', true),
                        'birth_date' => $this->input->post('birth_date', true),
                        'phone' => $this->input->post('phone', true),
                        'lat' => $this->input->post('lat', true),
                        'lon' => $this->input->post('lon', true),
                        /* <custo_M> */
                        'age' => $this->input->post('age', true),
                        'living_with' => $this->input->post('living_with', true),
                        'ethnicity' => $this->input->post('ethnicity', true),
                        'relationship_status' => $this->input->post('relationship_status', true),
                        'postal_code' => $this->input->post('postal_code', true),
                        
                        'looking_living_with' => $this->input->post('living_with', true),
                        'looking_ethnicity' => $this->input->post('looking_ethnicity', true),
                        'looking_relationship_status' => $this->input->post('looking_relationship_status', true),
                        'height' => $this->input->post('height', true),
                        'height_min' => $this->input->post('height_min', true),
                        'height_max' => $this->input->post('height_max', true),
                        'physical_appearance' => $this->input->post('physical_appearance', true),
                        'looking_physical_appearance' => $this->input->post('looking_physical_appearance', true),
                        'eye_color' => $this->input->post('eye_color', true),
                        'looking_eye_color' => $this->input->post('looking_eye_color', true),
                        'hair_color' => $this->input->post('hair_color', true),
                        'looking_hair_color' => $this->input->post('looking_hair_color', true),
                        'body_type' => $this->input->post('body_type', true),
                        'looking_body_type' => $this->input->post('looking_body_type', true),
                        'religion' => $this->input->post('religion', true),
                        'looking_religion' => $this->input->post('looking_religion', true),
                        'language' => $this->input->post('language', true),
                        'looking_language' => $this->input->post('looking_language', true),
                        'education' => $this->input->post('education', true),
                        'looking_education' => $this->input->post('looking_education', true),
                        'occupation' => $this->input->post('occupation', true),
                        'looking_occupation' => $this->input->post('looking_occupation', true),
                        'annual_income' => $this->input->post('annual_income', true),
                        'looking_annual_income' => $this->input->post('looking_annual_income', true),
                        'drinking' => $this->input->post('drinking', true),
                        'looking_drinking' => $this->input->post('looking_drinking', true),
                        'smoking' => $this->input->post('smoking', true),
                        'looking_smoking' => $this->input->post('looking_smoking', true),
                        'have_children' => $this->input->post('have_children', true),
                        'looking_have_children' => $this->input->post('looking_have_children', true),
                        'want_children' => $this->input->post('want_children', true),
                        'looking_want_children' => $this->input->post('looking_want_children', true),
                        'have_pets' => $this->input->post('have_pets', true),
                        'looking_have_pets' => $this->input->post('looking_have_pets', true),
                        
                        'headline' => $this->input->post('headline', true),
                        'about_me' => $this->input->post('about_me', true),
                        'looking_about' => $this->input->post('looking_about', true),
                        'looking_for' => $this->input->post('looking_for', true),
                        'looking_distance' => $this->input->post('looking_distance', true),
                        'listening_music' => $this->input->post('listening_music', true),
                        'astrological_sign' => $this->input->post('astrological_sign', true),
                        'is_subscribed' => $this->input->post('is_subscribed', true),
                        
                        /* </custo_M> */
                    );

                    if ($pm_installed) {
                        $post_data['looking_user_type'] = $this->input->post('looking_user_type',
                            true);
                        $post_data['age_min']           = $this->input->post('age_min',
                            true);
                        $post_data['age_max']           = $this->input->post('age_max',
                            true);
                    }

                    if (!$user_id) {
                        $post_data['user_type'] = $this->input->post('user_type',
                            true);
                    }
                    break;
                case 'seo':
                    $this->load->model('Seo_advanced_model');
                    $seo_fields = $this->Seo_advanced_model->get_seo_fields();
                    foreach ($seo_fields as $key => $section_data) {
                        if ($this->input->post('btn_save_' . $section_data['gid'])) {
                            $post_data[$section_data['gid']] = $this->input->post($section_data['gid'],
                                true);
                            $validate_data                   = $this->Seo_advanced_model->validate_seo_tags($user_id,
                                $post_data);
                            if (!empty($validate_data["errors"])) {
                                $this->system_messages->addMessage(View::MSG_ERROR,
                                    $validate_data["errors"]);
                            } else {
                                $user_data['id_seo_settings'] = $this->Seo_advanced_model->save_seo_tags($data['id_seo_settings'],
                                    $validate_data['data']);
                                if (!$data['id_seo_settings']) {
                                    $data['id_seo_settings'] = $user_data['id_seo_settings'];
                                    $this->Users_model->save_user($user_id,
                                        $user_data, false);
                                }
                                $this->system_messages->addMessage(View::MSG_SUCCESS,
                                    l('success_settings_updated', 'seo'));
                            }
                            $data = array_merge($data, $post_data);
                            break;
                        }
                    }
                    break;
                default:
                    foreach ($fields_for_select as $field) {
                        $post_data[$field] = $this->input->post($field, true);
                    }
                    $validate_section = $section;
                    break;
            }
            if (intval($this->input->post('update_password')) || !$user_id) {
                $post_data['password'] = $this->input->post('password', true);
                if ($use_repassword) {
                    $post_data['repassword'] = $this->input->post('repassword',
                        true);
                }
            }

            $validate_data = $this->Users_model->validate($user_id, $post_data,
                'user_icon', $validate_section);
            if (!empty($validate_data["errors"])) {
                $this->system_messages->addMessage(View::MSG_ERROR,
                    $validate_data["errors"]);
                $data = $validate_data['data'];
            } else {
                $save_data = $validate_data["data"];

                if ($this->input->post('user_icon_delete') || (isset($_FILES['user_icon'])
                    && is_array($_FILES['user_icon']) && is_uploaded_file($_FILES['user_icon']['tmp_name']))) {
                    $this->load->model("Uploads_model");
                    if ($data['user_logo_moderation']) {
                        $this->Uploads_model->delete_upload($this->Users_model->upload_config_id,
                            $user_id . "/", $data['user_logo_moderation']);
                        $save_data["user_logo_moderation"] = '';
                        $this->load->model('Moderation_model');
                        $this->Moderation_model->delete_moderation_item_by_obj($this->Users_model->moderation_type,
                            $user_id);
                    } elseif ($data['user_logo']) {
                        $this->Uploads_model->delete_upload($this->Users_model->upload_config_id,
                            $user_id . "/", $data['user_logo']);
                        $save_data["user_logo"] = '';
                    }
                }

                if (!empty($save_data['password'])) {
                    $save_password         = $save_data['password'];
                    $save_data['password'] = md5($save_data['password']);
                }
                if (!$user_id) {
                    $save_data['confirm']  = 1;
                    $save_data['approved'] = 1;
                }

                $this->load->model('Notifications_model');
                if (!empty($save_data['password'])) {
                    // send notification password
                    $data["password"] = $save_password;
                    $this->Notifications_model->send_notification($data["email"],
                        'users_change_password', $data);
                }
                if ($data["email"] != $save_data["email"] && !empty($save_data["email"])) {
                    // send notification email
                    $data["new_email"] = $save_data["email"];
                    $this->Notifications_model->send_notification($data["email"],
                        'users_change_email', $data);
                    $data["email"]     = $data["new_email"];
                    $this->Notifications_model->send_notification($data["new_email"],
                        'users_change_email', $data);
                }

                $validate_data["data"]["id"] = $user_id                     = $this->Users_model->save_user($user_id,
                    $save_data, 'user_icon', false);

                if ($user_id) {
                    $this->system_messages->addMessage(View::MSG_SUCCESS,
                        l('success_update_user', 'users'));
                } else {
                    $this->system_messages->addMessage(View::MSG_SUCCESS,
                        l('success_add_user', 'users'));
                }

                $cur_set = $_SESSION["users_list"];

                $url = site_url() . "admin/users/edit/" . $section . "/" . $user_id;
                redirect($url);
            }
            $data = array_merge($data, $validate_data["data"]);
        }

        if (!isset($data['user_type']) && !empty($user_types['option'])) {
            $data['user_type'] = key($user_types['option']);
        }

        // Differ looking_user_type from user_type
        if ($pm_installed) {
            if (isset($data['user_type']) && !isset($data['looking_user_type']) && count($user_types['option'])) {
                $data['looking_user_type'] = key(array_slice($user_types['option'],
                        1, 1));
            }

            if (!isset($data['age_max'])) {
                $data['age_max'] = $age_max;
            }
        } else {
            $not_editable_fields['looking_user_type'] = 1;
            $not_editable_fields['partner_age']       = 1;
            $this->view->assign('not_editable_fields', $not_editable_fields);
        }

        $data           = $this->Users_model->format_user($data);
        $this->view->assign('use_repassword', $use_repassword);
        $this->view->assign('langs', $this->pg_language->languages);
        $data['action'] = '';
        $this->view->assign('data', $data);
        $this->view->assign('section', $section);

        switch ($section) {
            case 'personal':
                break;
            case 'details':
                break;
            case 'seo':
                $this->load->model('Seo_advanced_model');
                $seo_fields = $this->Seo_advanced_model->get_seo_fields();
                $this->view->assign('seo_fields', $seo_fields);

                $languages = $this->pg_language->languages;
                $this->view->assign('languages', $languages);

                $current_lang_id = $this->pg_language->current_lang_id;
                $this->view->assign('current_lang_id', $current_lang_id);

                if ($data['id_seo_settings']) {
                    $seo_settings = $this->Seo_advanced_model->get_seo_tags($data['id_seo_settings']);
                    $this->view->assign('seo_settings', $seo_settings);
                }
                break;
            default:
                $params["where"]["section_gid"] = $fe_section['gid'];
                $fields_data                    = $this->Field_editor_model->get_form_fields_list($data,
                    $params);
                $this->view->assign('fields_data', $fields_data);
                break;
        }

        if (!empty($_SESSION["users_list"])) {
            $cur_set  = $_SESSION["users_list"];
            $back_url = site_url() . "admin/users/index";
        } else {
            $back_url = '';
        }
        $this->view->assign('back_url', $back_url);
        $this->view->setHeader(l('admin_header_users_edit', 'users'));

        $this->load->model('Properties_model');
        $looking_living_with_types = $this->Properties_model->get_property('living_with', $this->pg_language->current_lang_id);
        $this->view->assign('living_with_options', $looking_living_with_types['option']);

        foreach ($this->Users_model->dictionaries as $dictionary) {
            $dictionary_options = ld($dictionary, 'users');
            if (!empty($dictionary_options['option'])) {
                $this->view->assign($dictionary . '_options', $dictionary_options['option']);
            }
        }

        $this->Menu_model->set_menu_active_item('admin_menu', 'users_menu_item');

        $this->view->render('edit_form');
    }

    public function delete()
    {
        $user_ids = $this->input->post('user_ids', true);
        if (!empty($user_ids)) {
            $action_user = trim(strip_tags($this->input->post('action_user',
                        true)));
            if (!empty($action_user) && $action_user == 'block_user') {
                foreach ($_POST['user_ids'] as $user_id) {
                    $this->Users_model->activate_user($user_id, 0);
                }
                $this->system_messages->addMessage(View::MSG_SUCCESS,
                    l('success_deactivate_user', 'users'));
            } else {
                $callbacks_gid = array();
                foreach ($_POST['module'] as $module) {
                    $callbacks_gid[] = trim(strip_tags($module));
                }
                foreach ($_POST['user_ids'] as $user_id) {
                    $this->Users_model->delete_user($user_id, $callbacks_gid);
                }
                if (in_array('users_delete', $callbacks_gid)) {
                    $this->system_messages->addMessage(View::MSG_SUCCESS,
                        l('success_delete_user', 'users'));
                } else {
                    $this->system_messages->addMessage(View::MSG_SUCCESS,
                        l('success_clear_user', 'users'));
                }
            }
        }
        $cur_set = $_SESSION["users_list"];
        $url     = site_url() . "admin/users/index";
        redirect($url);
    }

    public function activate($user_id, $status = 0)
    {
        if (!empty($user_id)) {
            $this->Users_model->activate_user($user_id, $status);
            if ($status) {
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_activate_user', 'users'));
            } else {
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_deactivate_user', 'users'));
            }
        }
        redirect(site_url() . "admin/users/index");
    }

    public function ajax_change_user_group($user_id, $group_gid)
    {
        $this->load->model("users/models/Groups_model");
        $group_gid  = strval($group_gid);
        $group_data = $this->Groups_model->getGroupByGid($group_gid);
        $group_id   = (!empty($group_data)) ? $group_data["id"] : 0;

        $user_id = intval($user_id);
        if ($user_id && $group_id) {
            $save_data["group_id"] = $group_id;
            $user_id = $this->Users_model->save_user($user_id, $save_data);
        }
    }

    public function ajax_change_users_group($group_gid)
    {
        $this->load->model("users/models/Groups_model");
        $group_gid  = strval($group_gid);
        $group_data = $this->Groups_model->getGroupByGid($group_gid);
        $group_id   = (!empty($group_data)) ? $group_data["id"] : 0;

        $user_ids = $this->input->post('user_ids');
        if (!empty($user_ids) && $group_id) {
            $save_data["group_id"] = $group_id;
            foreach ($user_ids as $user_id) {
                $this->Users_model->save_user($user_id, $save_data);
            }
            $this->system_messages->addMessage(View::MSG_SUCCESS,
                l('error_user_successfully_change_group', 'users'));
        }
    }

    public function ajax_delete_select($id_user = null, $deleted = 0)
    {
        $this->load->model("users/models/Users_delete_callbacks_model");
        $this->load->model("users/models/Users_deleted_model");
        $callbacks = $this->Users_delete_callbacks_model->get_callbacks();

        foreach ($callbacks as $key => $value) {
            if ($value['module'] == 'perfect_match') {
                unset($callbacks[$key]);
                break;
            }
        }

        if ($id_user) {
            $callbacks_data   = $this->Users_deleted_model->get_user_callbacks(intval($id_user),
                $callbacks);
            $data['user_ids'] = array(intval($id_user));
        } else {
            $callbacks_data   = $this->Users_deleted_model->get_user_callbacks(0,
                $callbacks);
            $data['user_ids'] = $_POST['user_ids'];
        }
        foreach ($data['user_ids'] as $id_user) {
            $user_data            = $this->Users_model->get_user_by_id($id_user);
            $data['user_names'][] = $user_data['nickname'];
        }
        $data['action']  = site_url() . "admin/users/delete/";
        $data['deleted'] = intval($deleted);
        $this->view->assign('data', $data);
        $this->view->assign('callbacks_data', $callbacks_data);

        return $this->view->render('ajax_delete_select_block');
    }

    public function groups($page = 1)
    {
        $this->load->model("users/models/Groups_model");

        $attrs                    = array();
        $current_settings["page"] = $page;
        if (!isset($current_settings["page"])) {
            $current_settings["page"] = 1;
        }

        $group_count = $this->Groups_model->getGroupsCount();

        if (!$page) {
            $page = $current_settings["page"];
        }
        $items_on_page            = $this->pg_module->get_module_config('start',
            'admin_items_per_page');
        $this->load->helper('sort_order');
        $page                     = get_exists_page_number($page, $group_count,
            $items_on_page);
        $current_settings["page"] = $page;

        $_SESSION["groups_list"] = $current_settings;

        if ($group_count > 0) {
            $groups = $this->Groups_model->getGroupsList($page, $items_on_page,
                array("date_created" => "DESC"));
            $this->view->assign('groups', $groups);
        }
        $this->load->helper("navigation");
        $url                      = site_url() . "admin/users/groups/";
        $page_data                = get_admin_pages_data($url, $group_count,
            $items_on_page, $page, 'briefPage');
        $page_data["date_format"] = $this->pg_date->get_format('date_time_literal',
            'st');
        $this->view->assign('page_data', $page_data);

        $this->Menu_model->set_menu_active_item('admin_users_menu',
            'groups_list_item');

        $this->Menu_model->set_menu_active_item('admin_menu', 'users_menu_item');

        $this->view->setHeader(l('admin_header_groups_list', 'users'));
        $this->view->render('groups_list');
    }

    public function group_edit($group_id = null)
    {
        $this->load->model("users/models/Groups_model");
        if ($group_id) {
            $data = $this->Groups_model->getGroupById($group_id);
        } else {
            $data = array();
        }

        if ($this->input->post('btn_save')) {
            $post_data = array(
                "gid" => $this->input->post('gid', true),
            );

            $langs_data    = $this->input->post('langs', true);
            $validate_data = $this->Groups_model->validateGroup($group_id,
                $post_data);
            if (!empty($validate_data["errors"])) {
                $this->system_messages->addMessage(View::MSG_ERROR,
                    $validate_data["errors"]);
                $data = array_merge($data, $validate_data["data"]);
            } else {
                $data     = $validate_data["data"];
                $group_id = $this->Groups_model->saveGroup($group_id, $data,
                    $langs_data);

                if ($group_id) {
                    $this->system_messages->addMessage(View::MSG_SUCCESS,
                        l('success_update_group', 'users'));
                } else {
                    $this->system_messages->addMessage(View::MSG_SUCCESS,
                        l('success_add_group', 'users'));
                }

                $url = site_url() . "admin/users/groups";
                redirect($url);
            }
        }

        $data          = $this->Groups_model->formatGroup($data);
        $data["langs"] = $this->Groups_model->getGroupStringData($group_id);

        $this->view->assign('languages', $this->pg_language->languages);
        $this->view->assign('data', $data);

        $this->Menu_model->set_menu_active_item('admin_menu', 'users_menu_item');

        $this->view->setHeader(l('admin_header_groups_edit', 'users'));
        $this->view->render('group_edit_form');
    }

    public function group_set_default($group_id)
    {
        if (!empty($group_id)) {
            $this->load->model("users/models/Groups_model");
            $this->Groups_model->setDefault($group_id);
            $this->system_messages->addMessage(View::MSG_SUCCESS,
                l('success_defaulted_group', 'users'));
        }
        $current_settings = $_SESSION["groups_list"];
        $url              = site_url() . "admin/users/groups/" . $current_settings["page"] . "";
        redirect($url);
    }

    public function group_delete($group_id)
    {
        if (!empty($group_id)) {
            $this->load->model("users/models/Groups_model");
            $group_data = $this->Groups_model->getGroupById($group_id);
            if ($group_data["is_default"]) {
                $this->system_messages->addMessage(View::MSG_ERROR,
                    l('error_cant_delete_default_group', 'users'));
            } else {
                $this->Groups_model->deleteGroup($group_id);
                $this->system_messages->addMessage(View::MSG_SUCCESS,
                    l('success_delete_group', 'users'));
            }
        }
        $current_settings = $_SESSION["groups_list"];
        $url              = site_url() . "admin/users/groups/" . $current_settings["page"] . "";
        redirect($url);
    }

    public function deleted($filter = "deleted", $order = "nickname",
                            $order_direction = "ASC", $page = 1)
    {
        $attrs            = $search_param     = $search_params    = array();
        $current_settings = isset($_SESSION["users_deleted_list"]) ? $_SESSION["users_deleted_list"]
                : array();
        if (!isset($current_settings["filter"])) {
            $current_settings["filter"] = $filter;
        }
        if (!isset($current_settings["order"])) {
            $current_settings["order"] = $order;
        }
        if (!isset($current_settings["order_direction"])) {
            $current_settings["order_direction"] = $order_direction;
        }
        if (!isset($current_settings["page"])) {
            $current_settings["page"] = $page;
        }
        if ($this->input->post('btn_search', true)) {
            $current_settings["search_text"]          = $this->input->post('val_text',
                true);
            $current_settings["date_deleted"]["from"] = $this->input->post('date_deleted_from',
                true);
            $current_settings["date_deleted"]["to"]   = $this->input->post('date_deleted_to',
                true);
        }
        if (!empty($current_settings["search_text"])) {
            $search_text_escape           = $this->db->escape("%" . $current_settings["search_text"] . "%");
            $attrs["where_sql"][]         = $search_params["where_sql"][] = "(nickname LIKE " . $search_text_escape . " OR fname LIKE " . $search_text_escape . " OR sname LIKE " . $search_text_escape . " OR email LIKE " . $search_text_escape . ")";
        }

        if (!empty($current_settings["date_deleted"]["from"])) {
            $attrs["where_sql"][]         = $search_params["where_sql"][] = "date_deleted >= '" . $current_settings["date_deleted"]["from"] . "'";
            $search_param['text']         = $current_settings["search_text"];
        }
        if (!empty($current_settings["date_deleted"]["to"])) {
            $attrs["where_sql"][]         = $search_params["where_sql"][] = "date_deleted <= '" . $current_settings["date_deleted"]["to"] . " 23:59:59'";
            $search_param['date_deleted'] = $current_settings["date_deleted"];
        }

        $this->load->model("users/models/Users_deleted_model");
        $filter_data["all"]                = $this->Users_model->get_users_count();
        $search_attrs["where"]["approved"] = 0;
        $filter_data["not_active"]         = $this->Users_model->get_users_count($search_attrs);
        $search_attrs["where"]["approved"] = 1;
        $filter_data["active"]             = $this->Users_model->get_users_count($search_attrs);
        $search_attrs["where"]["confirm"]  = 0;
        $filter_data["not_confirm"]        = $this->Users_model->get_users_count($search_attrs);
        $filter_data["deleted"]            = $this->Users_deleted_model->get_users_count($search_params);

        $this->view->assign('search_param', $search_param);
        $this->view->assign('filter', $filter);
        $this->view->assign('filter_data', $filter_data);
        $current_settings["page"] = $page;

        if (!$order) {
            $order = $current_settings["order"];
        }
        $this->view->assign('order', $order);
        $current_settings["order"] = $order;

        if (!$order_direction) {
            $order_direction = $current_settings["order_direction"];
        }
        $this->view->assign('order_direction', $order_direction);
        $current_settings["order_direction"] = $order_direction;

        $users_count = $filter_data[$filter];

        if (!$page) {
            $page = $current_settings["page"];
        }
        $items_on_page            = $this->pg_module->get_module_config('start',
            'admin_items_per_page');
        $this->load->helper('sort_order');
        $page                     = get_exists_page_number($page, $users_count,
            $items_on_page);
        $current_settings["page"] = $page;

        $_SESSION["users_deleted_list"] = $current_settings;

        $sort_links = array(
            "nickname" => site_url() . "admin/users/deleted/{$filter}/nickname/" . (($order
            != 'nickname' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
            "date_deleted" => site_url() . "admin/users/deleted/{$filter}/date_deleted/" . (($order
            != 'date_deleted' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
        );

        $this->view->assign('sort_links', $sort_links);

        if ($users_count > 0) {
            $users = $this->Users_deleted_model->get_users_list($page,
                $items_on_page, array($order => $order_direction), $attrs);
            $this->view->assign('users', $users);
        }

        $this->load->helper("navigation");
        $url                      = site_url() . "admin/users/deleted/{$filter}/{$order}/{$order_direction}/";
        $page_data                = get_admin_pages_data($url, $users_count,
            $items_on_page, $page, 'briefPage');
        $page_data["date_format"] = $this->pg_date->get_format('date_time_literal',
            'st');
        $this->view->assign('page_data', $page_data);

        $this->Menu_model->set_menu_active_item('admin_menu', 'users_menu_item');

        $this->view->setHeader(l('admin_header_users_list', 'users'));
        $this->view->render('deleted_list');
    }

    public function settings()
    {
        $settings = array();
        $fields   = array(
            'guest_view_profile_allow' => array('filter' => FILTER_VALIDATE_BOOLEAN),
            'guest_view_profile_limit' => array('filter' => FILTER_VALIDATE_BOOLEAN),
            'guest_view_profile_num' => array('filter' => FILTER_VALIDATE_INT),
            'user_approve' => array('filter' => FILTER_VALIDATE_INT),
            'user_confirm' => array('filter' => FILTER_VALIDATE_BOOLEAN),
            'use_repassword' => array('filter' => FILTER_VALIDATE_BOOLEAN),
            'hide_user_names' => array('filter' => FILTER_VALIDATE_BOOLEAN),
            'age_min' => array('filter' => FILTER_VALIDATE_INT),
            'age_max' => array('filter' => FILTER_VALIDATE_INT),
        );
        if ($this->input->post('btn_save')) {
            $settings = filter_input_array(INPUT_POST, $fields);
            foreach ($settings as $config_gid => $value) {
                $this->pg_module->set_module_config('users', $config_gid, $value);
            }

            $this->system_messages->addMessage(View::MSG_SUCCESS,
                l('success_save_settings', 'users'));
        } else {
            foreach (array_keys($fields) as $key) {
                $settings[$key] = $this->pg_module->get_module_config('users',
                    $key);
            }
        }
        $this->view->assign('users_settings', $settings);

        $this->Menu_model->set_menu_active_item('admin_menu', 'users_menu_item');

        $this->view->setHeader(l('header_settings', 'users'));
        $this->view->render('settings');
    }

    public function ajax_get_users_data($page = 1)
    {
        $return = array();
        $params = array();
        if (!$page) {
            $page = intval($this->input->post('page', true));
            if (!$page) {
                $page = 1;
            }
        }

        $search_string = trim(strip_tags($this->input->post('search', true)));
        if (!empty($search_string)) {
            $hide_user_names = $this->pg_module->get_module_config('users',
                'hide_user_names');
            if ($hide_user_names) {
                $params["where"]["nickname LIKE"] = "%" . $search_string . "%";
            } else {
                $search_string_escape  = $this->db->escape("%" . $search_string . "%");
                $params["where_sql"][] = "(nickname LIKE " . $search_string_escape
                    . " OR fname LIKE " . $search_string_escape
                    . " OR sname LIKE " . $search_string_escape . ")";
            }
        }

        $selected = $this->input->post('selected', true);
        if (!empty($selected)) {
            $params["where_sql"][] = "id NOT IN (" . implode($selected) . ")";
        }

        $user_type = $this->input->post('user_type', true);
        if ($user_type) {
            $params["where"]["user_type"] = $user_type;
        }

        $items_on_page = $this->pg_module->get_module_config('start',
            'admin_items_per_page');
        $items         = $this->Users_model->get_users_list_by_key($page,
            $items_on_page, array("nickname" => "asc"), $params, array(), true,
            true);

        $return["all"]          = $this->Users_model->get_users_count($params);
        $return["items"]        = $items;
        $return["current_page"] = $page;
        $return["pages"]        = ceil($return["all"] / $items_on_page);

        $this->view->assign($return);
        $this->view->render();
    }

    public function ajax_get_selected_users()
    {
        $selected = $this->input->post('selected', true);
        $selected = array_slice(array_unique(array_map('intval',
                    (array) $selected)), 0, 1000);
        if (!empty($selected)) {
            $return['selected'] = $this->Users_model->get_users_list(null, null,
                array("nickname" => "asc"), array(), $selected, true, true);
        } else {
            $return['selected'] = array();
        }
        $this->view->assign($return);
        $this->view->render();
    }

    public function ajax_get_users_form($max_select = 1)
    {
        $selected = $this->input->post('selected', true);

        if (!empty($selected)) {
            $data["selected"] = $this->Users_model->get_users_list(null, null,
                array("nickname" => "asc"), array(), $selected, false);
        } else {
            $data["selected"] = array();
        }
        $data["max_select"] = $max_select ? $max_select : 0;

        $this->view->assign('select_data', $data);
        $this->view->render('ajax_user_select_form');
    }

    public function verifications($order = null, $order_direction = null, $page = null)
    {
        $this->load->model('users/models/Verifications_model');

        $sources_count = $this->Verifications_model->count();

        $current_settings = isset($_SESSION["verifications_list"]) ? $_SESSION["verifications_list"] : [];

        if (!isset($current_settings["page"])) {
            $current_settings["page"] = 1;
        }

        if (!isset($current_settings["order"])) {
            $current_settings["order"] = 'date_created';
        }

        if (!isset($current_settings["order_direction"])) {
            $current_settings["order_direction"] = 'DESC';
        }

        if ($page) {
            $current_settings["page"] = $page;
        }

        if (!$order) {
            $order = $current_settings["order"];
        }

        if (!$order_direction) {
            $order_direction = $current_settings["order_direction"];
        }

        $this->view->assign('order', $order);
        $current_settings["order"] = $order;


        $this->view->assign('order_direction', $order_direction);
        $current_settings["order_direction"] = $order_direction;

        $items_on_page = $this->pg_module->get_module_config('start', 'admin_items_per_page');
        $this->load->helper('sort_order');
        $page = get_exists_page_number($page, $sources_count, $items_on_page);
        $current_settings["page"] = $page;

        $_SESSION["users_list"] = $current_settings;

        $sort_links = [
            "date_created" => site_url() . "admin/users/verifications/" . (($order
            != 'date_created' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
        ];
        $this->view->assign('sort_links', $sort_links);

        if ($sources_count > 0) {
            $sources = $this->Verifications_model->format(
                $this->Verifications_model->get($page, $items_on_page, [$order => $order_direction])
            );
            $this->view->assign('sources', $sources);
        }

        $this->load->helper("navigation");
        $url = site_url() . "admin/users/verifications/{$order}/{$order_direction}/";
        $page_data = get_admin_pages_data($url, $sources_count, $items_on_page, $page, 'briefPage');
        $page_data["date_format"] = $this->pg_date->get_format('date_time_literal', 'st');
        $this->view->assign('page_data', $page_data);

        $this->Menu_model->set_menu_active_item('admin_menu', 'verifications-item');
        
        $this->view->render('verifications');
    }

    public function verificationApprove($user_id)
    {
        $this->load->model('users/models/Verifications_model');

        $this->Verifications_model->approve($user_id);
        $this->Users_model->save_user($user_id, ['is_verified' => 1]);

        redirect(site_url() . 'admin/users/verifications');
    }

    public function verificationDecline($source_id)
    {
        $this->load->model('users/models/Verifications_model');

        $this->Verifications_model->decline($source_id);

        redirect(site_url() . 'admin/users/verifications');
    }

    public function verificationDelete($source_id)
    {
        $this->load->model('users/models/Verifications_model');

        $this->Verifications_model->delete($source_id);

        redirect(site_url() . 'admin/users/verifications');
    }
}
