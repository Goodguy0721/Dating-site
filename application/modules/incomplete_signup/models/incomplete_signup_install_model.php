<?php

/**
 * Incomplete_signup install model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 **/
class Incomplete_signup_install_model extends Model
{
    private $CI;

    private $menu = array(
        'admin_menu' => array(
            'action' => 'none',
            'name'   => '',
            'items'  => array(
                'other_items' => array(
                    'action' => 'none',
                    'name'   => '',
                    'items'  => array(
                        "add_ons_items" => array(
                            "action" => "none",
                            'name'   => '',
                            "items"  => array(
                                "incomplete_signup_menu_item" => array("action" => "create", "link" => "admin/incomplete_signup", "status" => 1, "sorter" => 9),
                            ),
                        ),
                    ),
                ),
            ),
        ),

        'admin_incomplete_signup_menu' => array(
            'action' => 'create',
            'name'   => 'Incomplete signup menu',
            'items'  => array(
                'incomplete_signup_settings' => array('action' => 'create', 'link' => 'admin/incomplete_signup', 'status' => 1, "sorter" => 1),
            ),
        ),
    );

    private $_notifications = array(
        'notifications' => array(
            array('gid' => 'users_incomplete_signup', 'send_type' => 'simple'),
        ),
        'templates' => array(
            array('gid' => 'users_incomplete_signup', 'name' => 'User incomplete registration letter', 'vars' => array('email', 'nickname'), 'content_type' => 'text'),
        ),
    );

    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
    }

    public function install_menu()
    {
        $this->CI->load->helper('menu');

        foreach ($this->menu as $gid => $menu_data) {
            $this->menu[$gid]['id'] = linked_install_set_menu($gid, $menu_data['action'], $menu_data['name']);
            linked_install_process_menu_items($this->menu, 'create', $gid, 0, $this->menu[$gid]['items']);
        }
    }

    public function install_menu_lang_update($langs_ids = null)
    {
        if (empty($langs_ids)) {
            return false;
        }
        $langs_file = $this->CI->Install_model->language_file_read('incomplete_signup', 'menu', $langs_ids);

        if (!$langs_file) {
            log_message('info', 'Empty menu langs data');

            return false;
        }

        $this->CI->load->helper('menu');

        foreach ($this->menu as $gid => $menu_data) {
            linked_install_process_menu_items($this->menu, 'update', $gid, 0, $this->menu[$gid]['items'], $gid, $langs_file);
        }

        return true;
    }

    public function install_menu_lang_export($langs_ids)
    {
        if (empty($langs_ids)) {
            return false;
        }
        $this->CI->load->helper('menu');

        $return = array();
        foreach ($this->menu as $gid => $menu_data) {
            $temp = linked_install_process_menu_items($this->menu, 'export', $gid, 0, $this->menu[$gid]['items'], $gid, $langs_ids);
            $return = array_merge($return, $temp);
        }

        return array('menu' => $return);
    }

    public function deinstall_menu()
    {
        $this->CI->load->helper('menu');
        foreach ($this->menu as $gid => $menu_data) {
            if ($menu_data['action'] == 'create') {
                linked_install_set_menu($gid, 'delete');
            } else {
                linked_install_delete_menu_items($gid, $this->menu[$gid]['items']);
            }
        }
    }

    /**
     * Check system requirements of module
     */
    public function _validate_requirements()
    {
        $result = array("data" => array(), "result" => true);

        //check for Mbstring
        $good            = function_exists("mb_substr");
        $result["data"][] = array(
            "name"   => "Mbstring extension (required for feeds parsing) is installed",
            "value"  => $good ? "Yes" : "No",
            "result" => $good,
        );
        $result["result"] = $result["result"] && $good;

        return $result;
    }

    public function install_notifications()
    {
        // add notification
        $this->CI->load->model('Notifications_model');
        $this->CI->load->model('notifications/models/Templates_model');

        foreach ($this->_notifications['templates'] as $tpl) {
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

        foreach ($this->_notifications['notifications'] as $notification) {
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

    public function install_notifications_lang_update($langs_ids = null)
    {
        if (empty($langs_ids)) {
            return false;
        }
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }
        $this->CI->load->model('Notifications_model');

        $langs_file = $this->CI->Install_model->language_file_read('incomplete_signup', 'notifications', $langs_ids);

        if (!$langs_file) {
            log_message('info', 'Empty notifications langs data');

            return false;
        }

        $this->CI->Notifications_model->update_langs($this->_notifications, $langs_file, $langs_ids);

        return true;
    }

    public function install_notifications_lang_export($langs_ids = null)
    {
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }
        $this->CI->load->model('Notifications_model');
        $langs = $this->CI->Notifications_model->export_langs($this->_notifications, $langs_ids);

        return array('notifications' => $langs);
    }

    public function deinstall_notifications()
    {
        $this->CI->load->model('Notifications_model');
        $this->CI->load->model('notifications/models/Templates_model');
        foreach ($this->_notifications['templates'] as $tpl) {
            $this->CI->Templates_model->delete_template_by_gid($tpl['gid']);
        }
        foreach ($this->_notifications['notifications'] as $notification) {
            $this->CI->Notifications_model->delete_notification_by_gid($notification['gid']);
        }
    }

    public function _arbitrary_installing()
    {
    }

    public function _arbitrary_deinstalling()
    {
    }
}
