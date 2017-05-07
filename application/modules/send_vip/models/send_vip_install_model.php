<?php

namespace Pg\Modules\Send_vip\Models;

/**
 * send_vip module
 *
 * @package 	PG_Dating
 *
 * @copyright 	Copyright (c) 2000-2015 PG Dating Pro - php dating software
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */

/**
 * Install model
 *
 * @package 	PG_Dating
 * @subpackage 	Send_vip
 *
 * @category	models
 *
 * @copyright 	Copyright (c) 2000-2015 PG Dating Pro - php dating software
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
class Send_vip_install_model extends \Model
{
    /**
     * Link to CodeIgniter object
     *
     * @var object
     */
    protected $CI;

    /**
     * Menu configuration
     *
     * @var array
     */
    protected $menu = array(
        "admin_menu" => array(
            "action" => "none",
            "items"  => array(
                "other_items" => array(
                    "action"  => "none",
                    "items"   => array(
                        "add_ons_items" => array(
                            "action"    => "none",
                            "items"     => array(
                                "send_vip_menu_item" => array("action" => "create", "link" => "admin/send_vip/index", "status" => 1, "sorter" => 1),
                            ),
                        ),
                    ),
                ),
            ),
        ),
        'admin_send_vip_menu' => array(
            'action' => 'create',
            'name'   => 'Admin mode - Add-ons - Send vip menu',
            'items'  => array(
                'send_vip_settings_item' => array('action' => 'create', 'link' => 'admin/send_vip/settings', 'status' => 1, "sorter" => 10),
                'send_vip_view_item'     => array('action' => 'create', 'link' => 'admin/send_vip/view', 'status' => 1, "sorter" => 20),
            ),
        ),
    );

    /**
     * Moderators configuration
     *
     * @var array
     */
    protected $moderators = array(
        array("module" => "send_vip", "method" => "index", "is_default" => 0),
    );

    /**
     * Fields depended on languages
     *
     * @var array
     */
    protected $lang_dm_data = array(
        array(
            "module"        => "send_vip",
            "model"         => "send_vip_model",
            "method_add"    => "langDedicateModuleCallbackAdd",
            "method_delete" => "langDedicateModuleCallbackDelete",
        ),
    );

    /**
     * Payment configuration
     *
     * @var array
     */
    protected $payment_types = array(
        array(
            'gid'             => 'send_vip',
            'callback_module' => 'send_vip',
            'callback_model'  => 'Send_vip_model',
            'callback_method' => 'paymentSendVipStatus',
        ),
    );

    /**
     * Notifications configuration
     */
    protected $notifications = array(
        "templates" => array(
            array("gid" => "send_vip_msg", "name" => "Send VIP message", "vars" => array("membership", "approve", "decline", "id", "sender"), "content_type" => "html"),
        ),
        "notifications" => array(
            array('gid' => 'send_vip_msg', 'send_type' => 'simple'),
        ),
    );

    /**
     * Class constructor
     *
     * @return Send_vip_Install
     */
    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
        $this->CI->load->model('Install_model');
    }

    /**
     * Install menu data of Send_vip
     *
     * @return void
     */
    public function install_menu()
    {
        $this->CI->load->helper("menu");
        foreach ($this->menu as $gid => $menu_data) {
            $this->menu[$gid]["id"] = linked_install_set_menu($gid, $menu_data["action"], isset($menu_data["name"]) ? $menu_data["name"] : '');
            linked_install_process_menu_items($this->menu, "create", $gid, 0, $this->menu[$gid]["items"]);
        }
    }

    /**
     * Import menu languages of send_vip
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
        $langs_file = $this->CI->Install_model->language_file_read("send_vip", "menu", $langs_ids);

        if (!$langs_file) {
            log_message("info", "Empty menu langs data (send_vip)");

            return false;
        }

        $this->CI->load->helper("menu");

        foreach ($this->menu as $gid => $menu_data) {
            linked_install_process_menu_items($this->menu, "update", $gid, 0, $this->menu[$gid]["items"], $gid, $langs_file);
        }

        return true;
    }

    /**
     * Export menu languages of send_vip
     *
     * @param array $langs_ids languages identifiers
     *
     * @return array
     */
    public function install_menu_lang_export($langs_ids)
    {
        if (empty($langs_ids)) {
            return false;
        }
        $this->CI->load->helper("menu");

        $return = array();
        foreach ($this->menu as $gid => $menu_data) {
            $temp = linked_install_process_menu_items($this->menu, "export", $gid, 0, $this->menu[$gid]["items"], $gid, $langs_ids);
            $return = array_merge($return, $temp);
        }

        return array("menu" => $return);
    }

    /**
     * Uninstall menu data of video chats
     *
     * @return void
     */
    public function deinstall_menu()
    {
        $this->CI->load->helper("menu");
        foreach ($this->menu as $gid => $menu_data) {
            if ($menu_data["action"] == "create") {
                linked_install_set_menu($gid, "delete");
            } else {
                linked_install_delete_menu_items($gid, $this->menu[$gid]["items"]);
            }
        }
    }

    /**
     * Install data of moderators module
     *
     * @return void
     */
    public function install_moderators()
    {
        // install moderators permissions
        $this->CI->load->model('Moderators_model');

        foreach ($this->moderators_methods as $method) {
            $this->CI->Moderators_model->save_method(null, $method);
        }
    }

    /**
     * Import languages of moderators module
     *
     * @param array $langs_ids languages identifiers
     *
     * @return void
     */
    public function install_moderators_lang_update($langs_ids = null)
    {
        $langs_file = $this->CI->Install_model->language_file_read('send_vip', 'moderators', $langs_ids);

        // install moderators permissions
        $this->CI->load->model('Moderators_model');
        $params['where']['module'] = 'send_vip';
        $methods = $this->CI->Moderators_model->get_methods_lang_export($params);

        foreach ($methods as $method) {
            if (!empty($langs_file[$method['method']])) {
                $this->CI->Moderators_model->save_method($method['id'], array(), $langs_file[$method['method']]);
            }
        }
    }

    /**
     * Export languages of moderators module
     *
     * @param array $langs_ids languages identifiers
     *
     * @return array
     */
    public function install_moderators_lang_export($langs_ids)
    {
        $this->CI->load->model('Moderators_model');
        $params['where']['module'] = 'send_vip';
        $methods = $this->CI->Moderators_model->get_methods_lang_export($params, $langs_ids);
        foreach ($methods as $method) {
            $return[$method['method']] = $method['langs'];
        }

        return array('moderators' => $return);
    }

    /**
     * Uninstall data of moderators module
     *
     * @return void
     */
    public function deinstall_moderators()
    {
        // delete moderation methods in moderators
        $this->CI->load->model('Moderators_model');
        $params['where']['module'] = 'send_vip';
        $this->CI->Moderators_model->delete_methods($params);
    }

    /**
     * Install data of payments module
     */
    public function install_payments()
    {
        // add account payment type
        $this->CI->load->model("Payments_model");
        foreach ($this->payment_types as $payment_type) {
            $data = array(
                'gid'             => $payment_type['gid'],
                'callback_module' => $payment_type['callback_module'],
                'callback_model'  => $payment_type['callback_model'],
                'callback_method' => $payment_type['callback_method'],
            );
            $id = $this->CI->Payments_model->save_payment_type(null, $data);
        }
    }

    /**
     * Import data of payment module depended on language
     *
     * @param array $langs_ids languages identifiers
     *
     * @return boolean
     */
    public function install_payments_lang_update($langs_ids = null)
    {
        if (empty($langs_ids)) {
            return false;
        }
        $langs_file = $this->CI->Install_model->language_file_read('send_vip', 'payments', $langs_ids);
        if (!$langs_file) {
            log_message('info', 'Empty payments langs data (send_vip)');

            return false;
        }
        $this->CI->load->model('Payments_model');
        $this->CI->Payments_model->update_langs($this->payment_types, $langs_file, $langs_ids);

        return true;
    }

    /**
     * Export data of payment module depended on language
     *
     * @param array $langs_ids languages identifiers
     *
     * @return array
     */
    public function install_payments_lang_export($langs_ids = null)
    {
        $this->CI->load->model('Payments_model');
        $return = $this->CI->Payments_model->export_langs($this->payment_types, $langs_ids);

        return array("payments" => $return);
    }

    /**
     * Uninstall data of payments module
     *
     * @return void
     */
    public function deinstall_payments()
    {
        $this->CI->load->model('Payments_model');
        foreach ($this->payment_types as $payment_type) {
            $this->CI->Payments_model->delete_payment_type_by_gid($payment_type['gid']);
        }
    }
    /**
     * Install links to notifications module
     */
    public function install_notifications()
    {
        // add notification
        $this->CI->load->model("Notifications_model");
        $this->CI->load->model("notifications/models/Templates_model");

        $tpl_ids = array();

        foreach ($this->notifications['templates'] as $tpl) {
            $template_data = array(
                        'gid'          => $tpl['gid'],
                        'name'         => $tpl['name'],
                        'vars'         => serialize($tpl['vars']),
                        'content_type' => $tpl['content_type'],
                        'date_add'     => date('Y-m-d H:i:s'),
                        'date_update'  => date('Y-m-d H:i:s'),
                );
            $tpl_ids[$tpl['gid']] = $this->CI->Templates_model->save_template(null, $template_data);
        }

        foreach ($this->notifications['notifications'] as $notification) {
            $notification_data = array(
                        'gid'                 => $notification['gid'],
                        'send_type'           => $notification['send_type'],
                        'id_template_default' => $tpl_ids[$notification['gid']],
                        'date_add'            => date("Y-m-d H:i:s"),
                        'date_update'         => date("Y-m-d H:i:s"),
                );
            $this->CI->Notifications_model->save_notification(null, $notification_data);
        }
    }

    /**
     * Import notifications languages
     *
     * @param array $langs_ids
     */
    public function install_notifications_lang_update($langs_ids = null)
    {
        if (empty($langs_ids)) {
            return false;
        }
        $this->CI->load->model("Notifications_model");

        $langs_file = $this->CI->Install_model->language_file_read("send_vip", "notifications", $langs_ids);
        if (!$langs_file) {
            log_message("info", "Empty notifications langs data (send_vip)");

            return false;
        }

        $this->CI->Notifications_model->update_langs($this->notifications, $langs_file, $langs_ids);

        return true;
    }

    /**
     * Export notifications languages
     *
     * @param array $langs_ids
     */
    public function install_notifications_lang_export($langs_ids = null)
    {
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }
        $this->CI->load->model("Notifications_model");
        $langs = $this->CI->Notifications_model->export_langs((array) $this->notifications, $langs_ids);

        return array("notifications" => $langs);
    }

    /**
     * Uninstall links to notifications module
     */
    public function deinstall_notifications()
    {
        ////// add notification
        $this->CI->load->model("Notifications_model");
        $this->CI->load->model("notifications/models/Templates_model");

        foreach ((array) $this->notifications["templates"] as $template_data) {
            $this->CI->Templates_model->delete_template_by_gid($template_data["gid"]);
        }
        foreach ($this->notifications['notifications'] as $notification) {
            $this->CI->Notifications_model->delete_notification_by_gid($notification['gid']);
        }
    }

    /**
     * Install module data
     *
     * @return void
     */
    public function _arbitrary_installing()
    {
        // add entries for lang data updates
        foreach ($this->lang_dm_data as $lang_dm_data) {
            $this->CI->pg_language->add_dedicate_modules_entry($lang_dm_data);
        }
    }

    /**
     * Import module languages
     *
     * @param array $langs_ids languages identifiers
     *
     * @return void
     */
    public function _arbitrary_lang_install($langs_ids = null)
    {
        $langs_file = $this->CI->Install_model->language_file_read("send_vip", "arbitrary", $langs_ids);
        if (!$langs_file) {
            log_message("info", "Empty send_vip arbitrary langs data");

            return false;
        }
        
        return false;
    }

    /**
     * Export module languages
     *
     * @param array $langs_ids languages identifiers
     *
     * @return array
     */
    public function _arbitrary_lang_export($langs_ids = null)
    {
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }

        $arbitrary_return = array();

        return array("arbitrary" => $arbitrary_return);
    }

    /**
     * Uninstall module data
     *
     * @return void
     */
    public function _arbitrary_deinstalling()
    {
        $this->CI->pg_seo->delete_seo_module('send_vip');

        /// delete entries in dedicate modules
        foreach ($this->lang_dm_data as $lang_dm_data) {
            $this->CI->pg_language->delete_dedicate_modules_entry(array('where' => $lang_dm_data));
        }
    }
}
