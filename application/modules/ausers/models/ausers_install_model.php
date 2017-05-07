<?php

namespace Pg\Modules\Ausers\Models;

use Pg\Libraries\Setup;

/**
 * Ausers module
 *
 * @package 	PG_Core
 *
 * @copyright 	Copyright (c) 2000-2014 PG Core
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */

/**
 * Administrators install model
 *
 * @package 	PG_RealEstate
 * @subpackage 	Ausers
 *
 * @category	models
 *
 * @copyright 	Copyright (c) 2000-2014 PG Core
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
class Ausers_install_model extends \Model
{
    /**
     * Link to CodeIgniter object
     *
     * @var object
     */
    protected $ci;

    /**
     * Constructor
     *
     * @return Ausers_install_model
     */
    public function __construct()
    {
        parent::__construct();
        $this->ci = &get_instance();
        $this->modules_data = Setup::getModuleData('ausers', Setup::TYPE_MODULES_DATA);
        // load langs
        $this->ci->load->model('Install_model');
    }

    /**
     * Form of module settings
     *
     * @return array
     */
    public function _validate_settings_form()
    {
        $errors = array();
        $data["name"] = $this->ci->input->post('name', true);
        $data["password"] = $this->ci->input->post('password', true);
        $data["email"] = $this->ci->input->post('email', true);
        $data["nickname"] = $this->ci->input->post('nickname', true);

        if (empty($data["name"])) {
            $errors[] = $this->ci->pg_language->get_string('ausers', 'error_name_incorrect');
        }

        $this->ci->config->load('reg_exps', true);
        $login_expr = $this->ci->config->item('nickname', 'reg_exps');
        $password_expr = $this->ci->config->item('password', 'reg_exps');
        $email_expr = $this->ci->config->item('email', 'reg_exps');

        if (empty($data["nickname"]) || !preg_match($login_expr, $data["nickname"])) {
            $errors[] = $this->ci->pg_language->get_string('ausers', 'error_nickname_incorrect');
        }

        if (empty($data["password"]) || !preg_match($password_expr, $data["password"])) {
            $errors[] = $this->ci->pg_language->get_string('ausers', 'error_password_incorrect');
        }

        if (empty($data["email"]) || !preg_match($email_expr, $data["email"])) {
            $errors[] = $this->ci->pg_language->get_string('ausers', 'error_email_incorrect');
        }

        $return = array(
            "data"   => $data,
            "errors" => $errors,
        );

        return $return;
    }

    /**
     * Save module settings
     *
     * @param array $data settings data
     *
     * @return void
     */
    public function _save_settings_form($data)
    {
        $data["password"] = md5($data["password"]);
        $data["status"] = 1;
        $data["lang_id"] = 1;
        $data["user_type"] = "admin";

        $this->ci->load->model('Ausers_model');
        $this->ci->Ausers_model->save_user(null, $data);

        return;
    }

    /**
     * Return settings form
     *
     * @param boolean $submit turn on if it is submit
     *
     * @return string/false
     */
    public function _get_settings_form($submit = false)
    {
        $data = array(
            "nickname" => "admin",
            "name"     => "Administrator",
        );
        if ($submit) {
            $validate = $this->_validate_settings_form();
            if (!empty($validate["errors"])) {
                $this->ci->view->assign('settings_errors', $validate["errors"]);
                $data = $validate["data"];
            } else {
                $this->_save_settings_form($validate["data"]);

                return false;
            }
        }

        $this->ci->view->assign('settings_data', $data);
        $html = $this->ci->view->fetch('install_settings_form', 'admin', 'ausers');

        return $html;
    }

    /**
     * Install data of menu module
     *
     * @return void
     */
    public function install_menu()
    {
        $this->ci->load->model('Menu_model');
        $this->ci->load->helper('menu');

        foreach ($this->modules_data['menu'] as $gid => $menu_data) {
            $this->modules_data['menu'][$gid]['id'] = linked_install_set_menu($gid, $menu_data["action"], $menu_data["name"]);
            linked_install_process_menu_items($this->modules_data['menu'], 'create', $gid, 0, $this->modules_data['menu'][$gid]["items"]);
        }
    }

    /**
     * Import languages of menu module
     *
     * @param array $langs_ids languages identifiers
     *
     * @return void
     */
    public function install_menu_lang_update($langs_ids = null)
    {
        if (empty($langs_ids)) {
            return false;
        }
        $langs_file = $this->ci->Install_model->language_file_read('ausers', 'menu', $langs_ids);

        if (!$langs_file) {
            log_message('info', 'Empty menu langs data');

            return false;
        }

        $this->ci->load->model('Menu_model');
        $this->ci->load->helper('menu');

        foreach ($this->modules_data['menu'] as $gid => $menu_data) {
            linked_install_process_menu_items($this->modules_data['menu'], 'update', $gid, 0, $this->modules_data['menu'][$gid]["items"], $gid, $langs_file);
        }

        return true;
    }

    /**
     * Export languages of menu module
     *
     * @param array $langs_ids languages identifiers
     *
     * @return array
     */
    public function install_menu_lang_export($langs_ids = null)
    {
        if (empty($langs_ids)) {
            return false;
        }
        $this->ci->load->model('Menu_model');
        $this->ci->load->helper('menu');

        $return = array();
        foreach ($this->modules_data['menu'] as $gid => $menu_data) {
            $temp = linked_install_process_menu_items($this->modules_data['menu'], 'export', $gid, 0, $this->modules_data['menu'][$gid]["items"], $gid, $langs_ids);
            $return = array_merge($return, $temp);
        }

        return array("menu" => $return);
    }

    /**
     * Uninstall data of menu module
     *
     * @return void
     */
    public function deinstall_menu()
    {
        $this->ci->load->model('Menu_model');
        $this->ci->load->helper('menu');
        linked_install_delete_menu_items($menu_gid, $items);
        foreach ($this->modules_data['menu'] as $gid => $menu_data) {
            if ($menu_data["action"] == "create") {
                linked_install_set_menu($gid, "delete");
            } else {
                linked_install_delete_menu_items($gid, $this->modules_data['menu'][$gid]["items"]);
            }
        }
    }

    /**
     * Install data of notifications module
     *
     * @return void
     */
    public function install_notifications()
    {
        // add notification
        $this->ci->load->model("Notifications_model");
        $this->ci->load->model("notifications/models/Templates_model");
        $templates_ids = array();
        foreach ((array) $this->modules_data['notifications']["templates"] as $template_data) {
            if (is_array($template_data["vars"])) {
                $template_data["vars"] = implode(", ", $template_data["vars"]);
            }
            $validate_data = $this->ci->Templates_model->validate_template(null, $template_data);
            if (!empty($validate_data["errors"])) {
                continue;
            }
            $templates_ids[$template_data['gid']] = $this->ci->Templates_model->save_template(null, $validate_data["data"]);
        }

        foreach ((array) $this->modules_data['notifications']["notifications"] as $notification_data) {
            if (!isset($templates_ids[$notification_data["template"]])) {
                $template = $this->ci->Templates_model->get_template_by_gid($notification_data["template"]);
                $templates_ids[$notification_data["template"]] = $template["id"];
            }
            $notification_data["id_template_default"] = $templates_ids[$notification_data["template"]];
            $validate_data = $this->ci->Notifications_model->validate_notification(null, $notification_data);
            if (!empty($validate_data["errors"])) {
                continue;
            }
            $this->ci->Notifications_model->save_notification(null, $validate_data["data"]);
        }
    }

    /**
     * Import languages of notifiactions module
     *
     * @param array $langs_ids languages identifiers
     *
     * @return void
     */
    public function install_notifications_lang_update($langs_ids = null)
    {
        if (empty($langs_ids)) {
            return false;
        }
        $this->ci->load->model("Notifications_model");

        $langs_file = $this->ci->Install_model->language_file_read("ausers", "notifications", $langs_ids);
        if (!$langs_file) {
            log_message("info", "Empty notifications langs data");

            return false;
        }

        $this->ci->Notifications_model->update_langs($this->modules_data['notifications'], $langs_file, $langs_ids);

        return true;
    }

    /**
     * Export languages of notifications module
     *
     * @param array $langs_ids languages identifiers
     *
     * @return array
     */
    public function install_notifications_lang_export($langs_ids = null)
    {
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }
        
        $this->ci->load->model("Notifications_model");
        $langs = $this->ci->Notifications_model->export_langs($this->modules_data['notifications'], $langs_ids);

        return array("notifications" => $langs);
    }

    /**
     * Unistall data of notifacations module
     *
     * @return void
     */
    public function deinstall_notifications()
    {
        $this->ci->load->model("Notifications_model");
        $this->ci->load->model("notifications/models/Templates_model");

        foreach ((array) $this->modules_data['notifications']["notifications"] as $notification_data) {
            $this->ci->Notifications_model->delete_notification_by_gid($notification_data["gid"]);
        }

        foreach ((array) $this->modules_data['notifications']["templates"] as $template_data) {
            $this->ci->Templates_model->delete_template_by_gid($template_data["gid"]);
        }
    }

    /**
     * Install module data
     *
     * @return void
     */
    public function _arbitrary_installing()
    {
        if (FREE_VERSION) {
            $this->ci->pg_module->set_module_config('ausers', 'is_add_available', '0');
        }
    }
}
