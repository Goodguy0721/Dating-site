<?php

/**
 * Statistics module
 *
 * @package 	PG_Dating
 *
 * @copyright 	Copyright (c) 2000-2014 PG Dating Pro - php dating software
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
namespace Pg\Modules\Statistics\Models;

/**
 * Statistics install model
 *
 * @package 	PG_Dating
 * @subpackage 	Statistics
 *
 * @category	models
 *
 * @copyright 	Copyright (c) 2000-2014 PG Dating Pro - php dating software
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
class Statistics_install_model extends \Model
{
    /**
     * Link to CodeIgniter object
     *
     * @var object
     */
    protected $ci;

    /**
     * Menu configuration
     *
     * "<menu_gid>" => array(
     *     "action" => "<create|none>",
     *     "name" => "<menu_name>",
     *     "items" => array(
     *         "<menu_item_gid>" => array(
     *         "action" => "<create|none>",
     *         "name" => "<menu_item_gid>",
     *         "items" => array(
     *             ...
     *         )
     *     )
     * )
     *
     * @var array
     */
    protected $menu = array(
        'admin_menu' => array(
            'action' => 'none',
            'items'  => array(
                'settings_items' => array(
                    'action' => 'none',
                    'items'  => array(
                        'system-items' => array(
                            'action' => 'none',
                            'items'  => array(
                                'statistics_sett_menu_item' => array(
                                    'action' => 'create', 
                                    'link' => 'admin/statistics/index', 
                                    'status' => 1, 
                                    'sorter' => 15,
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    );

    /**
     * Indicators configuration
     *
     *    array(
     *		  "gid"				=> "<indicator_gid>",
     *		  "delete_by_cron"	=> <true|false>,
     *		  "auth_type"		=> "<admin|user>",
     *    ),
     *
     * @var array
     */
    private $menu_indicators = array(

    );

    /**
     * Cronjobs configuration
     */
    private $cronjobs = array(
        array(
            "name"     => "Statistics handler",
            "module"   => "statistics",
            "model"    => "Statistics_model",
            "method"   => "parse_statistics",
            "cron_tab" => "*/10 * * * *",
            "status"   => "1",
        ),
    );

    /**
     * Fields depended on languages
     *
     * @var array
     */
    protected $lang_dm_data = array(
        array(
            "module"        => "statistics",
            "model"         => "Statistics_forge_model",
            "method_add"    => "langDedicateModuleCallbackAdd",
            "method_delete" => "langDedicateModuleCallbackDelete",
        ),
    );

    /**
     * Class constructor
     *
     * @return Memberships_Install
     */
    public function __construct()
    {
        parent::__construct();
        $this->ci = &get_instance();
        $this->ci->load->model('Install_model');
    }

    /**
     * Install data of cronjobs module
     *
     * @return void
     */
    public function install_cronjob()
    {
        ////// add lift up cronjob
        $this->ci->load->model('Cronjob_model');
        foreach ((array) $this->cronjobs as $cron_data) {
            $validation_data = $this->ci->Cronjob_model->validate_cron(null, $cron_data);
            if (!empty($validation_data['errors'])) {
                continue;
            }
            $this->ci->Cronjob_model->save_cron(null, $validation_data['data']);
        }
    }

    /**
     * Uninstall data of cronjobs module
     *
     * @return void
     */
    public function deinstall_cronjob()
    {
        $this->ci->load->model('Cronjob_model');
        $cron_data = array();
        $cron_data["where"]["module"] = "statistics";
        $this->ci->Cronjob_model->delete_cron_by_param($cron_data);
    }

    /**
     * Install menu data of statistics
     *
     * @return void
     */
    public function install_menu()
    {
        $this->ci->load->helper("menu");
        foreach ($this->menu as $gid => $menu_data) {
            $this->menu[$gid]["id"] = linked_install_set_menu($gid, $menu_data["action"], isset($menu_data["name"]) ? $menu_data["name"] : '');
            linked_install_process_menu_items($this->menu, "create", $gid, 0, $this->menu[$gid]["items"]);
        }
    }

    /**
     * Import menu languages of statistics
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

        $langs_file = $this->ci->Install_model->language_file_read("statistics", "menu", $langs_ids);

        if (!$langs_file) {
            log_message("info", "Empty menu langs data of statistics");

            return false;
        }

        $this->ci->load->helper("menu");

        foreach ($this->menu as $gid => $menu_data) {
            linked_install_process_menu_items(
                $this->menu,
                "update",
                $gid,
                0,
                $this->menu[$gid]["items"],
                $gid,
                $langs_file);
        }

        return true;
    }

    /**
     * Export menu languages of statistics
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

        $this->ci->load->helper("menu");

        $return = array();

        foreach ($this->menu as $gid => $menu_data) {
            $return = array_merge($return, linked_install_process_menu_items(
                $this->menu,
                "export",
                $gid,
                0,
                $this->menu[$gid]["items"],
                $gid,
                $langs_ids));
        }

        return array("menu" => $return);
    }

    /**
     * Uninstall menu data of statistics
     *
     * @return void
     */
    public function deinstall_menu()
    {
        $this->ci->load->helper("menu");

        foreach ($this->menu as $gid => $menu_data) {
            if ($menu_data["action"] == "create") {
                linked_install_set_menu($gid, "delete");
            } else {
                linked_install_delete_menu_items($gid, $this->menu[$gid]["items"]);
            }
        }
    }

    /**
     * Install fields of dedicated languages
     *
     * @return void
     */
    public function _prepare_installing()
    {
        $this->ci->load->model("statistics/models/Statistics_forge_model");
        foreach ($this->ci->pg_language->languages as $lang_id => $value) {
            $this->ci->Statistics_forge_model->langDedicateModuleCallbackAdd($lang_id);
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
            $this->ci->pg_language->add_dedicate_modules_entry($lang_dm_data);
        }

        $this->installDemoContent();
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
        $langs_file = $this->ci->Install_model->language_file_read("statistics", "arbitrary", $langs_ids);
        if (!$langs_file) {
            log_message("info", "Empty arbitrary langs data of statistics");

            return false;
        }

        // TODO:
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

        // TODO:

        return array("arbitrary" => $arbitrary_return);
    }

    /**
     * Uninstall module data
     *
     * @return void
     */
    public function _arbitrary_deinstalling()
    {

        // delete entries in dedicate modules
        foreach ($this->lang_dm_data as $lang_dm_data) {
            $params = array('where' => $lang_dm_data);
            $this->ci->pg_language->delete_dedicate_modules_entry($params);
        }

        $this->clearLogs();
        $this->dropSystems();
    }

    protected function clearLogs()
    {
        if (file_exists(TEMPPATH . 'logs/statistics')) {
            foreach (glob(TEMPPATH . 'logs/statistics/*') as $file) {
                unlink($file);
            }
        }
    }

    protected function dropSystems()
    {
        $path = MODULEPATH . 'statistics/models/systems/';
        if (!is_dir($path)) {
            return;
        }

        $allFiles = scandir($path);
        $files = array_diff($allFiles, array('.', '..'));
        foreach ($files as $file) {
            $result = explode('.', $file);
            if (!empty($result[1])) {
                $system_gid = explode('_', $result[0]);
                $gid = $system_gid[1];

                $system_model_name = "statistics_" . $gid . "_model";
                $this->ci->load->model("statistics/models/systems/" . $system_model_name);
                $this->ci->{$system_model_name}->uninstall_system();
            }
        }
    }

    protected function installDemoContent()
    {
        $demo = include MODULEPATH . 'statistics/demo/demo_content.php';

        if (empty($demo)) {
            return false;
        }

        // TODO:
    }
}
