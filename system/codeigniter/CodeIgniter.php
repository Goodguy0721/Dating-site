<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package     CodeIgeniter
 *
 * @author      ExpressionEngine Dev Team
 * @copyright   Copyright (c) 2008, EllisLab, Inc.
 * @license     http://codeigniter.com/user_guide/license.html
 *
 * @link        http://codeigniter.com
 * @since       Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------

/**
 * System Front Controller
 *
 * Loads the base classes and executes the request.
 *
 * @package     CodeIgniter
 * @subpackage  codeigniter
 *
 * @category    Front-controller
 *
 * @author      ExpressionEngine Dev Team
 *
 * @link        http://codeigniter.com/user_guide/
 *
 * @version $Revision: 387 $ $Date: 2010-09-15 08:35:53 +0400 (Ср, 15 сен 2010) $ $Author: kkashkova $
 */
// CI Version
define('CI_VERSION', '1.7.0');

/*
 * ------------------------------------------------------
 *  Load the global functions
 * ------------------------------------------------------
 */
require BASEPATH . 'codeigniter/Common' . EXT;

/*
 * ------------------------------------------------------
 *  Load the compatibility override functions
 * ------------------------------------------------------
 */
require BASEPATH . 'codeigniter/Compat' . EXT;

/*
 * ------------------------------------------------------
 *  Load the framework constants
 * ------------------------------------------------------
 */
require APPPATH . 'config/constants' . EXT;

spl_autoload_register(function ($class_name) {
    $class_name = strtolower($class_name);
    if (substr($class_name, 0, 6) !== 'class_') {
        return false;
    } else {
        $module = str_replace('class_', '', $class_name);
        $file = MODULEPATH . $module . '/controllers/' . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;

            return true;
        } else {
            return false;
        }
    }
});

// Autoloading libraries
spl_autoload_register(function ($class) {
    if (strpos($class, NS_LIB) !== 0) {
        return false;
    }

    $segments = explode('\\', substr($class, strlen(NS_LIB)));
    $relative_path = implode(DIRECTORY_SEPARATOR, $segments);

    $file = LIBPATH . $relative_path . EXT;
    if (file_exists($file)) {
        require $file;

        return true;
    }
});

// Autoloading modules
// 
// TODO: пределать для подгрузки одним автолодером
spl_autoload_register(function ($class) {
    if (strpos($class, NS_MODULES) !== 0) {
        return false;
    }

    $segments = explode('\\', substr($class, strlen(NS_MODULES)));
    array_unshift($segments, strtolower(array_shift($segments)));
    $relative_path = implode(DIRECTORY_SEPARATOR, $segments);

    $file = MODULEPATH . $relative_path . EXT;
    if (file_exists($file)) {
        require $file;

        return true;
    }
});

// Autoloading by namespaces
spl_autoload_register(function ($class) {
    if (strpos($class, NS_PREFIX) !== 0) {
        return false;
    }
    // TODO: Add 'fast' autoloading here

    $segments = explode('\\', substr($class, strlen(NS_PREFIX)));
    array_unshift($segments, strtolower(array_shift($segments)));
    $filename = array_pop($segments);

    $relative_segment = strtolower(implode(DIRECTORY_SEPARATOR, $segments));

    $file = APPPATH . $relative_segment . DIRECTORY_SEPARATOR . $filename . EXT;
    if (file_exists($file)) {
        require_once $file;

        return true;
    }

    $file = APPPATH . $relative_segment . DIRECTORY_SEPARATOR . strtolower($filename) . EXT;
    if (file_exists($file)) {
        require_once $file;

        return true;
    }

    return false;
});

require SITE_PHYSICAL_PATH . 'vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(SITE_PHYSICAL_PATH, '.env.' . ENVIRONMENT);
$dotenv->load();

/*
|---------------------------------------------------------------
| PHP ERROR REPORTING LEVEL
|---------------------------------------------------------------
*/
ini_set("display_errors", $_ENV['DISPLAY_ERRORS'] || DISPLAY_ERRORS);
error_reporting($_ENV['DISPLAY_ERRORS'] || DISPLAY_ERRORS ? E_ALL : ~E_ALL);

/*
 * ------------------------------------------------------
 *  Define a custom error handler so we can log PHP errors
 * ------------------------------------------------------
 */
set_error_handler('_exception_handler');
//@set_magic_quotes_runtime(0); // Kill magic quotes, deprecated in php 5.3

/*
 * ------------------------------------------------------
 *  Start the timer... tick tock tick tock...
 * ------------------------------------------------------
 */

$BM = &load_class('Benchmark');
$BM->mark('total_execution_time_start');
$BM->mark('loading_time_base_classes_start');

/*
 * ------------------------------------------------------
 *  Instantiate the hooks class
 * ------------------------------------------------------
 */

@include APPPATH . 'config/routes' . EXT;

$EXT = &load_class('Hooks');

/*
 * ------------------------------------------------------
 *  Is there a "pre_system" hook?
 * ------------------------------------------------------
 */
$EXT->_call_hook('pre_system');

/*
 * ------------------------------------------------------
 *  Instantiate the base classes
 * ------------------------------------------------------
 */

$CFG = &load_class('Config');
$URI = &load_class('URI');
$RTR = &load_class('Router');
$OUT = &load_class('Output');

/*
 * ------------------------------------------------------
 *  Is there a valid cache file?  If so, we're done...
 * ------------------------------------------------------
 */

if ($EXT->_call_hook('cache_override') === false) {
    if ($OUT->_display_cache($CFG, $URI) == true) {
        exit;
    }
}

/*
 * ------------------------------------------------------
 *  Load the remaining base classes
 * ------------------------------------------------------
 */

$IN = &load_class('Input');
$LANG = &load_class('Language');

/*
 * ------------------------------------------------------
 *  Load the app controller and local controller
 * ------------------------------------------------------
 *
 *  Note: Due to the poor object handling in PHP 4 we'll
 *  conditionally load different versions of the base
 *  class.  Retaining PHP 4 compatibility requires a bit of a hack.
 *
 *  Note: The Loader class needs to be included first
 *
 */
require BASEPATH . 'codeigniter/Base' . EXT;

// Load the base controller class
load_class('Controller', false);

$custom_class = $RTR->fetch_custom_class(true);

// Load the local application controller
// Note: The Router class automatically validates the controller path.  If this include fails it
// means that the default controller in the Routes.php file is not resolving to something valid.
$controller_class = MODULEPATH . $RTR->fetch_directory() . $RTR->fetch_class() . '/controllers/'
        . $RTR->fetch_directory() . $custom_class . EXT;
if ((@include($controller_class)) === false) {
    if (CUSTOM_MODE) {
        $custom_class = preg_replace('#^' . preg_quote(trim(CUSTOM_MODE, '_') . '_', '#') . '#', '', $custom_class);
        $controller_class = MODULEPATH . $RTR->fetch_directory() . $RTR->fetch_class() . '/controllers/'
            . $RTR->fetch_directory() . $custom_class . EXT;
        if ((@include($controller_class)) === false) {
            new Controller();
            show_404();
        }
    } else {
        new Controller();
        show_404();
    }
}

// Set a mark point for benchmarking
$BM->mark('loading_time_base_classes_end');

/*
 * ------------------------------------------------------
 *  Security check
 * ------------------------------------------------------
 *
 *  None of the functions in the app controller or the
 *  loader class can be called via the URI, nor can
 *  controller functions that begin with an underscore
 */

$class = $custom_class;
$method = $RTR->fetch_method();
if (!class_exists($class, false)) {
    // Try with namespace
    $class_ns = NS_MODULES . $RTR->fetch_class() . '\\controllers\\' . $class;
    if (class_exists($class_ns, false)) {
        $class = $class_ns;
    }
    if ($method == 'controller' || strncmp($method, '_', 1) == 0 || in_array(strtolower($method), array_map('strtolower', get_class_methods('Controller')))
    ) {
        show_404("{$class}/{$method}");
    }
}

/*
 * ------------------------------------------------------
 *  Is there a "pre_controller" hook?
 * ------------------------------------------------------
 */
$EXT->_call_hook('pre_controller');

/*
 * ------------------------------------------------------
 *  Instantiate the controller and call requested method
 * ------------------------------------------------------
 */

// Mark a start point so we can benchmark the controller
$BM->mark('controller_execution_time_( ' . $class . ' / ' . $method . ' )_start');

$CI = new $class();

// Is this a scaffolding request?
if ($RTR->scaffolding_request === true) {
    if ($EXT->_call_hook('scaffolding_override') === false) {
        $CI->_ci_scaffolding();
    }
} else {
    /*
     * ------------------------------------------------------
     *  Is there a "post_controller_constructor" hook?
     * ------------------------------------------------------
     */
    $EXT->_call_hook('post_controller_constructor');

    // Is there a "remap" function?
    if (method_exists($CI, '_remap')) {
        $CI->_remap($method);
    } elseif ($CI->view->isRendered()) {
    } else {
        if (!is_callable(array($CI, $method))) {
            $ccMethod = str_replace(' ', '', ucwords(str_replace('_', ' ', $method)));
            if (!is_callable(array($CI, $ccMethod))) {
                show_404("{$class}/{$method}");
            } else {
                $method = $ccMethod;
            }
        }

        // Call the requested method.
        // Any URI segments present (besides the class/function) will be passed to the method for convenience
        call_user_func_array(array(&$CI, $method), array_slice($URI->rsegments, 2));
    }
}

// Mark a benchmark end point
$BM->mark('controller_execution_time_( ' . $class . ' / ' . $method . ' )_end');

/*
 * ------------------------------------------------------
 *  Is there a "post_controller" hook?
 * ------------------------------------------------------
 */
$EXT->_call_hook('post_controller');

/*
 * ------------------------------------------------------
 *  Send the final rendered output to the browser
 * ------------------------------------------------------
 */

if ($EXT->_call_hook('display_override') === false) {
    $OUT->_display();
}

/*
 * ------------------------------------------------------
 *  Is there a "post_system" hook?
 * ------------------------------------------------------
 */
$EXT->_call_hook('post_system');

/*
 * ------------------------------------------------------
 *  Close the DB connection if one exists
 * ------------------------------------------------------
 */
if (class_exists('CI_DB') && isset($CI->db)) {
    $CI->db->close();
}

/* End of file CodeIgniter.php */
/* Location: ./system/codeigniter/CodeIgniter.php */
