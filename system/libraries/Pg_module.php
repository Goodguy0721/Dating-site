<?php

/**
 * Libraries
 *
 * @package 	PG_Core
 *
 * @copyright 	Copyright (c) 2000-2015 PG Core
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

define('MODULES_TABLE', DB_PREFIX . 'modules');
define('MODULES_CONFIG_TABLE', DB_PREFIX . 'modules_config');
define('MODULES_METHODS_TABLE', DB_PREFIX . 'modules_methods');

/**
 * PG Modules Model
 *
 * @package 	PG_Core
 * @subpackage 	Libraries
 *
 * @category	libraries
 *
 * @copyright 	Copyright (c) 2000-2014 PG Core
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
class CI_Pg_module
{
    /**
     * Link to CodeIgniter object
     *
     * @var object
     */
    public $ci;

    /**
     * Modules cache
     *
     * @var array
     */
    public $modules = array();

    /**
     * Modules configurations cache
     *
     * @var array
     */
    public $config = array();

    /**
     * Constructor
     *
     * @return CI_PG_Module Object
     */
    public function __construct()
    {
        $this->ci = &get_instance();
        if (INSTALL_DONE) {
            $this->ci->db->memcache_tables(array(
                MODULES_TABLE,
                MODULES_CONFIG_TABLE,
                MODULES_METHODS_TABLE,
            ));
        }
    }

    /**
     * Get installed modules data from base, put into the $this->modules
     *
     * @param string $order_by sorting data
     *
     * @return array
     */
    public function get_modules($order_by = 'module_gid ASC')
    {
        unset($this->modules);
        $this->modules = array();
        $fields = array('id', 'module_gid', 'module_name', 'module_description',
            'category', 'version', 'is_disabled', 'date_add', 'date_update', );
        $this->ci->db->select($fields)
                     ->from(MODULES_TABLE)
                     ->order_by($order_by);
        $results = $this->ci->db->get()->result_array();
        if (!empty($results)) {
            foreach ($results as $result) {
                $this->modules[$result['id']] = $result;
            }
        }

        return $this->modules;
    }

    /**
     * Execute get_modules, if modules cache not exists
     *
     * @return array
     */
    public function return_modules()
    {
        if (!isset($this->modules) || empty($this->modules)) {
            $this->get_modules();
        }

        return $this->modules;
    }

    /**
     * Return module by identifier
     *
     * @param integer $module_id module identifier
     *
     * @return array
     */
    public function get_module_by_id($module_id)
    {
        $modules = $this->return_modules();

        return $modules[$module_id];
    }

    /**
     * Return module by guid
     *
     * $param string $module_gid module guid
     *
     * @return array/false
     */
    public function get_module_by_gid($module_gid)
    {
        $modules = $this->return_modules();
        foreach ($modules as $module) {
            if ($module['module_gid'] === $module_gid) {
                return $module;
            }
        }

        return array();
    }

    /**
     * Return status of module installed
     *
     * @param string $module_gid module guid
     *
     * @return boolean
     */
    public function is_module_installed($module_gid)
    {
        return array() !== $this->get_module_by_gid($module_gid);
    }

    /**
     * Is module active
     *
     * @param string $module_gid
     *
     * @return boolean
     */
    public function is_module_active($module_gid)
    {
        if (!$this->is_module_installed($module_gid)) {
            return false;
        } else {
            $module = $this->get_module_by_gid($module_gid);

            return !$module['is_disabled'];
        }
    }

    /**
     * Install module data
     *
     * @param array $data module data
     *
     * @return void
     */
    public function set_module_install($data)
    {
        $this->ci->db->insert(MODULES_TABLE, $data);
        $this->get_modules();

        return true;
    }

    /**
     * Update module data
     *
     * @param string $module_gid module guid
     * @param array  $data       module data
     *
     * @return void
     */
    public function set_module_update($module_gid, $data)
    {
        $this->ci->db->where('module_gid', $module_gid)
                     ->update(MODULES_TABLE, $data);
        $this->get_modules();

        return true;
    }

    /**
     * Set module disabled
     *
     * @param string $module_gid
     * @param bool   $is_disabled
     *
     * @return bool
     */
    public function setModuleDisabled($module_gid, $is_disabled = true)
    {
        return $this->set_module_update($module_gid, array('is_disabled' => $is_disabled));
    }

    /**
     * Uninstall module data
     *
     * @param string $module_gid module guid
     *
     * @return void
     */
    public function set_module_uninstall($module_gid)
    {
        $this->ci->db->where('module_gid', $module_gid)
                     ->delete(MODULES_TABLE);
        $this->delete_module_all_config($module_gid);
        $this->get_modules();

        return;
    }

    /**
     * Return all module settings
     *
     * @param string $module_gid module guid
     *
     * @return array
     */
    public function get_module_all_config($module_gid)
    {
        unset($this->config[$module_gid]);
        $this->config[$module_gid] = array();
        $this->ci->db->select('config_gid, value')
                     ->from(MODULES_CONFIG_TABLE)
                     ->where('module_gid', $module_gid)
                     ->order_by('config_gid DESC');
        $results = $this->ci->db->get()->result_array();
        if (!empty($results)) {
            foreach ($results as $result) {
                $this->config[$module_gid][$result['config_gid']] = $result['value'];
            }
        }

        return $this->config[$module_gid];
    }

    /**
     * Return cache if exists, get from base if not
     *
     * @param string $module_gid module guid
     *
     * @return array
     */
    public function return_module_all_config($module_gid)
    {
        if (empty($this->config[$module_gid])) {
            $this->get_module_all_config($module_gid);
        }

        return $this->config[$module_gid];
    }

    /**
     * Update module settings
     *
     * @param string $module_gid module guid
     * @param array  $data       module settings
     *
     * @return void
     */
    public function set_module_all_config($module_gid, $data)
    {
        if (empty($data)) {
            return;
        }
        $update_data = $this->return_module_all_config($module_gid);

        foreach ($data as $config_gid => $value) {
            if (isset($update_data[$config_gid])) {
                $this->ci->db->set('value', $value)
                             ->where('module_gid', $module_gid)
                             ->where('config_gid', $config_gid)
                             ->update(MODULES_CONFIG_TABLE);
            } else {
                $this->ci->db->insert(MODULES_CONFIG_TABLE, array(
                    'value'      => $value,
                    'module_gid' => $module_gid,
                    'config_gid' => $config_gid,
                ));
            }
        }

        $this->get_module_all_config($module_gid);

        return;
    }

    /**
     * Remove all module settings
     *
     * @param string $module_gid module guid
     *
     * @return void
     */
    public function delete_module_all_config($module_gid)
    {
        $this->ci->db->where('module_gid', $module_gid)
                     ->delete(MODULES_CONFIG_TABLE);
        $this->get_module_all_config($module_gid);

        return;
    }

    /**
     * Return module option value by guid
     *
     * @param string $module_gid module guid
     * @param string $config_gid option guid
     *
     * @return mixed string/boolean
     */
    public function get_module_config($module_gid, $config_gid)
    {
        $config = $this->return_module_all_config($module_gid);
        if (isset($config[$config_gid])) {
            return $config[$config_gid];
        } else {
            return false;
        }
    }

    /**
     * Update module option value
     *
     * @param string $module_gid module guid
     * @param string $config_gid option guid
     * @param string $value      option value
     *
     * @return void
     */
    public function set_module_config($module_gid, $config_gid, $value)
    {
        $config = $this->return_module_all_config($module_gid);
        if (isset($config[$config_gid])) {
            $this->ci->db->set('value', $value)
                         ->where('module_gid', $module_gid)
                         ->where('config_gid', $config_gid)
                         ->update(MODULES_CONFIG_TABLE);
        } else {
            $this->ci->db->insert(MODULES_CONFIG_TABLE, array(
                'value'      => $value,
                'module_gid' => $module_gid,
                'config_gid' => $config_gid,
            ));
        }
        $this->get_module_all_config($module_gid);

        return;
    }

    /**
     * Remove module option
     *
     * @param string $module_gid module guid
     * @param string $config_gid option guid
     *
     * @return void
     */
    public function delete_module_config($module_gid, $config_gid = null)
    {
        $this->ci->db->where('module_gid', $module_gid);
        if (!empty($config_gid)) {
            $this->ci->db->where('config_gid', $config_gid);
        }
        $this->ci->db->delete(MODULES_CONFIG_TABLE);
        $this->get_module_all_config($module_gid);

        return;
    }

    /**
     * Return all module methods as array
     *
     * @param string $module module guid
     *
     * @return array
     */
    public function get_module_methods($module)
    {
        $data = array();
        $this->ci->db->select('id, controller, method, access')
                     ->from(MODULES_METHODS_TABLE)
                     ->where('module_gid', $module);
        $results = $this->ci->db->get()->result_array();
        if (!empty($results)) {
            foreach ($results as $result) {
                $data[$result["controller"]][$result["method"]] = $result["access"];
            }
        }

        return $data;
    }

    /**
     * Check module method is accessible
     *
     * @param string $module     module name
     * @param string $controller controller name
     * @param string $method     method name
     *
     * @return integer/false
     */
    public function get_module_method_access($module, $controller, $method)
    {
        $this->ci->db->select('id, access')
                     ->from(MODULES_METHODS_TABLE)
                     ->where('module_gid', $module)
                     ->where('controller', $controller)
                     ->where('method', $method);
        $results = $this->ci->db->get()->result();
        if (!empty($results)) {
            return intval($results[0]->access);
        } else {
            return false;
        }
    }

    /**
     * Check module method is exists
     *
     * @param string $module     module name
     * @param string $controller controller name
     * @param string $method     method name
     *
     * @return boolean
     */
    public function is_module_method_exists($module, $controller, $method)
    {
        if ($this->is_module_method_exists_ns($module, $controller, $method)) {
            return true;
        }
        $controller_file = MODULEPATH . $module . '/controllers/' . $controller . EXT;
        if (file_exists($controller_file)) {
            require_once $controller_file;
            $model_methods = get_class_methods(ucfirst($controller));

            return (!empty($model_methods) && in_array($method, $model_methods));
        }

        return false;
    }

    public function is_module_method_exists_ns($module, $controller, $method)
    {
        $class = NS_MODULES . ucfirst($module) . '\\Controllers\\' . ucfirst($controller);
        $model_methods = get_class_methods($class);
        if(empty($model_methods)) {
            return false;
        } else {
            
            $method_camel_case = preg_replace_callback(
                '/_([a-z])/', 
                function ($string) {
                    return strtoupper($string[1]);
                }, 
                $method
            );

            return in_array($method, $model_methods) || in_array($method_camel_case, $model_methods);
        }
    }

    /**
     * Clean up module methods permissions
     *
     * @param string $module_gid module name
     *
     * @return void
     */
    public function clear_module_metods_access($module_gid)
    {
        $this->ci->db->where('module_gid', $module_gid)
                     ->delete(MODULES_METHODS_TABLE);
    }

    /**
     * Add module methods permissions
     *
     * @param string $module_gid   module name
     * @param string $controller   controller name
     * @param array  $methods_data methods data
     *
     * @return void
     */
    public function set_module_methods_access($module_gid, $controller, $methods_data)
    {
        if (empty($methods_data)) {
            return;
        }
        foreach ($methods_data as $method => $access) {
            $this->ci->db->insert(MODULES_METHODS_TABLE, array(
                'method'     => $method,
                'access'     => $access,
                'module_gid' => $module_gid,
                'controller' => $controller,
            ));
        }

        return;
    }

    // generate functions

    /**
     * Generate module permissions installer
     *
     * @param string $module_gid module guid
     *
     * @return string
     */
    public function generate_install_module_permissions($module_gid)
    {
        $data = $this->get_module_methods($module_gid);
        $return = "<?php\n\n";

        foreach ($data as $controller => $method_data) {
            ksort($method_data);
            foreach ($method_data as $method => $access) {
                $return .= '$_permissions["' . $controller . '"]["' . $method . '"] = "' . $access . '";' . "\n";
            }
            $return .= "\n";
        }

        return $return;
    }

    /**
     * Generate module settings installer
     *
     * @param string $module_gid module guid
     *
     * @return string
     */
    public function generate_install_module_settings($module_gid)
    {
        $data = $this->return_module_all_config($module_gid);
        $return = "<?php\n\n";

        foreach ($data as $gid => $value) {
            $return .= '$install_settings["' . $gid . '"] = "' . str_replace('"', '\"', $value) . '";' . "\n";
        }
        $return .= "\n";

        return $return;
    }
}
