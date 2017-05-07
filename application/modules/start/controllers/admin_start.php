<?php

namespace Pg\Modules\Start\Controllers;

use Pg\Libraries\View;
use Pg\Libraries\Acl\Action\Admin as AdminAction;
use Pg\Libraries\Acl\Resource\Admin as AdminResource;
use Pg\Libraries\Acl\Handler\NoGuestAccess as NoGuestAccessHandler;
use Pg\Libraries\Acl\Handler\NoUserAccess as NoUserAccessHandler;

/**
 * Start admin side controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category    modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * */
class Admin_start extends \Controller
{
    /**
     * link to CodeIgniter object
     *
     * @var object
     */
    private $numerics_settings = array(
        "start" => array(
            'admin_items_per_page',
            'index_items_per_page',
        ),
        "mailbox" => array(
            'items_per_page',
            'attach_limit',
        ),
        "news" => array(
            'userside_items_per_page',
        ),
        "payments" => array(
            'items_per_page',
        ),
        'users' => array(
            'items_per_page',
        ),
        'wall_events' => array(
            'items_per_page',
        ),
        'media' => array(
            'items_per_page',
        ),
        'banners' => array(
            'items_per_page',
        ),
    );
    private $other_settings = array(
        "countries" => array(
            array('var' => 'output_country_format', 'type' => 'text'),
            array('var' => 'output_region_format', 'type' => 'text'),
            array('var' => 'output_city_format', 'type' => 'text'),
        ),
        /* 'users' => array(
          array('var' => 'user_approve', 'type' => 'select', 'values' => array(0 => 'no', 1 => 'admin', 2 => 'service')),
          array('var' => 'user_confirm', 'type' => 'checkbox'),
          array('var' => 'hide_user_names', 'type' => 'checkbox'),
          array('var' => 'age_min', 'type' => 'text'),
          array('var' => 'age_max', 'type' => 'text')
          ), */
    );
    private $date_formats = array(
        'date_literal',
        'date_time_numeric',
        'date_time_literal',
    );
    private $date_formats_pages = array(
        'date_literal' => array(
            'users/profile/',
            'users/my_visits/',
            'users/my_guests/',
            'users/account/services/',
            'users/view/',
            'users/my_guests/',
            'users/my_visits/',
        ),
        'date_time_numeric' => array(
            'admin/moderation',
            'admin/cronjob/index/',
            'admin/cronjob/log/',
            'admin/mail_list/filters/',
            'admin/polls/index/',
            'admin/polls/results/',
            'admin/banners/index/',
        ),
        'date_time_literal' => array(
            'admin/reviews/index/',
            'admin/reviews/types/',
            'admin/ausers/index/',
            'admin/users/index/',
            'admin/payments/index/',
            'admin/menu/index/',
            'admin/notifications/index/',
            'admin/notifications/templates/',
            'admin/news/index/',
            'admin/news/feeds/',
            'admin/mail_list/users/',
            'admin/seo/robots/',
            'mailbox/index/ (mailbox)',
            'start/homepage/ (wall events)',
            'users/profile/wall/ (wall events)',
            'users/view/ (wall events)',
            'friendlist/',
            'users/my_guests/',
            'users/my_visits/',
            'users/account/services/',
            'payments/statistic',
            'news/index/',
            'news/view/',
            'im',
            'comments',
            'media',
        ),
    );

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $settings_for_modules = array_unique(array_merge(array_keys($this->numerics_settings), array_keys($this->other_settings)));
        foreach ($settings_for_modules as $module) {
            if (!$this->pg_module->is_module_installed($module)) {
                unset($this->numerics_settings[$module], $this->other_settings[$module]);
            }
        }
    }

    public function index()
    {
        if ($this->session->userdata("auth_type") != 'admin') {
            $this->session->set_userdata('demo_user_type', 'admin');
            redirect(site_url() . "admin/ausers/login");
        }

        $theme = $this->pg_theme->return_active_settings('user');
        $this->view->assign('scheme', $theme['scheme_data']);

        if ($this->pg_module->is_module_active('statistics')) {
            $this->load->model('Statistics_model');

            $statistics = [];

            // users
            $users_stats = $this->Statistics_model->getStatPoints('users', null, [
                'registered_total', 'registered_day_1', 'registered_day_2', 'registered_day_3',
                'registered_day_4', 'registered_day_5', 'registered_day_6',
                'registered_day_7', 'registered_day_8', 'registered_day_9', 'registered_day_10',
                'registered_day_11', 'registered_day_12', 'registered_day_13', 'registered_day_14']);
            if ($users_stats !== false) {
                $week_1 = $users_stats['registered_day_1'] + $users_stats['registered_day_2'] +
                          $users_stats['registered_day_3'] + $users_stats['registered_day_4'] +
                          $users_stats['registered_day_5'] + $users_stats['registered_day_6'] +
                          $users_stats['registered_day_7'];

                $week_2 = $users_stats['registered_day_8'] + $users_stats['registered_day_9'] +
                          $users_stats['registered_day_10'] + $users_stats['registered_day_11'] +
                          $users_stats['registered_day_12'] + $users_stats['registered_day_13'] +
                          $users_stats['registered_day_14'];

                $statistics['registered'] = [
                    'week_1' => $week_1,
                    'week_2' => $week_2,
                    'week_percent' => round(($week_2 > 0 ? ($week_1 - $week_2)/$week_2 : $week_1) * 100, 2),
                ];
            }

            // payments
            $payments_stats = $this->Statistics_model->getStatPoints('payments', null, [
                'amount_total', 'amount_day_1', 'amount_day_2', 'amount_day_3',
                'amount_day_4', 'amount_day_5', 'amount_day_6', 'amount_day_7',
                'amount_day_8', 'amount_day_9', 'amount_day_10', 'amount_day_11',
                'amount_day_12', 'amount_day_13', 'amount_day_14', 'transactions_day_1',
                'transactions_day_2', 'transactions_day_3', 'transactions_day_4',
                'transactions_day_5', 'transactions_day_6', 'transactions_day_7',
                'transactions_day_8', 'transactions_day_9', 'transactions_day_10',
                'transactions_day_11', 'transactions_day_12', 'transactions_day_13',
                'transactions_day_14']);
            if ($payments_stats !== false) {
                $amount_week_1 = $payments_stats['amount_day_1'] + $payments_stats['amount_day_2'] +
                                 $payments_stats['amount_day_3'] + $payments_stats['amount_day_4'] +
                                 $payments_stats['amount_day_5'] + $payments_stats['amount_day_6'] +
                                 $payments_stats['amount_day_7'];

                $amount_week_2 = $payments_stats['amount_day_8'] + $payments_stats['amount_day_9'] +
                                 $payments_stats['amount_day_10'] + $payments_stats['amount_day_11'] +
                                 $payments_stats['amount_day_12'] + $payments_stats['amount_day_13'] +
                                 $payments_stats['amount_day_14'];

                $this->load->helper('start');

                $this->view->assign('payment_total', currency_format_output(['value' => $payments_stats['amount_total'] ?: 0]));

                $statistics['payments'] = [
                    'week_1' => currency_format_output(['value' => $amount_week_1]),
                    'week_2' => currency_format_output(['value' => $amount_week_2]),
                    'week_percent' => round(($amount_week_2 > 0 ? ($amount_week_1 - $amount_week_2)/$amount_week_2 : $amount_week_1) * 100, 2),
                ];

                $transactions_week_1 =
                    $payments_stats['transactions_day_1'] + $payments_stats['transactions_day_2'] +
                    $payments_stats['transactions_day_3'] + $payments_stats['transactions_day_4'] +
                    $payments_stats['transactions_day_5'] + $payments_stats['transactions_day_6'] +
                    $payments_stats['transactions_day_7'];

                $transactions_week_2 =
                    $payments_stats['transactions_day_8'] + $payments_stats['transactions_day_9'] +
                    $payments_stats['transactions_day_10'] + $payments_stats['transactions_day_11'] +
                    $payments_stats['transactions_day_12'] + $payments_stats['transactions_day_13'] +
                    $payments_stats['transactions_day_14'];

                $payment_avg_1 = $transactions_week_1 > 0 ? $amount_week_1/$transactions_week_1 : 0;
                $payment_avg_2 = $transactions_week_2 > 0 ? $amount_week_2/$transactions_week_2 : 0;

                $statistics['payments_avg'] = [
                    'week_1' => currency_format_output(['value' => $payment_avg_1]),
                    'week_2' => currency_format_output(['value' => $payment_avg_2]),
                    'week_percent' => round(($payment_avg_2 > 0 ? ($payment_avg_1 - $payment_avg_2)/$payment_avg_2 : $payment_avg_1) * 100, 2),
                ];
            }

            $this->view->assign('statistics', $statistics);
        } else {
            $this->view->assign('statistics', false);
        }

        $ql_modules_available = [
            ['name' => 'users', 'options' => []],
            ['name' => 'spam', 'options' => []],
            ['name' => 'media', 'options' => []],
            ['name' => 'comments', 'options' => []],
            ['name' => 'banners', 'options' => []],
            ['name' => 'tickets', 'options' => []],
            ['name' => 'subscriptions', 'options' => []],
            ['name' => 'payments', 'options' => []],
            ['name' => 'news', 'options' => []],
            ['name' => 'languages', 'options' => []],
            ['name' => 'themes', 'options' => [
                'theme' => $theme['scheme_data']['id_theme'],
                'scheme' => $theme['scheme_data']['id']]],
        ];

        $ql_modules = [];

        foreach ($ql_modules_available as $i => $ql_module) {
            if ($this->pg_module->is_module_active($ql_module['name'])) {
                $ql_modules[] = $ql_module;
            }
        }

        $ql_modules[] = ['name' => 'start', 'options' => []];

        $this->view->assign('ql_modules', $ql_modules);

        $this->view->setHeader(l("header_admin_homepage", 'start'));
        if ($this->session->userdata("user_type") == "moderator") {
            $this->view->render('index_moderator');
        } else {
            $this->view->render('index');
        }
    }

    public function error($error_type = '', $request_method = '')
    {
        if ($request_method == 'ajax') {
            $this->view->assign('ajax', '1');
        }
        $this->view->render('error');
    }

    public function mod_login()
    {
        $this->load->model('Menu_model');
        $this->Menu_model->set_menu_active_item('admin_menu', 'add_ons_items');
        
        $data["action"] = site_url() . "admin/install/login";
        $this->view->assign("data", $data);

        $this->view->setHeader(l("header_modinstaller_login", 'start'));
        $this->view->render('modules_login');

        return;
    }

    public function menu($menu_item_gid)
    {
        $this->load->model('Menu_model');
        $this->Menu_model->set_menu_active_item('admin_menu', $menu_item_gid);

        // add link to menu
        $menu_data = $this->Menu_model->get_menu_by_gid('admin_menu');
        $menu_item = $this->Menu_model->get_menu_item_by_gid($menu_item_gid, $menu_data["id"]);

        $user_type = $this->session->userdata("user_type");
        if ($user_type == "admin") {
            $menu_data["check_permissions"] = false;
            $permissions = array();
        } else {
            $menu_data["check_permissions"] = true;
            $permissions = $this->session->userdata("permission_data");
        }
        $menu_items = $this->Menu_model->get_menu_active_items_list($menu_data["id"], $menu_data["check_permissions"], array(), $menu_item["id"], $permissions);

        $this->view->assign("menu", $menu_item);
        $this->view->assign("options", $menu_items);
        $this->view->setHeader(l("header_settings_list", 'start') . "" . $menu_item["value"]);
        $this->view->render('menu_list');

        return;
    }

    public function settings($section = "overview")
    {
        $this->load->model('Menu_model');
        $this->Menu_model->set_menu_active_item('admin_menu', 'system-items');

        if ($this->input->post('btn_save')) {
            $errors = array();
            $post_data = $this->input->post('settings', true);

            if ($section == 'numerics') {
                foreach ($this->numerics_settings as $module => $settings) {
                    foreach ($settings as $var) {
                        $value = !empty($post_data[$module][$var]) ? intval($post_data[$module][$var]) : 0;
                        if ($value <= 0) {
                            $errors[] = l('error_numerics_empty', 'start', '', 'text', array('field' => l($module . "_" . $var . "_field", "start")));
                        } else {
                            $this->pg_module->set_module_config($module, $var, $value);
                        }
                    }
                }
            } elseif ($section == 'countries') {
                $save_data = array();

                if (isset($post_data['output_country_format'])) {
                    $save_data['output_country_format'] = trim(strip_tags($post_data['output_country_format']));
                    if (empty($save_data['output_country_format'])) {
                        $errors[] = l('error_output_country_format_empty', 'start');
                    }
                }

                if (isset($post_data['output_region_format'])) {
                    $save_data['output_region_format'] = trim(strip_tags($post_data['output_region_format']));
                    if (empty($save_data['output_region_format'])) {
                        $errors[] = l('error_output_region_format_empty', 'start');
                    }
                }

                if (isset($post_data['output_city_format'])) {
                    $save_data['output_city_format'] = trim(strip_tags($post_data['output_city_format']));
                    if (empty($save_data['output_city_format'])) {
                        $errors[] = l('error_output_city_format_empty', 'start');
                    }
                }

                if (empty($errors)) {
                    foreach ($save_data as $var => $value) {
                        $this->pg_module->set_module_config($section, $var, $value);
                    }
                }
            } else {
                $settings = $this->other_settings[$section];
                foreach ($settings as $subsection => $var) {
                    $field_type = 'text';
                    if (is_array($var)) {
                        $field_type = $var['type'];
                        $field_values = !empty($var['values']) ? $var['values'] : array();
                        $var = $var['var'];
                    }
                    $value = !empty($post_data[$var]) ? $post_data[$var] : '';
                    $error = '';
                    if (!empty($error)) {
                        $errors[] = $error;
                    } else {
                        $this->pg_module->set_module_config($section, $var, $value);
                    }
                }
            }

            if (empty($errors)) {
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_update_numerics_data', 'start'));
            } else {
                $this->system_messages->addMessage(View::MSG_ERROR, $errors);
            }
        }

        if ($section == 'overview') {
            foreach ($this->numerics_settings as $module => $settings) {
                $settings_data["numerics"][$module]["name"] = l($module . "_settings_module", "start");
                foreach ($settings as $var) {
                    $settings_data["numerics"][$module]['vars'][] = array(
                        "field"      => $var,
                        "field_name" => l($module . "_" . $var . "_field", "start"),
                        "value"      => $this->pg_module->get_module_config($module, $var),
                    );
                }
            }
            foreach ($this->other_settings as $module => $settings) {
                $settings_data["other"][$module]["name"] = l($module . "_settings_module", "start");
                foreach ($settings as $var) {
                    $field_type = 'text';
                    $field_values = array();
                    if (is_array($var)) {
                        $field_type = $var['type'];
                        $field_values = !empty($var['values']) ? $var['values'] : array();
                        $var = $var['var'];
                    }
                    $vars_value = $this->pg_module->get_module_config($module, $var);
                    $vars_value_int = intval($vars_value);
                    if (!empty($field_values[$vars_value_int])) {
                        $vars_value_name = l($module . "_" . $var . "_" . $field_values[$vars_value_int] . "_value", "start");
                    } else {
                        $vars_value_name = '';
                    }
                    $settings_data["other"][$module]['vars'][] = array(
                        "field"        => $var,
                        "field_name"   => l($module . "_" . $var . "_field", "start"),
                        "value"        => $vars_value,
                        "value_name"   => $vars_value_name,
                        'field_type'   => $field_type,
                        'field_values' => $field_values,
                    );
                }
            }
            $this->view->setHeader(l("admin_header_settings_overview", 'start'));
        } elseif ($section == 'numerics') {
            foreach ($this->numerics_settings as $module => $settings) {
                $settings_data[$module]["name"] = l($module . "_settings_module", "start");
                foreach ($settings as $var) {
                    $settings_data[$module]['vars'][] = array(
                        "field"      => $var,
                        "field_name" => l($module . "_" . $var . "_field", "start"),
                        "value"      => $this->pg_module->get_module_config($module, $var),
                    );
                }
            }
            $this->view->setHeader(l("admin_header_settings_numerics", 'start'));
        } elseif ($section == 'date_formats') {
            $this->view->assign('date_formats_pages', $this->date_formats_pages);
            $settings_data["name"] = l($section . "_settings_module", "start");
            foreach ($this->date_formats as $var) {
                $settings_data['vars'][] = array(
                    "field"      => $var,
                    "field_name" => l($section . "_" . $var . "_field", "start"),
                    "value"      => $this->pg_date->strftime($this->pg_date->get_format($var, 'st'), time()),
                );
            }
            $this->view->setHeader(l("admin_header_settings_date_formats", 'start'));
        } else {
            $settings_data["name"] = l($section . "_settings_module", "start");
            foreach ($this->other_settings[$section] as $var) {
                $field_type = 'text';
                if (is_array($var)) {
                    $field_type = $var['type'];
                    $field_values = !empty($var['values']) ? $var['values'] : array();
                    $var = $var['var'];
                }
                $vars_value = $this->pg_module->get_module_config($section, $var);
                $vars_value_int = intval($vars_value);
                if (!empty($field_values[$vars_value_int])) {
                    $vars_value_name = l($section . "_" . $var . "_" . $field_values[intval($vars_value)] . "_value", "start");
                } else {
                    $vars_value_name = '';
                }
                $settings_data['vars'][] = array(
                    "field"        => $var,
                    "field_name"   => l($section . "_" . $var . "_field", "start"),
                    "value"        => $vars_value,
                    "value_name"   => $vars_value_name,
                    'field_type'   => $field_type,
                    'field_values' => $field_values,
                );
            }
            if ($section === 'countries') {
                $this->view->setHeader(l("admin_header_settings_countries", 'start'));
            } else {
                $this->view->setHeader(l("header_settings_numerics_list", 'start'));
            }
        }

        $this->view->assign("section", $section);
        $this->view->assign("settings_data", $settings_data);
        $this->view->assign("numerics_settings", $this->numerics_settings);
        $this->view->assign("other_settings", $this->other_settings);
        $this->view->render('numerics_list');

        return;
    }

    public function date_formats($format_id)
    {
        if (!in_array($format_id, array_keys($this->date_formats))) {
            $this->settings('date_formats');
        }

        $this->load->model('Menu_model');
        $this->Menu_model->set_menu_active_item('admin_menu', 'system-items');

        if ($this->input->post('btn_save')) {
            $generic_tpl = '';

            $tpl = $this->input->post('tpl', true);
            $tpl = trim(strip_tags($tpl));
            if (empty($tpl)) {
                $this->system_messages->addMessage(View::MSG_ERROR, l('error_date_format_empty', 'start'));
            } else {
                // Fill $data with used patterns
                foreach ($this->pg_date->available_formats[$format_id] as $f_id => $available) {
                    $value = $this->input->post($f_id, true);
                    if (in_array($value, $available)) {
                        $data[$f_id] = $value;
                    }
                }
                $generic_tpl = $this->pg_date->create_generic_tpl($tpl, $data);
                $this->pg_date->save_format($generic_tpl, $format_id);
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_date_format_update', 'start'));
            }
        } else {
            $generic_tpl = $this->pg_module->get_module_config('start', 'date_format_' . $format_id);
        }

        $format = array(
            'current'   => $this->pg_date->parse_generic_template($generic_tpl, 'js'),
            'available' => $this->pg_date->available_formats[$format_id],
            'name'      => l('date_formats_' . $format_id . '_field', 'start'),
            'gid'       => $format_id,
        );
        $this->view->setHeader(l('header_settings_numerics_list', 'start'));
        $this->view->assign('section', 'date_formats');
        $this->view->assign('format', $format);
        $this->view->assign('settings_name', l('date_formats_settings_module', 'start'));
        $this->view->assign('other_settings', $this->other_settings);
        $this->view->render('date_formats');

        return;
    }

    public function ajax_get_example()
    {
        $uf_tpl = $this->input->get('tpl', true);
        $format_id = $this->input->get('format_id', true);
        if (empty($uf_tpl) || empty($format_id)) {
            return false;
        }

        // Fill $data with used patterns
        foreach ($this->pg_date->available_formats[$format_id] as $f_id => $available) {
            $value = $this->input->get($f_id, true);
            if (in_array($value, $available)) {
                $data[$f_id] = $value;
            }
        }
        $generic_tpl = $this->pg_date->create_generic_tpl($uf_tpl, $data);
        echo $this->pg_date->strftime($generic_tpl, null, 'generic');
    }

    public function lang_inline_editor($is_textarea = 0)
    {
        $this->view->assign("langs", $this->pg_language->languages);
        $this->view->assign('is_textarea', $is_textarea);
        $this->view->render('helper_lang_inline_editor');

        return;
    }

    public function wysiwyg_uploader($module = '', $id = 0, $upload_config_gid = '', $field = 'upload')
    {
        $module = trim(strip_tags($module));
        $id = intval($id);
        $upload_config_gid = trim(strip_tags($upload_config_gid));
        $field = trim(strip_tags($field));

        $this->load->model('Start_model');
        $upload = $this->Start_model->do_wysiwyg_upload($module, $id, $upload_config_gid, $field);
        $message = '';
        $url = '';
        if ($upload['error']) {
            $message = $upload['error'];
        } elseif ($upload['is_uploaded']) {
            $url = $upload['upload_url'];
        }
        $funcNum = intval($this->input->get('CKEditorFuncNum', true));
        $ckeditor = trim(strip_tags($this->input->get('CKEditor', true)));
        $langcode = trim(strip_tags($this->input->get('langCode', true)));
        echo("<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($funcNum, '$url', '$message');</script>");

        return;
    }

    public function geolocation()
    {
        $this->load->model('Menu_model');
        $this->Menu_model->set_menu_active_item('admin_menu', 'system-items');

        if ($this->input->post('btn_save')) {
            $geolocation_onoff = intval($this->input->post('geolocation_onoff', true));
            $this->pg_module->set_module_config('start', 'geolocation_onoff', $geolocation_onoff);
        } else {
            $geolocation_onoff = $this->pg_module->get_module_config('start', 'geolocation_onoff');
        }
        $this->view->assign('geolocation_onoff', $geolocation_onoff);

        $this->view->setHeader(l("header_settings_geolocation", 'start'));
        $this->view->render('geolocation_settings');

        return;
    }
}
