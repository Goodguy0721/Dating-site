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
 * Validation Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 *
 * @category	Validation
 *
 * @author		ExpressionEngine Dev Team
 *
 * @link		http://codeigniter.com/user_guide/libraries/validation.html
 */
class CI_Validation
{
    public $CI;
    public $error_string        = '';
    public $_error_array        = array();
    public $_rules                = array();
    public $_fields            = array();
    public $_error_messages    = array();
    public $_current_field    = '';
    public $_safe_form_data    = false;
    public $_error_prefix        = '<p>';
    public $_error_suffix        = '</p>';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->CI = &get_instance();

        if (function_exists('mb_internal_encoding')) {
            mb_internal_encoding($this->CI->config->item('charset'));
        }

        log_message('debug', "Validation Class Initialized");
    }

    // --------------------------------------------------------------------

    /**
     * Set Fields
     *
     * This function takes an array of field names as input
     * and generates class variables with the same name, which will
     * either be blank or contain the $_POST value corresponding to it
     *
     * @param	string
     * @param	string
     *
     * @return void
     */
    public function set_fields($data = '', $field = '')
    {
        if ($data == '') {
            if (count($this->_fields) == 0) {
                return false;
            }
        } else {
            if (!is_array($data)) {
                $data = array($data => $field);
            }

            if (count($data) > 0) {
                $this->_fields = $data;
            }
        }

        foreach ($this->_fields as $key => $val) {
            $this->{$key} = (!isset($_POST[$key])) ? '' : $this->prep_for_form($_POST[$key]);

            $error = $key . '_error';
            if (!isset($this->{$error})) {
                $this->{$error} = '';
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Set Rules
     *
     * This function takes an array of field names and validation
     * rules as input ad simply stores is for use later.
     *
     * @param	mixed
     * @param	string
     *
     * @return void
     */
    public function set_rules($data, $rules = '')
    {
        if (!is_array($data)) {
            if ($rules == '') {
                return;
            }

            $data = array($data => $rules);
        }

        foreach ($data as $key => $val) {
            $this->_rules[$key] = $val;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Set Error Message
     *
     * Lets users set their own error messages on the fly.  Note:  The key
     * name has to match the  function name that it corresponds to.
     *
     * @param	string
     * @param	string
     *
     * @return string
     */
    public function set_message($lang, $val = '')
    {
        if (!is_array($lang)) {
            $lang = array($lang => $val);
        }

        $this->_error_messages = array_merge($this->_error_messages, $lang);
    }

    // --------------------------------------------------------------------

    /**
     * Set The Error Delimiter
     *
     * Permits a prefix/suffix to be added to each error message
     *
     * @param	string
     * @param	string
     *
     * @return void
     */
    public function set_error_delimiters($prefix = '<p>', $suffix = '</p>')
    {
        $this->_error_prefix = $prefix;
        $this->_error_suffix = $suffix;
    }

    // --------------------------------------------------------------------

    /**
     * Run the Validator
     *
     * This function does all the work.
     *
     * @return bool
     */
    public function run()
    {
        // Do we even have any data to process?  Mm?
        if (count($_POST) == 0 or count($this->_rules) == 0) {
            return false;
        }

        // Load the language file containing error messages
        $this->CI->lang->load('validation');

        // Cycle through the rules and test for errors
        foreach ($this->_rules as $field => $rules) {
            //Explode out the rules!
            $ex = explode('|', $rules);

            // Is the field required?  If not, if the field is blank  we'll move on to the next test
            if (!in_array('required', $ex, true)) {
                if (!isset($_POST[$field]) or $_POST[$field] == '') {
                    continue;
                }
            }

            /*
             * Are we dealing with an "isset" rule?
             *
             * Before going further, we'll see if one of the rules
             * is to check whether the item is set (typically this
             * applies only to checkboxes).  If so, we'll
             * test for it here since there's not reason to go
             * further
             */
            if (!isset($_POST[$field])) {
                if (in_array('isset', $ex, true) or in_array('required', $ex)) {
                    if (!isset($this->_error_messages['isset'])) {
                        if (false === ($line = $this->CI->lang->line('isset'))) {
                            $line = 'The field was not set';
                        }
                    } else {
                        $line = $this->_error_messages['isset'];
                    }

                    // Build the error message
                    $mfield = (!isset($this->_fields[$field])) ? $field : $this->_fields[$field];
                    $message = sprintf($line, $mfield);

                    // Set the error variable.  Example: $this->username_error
                    $error = $field . '_error';
                    $this->{$error} = $this->_error_prefix . $message . $this->_error_suffix;
                    $this->_error_array[] = $message;
                }

                continue;
            }

            /*
             * Set the current field
             *
             * The various prepping functions need to know the
             * current field name so they can do this:
             *
             * $_POST[$this->_current_field] == 'bla bla';
             */
            $this->_current_field = $field;

            // Cycle through the rules!
            foreach ($ex as $rule) {
                // Is the rule a callback?
                $callback = false;
                if (substr($rule, 0, 9) == 'callback_') {
                    $rule = substr($rule, 9);
                    $callback = true;
                }

                // Strip the parameter (if exists) from the rule
                // Rules can contain a parameter: max_length[5]
                $param = false;
                if (preg_match("/(.*?)\[(.*?)\]/", $rule, $match)) {
                    $rule    = $match[1];
                    $param    = $match[2];
                }

                // Call the function that corresponds to the rule
                if ($callback === true) {
                    if (!method_exists($this->CI, $rule)) {
                        continue;
                    }

                    $result = $this->CI->{$rule}($_POST[$field], $param);

                    // If the field isn't required and we just processed a callback we'll move on...
                    if (!in_array('required', $ex, true) and $result !== false) {
                        continue 2;
                    }
                } else {
                    if (!method_exists($this, $rule)) {
                        /*
                         * Run the native PHP function if called for
                         *
                         * If our own wrapper function doesn't exist we see
                         * if a native PHP function does. Users can use
                         * any native PHP function call that has one param.
                         */
                        if (function_exists($rule)) {
                            $_POST[$field] = $rule($_POST[$field]);
                            $this->{$field} = $_POST[$field];
                        }

                        continue;
                    }

                    $result = $this->{$rule}($_POST[$field], $param);
                }

                // Did the rule test negatively?  If so, grab the error.
                if ($result === false) {
                    if (!isset($this->_error_messages[$rule])) {
                        if (false === ($line = $this->CI->lang->line($rule))) {
                            $line = 'Unable to access an error message corresponding to your field name.';
                        }
                    } else {
                        $line = $this->_error_messages[$rule];
                    }

                    // Build the error message
                    $mfield = (!isset($this->_fields[$field])) ? $field : $this->_fields[$field];
                    $mparam = (!isset($this->_fields[$param])) ? $param : $this->_fields[$param];
                    $message = sprintf($line, $mfield, $mparam);

                    // Set the error variable.  Example: $this->username_error
                    $error = $field . '_error';
                    $this->{$error} = $this->_error_prefix . $message . $this->_error_suffix;

                    // Add the error to the error array
                    $this->_error_array[] = $message;
                    continue 2;
                }
            }
        }

        $total_errors = count($this->_error_array);

        /*
         * Recompile the class variables
         *
         * If any prepping functions were called the $_POST data
         * might now be different then the corresponding class
         * variables so we'll set them anew.
         */
        if ($total_errors > 0) {
            $this->_safe_form_data = true;
        }

        $this->set_fields();

        // Did we end up with any errors?
        if ($total_errors == 0) {
            return true;
        }

        // Generate the error string
        foreach ($this->_error_array as $val) {
            $this->error_string .= $this->_error_prefix . $val . $this->_error_suffix . "\n";
        }

        return false;
    }

    // --------------------------------------------------------------------

    /**
     * Required
     *
     * @param	string
     *
     * @return bool
     */
    public function required($str)
    {
        if (!is_array($str)) {
            return (trim($str) == '') ? false : true;
        } else {
            return (!empty($str));
        }
    }

    // --------------------------------------------------------------------

    /**
     * Match one field to another
     *
     * @param	string
     * @param	field
     *
     * @return bool
     */
    public function matches($str, $field)
    {
        if (!isset($_POST[$field])) {
            return false;
        }

        return ($str !== $_POST[$field]) ? false : true;
    }

    // --------------------------------------------------------------------

    /**
     * Minimum Length
     *
     * @param	string
     * @param	value
     *
     * @return bool
     */
    public function min_length($str, $val)
    {
        if (preg_match("/[^0-9]/", $val)) {
            return false;
        }

        if (function_exists('mb_strlen')) {
            return (mb_strlen($str) < $val) ? false : true;
        }

        return (strlen($str) < $val) ? false : true;
    }

    // --------------------------------------------------------------------

    /**
     * Max Length
     *
     * @param	string
     * @param	value
     *
     * @return bool
     */
    public function max_length($str, $val)
    {
        if (preg_match("/[^0-9]/", $val)) {
            return false;
        }

        if (function_exists('mb_strlen')) {
            return (mb_strlen($str) > $val) ? false : true;
        }

        return (strlen($str) > $val) ? false : true;
    }

    // --------------------------------------------------------------------

    /**
     * Exact Length
     *
     * @param	string
     * @param	value
     *
     * @return bool
     */
    public function exact_length($str, $val)
    {
        if (preg_match("/[^0-9]/", $val)) {
            return false;
        }

        if (function_exists('mb_strlen')) {
            return (mb_strlen($str) != $val) ? false : true;
        }

        return (strlen($str) != $val) ? false : true;
    }

    // --------------------------------------------------------------------

    /**
     * Valid Email
     *
     * @param	string
     *
     * @return bool
     */
    public function valid_email($str)
    {
        return (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? false : true;
    }

    // --------------------------------------------------------------------

    /**
     * Valid Emails
     *
     * @param	string
     *
     * @return bool
     */
    public function valid_emails($str)
    {
        if (strpos($str, ',') === false) {
            return $this->valid_email(trim($str));
        }

        foreach (explode(',', $str) as $email) {
            if (trim($email) != '' && $this->valid_email(trim($email)) === false) {
                return false;
            }
        }

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Validate IP Address
     *
     * @param	string
     *
     * @return string
     */
    public function valid_ip($ip)
    {
        return $this->CI->input->valid_ip($ip);
    }

    // --------------------------------------------------------------------

    /**
     * Alpha
     *
     * @param	string
     *
     * @return bool
     */
    public function alpha($str)
    {
        return (!preg_match("/^([a-z])+$/i", $str)) ? false : true;
    }

    // --------------------------------------------------------------------

    /**
     * Alpha-numeric
     *
     * @param	string
     *
     * @return bool
     */
    public function alpha_numeric($str)
    {
        return (!preg_match("/^([a-z0-9])+$/i", $str)) ? false : true;
    }

    // --------------------------------------------------------------------

    /**
     * Alpha-numeric with underscores and dashes
     *
     * @param	string
     *
     * @return bool
     */
    public function alpha_dash($str)
    {
        return (!preg_match("/^([-a-z0-9_-])+$/i", $str)) ? false : true;
    }

    // --------------------------------------------------------------------

    /**
     * Numeric
     *
     * @param	string
     *
     * @return bool
     */
    public function numeric($str)
    {
        return (bool) preg_match('/^[\-+]?[0-9]*\.?[0-9]+$/', $str);
    }

    // --------------------------------------------------------------------

    /**
     * Is Numeric
     *
     * @param	string
     *
     * @return bool
     */
    public function is_numeric($str)
    {
        return (!is_numeric($str)) ? false : true;
    }

    // --------------------------------------------------------------------

    /**
     * Integer
     *
     * @param	string
     *
     * @return bool
     */
    public function integer($str)
    {
        return (bool) preg_match('/^[\-+]?[0-9]+$/', $str);
    }

    // --------------------------------------------------------------------

    /**
     * Is a Natural number  (0,1,2,3, etc.)
     *
     * @param	string
     *
     * @return bool
     */
    public function is_natural($str)
    {
        return (bool) preg_match('/^[0-9]+$/', $str);
    }

    // --------------------------------------------------------------------

    /**
     * Is a Natural number, but not a zero  (1,2,3, etc.)
     *
     * @param	string
     *
     * @return bool
     */
    public function is_natural_no_zero($str)
    {
        if (!preg_match('/^[0-9]+$/', $str)) {
            return false;
        }

        if ($str == 0) {
            return false;
        }

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Valid Base64
     *
     * Tests a string for characters outside of the Base64 alphabet
     * as defined by RFC 2045 http://www.faqs.org/rfcs/rfc2045
     *
     * @param	string
     *
     * @return bool
     */
    public function valid_base64($str)
    {
        return (bool) !preg_match('/[^a-zA-Z0-9\/\+=]/', $str);
    }

    // --------------------------------------------------------------------

    /**
     * Set Select
     *
     * Enables pull-down lists to be set to the value the user
     * selected in the event of an error
     *
     * @param	string
     * @param	string
     *
     * @return string
     */
    public function set_select($field = '', $value = '')
    {
        if ($field == '' or $value == '' or  !isset($_POST[$field])) {
            return '';
        }

        if ($_POST[$field] == $value) {
            return ' selected="selected"';
        }
    }

    // --------------------------------------------------------------------

    /**
     * Set Radio
     *
     * Enables radio buttons to be set to the value the user
     * selected in the event of an error
     *
     * @param	string
     * @param	string
     *
     * @return string
     */
    public function set_radio($field = '', $value = '')
    {
        if ($field == '' or $value == '' or  !isset($_POST[$field])) {
            return '';
        }

        if ($_POST[$field] == $value) {
            return ' checked="checked"';
        }
    }

    // --------------------------------------------------------------------

    /**
     * Set Checkbox
     *
     * Enables checkboxes to be set to the value the user
     * selected in the event of an error
     *
     * @param	string
     * @param	string
     *
     * @return string
     */
    public function set_checkbox($field = '', $value = '')
    {
        if ($field == '' or $value == '' or  !isset($_POST[$field])) {
            return '';
        }

        if ($_POST[$field] == $value) {
            return ' checked="checked"';
        }
    }

    // --------------------------------------------------------------------

    /**
     * Prep data for form
     *
     * This function allows HTML to be safely shown in a form.
     * Special characters are converted.
     *
     * @param	string
     *
     * @return string
     */
    public function prep_for_form($data = '')
    {
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                $data[$key] = $this->prep_for_form($val);
            }

            return $data;
        }

        if ($this->_safe_form_data == false or $data == '') {
            return $data;
        }

        return str_replace(array("'", '"', '<', '>'), array("&#39;", "&quot;", '&lt;', '&gt;'), stripslashes($data));
    }

    // --------------------------------------------------------------------

    /**
     * Prep URL
     *
     * @param	string
     *
     * @return string
     */
    public function prep_url($str = '')
    {
        if ($str == 'http://' or $str == '') {
            $_POST[$this->_current_field] = '';

            return;
        }

        if (substr($str, 0, 7) != 'http://' && substr($str, 0, 8) != 'https://') {
            $str = 'http://' . $str;
        }

        $_POST[$this->_current_field] = $str;
    }

    // --------------------------------------------------------------------

    /**
     * Strip Image Tags
     *
     * @param	string
     *
     * @return string
     */
    public function strip_image_tags($str)
    {
        $_POST[$this->_current_field] = $this->CI->input->strip_image_tags($str);
    }

    // --------------------------------------------------------------------

    /**
     * XSS Clean
     *
     * @param	string
     *
     * @return string
     */
    public function xss_clean($str)
    {
        $_POST[$this->_current_field] = $this->CI->input->xss_clean($str);
    }

    // --------------------------------------------------------------------

    /**
     * Convert PHP tags to entities
     *
     * @param	string
     *
     * @return string
     */
    public function encode_php_tags($str)
    {
        $_POST[$this->_current_field] = str_replace(array('<?php', '<?PHP', '<?', '?>'),  array('&lt;?php', '&lt;?PHP', '&lt;?', '?&gt;'), $str);
    }
}
// END Validation Class

/* End of file Validation.php */
/* Location: ./system/libraries/Validation.php */
