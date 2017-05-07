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
 * URI Class
 *
 * Parses URIs and determines routing
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 *
 * @category	URI
 *
 * @author		ExpressionEngine Dev Team
 *
 * @link		http://codeigniter.com/user_guide/libraries/uri.html
 */
class CI_URI
{
    public $keyval    = array();
    public $uri_string;
    public $segments        = array();
    public $rsegments        = array();

    /**
     * Constructor
     *
     * Simply globalizes the $RTR object.  The front
     * loads the Router class early on so it's not available
     * normally as other classes are.
     */
    public function __construct()
    {
        $this->config = &load_class('Config');
        log_message('debug', "URI Class Initialized");
    }

    // --------------------------------------------------------------------

    /**
     * Get the URI String
     *
     * @return string
     */
    public function _fetch_uri_string()
    {
        if (strtoupper($this->config->item('uri_protocol')) == 'AUTO') {
            // If the URL has a question mark then it's simplest to just
            // build the URI string from the zero index of the $_GET array.
            // This avoids having to deal with $_SERVER variables, which
            // can be unreliable in some environments
            if (is_array($_GET) && count($_GET) == 1 && trim(key($_GET), '/') != '') {
                $this->uri_string = urldecode(urldecode(urldecode(key($_GET))));

                return;
            }

            // Is there a PATH_INFO variable?
            // Note: some servers seem to have trouble with getenv() so we'll test it two ways
            $path = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : @getenv('PATH_INFO');
            if (trim($path, '/') != '' && $path != "/" . SELF) {
                $this->uri_string = urldecode(urldecode(urldecode($path)));

                return;
            }

            // No PATH_INFO?... What about QUERY_STRING?
            $path =  (isset($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : @getenv('QUERY_STRING');
            if (trim($path, '/') != '') {
                $this->uri_string = urldecode(urldecode(urldecode($path)));

                return;
            }

            // No QUERY_STRING?... Maybe the ORIG_PATH_INFO variable exists?
            $path = (isset($_SERVER['ORIG_PATH_INFO'])) ? $_SERVER['ORIG_PATH_INFO'] : @getenv('ORIG_PATH_INFO');
            if (trim($path, '/') != '' && $path != "/" . SELF) {
                // remove path and script information so we have good URI data
                $this->uri_string = urldecode(urldecode(urldecode(str_replace($_SERVER['SCRIPT_NAME'], '', $path))));

                return;
            }

            // We've exhausted all our options...
            $this->uri_string = '';
        } else {
            $uri = strtoupper($this->config->item('uri_protocol'));

            if ($uri == 'REQUEST_URI') {
                $this->uri_string = urldecode(urldecode(urldecode($this->_parse_request_uri())));

                return;
            }

            $this->uri_string = urldecode(urldecode(urldecode((isset($_SERVER[$uri])) ? $_SERVER[$uri] : @getenv($uri))));
        }

        // If the URI contains only a slash we'll kill it
        if ($this->uri_string == '/') {
            $this->uri_string = '';
        }
    }

    // --------------------------------------------------------------------

    /**
     * Parse the REQUEST_URI
     *
     * Due to the way REQUEST_URI works it usually contains path info
     * that makes it unusable as URI data.  We'll trim off the unnecessary
     * data, hopefully arriving at a valid URI that we can use.
     *
     * @return string
     */
    public function _parse_request_uri()
    {
        if (!isset($_SERVER['REQUEST_URI']) or $_SERVER['REQUEST_URI'] == '') {
            return '';
        }

        $request_uri = preg_replace("|/(.*)|", "\\1", str_replace("\\", "/", $_SERVER['REQUEST_URI']));

        // TODO: Remove query string
        $request_uri = preg_replace('|\?.*$|', '', $request_uri);

        if ($request_uri == '' or $request_uri == SELF) {
            return '';
        }

        $fc_path = FCPATH;
        if (strpos($request_uri, '?') !== false) {
            $fc_path .= '?';
        }

        $parsed_uri = explode("/", $request_uri);

        $i = 0;
        foreach (explode("/", $fc_path) as $segment) {
            if (isset($parsed_uri[$i]) && $segment == $parsed_uri[$i]) {
                ++$i;
            }
        }

        $parsed_uri = implode("/", array_slice($parsed_uri, $i));

        if ($parsed_uri != '') {
            $parsed_uri = '/' . $parsed_uri;
        }

        return $parsed_uri;
    }

    // --------------------------------------------------------------------

    /**
     * Filter segments for malicious characters
     *
     * @param	string
     *
     * @return string
     */
    public function _filter_uri($str)
    {
        if ($str != '' && $this->config->item('permitted_uri_chars') != '' && $this->config->item('enable_query_strings') == false) {
            // preg_quote() in PHP 5.3 escapes -, so the str_replace() and addition of - to preg_quote() is to maintain backwards
            // compatibility as many are unaware of how characters in the permitted_uri_chars will be parsed as a regex pattern
            if (!preg_match("|^[" . str_replace(array('\\-', '\-', '\pL', '\pN', '\pM', '\pZ'), array('-', '-', 'pL', 'pN', 'pM', 'pZ'), preg_quote($this->config->item('permitted_uri_chars'), '-')) . "]+$|iu", $str)) {
                show_error('The URI you submitted has disallowed characters.', 400);
            }
        }

        // Convert programatic characters to entities
        $bad    = array('$',        '(',        ')',        '%28',        '%29');
        $good    = array('&#36;',    '&#40;',    '&#41;',    '&#40;',    '&#41;');

        return str_replace($bad, $good, $str);
    }

    public function _unfilter_uri($str)
    {
        $search  = array('&#36;',    '&#40;',    '&#41;',    '&#40;',    '&#41;');
        $replace = array('$',        '(',        ')',        '%28',        '%29');

        return str_replace($search, $replace, $str);
    }

    // --------------------------------------------------------------------

    /**
     * Remove the suffix from the URL if needed
     *
     * @return void
     */
    public function _remove_url_suffix()
    {
        if ($this->config->item('url_suffix') != "") {
            $this->uri_string = preg_replace("|" . preg_quote($this->config->item('url_suffix')) . "$|", "", $this->uri_string);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Explode the URI Segments. The individual segments will
     * be stored in the $this->segments array.
     *
     * @return void
     */
    public function _explode_segments()
    {
        $sts = explode("/", preg_replace("|/*(.+?)/*$|", "\\1", $this->uri_string));
        foreach ($sts as $val) {
            // Filter segments for security
            $val = trim($this->_filter_uri($val));
            $t = explode('?', $val);
            $val = $t[0];

            if ($val != '') {
                $this->segments[] = $val;
            }
        }
    }

    // --------------------------------------------------------------------
    /**
     * Re-index Segments
     *
     * This function re-indexes the $this->segment array so that it
     * starts at 1 rather than 0.  Doing so makes it simpler to
     * use functions like $this->uri->segment(n) since there is
     * a 1:1 relationship between the segment array and the actual segments.
     *
     * @return void
     */
    public function _reindex_segments()
    {
        array_unshift($this->segments, null);
        array_unshift($this->rsegments, null);
        unset($this->segments[0]);
        unset($this->rsegments[0]);
    }

    // --------------------------------------------------------------------

    /**
     * Fetch a URI Segment
     *
     * This function returns the URI segment based on the number provided.
     *
     * @param	integer
     * @param	bool
     *
     * @return string
     */
    public function segment($n, $no_result = false)
    {
        return (!isset($this->segments[$n])) ? $no_result : $this->segments[$n];
    }

    // --------------------------------------------------------------------

    /**
     * Fetch a URI "routed" Segment
     *
     * This function returns the re-routed URI segment (assuming routing rules are used)
     * based on the number provided.  If there is no routing this function returns the
     * same result as $this->segment()
     *
     * @param	integer
     * @param	bool
     *
     * @return string
     */
    public function rsegment($n, $no_result = false)
    {
        return (!isset($this->rsegments[$n])) ? $no_result : $this->rsegments[$n];
    }

    // --------------------------------------------------------------------

    /**
     * Generate a key value pair from the URI string
     *
     * This function generates and associative array of URI data starting
     * at the supplied segment. For example, if this is your URI:
     *
     *	example.com/user/search/name/joe/location/UK/gender/male
     *
     * You can use this function to generate an array with this prototype:
     *
     * array (
     *			name => joe
     *			location => UK
     *			gender => male
     *		 )
     *
     * @param	integer	the starting segment number
     * @param	array	an array of default values
     *
     * @return array
     */
    public function uri_to_assoc($n = 3, $default = array())
    {
        return $this->_uri_to_assoc($n, $default, 'segment');
    }
    /**
     * Identical to above only it uses the re-routed segment array
     */
    public function ruri_to_assoc($n = 3, $default = array())
    {
        return $this->_uri_to_assoc($n, $default, 'rsegment');
    }

    // --------------------------------------------------------------------

    /**
     * Generate a key value pair from the URI string or Re-routed URI string
     *
     * @param	integer	the starting segment number
     * @param	array	an array of default values
     * @param	string	which array we should use
     *
     * @return array
     */
    public function _uri_to_assoc($n = 3, $default = array(), $which = 'segment')
    {
        if ($which == 'segment') {
            $total_segments = 'total_segments';
            $segment_array = 'segment_array';
        } else {
            $total_segments = 'total_rsegments';
            $segment_array = 'rsegment_array';
        }

        if (!is_numeric($n)) {
            return $default;
        }

        if (isset($this->keyval[$n])) {
            return $this->keyval[$n];
        }

        if ($this->{$total_segments}() < $n) {
            if (count($default) == 0) {
                return array();
            }

            $retval = array();
            foreach ($default as $val) {
                $retval[$val] = false;
            }

            return $retval;
        }

        $segments = array_slice($this->{$segment_array}(), ($n - 1));

        $i = 0;
        $lastval = '';
        $retval  = array();
        foreach ($segments as $seg) {
            if ($i % 2) {
                $retval[$lastval] = $seg;
            } else {
                $retval[$seg] = false;
                $lastval = $seg;
            }

            ++$i;
        }

        if (count($default) > 0) {
            foreach ($default as $val) {
                if (!array_key_exists($val, $retval)) {
                    $retval[$val] = false;
                }
            }
        }

        // Cache the array for reuse
        $this->keyval[$n] = $retval;

        return $retval;
    }

    // --------------------------------------------------------------------

    /**
     * Generate a URI string from an associative array
     *
     *
     * @param	array	an associative array of key/values
     *
     * @return array
     */
    public function assoc_to_uri($array)
    {
        $temp = array();
        foreach ((array) $array as $key => $val) {
            $temp[] = $key;
            $temp[] = $val;
        }

        return implode('/', $temp);
    }

    // --------------------------------------------------------------------

    /**
     * Fetch a URI Segment and add a trailing slash
     *
     * @param	integer
     * @param	string
     *
     * @return string
     */
    public function slash_segment($n, $where = 'trailing')
    {
        return $this->_slash_segment($n, $where, 'segment');
    }

    // --------------------------------------------------------------------

    /**
     * Fetch a URI Segment and add a trailing slash
     *
     * @param	integer
     * @param	string
     *
     * @return string
     */
    public function slash_rsegment($n, $where = 'trailing')
    {
        return $this->_slash_segment($n, $where, 'rsegment');
    }

    // --------------------------------------------------------------------

    /**
     * Fetch a URI Segment and add a trailing slash - helper function
     *
     * @param	integer
     * @param	string
     * @param	string
     *
     * @return string
     */
    public function _slash_segment($n, $where = 'trailing', $which = 'segment')
    {
        if ($where == 'trailing') {
            $trailing    = '/';
            $leading    = '';
        } elseif ($where == 'leading') {
            $leading    = '/';
            $trailing    = '';
        } else {
            $leading    = '/';
            $trailing    = '/';
        }

        return $leading . $this->{$which}($n) . $trailing;
    }

    // --------------------------------------------------------------------

    /**
     * Segment Array
     *
     * @return array
     */
    public function segment_array()
    {
        return $this->segments;
    }

    // --------------------------------------------------------------------

    /**
     * Routed Segment Array
     *
     * @return array
     */
    public function rsegment_array()
    {
        return $this->rsegments;
    }

    // --------------------------------------------------------------------

    /**
     * Total number of segments
     *
     * @return integer
     */
    public function total_segments()
    {
        return count($this->segments);
    }

    // --------------------------------------------------------------------

    /**
     * Total number of routed segments
     *
     * @return integer
     */
    public function total_rsegments()
    {
        return count($this->rsegments);
    }

    // --------------------------------------------------------------------

    /**
     * Fetch the entire URI string
     *
     * @return string
     */
    public function uri_string()
    {
        return $this->uri_string;
    }

    // --------------------------------------------------------------------

    /**
     * Fetch the entire Re-routed URI string
     *
     * @return string
     */
    public function ruri_string()
    {
        return '/' . implode('/', $this->rsegment_array()) . '/';
    }
}
// END URI Class

/* End of file URI.php */
/* Location: ./system/libraries/URI.php */
