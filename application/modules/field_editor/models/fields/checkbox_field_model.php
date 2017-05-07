<?php

namespace Pg\Modules\Field_editor\Models\Fields;

/**
 * Checkbox field model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Katya Kashkova <katya@pilotgroup.net>
 * */
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Checkbox_field_model extends Field_type_model
{
    public $base_field_param = array(
        'type'       => 'TINYINT',
        'constraint' => 3,
        'null'       => false,
        'default'    => 0,
    );
    public $manage_field_param = array(
        'default_value' => array('type' => 'bool', "default" => false),
    );
    public $form_field_settings = array();

    public function format_form_fields($field, $content = null)
    {
        parent::format_form_fields($field, $content);
        $field["value"] = !is_null($content) ? $content : false;
        if ($field["value"] === false) {
            $field["value"] = $field["settings_data_array"]["default_value"];
        }

        return $field;
    }

    public function format_fulltext_fields($settings, $field, $value)
    {
        return $value ? $field['name'] . ';' : '';
    }

    public function validate_field($settings, $value)
    {
        $return = array("errors" => array(), "data" => $value);
        $return["data"] = ($return["data"]) ? 1 : 0;

        return $return;
    }

    public function get_search_field_criteria($field, $settings, $data, $prefix)
    {
        $criteria = array();
        $gid = $field['gid'];
        if (!empty($data[$gid]) && $data[$gid] == 1) {
            $criteria["where"][$prefix . $gid] = 1;
        }

        return $criteria;
    }

    public function set_field_option($field, $option_gid, $data)
    {
        return;
    }

    public function delete_field_option($field, $option_gid)
    {
        return;
    }

    public function sorter_field_option($field, $sorter_data)
    {
        return;
    }
}
