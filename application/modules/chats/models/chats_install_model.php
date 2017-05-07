<?php

namespace Pg\Modules\Chats\Models;

/**
 * Chats install model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Alexander Batukhtin <abatukhtin@pilotgroup.net>
 * */
class Chats_install_model extends \Model
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
                                "chats_menu_item" => array("action" => "create", "link" => "admin/chats", 'icon' => 'video-camera', "status" => 1, "sorter" => 3),
                            ),
                        ),
                    ),
                ),
            ),
        ),
        'user_top_menu' => array(
            'action' => 'none',
            'name'   => '',
            'items'  => array(
                'user-menu-communication' => array(
                    'action' => 'none',
                    'name'   => '',
                    'items'  => array(
                        'chat_item' => array(
                            'action' => 'create',
                            'link'   => 'chats/',
                            'status' => 0,
                            'sorter' => 11,
                        ),
                    ),
                ),
            ),
        ),
    );
    private $_chats = array(
        'cometchat',
        'flashchat',
        'pg_videochat',
        'oovoochat',
    );
    private $_chat_models = array();

    /**
     * Constructor
     *
     * @return Install object
     */
    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
        foreach ($this->_chats as $chat) {
            if (file_exists(__DIR__ . "/chats/$chat" . EXT)) {
                $this->CI->load->model("chats/models/chats/$chat");
                $this->_chat_models[] = $this->CI->{$chat};
            }
        }
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
        $langs_file = $this->CI->Install_model->language_file_read('chats', 'menu', $langs_ids);
        if (!$langs_file) {
            log_message('info', 'Empty menu langs data');

            return false;
        }
        $this->CI->load->helper('menu');
        foreach (array_keys($this->menu) as $gid) {
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
        foreach (array_keys($this->menu) as $gid) {
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

    private function _add_chats()
    {
        $this->CI->load->model('Chats_model');
        foreach ($this->_chat_models as $chat) {
            $this->CI->Chats_model->save($chat->as_array(true));
        }
    }

    /**
     * Install data of cronjob module
     *
     * @return void
     */
    public function install_cronjob()
    {
        ///// cronjob
        $this->CI->load->model('Cronjob_model');
        $cron_data = array(
            "name"     => "Canceled Video chats",
            "module"   => "chats",
            "model"    => "Chats_model",
            "method"   => "cron_canceled_chats",
            "cron_tab" => "*/15 * * * *",
            "status"   => "1",
        );
        $this->CI->Cronjob_model->save_cron(null, $cron_data);
        $cron_data = array(
            "name"     => "Time alert for video chat",
            "module"   => "chats",
            "model"    => "Chats_model",
            "method"   => "cron_send_alert_per_hour",
            "cron_tab" => "*/15 * * * *",
            "status"   => "1",
        );
        $this->CI->Cronjob_model->save_cron(null, $cron_data);
    }

    /**
     * Uninstall data of cronjob module
     *
     * @return void
     */
    public function deinstall_cronjob()
    {
        $this->CI->load->model('Cronjob_model');
        $cron_data = array();
        $cron_data["where"]["module"] = "chats";
        $this->CI->Cronjob_model->delete_cron_by_param($cron_data);
    }

    public function _arbitrary_installing()
    {
        $this->_add_chats();
        
        $seo_data = array(
            'module_gid' => 'chats',
            'model_name' => 'Chats_model',
            'get_settings_method' => 'get_seo_settings',
            'get_rewrite_vars_method' => 'request_seo_rewrite',
            'get_sitemap_urls_method' => 'get_sitemap_xml_urls',
        );
        $this->CI->pg_seo->set_seo_module('chats', $seo_data);
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
        $langs_file = $this->CI->Install_model->language_file_read('chats', 'arbitrary', $langs_ids);
        if (!$langs_file) {
            log_message('info', 'Empty chats arbitrary langs data');

            return false;
        }

        $post_data = array(
            'title'          => $langs_file['seo_tags_index_title'],
            'keyword'        => $langs_file['seo_tags_index_keyword'],
            'description'    => $langs_file['seo_tags_index_description'],
            'header'         => $langs_file['seo_tags_index_header'],
            'og_title'       => $langs_file['seo_tags_index_og_title'],
            'og_type'        => $langs_file['seo_tags_index_og_type'],
            'og_description' => $langs_file['seo_tags_index_og_description'],
        );
        $this->CI->pg_seo->set_settings('user', 'chats', 'index', $post_data);
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

        // arbitrary
        $arbitrary_return = array();
        $seo_settings = $this->pg_seo->get_all_settings_from_cache('user', 'chats');
        $lang_ids = array_keys($this->CI->pg_language->languages);
        foreach ($seo_settings as $seo_page) {
            $prefix = 'seo_tags_' . $seo_page['method'];
            foreach ($lang_ids as $lang_id) {
                $meta = 'meta_' . $lang_id;
                $arbitrary_return[$prefix . '_header'][$lang_id] = $seo_page[$meta]['header'];
                $arbitrary_return[$prefix . '_title'][$lang_id] = $seo_page[$meta]['title'];
            }
        }

        return array('arbitrary' => $arbitrary_return);
    }

    public function _arbitrary_deinstalling()
    {
        $this->CI->pg_seo->delete_seo_module('chats');
    }
}
