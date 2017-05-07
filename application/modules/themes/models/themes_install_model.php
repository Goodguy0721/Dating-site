<?php

namespace Pg\Modules\Themes\Models;

/**
 * Themes install model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * */
class Themes_install_model extends \Model
{
    protected $ci;
    protected $menu = array(
        'admin_menu' => array(
            'action' => 'none',
            'items'  => array(
                'settings_items' => array(
                    'action' => 'none',
                    'items'  => array(
                        'interface-items' => array(
                            'action' => 'none',
                            'items'  => array(
                                'themes_menu_item' => array('action' => 'create', 'link' => 'admin/themes/installed_themes', 'status' => 1, 'sorter' => 3),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    );
    protected $moderators_methods = array(
        array('module' => 'themes', 'method' => 'installed_themes', 'is_default' => 1),
        array('module' => 'themes', 'method' => 'enable_themes', 'is_default' => 0),
    );

    /**
     * Dynamic blocks configuration
     *
     * @var array
     */
    protected $dynamic_blocks = array();

    /**
     * Constructor
     *
     * @return Install object
     */
    public function __construct()
    {
        parent::__construct();
        $this->ci = &get_instance();

        if (SOCIAL_MODE) {
            $this->dynamic_blocks = include MODULEPATH . 'themes/install/dynamic_blocks_social.php';
        } else {
            $this->dynamic_blocks = include MODULEPATH . 'themes/install/dynamic_blocks_dating.php';
        }
    }

    public function _validate_requirements()
    {
        $result = array('data' => array(), 'result' => true);

        //check for GD library
        $good = extension_loaded('gd');
        $result["data"][] = array(
            "name"   => "GD library (works with graphics and images) is installed",
            "value"  => $good ? "Yes" : "No",
            "result" => $good,
        );
        $result["result"] = $result["result"] && $good;

        return $result;
    }

    public function install_menu()
    {
        if (SOCIAL_MODE === true) {
            return false;
        }
        $this->ci->load->helper('menu');
        foreach ($this->menu as $gid => $menu_data) {
            $this->menu[$gid]['id'] = linked_install_set_menu($gid, $menu_data["action"], $menu_data["name"]);
            linked_install_process_menu_items($this->menu, 'create', $gid, 0, $this->menu[$gid]["items"]);
        }
    }

    public function install_menu_lang_update($langs_ids = null)
    {
        if (empty($langs_ids) || SOCIAL_MODE === true) {
            return false;
        }
        $langs_file = $this->ci->Install_model->language_file_read('themes', 'menu', $langs_ids);

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
        if (empty($langs_ids) || SOCIAL_MODE === true) {
            return false;
        }
        $this->ci->load->helper('menu');

        $return = array();
        foreach ($this->menu as $gid => $menu_data) {
            $temp = linked_install_process_menu_items($this->menu, 'export', $gid, 0, $this->menu[$gid]["items"], $gid, $langs_ids);
            $return = array_merge($return, $temp);
        }

        return array("menu" => $return);
    }

    public function deinstall_menu()
    {
        if (SOCIAL_MODE === true) {
            return false;
        }
        $this->ci->load->helper('menu');
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
        $this->ci->load->model('moderators/models/Moderators_model');

        foreach ($this->moderators_methods as $method) {
            $this->ci->Moderators_model->save_method(null, $method);
        }
    }

    public function install_moderators_lang_update($langs_ids = null)
    {
        $langs_file = $this->ci->Install_model->language_file_read('themes', 'moderators', $langs_ids);

        // install moderators permissions
        $this->ci->load->model('moderators/models/Moderators_model');
        $params['where']['module'] = 'themes';
        $methods = $this->ci->Moderators_model->get_methods_lang_export($params);

        foreach ($methods as $method) {
            if (!empty($langs_file[$method['method']])) {
                $this->ci->Moderators_model->save_method($method['id'], array(), $langs_file[$method['method']]);
            }
        }
    }

    public function install_moderators_lang_export($langs_ids)
    {
        $this->ci->load->model('moderators/models/Moderators_model');
        $params['where']['module'] = 'themes';
        $methods = $this->ci->Moderators_model->get_methods_lang_export($params, $langs_ids);
        foreach ($methods as $method) {
            $return[$method['method']] = $method['langs'];
        }

        return array('moderators' => $return);
    }

    public function deinstall_moderators()
    {
        // delete moderation methods in moderators
        $this->ci->load->model('moderators/models/Moderators_model');
        $params['where']['module'] = 'themes';
        $this->ci->Moderators_model->delete_methods($params);
    }

    public function _arbitrary_installing()
    {
    }

    public function _arbitrary_deinstalling()
    {
        $this->ci->load->model('themes/models/Themes_model');
        $this->ci->Themes_model->clearAllSet();
    }

    /*
     * Dynamic blocks methods
     *
     */
    public function install_dynamic_blocks()
    {
        $this->ci->load->model('Dynamic_blocks_model');
        $this->ci->Dynamic_blocks_model->installBatch($this->dynamic_blocks);
    }

    public function install_dynamic_blocks_lang_update($langs_ids = null)
    {
        $this->ci->load->model('Dynamic_blocks_model');

        return $this->ci->Dynamic_blocks_model->updateLangsByModuleBlocks($this->dynamic_blocks, $langs_ids);
    }

    public function install_dynamic_blocks_lang_export($langs_ids = null)
    {
        $this->ci->load->model('Dynamic_blocks_model');

        return array(
            'dynamic_blocks' => $this->ci->Dynamic_blocks_model->export_langs($this->dynamic_blocks, $langs_ids),
        );
    }

    public function deinstall_dynamic_blocks()
    {
        $this->ci->load->model('Dynamic_blocks_model');
        foreach ($this->dynamic_blocks as $block) {
            $this->ci->Dynamic_blocks_model->delete_block_by_gid($block['gid']);
        }
    }
}
