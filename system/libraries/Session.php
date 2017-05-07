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
 * Session Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 *
 * @category	Sessions
 *
 * @author		ExpressionEngine Dev Team
 *
 * @link		http://codeigniter.com/user_guide/libraries/sessions.html
 */
class CI_Session
{
    public $sess_encrypt_cookie        = false;
    public $sess_use_database        = true;
    public $sess_table_name            = '';
    public $api_table_name            = '';
    public $sess_expiration            = 7200;        // 2 hours
    public $token_expiration        = 2592000;    // 1 month
    public $sess_match_ip            = false;
    public $sess_match_useragent    = true;
    public $sess_cookie_name        = 'ci_session';
    public $cookie_prefix            = '';
    public $api_cookie_prefix        = 'pgapi';
    public $cookie_path                = '';
    public $cookie_domain            = '';
    public $sess_time_to_update        = 300;
    public $encryption_key            = '';
    public $flashdata_key            = 'flash';
    public $time_reference            = 'time';
    public $gc_probability            = 5;
    public $userdata                = array();
    public $CI;
    public $now;

    /**
     * Session Constructor
     *
     * The constructor runs the session routines automatically
     * whenever the class is instantiated.
     */
    public function __construct($params = array())
    {
        log_message('debug', "Session Class Initialized");

        // Set the super object to a local variable for use throughout the class
        $this->CI = &get_instance();

        // Set all the session preferences, which can either be set
        // manually via the $params array above or via the config file
        foreach (array('sess_encrypt_cookie', 'sess_use_database', 'sess_table_name', 'api_table_name', 'sess_expiration', 'token_expiration', 'sess_match_ip', 'sess_match_useragent', 'sess_cookie_name', 'cookie_path', 'cookie_domain', 'sess_time_to_update', 'time_reference', 'cookie_prefix', 'encryption_key') as $key) {
            $this->{$key} = (isset($params[$key])) ? $params[$key] : $this->CI->config->item($key);
        }
        // Load the string helper so we can use the strip_slashes() function
        $this->CI->load->helper('string');

        // Do we need encryption? If so, load the encryption class
        if ($this->sess_encrypt_cookie == true) {
            $this->CI->load->library('encrypt');
        }

        // Are we using a database?  If so, load it
        if ($this->sess_use_database === true and $this->sess_table_name != '') {
            $this->CI->load->database();
        }

        // Set the "now" time.  Can either be GMT or server time, based on the
        // config prefs.  We use this to set the "last activity" time
        $this->now = $this->_get_time();

        // Set the session length. If the session expiration is
        // set to zero we'll set the expiration two years from now.
        if ($this->sess_expiration == 0) {
            $this->sess_expiration = (60 * 60 * 24 * 365 * 2);
        }

        if ($this->token_expiration == 0) {
            $this->token_expiration = (60 * 60 * 24 * 365 * 2);
        }

        // Set the cookie name
        if ($this->CI->router->is_api_class) {
            $this->sess_cookie_name = $this->api_cookie_prefix . $this->sess_cookie_name;
        } else {
            $this->sess_cookie_name = $this->cookie_prefix . $this->sess_cookie_name;
        }

        // Run the Session routine. If a session doesn't exist we'll
        // create a new one.  If it does, we'll update it.
        if (!$this->sess_read()) {
            $this->sess_create();
        } else {
            $this->sess_update();
        }

        // Delete 'old' flashdata (from last request)
        $this->_flashdata_sweep();

        // Mark all new flashdata as old (data will be deleted before next request)
        $this->_flashdata_mark();

        // Delete expired sessions if necessary
        $this->_sess_gc();

        log_message('debug', "Session routines successfully run");
    }

    // --------------------------------------------------------------------

    /**
     * Fetch the current session data if it exists
     *
     * @return void
     */
    public function sess_read()
    {
        if (!$this->CI->router->is_api_class) {
            // Fetch the cookie
            $session = $this->CI->input->cookie($this->sess_cookie_name);

            // No cookie?  Goodbye cruel world!...
            if ($session === false) {
                log_message('debug', 'A session cookie was not found.');

                return false;
            }

            // Decrypt the cookie data
            if ($this->sess_encrypt_cookie == true) {
                $session = $this->CI->encrypt->decode($session);
            } else {
                // encryption was not used, so we need to check the md5 hash
                $hash     = substr($session, strlen($session) - 32); // get last 32 chars
                $session = substr($session, 0, strlen($session) - 32);

                // Does the md5 hash match?  This is to prevent manipulation of session data in userspace
                if ($hash !==  md5($session . $this->encryption_key)) {
                    log_message('error', 'The session cookie data did not match what was expected. This could be a possible hacking attempt.');
                    $this->sess_destroy();

                    return false;
                }
            }

            // Unserialize the session array
            $session = $this->_unserialize($session);

            // Is the session data we unserialized an array with the correct format?
            if (!is_array($session) or !isset($session['session_id']) or !isset($session['ip_address']) or !isset($session['user_agent']) or !isset($session['last_activity'])) {
                $this->sess_destroy();

                return false;
            }

            // Is the session current?
            if (($session['last_activity'] + $this->sess_expiration) < $this->now) {
                $this->sess_destroy();

                return false;
            }

            // Does the IP Match?
            if ($this->sess_match_ip == true and $session['ip_address'] != $this->CI->input->ip_address()) {
                $this->sess_destroy();

                return false;
            }

            // Does the User Agent Match?
            if ($this->sess_match_useragent == true and trim($session['user_agent']) != trim(substr($this->CI->input->user_agent(), 0, 50))) {
                $this->sess_destroy();

                return false;
            }
        } else {
            $session['session_id'] = $this->get_session_id_by_token();
        }
        // Is there a corresponding session in the DB?
        if ($this->sess_use_database === true) {
            $this->CI->db->where('session_id', $session['session_id']);

            if ($this->sess_match_ip == true && !$this->CI->router->is_api_class) {
                $this->CI->db->where('ip_address', $session['ip_address']);
            }

            if ($this->sess_match_useragent == true && !$this->CI->router->is_api_class) {
                $this->CI->db->where('user_agent', $session['user_agent']);
            }

            $query = $this->CI->db->get($this->sess_table_name);

            // No result?  Kill it!
            if ($query->num_rows() == 0) {
                $this->sess_destroy();

                return false;
            }

            // Is there custom data?  If so, add it to the main session array
            $row = $query->row();
            if (isset($row->user_data) and $row->user_data != '') {
                $custom_data = $this->_unserialize($row->user_data);

                if (is_array($custom_data)) {
                    foreach ($custom_data as $key => $val) {
                        $session[$key] = $val;
                    }
                }
            }
        }

        // Session is valid!
        $this->userdata = $session;
        unset($session);

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Write the session data
     *
     * @return void
     */
    public function sess_write()
    {
        // Are we saving custom data to the DB?  If not, all we do is update the cookie
        if ($this->sess_use_database === false) {
            $this->_set_cookie();

            return;
        }

        // set the custom userdata, the session data we will set in a second
        $custom_userdata = $this->userdata;
        $cookie_userdata = array();

        // Before continuing, we need to determine if there is any custom data to deal with.
        // Let's determine this by removing the default indexes to see if there's anything left in the array
        // and set the session data while we're at it
        foreach (array('session_id', 'ip_address', 'user_agent', 'last_activity') as $val) {
            unset($custom_userdata[$val]);
            if (isset($this->userdata[$val])) {
                $cookie_userdata[$val] = $this->userdata[$val];
            }
        }

        // Did we find any custom data?  If not, we turn the empty array into a string
        // since there's no reason to serialize and store an empty array in the DB
        if (count($custom_userdata) === 0) {
            $custom_userdata = '';
        } else {
            // Serialize the custom data array so we can store it
            $custom_userdata = $this->_serialize($custom_userdata);
        }

        // Run the update query
        $this->CI->db->where('session_id', $this->userdata['session_id']);
        $this->CI->db->update($this->sess_table_name, array('last_activity' => $this->userdata['last_activity'], 'user_data' => $custom_userdata));

        // Write the cookie.  Notice that we manually pass the cookie data array to the
        // _set_cookie() function. Normally that function will store $this->userdata, but
        // in this case that array contains custom data, which we do not want in the cookie.
        $this->_set_cookie($cookie_userdata);
    }

    // --------------------------------------------------------------------

    /**
     * Create a new session
     *
     * @return void
     */
    public function sess_create()
    {
        // TODO: не создавать сессии для клиентов нетворка (привести к нормальному виду).
        $dir = dirname(dirname(dirname(__FILE__)));
        if (
            $_SERVER['SCRIPT_FILENAME'] == $dir . '/application/modules/network/client/slow-client.php' ||
            $_SERVER['SCRIPT_FILENAME'] == $dir . '/application/modules/network/client/fast-client.php'
        ) {
            return;
        }

        $sessid = '';
        while (strlen($sessid) < 32) {
            $sessid .= mt_rand(0, mt_getrandmax());
        }

        // To make the session ID even more secure we'll combine it with the user's IP
        $sessid .= $this->CI->input->ip_address();

        $this->userdata = array(
                            'session_id'       => md5(uniqid($sessid, true)),
                            'ip_address'       => ip2long($this->CI->input->ip_address()),
                            'user_agent'       => substr($this->CI->input->user_agent(), 0, 50),
                            'last_activity'    => $this->now,
                            );

        if ($this->CI->router->is_api_class) {
            $this->sess_create_token();
        }

        // Save the data to the DB if needed
        if ($this->sess_use_database === true) {
            $this->CI->db->query($this->CI->db->insert_string($this->sess_table_name, $this->userdata));
        }

        // Write the cookie
        $this->_set_cookie();
    }

    public function sess_create_token()
    {
        if ($this->CI->pg_module->is_module_active('get_token')) {
            $this->CI->db->select("COUNT(*) AS cnt, id, token");
            $this->CI->db->from($this->api_table_name);
            $this->CI->db->where('session_id', $this->userdata['session_id']);
            $result = $this->CI->db->get()->result();
            if (!empty($result) && intval($result[0]->cnt) > 0) {
                $this->userdata['user_data'] = serialize(array('token_id' => serialize($result[0]->id)));

                return $result[0]->token;
            } else {
                $attrs["session_id"] = $this->userdata['session_id'];
                $attrs["token"] = md5(uniqid($attrs["session_id"], true));
                $this->CI->db->insert($this->api_table_name, $attrs);
                $this->userdata['user_data'] = serialize(array('token_id' => $this->CI->db->insert_id()));

                return $attrs["token"];
            }
        }
    }

    public function get_session_id_by_token($token = '')
    {

        /*if(!$this->CI->pg_module->is_module_active('mobile')){
            $this->CI->set_api_content('code', '404');
            echo $this->CI->get_api_content();
            exit;
        }*/

        if ($this->CI->pg_module->is_module_active('get_token')) {
            $token = $token ? $token : trim(strip_tags($this->CI->input->get_post('token', true)));
            $this->CI->db->select("session_id");
            $this->CI->db->from($this->api_table_name);
            $this->CI->db->where('token', $token);
            $result = $this->CI->db->get()->result();
            if (!empty($result) && $result[0]->session_id) {
                return $result[0]->session_id;
            } elseif ($this->CI->router->method === 'index' && $this->CI->router->class === 'get_token') {
                return;
            } else {
                header("HTTP/1.1 401 Unauthorized");
                $this->CI->set_api_content('errors', 'Invalid token');
                $this->CI->set_api_content('code', '401');
                echo $this->CI->get_api_content();
                exit;
            }
        } else {
            if ($this->CI->router->method === 'index' && $this->CI->router->class === 'get_token') {
                header("HTTP/1.1 404 Not Found");
                $this->CI->set_api_content('errors', 'Module not installed');
                $this->CI->set_api_content('code', '404');
                echo $this->CI->get_api_content();
                exit;
            } else {
                header("HTTP/1.1 401 Unauthorized");
                $this->CI->set_api_content('errors', 'Invalid token');
                $this->CI->set_api_content('code', '401');
                echo $this->CI->get_api_content();
                exit;
            }
            //return null;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Update an existing session
     *
     * @return void
     */
    public function sess_update()
    {
        // No update with ajax request for prevent destroy session on a few async ajax requests at the same time
        if ($this->CI->input->is_ajax_request()) {
            return;
        }
        // We only update the session every five minutes by default
        if (!empty($this->userdata['last_activity']) && (($this->userdata['last_activity'] + $this->sess_time_to_update) >= $this->now)) {
            return;
        }

        // Save the old session id so we know which record to
        // update in the database if we need it
        if ($this->CI->router->is_api_class) {
            $old_sessid = $this->get_session_id_by_token();
        } else {
            $old_sessid = $this->userdata['session_id'];
        }
        $new_sessid = '';
        while (strlen($new_sessid) < 32) {
            $new_sessid .= mt_rand(0, mt_getrandmax());
        }

        // To make the session ID even more secure we'll combine it with the user's IP
        $new_sessid .= $this->CI->input->ip_address();

        // Turn it into a hash
        $new_sessid = md5(uniqid($new_sessid, true));

        // Update the session data in the session data array
        $this->userdata['session_id'] = $new_sessid;
        $this->userdata['last_activity'] = $this->now;

        // _set_cookie() will handle this for us if we aren't using database sessions
        // by pushing all userdata to the cookie.
        $cookie_data = null;

        // Update the session ID and last_activity field in the DB if needed
        if ($this->sess_use_database === true) {
            // set cookie explicitly to only have our session data
            $cookie_data = array();
            foreach (array('session_id', 'ip_address', 'user_agent', 'last_activity') as $val) {
                if (isset($this->userdata[$val])) {
                    $cookie_data[$val] = $this->userdata[$val];
                }
            }

            $this->CI->db->query($this->CI->db->update_string($this->sess_table_name, array('last_activity' => $this->now, 'session_id' => $new_sessid), array('session_id' => $old_sessid)));
            if ($this->CI->router->is_api_class && $this->CI->pg_module->is_module_active('get_token')) {
                $this->CI->db->query($this->CI->db->update_string($this->api_table_name, array('session_id' => $new_sessid), array('session_id' => $old_sessid)));
            }
        }

        // Write the cookie
        $this->_set_cookie($cookie_data);
    }

    // --------------------------------------------------------------------


    public function token_destroy($token = '')
    {
        if (!$this->CI->pg_module->is_module_active('get_token')) {
            return false;
        }
        if (empty($token)) {
            $token = filter_input(INPUT_POST, 'token');
        }
        if ($token) {
            $this->CI->db->where('token', $token)
                    ->delete($this->api_table_name);

            return true;
        }

        return false;
    }

    /**
     * Destroy the current session
     *
     * @return void
     */
    public function sess_destroy()
    {
        // Kill the session DB row
        if ($this->sess_use_database === true and isset($this->userdata['session_id'])) {
            $this->CI->db->where('session_id', $this->userdata['session_id']);
            $this->CI->db->delete($this->sess_table_name);
        }
        if ($this->CI->router->is_api_class) {
            $this->token_destroy();
        }
        // Kill the cookie
        setcookie(
                    $this->sess_cookie_name,
                    addslashes(serialize(array())),
                    ($this->now - 31500000),
                    $this->cookie_path,
                    $this->cookie_domain,
                    0
                );
    }

    // --------------------------------------------------------------------

    /**
     * Fetch a specific item from the session array
     *
     * @param	string
     *
     * @return string
     */
    public function userdata($item)
    {
        return (!isset($this->userdata[$item])) ? false : $this->userdata[$item];
    }

    // --------------------------------------------------------------------

    /**
     * Fetch all session data
     *
     * @return mixed
     */
    public function all_userdata()
    {
        return (!isset($this->userdata)) ? false : $this->userdata;
    }

    // --------------------------------------------------------------------

    /**
     * Add or change data in the "userdata" array
     *
     * @param	mixed
     * @param	string
     *
     * @return void
     */
    public function set_userdata($newdata = array(), $newval = '')
    {
        if (is_string($newdata)) {
            $newdata = array($newdata => $newval);
        }

        if (count($newdata) > 0) {
            foreach ($newdata as $key => $val) {
                $this->userdata[$key] = $val;
            }
        }

        $this->sess_write();
    }

    // --------------------------------------------------------------------

    /**
     * Delete a session variable from the "userdata" array
     *
     * @return void
     */
    public function unset_userdata($newdata = array())
    {
        if (is_string($newdata)) {
            $newdata = array($newdata => '');
        }

        if (count($newdata) > 0) {
            foreach ($newdata as $key => $val) {
                unset($this->userdata[$key]);
            }
        }

        $this->sess_write();
    }

    // ------------------------------------------------------------------------

    /**
     * Add or change flashdata, only available
     * until the next request
     *
     * @param	mixed
     * @param	string
     *
     * @return void
     */
    public function set_flashdata($newdata = array(), $newval = '')
    {
        if (is_string($newdata)) {
            $newdata = array($newdata => $newval);
        }

        if (count($newdata) > 0) {
            foreach ($newdata as $key => $val) {
                $flashdata_key = $this->flashdata_key . ':new:' . $key;
                $this->set_userdata($flashdata_key, $val);
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Keeps existing flashdata available to next request.
     *
     * @param	string
     *
     * @return void
     */
    public function keep_flashdata($key)
    {
        // 'old' flashdata gets removed.  Here we mark all
        // flashdata as 'new' to preserve it from _flashdata_sweep()
        // Note the function will return FALSE if the $key
        // provided cannot be found
        $old_flashdata_key = $this->flashdata_key . ':old:' . $key;
        $value = $this->userdata($old_flashdata_key);

        $new_flashdata_key = $this->flashdata_key . ':new:' . $key;
        $this->set_userdata($new_flashdata_key, $value);
    }

    // ------------------------------------------------------------------------

    /**
     * Fetch a specific flashdata item from the session array
     *
     * @param	string
     *
     * @return string
     */
    public function flashdata($key)
    {
        $flashdata_key = $this->flashdata_key . ':old:' . $key;

        return $this->userdata($flashdata_key);
    }

    // ------------------------------------------------------------------------

    /**
     * Identifies flashdata as 'old' for removal
     * when _flashdata_sweep() runs.
     *
     * @return void
     */
    public function _flashdata_mark()
    {
        $userdata = $this->all_userdata();
        foreach ($userdata as $name => $value) {
            $parts = explode(':new:', $name);
            if (is_array($parts) && count($parts) === 2) {
                $new_name = $this->flashdata_key . ':old:' . $parts[1];
                $this->set_userdata($new_name, $value);
                $this->unset_userdata($name);
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Removes all flashdata marked as 'old'
     *
     * @return void
     */
    public function _flashdata_sweep()
    {
        $userdata = $this->all_userdata();
        foreach ($userdata as $key => $value) {
            if (strpos($key, ':old:')) {
                $this->unset_userdata($key);
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Get the "now" time
     *
     * @return string
     */
    public function _get_time()
    {
        if (strtolower($this->time_reference) == 'gmt') {
            $now = time();
            $time = mktime(gmdate("H", $now), gmdate("i", $now), gmdate("s", $now), gmdate("m", $now), gmdate("d", $now), gmdate("Y", $now));
        } else {
            $time = time();
        }

        return $time;
    }

    // --------------------------------------------------------------------

    /**
     * Write the session cookie
     *
     * @return void
     */
    public function _set_cookie($cookie_data = null)
    {
        if (is_null($cookie_data)) {
            $cookie_data = $this->userdata;
        }

        // Serialize the userdata for the cookie
        $cookie_data = $this->_serialize($cookie_data);

        if ($this->sess_encrypt_cookie == true) {
            $cookie_data = $this->CI->encrypt->encode($cookie_data);
        } else {
            // if encryption is not used, we provide an md5 hash to prevent userside tampering
            $cookie_data = $cookie_data . md5($cookie_data . $this->encryption_key);
        }

        // Set the cookie
        setcookie(
                    $this->sess_cookie_name,
                    $cookie_data,
                    ($this->CI->router->is_api_class ? $this->token_expiration : $this->sess_expiration) + time(),
                    $this->cookie_path,
                    $this->cookie_domain,
                    0
                );
    }

    // --------------------------------------------------------------------

    /**
     * Serialize an array
     *
     * This function first converts any slashes found in the array to a temporary
     * marker, so when it gets unserialized the slashes will be preserved
     *
     * @param	array
     *
     * @return string
     */
    public function _serialize($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                $data[$key] = str_replace('\\', '{{slash}}', $val);
            }
        } else {
            $data = str_replace('\\', '{{slash}}', $data);
        }

        return serialize($data);
    }

    // --------------------------------------------------------------------

    /**
     * Unserialize
     *
     * This function unserializes a data string, then converts any
     * temporary slash markers back to actual slashes
     *
     * @param	array
     *
     * @return string
     */
    public function _unserialize($data)
    {
        $data = @unserialize(strip_slashes($data));

        if (is_array($data)) {
            foreach ($data as $key => $val) {
                $data[$key] = str_replace('{{slash}}', '\\', $val);
            }

            return $data;
        }

        return str_replace('{{slash}}', '\\', $data);
    }

    // --------------------------------------------------------------------

    /**
     * Garbage collection
     *
     * This deletes expired session rows from database
     * if the probability percentage is met
     *
     * @return void
     */
    public function _sess_gc()
    {
        if (true === $this->sess_use_database && (rand() % 100 < $this->gc_probability)) {
            // TODO: Delete tokens
            $expire = $this->now - $this->sess_expiration;
            $this->CI->db->where("last_activity < $expire");
            $this->CI->db->delete($this->sess_table_name);

            if ($this->CI->pg_module->is_module_active('get_token')) {
                $where = "NOT EXISTS(SELECT 1 FROM " . $this->sess_table_name . " "
                       . "WHERE session_id = " . $this->api_table_name . ".session_id)";
                $this->CI->db->where($where, null, false);
                $this->CI->db->delete($this->api_table_name);
            }

            log_message('debug', 'Session garbage collection performed.');
        }
    }
}
// END Session Class

/* End of file Session.php */
/* Location: ./system/libraries/Session.php */
