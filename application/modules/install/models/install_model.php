<?php

namespace Pg\Modules\Install\Models;

use Pg\Libraries\Setup;

/**
 * Install module
 *
 * @package     PG_Core
 *
 * @copyright   Copyright (c) 2000-2014 PG Core
 * @author      Pilot Group Ltd <http://www.pilotgroup.net/>
 */

/**
 * Install Main Model
 *
 * @package     PG_Core
 * @subpackage  Install
 *
 * @category    models
 *
 * @copyright   Copyright (c) 2000-2014 PG Core
 * @author      Pilot Group Ltd <http://www.pilotgroup.net/>
 */
class Install_model extends \Model
{
    /**
     * Link to CodeIgniter object
     *
     * @var object
     */
    public $ci;

    /**
     * Enable modules
     *
     * @var array
     */
    public $enable_modules = array();

    /**
     * Prepare installing method
     *
     * @var string
     */
    public $prepare_installing_method = '_prepare_installing';

    /**
     * Arbitrary installing method
     *
     * @var string
     */
    public $arbitrary_installing_method = '_arbitrary_installing';

    /**
     * Arbitrary uninstalling method
     *
     * @var string
     */
    public $arbitrary_deinstalling_method = '_arbitrary_deinstalling';

    /**
     * Get settings form method
     *
     * @var string
     */
    public $get_settings_form_method = '_get_settings_form';

    /**
     * Validate module settings method
     *
     * @var string
     */
    public $validate_requirements_method = '_validate_requirements';

    /**
     * Languages installing method
     *
     * @var string
     */
    public $arbitrary_lang_install_method = '_arbitrary_lang_install';

    /**
     * Languages uninstalling method
     *
     * @var string
     */
    public $arbitrary_lang_export_method = '_arbitrary_lang_export';

    /**
     * File of structure install
     *
     * @var string
     */
    protected $structure_install_file = 'structure_install.sql';

    /**
     * File of structure uninstall
     *
     * @var string
     */
    protected $structure_deinstall_file = 'structure_deinstall.sql';

    /**
     * File of demo structure install
     *
     * @var string
     */
    protected $demo_structure_install_file = 'demo_structure_install.sql';

    /**
     * File of demo structure uninstall
     *
     * @var string
     */
    protected $demo_structure_deinstall_file = 'demo_structure_deinstall.sql';

    /**
     * Class constructor
     *
     * @return Install_model
     */
    public function __construct()
    {
        parent::__construct();
        $this->ci = &get_instance();
    }

    public function is_file_writable($file, $mode)
    {
        @chmod($file, $mode);

        clearstatcache();

        return is_writable($file);
    }

    public function generate_password()
    {
        $str = md5(date("Y-m-d H:i:s") . rand(10, 6000));
        $password = substr($str, rand(0, 15), 8);

        return $password;
    }

    public function save_install_config($data)
    {
        $template_file = MODULEPATH . 'install/install/install.php.default';
        $template = file_get_contents($template_file);

        $template = str_replace("[install_module_done]", ($data["install_module_done"] ? 'true' : 'false'), $template);
        $template = str_replace("[install_login]", addslashes($data["install_login"]), $template);
        $template = str_replace("[install_password]", addslashes($data["install_password"]), $template);

        if (isset($data["ftp_host"])) {
            $template = str_replace("[ftp_host]", addslashes($data["ftp_host"]), $template);
            if ($data["ftp_path"]) {
                $template = str_replace("[ftp_path]", addslashes($data["ftp_path"]), $template);
            } else {
                $template = str_replace("[ftp_path]", '/', $template);
            }
            $template = str_replace("[ftp_user]", addslashes($data["ftp_user"]), $template);
            $template = str_replace("[ftp_password]", addslashes($data["ftp_password"]), $template);
        } else {
            $template = str_replace("[ftp_host]", '', $template);
            $template = str_replace("[ftp_path]", '', $template);
            $template = str_replace("[ftp_user]", '', $template);
            $template = str_replace("[ftp_password]", '', $template);
        }

        if (isset($data["installer_ip_protect"])) {
            $template = str_replace("[installer_ip_protect]", ($data["installer_ip_protect"] ? 'true' : 'false'), $template);
            $installer_ip = "array('" . implode("', '", $data["installer_ip"]) . "')";
            $template = str_replace("[installer_ip]", $installer_ip, $template);
        } else {
            $template = str_replace("[installer_ip_protect]", 'false', $template);
            $template = str_replace("[installer_ip]", 'array()', $template);
        }

        $config_file = APPPATH . 'config/install' . EXT;
        $h = fopen($config_file, "w");
        if ($h) {
            fwrite($h, $template);
            fclose($h);
            if (extension_loaded('Zend OPcache')) {
                opcache_reset();
            }

            return true;
        } else {
            return false;
        }
    }

    public function save_config($data)
    {
        $template_file = MODULEPATH . 'install/install/config.php.default';
        $template = file_get_contents($template_file);

        $template = str_replace("[install_done]", ($data["install_done"] ? 'true' : 'false'), $template);
        $template = str_replace("[db_hostname]", $data["db_hostname"], $template);
        $template = str_replace("[db_username]", addslashes($data["db_username"]), $template);
        $template = str_replace("[db_password]", addslashes($data["db_password"]), $template);
        $template = str_replace("[db_database]", addslashes($data["db_database"]), $template);
        $template = str_replace("[db_prefix]", $data["db_prefix"], $template);
        $template = str_replace("[db_driver]", $data["db_driver"], $template);

        $template = str_replace("[site_server]", $data["site_server"], $template);
        $template = str_replace("[cookie_site_server]", $this->get_two_last_domain_signature($data["site_server"]), $template);
        $template = str_replace("[site_path]", $data["site_path"], $template);
        $template = str_replace("[site_subfolder]", $data["site_subfolder"], $template);
        $template = str_replace("[timezone]", date_default_timezone_get(), $template);

        $config_file = SITE_PHYSICAL_PATH . 'config' . EXT;
        $h = fopen($config_file, "w");
        if ($h) {
            fwrite($h, $template);
            fclose($h);
            if (extension_loaded('Zend OPcache')) {
                opcache_reset();
            }

            return true;
        } else {
            return false;
        }
    }

    public function simple_execute_sql($file)
    {
        if (!file_exists($file)) {
            return false;
        }

        $errors = array();

        $file_content = file_get_contents($file);
        $file_content = preg_replace("/\n+/", "", $file_content);
        $file_content = preg_replace("/;\s*(create|delete|drop|insert|update|alter)/i", "[sql_install_delim]$1", $file_content);
        $file_content = str_replace("[prefix]", DB_PREFIX, $file_content);
        $sql_array = explode("[sql_install_delim]", $file_content);

        if (!empty($sql_array)) {
            foreach ($sql_array as $sql) {
                $sql = trim($sql);
                if (!empty($sql)) {
                    $result = $this->ci->db->simple_query($sql);
                    if (!$result && !preg_match('/^alter/i', $sql) && !preg_match('/^duplicate/i', mysql_error())) {
                        $errors[] = "Error in sql: " . $sql;
                    }
                }
            }
        }

        return $errors;
    }

    public function set_module_installed($module_gid)
    {
        if ($this->ci->pg_module->is_module_active($module_gid)) {
            return false;
        }
        $module_data = $this->get_module_config($module_gid);
        $data = array(
            'module_gid'         => $module_gid,
            'module_name'        => $module_data["install_name"],
            'module_description' => $module_data["install_descr"],
            'category'           => !empty($module_data["category"]) ? $module_data["category"] : '',
            'version'            => $module_data["version"],
            'date_add'           => date('Y-m-d H:i:s'),
        );
        $this->ci->pg_module->set_module_install($data);

        return;
    }

    public function set_module_uninstalled($module_gid)
    {
        if (!$this->ci->pg_module->is_module_active($module_gid)) {
            return false;
        }
        $this->ci->pg_module->set_module_uninstall($module_gid);

        return;
    }

    public function get_enabled_modules()
    {
        if (empty($this->enable_modules)) {
            $installed_modules = $this->get_installed_modules();

            $dir_path = MODULEPATH;
            $d = dir($dir_path);
            while (false !== ($entry = $d->read())) {
                if (substr($entry, 0, 1) == '.') {
                    continue;
                }
                if ($entry == 'install') {
                    continue;
                }
                if (isset($installed_modules[$entry]) && !empty($installed_modules[$entry])) {
                    continue;
                }
                $module_data = $this->get_module_config($entry);
                if (empty($module_data)) {
                    continue;
                }
                $this->enable_modules[$entry] = $module_data;
            }
            $d->close();
        }
        ksort($this->enable_modules);

        return $this->enable_modules;
    }

    public function get_installed_modules($order_by = 'module_gid ASC')
    {
        $modules = array();
        $modules_by_id = $this->ci->pg_module->get_modules($order_by);
        if (!empty($modules_by_id)) {
            foreach ($modules_by_id as $module) {
                $modules[$module["module_gid"]] = $module;
            }
        }

        return $modules;
    }

    public function get_depend_modules($module_gid)
    {
        $modules = array();
        $modules_by_id = $this->ci->pg_module->get_modules();
        if (!empty($modules_by_id)) {
            foreach ($modules_by_id as $module) {
                $temp_module = $this->get_module_config($module["module_gid"]);
                if (isset($temp_module["dependencies"][$module_gid]) && !empty($temp_module["dependencies"][$module_gid])) {
                    $modules[$module["module_gid"]] = $temp_module;
                }
            }
        }

        return $modules;
    }

    public function get_product_setup_modules()
    {
        $product_modules_file = SITE_PHYSICAL_PATH . 'product.php';
        if (file_exists($product_modules_file)) {
            require $product_modules_file;
            if (!isset($product_modules) || empty($product_modules)) {
                return false;
            }

            return $product_modules;
        }

        return false;
    }

    public function get_product_setup_libraries()
    {
        $product_modules_file = SITE_PHYSICAL_PATH . 'product.php';
        if (file_exists($product_modules_file)) {
            require $product_modules_file;
            if (!isset($product_libraries) || empty($product_libraries)) {
                return false;
            }

            return $product_libraries;
        }

        return false;
    }

    public function get_module_config($module_gid)
    {
        $module_config = MODULEPATH . $module_gid . '/install/module.php';
        if (file_exists($module_config)) {
            unset($module);
            require $module_config;

            return $module;
        }

        return false;
    }

    public function check_modules_not_exists($module_gids = array())
    {
        $not_existed = array();
        if (!empty($module_gids)) {
            foreach ($module_gids as $gid) {
                $module_config = MODULEPATH . $gid . '/install/module.php';
                if (!file_exists($module_config)) {
                    $not_existed[] = $gid;
                }
            }
        }

        return $not_existed;
    }

    // check validate requirements method exists
    public function has_module_requirements($module_gid)
    {
        $model_name = ucfirst($module_gid . '_install_model');
        $model_file = MODULEPATH . $module_gid . '/models/' . strtolower($model_name) . EXT;
        if (file_exists($model_file)) {
            $this->ci->load->model($module_gid . '/models/' . $model_name);

            $method_name = $this->validate_requirements_method;
            $validate_exists = method_exists($this->ci->{$model_name}, $method_name);
            if ($validate_exists) {
                return true;
            }
        }

        return false;
    }

    // execute validate requirements method
    public function load_requirements($module_gid)
    {
        $return = array('data' => array(), 'result' => true);
        if ($this->has_module_requirements($module_gid)) {
            $model_name = ucfirst($module_gid . '_install_model');
            $method_name = $this->validate_requirements_method;
            $return = $this->ci->{$model_name}->{$method_name}();
        }

        return $return;
    }

    public function install_stucture($module_gid)
    {
        //  structure
        $errors = $this->_structure_install($module_gid);

        if (empty($errors)) {
            // lang install
            $this->_language_install($module_gid);

            // settings install
            $this->_settings_install($module_gid);

            // permissions install
            $this->_permissions_install($module_gid);
        }

        return $errors;
    }

    public function install_linked($module_gid)
    {
        // prepare install
        $this->_prepare_install($module_gid);

        // linked modules callbacks
        $this->_linked_modules($module_gid, 'install');

        // Arbitrary install
        $this->_arbitrary_install($module_gid);
    }

    public function get_module_install_messages($module_gid)
    {
        $file = MODULEPATH . $module_gid . '/install/messages.php';
        if (!file_exists($file)) {
            return false;
        }
        unset($install_messages);
        require $file;

        if (!isset($install_messages) || empty($install_messages)) {
            return false;
        } else {
            return $install_messages;
        }
    }

    public function get_module_deinstall_messages($module_gid)
    {
        $file = MODULEPATH . $module_gid . '/install/messages.php';
        if (!file_exists($file)) {
            return false;
        }
        unset($deinstall_messages);
        require $file;

        if (!isset($deinstall_messages) || empty($deinstall_messages)) {
            return false;
        } else {
            return $deinstall_messages;
        }
    }

    /**
     * Executes method from $model_name with version check
     *
     * @param string $model_name
     * @param var    $method
     * @param float  $min_version
     *
     * @return bool
     */
    public function execute_method($model_name, $method, $min_version = null, $params = null)
    {
        if (!is_array($method)) {
            $method_name = $method;
        } elseif (empty($method['version']) || (float) $min_version >= (float) $method['version']) {
            $method_name = $method['name'];
        }
        if (property_exists($this->ci, $model_name) && method_exists($this->ci->{$model_name}, $method_name)) {
            if (!is_null($params)) {
                return $this->ci->{$model_name}->{$method_name}($params);
            } else {
                return $this->ci->{$model_name}->{$method_name}();
            }
        } else {
            return false;
        }
    }

    /**
     * Load install model for the module
     *
     * @param string $module_gid
     *
     * @return string Model or false
     */
    public function load_install_model($module_gid)
    {
        $model_name = ucfirst($module_gid . '_install_model');
        $model_file = MODULEPATH . $module_gid . '/models/' . strtolower($model_name) . EXT;
        if (!file_exists($model_file)) {
            return false;
        }
        $this->ci->load->model($module_gid . '/models/' . $model_name);

        return $model_name;
    }

    public function _structure_install($module_gid)
    {
        $module_data = $this->get_module_config($module_gid);

        if (!empty($module_data['structure_install_file'])) {
            $structure_install_file = $module_data['structure_install_file'];
        } else {
            $structure_install_file = $this->structure_install_file;
        }

        $file = MODULEPATH . $module_gid . '/install/' . $structure_install_file;

        return $this->simple_execute_sql($file);
    }

    public function _structure_deinstall($module_gid)
    {
        $module_data = $this->get_module_config($module_gid);

        if (!empty($module_data['structure_deinstall_file'])) {
            $structure_deinstall_file = $module_data['structure_deinstall_file'];
        } else {
            $structure_deinstall_file = $this->structure_deinstall_file;
        }

        $file = MODULEPATH . $module_gid . '/install/' . $structure_deinstall_file;

        return $this->simple_execute_sql($file);
    }

    public function demo_structure_install($module_gid)
    {
        $module_data = $this->get_module_config($module_gid);

        if (!empty($module_data['demo_structure_install_file'])) {
            $demo_structure_install_file = $module_data['demo_structure_install_file'];
        } else {
            $demo_structure_install_file = $this->demo_structure_install_file;
        }

        $file = MODULEPATH . $module_gid . '/install/' . $demo_structure_install_file;

        return $this->simple_execute_sql($file);
    }

    public function demo_structure_deinstall($module_gid)
    {
        $module_data = $this->get_module_config($module_gid);

        if (!empty($module_data['demo_structure_deinstall_file'])) {
            $demo_structure_deinstall_file = $module_data['demo_structure_deinstall_file'];
        } else {
            $demo_structure_deinstall_file = $this->demo_structure_deinstall_file;
        }

        $file = MODULEPATH . $module_gid . '/install/' . $demo_structure_deinstall_file;

        return $this->simple_execute_sql($file);
    }

    /**
     * Reads language file from the module
     *
     * @param string $module_gid Module gid
     * @param string $file       Filename without extension
     * @param array  $langs_ids  Array of langs ids
     *
     * @return array
     */
    public function language_file_read($module_gid, $file = "pages", $langs_ids = null)
    {
        $langs_path = MODULEPATH . $module_gid . '/langs/';
        if (!file_exists($langs_path)) {
            log_message('error', 'The directory does not exist. (' . $module_gid . '/langs/)');
            return false;
        }

        // validate lang ids array
        if (!$langs_ids) {
            $langs_ids = array_keys($this->ci->pg_language->languages);
        } elseif (!is_array($langs_ids)) {
            $langs_ids = array($langs_ids);
        }
        $default_lang = $this->ci->pg_language->get_default_lang_id();
        $lang_data = array();
        // Get specified langs data
        foreach ($langs_ids as $lang_id) {
            $lang_code = $this->ci->pg_language->languages[$lang_id]['code'];
            if (!$lang_code) {
                log_message('info', '"' . $lang_code . '" language is not found');
                continue;
            }
            $lang_file = $langs_path . $lang_code . '/' . $file . EXT;
            // Get default langs if file doesn't exist
            if (!file_exists($lang_file)) {
                log_message('info', 'File not found (' . $lang_file . '), use default langs');
                $lang_code = $this->ci->pg_language->languages[$default_lang]['code'];
                $lang_file = $langs_path . $lang_code . '/' . $file . EXT;
                if (!file_exists($lang_file)) {
                    continue;
                }
            }

            require $lang_file;

            if (isset($install_lang)) {
                foreach ($install_lang as $gid => $value) {
                    $lang_data[$gid][$lang_id] = $value;
                }
                unset($install_lang);
            }
        }

        return $lang_data;
    }

    /**
     * Installs module langs from files
     *
     * @param string $module_gid
     * @param int    $lang_id
     * @param bool   $delete_old Remove old langs data before update
     *
     * @return bool
     */
    public function _language_install($module_gid, $lang_id = null, $delete_old = true)
    {
        // Default lang should go first
        $installed_langs = $this->ci->pg_language->get_langs('is_default DESC');
        $default_lang = current($installed_langs);
        $default_lang_id = $default_lang['id'];
        $default_lang_code = $default_lang['code'];

        if ($delete_old) {
            // Delete old langs
            $this->ci->pg_language->pages->delete_module($module_gid);
            $this->ci->pg_language->ds->delete_module($module_gid);
        }

        $langs_path = MODULEPATH . $module_gid . '/langs/';

        // Check if the module has language which is default on the site
        $default_lang_installing = file_exists($langs_path . $default_lang_code);

        foreach ($installed_langs as $installed_lang) {
            if ($lang_id && ($lang_id != $installed_lang['id'])) {
                continue;
            }

            if (!file_exists($langs_path . $installed_lang['code'])) {
                mkdir($langs_path . $installed_lang['code'], '0777');
            }

            // Pages langs
            $pages_file = $langs_path . $installed_lang['code'] . '/pages' . EXT;
            if (file_exists($pages_file)) {
                $install_lang = array();
                include $pages_file;
                if (!empty($install_lang) && is_array($install_lang)) {
                    $this->ci->pg_language->pages->set_strings($module_gid, $install_lang, $installed_lang['id']);
                    if (!$default_lang_installing) {
                        $this->ci->pg_language->pages->set_strings($module_gid, $install_lang, $default_lang_id);
                    }
                } else {
                    log_message('info', 'No pages langs for the module "' . $module_gid . '"');
                }
            }
            // Data sources langs
            $ds_file = $langs_path . $installed_lang['code'] . '/ds' . EXT;
            if (file_exists($ds_file)) {
                unset($install_lang);
                include $ds_file;
                if (!empty($install_lang) && is_array($install_lang)) {
                    foreach ($install_lang as $ref_gid => $value) {
                        $this->ci->pg_language->ds->set_module_reference($module_gid, $ref_gid, $value, $installed_lang['id']);
                        if (!$default_lang_installing) {
                            $this->ci->pg_language->ds->set_module_reference($module_gid, $ref_gid, $value, $default_lang_id);
                        }
                    }
                } else {
                    log_message('info', 'No ds langs for the module "' . $module_gid . '"');
                }
            }
            // Install default lang data only once
            if (!$default_lang_installing) {
                $default_lang_installing = true;
            }
        }

        return true;
    }

    public function _language_deinstall($module_gid)
    {
        $this->ci->pg_language->pages->delete_module($module_gid);
        $this->ci->pg_language->ds->delete_module($module_gid);
    }

    public function _settings_install($module_gid)
    {
        $install_settings = Setup::getModuleData($module_gid, Setup::TYPE_SETTINGS);
        if(!empty($install_settings)) {
            foreach ($install_settings as $config_gid => $value) {
                $this->ci->pg_module->set_module_config($module_gid, $config_gid, $value);
            }
            return;
        } else {
            $settings_file = MODULEPATH . $module_gid . '/install/settings.php';
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
            return;
        }
    }

    public function _settings_deinstall($module_gid)
    {
        $this->ci->pg_module->delete_module_config($module_gid);
    }

    public function _permissions_install($module_gid)
    {
        $_permissions = Setup::getModuleData($module_gid, Setup::TYPE_PERMISSIONS);
        if(!empty($_permissions)) {
            foreach ($_permissions as $controller => $data) {
                $this->ci->pg_module->set_module_methods_access($module_gid, $controller, $data);
            }
        } else {
            $permissions_file = MODULEPATH . $module_gid . '/install/permissions.php';
            if (!file_exists($permissions_file)) {
                return false;
            }
            unset($_permissions);

            $this->ci->pg_module->clear_module_metods_access($module_gid);

            require $permissions_file;
            if (empty($_permissions)) {
                return false;
            }

            foreach ($_permissions as $controller => $data) {
                $this->ci->pg_module->set_module_methods_access($module_gid, $controller, $data);
            }
        }
        $this->addAclPermissions($module_gid, $_permissions);
        return;
    }

    private function addAclPermissions($module_gid, array $permissions)
    {
        $this->ci->acl->getManager()->setRole('guest');
        $this->ci->acl->getManager()->setRole(['user', 'admin', 'installer'], 'guest');
        $roles = [
            1 => 'guest',
            2 => 'user',
            3 => 'admin',
            4 => 'installer',
        ];
        $action_view_page = new \Pg\Libraries\Acl\Action\ViewPage();
        foreach ($permissions as $controller => $data) {
            foreach($data as $action => $role_id) {
                $res_page = new \Pg\Libraries\Acl\Resource\Page(
                        ['module' => $module_gid, 'controller' => $controller, 'action' => $action,]
                );
                $this->ci->acl->getManager()
                        ->role($roles[$role_id])
                        ->allow($action_view_page->getGid(), $res_page->getResourceType(), $res_page->getResourceId());
            }
        }
    }

    public function _permissions_deinstall($module_gid)
    {
        $this->ci->pg_module->clear_module_metods_access($module_gid);
    }

    public function _arbitrary_install($module_gid)
    {
        $model_name = ucfirst($module_gid . '_install_model');
        $model_file = MODULEPATH . $module_gid . '/models/' . strtolower($model_name) . EXT;
        if (file_exists($model_file)) {
            $this->ci->load->model($module_gid . '/models/' . $model_name);

            $method_name = $this->arbitrary_installing_method;
            $validate_exists = method_exists($this->ci->{$model_name}, $method_name);
            if ($validate_exists) {
                $this->ci->{$model_name}->{$method_name}();
            }
            // Update langs
            $method_name = $this->arbitrary_lang_install_method;
            if (method_exists($this->ci->{$model_name}, $method_name)) {
                $this->ci->{$model_name}->{$method_name}(array_keys($this->ci->pg_language->languages));
            }
        }

        return;
    }

    public function _arbitrary_deinstall($module_gid)
    {
        $model_name = ucfirst($module_gid . '_install_model');
        $model_file = MODULEPATH . $module_gid . '/models/' . strtolower($model_name) . EXT;
        if (file_exists($model_file)) {
            $this->ci->load->model($module_gid . '/models/' . $model_name);

            $method_name = $this->arbitrary_deinstalling_method;
            $validate_exists = method_exists($this->ci->{$model_name}, $method_name);
            if ($validate_exists) {
                $this->ci->{$model_name}->{$method_name}();
            }
        }

        return;
    }

    /**
     * Performs some actions with installed modules
     * that is linked with $installing_gid
     *
     * @param string $module_gid
     * @param string $action     install/deinstall
     *
     * @return boolean
     */
    public function _linked_modules($module_gid, $action)
    {
        $model = $this->load_install_model($module_gid);
        if (!$model) {
            return false;
        }

        $config = $this->get_module_config($module_gid);

        $installed_modules = array_keys($this->get_installed_modules('id ASC'));

        if (!empty($config['linked_modules'][$action])) {
            $config_linked = $config['linked_modules'][$action];
            // Pass through linked modules
            foreach ($config_linked as $module => $method) {
                if (strpos($module, ';') !== false) {
                    $modules = explode(';', $module);
                } else {
                    $modules = array($module);
                }
                if (count(array_diff($modules, $installed_modules)) == 0) {
                    $this->execute_method($model, $method, $config['version']);
                    // Langs
                    $this->execute_method($model,
                        (is_array($method) ? $method['name'] : $method) . '_lang_update',
                        $config['version'], array_keys($this->ci->pg_language->languages));
                }
            }
        }

        // Pass though already installed modules
        foreach ($installed_modules as $installed_module_gid) {
            $linked_config = $this->get_module_config($installed_module_gid);
            if (empty($linked_config['linked_modules'][$action])) {
                continue;
            }

            $linked_model = $this->load_install_model($installed_module_gid);
            if (!$linked_model) {
                continue;
            }

            $installed_modules[] = $module_gid;

            foreach ($linked_config['linked_modules'][$action] as $linked_module => $linked_method) {
                if (strpos($linked_module, ';') !== false) {
                    $linked_modules = explode(';', $linked_module);
                } else {
                    $linked_modules = array($linked_module);
                }
                if (in_array($module_gid, $linked_modules) && count(array_diff($linked_modules, $installed_modules)) == 0) {
                    $this->execute_method($linked_model, $linked_method, $config['version']);
                    // Langs
                    $this->execute_method($linked_model, (is_array($linked_method) ? $linked_method['name'] : $linked_method) .
                        '_lang_update', $config['version'], array_keys($this->ci->pg_language->languages));
                }
            }
        }

        return true;
    }

    public function _prepare_install($module_gid)
    {
        $model_name = ucfirst($module_gid . '_install_model');
        $model_file = MODULEPATH . $module_gid . '/models/' . strtolower($model_name) . EXT;

        if (file_exists($model_file)) {
            $this->ci->load->model($module_gid . '/models/' . $model_name);

            $method_name = $this->prepare_installing_method;
            $validate_exists = method_exists($this->ci->{$model_name}, $method_name);
            if ($validate_exists) {
                $this->ci->{$model_name}->{$method_name}();
            }
        }

        return;
    }

    public function _default_data_install()
    {
    }

    public function has_module_settings($module_gid)
    {
        $model_name = ucfirst($module_gid . '_install_model');
        $model_file = MODULEPATH . $module_gid . '/models/' . strtolower($model_name) . EXT;
        if (file_exists($model_file)) {
            $this->ci->load->model($module_gid . '/models/' . $model_name);

            $method_name = $this->get_settings_form_method;
            $validate_exists = method_exists($this->ci->{$model_name}, $method_name);
            if ($validate_exists) {
                return true;
            }
        }

        return false;
    }

    public function load_settings($module_gid, $submit = false)
    {
        $return = false;
        if ($this->has_module_settings($module_gid)) {
            $model_name = ucfirst($module_gid . '_install_model');
            $method_name = $this->get_settings_form_method;
            $return = $this->ci->{$model_name}->{$method_name}($submit);
        }

        return $return;
    }

    /**
     * Method generates zip file with module's langs
     *
     * @param array     $modules  Might be a string
     * @param int|array $langs
     * @param string    $filename
     *
     * @return zip file
     */
    public function generate_module_lang_install($modules = null, $langs = null, $filename = 'lang_install', $unit_test = false)
    {
        $installed_modules = array_keys($this->get_installed_modules());
        if (is_null($modules)) {
            // If empty, get langs for all modules
            $modules = $installed_modules;
        } elseif (is_string($modules)) {
            // In case it's a string
            $modules = array($modules);
        }

        $this->ci->load->library('zip');
        $this->ci->zip->clear_data();
        foreach ($modules as $module_gid) {
            // Check whether $module_gid is installed.
            if (!in_array($module_gid, $installed_modules)) {
                log_message('info', 'Module ' . $module_gid . ' is not installed');
                continue;
            }

            $model = $this->load_install_model($module_gid);
            $config = $this->get_module_config($module_gid);
            $linked_modules = (array) $config['linked_modules']['install'];
            $langs_path = 'application/modules/' . $module_gid . '/langs/';

            // Add langs data to the zip file
            foreach ($this->ci->pg_language->languages as $lang_id => $lang_data) {
                if (!in_array($lang_id, (array) $langs)) {
                    continue;
                }
                // pages
                $lang_install_code = $this->ci->pg_language->generate_install_module_lang($module_gid, $lang_id, "pages");
                if ($lang_install_code) {
                    $this->ci->zip->add_data($langs_path . $lang_data['code'] . '/pages.php', $lang_install_code);
                }
                // ds
                $lang_install_code = $this->ci->pg_language->generate_install_module_lang($module_gid, $lang_id, 'ds');
                if ($lang_install_code) {
                    $this->ci->zip->add_data($langs_path . $lang_data['code'] . '/ds.php', $lang_install_code);
                }
                // arbitrary
                if (method_exists($this->ci->{$model}, '_arbitrary_lang_export')) {
                    $langs_data = (array) $this->execute_method($model, '_arbitrary_lang_export', null, (array) $lang_id);
                    foreach ($langs_data as $lmodule => $ldata) {
                        $lang_install_code = $this->ci->pg_language->generate_install_linked_lang($ldata, $lang_id);
                        if ($lang_install_code) {
                            $this->ci->zip->add_data($langs_path . $lang_data['code'] . '/' . $lmodule . '.php', $lang_install_code);
                        }
                    }
                }
                // linked modules
                foreach ($linked_modules as $linked_module => $linked_method) {
                    if (strpos($linked_module, ';') !== false) {
                        $linked_modules = explode(';', $linked_module);
                    } else {
                        $linked_modules = array($linked_module);
                    }
                    if (count(array_diff($linked_modules, $installed_modules)) == 0) {
                        $method_name = (is_array($linked_method) ? $linked_method['name'] : $linked_method) . '_lang_export';
                        if (method_exists($this->ci->{$model}, $method_name)) {
                            $langs_data = (array) $this->execute_method($model, $method_name, null, (array) $lang_id);
                            foreach ($langs_data as $lmodule => $ldata) {
                                $lang_install_code = $this->ci->pg_language->generate_install_linked_lang($ldata, $lang_id);
                                if ($lang_install_code) {
                                    $this->ci->zip->add_data($langs_path . $lang_data['code'] . '/' . $lmodule . '.php', $lang_install_code);
                                }
                            }
                        }
                    } else {
                        $langs_data = $this->language_file_read($module_gid, str_replace(';', '_', $linked_module), $lang_id);
                        $lang_install_code = $this->ci->pg_language->generate_install_linked_lang($langs_data, $lang_id);
                        if ($lang_install_code) {
                            $this->ci->zip->add_data($langs_path . $lang_data['code'] . '/' . $linked_module . '.php', $lang_install_code);
                        }
                    }
                }
            }
        }

        // put install lang file into zip
        foreach ($this->ci->pg_language->languages as $lang_id => $lang_data) {
            if (in_array($lang_id, (array) $langs)) {
                $this->ci->zip->add_data('langs/' . $lang_data['code'] . '.php', $this->ci->pg_language->generate_install_lang_description($lang_id));
            }
        }

        if (is_object($this->ci->zip) && $unit_test == true) {
            return $this->ci->zip->get_zip($filename . '.zip');
        } elseif (is_object($this->ci->zip)) {
            return $this->ci->zip->download($filename . '.zip');
        } else {
            return false;
        }
    }

    /**
     * Updates langs data for the installed modules
     *
     * @param int|array $lang_id
     * @param string    $module_gid
     *
     * @return boolean
     */
    public function update_langs($lang_id, $module_gid = null)
    {
        if (!$lang_id) {
            log_message('error', 'Empty lang id');

            return false;
        }
        $installed_modules = array_keys($this->get_installed_modules());
        foreach ($installed_modules as $installed_module_gid) {
            if ($module_gid && ($module_gid != $installed_module_gid)) {
                continue;
            }
            $model = $this->load_install_model($installed_module_gid);
            // pages, ds
            $this->_language_install($installed_module_gid, $lang_id, false);
            // arbitrary
            $this->execute_method($model, $this->arbitrary_lang_install_method, null, $lang_id);
            // linked
            $config = $this->get_module_config($installed_module_gid);
            if (empty($config['linked_modules']['install']) || !is_array($config['linked_modules']['install'])) {
                continue;
            }
            foreach ($config['linked_modules']['install'] as $linked_module => $linked_method) {
                if (strpos($linked_module, ';') !== false) {
                    $linked_modules = explode(';', $linked_module);
                } else {
                    $linked_modules = array($linked_module);
                }
                if (count(array_diff($linked_modules, $installed_modules)) == 0) {
                    $method_name = (is_array($linked_method) ? $linked_method['name'] : $linked_method) . '_lang_update';
                    $this->execute_method($model, $method_name, null, (array) $lang_id);
                }
            }
        }

        return true;
    }

    public function get_files_list($dir)
    {
        $return = array();
        $d = dir($dir);
        while (false !== ($entry = $d->read())) {
            if (substr($entry, 0, 1) == '.') {
                continue;
            } elseif (is_dir($dir . $entry)) {
                $return[] = array(
                    "type" => "dir",
                    "path" => $dir . $entry,
                );
                $files = $this->get_files_list($dir . $entry . "/");
                if (!empty($files)) {
                    foreach ($files as $file) {
                        $return[] = $file;
                    }
                }
            } else {
                $return[] = array(
                    "type" => "file",
                    "path" => $dir . $entry,
                );
            }
        }
        $d->close();

        return $return;
    }

    public function get_two_last_domain_signature($domain)
    {
        preg_match('@^(?:http://)?([^/]+)@i', $domain, $matches);
        $host = str_replace("www.", "", $matches[1]);
        if (substr_count($host, '.') != 1) {
            return '';
        }

        return $host;
    }

    /**
     * Returns the array of languages from langs/
     *
     * @return array
     */
    public function get_available_langs()
    {
        $handle = opendir(SITE_PHYSICAL_PATH . 'langs');
        $langs = array();
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != ".." && is_file(SITE_PHYSICAL_PATH . 'langs/' . $file)) {
                $inc = include SITE_PHYSICAL_PATH . 'langs/' . $file;
                if (!is_array($inc) || empty($inc['code'])) {
                    continue;
                }
                $inc['is_default'] = 0;
                $langs[] = $inc;
            }
        }

        closedir($handle);

        return $langs;
    }

    public static function sort_langs($a, $b)
    {
        return ($a['is_default'] > $b['is_default']) ? -1 : 1;
    }

    /**
     * Saves languages
     *
     * @param array  $langs
     * @param string $defaule
     */
    public function save_langs($langs)
    {
        if (0 === count($langs)) {
            log_message('error', 'Empty langs array is passed');

            return false;
        }

        // Set default lang first
        if (!$langs[0]['is_default']) {
            usort($langs, array(self, "sort_langs"));
        }
        if (!$langs[0]['is_default']) {
            log_message('error', 'No default language');

            return false;
        }

        foreach ($langs as $lang) {
            $data = array(
                'rtl'        => $lang['dir'],
                'code'       => $lang['code'],
                'name'       => $lang['name'],
                'status'     => 1,
                'is_default' => $lang['is_default'],
            );

            if (isset($lang['id'])) {
                $data['id'] = $lang['id'];
            }

            $this->ci->pg_language->set_lang(null, $data);
        }

        return true;
    }
}
