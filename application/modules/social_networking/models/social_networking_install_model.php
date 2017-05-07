<?php

namespace Pg\Modules\Social_networking\Models;

/**
 * Social networking install model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * */
class Social_networking_install_model extends \Model
{
    public $ci;
    private $menu = array(
        'admin_menu' => array(
            'action' => 'none',
            'items'  => array(
                'settings_items' => array(
                    'action' => 'none',
                    'items'  => array(
                        'system-items' => array(
                            'action' => 'none',
                            'items'  => array(
                                'social_networking_menu_item' => array('action' => 'create', 'link' => 'admin/social_networking/services/', 'status' => 1, 'sorter' => 1),
                            ),
                        ),
                    ),
                ),
            ),
        ),
        'admin_social_networking_menu' => array(
            'action' => 'create',
            'name'   => 'Services section menu',
            'items'  => array(
                'sn_services_item' => array('action' => 'create', 'link' => 'admin/social_networking/services/', 'status' => 1),
                'sn_pages_item'    => array('action' => 'create', 'link' => 'admin/social_networking/pages/', 'status' => 1),
            ),
        ),
    );

    public function __construct()
    {
        parent::__construct();
        $this->ci = &get_instance();
        // load langs
        $this->ci->load->model('Install_model');
    }

    public function install_menu()
    {
        $this->ci->load->helper('menu');
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
        $langs_file = $this->ci->Install_model->language_file_read('social_networking', 'menu', $langs_ids);

        if (!$langs_file) {
            log_message('info', 'Empty menu langs data');

            return false;
        }

        $this->ci->load->helper('menu');

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
        $this->ci->load->helper('menu');

        $return = array();
        foreach ($this->menu as $gid => $menu_data) {
            $temp = linked_install_process_menu_items($this->menu, 'export', $gid, 0, $this->menu[$gid]["items"], $gid, $langs_ids);
            $return = array_merge($return, $temp);
        }

        return array('menu' => $return);
    }

    public function deinstall_menu()
    {
        $this->ci->load->helper('menu');
        foreach ($this->menu as $gid => $menu_data) {
            if ($menu_data['action'] == 'create') {
                linked_install_set_menu($gid, 'delete');
            } else {
                linked_install_delete_menu_items($gid, $this->menu[$gid]['items']);
            }
        }
    }

    private function addServices()
    {
        $this->ci->load->model('social_networking/models/Social_networking_services_model');

        // Serivces
        $service_data = array(
            'id'             => 1,
            'gid'            => 'facebook',
            'name'           => 'Facebook',
            'authorize_url'  => 'https://www.facebook.com/dialog/oauth',
            'access_key_url' => 'https://graph.facebook.com/oauth/access_token',
            'oauth_enabled'  => 1,
            'oauth_version'  => 2,
            'app_enabled'    => 1,
            'status'         => 0,
            'date_add'       => '2012-00-00 00:00:00',
        );
        $this->ci->Social_networking_services_model->save_service(null, $service_data);

        $service_data = array(
            'id'             => 2,
            'gid'            => 'vkontakte',
            'name'           => 'Vk.com',
            'authorize_url'  => 'http://oauth.vk.com/authorize',
            'access_key_url' => 'https://api.vk.com/oauth/access_token',
            'oauth_enabled'  => 1,
            'oauth_version'  => 2,
            'app_enabled'    => 1,
            'status'         => 0,
            'date_add'       => '2012-00-00 00:00:00',
        );
        $this->ci->Social_networking_services_model->save_service(null, $service_data);

        $service_data = array(
            'id'             => 3,
            'gid'            => 'google',
            'name'           => 'Google',
            'authorize_url'  => 'https://accounts.google.com/o/oauth2/auth',
            'access_key_url' => 'https://accounts.google.com/o/oauth2/token',
            'oauth_enabled'  => 1,
            'oauth_version'  => 2,
            'app_enabled'    => 1,
            'status'         => 0,
            'date_add'       => '2012-00-00 00:00:00',
        );
        $this->ci->Social_networking_services_model->save_service(null, $service_data);

        $service_data = array(
            'id'            => 4,
            'gid'           => 'linkedin',
            'name'          => 'LinkedIn',
            'oauth_enabled' => 0,
            'status'        => 0,
            'app_enabled'   => 0,
            'date_add'      => '2012-00-00 00:00:00',
        );
        $this->ci->Social_networking_services_model->save_service(null, $service_data);

        $service_data = array(
            'id'            => 5,
            'gid'           => 'twitter',
            'name'          => 'Twitter',
            'oauth_enabled' => 1,
            'oauth_version' => 1,
            'status'        => 0,
            'app_enabled'   => 1,
            'date_add'      => '2012-00-00 00:00:00',
        );
        $this->ci->Social_networking_services_model->save_service(null, $service_data);
    }

    public function _arbitrary_installing()
    {
        $this->addServices();

        // add social netorking page
        $this->ci->load->model('social_networking/models/Social_networking_pages_model');
        /*$page_data = array(
            'controller' => 'start',
            'method'     => 'index',
            'name'       => 'Index page',
        );
        $this->ci->Social_networking_pages_model->save_page(null, $page_data);*/
    }

    public function _arbitrary_deinstalling()
    {
    }

    public function _arbitrary_lang_install($langs_ids)
    {
    }

    public function _arbitrary_lang_export($langs_ids = null)
    {
    }
}
