<?php

namespace Pg\Modules\Mobile\Models;

/**
 * Mobile install model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * */
class Mobile_install_model extends \Model
{
    protected $CI;

    /**
     * Menu configuration
     */
    protected $menu = array();

    protected $menu_dating = array(
        'admin_menu' => array(
            'action' => 'none',
            'name'   => 'Mobile section menu',
            'items'  => array(
                'content_items' => array(
                    'action' => 'none',
                    'name'   => '',
                    'items'  => array(
                        'add_ons_items' => array(
                            'action' => 'none',
                            'name'   => '',
                            'items'  => array(
                                'mobile_menu_item' => array(
                                    'action' => 'create',
                                    'link'   => 'admin/mobile',
                                    'status' => 1,
                                    'sorter' => 9,
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
        'user_footer_menu' => array(
            'action' => 'none',
            'items'  => array(
                'footer-menu-links-item' => array(
                    'action' => 'none',
                    'items'  => array(
                        'footer-menu-mobile-item' => array('action' => 'create', 'link' => 'm/', 'is_external' => true, 'status' => 1, 'sorter' => 1),
                    ),
                ),
            ),
        ),
    );

    protected $menu_social = array(
        'admin_menu' => array(
            'action' => 'none',
            'name'   => 'Mobile section menu',
            'items'  => array(
                'content_items' => array(
                    'action' => 'none',
                    'name'   => '',
                    'items'  => array(
                        'add_ons_items' => array(
                            'action' => 'none',
                            'name'   => '',
                            'items'  => array(
                                'mobile_menu_item' => array(
                                    'action' => 'create',
                                    'link'   => 'admin/mobile',
                                    'status' => 1,
                                    'sorter' => 9,
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
        'user_footer_menu' => array(
            'action' => 'none',
            'items'  => array(),
        ),
    );

    public function __construct()
    {
        parent::__construct();
        $this->CI = get_instance();

        if (SOCIAL_MODE) {
            $this->menu = $this->menu_social;
        } else {
            $this->menu = $this->menu_dating;
        }
        $this->menu['user_footer_menu']['items']['footer-menu-mobile-item']['link'] = site_url() . 'm';
    }

    protected function setPaths()
    {
        $mobile_path = SITE_PHYSICAL_PATH . 'm/';
        $files = array(
            array(
                'path'    => $mobile_path . 'index.html',
                'replace' => array(
                    '[m_subfolder]' => '/' . SITE_SUBFOLDER . 'm',
                ),
            ),
            array(
                'path'    => $mobile_path . 'scripts/app.js',
                'replace' => array(
                    '[api_virtual_path]' => SITE_VIRTUAL_PATH . 'api',
                ),
            ),
        );
        foreach ($files as $file) {
            $file_contents = file_get_contents($file['path']);
            if ($file_contents) {
                $file_contents = str_replace(array_keys($file['replace']), array_values($file['replace']), $file_contents);
                file_put_contents($file['path'], $file_contents);
            }
        }
    }

    public function _arbitrary_installing()
    {
        $this->setPaths();
    }

    public function _arbitrary_lang_install($langs_ids = null)
    {
        $langs_file = $this->CI->Install_model->language_file_read('mobile', 'mobile_app', $langs_ids);
        if (!$langs_file) {
            log_message('info', 'Empty mobile app langs data');

            return false;
        }

        foreach ($langs_file as $gid => $ldata) {
            if (!empty($ldata)) {
                $this->CI->pg_language->pages->set_string_langs('mobile_app', $gid, $ldata, array_keys($ldata));
            }
        }
    }
    
    public function _arbitrary_lang_export($langs_ids = null)
    {
        $langs_file = $this->CI->Install_model->language_file_read('mobile', 'mobile_app', $langs_ids);
        
        $gids = array();
        foreach($langs_file as $key => $lang) {
            $gids[] = $key;
        }
        
        $return = $this->CI->pg_language->export_langs('mobile_app', $gids, $langs_ids);
        
        return array('mobile_app' => $return);
    }

    /**
     * Install menu data
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
     */
    public function install_menu_lang_update($langs_ids = null)
    {
        if (empty($langs_ids)) {
            return false;
        }
        $langs_file = $this->CI->Install_model->language_file_read(Mobile_model::MODULE_GID, 'menu', $langs_ids);

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
}
