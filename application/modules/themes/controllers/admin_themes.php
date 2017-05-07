<?php

namespace Pg\Modules\Themes\Controllers;

use Pg\Libraries\View;

/**
 * Themes admin side controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * */
class Admin_Themes extends \Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->isAccess();
        $this->load->model("Themes_model");
        $this->load->model('Menu_model');
        $this->Menu_model->set_menu_active_item('admin_menu', 'interface-items');
    }

    public function index($type = 'user')
    {
        $this->installed_themes($type);
    }

    public function installed_themes($type = 'user')
    {
        if (!$type) {
            $type = "user";
        }
        $current_settings["filter"] = $type;

        $this->view->assign('type', $type);
        $_SESSION["i_themes_list"] = $current_settings;

        $filter = array(
            "user"  => $this->Themes_model->get_installed_themes_count(array("where" => array("theme_type" => 'user'))),
            "admin" => $this->Themes_model->get_installed_themes_count(array("where" => array("theme_type" => 'admin'))),
        );
        $this->view->assign('filter', $filter);

        $params["where"]["theme_type"] = $type;
        $themes = $this->Themes_model->get_installed_themes_list($params);
        $this->view->assign('themes', $themes);

        $this->view->setHeader(l('admin_header_installed_themes_list', 'themes'));
        $this->view->render('list_installed');
    }

    public function enable_themes($type = 'user')
    {
        $current_settings = isset($_SESSION["e_themes_list"]) ? $_SESSION["e_themes_list"] : array();
        if (!isset($current_settings["filter"])) {
            $current_settings["filter"] = "user";
        }

        if (!$type) {
            $type = $current_settings["filter"];
        }
        $this->view->assign('type', $type);
        $_SESSION["e_themes_list"] = $current_settings;

        $themes = $this->Themes_model->get_uninstalled_themes_list($type);
        $this->view->assign('themes', $themes);

        $this->view->setHeader(l('admin_header_enable_themes_list', 'themes'));
        $this->view->render('list_enable');
    }

    public function view_installed($id, $lang_id = 0)
    {
        if (!$lang_id) {
            $lang_id = $this->pg_language->current_lang_id;
        }

        $theme_data = $this->Themes_model->get_theme($id, '', $lang_id);
        $this->view->assign('theme', $theme_data);

        $permissions = $this->Themes_model->check_theme_permissions($theme_data['theme']);
        if (!$permissions['logo']) {
            $error = str_replace('[dir]', $permissions['logo_path'], l('error_logo_dir_writeable_error', 'themes'));
            $this->system_messages->addMessage(View::MSG_ERROR, $error);
        }

        if ($this->input->post('btn_save')) {
            $post_data = array(
                "logo_width"       => $this->input->post('logo_width', true),
                "logo_height"      => $this->input->post('logo_height', true),
                "mini_logo_width"  => $this->input->post('mini_logo_width', true),
                "mini_logo_height" => $this->input->post('mini_logo_height', true),
            );
            $validate_data = $this->Themes_model->validate_logo_params($post_data);

            if (!empty($validate_data["errors"])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data["errors"]);
            } else {
                $this->Themes_model->save_logo_params($id, $validate_data["data"]);
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_logo_params_saved', 'themes'));
            }

            if ($this->input->post('logo_delete') == '1' || $this->input->post('mini_logo_delete') == '1') {
                if ($this->input->post('logo_delete') == '1') {
                    $this->Themes_model->delete_logo($id, $lang_id, 'logo');
                }
                if ($this->input->post('mini_logo_delete') == '1') {
                    $this->Themes_model->delete_logo($id, $lang_id, 'mini_logo');
                }
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_delete_logo', 'themes'));
            }

            if (isset($_FILES["logo"]) && is_array($_FILES["logo"]) && is_uploaded_file($_FILES["logo"]["tmp_name"])) {
                $ret = $this->Themes_model->upload_logo('logo', $id, $theme_data["logo_width"], $theme_data["logo_height"], $lang_id);
                if (!empty($ret["error"])) {
                    $this->system_messages->addMessage(View::MSG_ERROR, $ret["error"]);
                } else {
                    $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_uploaded_logo', 'themes'));
                }
            }
            if (isset($_FILES["mini_logo"]) && is_array($_FILES["mini_logo"]) && is_uploaded_file($_FILES["mini_logo"]["tmp_name"])) {
                $ret_mini = $this->Themes_model->upload_logo('mini_logo', $id, $theme_data["mini_logo_width"], $theme_data["mini_logo_height"], $lang_id);
                if (!empty($ret["error"])) {
                    $this->system_messages->addMessage(View::MSG_ERROR, $ret["error"]);
                } else {
                    $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_uploaded_logo', 'themes'));
                }
            }
            redirect(site_url() . "admin/themes/view_installed/" . $id . "/" . $lang_id);
        }

        $languages = $this->pg_language->languages;
        $this->view->assign('lang_id', $lang_id);
        $this->view->assign('langs', $languages);

        $this->view->setHeader(l('admin_header_theme_view', 'themes'));
        $this->view->render('view');
    }

    public function activate($id)
    {
        $this->Themes_model->set_active($id);
        $this->pg_theme->generateCssForCurrentThemes();
        
        $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_activate_theme', 'themes'));
        $current_settings = $_SESSION["i_themes_list"];
        $url = site_url() . "admin/themes/installed_themes/" . $current_settings["filter"];
        redirect($url);
    }

    public function sets($id_theme)
    {
        if (empty($id_theme)) {
            redirect(site_url() . "admin/themes");
        }
        $theme_data = $this->Themes_model->get_theme($id_theme);
        $this->view->assign('theme', $theme_data);
        $sets = $this->Themes_model->get_sets_list($id_theme);
        $this->view->assign('sets', $sets);
        $this->view->assign('id_theme', $id_theme);

        $permissions = $this->Themes_model->check_theme_permissions($theme_data['theme']);
        if (!$permissions['sets']) {
            $error = str_replace('[dir]', $permissions['sets_path'], l('error_sets_dir_writeable_error', 'themes'));
            $this->system_messages->addMessage(View::MSG_ERROR, $error);
        }

        $this->view->setHeader($theme_data["name"] . " : " . l('admin_header_theme_sets', 'themes'));

        if (!empty($_SESSION["i_themes_list"])) {
            $current_settings = $_SESSION["i_themes_list"];
        }
        $this->view->setBackLink(site_url() . "admin/themes/installed_themes/" . empty($current_settings["filter"]) ? '' : $current_settings["filter"]);
        $this->view->render('list_sets');
    }

    public function edit_set($id_theme = null, $id_set = 0)
    {
        $theme_data = $this->Themes_model->get_theme($id_theme);
        
        $permissions = $this->Themes_model->check_theme_permissions($theme_data['theme']);
        if (!$permissions['sets']) {
            $error = str_replace('[dir]', $permissions['sets_path'], l('error_sets_dir_writeable_error', 'themes'));
            $this->system_messages->addMessage(View::MSG_ERROR, $error);
        }

        if ($id_set) {
            $set = $this->Themes_model->get_set_by_id($id_set);
        } else {
            $set = array(
                'template_id' => 1,
                'scheme_type' => 'light',
            );
        }
        
        if(empty($set['set_gid'])){
            $set['set_gid'] = '';
        }
        
        $theme_settings = $this->pg_theme->format_theme_settings('', $theme_data['type'], $theme_data['gid'], $set['set_gid']);

        // load defaults settings, $scheme = array()
        include $theme_data["path"] . "config/colors.config.php";
        
        $sections = $this->Themes_model->getSections($scheme, $set, $theme_settings['img_set_path']);
        if ($this->input->post('btn_save') || $this->input->post('btn_save_close')) {
            $post_data = array(
                "set_name"    => $this->input->post('set_name', true),
                "set_gid"     => $this->input->post('set_gid', true),
                "id_theme"    => $id_theme,
                "scheme_type" => $this->input->post('scheme_type', true),
            );
            foreach ($scheme as $name => $value) {
                if($value['active']){
                    $post_data["color_settings"][$name] = $this->input->post($name, true);
                }
            }
            foreach ($sections['files'] as $name => &$bg_file) {
                $post_data["color_settings"][$name] = $set['color_settings'][$name];
                if ($this->input->post($name . '_delete', true)) {
                    $bg_file = false;
                }
            }

            $validate_data = $this->Themes_model->validate_set($id_set, $post_data, $sections['files'], $theme_data);
            if (!empty($validate_data["errors"])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data["errors"]);
            } else {
                $flag_add = empty($id_set);
                if ($flag_add) {
                    $validate_data["data"]["active"] = 0;
                }
                if (DEMO_MODE && $id_set == 2) {
                    $this->system_messages->addMessage(View::MSG_ERROR, l('error_demo_mode', 'start'));
                } else {
                    $id_set = $this->Themes_model->save_set($id_set, $validate_data["data"]);
                    
                    if (!$flag_add) {
                        $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_update_set', 'themes'));
                    } else {
                        $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_add_set', 'themes'));
                    }

                    if($this->input->post('btn_save')){
                        redirect(site_url() . "admin/themes/edit_set/" . $id_theme . "/" . $id_set);
                    }
                    if($this->input->post('btn_save_close')){
                        redirect(site_url() . "admin/themes/sets/" . $id_theme);
                    }
                }
            }
        }
        
        $this->view->assign("sections", $sections);

        $this->view->assign("scheme_json", json_encode($scheme));
        $this->view->assign("id_theme", $id_theme);
        $this->view->assign("id_set", $id_set);

        $this->view->setHeader(l('admin_header_set_edit', 'themes'));
        $this->view->setBackLink(site_url() . "admin/themes/sets/" . $id_theme);
        $this->view->render('edit_set');
    }

    public function activate_set($id_theme, $id_set)
    {
        $id_set = intval($id_set);
        if ($id_set) {
            $this->Themes_model->activate_set($id_set);
            $this->Themes_model->regenerateColorset($id_set);
            $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_activate_set', 'themes'));
        }
        $url = site_url() . "admin/themes/sets/" . $id_theme;
        redirect($url);
    }

    public function delete_set($id_theme, $id_set)
    {
        $id_set = intval($id_set);
        if ($id_set) {
            if (DEMO_MODE && $id_set == 2 /* && $validate_data['data']['set_gid'] == 'default' */) {
                $this->system_messages->addMessage(View::MSG_ERROR, l('error_demo_mode', 'start'));
            } else {
                $this->Themes_model->delete_set($id_set);
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_delete_set', 'themes'));
            }
        }
        $url = site_url() . "admin/themes/sets/" . $id_theme;
        redirect($url);
    }

    public function install($theme)
    {
        if (!empty($theme)) {
            $return = $this->Themes_model->install_theme($theme);
            if ($return) {
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_installed_theme', 'themes'));
            } else {
                $this->system_messages->addMessage(View::MSG_ERROR, l('error_installed_theme', 'themes'));
            }
        }
        $current_settings = $_SESSION["e_themes_list"];
        $url = site_url() . "admin/themes/enable_themes/" . $current_settings["filter"];
        redirect($url);
    }

    public function uninstall($id)
    {
        if (!empty($id)) {
            $return = $this->Themes_model->uninstall_theme($id);
            if ($return) {
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_uninstalled_theme', 'themes'));
            } else {
                $this->system_messages->addMessage(View::MSG_ERROR, l('error_uninstalled_theme', 'themes'));
            }
        }
        $current_settings = $_SESSION["i_themes_list"];
        $url = site_url() . "admin/themes/installed_themes/" . $current_settings["filter"];
        redirect($url);
    }

    public function preview($theme, $scheme = '')
    {
        if (!$scheme) {
            $theme_base_data = $this->pg_theme->get_theme_base_data($theme);
            $scheme = !empty($theme_base_data[$theme]['scheme']) ? $theme_base_data[$theme]['scheme'] : '';
        }
        $_SESSION["preview_theme"] = $theme;
        $_SESSION["preview_scheme"] = $scheme;
        $_SESSION['change_color_scheme'] = true;
        redirect(site_url());
    }

    public function regenerate_colorsets($id_theme)
    {
        $return = $this->Themes_model->regenerate_color_sets($id_theme);
        if (!empty($return["errors"])) {
            echo "<b>Errors:</b><br>";
            print_r($return["errors"]);
        } else {
            echo "<b>Success</b><br>";
        }
    }

    public function rtl_parser($id_theme = 0, $css_gid = '')
    {
        $themes = $this->Themes_model->get_installed_themes_list();
        $this->view->assign('themes', $themes);

        if (!empty($id_theme)) {
            $theme_data = $this->Themes_model->get_theme($id_theme);
            $this->view->assign('theme_data', $theme_data);
        }

        if (!empty($css_gid)) {
            $css_path = $theme_data['path'] . 'config/' . str_replace('[rtl]', 'ltr', $theme_data['css'][$css_gid]['file']);
            $css_data = $this->Themes_model->parse_current_rtl($css_path);
            $this->view->assign('css_data', $css_data);
        }

        $this->view->assign('id_theme', $id_theme);
        $this->view->assign('css_gid', $css_gid);

        $this->view->setHeader(l('admin_header_rtl_parser', 'themes'));
        $this->view->render('form_rtl');
    }
    
    private function isAccess()
    {
        if (SOCIAL_MODE === true) {
            $this->view->setRedirect(site_url() . 'admin/start/menu/interface-items');
        }
    }
}
