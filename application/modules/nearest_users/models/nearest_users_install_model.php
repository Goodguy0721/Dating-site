<?php

namespace Pg\Modules\Nearest_users\Models;

/**
 * Nearest users module
 *
 * @package 	PG_Dating
 *
 * @copyright 	Copyright (c) 2000-2015 PG_Dating - php dating software
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */

/**
 * Nearest users install model
 *
 * @package 	PG_Dating
 * @subpackage 	Nearest users
 *
 * @category	models
 *
 * @copyright 	Copyright (c) 2000-2015 PG_Dating - php dating software
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
class Nearest_users_install_model extends \Model
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
        'admin_menu' => array(
            'action' => 'none',
            'name'   => 'Nearest users menu',
            'items'  => array(
                'content_items' => array(
                    'action' => 'none',
                    'name'   => '',
                    'items'  => array(
                        'add_ons_items' => array(
                            'action' => 'none',
                            'items'  => array(
                                'nearest_users_menu_item' => array('action' => 'create', 'link' => 'admin/nearest_users', 'status' => 1, 'sorter' => 9),
                            ),
                        ),
                    ),
                ),
            ),
        ),
        'user_top_menu' => array(
            'action' => 'none',
            'items'  => array(
                'user-menu-people' => array(
                    'action' => 'none',
                    'items'  => array(
                        'nearest_users_item' => array('action' => 'create', 'link' => 'nearest_users/index', 'icon' => '', 'status' => 1, 'sorter' => 40),
                    ),
                ),
            ),
        ),
    );

    /**
     * Fields seo
     *
     * @var array
     */
    protected $seo_data = array(
        'module_gid'              => 'nearest_users',
        'model_name'              => 'Nearest_users_model',
        'get_settings_method'     => 'getSeoSettings',
        'get_rewrite_vars_method' => 'requestSeoRewrite',
        'get_sitemap_urls_method' => 'getSitemapXmlUrls',
    );

    protected $_seo_pages = array(
        'index',
    );

    /**
     * Fields depended on languages
     *
     * @var array
     */
    protected $lang_dm_data = array(
        array(
            'module'        => 'nearest_users',
            'model'         => 'Nearest_users_model',
            'method_add'    => 'lang_dedicate_module_callback_add',
            'method_delete' => 'lang_dedicate_module_callback_delete',
        ),
    );

    private $_geomap = array(
        array(
            'map_settings' => array(
                'use_type_selector' => 0,
                'use_panorama'      => 0,
                'use_router'        => 0,
                'use_searchbox'     => 0,
                'use_search_radius' => 0,
                'use_search_auto'   => 0,
                'use_show_details'  => 0,
                'use_amenities'     => 0,
                'amenities'         => array(),
                'zoom'              => 10,
                'view_type'         => 4,
                'lat'               => 55.7497723,
                'lon'               => 37.6242685,
            ),
            'settings' => array(
                'map_gid'   => 'googlemapsv3',
                'id_user'   => 0,
                'id_object' => 0,
                'gid'       => 'nearest_view',
            ),
        ),
    );

    /**
     * Constructor
     *
     * @return nearest_users_install_model
     */
    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
        //// load langs
        $this->CI->load->model('Install_model');
    }

    /**
     * Install geomap links
     *
     * @return void
     */
    public function install_geomap()
    {
        //add geomap settings
        $this->CI->load->model('geomap/models/Geomap_settings_model');
        foreach ($this->_geomap as $geomap) {
            $validate_data = $this->CI->Geomap_settings_model->validate_settings($geomap['map_settings']);
            if (!empty($validate_data['errors'])) {
                continue;
            }
            $this->CI->Geomap_settings_model->save_settings($geomap['settings']['map_gid'], $geomap['settings']['id_user'], $geomap['settings']['id_object'], $geomap['settings']['gid'], $validate_data['data']);
        }
    }

    /**
     * Install languages
     *
     * @param array $langs_ids
     *
     * @return void
     */
    public function install_geomap_lang_update($langs_ids = null)
    {
        if (empty($langs_ids)) {
            return false;
        }

        $langs_file = $this->CI->Install_model->language_file_read('nearest_users', 'geomap', $langs_ids);

        if (!$langs_file) {
            log_message('info', 'Empty geomap langs data');

            return false;
        }

        $this->CI->load->model('geomap/models/Geomap_settings_model');

        $gids = array();
        foreach ($this->_geomap as $geomap) {
            $gids[$geomap['settings']['gid']] = 'map_' . $geomap['settings']['gid'];
        }
        $this->CI->Geomap_settings_model->update_lang($gids, $langs_file, $langs_ids);
    }

    /**
     * Import languages
     *
     * @param array $langs_ids
     *
     * @return void
     */
    public function install_geomap_lang_export($langs_ids = null)
    {
        $this->CI->load->model('geomap/models/Geomap_settings_model');

        $gids = array();
        foreach ($this->_geomap as $geomap) {
            $gids[$geomap['settings']['gid']] = 'map_' . $geomap['settings']['gid'];
        }
        $langs = $this->CI->Geomap_settings_model->export_lang($gids, $langs_ids);

        return array('geomap' => $langs);
    }

    /**
     * Uninstall geomap links
     *
     * @return void
     */
    public function deinstall_geomap()
    {
        $this->CI->load->model("geomap/models/Geomap_settings_model");
        foreach ($this->_geomap as $geomap) {
            $this->CI->Geomap_settings_model->delete_settings($geomap['settings']['map_gid'], $geomap['settings']['id_user'], $geomap['settings']['id_object'], $geomap['settings']['gid']);
        }
    }

    /**
     * Install data of menu module
     *
     * @return void
     */
    public function install_menu()
    {
        $this->CI->load->helper('menu');
        foreach ($this->menu as $gid => $menu_data) {
            $this->menu[$gid]['id'] = linked_install_set_menu($gid, $menu_data['action'], $menu_data['name']);
            linked_install_process_menu_items($this->menu, 'create', $gid, 0, $this->menu[$gid]['items']);
        }
    }

    /**
     * Install menu languages
     *
     * @param array $langs_ids languages identifiers
     *
     * @return bool
     */
    public function install_menu_lang_update($langs_ids = null)
    {
        if (empty($langs_ids)) {
            return false;
        }
        $langs_file = $this->CI->Install_model->language_file_read('nearest_users', 'menu', $langs_ids);

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

    /**
     * Export menu languages
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
        $this->CI->load->helper('menu');

        $return = array();
        foreach ($this->menu as $gid => $menu_data) {
            $temp = linked_install_process_menu_items($this->menu, 'export', $gid, 0, $this->menu[$gid]['items'], $gid, $langs_ids);
            $return = array_merge($return, $temp);
        }

        return array('menu' => $return);
    }

    /**
     * Uninstall menu languages
     *
     * @return void
     */
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
     * Install site map data
     *
     * @return void
     */
    public function install_site_map()
    {
        $this->CI->load->model('Site_map_model');
        $site_map_data = array(
            'module_gid'      => 'nearest_users',
            'model_name'      => 'Nearest_users_model',
            'get_urls_method' => 'getSitemapUrls',
        );
        $this->CI->Site_map_model->set_sitemap_module('nearest_users', $site_map_data);
    }

    /**
     * Uninstall site map data
     *
     * @return void
     */
    public function deinstall_site_map()
    {
        $this->CI->load->model('Site_map_model');
        $this->CI->Site_map_model->delete_sitemap_module('nearest_users');
    }

    /**
     * Install banners
     *
     * @return void
     */
    public function install_banners()
    {
        $this->CI->load->model("banners/models/Banner_group_model");
        $this->CI->load->model('Nearest_users_model');
        $this->CI->Banner_group_model->set_module("nearest_users", "Nearest_users_model", "_bannerAvailablePages");
        $group_id = $this->CI->Banner_group_model->get_group_id_by_gid('users_groups');
        $pages = $this->CI->Nearest_users_model->_bannerAvailablePages();
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

    /**
     * Uninstall banners
     *
     * @return void
     */
    public function deinstall_banners()
    {
        $this->CI->load->model("banners/models/Banner_group_model");
        $this->CI->Banner_group_model->delete_module("nearest_users");
        $group_id = $this->CI->Banner_group_model->get_group_id_by_gid('users_groups');
        $this->CI->Banner_group_model->delete($group_id);
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
        
        $this->CI->pg_seo->set_seo_module('nearest_users', array(
            'module_gid'              => 'nearest_users',
            'model_name'              => 'Nearest_users_model',
            'get_settings_method'     => 'getSeoSettings',
            'get_rewrite_vars_method' => 'requestSeoRewrite',
            'get_sitemap_urls_method' => 'getSitemapXmlUrls',
        ));

        return;
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
        $langs_file = $this->CI->Install_model->language_file_read('nearest_users', 'arbitrary', $langs_ids);
        if (!$langs_file) {
            log_message('info', 'Empty blacklist arbitrary langs data');

            return false;
        }
        foreach ($this->_seo_pages as $page) {
            $post_data = array(
                'title'          => $langs_file["seo_tags_{$page}_title"],
                'keyword'        => $langs_file["seo_tags_{$page}_keyword"],
                'description'    => $langs_file["seo_tags_{$page}_description"],
                'header'         => $langs_file["seo_tags_{$page}_header"],
                'og_title'       => $langs_file["seo_tags_{$page}_og_title"],
                'og_type'        => $langs_file["seo_tags_{$page}_og_type"],
                'og_description' => $langs_file["seo_tags_{$page}_og_description"],
            );
            $this->CI->pg_seo->set_settings('user', 'nearest_users', $page, $post_data);
        }
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
        $arbitrary_return = array();
        $seo_settings = $this->pg_seo->get_all_settings_from_cache('user', 'nearest_users');
        $lang_ids = array_keys($this->CI->pg_language->languages);
        foreach ($seo_settings as $seo_page) {
            $prefix = 'seo_tags_' . $seo_page['method'];
            foreach ($lang_ids as $lang_id) {
                $meta = 'meta_' . $lang_id;
                $og = 'og_' . $lang_id;
                $arbitrary_return[$prefix . '_title'][$lang_id] = $seo_page[$meta]['title'];
                $arbitrary_return[$prefix . '_keyword'][$lang_id] = $seo_page[$meta]['keyword'];
                $arbitrary_return[$prefix . '_description'][$lang_id] = $seo_page[$meta]['description'];
                $arbitrary_return[$prefix . '_header'][$lang_id] = $seo_page[$meta]['header'];
                $arbitrary_return[$prefix . '_og_title'][$lang_id] = $seo_page[$og]['og_title'];
                $arbitrary_return[$prefix . '_og_type'][$lang_id] = $seo_page[$og]['og_type'];
                $arbitrary_return[$prefix . '_og_description'][$lang_id] = $seo_page[$og]['og_description'];
            }
        }

        return array('arbitrary' => $arbitrary_return);
    }

    /**
     * Uninstall module data
     *
     * @return void
     */
    public function _arbitrary_deinstalling()
    {
        $this->CI->pg_seo->delete_seo_module('nearest_users');

        /// delete entries in dedicate modules
        foreach ($this->lang_dm_data as $lang_dm_data) {
            $this->CI->pg_language->delete_dedicate_modules_entry(array('where' => $lang_dm_data));
        }
    }
}
