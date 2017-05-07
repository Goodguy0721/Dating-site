<?php

/**
 * Tickets install model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Katya Kashkova <katya@pilotgroup.net>
 *
 * @version $Revision: 2 $ $Date: 2010-04-02 15:07:07 +0300 (Ср, 02 апр 2010) $ $Author: kkashkova $
 **/
class Tickets_install_model extends Model
{
    private $CI;
    private $menu = array(
        'user_footer_menu' => array(
            'action' => 'none',
            'items'  => array(
                'footer-menu-help-item' => array(
                    'action' => 'none',
                    'items'  => array(
                        'footer-menu-tickets-item' => array('action' => 'create', 'link' => 'tickets', 'status' => 1, 'sorter' => 1),
                    ),
                ),
            ),
        ),
        'user_top_advanced_menu' => array(
            'action' => 'none',
            'name'   => '',
            'items'  => array(
                'tickets_advanced_item' => array(
                    'action' => 'create',
                    'link'   => 'tickets/index',
                    'icon'   => 'ticket',
                    'status' => 1,
                    'sorter' => 30,
                ),
            ),
        ),
        'admin_menu' => array(
            'action' => 'none',
            'items'  => array(
                'system_items' => array(
                    'action' => 'none',
                    'items'  => array(
                        'tickets_menu_item' => array('action' => 'create', 'link' => 'admin/tickets','icon' => 'comments-o', 'status' => 1, 'sorter' => 0, 'indicator_gid' => 'new_ticket_item'),
                    ),
                ),
            ),
        ),
        'admin_tickets_menu' => array(
            'action' => 'create',
            'name'   => 'Contact section menu',
            'items'  => array(
                'responder_list_item' => array('action' => 'create', 'link' => 'admin/tickets', 'status' => 1, 'sorter' => 1),
                'reasons_list_item'   => array('action' => 'create', 'link' => 'admin/tickets/reasons', 'status' => 1, 'sorter' => 2),
                'settings_list_item'  => array('action' => 'create', 'link' => 'admin/tickets/settings', 'status' => 1, 'sorter' => 3),
            ),
        ),

        'user_alerts_menu' => array(
            'action' => 'none',
            'items'  => array(
                'tickets_new_item' => array(
                    'action' => 'create',
                    'link'   => 'tickets/get_new_tickets',
                    'icon'   => 'ticket',
                    'status' => 1,
                    'sorter' => 2,
                ),
            ),
        ),
    );
    private $notifications = array(
        'notifications' => array(
            array('gid' => 'tickets_form', 'send_type' => 'simple'),
            array('gid' => 'tickets_for_user', 'send_type' => 'simple'),
            array('gid' => 'tickets_for_admin', 'send_type' => 'simple'),
        ),
        'templates' => array(
            array('gid' => 'tickets_form', 'name' => 'Tickets form mail', 'vars' => array("user_name", "user_email", "subject", "message", "reason", "form_date"), 'content_type' => 'text'),
            array('gid' => 'tickets_for_user', 'name' => 'Message from site admin ', 'vars' => array("output_name", "message", "link"), 'content_type' => 'text'),
            array('gid' => 'tickets_for_admin', 'name' => 'Message from a site member', 'vars' => array("output_name", "message", "link"), 'content_type' => 'text'),
        ),
    );

    private $moderation_types = array(
        array(
            "name"                 => "tickets",
            "mtype"                => "-1",
            "module"               => "tickets",
            "model"                => "Tickets_model",
            "check_badwords"       => "1",
            "method_get_list"      => "",
            "method_set_status"    => "",
            "method_delete_object" => "",
            "allow_to_decline"     => "0",
            "template_list_row"    => "",
        ),
    );
    
    protected $menu_indicators = array(
        array(
            'gid'               => 'new_ticket_item',
            'delete_by_cron'    => false,
            'auth_type'         => 'admin',
        ),
    );

    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
        $this->CI->load->model('Install_model');
    }

    public function install_menu()
    {
        $this->CI->load->helper('menu');
        foreach ($this->menu as $gid => $menu_data) {
            $this->menu[$gid]['id'] = linked_install_set_menu($gid, $menu_data["action"], $menu_data["name"]);
            linked_install_process_menu_items($this->menu, 'create', $gid, 0, $this->menu[$gid]["items"]);
        }
        if (!empty($this->menu_indicators)) {
            $this->CI->load->model('menu/models/Indicators_model');
            foreach ($this->menu_indicators as $data) {
                $this->CI->Indicators_model->save_type(null, $data);
            }
        }
    }

    public function install_menu_lang_update($langs_ids = null)
    {
        if (empty($langs_ids)) {
            return false;
        }
        $langs_file = $this->CI->Install_model->language_file_read('tickets', 'menu', $langs_ids);

        if (!$langs_file) {
            log_message('info', 'Empty menu langs data');

            return false;
        }
        $this->CI->load->helper('menu');
        foreach ($this->menu as $gid => $menu_data) {
            linked_install_process_menu_items($this->menu, 'update', $gid, 0, $this->menu[$gid]["items"], $gid, $langs_file);
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
            $temp = linked_install_process_menu_items($this->menu, 'export', $gid, 0, $this->menu[$gid]["items"], $gid, $langs_ids);
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
        
        if (!empty($this->menu_indicators)) {
            $this->CI->load->model('menu/models/Indicators_model');
            foreach ($this->menu_indicators as $data) {
                $this->CI->Indicators_model->delete_type($data['gid']);
            }
        }
    }

    public function install_site_map()
    {
        $this->CI->load->model('Site_map_model');
        $site_map_data = array(
            'module_gid'      => 'tickets',
            'model_name'      => 'Tickets_model',
            'get_urls_method' => 'get_sitemap_urls',
        );
        $this->CI->Site_map_model->set_sitemap_module('tickets', $site_map_data);
    }

    public function install_banners()
    {
        ///// add banners module
        $this->CI->load->model('Tickets_model');
        $this->CI->load->model('banners/models/Banner_group_model');
        $this->CI->load->model('banners/models/Banner_place_model');
        $this->CI->Banner_group_model->set_module("tickets", "Tickets_model", "_banner_available_pages");

        $group_id = $this->CI->Banner_group_model->get_group_id_by_gid('contact_groups');
        ///add pages in group
        $pages = $this->CI->Tickets_model->_banner_available_pages();
        if ($pages) {
            foreach ($pages  as $key => $value) {
                $page_attrs = array(
                    'group_id' => $group_id,
                    'name'     => $value['name'],
                    'link'     => $value['link'],
                );
                $this->CI->Banner_group_model->add_page($page_attrs);
            }
        }
    }

    public function install_notifications()
    {
        // add notification
        $this->CI->load->model('Notifications_model');
        $this->CI->load->model('notifications/models/Templates_model');

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

    public function install_notifications_lang_update($langs_ids = null)
    {
        if (empty($langs_ids)) {
            return false;
        }
        $this->CI->load->model('Notifications_model');

        $langs_file = $this->CI->Install_model->language_file_read('tickets', 'notifications', $langs_ids);

        if (!$langs_file) {
            log_message('info', 'Empty notifications langs data');

            return false;
        }

        $this->CI->Notifications_model->update_langs($this->notifications, $langs_file, $langs_ids);

        return true;
    }

    public function install_notifications_lang_export($langs_ids = null)
    {
        $this->CI->load->model('Notifications_model');
        $langs = $this->CI->Notifications_model->export_langs($this->notifications, $langs_ids);

        return array('notifications' => $langs);
    }

    public function deinstall_notifications()
    {
        $this->CI->load->model('Notifications_model');
        $this->CI->load->model('notifications/models/Templates_model');
        foreach ($this->notifications['templates'] as $tpl) {
            $this->CI->Templates_model->delete_template_by_gid($tpl['gid']);
        }
        foreach ($this->notifications['notifications'] as $notification) {
            $this->CI->Notifications_model->delete_notification_by_gid($notification['gid']);
        }
    }

    public function install_social_networking()
    {
        ///// add social netorking page
        $this->CI->load->model('social_networking/models/Social_networking_pages_model');
        $page_data = array(
            'controller' => 'tickets',
            'method'     => 'index',
            'name'       => 'Tickets page',
            'data'       => 'a:3:{s:4:"like";a:3:{s:8:"facebook";s:2:"on";s:9:"vkontakte";s:2:"on";s:6:"google";s:2:"on";}s:5:"share";a:4:{s:8:"facebook";s:2:"on";s:9:"vkontakte";s:2:"on";s:8:"linkedin";s:2:"on";s:7:"twitter";s:2:"on";}s:8:"comments";s:1:"1";}',
        );
        $this->CI->Social_networking_pages_model->save_page(null, $page_data);
    }

    public function _arbitrary_installing()
    {
        //// load langs
        $seo_data = array(
            'module_gid'              => 'tickets',
            'model_name'              => 'Tickets_model',
            'get_settings_method'     => 'get_seo_settings',
            'get_rewrite_vars_method' => 'request_seo_rewrite',
            'get_sitemap_urls_method' => 'get_sitemap_xml_urls',
        );
        $this->CI->pg_seo->set_seo_module('tickets', $seo_data);
    }

    public function deinstall_site_map()
    {
        $this->CI->load->model('Site_map_model');
        $this->CI->Site_map_model->delete_sitemap_module('tickets');
    }

    public function deinstall_banners()
    {
        ///// delete banners module
        $this->CI->load->model("banners/models/Banner_group_model");
        $this->CI->Banner_group_model->delete_module("tickets");
    }

    public function deinstall_social_networking()
    {
        ///// delete social netorking page
        $this->CI->load->model('social_networking/models/Social_networking_pages_model');
        $this->CI->Social_networking_pages_model->delete_pages_by_controller('tickets');
    }

    public function install_moderation()
    {
        // Moderation
        $this->CI->load->model('moderation/models/Moderation_type_model');
        foreach ($this->moderation_types as $mtype) {
            $mtype['date_add'] = date("Y-m-d H:i:s");
            $this->CI->Moderation_type_model->save_type(null, $mtype);
        }
    }

    public function install_moderation_lang_update($langs_ids = null)
    {
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }
        $langs_file = $this->CI->Install_model->language_file_read('tickets', 'moderation', $langs_ids);

        if (!$langs_file) {
            log_message('info', 'Empty moderation langs data');

            return false;
        }
        $this->CI->load->model('moderation/models/Moderation_type_model');
        $this->CI->Moderation_type_model->update_langs($this->moderation_types, $langs_file);
    }

    public function install_moderation_lang_export($langs_ids = null)
    {
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }
        $this->CI->load->model('moderation/models/Moderation_type_model');

        return array('moderation' => $this->CI->Moderation_type_model->export_langs($this->moderation_types, $langs_ids));
    }

    public function deinstall_moderation()
    {
        $this->CI->load->model('moderation/models/Moderation_type_model');
        foreach ($this->moderation_types as $mtype) {
            $type = $this->CI->Moderation_type_model->get_type_by_name($mtype["name"]);
            $this->CI->Moderation_type_model->delete_type($type['id']);
        }
    }

    /**
     * Import module languages
     *
     * @param array $langs_ids array languages identifiers
     *
     * @return void
     */
    public function _arbitrary_lang_install($langs_ids = null)
    {
        $langs_file = $this->CI->Install_model->language_file_read("tickets", "arbitrary", $langs_ids);
        if (!$langs_file) {
            log_message("info", "Empty tickets arbitrary langs data");

            return false;
        }

        $post_data = array(
            "title"          => $langs_file["seo_tags_index_title"],
            "keyword"        => $langs_file["seo_tags_index_keyword"],
            "description"    => $langs_file["seo_tags_index_description"],
            "header"         => $langs_file["seo_tags_index_header"],
            "og_title"       => $langs_file["seo_tags_index_og_title"],
            "og_type"        => $langs_file["seo_tags_index_og_type"],
            "og_description" => $langs_file["seo_tags_index_og_description"],
        );
        $this->CI->pg_seo->set_settings("user", "tickets", "index", $post_data);
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
        if (empty($langs_ids)) {
            return false;
        }

        //// arbitrary
        $settings = $this->CI->pg_seo->get_settings("user", "tickets", "index", $langs_ids);
        $arbitrary_return["seo_tags_index_title"] = $settings["title"];
        $arbitrary_return["seo_tags_index_keyword"] = $settings["keyword"];
        $arbitrary_return["seo_tags_index_description"] = $settings["description"];
        $arbitrary_return["seo_tags_index_header"] = $settings["header"];
        $arbitrary_return["seo_tags_index_og_title"] = $settings["og_title"];
        $arbitrary_return["seo_tags_index_og_type"] = $settings["og_type"];
        $arbitrary_return["seo_tags_index_og_description"] = $settings["og_description"];

        return array("arbitrary" => $arbitrary_return);
    }

    public function _arbitrary_deinstalling()
    {
        $this->CI->load->model('Menu_model');
        $this->CI->pg_seo->delete_seo_module('tickets');
    }
}
