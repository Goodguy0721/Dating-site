<?php

namespace Pg\Modules\Users\Controllers;

use Pg\Modules\Users\Acl;
use Pg\Libraries\EventDispatcher;
use Pg\Libraries\View;
use Pg\Modules\Users\Models\Events\EventUsers;

/**
 * Users user side controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category    modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * */
class Users extends \Controller
{
    /**
     * link to CodeIgniter object
     *
     * @var object
     */
    public $use_email_confirmation = false;
    public $use_approve            = false;
    private $user_id               = 0;
    private $subsections           = array('default' => 'all', 'photo', 'video', 'audio', 'albums', 'favorites');

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model("Users_model");
        $this->load->model('Menu_model');
        $this->use_email_confirmation = (bool) $this->pg_module->get_module_config('users', 'user_confirm');
        $this->use_approve            = intval($this->pg_module->get_module_config('users', 'user_approve'));
        if ('user' === $this->session->userdata('auth_type')) {
            $this->user_id = intval($this->session->userdata('user_id'));
        }
    }

    public function ajax_login_form($type='login')
    {
        if ($type == 'register') {
            $this->load->model('Properties_model');
            $user_types     = $this->Properties_model->get_property('user_type');
            $this->view->assign('user_types', $user_types);
            
            foreach ($this->Users_model->dictionaries as $dictionary) {
                $dictionary_options = ld($dictionary, 'users');
                if (!empty($dictionary_options['option'])) {
                    $this->view->assign($dictionary . '_options', $dictionary_options['option']);
                }
            }
            
            $age_min        = $this->pg_module->get_module_config('users', 'age_min');
            $age_max        = $this->pg_module->get_module_config('users', 'age_max');
            $this->view->assign('age_min', $age_min);
            $this->view->assign('age_max', $age_max);
        
            $this->view->render('ajax_register_form');
        } else {
            $this->view->render('ajax_login_form');
        }
    }

    public function login_form()
    {
        $this->load->model('Menu_model');
        $this->Menu_model->breadcrumbs_set_active(l('header_login', 'users'));
        if (!empty($this->user_id)) {
            $this->Users_model->update_online_status($this->user_id, 0);
            $this->load->model("users/models/Auth_model");
            $this->Auth_model->logoff();
        }

        $this->view->render('login_form');
    }

    public function login()
    {
        $errors = array();

        $this->load->model("users/models/Auth_model");

        $data = array(
            "email"    => trim(strip_tags($this->input->post('email', true))),
            "password" => trim(strip_tags($this->input->post('password', true))),
        );

        $validate = $this->Auth_model->validate_login_data($data);
        if (!empty($validate["errors"])) {
            $errors = array_merge($errors, $validate["errors"]);
        } else {
            $login_return = $this->Auth_model->login_by_email_password($validate["data"]["email"], md5($validate["data"]["password"]));
            if (!empty($login_return["errors"])) {
                $errors = array_merge($errors, $login_return["errors"]);
            }
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->system_messages->addMessage(View::MSG_ERROR, $error);
            }
            /*if (isset($login_return['user_data']['confirm']) && !$login_return['user_data']['confirm']) {
                redirect(site_url() . 'users/confirm', 'hard');
            } else {*/
                redirect(site_url() . 'users/login_form');
            //}
        }

        $this->session->set_flashdata('js_events', 'users:login');
        if (strpos($_SERVER["HTTP_REFERER"], site_url()) > 0) {
            redirect($_SERVER["HTTP_REFERER"], 'hard');
        } else {
            if (SOCIAL_MODE) {
                redirect('', 'hard');
            } else {
                redirect('users/search', 'hard');
            }
        }
    }

    public function logout()
    {
        $this->load->model("users/models/Auth_model");
        $this->Users_model->update_online_status($this->user_id, 0);
        $lang_id = $this->session->userdata('lang_id');
        $this->Auth_model->logoff();
        $this->clearCookies('available_activation');
        $this->session->sess_create();
        if ($this->session->userdata('lang_id') != $lang_id) {
            $this->session->set_userdata("lang_id", $lang_id);
        }
        $this->session->set_flashdata('js_events', 'users:logout');

        if ($this->pg_module->is_module_installed('logout_page')) {
            $this->load->model('logout_page/models/Logout_page_model');
            $logout_page = $this->Logout_page_model->getRandomPage();
            if (isset($logout_page['gid'])) {
                redirect(site_url() . 'logout_page/index/' . $logout_page['gid']);
            }
        }

        redirect('', 'hard');
    }

    public function change_language($lang_id)
    {
        $lang_id                            = intval($lang_id);
        $this->session->set_userdata("lang_id", $lang_id);
        $old_code                           = $this->pg_language->languages[$this->pg_language->current_lang_id]["code"];
        $this->pg_language->current_lang_id = $lang_id;
        $code                               = $this->pg_language->languages[$lang_id]["code"];
        $_SERVER["HTTP_REFERER"]            = str_replace("/" . $old_code . "/", "/" . $code . "/", $_SERVER["HTTP_REFERER"]);
        $site_url                           = str_replace("/" . $code . "/", "", site_url());

        if ($this->session->userdata('auth_type') == 'user') {
            $save_data["lang_id"] = $lang_id;
            $this->Users_model->save_user($this->user_id, $save_data);
        }

        if (strpos($_SERVER["HTTP_REFERER"], $site_url) !== false) {
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            redirect();
        }
    }

    public function settings()
    {
        $this->load->model('users/models/Auth_model');
        $this->Auth_model->update_user_session_data($this->user_id);
        $use_repassword = $this->pg_module->get_module_config('users', 'use_repassword');
        if ($this->input->post('btn_password_save')) {
            $post_data = array(
                'password' => $this->input->post('password', true),
            );
            if ($use_repassword) {
                $post_data['repassword'] = $this->input->post('repassword', true);
            }
            $validate_data = $this->Users_model->validate($this->user_id, $post_data);

            if (!empty($validate_data["errors"])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data["errors"]);
            } else {
                $save_data             = $validate_data["data"];
                $save_password         = $save_data["password"];
                $save_data["password"] = md5($save_data["password"]);
                $this->Users_model->save_user($this->user_id, $save_data);

                // send notification
                $this->load->model('Notifications_model');
                $user_data             = $this->Users_model->get_user_by_id($this->user_id);
                $user_data["password"] = $save_password;
                $this->Notifications_model->send_notification($user_data["email"], 'users_change_password', $user_data, '', $user_data['lang_id']);

                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_user_updated', 'users'));
            }
        }

        if ($this->input->post('btn_contact_save')) {
            $post_data     = array(
                "email"      => $this->input->post('email', true),
                "phone"      => $this->input->post('phone', true),
                "show_adult" => $this->input->post('show_adult', true),
            );
            $validate_data = $this->Users_model->validate($this->user_id, $post_data);

            if (!empty($validate_data["errors"])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data["errors"]);
            } else {
                $user_data = $this->Users_model->get_user_by_id($this->user_id);
                $save_data = $validate_data["data"];
                $this->Users_model->save_user($this->user_id, $save_data);

                // send notification
                if ($user_data["email"] !== $save_data["email"]) {
                    $this->load->model('Notifications_model');
                    $user_data["new_email"] = $save_data["email"];
                    $user_data              = array_merge($user_data, $save_data);
                    $this->Notifications_model->send_notification($user_data["email"], 'users_change_email', $user_data, '', $user_data['lang_id']);
                    $this->Notifications_model->send_notification($user_data["new_email"], 'users_change_email', $user_data, '', $user_data['lang_id']);
                }

                $this->load->model('users/models/Auth_model');
                $this->Auth_model->update_user_session_data($this->user_id);

                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_user_updated', 'users'));
            }
        }

        if ($this->input->post('btn_subscriptions_save') && $this->pg_module->is_module_installed('subscriptions')) {
            // Save user subscribers
            $user_subscriptions_list = $this->input->post('user_subscriptions_list', true);
            $this->load->model('subscriptions/models/Subscriptions_users_model');
            $this->Subscriptions_users_model->save_user_subscriptions($this->user_id, $user_subscriptions_list);
        }

        $user = $this->Users_model->get_user_by_id($this->user_id);
        $this->pg_seo->set_seo_data($user);

        $this->load->helper('seo');
        $this->session->set_userdata(array('service_redirect' => rewrite_link('users', 'settings')));

        // breadcrumbs
        $this->load->model('Menu_model');
        $this->Menu_model->breadcrumbs_set_parent('settings-item');

        $this->view->assign('use_repassword', $use_repassword);
        $this->view->assign('user_id', $this->user_id);
        $this->view->assign('user', $user);
        $this->view->render('account_settings');
    }

    public function account($action = 'services', $page = 1)
    {
        $page = intval($page);

        $this->load->model('users/models/Auth_model');
        $this->Auth_model->update_user_session_data($this->user_id);

        $user = $this->Users_model->get_user_by_id($this->user_id);
        $this->pg_seo->set_seo_data($user);

        // breadcrumbs
        $this->load->model('Menu_model');
        $this->Menu_model->breadcrumbs_set_parent('account-item');

        $this->load->helper('seo');
        $base_url = rewrite_link('users', 'account', array('action' => $action));
        $this->setAccountMenu($action);

        $this->view->assign('base_url', $base_url);
        $this->view->assign('page', $page);
        $this->view->assign('action', $action);
        $this->view->assign('user_id', $this->user_id);
        $this->view->assign('user', $user);
        $this->view->render('account');
    }

    private function setAccountMenu($action)
    {
        switch ($action) {
            case 'services':
                $this->session->set_userdata(array('service_redirect' => rewrite_link('users', 'account', array('action' => 'services'))));
                $this->session->set_userdata(array('service_activate_redirect' => rewrite_link('users', 'account', array('asction' => 'services'))));
                $this->Menu_model->breadcrumbs_set_active(l('header_services', 'users'));
                break;
            /* case 'my_services':
              $this->session->set_userdata(array('service_activate_redirect' => rewrite_link('users', 'account', array('action'=>'my_services'))));
              $this->Menu_model->breadcrumbs_set_active(l('header_my_services', 'users'));
              break; */
            case 'update':
                $this->Menu_model->breadcrumbs_set_active(l('header_account_update', 'users'));
                break;
            case 'payments_history':
                $this->Menu_model->breadcrumbs_set_active(l('header_my_payments_statistic', 'payments'));
                break;
            case 'banners':
                $this->Menu_model->breadcrumbs_set_active(l('header_my_banners', 'banners'));
                break;
            case 'memberships':
                $this->Menu_model->breadcrumbs_set_active(l('header_memberships', 'users'));
                break;
            case 'donate':
                $this->Menu_model->breadcrumbs_set_active(l('donate', 'start'));
                break;
        }
    }

    public function ajax_view_map_user_location()
    {
        $return = array("errors" => "", "html" => "");

        if (!$this->pg_module->is_module_installed('geomap')) {
            $this->view->assign($return);

            return false;
        }

        $id_user          = $this->input->post('id', true);
        $load_map_scripts = $this->input->post('load_map_scripts', true);

        if ($id_user) {
            $user = $this->Users_model->get_user_by_id($id_user, true);

            $markers[] = array(
                'gid'     => $user['id'],
                'country' => $user['country'],
                'region'  => $user['region'],
                'city'    => $user['city'],
                'address' => $user['address'],
                'lat'     => (float) $user['lat'],
                'lon'     => (float) $user['lon'],
                'info'    => $user['output_name'] . ", " . $user['age'],
            );
            $this->view->assign('markers', $markers);
            $this->view->assign('header', $user["location"]);
            $this->view->assign('load_map_scripts', $load_map_scripts);

            $return['html'] = $this->view->fetch('ajax_view_map_user_location', 'user', 'users');
        }
        $this->view->assign($return);
    }

    public function profile($profile_section = 'view', $subsection = 'all')
    {
        $subsection = trim(strip_tags($subsection));
        if (!$profile_section) {
            $profile_section = 'view';
        }
        if ($profile_section == 'gallery' && !in_array($subsection, $this->subsections)) {
            $subsection = $this->subsections['default'];
        }

        $pm_installed = $this->pg_module->is_module_installed('perfect_match');

        $this->load->model('Field_editor_model');
        $this->Field_editor_model->initialize($this->Users_model->form_editor_type);
        $fields_for_select = array();
        $sections          = array();
        if ($profile_section != 'view' && $profile_section != 'details' && $profile_section != 'wall' && $profile_section != 'gallery' && $profile_section != 'subscriptions') {
            $section = $this->Field_editor_model->get_section_by_gid($profile_section);
            if (!empty($section)) {
                $fields_for_select = $this->Field_editor_model->get_fields_for_select($section['gid']);
            }
        } elseif ($profile_section == 'view' || $profile_section == 'details') {
            $sections          = $this->Field_editor_model->get_section_list();
            $sections_gids     = array_keys($sections);
            $fields_for_select = $this->Field_editor_model->get_fields_for_select($sections_gids);
        }
        $this->Users_model->set_additional_fields($fields_for_select);

        $data = $this->Users_model->get_user_by_id($this->user_id);

        if ($this->input->post('btn_register')) {
            $post_data        = array();
            $validate_section = null;

            if ($profile_section == 'personal') {
                $post_data = array(
                    'nickname'   => $this->input->post('nickname', true),
                    'fname'      => $this->input->post('fname', true),
                    'sname'      => $this->input->post('sname', true),
                    'id_country' => $this->input->post('id_country', true),
                    'id_region'  => $this->input->post('id_region', true),
                    'id_city'    => $this->input->post('id_city', true),
                    'birth_date' => $this->input->post('birth_date', true),
                    'lat'        => $this->input->post('lat', true),
                    'lon'        => $this->input->post('lon', true),
                    /* <custo_M> */
                    'living_with' => $this->input->post('living_with', true),
                    /* </custo_M> */
                );

                if ($pm_installed) {
                    $post_data['looking_user_type'] = $this->input->post('looking_user_type', true);
                    $post_data['age_min']           = $this->input->post('age_min', true);
                    $post_data['age_max']           = $this->input->post('age_max', true);
                }
            } else {
                foreach ($fields_for_select as $field) {
                    $post_data[$field] = $this->input->post($field, true);
                }
                $validate_section = $profile_section;
            }

            $validate_data = $this->Users_model->validate($this->user_id, $post_data, 'user_icon', $validate_section);
            if (!empty($validate_data['errors'])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data['errors']);
            } else {
                if ($this->input->post('user_icon_delete') || (isset($_FILES['user_icon']) && is_array($_FILES['user_icon']) && is_uploaded_file($_FILES['user_icon']['tmp_name']))) {
                    $this->load->model('Uploads_model');
                    if ($data['user_logo_moderation']) {
                        $this->Uploads_model->delete_upload($this->Users_model->upload_config_id, $this->user_id, $data['user_logo_moderation']);
                        $validate_data['data']['user_logo_moderation'] = '';
                        $this->load->model('Moderation_model');
                        $this->Moderation_model->delete_moderation_item_by_obj($this->Users_model->moderation_type, $this->user_id);
                    } elseif ($data['user_logo']) {
                        $this->Uploads_model->delete_upload($this->Users_model->upload_config_id, $this->user_id, $data['user_logo']);
                        $validate_data['data']['user_logo'] = '';
                    }
                }
                if ($this->Users_model->save_user($this->user_id, $validate_data['data'], 'user_icon')) {
                    $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_update_user', 'users'));
                }

                $this->load->model('users/models/Auth_model');
                $this->Auth_model->update_user_session_data($this->user_id);

                $seo_data                 = $data;
                $seo_data['section']      = $profile_section;
                $seo_data['section-code'] = $profile_section;
                $seo_data['section-name'] = l('filter_section_' . $profile_section, 'users');

                if ($subsection != 'all') {
                    $seo_data['subsection']      = $subsection;
                    $seo_data['subsection-code'] = $subsection;
                    $seo_data['subsection-name'] = l($subsection, 'media');
                }

                $this->load->helper('seo');
                $url = rewrite_link('users', 'profile', 'view');
                redirect($url);
            }
        }

        $this->view->assign('action', $profile_section);

        $lang_id    = $this->pg_language->current_lang_id;
        $data       = $this->Users_model->format_user($data, false, $lang_id);

        $data['have_avatar'] = ($data['user_logo'] || $data['user_logo_moderation']);

        if (empty($data['activity'])) {
            $data['available_activation'] = $this->Users_model->check_available_user_activation($this->user_id);
            $service_status               = $this->Users_model->service_available_user_activate_in_search_action($this->user_id);
            if ($service_status["content_buy_block"] == false && ($data['user_logo'] || $data['user_logo_moderation']) && $data['id_region']) {
                $data['activity'] = 1;
            }
        }
        if ($profile_section == 'view' || $profile_section == 'details') {
            foreach ($sections as $sgid => $sdata) {
                $params["where"]["section_gid"] = $sgid;
                $sections[$sgid]['fields']      = $this->Field_editor_model->format_item_fields_for_view($params, $data);
            }
        } elseif (!empty($section)) {
            $params["where"]["section_gid"] = $section['gid'];
            $fields_data                    = $this->Field_editor_model->get_form_fields_list($data, $params);
            $this->view->assign('fields_data', $fields_data);
        }
        if ($profile_section == 'personal') {
            $this->load->model('Properties_model');
            $user_types = $this->Properties_model->get_property('user_type');
            $this->view->assign('user_types', $user_types);
            $age_range  = range($this->pg_module->get_module_config('users', 'age_min'), $this->pg_module->get_module_config('users', 'age_max'));
            $this->view->assign('age_range', $age_range);
        }

        $this->load->helper('seo');

        if ($profile_section == 'gallery') {
            $gallery_filters = array();

            foreach ($this->subsections as $subsection_code) {
                $subsection_name = l($subsection_code, 'media');

                $seo_data                 = $data;
                $seo_data['section']      = $profile_section;
                $seo_data['section-code'] = $profile_section;
                $seo_data['section-name'] = l('filter_section_' . $profile_section, 'users');

                $seo_data['subsection']            = $subsection_code;
                $seo_data['subsection-code']       = $subsection_code;
                $seo_data['subsection-name']       = $subsection_name;
                $subsection_link                   = rewrite_link('users', 'view', $seo_data);
                $gallery_filters[$subsection_code] = array('link' => $subsection_link, 'name' => $subsection_name);
            }

            $this->view->assign('gallery_filters', $gallery_filters);

            $location_base_url = function ($subsection_code, $subsection_name) use ($data, $profile_section) {
                $seo_data                 = $data;
                $seo_data['section']      = $profile_section;
                $seo_data['section-code'] = $profile_section;
                $seo_data['section-name'] = l('filter_section_' . $profile_section, 'users');

                $seo_data['subsection']      = $subsection_code;
                $seo_data['subsection-code'] = $subsection_code;
                $seo_data['subsection-name'] = $subsection_name;

                $subsection_link = rewrite_link('users', 'profile', $seo_data);

                return $subsection_link;
            };
            $this->view->assign('location_base_url', $location_base_url);
        }

        $this->pg_seo->set_seo_data($data);

        if (!empty($section)) {
            $section_name = $section['name'];
        } else {
            $section_name = l("filter_section_" . $profile_section, "users");
        }

        // breadcrumbs
        $this->load->model('Menu_model');
        $this->Menu_model->breadcrumbs_set_parent('my-profile-item');
        $this->Menu_model->breadcrumbs_set_active($section_name);

        $page_data['date_format']      = $this->pg_date->get_format('date_literal', 'st');
        $page_data["date_time_format"] = $this->pg_date->get_format('date_time_literal', 'st');

        $not_editable_fields = $this->Users_model->fields_not_editable;
        foreach ($not_editable_fields as $field) {
            $not_editable_fields[$field] = 1;
        }

        $data['services_status'] = $this->Users_model->services_status($data);

        if (!empty($data['services_status']['user_activate_in_search']) && $data['services_status']['user_activate_in_search']['status'] == 1) {
            if (strtotime($data['activated_end_date']) > date("U")) {
                $data['activity_in_search'] = 1;
            } else {
                $data['activity_in_search'] = 0;
            }
        } else {
            $data['services_status']['user_activate_in_search']['status'] = 0;
        }

        if ($profile_section == 'gallery') {
            $gallery_filters = array();

            foreach ($this->subsections as $subsection_code) {
                $subsection_name = l($subsection_code, 'media');

                $seo_data                 = $data;
                $seo_data['section']      = $profile_section;
                $seo_data['section-code'] = $profile_section;
                $seo_data['section-name'] = l('filter_section_' . $profile_section, 'users');

                $seo_data['subsection']            = $subsection_code;
                $seo_data['subsection-code']       = $subsection_code;
                $seo_data['subsection-name']       = $subsection_name;
                $subsection_link                   = rewrite_link('users', 'profile', $seo_data);
                $gallery_filters[$subsection_code] = array('link' => $subsection_link, 'name' => $subsection_name);
            }

            $this->view->assign('gallery_filters', $gallery_filters);
        }

        $seo_data                 = $data;
        $seo_data['section']      = $profile_section;
        $seo_data['section-code'] = $profile_section;
        $seo_data['section-name'] = l('filter_section_' . $profile_section, 'users');

        if ($subsection != 'all' && $subsection != 'prompt') {
            $seo_data['subsection']      = $subsection;
            $seo_data['subsection-code'] = $subsection;
            $seo_data['subsection-name'] = l($subsection, 'media');
        }

        $url               = rewrite_link('users', 'profile', $seo_data);
        $location_full_url = rewrite_link('users', 'profile', $seo_data);
        $this->session->set_userdata(array('service_redirect' => $location_full_url));

        if ($pm_installed) {
            $age_min = $this->pg_module->get_module_config('users', 'age_min');
            $age_max = $this->pg_module->get_module_config('users', 'age_max');
            $this->view->assign('age_min', $age_min);
            $this->view->assign('age_max', $age_max);
        } else {
            $not_editable_fields['looking_user_type'] = 1;
            $not_editable_fields['age_min']           = 1;
            $not_editable_fields['age_max']           = 1;
        }

        if ($profile_section == 'view') {
            $this->load->model('Properties_model');
            $looking_living_with_types = $this->Properties_model->get_property('living_with', $lang_id);
            $this->view->assign('living_with_options', $looking_living_with_types['option']);

            foreach ($this->Users_model->dictionaries as $dictionary) {
                $dictionary_options = ld($dictionary, 'users');
                if (!empty($dictionary_options['option'])) {
                    $this->view->assign($dictionary . '_options', $dictionary_options['option']);
                }
            }
        }

        $this->view->assign('not_editable_fields', $not_editable_fields);
        $this->view->assign('page_data', $page_data);
        $this->view->assign('data', $data);

        $this->view->assign('sections', $sections);
        $this->view->assign('subsection', $subsection);
        $this->view->assign('user_id', $this->user_id);
        $this->view->assign('edit_mode', 1);
        $this->view->render('profile');
    }

    /**
     *  Description
     *
     *  @param integer $ref_id
     *
     *  @return void
     */
    public function registration($ref_id = null)
    {
        if ($this->session->userdata('auth_type') == 'user') {
            redirect(site_url() . 'start/homepage');
        }

        $pm_installed = $this->pg_module->is_module_installed('perfect_match');

        $age_min        = $this->pg_module->get_module_config('users', 'age_min');
        $age_max        = $this->pg_module->get_module_config('users', 'age_max');
        $use_repassword = $this->pg_module->get_module_config('users', 'use_repassword');
        $this->load->model('Properties_model');
        $user_types     = $this->Properties_model->get_property('user_type');

        if ($this->input->post('is_short_form')) {
            $age = 0;

            $birth_date_month = (int)$this->input->post('birth_date_month', true);
            $birth_date_day = (int)$this->input->post('birth_date_day', true);
            $birth_date_year = (int)$this->input->post('birth_date_year', true);

            if ($birth_date_month && $birth_date_day && $birth_date_year) {
                $datetime = date_create($birth_date_year . '-' . sprintf('%02d', $birth_date_month) . '-' . sprintf('%02d', $birth_date_day));
                if ($datetime) {
                    $age = $datetime->diff(date_create('today'))->y;
                }
            }
            $data = [
                'postal_code' => $this->input->post('postal_code', true),
                'email' => $this->input->post('email', true),
                'password' => $this->input->post('password', true),
                'age' => $age,
            ];
        } elseif ($this->input->post('btn_register')) {
            $post_data = array(
                'email'                     => $this->input->post('email', true),
                'password'                  => $this->input->post('password', true),
                'nickname'                  => $this->input->post('nickname', true),
                //'birth_date'                => $this->input->post('birth_date', true),
                'id_country'                => $this->input->post('id_country', true),
                'id_region'                 => $this->input->post('id_region', true),
                'id_city'                   => $this->input->post('id_city', true),
                'user_type'                 => $this->input->post('user_type', true),
                'looking_user_type'         => $this->input->post('looking_user_type', true),
                'confirmation'              => $this->input->post('confirmation', true),
                //'captcha_confirmation'      => $this->input->post('captcha_confirmation', true),
                'age_min'                   => $age_min,
                'age_max'                   => $age_max,
                'lat'                       => $this->input->post('lat', true),
                'lon'                       => $this->input->post('lon', true),
                'fk_referral_id'            => $this->input->post('fk_referral_id', true),

                /* <custo_M> */
                'fname'                     => $this->input->post('fname', true),
                'age'                       => $this->input->post('age', true),
                'living_with'               => $this->input->post('living_with', true),
                'ethnicity'                 => $this->input->post('ethnicity', true),
                'relationship_status'       => $this->input->post('relationship_status', true),
                'height'                    => $this->input->post('height', true),
                'postal_code'               => $this->input->post('postal_code', true),
                /* </custo_M> */
            );

            if ($pm_installed) {
                $post_data['looking_user_type'] = $this->input->post('looking_user_type', true);
                $post_data['age_min']           = $age_min;
                $post_data['age_max'] = $age_max;
            }

            if ($use_repassword) {
                $post_data['repassword'] = $this->input->post('repassword', true);
            }

            $validate_data = $this->Users_model->validate(null, $post_data, 'user_icon');
            if (!empty($validate_data["success"])) {
                $this->system_messages->addMessage(View::MSG_SUCCESS, $validate_data["success"]);
            }
            if (!empty($validate_data["errors"])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data["errors"]);
                $data = $post_data;
            } else {
                $data             = $validate_data["data"];
                $data['activity'] = 0;
                if ($this->use_email_confirmation) {
                    $data["confirm"]      = 0;
                    $data["confirm_code"] = substr(md5(date("Y-m-d H:i:s") . $data["nickname"]), 0, 10);
                } else {
                    $data["confirm"]      = 1;
                    $data["confirm_code"] = "";
                }
                $data["approved"] = $this->use_approve ? 0 : 1;
                $saved_password   = $data["password"];
                $data["password"] = md5($data["password"]);
                $data["lang_id"]  = $this->session->userdata("lang_id");
                if (!$data["lang_id"]) {
                    $data["lang_id"] = $this->pg_language->get_default_lang_id();
                }

                $user_id = $this->Users_model->registerUser($data);

                if ($this->pg_module->is_module_installed('incomplete_signup')) {
                    $this->load->model("incomplete_signup/models/Incomplete_signup_model");
                    $this->Incomplete_signup_model->delete_unregistered_user_by_email($data['email']);
                }
                if ($this->pg_module->is_module_installed('subscriptions') && !SOCIAL_MODE) {
                    // Save user subscribers
                    $this->load->model("subscriptions/models/Subscriptions_users_model");
                    $this->Subscriptions_users_model->save_user_subscriptions($user_id, $this->input->post('user_subscriptions_list'));
                }

                $this->load->model('Notifications_model');
                $data["password"] = $saved_password;
                if ($this->use_email_confirmation) {
                    $link                  = site_url() . "users/confirm/" . $data["confirm_code"];
                    $data["confirm_block"] = l('confirmation_code', 'users') . ': ' . $data["confirm_code"] . "\n\n" . str_replace("[link]", $link, l('confirm_block_text', 'users'));
                }
                $this->Notifications_model->send_notification($data["email"], 'users_registration', $data, '', $data['lang_id']);

                //if (!$this->use_email_confirmation) {
                    $this->load->model("users/models/Auth_model");
                    $auth_data = $this->Auth_model->login($user_id);
                    if (!empty($auth_data["errors"])) {
                        $this->system_messages->addMessage(View::MSG_ERROR, $auth_data["errors"]);
                        $this->view->setRedirect(site_url() . 'users/registration');
                    } else {
                        $this->view->setRedirect(site_url() . 'users/profile/view/prompt', 'hard');
                    }
                /*} else {
                    $this->system_messages->addMessage(View::MSG_INFO, l('info_please_checkout_mailbox', 'users'));
                    $this->view->setRedirect(site_url() . 'users/confirm');
                }*/
            }
        } else {
            $post_data        = array(
                'email'          => $this->input->post('email', true),
                'fk_referral_id' => $ref_id,
            );
            $validate_data    = $this->Users_model->validate(null, $post_data);
            $data             = $validate_data['data'];
            $data['password'] = $this->input->post('password', true);
            // Differ looking_user_type from user_type
            if ($this->pg_module->is_module_installed('network')) {
                if (!isset($data['user_type']) && !isset($data['looking_user_type']) && count($user_types['option'])) {
                    $data['looking_user_type'] = key(array_slice($user_types['option'], 1, 1));
                }
            }
            if (DEMO_MODE) {
                $data = $this->Users_model->demoUser($data);
            }
        }

        $this->load->plugin('captcha');
        $this->config->load('captcha_settings');
        $captcha_settings            = $this->config->item('captcha_settings');
        $captcha                     = create_captcha($captcha_settings);
        $this->session->set_userdata('captcha_word', $captcha['word']);
        $data['captcha_image']       = $captcha['image'];
        $data['captcha_word_length'] = strlen($captcha['word']);

        $editable_fields = array('looking_user_type' => 0, 'subscriptions' => 0);
        if ($pm_installed) {
            $editable_fields['looking_user_type'] = 1;
        }
        if (!SOCIAL_MODE) {
            $editable_fields['subscriptions'] = 1;
        }
        $this->view->assign('editable_fields', $editable_fields);

        $this->view->assign('user_types', $user_types);

        $page_data = ['form_action' => site_url() . 'users/registration'];
        $this->view->assign('page_data', $page_data);

        foreach ($this->Users_model->dictionaries as $dictionary) {
            $dictionary_options = ld($dictionary, 'users');
            if (!empty($dictionary_options['option'])) {
                $this->view->assign($dictionary . '_options', $dictionary_options['option']);
            }
        }

        // breadcrumbs
        $this->load->model('Menu_model');
        $this->Menu_model->breadcrumbs_set_active(l('header_register', 'users'));
        $this->view->assign('use_repassword', $use_repassword);
        $this->view->assign('age_min', $age_min);
        $this->view->assign('age_max', $age_max);
        $this->view->assign('data', $data);
        $this->view->render('register');
    }

    public function restore()
    {
        if ($this->session->userdata("auth_type") == "user") {
            redirect();
        }

        if ($this->input->post('btn_save')) {
            $email     = strip_tags($this->input->post("email", true));
            $user_data = $this->Users_model->get_user_by_email($email);
            if (empty($user_data) || !$user_data["id"]) {
                $this->system_messages->addMessage(View::MSG_ERROR, l('error_user_no_exists', 'users'));
            } elseif (!$user_data["confirm"]) {
                $this->system_messages->addMessage(View::MSG_ERROR, l('error_unconfirmed_user', 'users'));
            } elseif (!$user_data["approved"]) {
                $this->system_messages->addMessage(View::MSG_ERROR, l('error_user_is_blocked', 'users'));
            } else {
                $user_data["password"] = $new_password          = substr(md5(date("Y-m-d H:i:s") . $user_data["email"]), 0, 6);
                $data["password"]      = md5($new_password);
                $this->Users_model->save_user($user_data["id"], $data);

                $this->load->model('Notifications_model');
                $this->Notifications_model->send_notification($user_data["email"], 'users_fogot_password', $user_data, '', $user_data['lang_id']);
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_restore_mail_sent', 'users'));
                redirect(site_url() . "users/restore");
            }
        }
        // breadcrumbs
        $this->load->model('Menu_model');
        $this->Menu_model->breadcrumbs_set_active(l('header_restore_password', 'users'));

        $this->view->render('forgot_form');
    }

    public function confirm($code = '')
    {
        $code = trim(strip_tags($code));
        if (!$code) {
            $code = $this->input->post('code', true);
        }
        if (!$code) {
            $this->load->model('Menu_model');
            $this->Menu_model->breadcrumbs_set_active(l('header_confirm_email', 'users'));
            $this->view->render('confirm_form');

            return;
        }
        $user = $this->Users_model->get_user_by_confirm_code($code);
        if (empty($user)) {
            $this->system_messages->addMessage(View::MSG_ERROR, l('error_user_no_exists', 'users'));
            redirect();
        } elseif ($user["confirm"]) {
            $this->system_messages->addMessage(View::MSG_ERROR, l('error_user_already_confirm', 'users'));
            redirect(site_url() . "users/profile", 'hard');
        } else {
            $data["confirm"] = 1;
            $this->Users_model->save_user($user["id"], $data);

            $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_confirm', 'users'));

            $this->load->model("users/models/Auth_model");
            $auth_data = $this->Auth_model->login($user["id"]);
            if (!empty($auth_data["errors"])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $auth_data["errors"]);
            }
            redirect(site_url() . "users/profile", 'hard');
        }
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
            $hide_user_names = $this->pg_module->get_module_config('users', 'hide_user_names');
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
            if (!is_array($selected)) {
                $selected = array($selected);
            }
            $params["where_sql"][] = "id NOT IN (" . implode($selected) . ")";
        }

        $user_type = $this->input->post('user_type', true);
        if ($user_type) {
            $params["where"]["user_type"] = $user_type;
        }

        $items_on_page = $this->pg_module->get_module_config('start', 'admin_items_per_page');
        $items         = $this->Users_model->get_users_list_by_key($page, $items_on_page, array("nickname" => "asc"), $params, array(), true, true);

        $return["all"]          = $this->Users_model->get_users_count($params);
        $return["items"]        = $items;
        $return["current_page"] = $page;
        $return["pages"]        = ceil($return["all"] / $items_on_page);

        $this->view->assign($return);

        return;
    }

    public function ajax_get_selected_users()
    {
        $selected = $this->input->post('selected', true);
        $selected = array_slice(array_unique(array_map('intval', (array) $selected)), 0, 1000);
        if (!empty($selected)) {
            $return['selected'] = $this->Users_model->get_users_list(null, null, array("nickname" => "asc"), array(), $selected, true, true);
        } else {
            $return['selected'] = array();
        }
        $this->view->assign($return);

        return;
    }

    public function ajax_get_users_form($max_select = 1)
    {
        $selected = $this->input->post('selected', true);

        if (!empty($selected)) {
            $data["selected"] = $this->Users_model->get_users_list(null, null, array("nickname" => "asc"), array(), $selected, false);
        } else {
            $data["selected"] = array();
        }
        $data["max_select"] = $max_select ? $max_select : 0;

        $this->view->assign('select_data', $data);
        $this->view->render('ajax_user_select_form');
    }

    public function search($order = "default", $order_direction = "DESC", $page = 1, $type = 'basic')
    {
        $event_handler = EventDispatcher::getInstance();
        $event         = new EventUsers();
        $event->setSearchFrom(intval($this->user_id));
        $event_handler->dispatch('user_search', $event);

        $viewer_id         = intval($this->user_id);
        $show_list_buttons = 1;

        if ($viewer_id) {
            $viewer = $this->Users_model->format_user($this->Users_model->get_user_by_id($viewer_id));
            if ($viewer['activity'] === '0') {
                $show_list_buttons = 0;
            }
        }
        $this->view->assign('show_list_buttons', $show_list_buttons);
        if (empty($_POST)) {
            if ($this->session->userdata("users_search")) {
                $current_settings = $this->session->userdata("users_search");
            } else {
                $current_settings = array();
            }
            $data = (!empty($current_settings)) ? $current_settings : array();
        } else {
            foreach ($_POST as $key => $val) {
                $value = $this->input->post($key, true);
                if (is_string($value)) {
                    $data[$key] = trim(strip_tags($value));
                } else {
                    $data[$key] = $value;
                }
            }
            $data = array_merge($this->Users_model->get_minimum_search_data(), $data);
        }
        $this->view->assign('block', $this->searchListBlock($data, $order, $order_direction, $page, 'advanced'));

        if (!empty($data['search'])) {
            $this->view->assign('search_text', $data['search']);
        }

        $_SESSION['search_view_mode'] = 'list';
        
        $view_mode = (!empty($_SESSION['search_view_mode']) && $_SESSION['search_view_mode'] == 'gallery') ? 'gallery' : 'list';
        $this->view->assign('view_mode', $view_mode);

        $this->view->assign('type', $type);

        $this->load->model('memberships/models/Memberships_users_model');
        $this->view->assign('is_has_platinum', $this->Memberships_users_model->userHasMembership($this->user_id, 3));

        $this->Menu_model->breadcrumbs_set_parent('search_item');
        $this->view->render('users_list');
    }

    public function advanced_search($order = "default", $order_direction = "DESC", $page = 1)
    {
        $this->search($order, $order_direction, $page, 'advanced_search');
    }

    public function living_with_search($order = "default", $order_direction = "DESC", $page = 1)
    {
        $this->search($order, $order_direction, $page, 'living_with_search');
    }

    public function by_location_search($order = "default", $order_direction = "DESC", $page = 1)
    {
        $this->search($order, $order_direction, $page, 'by_location_search');
    }

    public function my_match($order = "default", $order_direction = "DESC", $page = 1)
    {
        $this->search($order, $order_direction, $page, 'my_match');
    }

    public function reverse_match($order = "default", $order_direction = "DESC", $page = 1)
    {
        $this->search($order, $order_direction, $page, 'reverse_match');
    }

    public function ajax_search($s = '', $order = "default", $order_direction = "DESC", $page = 1)
    {
        if (empty($_POST)) {
            $current_settings = ($this->session->userdata("users_search")) ? $this->session->userdata("users_search") : array();
            $data             = (!empty($current_settings)) ? $current_settings : array();
        } else {
            foreach ($_POST as $key => $val) {
                $data[$key] = $this->input->post($key, true);
            }
            $data = array_merge($this->Users_model->get_minimum_search_data(), $data);

            if (!empty($data['save_search'])) {
                $this->load->model('users/models/Saved_search_model');
                $validate = $this->Saved_search_model->validateSearch(null, ['id_user' => $this->user_id, 'search_data' => $data]);
                if (empty($validate['errors'])) {
                    $this->Saved_search_model->saveSearch(null, $validate['data']);
                }
            }
        }

        if (!empty($data['location_type'])) {
            switch ($data['location_type']) {
                case 'region':
                    if (isset($data['distance'])) {
                        unset($data['distance']);
                    }
                    break;
                case 'distance':
                    if (isset($data['id_country'])) {
                        unset($data['id_country']);
                    }
                    if (isset($data['id_region'])) {
                        unset($data['id_region']);
                    }
                    if (isset($data['id_city'])) {
                        unset($data['id_city']);
                    }
                    break;
            }
        }

        echo $this->searchListBlock($data, $order, $order_direction, $page, 'advanced');
    }

    public function ajax_search_counts($type = 'advanced')
    {
        $result = array('count' => 0, 'error' => '', 'string' => '');
        if (!empty($_POST)) {
            foreach ($_POST as $key => $val) {
                $data[$key] = $this->input->post($key, true);
            }
            $criteria         = $this->getAdvancedSearchCriteria($data);
            $result["count"]  = $this->Users_model->get_users_count($criteria);
            $result["string"] = str_replace("[count]", $result["count"], l('user_results_string', 'users'));
        }
        $this->view->assign($result);
    }

    private function searchListBlock($data = array(), $order = "default", $order_direction = "DESC", $page = 1, $search_type = 'advanced')
    {
        if ($this->user_id) {
            $user = $this->Users_model->get_user_by_id($this->user_id);
            if (!$user['confirm']) {
                redirect(site_url() . 'users/profile');
            }
        }

        $this->view->assign('user_id', $this->user_id);

        $current_settings = $this->session->userdata("users_search") ? $this->session->userdata("users_search") : $this->Users_model->get_default_search_data();
        if (!empty($data)) {
            $current_settings = $data;
        }
        $this->session->set_userdata("users_search", $current_settings);
        $criteria = $this->getAdvancedSearchCriteria($current_settings);

        $search_url = site_url() . "users/search";
        $url        = site_url() . "users/search/" . $order . "/" . $order_direction . "/";

        /* highligth in search */
        $hl_data['service_highlight'] = $this->Users_model->service_status_highlight_in_search($this->Users_model->format_user($this->Users_model->get_user_by_id($this->user_id)));
        if ($hl_data['service_highlight']['status']) {
            $this->load->helper('seo');
            $this->session->set_userdata(array('service_redirect' => rewrite_link('users', 'search')));
        }
        $this->view->assign('hl_data', $hl_data);

        $this->view->assign('search_type', $search_type);
        $order = trim(strip_tags($order));
        if (!$order) {
            $order = "date_created";
        }
        $this->view->assign('order', $order);

        $order_direction = strtoupper(trim(strip_tags($order_direction)));
        if ($order_direction != 'DESC' && $order_direction != "ASC") {
            $order_direction = "DESC";
        }
        $this->view->assign('order_direction', $order_direction);

        $items_count = $this->Users_model->get_users_count($criteria);

        if (!$page) {
            $page = 1;
        }
        $items_on_page = $this->pg_module->get_module_config('users', 'items_per_page');
        $this->load->helper('sort_order');
        $page          = get_exists_page_number($page, $items_count, $items_on_page);

        $sort_data = array(
            "url"       => $search_url,
            "order"     => $order,
            "direction" => $order_direction,
            "links"     => array(
                "default"      => l('field_default_sorter', 'users'),
                "name"         => l('field_name', 'users'),
                "views_count"  => l('field_views_count', 'users'),
                "date_created" => l('field_date_created', 'users'),
            ),
        );
        $this->view->assign('sort_data', $sort_data);

        $use_leader = false;
        if ($items_count > 0) {
            $order_array = array();
            if ($order == 'default') {
                if (!empty($data['id_region']) && intval($data['id_region'])) {
                    $order_array['leader_bid'] = 'DESC';
                }
                if (!empty($criteria['fields']) && intval($criteria['fields'])) {
                    $order_array["fields"] = 'DESC';
                } else {
                    $order_array["up_in_search_end_date"] = 'DESC';
                    $order_array["date_created"]          = $order_direction;
                }
                $use_leader = true;
            } else {
                if ($order == 'name') {
                    if ($this->pg_module->get_module_config('users', 'hide_user_names')) {
                        $order_array['nickname'] = $order_direction;
                    } else {
                        $order_array['fname'] = $order_direction;
                        $order_array['sname'] = $order_direction;
                    }
                } else {
                    $order_array[$order] = $order_direction;
                }
            }

            $lang_id = $this->pg_language->current_lang_id;

            $users = $this->Users_model->get_users_list($page, $items_on_page, $order_array, $criteria, array(), true, false, $lang_id);

            $this->view->assign('users', $users);
            $this->view->assign('users_count', count($users));
        }

        $_SESSION['search_view_mode'] = 'list';
    
        $this->load->helper("navigation");
        $page_data                     = get_user_pages_data($url, $items_count, $items_on_page, $page, 'briefPage');
        $page_data["date_format"]      = $this->pg_date->get_format('date_literal', 'st');
        $page_data["date_time_format"] = $this->pg_date->get_format('date_time_literal', 'st');
        $page_data["use_leader"]       = $use_leader;
        $page_data["view_type"]        = isset($_SESSION['search_view_mode']) ? $_SESSION['search_view_mode'] : 'gallery';
        $this->view->assign('page_data', $page_data);

        $use_save_search = ($this->session->userdata("auth_type") == "user") ? true : false;
        $this->view->assign('use_save_search', $use_save_search);

        $pm_installed = $this->pg_module->is_module_installed('perfect_match');
        $this->view->assign('pm_installed', $pm_installed);

        return $this->view->fetch('users_list_block');
    }

    private function getAdvancedSearchCriteria($data)
    {
        $this->load->model('field_editor/models/Field_editor_forms_model');
        $fe_criteria = $this->Field_editor_forms_model->get_search_criteria($this->Users_model->advanced_search_form_gid, $data, $this->Users_model->form_editor_type, false);
        if (!empty($data["search"])) {
            $data["search"] = trim(strip_tags($data["search"]));
            $this->load->model('Field_editor_model');
            $this->Field_editor_model->initialize($this->Users_model->form_editor_type);
            if (strlen($data["search"]) > 3) {
                $temp_criteria              = $this->Field_editor_model->return_fulltext_criteria($data["search"], 'BOOLEAN MODE');
                $fe_criteria['fields'][]    = $temp_criteria['user']['field'];
                $fe_criteria['where_sql'][] = $temp_criteria['user']['where_sql'];
            } else {
                $search_text_escape         = $this->db->escape($data["search"] . "%");
                $fe_criteria['where_sql'][] = "(nickname LIKE " . $search_text_escape . ")";
            }
        }
        $common_criteria   = $this->Users_model->get_common_criteria($data);
        $advanced_criteria = $this->Users_model->get_advanced_search_criteria($data);
        $criteria          = array_merge_recursive($fe_criteria, $common_criteria, $advanced_criteria);

        return $criteria;
    }

    private function guestAccess($user_id)
    {
        if (!empty($this->user_id)) {
            // Not guest
            return true;
        } elseif (!$this->pg_module->get_module_config('users', 'guest_view_profile_allow')) {
            // Guest; disallowed
            $this->system_messages->addMessage(View::MSG_ERROR, l('error_guest_limit', 'users'), 'users');
            redirect(site_url() . 'users/login_form');
        } elseif (!$this->pg_module->get_module_config('users', 'guest_view_profile_limit')) {
            // Guest; unlimited
            return true;
        }
        // Guest; limited
        $cookie        = array(
            'name'   => 'profiles_viewed',
            'expire' => 604800, // 1 week
            'domain' => COOKIE_SITE_SERVER,
            'path'   => '/' . SITE_SUBFOLDER,
        );
        $this->load->helper('cookie');
        $allowed_views = $this->pg_module->get_module_config('users', 'guest_view_profile_num');
        if (empty($allowed_views)) {
            // limit is zero
            $cookie['value'] = array();
            set_cookie($cookie);
            $this->system_messages->addMessage(View::MSG_ERROR, l('error_guest_limit', 'users'), 'users');
            redirect(site_url() . 'users/login_form');
        }
        $curr_cookie = get_cookie('profiles_viewed');
        if (empty($curr_cookie)) {
            $viewed_arr = array();
        } else {
            $viewed_arr = unserialize($curr_cookie);
        }
        if ($this->pg_module->get_module_config('users', 'guest_view_profile_count_only_diff')) {
            $viewed_count = count(array_keys($viewed_arr));
        } else {
            $viewed_count = array_sum($viewed_arr);
        }
        if ($viewed_count >= $allowed_views) {
            $this->system_messages->addMessage(View::MSG_ERROR, l('error_guest_limit', 'users'), 'users');
            redirect(site_url() . 'users/login_form');
        } else {
            if (!isset($viewed_arr[$user_id])) {
                $viewed_arr[$user_id] = 1;
            } else {
                ++$viewed_arr[$user_id];
            }
            $cookie['value'] = serialize($viewed_arr);
            set_cookie($cookie);

            return true;
        }
    }

    public function view($user_id, $profile_section = '', $subsection = 'all')
    {
        $this->guestAccess($user_id);
        $user_id    = intval($user_id);
        $viewer_id  = intval($this->user_id);
        $subsection = trim(strip_tags($subsection));

        $event_handler = EventDispatcher::getInstance();
        $event         = new EventUsers();
        $event->setProfileViewFrom($viewer_id);
        $event->setProfileViewTo($user_id);
        $event_handler->dispatch('profile_view', $event);

        if ($viewer_id) {
            $viewer = $this->Users_model->format_user($this->Users_model->get_user_by_id($viewer_id));
        }

        if ($viewer_id && !$viewer['activity']) {
            if ($this->pg_module->is_module_installed('services')) {
                if ($service_access['use_status']) {
                    $this->system_messages->addMessage(View::MSG_ERROR, l('text_inactive_in_search', 'users'));
                    redirect(site_url() . 'services/form/user_activate_in_search', 'hard');

                    return;
                } else {
                    $viewer['available_activation'] = $this->Users_model->check_available_user_activation($viewer_id);
                    if ($viewer['available_activation']['status'] == 0) {
                        //$this->system_messages->addMessage(View::MSG_ERROR, l('text_register', 'users'));
                    } else {
                        //$this->system_messages->addMessage(View::MSG_ERROR, l('text_register_activate', 'users'));
                    }
                    redirect(site_url() . 'users/profile/view');
                }
            } else {
                $viewer['available_activation'] = $this->Users_model->check_available_user_activation($viewer_id);
                if ($viewer['available_activation']['status'] == 0) {
                    //$this->system_messages->addMessage(View::MSG_ERROR, l('text_register', 'users'));
                } else {
                    //$this->system_messages->addMessage(View::MSG_ERROR, l('text_register_activate', 'users'));
                }
                redirect(site_url() . 'users/profile/view');
            }
        }
        if (!$profile_section || 'wall' === $profile_section) {
            $profile_section = $this->pg_module->is_module_installed('wall_events') ? 'wall' : 'profile';
        }
        if ($profile_section == 'gallery') {
            if (!in_array($subsection, $this->subsections)) {
                $subsection = $this->subsections['default'];
            }
        }

        if ($viewer_id == $user_id) {
            redirect(site_url() . 'users/profile/');
        }

        if (!$viewer_id || !$viewer['is_hide_on_site']) {
            $this->load->model('users/models/Users_views_model');
            $this->Users_views_model->update_views($user_id, $viewer_id);
        }

        if ($profile_section == 'profile' || $profile_section == 'details') {
            $this->load->model('Field_editor_model');
            $this->Field_editor_model->initialize($this->Users_model->form_editor_type);
            $sections          = $this->Field_editor_model->get_section_list();
            $sections_gids     = array_keys($sections);
            $fields_for_select = $this->Field_editor_model->get_fields_for_select($sections_gids);
            $this->Users_model->set_additional_fields($fields_for_select);
        }

        $lang_id = $this->pg_language->current_lang_id;
        $data    = $this->Users_model->get_user_by_id($user_id);
        if (empty($data)) {
            redirect(site_url() . 'users/untitled/');
        }
        $data       = $this->Users_model->format_user($data, false, $lang_id);

        if ($profile_section == 'profile' || $profile_section == 'details') {
            foreach ($sections as $sgid => $sdata) {
                $params["where"]["section_gid"] = $sgid;
                $sections[$sgid]['fields']      = $this->Field_editor_model->format_item_fields_for_view($params, $data);
            }
            $this->view->assign('sections', $sections);
        }

        $this->load->helper('seo');

        $link = rewrite_link('users', 'view', $data);

        // breadcrumbs
        $this->load->model('Menu_model');
        $this->Menu_model->breadcrumbs_set_active($data['output_name'], $link);
        $this->Menu_model->breadcrumbs_set_active(l("filter_section_" . $profile_section, "users"));

        $page_data['date_format'] = $this->pg_date->get_format('date_literal', 'st');

        $page_data['date_format'] = $this->pg_date->get_format('date_literal', 'st');
        if ($profile_section == 'gallery') {
            $gallery_filters = array();

            foreach ($this->subsections as $subsection_code) {
                $subsection_name = l($subsection_code, 'media');

                $seo_data                 = $data;
                $seo_data['section']      = $profile_section;
                $seo_data['section-code'] = $profile_section;
                $seo_data['section-name'] = l('filter_section_' . $profile_section, 'users');

                $seo_data['subsection']            = $subsection_code;
                $seo_data['subsection-code']       = $subsection_code;
                $seo_data['subsection-name']       = $subsection_name;
                $subsection_link                   = rewrite_link('users', 'view', $seo_data);
                $gallery_filters[$subsection_code] = array('link' => $subsection_link, 'name' => $subsection_name);
            }

            $this->view->assign('gallery_filters', $gallery_filters);

            $location_base_url = function ($subsection_code, $subsection_name) use ($data, $profile_section) {
                $seo_data                 = $data;
                $seo_data['section']      = $profile_section;
                $seo_data['section-code'] = $profile_section;
                $seo_data['section-name'] = l('filter_section_' . $profile_section, 'users');

                $seo_data['subsection']      = $subsection_code;
                $seo_data['subsection-code'] = $subsection_code;
                $seo_data['subsection-name'] = $subsection_name;
                $subsection_link             = rewrite_link('users', 'view', $seo_data);

                return $subsection_link;
            };
            $this->view->assign('location_base_url', $location_base_url);
        }

        $lang_canonical = true;

        if ($this->pg_module->is_module_installed('seo')) {
            $lang_canonical = $this->pg_module->get_module_config('seo', 'lang_canonical');
        }

        if ($data['id_seo_settings']) {
            $this->load->model('Seo_advanced_model');
            $seo_settings              = $this->Seo_advanced_model->parse_seo_tags($data['id_seo_settings']);
            $seo_settings['canonical'] = rewrite_link('users', 'view', $data, false, null, $lang_canonical);
            $seo_settings['image']     = $data['media']['user_logo']['thumbs']['big'];
            $this->pg_seo->set_seo_tags($seo_settings);
        } else {
            $seo_settings                 = $data;
            $seo_settings['canonical']    = rewrite_link('users', 'view', $data, false, null, $lang_canonical);
            $seo_settings['image']        = $data['media']['user_logo']['thumbs']['big'];
            $seo_settings['section_code'] = $profile_section;
            $seo_settings['section_name'] = l('filter_section_' . $profile_section, 'users', $this->pg_language->current_lang_id);
            $this->pg_seo->set_seo_data($seo_settings);
        }

        $this->view->assign('page_data', $page_data);
        $this->view->assign('data', $data);
        $this->view->assign('seodata', $data);
        $this->view->assign('profile_section', $profile_section);
        $this->view->assign('subsection', $subsection);
        $this->view->assign('user_id', $user_id);
        $this->view->render('view');
    }

    public function my_guests($period = 'all', $page = 1)
    {
        $this->load->model('Menu_model');
        $this->Menu_model->breadcrumbs_set_parent('user-menu-people');
        $this->Menu_model->breadcrumbs_set_active(l('header_my_guests', 'users'));
        $this->views($period, 'my_guests', $page);

        return;
    }

    public function my_visits($period = 'all', $page = 1)
    {
        $this->load->model('Menu_model');
        $this->Menu_model->breadcrumbs_set_parent('user-menu-people');
        $this->Menu_model->breadcrumbs_set_active(l('header_my_visits', 'users'));
        $this->views($period, 'my_visits', $page);

        return;
    }

    private function views($period = 'all', $type = 'my_guests', $page = 1)
    {
        if (!in_array($period, array('today', 'week', 'month', 'all'))) {
            $period = 'all';
        }
        $this->load->model('users/models/Users_views_model');
        $this->load->model('users/models/Users_deleted_model');

        $criteria = array();

        $order_by['view_date'] = 'DESC';
        if ($type == 'my_guests') {
            $all_viewers = $this->Users_views_model->get_viewers_daily_unique($this->user_id, null, null, $order_by, array(), $period);
            $this->Users_views_model->remove_viewers_counter($all_viewers);
        } else {
            $all_viewers = $this->Users_views_model->get_views_daily_unique($this->user_id, null, null, $order_by, array(), $period);
        }
        $need_ids   = $view_dates = array();
        $key        = ($type == 'my_guests') ? 'id_viewer' : 'id_user';
        foreach ($all_viewers as $viewer) {
            $need_ids[]                = $viewer[$key];
            $view_dates[$viewer[$key]] = $viewer['view_date'];
        }

        $items_count = $need_ids ? count($need_ids) : 0;
        $page        = intval($page);
        if (!$page) {
            $page = 1;
        }
        $items_on_page = $this->pg_module->get_module_config('users', 'items_per_page');
        $this->load->helper('sort_order');
        $this->load->helper("navigation");
        $page          = get_exists_page_number($page, $items_count, $items_on_page);
        $url           = site_url() . "users/{$type}/{$period}/";
        $page_data     = get_user_pages_data($url, $items_count, $items_on_page, $page, 'briefPage');

        if ($items_count) {
            $users_list = $this->Users_model->get_users_list_by_key($page, $items_on_page, $order_by, $criteria, $need_ids);
            $users      = array();
            foreach ($need_ids as $uid) {
                if (isset($users_list[$uid]['id'])) {
                    $users[$uid] = $users_list[$uid];
                } else {
                    $default = $this->Users_model->format_default_user($uid);
                    $deleted = $this->Users_deleted_model->get_user_by_user_id($uid, true);
                    $users[$uid] = array_merge($default, $deleted);
                }
            }
            $this->view->assign('users', $users);
            $this->view->assign('view_dates', $view_dates);
        }
        $this->view->assign('views_type', $type);
        $this->view->assign('period', $period);
        $page_data['date_format']      = $this->pg_date->get_format('date_literal', 'st');
        $page_data["date_time_format"] = $this->pg_date->get_format('date_time_literal', 'st');
        $this->view->assign('page_data', $page_data);
        $this->view->assign('page', $page);
        $this->view->render('visits');
    }

    /* USERS SERVICES */

    public function ajax_available_user_activate_in_search()
    {
        $return = array('available' => 0, 'content' => '', 'display_login' => 0);
        if ($this->session->userdata('auth_type') != 'user') {
            $return['display_login'] = 1;
        } else {
            $return = $this->Users_model->service_available_user_activate_in_search_action($this->user_id);
            if ($return["content_buy_block"] == true) {
                $this->load->model('services/models/Services_users_model');
                $return["content"] = $this->Services_users_model->available_service_block($this->user_id, 'user_activate_in_search_template');
                $this->load->helper('seo');
                $this->session->set_userdata(array('service_redirect' => rewrite_link('users', 'profile')));
            }
        }
        $this->view->assign($return);

        return;
    }

    public function ajax_activate_user_activate_in_search($id_user_service)
    {
        $return = $this->Users_model->service_activate_user_activate_in_search($this->user_id, $id_user_service);
        $this->view->assign($return);

        return;
    }

    /**
     * The method checks the availability of featured user.
     *
     * @param int $id_user
     */
    public function ajax_available_users_featured()
    {
        $return = array('available' => 0, 'content' => '', 'display_login' => 0);
        if ($this->session->userdata('auth_type') != 'user') {
            $return['display_login'] = 1;
        } else {
            $return = $this->Users_model->service_available_users_featured_action($this->user_id);
            if ($return["content_buy_block"] == true) {
                $this->load->model('services/models/Services_users_model');
                $return["content"] = $this->Services_users_model->available_service_block($this->user_id, 'users_featured_template');
            }
        }
        $this->view->assign($return);

        return;
    }

    public function ajax_activate_users_featured($id_user_service)
    {
        $return = $this->Users_model->service_activate_users_featured($this->user_id, $id_user_service);
        $this->view->assign($return);

        return;
    }

    /**
     * The method checks the availability of approve user.
     *
     * @param int $id_user
     */
    public function ajax_available_admin_approve()
    {
        $return = array('available' => 0, 'content' => '', 'display_login' => 0);
        if ($this->session->userdata('auth_type') != 'user') {
            $return['display_login'] = 1;
        } else {
            $return = $this->Users_model->service_available_admin_approve_action($this->user_id);
            if ($return["content_buy_block"] == true) {
                $this->load->model('services/models/Services_users_model');
                $return["content"] = $this->Services_users_model->available_service_block($this->user_id, 'admin_approve_template');
            }
        }
        $this->view->assign($return);

        return;
    }

    public function ajax_activate_admin_approve($id_user_service)
    {
        $return = $this->Users_model->service_activate_admin_approve($this->user_id, $id_user_service);
        $this->view->assign($return);

        return;
    }

    /**
     * The method checks the availability of hide user on site.
     *
     * @param int $id_user
     */
    public function ajax_available_hide_on_site()
    {
        $return = array('available' => 0, 'content' => '', 'display_login' => 0);
        if ($this->session->userdata('auth_type') != 'user') {
            $return['display_login'] = 1;
        } else {
            $return = $this->Users_model->service_available_hide_on_site_action($this->user_id);
            if ($return["content_buy_block"] == true) {
                $this->load->model('services/models/Services_users_model');
                $return["content"] = $this->Services_users_model->available_service_block($this->user_id, 'hide_on_site_template');
            }
        }
        $this->view->assign($return);

        return;
    }

    public function ajax_activate_hide_on_site($id_user_service)
    {
        $return = $this->Users_model->service_activate_hide_on_site($this->user_id, $id_user_service);
        $this->view->assign($return);

        return;
    }

    /**
     * The method checks the availability of highlight user in search.
     *
     * @param int $id_user
     */
    public function ajax_available_highlight_in_search()
    {
        $return = array('available' => 0, 'content' => '', 'display_login' => 0);
        if ($this->session->userdata('auth_type') != 'user') {
            $return['display_login'] = 1;
        } else {
            $return = $this->Users_model->service_available_highlight_in_search_action($this->user_id);
            if ($return["content_buy_block"] == true) {
                $this->load->model('services/models/Services_users_model');
                $return["content"] = $this->Services_users_model->available_service_block($this->user_id, 'highlight_in_search_template');
            }
        }
        $this->view->assign($return);

        return;
    }

    public function ajax_activate_highlight_in_search($id_user_service)
    {
        $return = $this->Users_model->service_activate_highlight_in_search($this->user_id, $id_user_service);
        $this->view->assign($return);

        return;
    }

    /**
     * The method checks the availability of up user in search.
     *
     * @param int $id_user
     */
    public function ajax_available_up_in_search()
    {
        $return = array('available' => 0, 'content' => '', 'display_login' => 0);
        if ($this->session->userdata('auth_type') != 'user') {
            $return['display_login'] = 1;
        } else {
            $return = $this->Users_model->service_available_up_in_search_action($this->user_id);
            if ($return["content_buy_block"] == true) {
                $this->load->model('services/models/Services_users_model');
                $return["content"] = $this->Services_users_model->available_service_block($this->user_id, 'up_in_search_template');
            }
        }
        $this->view->assign($return);

        return;
    }

    public function ajax_activate_up_in_search($id_user_service)
    {
        $return = $this->Users_model->service_activate_up_in_search($this->user_id, $id_user_service);
        $this->view->assign($return);

        return;
    }

    /**
     * The method checks the availability of up user in search.
     *
     * @param int $id_user
     */
    public function ajax_available_ability_delete()
    {
        $return = array('available' => 0, 'content' => '', 'content_buy_block' => false, 'display_login' => 0);
        if ($this->session->userdata('auth_type') != 'user') {
            $return['display_login'] = 1;
        } else {
            $return = $this->Users_model->service_available_ability_delete_action($this->user_id);
        }
        $this->view->assign($return);

        return;
    }

    public function ajax_activate_ability_delete($id_user_service)
    {
        $return = $this->Users_model->service_activate_ability_delete($this->user_id, $id_user_service, 1);
        $this->view->assign($return);

        return;
    }

    public function account_delete()
    {
        if ($this->pg_module->is_module_installed('services')) {
            $this->load->model('services/models/Services_users_model');
            $service_access = $this->Services_users_model->is_service_access($this->user_id, 'ability_delete_template');
            if ($service_access['service_status'] && !$service_access['activate_status']) {
                show_404();
            }
        }
        if ($this->input->post('btn_delete')) {
            $this->Users_model->delete_user($this->user_id);
            $this->load->model("users/models/Auth_model");
            $this->Auth_model->logoff();
            redirect();
        } else {
            // breadcrumbs
            $this->load->model('Menu_model');
            $this->Menu_model->breadcrumbs_set_parent('settings-item');
            $this->Menu_model->breadcrumbs_set_active(l('seo_tags_account_delete_header', 'users'));
            $this->view->render('account_delete_block');
        }
    }

    public function ajax_load_avatar()
    {
        $result           = array('status' => 1, 'errors' => array(), 'msg' => array(), 'data' => array());
        $id_user          = $this->input->post('id_user') ? intval($this->input->post('id_user', true)) : $this->user_id;
        $data['user']     = $this->Users_model->format_user($this->Users_model->get_user_by_id($id_user));
        $data['is_owner'] = ($id_user == $this->user_id);
        if (!$id_user || !$data['user'] || (!$data['is_owner'] && !($data['user']['user_logo'] || $data['user']['user_logo_moderation']))) {
            $result['status']   = 0;
            $result['errors'][] = l('error_access_denied', 'users');
            $this->view->assign($result);

            return;
        }
        $data['have_avatar'] = ($data['user']['user_logo'] || $data['user']['user_logo_moderation']);
        if ($data['is_owner']) {
            $this->load->model('uploads/models/Uploads_config_model');
            $data['upload_config'] = $this->Uploads_model->get_config($this->Users_model->upload_config_id);
            $data['selections']    = array();
            foreach ($data['upload_config']['thumbs'] as $thumb_config) {
                $data['selections'][$thumb_config['prefix']] = array(
                    'width'  => $thumb_config['width'],
                    'height' => $thumb_config['height'],
                );
            }
        }

        $this->view->assign('avatar_data', $data);
        $result['data']['html'] = $this->view->fetchFinal('ajax_user_avatar');
        if (isset($data['selections'])) {
            $result['data']['selections'] = $data['selections'];
        }
        $this->view->assign($result);

        return;
    }

    public function ajax_recrop_avatar()
    {
        $result = array('status' => 1, 'errors' => array(), 'msg' => array(), 'data' => array());
        $user   = $this->Users_model->format_user($this->Users_model->get_user_by_id($this->user_id));
        if (!$user || !($user['user_logo'] || $user['user_logo_moderation'])) {
            $result['status']   = 0;
            $result['errors'][] = l('error_access_denied', 'users');
            $this->view->assign($result);

            return;
        }

        $logo_name                 = $user['user_logo_moderation'] ? 'user_logo_moderation' : 'user_logo';
        $recrop_data['x1']         = $this->input->post('x1', true);
        $recrop_data['y1']         = $this->input->post('y1', true);
        $recrop_data['width']      = $this->input->post('width', true);
        $recrop_data['height']     = $this->input->post('height', true);
        $thumb_prefix              = trim(strip_tags($this->input->post('prefix', true)));
        $this->load->model('Uploads_model');
        $this->Uploads_model->recrop_upload($this->Users_model->upload_config_id, $this->user_id, $user[$logo_name], $recrop_data, $thumb_prefix);
        $result['data']['img_url'] = $user['media'][$logo_name]['thumbs'][$thumb_prefix];
        $result['data']['rand']    = rand(0, 999999);
        $result['msg'][]           = l('photo_successfully_saved', 'users');

        $this->view->assign($result);

        return;

        return;
    }

    public function upload_avatar()
    {
        $return        = array('errors' => array(), 'warnings' => array(), 'name' => '', 'logo' => array(), 'old_logo' => array());
        $validate_data = $this->Users_model->validate($this->user_id, array(), 'avatar');
        if (!empty($validate_data['errors'])) {
            $return['errors'] = $validate_data['errors'];
        } else {
            $data                          = $this->Users_model->format_user($this->Users_model->get_user_by_id($this->user_id));
            $return['old_logo']            = $data['media']['user_logo'];
            $return['old_logo_moderation'] = $data['media']['user_logo_moderation'];
            if ($this->input->post('user_icon_delete') || (isset($_FILES['avatar']) && is_array($_FILES['avatar']) && is_uploaded_file($_FILES['avatar']['tmp_name']))) {
                $this->load->model('Uploads_model');
                if ($data['user_logo_moderation']) {
                    $this->Uploads_model->delete_upload($this->Users_model->upload_config_id, $this->user_id . '/', $data['user_logo_moderation']);
                    $validate_data['data']['user_logo_moderation'] = '';
                    $this->load->model('Moderation_model');
                    $this->Moderation_model->delete_moderation_item_by_obj($this->Users_model->moderation_type, $this->user_id);
                } elseif ($data['user_logo']) {
                    $this->Uploads_model->delete_upload($this->Users_model->upload_config_id, $this->user_id . '/', $data['user_logo']);
                    $validate_data['data']['user_logo'] = '';
                }
            }
            $this->Users_model->save_user($this->user_id, $validate_data['data'], 'avatar');
            $this->Users_model->service_available_user_activate_in_search_action($this->user_id);
            $data           = $this->Users_model->format_user($this->Users_model->get_user_by_id($this->user_id));
            if($data['user_logo_moderation']) {
                $return['warnings'][] = l('file_uploaded_and_moderated', 'media');
            }
            $return['logo'] = $data['user_logo_moderation'] ? $data['media']['user_logo_moderation'] : $data['media']['user_logo'];

            $this->load->model('users/models/Auth_model');
            $this->Auth_model->update_user_session_data($this->user_id);
        }

        $this->view->assign($return);
        $this->view->render(null);

        return;
    }

    public function untitled()
    {
        $data['img'] = site_url() . "uploads/default/midle-default-user-logo-deleted.png";
        $this->view->assign('data', $data);

        $this->load->model('Menu_model');
        $this->Menu_model->breadcrumbs_set_active(l('user_deleted', 'users'));
        $this->view->render('profile_deleted');
    }

    public function ajax_refresh_active_users()
    {
        $attrs["where_sql"][] = " id!='" . $this->session->userdata("user_id") . "'";
        $params['count']      = intval($this->input->post('count', true));
        $this->load->model('Properties_model');
        $user_types           = $this->Properties_model->get_property('user_type');
        $this->view->assign('user_types', $user_types["option"]);

        $filter_user_type = $this->input->post('user_type');
        if (!empty($filter_user_type)) {
            foreach ($user_types["option"] as $key => $value) {
                if ($key == $filter_user_type) {
                    $attrs["where_sql"][] = " user_type='" . $key . "'";
                    $this->view->assign('active_user_type', $key);
                }
            }
        }

        $data['users'] = $this->Users_model->get_active_users($params['count'], 0, $attrs);

        if (!empty($data['users'])) {
            $users_count = 16 - count($data['users']);
            switch ($users_count) {
                case 13: $recent_thumb['name'] = 'middle';
                    $recent_thumb['width'] = '82px';
                    break;
                case 14: $recent_thumb['name'] = 'big';
                    $recent_thumb['width'] = '125px';
                    break;
                case 15: $recent_thumb['name'] = 'great';
                    $recent_thumb['width'] = '255px';
                    break;
                default: $recent_thumb['name'] = 'small';
                    $recent_thumb['width'] = '60px';
            }
            $this->view->assign('recent_thumb', $recent_thumb);
            $this->view->assign('active_users_block_data', $data);
            exit($this->view->fetch('helper_active_users_block', 'user', 'users'));
        }

        return false;
    }

    public function ajax_refresh_last_registered_users()
    {
        $attrs["where_sql"][] = " id!='" . $this->session->userdata("user_id") . "'";
        $params['count']      = intval($this->input->post('count', true));
        $this->load->model('Properties_model');
        $user_types           = $this->Properties_model->get_property('user_type');
        $this->view->assign('user_types', $user_types["option"]);

        $filter_user_type = $this->input->post('user_type');
        if (!empty($filter_user_type)) {
            foreach ($user_types["option"] as $key => $value) {
                if ($key == $filter_user_type) {
                    $attrs["where_sql"][] = " user_type='" . $key . "'";
                    $this->view->assign('active_user_type', $key);
                }
            }
        }

        $attrs['order_by'] = array('field'     => 'date_created',
            'direction'                        => 'DESC', );

        $data['users'] = $this->Users_model->get_active_users($params['count'], 0, $attrs);

        if (!empty($data['users'])) {
            $users_count = 16 - count($data['users']);
            switch ($users_count) {
                case 13: $recent_thumb['name'] = 'middle';
                    $recent_thumb['width'] = '82px';
                    break;
                case 14: $recent_thumb['name'] = 'big';
                    $recent_thumb['width'] = '125px';
                    break;
                case 15: $recent_thumb['name'] = 'great';
                    $recent_thumb['width'] = '255px';
                    break;
                default: $recent_thumb['name'] = 'small';
                    $recent_thumb['width'] = '60px';
            }
            $this->view->assign('recent_thumb', $recent_thumb);
            $this->view->assign('active_users_block_data', $data);
            exit($this->view->fetch('helper_last_registered', 'user', 'users'));
        }

        return false;
    }

    public function ajax_refresh_online_users()
    {
        $attrs["where_sql"][] = " id!='" . $this->session->userdata("user_id") . "'";
        $params['count']      = intval($this->input->post('count', true));
        $this->load->model('Properties_model');
        $user_types           = $this->Properties_model->get_property('user_type');
        $this->view->assign('user_types', $user_types["option"]);

        $filter_user_type = $this->input->post('user_type');
        if (!empty($filter_user_type)) {
            foreach ($user_types["option"] as $key => $value) {
                if ($key == $filter_user_type) {
                    $attrs["where_sql"][] = " user_type='" . $key . "'";
                    $this->view->assign('active_user_type', $key);
                }
            }
        }

        $data['users'] = $this->Users_model->get_online_users($params['count'], 0, $attrs);

        if (!empty($data['users'])) {
            $users_count = 16 - count($data['users']);
            switch ($users_count) {
                case 13: $recent_thumb['name'] = 'middle';
                    $recent_thumb['width'] = '82px';
                    break;
                case 14: $recent_thumb['name'] = 'big';
                    $recent_thumb['width'] = '125px';
                    break;
                case 15: $recent_thumb['name'] = 'great';
                    $recent_thumb['width'] = '255px';
                    break;
                default: $recent_thumb['name'] = 'small';
                    $recent_thumb['width'] = '60px';
            }
            $this->view->assign('recent_thumb', $recent_thumb);
            $this->view->assign('active_users_block_data', $data);
            exit($this->view->fetch('helper_online', 'user', 'users'));
        }

        return false;
    }

    /**
     * If user not approve
     *
     * @return void
     * */
    public function blocked()
    {
        if ($this->session->userdata('auth_type') == 'user') {
            redirect(site_url() . 'start/homepage');
        }

        $module = 'contact_us';
        if ($this->pg_module->is_module_installed('tickets')) {
            $module = 'tickets';
        }

        $this->view->assign('module', $module);
        $this->view->render('user_blocked');
    }

    public function inactive()
    {
        if ($this->session->userdata('auth_type') == 'user') {
            redirect(site_url() . 'start/homepage');
        }

        $module = 'contact_us';
        if ($this->pg_module->is_module_installed('tickets')) {
            $module = 'tickets';
        }
        $this->view->assign('module', $module);
        $this->view->render('user_inactive');
    }

    public function set_view_mode($view_mode)
    {
        if (in_array($view_mode, array('list', 'gallery'))) {
            $_SESSION['search_view_mode'] = $view_mode;
        }
    }

    public function prevent_view()
    {
        $data['available_activation'] = $this->Users_model->check_available_user_activation($this->user_id);
        $service_status               = $this->Users_model->service_available_user_activate_in_search_action($this->user_id);

        if (in_array('user_logo', $data['available_activation']['fields']) || in_array('id_region', $data['available_activation']['fields']) || in_array('id_country', $data['available_activation']['fields'])) {
            if (in_array('user_logo', $data['available_activation']['fields'])) {
                $this->view->assign('user_logo_button', '1');
            }
            if (in_array('id_region', $data['available_activation']['fields']) || in_array('id_country', $data['available_activation']['fields'])) {
                $this->view->assign('user_location_button', '1');
            }
        } else {
            if ($service_status["content_buy_block"] == true) {
                $this->view->assign('user_service_button', '1');
            }
        }

        exit($this->view->fetch('prevent_view', 'user', 'users'));
    }

    public function getChangeLocationForm()
    {
        if ($this->user_id) {
            $user = $this->Users_model->get_user_by_id($this->user_id, true);
            $this->view->assign('user', $user);
            $this->view->output($this->view->fetchFinal('change_location_form'));
            $this->view->render();
        }
    }

    public function setChangeLocationForm()
    {
        if ($this->user_id) {
            $post_data = array(
                'id_country' => $this->input->post('id_country', true),
                'id_region' => $this->input->post('id_region', true),
                'id_city' => $this->input->post('id_city', true),
                'lat' => $this->input->post('lat', true),
                'lon' => $this->input->post('lon', true),
            );
            $validate_data = $this->Users_model->validate($this->user_id, $post_data);
            if (empty($validate_data['errors'])) {
                $this->Users_model->save_user($this->user_id, $validate_data['data']);
                $validate_data['success'] = l('success_update_user', 'users');
            }
            exit(json_encode($validate_data));
        }
    }

    /**
     *  Clear cookies
     *
     *  @return void
     */
    private function clearCookies($cookie)
    {
        setcookie(
            $cookie,
            '',
            (time() - 31500000),
            '/' . SITE_SUBFOLDER,
            COOKIE_SITE_SERVER,
            0
        );
    }

    /**
     *  Get user site status
     *
     *  @return void
     */
    public function getAvailableActivation()
    {
        $return = [];
        if ($this->user_id) {
            $this->load->helper('cookie');
            $cookie = get_cookie('available_activation', true);
            if ($cookie) {
                $cookie = array(
                    'name'         => 'available_activation',
                    'value'        => '1',
                    'expire'       => 3600,
                    'domain'       => COOKIE_SITE_SERVER,
                    'path'         => '/' . SITE_SUBFOLDER,
                );
                set_cookie($cookie);
            } else {
                $available_activation = $this->Users_model->check_available_user_activation($this->user_id);
                if (!$available_activation['status']) {
                    if (in_array('confirm', $available_activation['fields'])) {
                        $return[] = l('user_check_activation_subject', 'users');
                        $return[] = l('user_check_activation_body', 'users');
                    } else {
                        $return[] = l('text_inactive_in_search', 'users');
                        $return[] = l('text_need_for_activation', 'users');
                        foreach ($available_activation['fields'] as $field) {
                            if ($field == 'user_logo_moderation') {
                                $return[] = l('wait_image_approve', 'users');
                            } elseif ($field == 'user_logo') {
                                $return[] = l('upload_photo', 'users');
                            } else {
                                $return[] = l('fill_field', 'users') . ":&nbsp;" . l('field_' . $field, 'users');
                            }
                        }
                    }
                }
            }
        }
        exit(json_encode($return));
    }

    public function ajaxUserSiteVisit()
    {
        $user_id = intval($this->input->post('user_id', true));
        if ($user_id) {
            $this->Users_model->setVisit($user_id);
        }
    }

    public function ajaxRegValidation()
    {
        $field_name = $this->input->post('field_name', true);
        $field_value = $this->input->post('field_value', true);

        if (!empty($field_name) && !empty($field_value)) {
            $post_data[$field_name] = $field_value;
        }

        $validate_data = $this->Users_model->validate(null, $post_data, 'user_icon');

        if (!empty($validate_data['errors'][$field_name])) {
            $return['error'][$field_name] = $validate_data['errors'][$field_name];
        } elseif (!empty($validate_data['data'][$field_name])) {
            $return['data'][$field_name] = $validate_data['data'][$field_name];
        } else {
            $return = array();
        }

        exit(json_encode($return));
    }

    public function prompt($step)
    {
        $user = $this->Users_model->get_user_by_id($this->user_id, true);

        if ($step == 1 && $user['user_logo']) {
            $this->view->render();
        } else {
            $this->view->assign('user', $user);

            $this->view->render('prompt_step_' . $step);
        }
    }

    public function ajax_save_avatar($user_id)
    {
        $validate_data = $this->Users_model->validate($user_id, [], 'user_logo');
        if (!empty($validate_data['errors'])) {
            $this->system_messages->addMessage(View::MSG_ERROR, $validate_data['errors']);
        } else {
            $this->Users_model->save_user($user_id, [], 'user_logo');
            $this->system_messages->addMessage(View::MSG_SUCCESS, 'Success');
        }

        $this->view->render();
    }

    public function ajax_save_field($user_id)
    {
        $user_data = $this->input->post('user', true);

        if (!empty($user_data)) {
            $validate = $this->Users_model->validate($user_id, $user_data);
            if (!empty($validate['errors'])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate['errors']);
            } else {
                $this->Users_model->save_user($user_id, $validate['data']);
                $this->system_messages->addMessage(View::MSG_SUCCESS, 'Success');
            }
        }

        $perfect_match = $this->input->post('pm', true);

        if (!empty($perfect_match)) {
            $this->load->model("Perfect_match_model");
            $validate = $this->Perfect_match_model->validate($user_id, $perfect_match);
            if (!empty($validate['errors'])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate['errors']);
            } else {
                $this->Perfect_match_model->save_user($user_id, $validate['data']);
                $this->system_messages->addMessage(View::MSG_SUCCESS, 'Success');
            }
        }

        $this->view->assign('user', $this->Users_model->get_user_by_id($user_id, true));

        $this->view->render();
    }

    public function verifications()
    {
        $this->view->render('verifications');
    }

    public function verificationSave()
    {
        $this->load->model('users/models/Verifications_model');

        $this->view->assign('errors', []);

        $result = $this->Verifications_model->save(null, 'avatar');
        if (!empty($result['errors'])) {
            $this->system_messages->addMessage(View::MSG_ERROR, $result['errors']);
        } else {
            $this->system_messages->addMessage(View::MSG_SUCCESS, 'Success');
        }

        $this->view->render();
    }

    public function searches($page = 1)
    {
        $this->load->model('users/models/Saved_search_model');

        $user_id = $this->session->userdata('user_id');
        $user_type = $this->session->userdata('user_type');

        $items_on_page = $this->pg_module->get_module_config('start', 'index_items_per_page');

        $params['user'] = $user_id;

        $searches_count = $this->Saved_search_model->getSearchesCount($params);
        if ($searches_count) {
            $searches = $this->Saved_search_model->getSearchesList($params, $page, $items_on_page, array('id' => 'DESC'));
            $this->view->assign('searches', $searches);
        }

        $this->load->helper('navigation');
        $url = site_url() . 'users/searches/';
        $page_data = get_user_pages_data($url, $searches_count, $items_on_page, $page, 'briefPage');
        $this->view->assign('page_data', $page_data);

        $this->view->render('searches');
    }

    public function load_saved_search($search_id)
    {
        $user_id = $this->session->userdata('user_id');

        $this->load->model('users/models/Saved_search_model');

        $search = $this->Saved_search_model->getSearchById($search_id);
        if ($search['id_user'] != $user_id) {
            show_404();
            return;
        }

        $current_settings = isset($_SESSION['listings_search']) ? $_SESSION['listings_search'] : array();
        $current_settings['data'] = $search['search_data'];

        $this->session->set_userdata("users_search", $search['search_data']);

        $this->load->helper('seo');

        $search_link = rewrite_link('users', 'advanced_search');

        $this->view->setRedirect($search_link);
    }

    public function delete_saved_search($search_id)
    {
        $this->load->model('users/models/Saved_search_model');
        $user_id = $this->session->userdata('user_id');
        $search = $this->Saved_search_model->getSearchById($search_id);
        if ($search['id_user'] != $user_id) {
            show_404();
            return;
        }
        $this->Saved_search_model->deleteSearch($search_id);
        $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_delete_search', 'users'));
        $this->view->setRedirect(site_url() . 'users/searches');
    }
}
