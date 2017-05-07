<?php

namespace Pg\Modules\Moderation\Models;

/**
 * Moderation install model
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
class Moderation_install_model extends \Model
{
    public $CI;
    private $menu = array(
        'admin_menu' => array(
            'action' => 'none',
            'items'  => array(
                'system_items' => array(
                    'action' => 'none',
                    'items'  => array(
            'moderation_menu_item' => array('action' => 'create', 'link' => 'admin/moderation', 'icon' => 'flag', 'status' => 1, 'sorter' => 3, 'indicator_gid' => 'new_moderation_item'),
                    ),
                ),
            ),
        ),
        'admin_moderation_menu' => array(
            'action' => 'create',
            'name'   => 'Moderation section menu',
            'items'  => array(
                'object_list_item'    => array('action' => 'create', 'link' => 'admin/moderation/index', 'status' => 1),
                'moder_settings_item' => array('action' => 'create', 'link' => 'admin/moderation/settings', 'status' => 1),
                'badwords_item'       => array('action' => 'create', 'link' => 'admin/moderation/badwords', 'status' => 1),
            ),
        ),
    );
    private $moderators_methods = array(
        array('module' => 'moderation', 'method' => 'index', 'is_default' => 1),
        array('module' => 'moderation', 'method' => 'settings', 'is_default' => 0),
        array('module' => 'moderation', 'method' => 'badwords', 'is_default' => 0),
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
        //// load langs
        $this->CI->load->model('Install_model');
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
        $langs_file = $this->CI->Install_model->language_file_read('moderation', 'menu', $langs_ids);

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

    /**
     * Moderators module methods
     */
    public function install_moderators()
    {
        // install moderators permissions
        $this->CI->load->model('moderators/models/Moderators_model');

        foreach ($this->moderators_methods as $method) {
            $this->CI->Moderators_model->save_method(null, $method);
        }
    }

    public function install_moderators_lang_update($langs_ids = null)
    {
        $langs_file = $this->CI->Install_model->language_file_read('moderation', 'moderators', $langs_ids);

        // install moderators permissions
        $this->CI->load->model('moderators/models/Moderators_model');
        $params['where']['module'] = 'moderation';
        $methods = $this->CI->Moderators_model->get_methods_lang_export($params);

        foreach ($methods as $method) {
            if (!empty($langs_file[$method['method']])) {
                $this->CI->Moderators_model->save_method($method['id'], array(), $langs_file[$method['method']]);
            }
        }
    }

    public function install_moderators_lang_export($langs_ids)
    {
        $this->CI->load->model('moderators/models/Moderators_model');
        $params['where']['module'] = 'moderation';
        $methods =  $this->CI->Moderators_model->get_methods_lang_export($params, $langs_ids);
        foreach ($methods as $method) {
            $return[$method['method']] = $method['langs'];
        }

        return array('moderators' => $return);
    }

    public function deinstall_moderators()
    {
        // delete moderation methods in moderators
        $this->CI->load->model('moderators/models/Moderators_model');
        $params['where']['module'] = 'moderation';
        $this->CI->Moderators_model->delete_methods($params);
    }

    public function _arbitrary_installing()
    {
    }

    public function _arbitrary_deinstalling()
    {
    }
}
