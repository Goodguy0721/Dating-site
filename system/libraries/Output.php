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
 * Output Class
 *
 * Responsible for sending final output to browser
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 *
 * @category	Output
 *
 * @author		ExpressionEngine Dev Team
 *
 * @link		http://codeigniter.com/user_guide/libraries/output.html
 */
class CI_Output
{
    public $final_output;
    public $cache_expiration    = 0;
    public $headers            = array();
    public $enable_profiler    = false;

    public function __construct()
    {
        log_message('debug', "Output Class Initialized");
    }

    // --------------------------------------------------------------------

    /**
     * Get Output
     *
     * Returns the current output string
     *
     * @return string
     */
    public function get_output()
    {
        return $this->final_output;
    }

    // --------------------------------------------------------------------

    /**
     * Set Output
     *
     * Sets the output string
     *
     * @param	string
     *
     * @return void
     */
    public function set_output($output)
    {
        $this->final_output = $output;
    }

    // --------------------------------------------------------------------

    /**
     * Append Output
     *
     * Appends data onto the output string
     *
     * @param	string
     *
     * @return void
     */
    public function append_output($output)
    {
        if ($this->final_output == '') {
            $this->final_output = $output;
        } else {
            $this->final_output .= $output;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Set Header
     *
     * Lets you set a server header which will be outputted with the final display.
     *
     * Note:  If a file is cached, headers will not be sent.  We need to figure out
     * how to permit header data to be saved with the cache data...
     *
     * @param	string
     *
     * @return void
     */
    public function set_header($header, $replace = true)
    {
        $this->headers[] = array($header, $replace);
    }

    // --------------------------------------------------------------------

    /**
     * Set HTTP Status Header
     *
     * @param	int 	the status code
     * @param	string
     *
     * @return void
     */
    public function set_status_header($code = '200', $text = '')
    {
        $stati = array(
                            '200'    => 'OK',
                            '201'    => 'Created',
                            '202'    => 'Accepted',
                            '203'    => 'Non-Authoritative Information',
                            '204'    => 'No Content',
                            '205'    => 'Reset Content',
                            '206'    => 'Partial Content',

                            '300'    => 'Multiple Choices',
                            '301'    => 'Moved Permanently',
                            '302'    => 'Found',
                            '304'    => 'Not Modified',
                            '305'    => 'Use Proxy',
                            '307'    => 'Temporary Redirect',

                            '400'    => 'Bad Request',
                            '401'    => 'Unauthorized',
                            '403'    => 'Forbidden',
                            '404'    => 'Not Found',
                            '405'    => 'Method Not Allowed',
                            '406'    => 'Not Acceptable',
                            '407'    => 'Proxy Authentication Required',
                            '408'    => 'Request Timeout',
                            '409'    => 'Conflict',
                            '410'    => 'Gone',
                            '411'    => 'Length Required',
                            '412'    => 'Precondition Failed',
                            '413'    => 'Request Entity Too Large',
                            '414'    => 'Request-URI Too Long',
                            '415'    => 'Unsupported Media Type',
                            '416'    => 'Requested Range Not Satisfiable',
                            '417'    => 'Expectation Failed',

                            '500'    => 'Internal Server Error',
                            '501'    => 'Not Implemented',
                            '502'    => 'Bad Gateway',
                            '503'    => 'Service Unavailable',
                            '504'    => 'Gateway Timeout',
                            '505'    => 'HTTP Version Not Supported',
                        );

        if ($code == '' or !is_numeric($code)) {
            show_error('Status codes must be numeric');
        }

        if (isset($stati[$code]) and $text == '') {
            $text = $stati[$code];
        }

        if ($text == '') {
            show_error('No status text available.  Please check your status code number or supply your own message text.');
        }

        $server_protocol = (isset($_SERVER['SERVER_PROTOCOL'])) ? $_SERVER['SERVER_PROTOCOL'] : false;

        if (substr(php_sapi_name(), 0, 3) == 'cgi') {
            header("Status: {$code} {$text}", true);
        } elseif ($server_protocol == 'HTTP/1.1' or $server_protocol == 'HTTP/1.0') {
            header($server_protocol . " {$code} {$text}", true, $code);
        } else {
            header("HTTP/1.1 {$code} {$text}", true, $code);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Enable/disable Profiler
     *
     * @param	bool
     *
     * @return void
     */
    public function enable_profiler($val = true)
    {
        $this->enable_profiler = (is_bool($val)) ? $val : true;
    }

    // --------------------------------------------------------------------

    /**
     * Set Cache
     *
     * @param	integer
     *
     * @return void
     */
    public function cache($time)
    {
        $this->cache_expiration = (!is_numeric($time)) ? 0 : $time;
    }

    // --------------------------------------------------------------------

    /**
     * Display Output
     *
     * All "view" data is automatically put into this variable by the controller class:
     *
     * $this->final_output
     *
     * This function sends the finalized output data to the browser along
     * with any server headers and profile data.  It also stops the
     * benchmark timer so the page rendering speed and memory usage can be shown.
     *
     * @return mixed
     */
    public function _display($output = '')
    {
        // Note:  We use globals because we can't use $CI =& get_instance()
        // since this function is sometimes called by the caching mechanism,
        // which happens before the CI super object is available.
        global $BM, $CFG;

        // --------------------------------------------------------------------

        // Set the output data
        if ($output == '') {
            $output = &$this->final_output;
        }

        // --------------------------------------------------------------------

        // Do we need to write a cache file?
        if ($this->cache_expiration > 0) {
            $this->_write_cache($output);
        }

        // --------------------------------------------------------------------

        // Parse out the elapsed time and memory usage,
        // then swap the pseudo-variables with the data

        $elapsed = $BM ? $BM->elapsed_time('total_execution_time_start', 'total_execution_time_end') : 0;
        $output = str_replace('{elapsed_time}', $elapsed, $output);

        $memory     = (!function_exists('memory_get_usage')) ? '0' : round(memory_get_usage() / 1024 / 1024, 2) . 'MB';
        $output = str_replace('{memory_usage}', $memory, $output);

        // --------------------------------------------------------------------

        // Is compression requested?
        if ($CFG && $CFG->item('compress_output') === true) {
            if (extension_loaded('zlib')) {
                if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) and strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
                    ob_start('ob_gzhandler');
                }
            }
        }

        // --------------------------------------------------------------------

        // Are there any server headers to send?
        if (count($this->headers) > 0) {
            foreach ($this->headers as $header) {
                @header($header[0], $header[1]);
            }
        }

        // --------------------------------------------------------------------

        // Does the get_instance() function exist?
        // If not we know we are dealing with a cache file so we'll
        // simply echo out the data and exit.
        if (!function_exists('get_instance')) {
            echo $output;
            log_message('debug', "Final output sent to browser");
            log_message('debug', "Total execution time: " . $elapsed);

            return true;
        }

        // --------------------------------------------------------------------

        // Grab the super object.  We'll need it in a moment...
        $CI = &get_instance();

        // Do we need to generate profile data?
        // If so, load the Profile class and run it.
        if ($this->enable_profiler == true) {
            $CI->load->library('profiler');

            // If the output data contains closing </body> and </html> tags
            // we will remove them and add them back after we insert the profile data
            if (preg_match("|</body>.*?</html>|is", $output)) {
                $output  = preg_replace("|</body>.*?</html>|is", '', $output);
                $output .= $CI->profiler->run();
                $output .= '</body></html>';
            } else {
                $output .= $CI->profiler->run();
            }
        }

        // --------------------------------------------------------------------

        // Does the controller contain a function named _output()?
        // If so send the output there.  Otherwise, echo it.
        if (method_exists($CI, '_output')) {
            $CI->_output($output);
        } else {
            echo $output;  // Send it to the browser!
        }

        log_message('debug', "Final output sent to browser");
        log_message('debug', "Total execution time: " . $elapsed);
    }

    // --------------------------------------------------------------------

    /**
     * Write a Cache File
     *
     * @return void
     */
    public function _write_cache($output)
    {
        $CI = &get_instance();
        $path = $CI->config->item('cache_path');

        $cache_path = ($path == '') ? BASEPATH . 'cache/' : $path;

        if (!is_dir($cache_path) or !is_really_writable($cache_path)) {
            return;
        }

        $uri =    $CI->config->item('base_url') .
                $CI->config->item('index_page') .
                $CI->uri->uri_string();

        $cache_path .= md5($uri);

        if (!$fp = @fopen($cache_path, FOPEN_WRITE_CREATE_DESTRUCTIVE)) {
            log_message('error', "Unable to write cache file: " . $cache_path);

            return;
        }

        $expire = time() + ($this->cache_expiration * 60);

        if (flock($fp, LOCK_EX)) {
            fwrite($fp, $expire . 'TS--->' . $output);
            flock($fp, LOCK_UN);
        } else {
            log_message('error', "Unable to secure a file lock for file at: " . $cache_path);

            return;
        }
        fclose($fp);
        @chmod($cache_path, DIR_WRITE_MODE);

        log_message('debug', "Cache file written: " . $cache_path);
    }

    // --------------------------------------------------------------------

    /**
     * Update/serve a cached file
     *
     * @return void
     */
    public function _display_cache(&$CFG, &$URI)
    {
        $cache_path = ($CFG->item('cache_path') == '') ? BASEPATH . 'cache/' : $CFG->item('cache_path');

        if (!is_dir($cache_path) or !is_really_writable($cache_path)) {
            return false;
        }

        // Build the file path.  The file name is an MD5 hash of the full URI
        $uri =    $CFG->item('base_url') .
                $CFG->item('index_page') .
                $URI->uri_string;

        $filepath = $cache_path . md5($uri);

        if (!@file_exists($filepath)) {
            return false;
        }

        if (!$fp = @fopen($filepath, FOPEN_READ)) {
            return false;
        }

        flock($fp, LOCK_SH);

        $cache = '';
        if (filesize($filepath) > 0) {
            $cache = fread($fp, filesize($filepath));
        }

        flock($fp, LOCK_UN);
        fclose($fp);

        // Strip out the embedded timestamp
        if (!preg_match("/(\d+TS--->)/", $cache, $match)) {
            return false;
        }

        // Has the file expired? If so we'll delete it.
        if (time() >= trim(str_replace('TS--->', '', $match['1']))) {
            @unlink($filepath);
            log_message('debug', "Cache file has expired. File deleted");

            return false;
        }

        // Display the cache
        $this->_display(str_replace($match['0'], '', $cache));
        log_message('debug', "Cache file is current. Sending it to browser.");

        return true;
    }
}
// END Output Class

/* End of file Output.php */
/* Location: ./system/libraries/Output.php */
