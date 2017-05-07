<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package		CodeIgniter
 *
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 *
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * CodeIgniter Config Class
 *
 * This class contains functions that enable config files to be managed
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 *
 * @category	Libraries
 *
 * @author		ExpressionEngine Dev Team
 *
 * @link		http://codeigniter.com/user_guide/libraries/config.html
 */
class CI_Config
{
    public $config = array();
    public $is_loaded = array();

    /**
     * Constructor
     *
     * Sets the $config data from the primary config.php file as a class variable
     *
     * @param   string	the config file name
     * @param   boolean  if configuration values should be loaded into their own section
     * @param   boolean  true if errors should just return false, false if an error message should be displayed
     *
     * @return boolean if the file was successfully loaded or not
     */
    public function __construct()
    {
        $this->config = &get_config();
        log_message('debug', "Config Class Initialized");
    }

    // --------------------------------------------------------------------

    /**
     * Load Config File
     *
     * @param	string	the config file name
     *
     * @return boolean if the file was loaded correctly
     */
    public function load($file = '', $use_sections = false, $fail_gracefully = false)
    {
        $file = ($file == '') ? 'config' : str_replace(EXT, '', $file);

        if (in_array($file, $this->is_loaded, true)) {
            return true;
        }

        if (!file_exists(APPPATH . 'config/' . $file . EXT)) {
            if ($fail_gracefully === true) {
                return false;
            }
            show_error('The configuration file ' . $file . EXT . ' does not exist.');
        }

        include APPPATH . 'config/' . $file . EXT;

        if (!isset($config) or !is_array($config)) {
            if ($fail_gracefully === true) {
                return false;
            }
            show_error('Your ' . $file . EXT . ' file does not appear to contain a valid configuration array.');
        }

        if ($use_sections === true) {
            if (isset($this->config[$file])) {
                $this->config[$file] = array_merge($this->config[$file], $config);
            } else {
                $this->config[$file] = $config;
            }
        } else {
            $this->config = array_merge($this->config, $config);
        }

        $this->is_loaded[] = $file;
        unset($config);

        log_message('debug', 'Config file loaded: config/' . $file . EXT);

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Fetch a config file item
     *
     *
     * @param	string	the config item name
     * @param	string	the index name
     * @param	bool
     *
     * @return string
     */
    public function item($item, $index = '')
    {
        if ($index == '') {
            if (!isset($this->config[$item])) {
                return false;
            }

            $pref = $this->config[$item];
        } else {
            if (!isset($this->config[$index])) {
                return false;
            }

            if (!isset($this->config[$index][$item])) {
                return false;
            }

            $pref = $this->config[$index][$item];
        }

        return $pref;
    }

    // --------------------------------------------------------------------

    /**
     * Fetch a config file item - adds slash after item
     *
     * The second parameter allows a slash to be added to the end of
     * the item, in the case of a path.
     *
     * @param	string	the config item name
     * @param	bool
     *
     * @return string
     */
    public function slash_item($item)
    {
        if (!isset($this->config[$item])) {
            return false;
        }

        $pref = $this->config[$item];

        if ($pref != '' && substr($pref, -1) != '/') {
            $pref .= '/';
        }

        return $pref;
    }

    // --------------------------------------------------------------------

    /**
     * Fetch a config file item - removes slash after item
     *
     * @param	string	the config item name
     *
     * @return string
     */
    public function unslash_item($item)
    {
        if (!isset($this->config[$item])) {
            return false;
        }

        $pref = $this->config[$item];

        if ($pref != '' && substr($pref, -1) == '/') {
            $pref = substr($pref, 0, -1);
        }

        return $pref;
    }

    // --------------------------------------------------------------------

    /**
     * Site URL
     *
     * @param	string	the URI string
     *
     * @return string
     */
    public function site_url($uri = '')
    {
        if (is_array($uri)) {
            $uri = implode('/', $uri);
        }

        if ($uri == '') {
            if (HIDE_INDEX_PAGE) {
                return $this->slash_item('base_url');
            } else {
                return $this->slash_item('base_url') . $this->slash_item('index_page');
            }
        } else {
            $suffix = ($this->item('url_suffix') == false) ? '' : $this->item('url_suffix');
            $uri = preg_replace("|^/*(.+?)/*$|", "\\1", $uri) . '/';

            return $this->slash_item('base_url') . ((HIDE_INDEX_PAGE) ? '' : $this->slash_item('index_page')) . $uri . $suffix;
        }
    }

    // --------------------------------------------------------------------

    /**
     * System URL
     *
     * @return string
     */
    public function system_url()
    {
        $x = explode("/", preg_replace("|/*(.+?)/*$|", "\\1", BASEPATH));

        return $this->slash_item('base_url') . end($x) . '/';
    }

    // --------------------------------------------------------------------

    /**
     * Set a config file item
     *
     * @param	string	the config item key
     * @param	string	the config item value
     *
     * @return void
     */
    public function set_item($item, $value)
    {
        $this->config[$item] = $value;
    }
}

// END CI_Config class

/* End of file Config.php */
/* Location: ./system/libraries/Config.php */
