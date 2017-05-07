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
 * Exceptions Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 *
 * @category	Exceptions
 *
 * @author		ExpressionEngine Dev Team
 *
 * @link		http://codeigniter.com/user_guide/libraries/exceptions.html
 */
class CI_Exceptions
{
    public $action;
    public $severity;
    public $message;
    public $filename;
    public $line;
    public $ob_level;

    public $levels = array(
                        E_ERROR                => 'Error',
                        E_WARNING              => 'Warning',
                        E_PARSE                => 'Parsing Error',
                        E_NOTICE               => 'Notice',
                        E_CORE_ERROR           => 'Core Error',
                        E_CORE_WARNING         => 'Core Warning',
                        E_COMPILE_ERROR        => 'Compile Error',
                        E_COMPILE_WARNING      => 'Compile Warning',
                        E_USER_ERROR           => 'User Error',
                        E_USER_WARNING         => 'User Warning',
                        E_USER_NOTICE          => 'User Notice',
                        E_STRICT               => 'Runtime Notice',
                    );

    public $log_levels = array(
                        E_ERROR                => 'ERROR',
                        E_WARNING              => 'DEBUG',
                        E_PARSE                => 'ERROR',
                        E_NOTICE               => 'INFO',
                        E_CORE_ERROR           => 'ERROR',
                        E_CORE_WARNING         => 'DEBUG',
                        E_COMPILE_ERROR        => 'ERROR',
                        E_COMPILE_WARNING      => 'DEBUG',
                        E_USER_ERROR           => 'ERROR',
                        E_USER_WARNING         => 'DEBUG',
                        E_USER_NOTICE          => 'INFO',
                        E_STRICT               => 'INFO',
                    );

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ob_level = ob_get_level();
        // Note:  Do not log messages from this constructor.
    }

    // --------------------------------------------------------------------

    /**
     * Exception Logger
     *
     * This function logs PHP generated error messages
     *
     * @param	string	the error severity
     * @param	string	the error string
     * @param	string	the error filepath
     * @param	string	the error line number
     *
     * @return string
     */
    public function log_exception($severity, $message, $filepath, $line)
    {
        $log_level = (!isset($this->log_levels[$severity])) ? "ERROR" : $this->log_levels[$severity];
        $severity = (!isset($this->levels[$severity])) ? $severity : $this->levels[$severity];
        log_message($log_level, 'Severity: ' . $severity . '  --> ' . $message . ' ' . $filepath . ' ' . $line, true);
    }

    // --------------------------------------------------------------------

    /**
     * 404 Page Not Found Handler
     *
     * @param	string
     *
     * @return string
     */
    public function show_404($page = '')
    {
        $heading = "404 Page Not Found";
        $message = "The page you requested was not found.";

        log_message('error', '404 Page Not Found --> ' . $page);
        echo $this->show_error($heading, $message, 'error_404');
        exit;
    }

    // --------------------------------------------------------------------

    /**
     * General Error Page
     *
     * This function takes an error message as input
     * (either as a string or an array) and displays
     * it using the specified template.
     *
     * @param	string	the heading
     * @param	string	the message
     * @param	string	the template name
     *
     * @return string
     */
    public function show_error($heading, $message, $template = 'error_general')
    {
        $message = '<p>' . implode('</p><p>', (!is_array($message)) ? array($message) : $message) . '</p>';

        if (ob_get_level() > $this->ob_level + 1) {
            ob_end_flush();
        }
        ob_start();
        include APPPATH . 'errors/' . $template . EXT;
        $buffer = ob_get_contents();
        ob_end_clean();

        return $buffer;
    }

    // --------------------------------------------------------------------

    /**
     * Native PHP error handler
     *
     * @param	string	the error severity
     * @param	string	the error string
     * @param	string	the error filepath
     * @param	string	the error line number
     *
     * @return string
     */
    public function show_php_error($severity, $message, $filepath, $line)
    {
        $severity = (!isset($this->levels[$severity])) ? $severity : $this->levels[$severity];

        $filepath = str_replace("\\", "/", $filepath);

        // For safety reasons we do not show the full file path
        if (false !== strpos($filepath, '/')) {
            $x = explode('/', $filepath);
            $filepath = $x[count($x) - 2] . '/' . end($x);
        }

        if (ob_get_level() > $this->ob_level + 1) {
            ob_end_flush();
        }
        ob_start();
        include APPPATH . 'errors/error_php' . EXT;
        $buffer = ob_get_contents();
        ob_end_clean();
        echo $buffer;
    }
}
// END Exceptions Class

/* End of file Exceptions.php */
/* Location: ./system/libraries/Exceptions.php */
