<?php

/**
 * Site map install model
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
class Site_map_install_model extends Model
{
    public $CI;

    protected $menu = array(
        'user_footer_menu' => array(
            'action' => 'none',
            'items'  => array(
                'footer-menu-help-item' => array(
                    'action' => 'none',
                    'items'  => array(
                        'footer-menu-map-item' => array('action' => 'create', 'link' => 'site_map/', 'status' => 1, 'sorter' => 2),
                    ),
                ),
            ),
        ),
    );

    private $_seo_pages = array(
        'index',
    );

    /**
     * Constructor
     *
     * @return Install object
     */
    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
    }

    public function install_menu()
    {
        $this->CI->load->helper('menu');
        foreach ($this->menu as $gid => $menu_data) {
            $this->menu[$gid]['id'] = linked_install_set_menu($gid, $menu_data["action"], $menu_data["name"]);
            linked_install_process_menu_items($this->menu, 'create', $gid, 0, $this->menu[$gid]["items"]);
        }
    }

    public function install_menu_lang_update($langs_ids = null)
    {
        if (empty($langs_ids)) {
            return false;
        }
        $langs_file = $this->CI->Install_model->language_file_read('site_map', 'menu', $langs_ids);

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

        return array("menu" => $return);
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

    public function _arbitrary_installing()
    {
        $seo_data = array(
            'module_gid'              => 'site_map',
            'model_name'              => 'Site_map_model',
            'get_settings_method'     => 'get_seo_settings',
            'get_rewrite_vars_method' => 'request_seo_rewrite',
            'get_sitemap_urls_method' => 'get_sitemap_xml_urls',
        );
        $this->CI->pg_seo->set_seo_module('site_map', $seo_data);
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
        $langs_file = $this->CI->Install_model->language_file_read('site_map', 'arbitrary', $langs_ids);
        if (!$langs_file) {
            log_message('info', 'Empty site_map arbitrary langs data');

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
                'priority'       => 0.8,
            );
            $this->CI->pg_seo->set_settings('user', 'site_map', $page, $post_data);
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
        $seo_settings = $this->pg_seo->get_all_settings_from_cache('user', 'site_map');
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

    public function _arbitrary_deinstalling()
    {
        $this->CI->pg_seo->delete_seo_module('site_map');
    }
}
