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
 * CodeIgniter Form Helpers
 *
 * @package		CodeIgniter
 * @subpackage	Helpers
 *
 * @category	Helpers
 *
 * @author		ExpressionEngine Dev Team
 *
 * @link		http://codeigniter.com/user_guide/helpers/form_helper.html
 */

// ------------------------------------------------------------------------

/**
 * Form Declaration
 *
 * Creates the opening portion of the form.
 *
 * @param	string	the URI segments of the form destination
 * @param	array	a key/value pair of attributes
 * @param	array	a key/value pair hidden data
 *
 * @return	string
 */
if (!function_exists('form_open')) {
    function form_open($action = '', $attributes = '', $hidden = array())
    {
        $CI = &get_instance();

        if ($attributes == '') {
            $attributes = 'method="post"';
        }

        $action = (strpos($action, '://') === false) ? $CI->config->site_url($action) : $action;

        $form = '<form action="' . $action . '"';

        $form .= _attributes_to_string($attributes, true);

        $form .= '>';

        if (is_array($hidden) and count($hidden) > 0) {
            $form .= form_hidden($hidden);
        }

        return $form;
    }
}

// ------------------------------------------------------------------------

/**
 * Form Declaration - Multipart type
 *
 * Creates the opening portion of the form, but with "multipart/form-data".
 *
 * @param	string	the URI segments of the form destination
 * @param	array	a key/value pair of attributes
 * @param	array	a key/value pair hidden data
 *
 * @return	string
 */
if (!function_exists('form_open_multipart')) {
    function form_open_multipart($action, $attributes = array(), $hidden = array())
    {
        $attributes['enctype'] = 'multipart/form-data';

        return form_open($action, $attributes, $hidden);
    }
}

// ------------------------------------------------------------------------

/**
 * Hidden Input Field
 *
 * Generates hidden fields.  You can pass a simple key/value string or an associative
 * array with multiple values.
 *
 * @param	mixed
 * @param	string
 *
 * @return	string
 */
if (!function_exists('form_hidden')) {
    function form_hidden($name, $value = '')
    {
        if (!is_array($name)) {
            return '<input type="hidden" name="' . $name . '" value="' . form_prep($value) . '" />';
        }

        $form = '';

        foreach ($name as $name => $value) {
            $form .= "\n";
            $form .= '<input type="hidden" name="' . $name . '" value="' . form_prep($value) . '" />';
        }

        return $form;
    }
}

// ------------------------------------------------------------------------

/**
 * Text Input Field
 *
 * @param	mixed
 * @param	string
 * @param	string
 *
 * @return	string
 */
if (!function_exists('form_input')) {
    function form_input($data = '', $value = '', $extra = '')
    {
        $defaults = array('type' => 'text', 'name' => ((!is_array($data)) ? $data : ''), 'value' => $value);

        return "<input " . _parse_form_attributes($data, $defaults) . $extra . " />";
    }
}

// ------------------------------------------------------------------------

/**
 * Password Field
 *
 * Identical to the input function but adds the "password" type
 *
 * @param	mixed
 * @param	string
 * @param	string
 *
 * @return	string
 */
if (!function_exists('form_password')) {
    function form_password($data = '', $value = '', $extra = '')
    {
        if (!is_array($data)) {
            $data = array('name' => $data);
        }

        $data['type'] = 'password';

        return form_input($data, $value, $extra);
    }
}

// ------------------------------------------------------------------------

/**
 * Upload Field
 *
 * Identical to the input function but adds the "file" type
 *
 * @param	mixed
 * @param	string
 * @param	string
 *
 * @return	string
 */
if (!function_exists('form_upload')) {
    function form_upload($data = '', $value = '', $extra = '')
    {
        if (!is_array($data)) {
            $data = array('name' => $data);
        }

        $data['type'] = 'file';

        return form_input($data, $value, $extra);
    }
}

// ------------------------------------------------------------------------

/**
 * Textarea field
 *
 * @param	mixed
 * @param	string
 * @param	string
 *
 * @return	string
 */
if (!function_exists('form_textarea')) {
    function form_textarea($data = '', $value = '', $extra = '')
    {
        $defaults = array('name' => ((!is_array($data)) ? $data : ''), 'cols' => '90', 'rows' => '12');

        if (!is_array($data) or !isset($data['value'])) {
            $val = $value;
        } else {
            $val = $data['value'];
            unset($data['value']); // textareas don't use the value attribute
        }

        return "<textarea " . _parse_form_attributes($data, $defaults) . $extra . ">" . $val . "</textarea>";
    }
}

// ------------------------------------------------------------------------

/**
 * Drop-down Menu
 *
 * @param	string
 * @param	array
 * @param	string
 * @param	string
 *
 * @return	string
 */
if (!function_exists('form_dropdown')) {
    function form_dropdown($name = '', $options = array(), $selected = array(), $extra = '')
    {
        if (!is_array($selected)) {
            $selected = array($selected);
        }

        // If no selected state was submitted we will attempt to set it automatically
        if (count($selected) === 0) {
            // If the form name appears in the $_POST array we have a winner!
            if (isset($_POST[$name])) {
                $selected = array($_POST[$name]);
            }
        }

        if ($extra != '') {
            $extra = ' ' . $extra;
        }

        $multiple = (count($selected) > 1 && strpos($extra, 'multiple') === false) ? ' multiple="multiple"' : '';

        $form = '<select name="' . $name . '"' . $extra . $multiple . ">\n";

        foreach ($options as $key => $val) {
            $key = (string) $key;
            $val = (string) $val;

            $sel = (in_array($key, $selected)) ? ' selected="selected"' : '';

            $form .= '<option value="' . $key . '"' . $sel . '>' . $val . "</option>\n";
        }

        $form .= '</select>';

        return $form;
    }
}

// ------------------------------------------------------------------------

/**
 * Checkbox Field
 *
 * @param	mixed
 * @param	string
 * @param	bool
 * @param	string
 *
 * @return	string
 */
if (!function_exists('form_checkbox')) {
    function form_checkbox($data = '', $value = '', $checked = false, $extra = '')
    {
        $defaults = array('type' => 'checkbox', 'name' => ((!is_array($data)) ? $data : ''), 'value' => $value);

        if (is_array($data) and array_key_exists('checked', $data)) {
            $checked = $data['checked'];

            if ($checked == false) {
                unset($data['checked']);
            } else {
                $data['checked'] = 'checked';
            }
        }

        if ($checked == true) {
            $defaults['checked'] = 'checked';
        } else {
            unset($defaults['checked']);
        }

        return "<input " . _parse_form_attributes($data, $defaults) . $extra . " />";
    }
}

// ------------------------------------------------------------------------

/**
 * Radio Button
 *
 * @param	mixed
 * @param	string
 * @param	bool
 * @param	string
 *
 * @return	string
 */
if (!function_exists('form_radio')) {
    function form_radio($data = '', $value = '', $checked = false, $extra = '')
    {
        if (!is_array($data)) {
            $data = array('name' => $data);
        }

        $data['type'] = 'radio';

        return form_checkbox($data, $value, $checked, $extra);
    }
}

// ------------------------------------------------------------------------

/**
 * Submit Button
 *
 * @param	mixed
 * @param	string
 * @param	string
 *
 * @return	string
 */
if (!function_exists('form_submit')) {
    function form_submit($data = '', $value = '', $extra = '')
    {
        $defaults = array('type' => 'submit', 'name' => ((!is_array($data)) ? $data : ''), 'value' => $value);

        return "<input " . _parse_form_attributes($data, $defaults) . $extra . " />";
    }
}

// ------------------------------------------------------------------------

/**
 * Reset Button
 *
 * @param	mixed
 * @param	string
 * @param	string
 *
 * @return	string
 */
if (!function_exists('form_reset')) {
    function form_reset($data = '', $value = '', $extra = '')
    {
        $defaults = array('type' => 'reset', 'name' => ((!is_array($data)) ? $data : ''), 'value' => $value);

        return "<input " . _parse_form_attributes($data, $defaults) . $extra . " />";
    }
}

// ------------------------------------------------------------------------

/**
 * Form Button
 *
 * @param	mixed
 * @param	string
 * @param	string
 *
 * @return	string
 */
if (!function_exists('form_button')) {
    function form_button($data = '', $content = '', $extra = '')
    {
        $defaults = array('name' => ((!is_array($data)) ? $data : ''), 'type' => 'submit');

        if (is_array($data) and isset($data['content'])) {
            $content = $data['content'];
            unset($data['content']); // content is not an attribute
        }

        return "<button " . _parse_form_attributes($data, $defaults) . $extra . ">" . $content . "</button>";
    }
}

// ------------------------------------------------------------------------

/**
 * Form Label Tag
 *
 * @param	string	The text to appear onscreen
 * @param	string	The id the label applies to
 * @param	string	Additional attributes
 *
 * @return	string
 */
if (!function_exists('form_label')) {
    function form_label($label_text = '', $id = '', $attributes = array())
    {
        $label = '<label';

        if ($id != '') {
            $label .= " for=\"$id\"";
        }

        if (is_array($attributes) and count($attributes) > 0) {
            foreach ($attributes as $key => $val) {
                $label .= ' ' . $key . '="' . $val . '"';
            }
        }

        $label .= ">$label_text</label>";

        return $label;
    }
}

// ------------------------------------------------------------------------
/**
 * Fieldset Tag
 *
 * Used to produce <fieldset><legend>text</legend>.  To close fieldset
 * use form_fieldset_close()
 *
 * @param	string	The legend text
 * @param	string	Additional attributes
 *
 * @return	string
 */
if (!function_exists('form_fieldset')) {
    function form_fieldset($legend_text = '', $attributes = array())
    {
        $fieldset = "<fieldset";

        $fieldset .= _attributes_to_string($attributes, false);

        $fieldset .= ">\n";

        if ($legend_text != '') {
            $fieldset .= "<legend>$legend_text</legend>\n";
        }

        return $fieldset;
    }
}

// ------------------------------------------------------------------------

/**
 * Fieldset Close Tag
 *
 * @param	string
 *
 * @return	string
 */
if (!function_exists('form_fieldset_close')) {
    function form_fieldset_close($extra = '')
    {
        return "</fieldset>" . $extra;
    }
}

// ------------------------------------------------------------------------

/**
 * Form Close Tag
 *
 * @param	string
 *
 * @return	string
 */
if (!function_exists('form_close')) {
    function form_close($extra = '')
    {
        return "</form>" . $extra;
    }
}

// ------------------------------------------------------------------------

/**
 * Form Prep
 *
 * Formats text so that it can be safely placed in a form field in the event it has HTML tags.
 *
 * @param	string
 *
 * @return	string
 */
if (!function_exists('form_prep')) {
    function form_prep($str = '')
    {
        // if the field name is an array we do this recursively
        if (is_array($str)) {
            foreach ($str as $key => $val) {
                $str[$key] = form_prep($val);
            }

            return $str;
        }

        if ($str === '') {
            return '';
        }

        $temp = '__TEMP_AMPERSANDS__';

        // Replace entities to temporary markers so that
        // htmlspecialchars won't mess them up
        $str = preg_replace("/&#(\d+);/", "$temp\\1;", $str);
        $str = preg_replace("/&(\w+);/",  "$temp\\1;", $str);

        $str = htmlspecialchars($str);

        // In case htmlspecialchars misses these.
        $str = str_replace(array("'", '"'), array("&#39;", "&quot;"), $str);

        // Decode the temp markers back to entities
        $str = preg_replace("/$temp(\d+);/", "&#\\1;", $str);
        $str = preg_replace("/$temp(\w+);/", "&\\1;", $str);

        return $str;
    }
}

// ------------------------------------------------------------------------

/**
 * Form Value
 *
 * Grabs a value from the POST array for the specified field so you can
 * re-populate an input field or textarea.  If Form Validation
 * is active it retrieves the info from the validation class
 *
 * @param	string
 *
 * @return	mixed
 */
if (!function_exists('set_value')) {
    function set_value($field = '', $default = '')
    {
        if (false === ($OBJ = &_get_validation_object())) {
            if (!isset($_POST[$field])) {
                return $default;
            }

            return form_prep($_POST[$field]);
        }

        return form_prep($OBJ->set_value($field, $default));
    }
}

// ------------------------------------------------------------------------

/**
 * Set Select
 *
 * Let's you set the selected value of a <select> menu via data in the POST array.
 * If Form Validation is active it retrieves the info from the validation class
 *
 * @param	string
 * @param	string
 * @param	bool
 *
 * @return	string
 */
if (!function_exists('set_select')) {
    function set_select($field = '', $value = '', $default = false)
    {
        $OBJ = &_get_validation_object();

        if ($OBJ === false) {
            if (!isset($_POST[$field])) {
                if (count($_POST) === 0) {
                    return ' selected="selected"';
                }

                return '';
            }

            $field = $_POST[$field];

            if (is_array($field)) {
                if (!in_array($value, $field)) {
                    return '';
                }
            } else {
                if (($field == '' or $value == '') or ($field != $value)) {
                    return '';
                }
            }

            return ' selected="selected"';
        }

        return $OBJ->set_select($field, $value, $default);
    }
}

// ------------------------------------------------------------------------

/**
 * Set Checkbox
 *
 * Let's you set the selected value of a checkbox via the value in the POST array.
 * If Form Validation is active it retrieves the info from the validation class
 *
 * @param	string
 * @param	string
 * @param	bool
 *
 * @return	string
 */
if (!function_exists('set_checkbox')) {
    function set_checkbox($field = '', $value = '', $default = false)
    {
        $OBJ = &_get_validation_object();

        if ($OBJ === false) {
            if (!isset($_POST[$field])) {
                if (count($_POST) === 0) {
                    return ' checked="checked"';
                }

                return '';
            }

            $field = $_POST[$field];

            if (is_array($field)) {
                if (!in_array($value, $field)) {
                    return '';
                }
            } else {
                if (($field == '' or $value == '') or ($field != $value)) {
                    return '';
                }
            }

            return ' checked="checked"';
        }

        return $OBJ->set_checkbox($field, $value, $default);
    }
}

// ------------------------------------------------------------------------

/**
 * Set Radio
 *
 * Let's you set the selected value of a radio field via info in the POST array.
 * If Form Validation is active it retrieves the info from the validation class
 *
 * @param	string
 * @param	string
 * @param	bool
 *
 * @return	string
 */
if (!function_exists('set_radio')) {
    function set_radio($field = '', $value = '', $default = false)
    {
        $OBJ = &_get_validation_object();

        if ($OBJ === false) {
            if (!isset($_POST[$field])) {
                if (count($_POST) === 0) {
                    return ' checked="checked"';
                }

                return '';
            }

            $field = $_POST[$field];

            if (is_array($field)) {
                if (!in_array($value, $field)) {
                    return '';
                }
            } else {
                if (($field == '' or $value == '') or ($field != $value)) {
                    return '';
                }
            }

            return ' checked="checked"';
        }

        return $OBJ->set_radio($field, $value, $default);
    }
}

// ------------------------------------------------------------------------

/**
 * Form Error
 *
 * Returns the error for a specific form field.  This is a helper for the
 * form validation class.
 *
 * @param	string
 * @param	string
 * @param	string
 *
 * @return	string
 */
if (!function_exists('form_error')) {
    function form_error($field = '', $prefix = '', $suffix = '')
    {
        if (false === ($OBJ = &_get_validation_object())) {
            return '';
        }

        return $OBJ->error($field, $prefix, $suffix);
    }
}

// ------------------------------------------------------------------------

/**
 * Validation Error String
 *
 * Returns all the errors associated with a form submission.  This is a helper
 * function for the form validation class.
 *
 * @param	string
 * @param	string
 *
 * @return	string
 */
if (!function_exists('validation_errors')) {
    function validation_errors($prefix = '', $suffix = '')
    {
        if (false === ($OBJ = &_get_validation_object())) {
            return '';
        }

        return $OBJ->error_string($prefix, $suffix);
    }
}

// ------------------------------------------------------------------------

/**
 * Parse the form attributes
 *
 * Helper function used by some of the form helpers
 *
 * @param	array
 * @param	array
 *
 * @return	string
 */
if (!function_exists('_parse_form_attributes')) {
    function _parse_form_attributes($attributes, $default)
    {
        if (is_array($attributes)) {
            foreach ($default as $key => $val) {
                if (isset($attributes[$key])) {
                    $default[$key] = $attributes[$key];
                    unset($attributes[$key]);
                }
            }

            if (count($attributes) > 0) {
                $default = array_merge($default, $attributes);
            }
        }

        $att = '';

        foreach ($default as $key => $val) {
            if ($key == 'value') {
                $val = form_prep($val);
            }

            $att .= $key . '="' . $val . '" ';
        }

        return $att;
    }
}

// ------------------------------------------------------------------------

/**
 * Attributes To String
 *
 * Helper function used by some of the form helpers
 *
 * @param	mixed
 * @param	bool
 *
 * @return	string
 */
if (!function_exists('_attributes_to_string')) {
    function _attributes_to_string($attributes, $formtag = false)
    {
        if (is_string($attributes) and strlen($attributes) > 0) {
            if ($formtag == true and strpos($attributes, 'method=') === false) {
                $attributes .= ' method="post"';
            }

            return ' ' . $attributes;
        }

        if (is_object($attributes) and count($attributes) > 0) {
            $attributes = (array) $attributes;
        }

        if (is_array($attributes) and count($attributes) > 0) {
            $atts = '';

            if (!isset($attributes['method']) and $formtag === true) {
                $atts .= ' method="post"';
            }

            foreach ($attributes as $key => $val) {
                $atts .= ' ' . $key . '="' . $val . '"';
            }

            return $atts;
        }
    }
}

// ------------------------------------------------------------------------

/**
 * Validation Object
 *
 * Determines what the form validation class was instantiated as, fetches
 * the object and returns it.
 *
 * @return	mixed
 */
if (!function_exists('_get_validation_object')) {
    function &_get_validation_object()
    {
        $CI = &get_instance();

        // We set this as a variable since we're returning by reference
        $return = false;

        if (!isset($CI->load->_ci_classes) or  !isset($CI->load->_ci_classes['form_validation'])) {
            return $return;
        }

        $object = $CI->load->_ci_classes['form_validation'];

        if (!isset($CI->{$object}) or !is_object($CI->{$object})) {
            return $return;
        }

        return $CI->{$object};
    }
}

/* End of file form_helper.php */
/* Location: ./system/helpers/form_helper.php */
