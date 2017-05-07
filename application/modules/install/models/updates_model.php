<?php

namespace Pg\Modules\Install\Models;

/**
 * Install module
 *
 * @package 	PG_Core
 *
 * @copyright 	Copyright (c) 2000-2014 PG Core
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */

/**
 * Install Updates Model
 *
 * @package 	PG_Core
 * @subpackage 	Install
 *
 * @category 	models
 *
 * @copyright 	Copyright (c) 2000-2014 PG Core
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
class Updates_model extends \Model
{
    /**
     * Link to CodeIgniter object
     *
     * @var object
     */
    protected $ci;

    /**
     * Prepare updating method
     *
     * @var string
     */
    public $prepare_updating_method = '_prepare_updating';

    /**
     * Arbitrary updating method
     *
     * @var string
     */
    public $arbitrary_updating_method = '_arbitrary_updating';

    /**
     * Arbitrary update language method
     *
     * @var string
     */
    public $arbitrary_lang_update_method = '_arbitrary_lang_update';

    /**
     * Class constructor
     *
     * @retrun Updates_model
     */
    public function __construct()
    {
        parent::__construct();
        $this->ci = &get_instance();
        $this->ci->load->model("Install_model");
    }

    /**
     * Return modules with available update as array
     *
     * @return array
     */
    public function get_enabled_product_updates()
    {
        $dir_path = UPDPATH . '/product_updates';
        if (!file_exists($dir_path)) {
            return array();
        }
        $updates = array();
        $d = dir($dir_path);
        while (false !== ($entry = $d->read())) {
            if (substr($entry, 0, 1) === '.' || 'index.html' === $entry) {
                continue;
            }
            $conf = $this->get_update_product_config($entry, 'product_updates');
            if ($this->is_update_actual($conf['modules'])) {
                $updates[$entry] = $conf;
            }
        }
        $d->close();

        return $updates;
    }

    /**
     * Check module is need updateing
     *
     * @param array $module_conf module configuration
     *
     * @return boolean
     */
    public function is_update_actual($module_conf)
    {
        $installed_modules = $this->ci->Install_model->get_installed_modules();
        $need_update = 0;
        foreach ($module_conf as $module_gid => $action) {
            if (!is_array($action)) {
                if ($action == 'delete') {
                    if (isset($installed_modules[$module_gid])) {
                        $need_update = 1;
                        break;
                    }
                }
                if ($action == 'install') {
                    if (!isset($installed_modules[$module_gid])) {
                        $need_update = 1;
                        break;
                    }
                }
            } else {
                if (isset($action['update'])) {
                    if (isset($installed_modules[$module_gid]) && ($action['update']['version_from'] == $installed_modules[$module_gid]["version"])) {
                        $need_update = 1;
                        break;
                    }
                }
            }
        }

        return $need_update;
    }

    /**
     * Return enabled updates
     *
     * @return array
     */
    public function get_enabled_updates()
    {
        $updates = array();
        $installed_modules = $this->ci->Install_model->get_installed_modules();

        $dir_path = UPDPATH;
        if (!is_dir($dir_path)) {
            return $updates;
        }
        $d = dir($dir_path);
        while (false !== ($entry = $d->read())) {
            if (substr($entry, 0, 1) == '.') {
                continue;
            }

            $module = $this->is_module_for_update($entry);
            if (!$module['available']) {
                continue;
            }
            $update_data = $this->get_update_config($entry);
            if (empty($update_data) || $update_data['version_from'] != $installed_modules[$module['name']]["version"]) {
                continue;
            }
            $updates[$entry] = array_merge($installed_modules[$module['name']], $update_data);
        }
        $d->close();

        return $updates;
    }

    /**
     * Return module path by path name
     *
     * @param string $path_name path name
     *
     * @return string/false
     */
    public function get_module_by_path($path_name)
    {
        $path_name = mb_strrchr($path_name, '_', true);
        $installed_modules = $this->ci->Install_model->get_installed_modules();
        if (in_array($path_name, array_keys($installed_modules))) {
            return $path_name;
        } else {
            return false;
        }
    }

    /**
     * Check module is need updating
     *
     * @param string $path_name path name
     *
     * @return array
     */
    public function is_module_for_update($path_name)
    {
        $path_name = mb_strrchr($path_name, '_', true);
        $installed_modules = $this->ci->Install_model->get_installed_modules();
        $ret = array(
            'name'      => $path_name,
            'available' => in_array($path_name, array_keys($installed_modules)),
        );

        return $ret;
    }

    /**
     * Return update configuration
     *
     * @param string $module_gid module guid
     * @param string $path       module path
     *
     * @return array/false
     */
    public function get_update_config($module_gid, $path = '')
    {
        if (!$path) {
            $path = $module_gid;
        }
        $update_config = UPDPATH . $path . '/update.php';
        if (file_exists($update_config)) {
            unset($update);
            require $update_config;

            return $update;
        }

        return false;
    }

    /**
     * Return product update configuration
     *
     * @param string $file update file name
     * @param string $path path to update
     * @reutrn array/false
     */
    public function get_update_product_config($file, $path = '')
    {
        $product_update_config = UPDPATH . $path . '/' . $file;
        if (file_exists($product_update_config)) {
            unset($update);
            require $product_update_config;

            return $update;
        }

        return false;
    }

    /**
     * Return files of module has changed
     *
     * @param string $module_gid module guid
     *
     * @return array
     */
    public function get_module_changed_files($module_gid)
    {
        $files = array();
        $module = $this->ci->pg_module->get_module_by_gid($module_gid);
        $module_config = $this->ci->Install_model->get_module_config($module_gid);
        $uts = strtotime($module["date_update"]);
        if (strtotime($module["date_add"]) > $uts) {
            $uts = strtotime($module["date_add"]);
        }

        foreach ($module_config["files"] as $file) {
            if ($file[0] == 'file' && file_exists(SITE_PHYSICAL_PATH . $file[2])) {
                $mtime = filemtime(SITE_PHYSICAL_PATH . $file[2]);
                if ($mtime > $uts) {
                    $files[] = array('file' => $file[2], 'date' => date("Y-m-d H:i:s", $mtime));
                }
            }
        }

        return $files;
    }

    /**
     * Update module database
     *
     * @param string $module_gid module guid
     *
     * @return array
     */
    public function update_sql_install($module_gid)
    {
        $file = UPDPATH . $module_gid . '/structure_update.sql';

        return $this->ci->Install_model->simple_execute_sql($file);
    }

    /**
     * Update module languages
     *
     * @param string $module_gid module guid
     * @param string $path       to update file
     *
     * @return boolean
     */
    public function update_language_install($module_gid, $path = '')
    {
        if (!$path) {
            $path = $module_gid;
        }
        $lang_file = UPDPATH . $path . '/lang.php';
        if (!file_exists($lang_file)) {
            return false;
        }

        $install_lang = array();

        require $lang_file;

        if (empty($install_lang)) {
            return;
        }

        foreach ($this->ci->pg_language->languages as $lang_id => $lang_data) {
            if (!isset($install_lang[$lang_data['code']])) {
                continue;
            }
            $lang_data = $install_lang[$lang_data['code']];

            if (!empty($lang_data['pages'])) {
                $this->ci->pg_language->pages->set_strings($module_gid, $lang_data['pages'], $lang_id);
            }

            if (!empty($lang_data['ds'])) {
                foreach ($lang_data['ds'] as $ref_gid => $value) {
                    $this->ci->pg_language->ds->set_module_reference($module_gid, $ref_gid, $value, $lang_id);
                }
            }
        }

        return true;
    }

    /**
     * Update module settings
     *
     * @param string $module_gid module guid
     * @param string $path       path to module
     *
     * @return boolean
     */
    public function update_settings_install($module_gid, $path = '')
    {
        if (!$path) {
            $path = $module_gid;
        }
        $settings_file = UPDPATH . $path . '/settings.php';
        if (!file_exists($settings_file)) {
            return false;
        }
        unset($install_settings);

        require $settings_file;
        if (!isset($install_settings) || empty($install_settings)) {
            return false;
        }

        foreach ($install_settings as $config_gid => $value) {
            $this->ci->pg_module->set_module_config($module_gid, $config_gid, $value);
        }

        return true;
    }

    /**
     * Update module permissions
     *
     * @param string $module_gid module guid
     * @param string $path       path to module
     *
     * @return boolean
     */
    public function update_permissions_install($module_gid, $path = '')
    {
        if (!$path) {
            $path = $module_gid;
        }
        $permissions_file = UPDPATH . $path . '/permissions.php';
        if (!file_exists($permissions_file)) {
            return false;
        }
        unset($_permissions);

        require $permissions_file;
        if (!isset($_permissions) || empty($_permissions)) {
            return false;
        }

        foreach ($_permissions as $controller => $data) {
            $this->ci->pg_module->set_module_methods_access($module_gid, $controller, $data);
        }

        return true;
    }

    /**
     * Update module data
     *
     * @param string $module_gid module guid
     * @param string $path       path to module
     *
     * @return void
     */
    public function update_arbitrary_install($module_gid, $path = '')
    {
        if (!$path) {
            $path = $module_gid;
        }
        $model_name = ucfirst($module_gid . '_update_model');
        $model_file = UPDPATH . $path . '/' . strtolower($model_name) . EXT;
        if (file_exists($model_file)) {
            require_once $model_file;
            // TODO: Это вряд ли будет работать с неймспейсами. Проверить и пофиксить.
            $update_model = new $model_name();

            $method_name = $this->prepare_updating_method;
            $validate_exists = method_exists($update_model, $method_name);
            if ($validate_exists) {
                $update_model->{$method_name}();
            }

            $config = $this->ci->Install_model->get_module_config($module_gid);
            $linked_modules = (array) $config['linked_modules']['install'];
            $installed_modules = array_keys($this->ci->Install_model->get_installed_modules());
            $langs_ids = array_keys($this->ci->pg_language->languages);
            foreach ($linked_modules as $linked_module => $linked_method) {
                if (strpos($linked_module, ';') !== false) {
                    $linked_modules = explode(';', $linked_module);
                } else {
                    $linked_modules = array($linked_module);
                }
                if (count(array_diff($linked_modules, $installed_modules)) == 0) {
                    $method_name = (is_array($linked_method) ? $linked_method['name'] : $linked_method) . '_update';
                    if (method_exists($update_model, $method_name)) {
                        $update_model->{$method_name}();
                    }
                }
            }

            $method_name = $this->arbitrary_updating_method;
            $validate_exists = method_exists($update_model, $method_name);
            if ($validate_exists) {
                $update_model->{$method_name}();
            }
        }

        return;
    }

    /**
     * Load update model for the module
     *
     * @param string $module_gid module guid
     * @param string $path       path to update
     *
     * @return Model/false
     */
    public function load_update_model($module_gid, $path = '')
    {
        if (!$path) {
            $path = $module_gid;
        }
        $model_name = ucfirst($module_gid . '_update_model');
        $model_file = UPDPATH . $path . '/' . strtolower($model_name) . EXT;
        if (file_exists($model_file)) {
            require_once $model_file;
            $update_model = new $model_name();

            return $update_model;
        } else {
            return false;
        }
    }

    /**
     * Update module information
     *
     * @param string $module_gid module guid
     * @param array  $data       update data
     *
     * @return void
     */
    public function update_module_information($module_gid, $data)
    {
        $this->ci->pg_module->set_module_update($module_gid, $data);
    }

    /**
     * Update product information
     *
     * @param string $version version guid
     *
     * @return void
     */
    public function update_product_information($version_name, $version_code = null)
    {
        $this->ci->pg_module->set_module_config('start', 'product_version_name', $version_name);
        $this->ci->pg_module->set_module_config('start', 'product_version_name_update', $version_name);
        if (!empty($version_code)) {
            $this->ci->pg_module->set_module_config('start', 'product_version_code', $version_code);
            $this->ci->pg_module->set_module_config('start', 'product_version_code_update', $version_code);
        }
    }
}
