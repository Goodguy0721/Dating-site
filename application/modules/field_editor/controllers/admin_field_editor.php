<?php

namespace Pg\Modules\Field_editor\Controllers;

use Pg\Libraries\View;

/**
 * Field Editor admin side controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Katya Kashkova <katya@pilotgroup.net>
 * */
class Admin_Field_editor extends \Controller
{
    /**
     * Controller
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Field_editor_model');
        $this->load->model('Menu_model');
        $this->Menu_model->set_menu_active_item('admin_menu', 'system-items');
    }

    /**
     *
     */
    public function sections($type = '', $mode = '')
    {
        if (empty($type)) {
            $type = $this->Field_editor_model->get_default_editor_type(true);
        }
        $this->Field_editor_model->initialize($type);
        $this->view->assign('type', $type);

        $types = $this->Field_editor_model->get_editor_types(true);
        $this->view->assign('types', $types);

        $sections = $this->Field_editor_model->get_section_list();
        $this->view->assign('sections', $sections);
        $sections_count = count($sections);

        $this->load->helper("navigation");
        $page_data = get_admin_pages_data(site_url() . "admin/field_editor/sections/" . $type, $sections_count, $sections_count, 1, 'briefPage');
        $this->view->assign('page_data', $page_data);

        $this->view->assign('mode', $mode);

        $this->Menu_model->set_menu_active_item('admin_fields_menu', 'sections_list_item');
        $this->view->setHeader(l('admin_header_section_list', 'field_editor'));
        $this->view->render('list_sections');
    }

    /**
     *
     */
    public function section_edit($type, $id = null)
    {
        if (empty($type)) {
            $type = $this->Field_editor_model->get_default_editor_type(true);
        }
        $this->Field_editor_model->initialize($type);
        $this->view->assign('type', $type);

        if (!empty($id)) {
            $data = $this->Field_editor_model->get_section_by_id($id);
            foreach ($this->pg_language->languages as $lang_id => $lang_data) {
                $validate_lang[$lang_id] = l('section_' . $data["id"], 'field_editor_sections', $lang_id);
            }
        } else {
            $data = array();
        }

        if ($this->input->post('btn_save')) {
            $post_data = array(
                "gid"             => $this->input->post("gid", true),
                "editor_type_gid" => $type,
            );
            $langs = $this->input->post("langs", true);
            $validate_data = $this->Field_editor_model->validate_section($id, $post_data, $langs);

            if (!empty($validate_data["errors"])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data["errors"]);
                $validate_lang[] = $validate_data["lang"];
            } else {
                if (!$id) {
                    $validate_data["data"]["sorter"] = $this->Field_editor_model->get_section_count($params) + 1;
                }

                $this->Field_editor_model->save_section($id, $validate_data["data"], $validate_data["lang"]);

                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_update_section_data', 'field_editor'));
                redirect(site_url() . "admin/field_editor/sections");
            }
        }

        // languages
        $this->view->assign('languages', $this->pg_language->languages);
        $this->view->assign('languages_count', count($this->pg_language->languages));
        $this->view->assign('cur_lang', $this->pg_language->current_lang_id);

        if (!empty($validate_lang)) {
            $this->view->assign('validate_lang', $validate_lang);
        }

        $this->view->assign('data', $data);
        $this->Menu_model->set_menu_active_item('admin_fields_menu', 'sections_list_item');
        $this->view->setHeader(l('admin_header_section_list', 'field_editor'));
        $this->view->render('edit_sections');
    }

    /**
     *
     */
    public function section_delete($type, $id)
    {
        $this->Field_editor_model->initialize($type);
        if (!empty($id)) {
            $this->Field_editor_model->delete_section($id);
            $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_delete_section', 'field_editor'));
        }
        redirect(site_url() . "admin/field_editor/sections/" . $type);
    }

    /**
     *
     */
    public function ajax_section_sort()
    {
        $item_data = $this->input->post('sorter');
        $item_data = $item_data["parent_0"];
        if (empty($item_data)) {
            return false;
        }

        foreach ($item_data as $key => $sorter) {
            $section_id = intval(str_replace("item_", "", $key));
            if (empty($section_id)) {
                continue;
            }
            $this->Field_editor_model->set_section_sorter($section_id, $sorter);
        }

        return true;
    }

    /**
     *
     */
    public function fields($type = '', $section = '', $mode = '')
    {
        if (empty($type)) {
            $type = $this->Field_editor_model->get_default_editor_type(true);
        }
        $this->Field_editor_model->initialize($type);
        $this->view->assign('type', $type);

        $types = $this->Field_editor_model->get_editor_types(true);
        $this->view->assign('types', $types);

        $sections = $this->Field_editor_model->get_section_list();

        $this->view->assign('sections', $sections);
        if (empty($section)) {
            $t = current($sections);
            $section = $t["gid"];
        }
        $this->view->assign('section', $section);

        $params["where"]["section_gid"] = $section;
        $params["where"]["editor_type_gid"] = $type;
        $fields = $this->Field_editor_model->get_fields_list($params);

        $this->view->assign('fields', $fields);
        $fields_count = count($fields);

        $this->load->helper("navigation");
        $page_data = get_admin_pages_data(site_url() . "admin/field_editor/fields/" . $type . "/" . $section, $fields_count, $fields_count, 1, 'briefPage');
        $this->view->assign('page_data', $page_data);

        $this->view->assign('mode', $mode);

        $this->Menu_model->set_menu_active_item('admin_fields_menu', 'fields_list_item');
        $this->view->setHeader(l('admin_header_fields_list', 'field_editor'));
        $this->view->render('list_fields');
    }

    /**
     *
     */
    public function field_edit($type, $section, $id = null)
    {
        if (empty($type)) {
            $type = $this->Field_editor_model->get_default_editor_type();
        }
        $this->Field_editor_model->initialize($type);
        $this->view->assign('type', $type);
        $this->view->assign('type_settings', $this->Field_editor_model->get_settings());
        $validate_data['data'] = array();
        if (!empty($id)) {
            $data = $this->Field_editor_model->get_field_by_id($id);
            foreach ($this->pg_language->languages as $lang_id => $lang_data) {
                $validate_lang[$lang_id] = $this->Field_editor_model->format_field_name($data, $lang_id);
            }
            $section = $data["section_gid"];
        } else {
            $data['gid'] = $this->Field_editor_model->get_field_gid();
            $data["field_type"] = "text";
        }

        $this->view->assign('section', $section);
        $section_data = $this->Field_editor_model->get_section_by_gid($section);
        $this->view->assign('section_data', $section_data);

        if ($this->input->post('btn_save')) {
            if ($id) {
                $flag = "change";
                $field_type = $data["field_type"];
                $post_data = array(
                    "settings_data" => $this->input->post("settings_data", true),
                    "fts"           => $this->input->post("fts", true),
                );
            } else {
                $flag = "add";
                $post_data = array(
                    "gid"             => $this->input->post("gid", true),
                    "section_gid"     => $section,
                    "editor_type_gid" => $type,
                    "field_type"      => $this->input->post("field_type", true),
                    "fts"             => $this->input->post("fts", true),
                );
                $field_type = $this->input->post("field_type", true);
            }
            $langs = $this->input->post("langs", true);
            $validate_data = $this->Field_editor_model->validate_field($id, $field_type, $post_data, $langs);
            if (!empty($validate_data["errors"])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data["errors"]);
                $validate_lang = $langs;
            } else {
                if ($flag == "change") {
                    $validate_data["data"]["gid"] = $data["gid"];
                    $validate_data["data"]["field_type"] = $data["field_type"];
                } else {
                    $params["where"]["section_gid"] = $section;
                    $validate_data["data"]["sorter"] = $this->Field_editor_model->get_fields_count($params) + 1;
                }

                $id = $this->Field_editor_model->save_field($id, $type, $section, $validate_data["data"], $validate_data['lang']);
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_update_section_data', 'field_editor'));
                if ($flag == "add") {
                    redirect(site_url() . "admin/field_editor/field_edit/" . $type . "/" . $section . "/" . $id);
                } else {
                    redirect(site_url() . "admin/field_editor/fields/" . $type . "/" . $section);
                }
            }
        }
        $validate_data['data'] = array_merge($validate_data['data'], $data);

        // languages
        $this->view->assign('languages', $this->pg_language->languages);
        $this->view->assign('languages_count', count($this->pg_language->languages));
        $this->view->assign('cur_lang', $this->pg_language->current_lang_id);

        if (!empty($validate_lang)) {
            $this->view->assign('validate_lang', $validate_lang);
        }

        if ($id) {
            $this->view->assign('type_block_content', $this->getFieldTypeBlock($data["field_type"], $data));
        }

        $this->view->assign('data', $validate_data["data"]);
        $this->view->assign('field_type_lang', ld('field_type', 'field_editor'));
        $this->Menu_model->set_menu_active_item('admin_fields_menu', 'fields_list_item');
        $this->view->setHeader(l('admin_header_fields_list', 'field_editor'));
        $this->view->render('edit_fields');
    }

    /**
     *
     */
    private function getFieldTypeBlock($field_type, $data)
    {
        $this->view->assign('field_type', $field_type);
        $this->view->assign('data', $data);
        $this->view->assign('initial', $this->Field_editor_model->get_field_settings($field_type));

        if ($field_type == 'select' || $field_type == 'multiselect') {
            $this->view->assign('options_block', $this->getFieldSelectOptions($data["options"], $data["settings_data_array"]["default_value"]));
        }

        return $this->view->fetch('edit_fields_type_block');
    }

    /**
     *
     */
    private function getFieldSelectOptions($data, $default_option_gid = 0)
    {
        $this->view->assign('reference_data', $data);
        $this->view->assign('default_option_gid', $default_option_gid);

        return $this->view->fetch('edit_fields_select_options');
    }

    /**
     *
     */
    public function ajax_get_field_select_options($id_field)
    {
        $field_data = $this->Field_editor_model->get_field_by_id($id_field);
        echo $this->getFieldSelectOptions($field_data["options"], $field_data["settings_data_array"]["default_value"]);
    }

    /**
     *
     */
    public function ajax_get_select_option_form($id_field, $option_gid = '')
    {
        $field_data = $this->Field_editor_model->get_field_by_id($id_field);

        foreach ($this->pg_language->languages as $lid => $lang) {
            $lang_field_data = $this->Field_editor_model->format_field($field_data, $lid);
            $lang_data[$lid]["field_name"] = $lang_field_data["name"];
            $lang_data[$lid]["name"] = $lang["name"];
            $lang_data[$lid]["value"] = ($option_gid) ? $lang_field_data['options']["option"][$option_gid] : "";
        }

        $this->view->assign('field_data', $field_data);
        $this->view->assign('lang_data', $lang_data);
        $this->view->assign('option_gid', $option_gid);
        $this->view->render('edit_fields_select_option_form');
    }

    /**
     *
     */
    public function ajax_set_select_option($id_field, $option_gid = '')
    {
        $lang_data = $this->input->post('data', true);
        $return = $this->Field_editor_model->validate_field_option($id_field, $option_gid, $lang_data);
        if (empty($return['errors'])) {
            $this->Field_editor_model->set_field_option($id_field, $option_gid, $return['lang']);
            $return['success'] = l('success_update_option_data', 'field_editor');
            $return['is_success'] = 1;
        } else {
            $return['errors'] = implode(', ', $return['errors']);
            $return['is_error'] = 1;
        }
        $this->view->assign($return);

        return;
    }

    /**
     *
     */
    public function ajax_delete_select_option($id_field, $option_gid)
    {
        $this->Field_editor_model->delete_field_option($id_field, $option_gid);

        return;
    }

    /**
     *
     */
    public function ajax_save_select_option_sorter($id_field)
    {
        $field_data = $this->Field_editor_model->get_field_by_id($id_field);

        $sorter = $this->input->post("sorter");
        foreach ($sorter as $item_str => $sort_index) {
            $sorter_data[$sort_index] = str_replace("option_", "", $item_str);
        }

        if (empty($sorter_data)) {
            return;
        }
        ksort($sorter_data);
        $this->Field_editor_model->sorter_field_option($id_field, $sorter_data);

        return;
    }

    /**
     *
     */
    public function field_delete($type, $section, $id)
    {
        $this->Field_editor_model->initialize($type);
        if (!empty($id)) {
            $this->Field_editor_model->delete_field($id);
            $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_delete_field', 'field_editor'));
        }
        redirect(site_url() . "admin/field_editor/fields/" . $type . "/" . $section);
    }

    /**
     *
     */
    public function ajax_field_sort()
    {
        $item_data = $this->input->post('sorter');
        $item_data = $item_data["parent_0"];
        if (empty($item_data)) {
            return false;
        }

        foreach ($item_data as $key => $sorter) {
            $field_id = intval(str_replace("item_", "", $key));
            if (empty($field_id)) {
                continue;
            }
            $this->Field_editor_model->set_field_sorter($field_id, $sorter);
        }

        return true;
    }

    /**
     *
     */
    public function forms($type = '')
    {
        if (empty($type)) {
            $type = $this->Field_editor_model->get_default_editor_type(true);
        }
        $this->Field_editor_model->initialize($type);
        $this->view->assign('type', $type);

        $types = $this->Field_editor_model->get_editor_types(true);
        $this->view->assign('types', $types);

        $this->load->model('field_editor/models/Field_editor_forms_model');
        $params["where"]["editor_type_gid"] = $type;
        $forms = $this->Field_editor_forms_model->get_forms_list($params);
        $this->view->assign('forms', $forms);

        $forms_count = count($forms);

        $this->load->helper("navigation");
        $page_data = get_admin_pages_data(site_url() . "admin/field_editor/forms/" . $type, $forms_count, $forms_count, 1, 'briefPage');
        $this->view->assign('page_data', $page_data);

        $this->Menu_model->set_menu_active_item('admin_fields_menu', 'forms_list_item');
        $this->view->setHeader(l('admin_header_forms_list', 'field_editor'));
        $this->view->render('list_forms');
    }

    /**
     *
     */
    public function form_edit($type, $id = 0)
    {
        if (empty($type)) {
            $type = $this->Field_editor_model->get_default_editor_type(true);
        }
        $this->Field_editor_model->initialize($type);
        $this->view->assign('type', $type);

        $types = $this->Field_editor_model->get_editor_types(true);
        $this->view->assign('types', $types);

        $this->load->model('field_editor/models/Field_editor_forms_model');
        if (!empty($id)) {
            $data = $this->Field_editor_forms_model->get_form_by_id($id);
        } else {
            $data = array('editor_type_gid' => $type);
        }

        if ($this->input->post('btn_save')) {
            $post_data = array(
                "gid"             => $this->input->post("gid", true),
                "name"            => $this->input->post("name", true),
                "editor_type_gid" => $this->input->post("editor_type_gid", true),
            );

            $validate_data = $this->Field_editor_forms_model->validate_form($id, $post_data);
            if (!empty($validate_data["errors"])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data["errors"]);
            } else {
                $new_id = $this->Field_editor_forms_model->save_form($id, $validate_data["data"]);

                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_update_form_data', 'field_editor'));
                redirect(site_url() . "admin/field_editor/forms");
            }
        }

        $this->view->assign('data', $data);
        $this->Menu_model->set_menu_active_item('admin_fields_menu', 'forms_list_item');
        $this->view->setHeader(l('admin_header_forms_list', 'field_editor'));
        $this->view->render('edit_forms');
    }

    /**
     *
     */
    public function form_delete($type, $id = 0)
    {
        if (empty($type)) {
            $type = $this->Field_editor_model->get_default_editor_type(true);
        }
        $this->Field_editor_model->initialize($type);

        $this->load->model('field_editor/models/Field_editor_forms_model');
        $form = $this->Field_editor_forms_model->get_form_by_id($id);
        if ($form['is_system']) {
            $this->system_messages->addMessage(View::MSG_ERROR, l('error_delete_system_form', 'field_editor'));

            return redirect(site_url() . "admin/field_editor/forms/" . $type);
        }
        $this->Field_editor_forms_model->delete_form_by_id($id);
        $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_delete_form', 'field_editor'));
        redirect(site_url() . "admin/field_editor/forms/" . $type);
    }

    /**
     *
     */
    public function form_fields($id)
    {
        $this->load->model('field_editor/models/Field_editor_forms_model');
        $data = $this->Field_editor_forms_model->get_form_by_id($id);
        $data = $this->Field_editor_forms_model->format_form($data);
        $data["field_data_json"] = (!empty($data["field_data"])) ? json_encode($data["field_data"]) : '';

        $data["names"] = array();
        if (!empty($data["field_data"])) {
            list($section_gids, $field_gids) = $this->getDisallowedData($data["field_data"]);
            $this->Field_editor_model->initialize($data["editor_type_gid"]);

            if (!empty($section_gids)) {
                $raw_sections = $this->Field_editor_forms_model->get_form_sections($data["editor_type_gid"]);
                foreach ($raw_sections as $sgid => $sname) {
                    $data["names"]["section_" . $sgid] = $sname;
                }
            }
            if (!empty($field_gids)) {
                $f_params["where_in"]["gid"] = $field_gids;
                $raw_fields = $this->Field_editor_model->get_fields_list($f_params);
                foreach ($raw_fields as $r) {
                    $data["names"]["field_" . $r["gid"]] = $r["name"];
                }
            }
        }
        $data["field_names_json"] = (!empty($data["names"])) ? json_encode($data["names"]) : '';

        $this->view->assign('data', $data);
        $this->Menu_model->set_menu_active_item('admin_fields_menu', 'forms_list_item');
        $this->view->setBackLink(site_url() . "admin/field_editor/forms");
        $this->view->setHeader(l('admin_header_forms_list', 'field_editor'));
        $this->view->render('edit_form_fields');
    }

    /**
     *
     */
    public function ajax_save_form_fields($id)
    {
        $this->load->model('field_editor/models/Field_editor_forms_model');
        $post_data = array(
            'field_data' => $this->input->post("field_data", true),
        );
        $validate_data = $this->Field_editor_forms_model->validate_form($id, $post_data);
        $this->Field_editor_forms_model->save_form($id, $validate_data["data"]);
        $return["success"] = l('success_update_form_data', 'field_editor');

        $this->view->assign($return);
    }

    /**
     *
     */
    public function ajax_get_add_section_form($id, $section_gid = '')
    {
        $this->load->model('field_editor/models/Field_editor_forms_model');
        $form = $this->Field_editor_forms_model->get_form_by_id($id);

        if (!empty($section_gid)) {
            $data = $this->Field_editor_forms_model->get_form_section($form["editor_type_gid"], $section_gid, 'all');
        } else {
            $data = array();
        }
        ///// languages
        $this->view->assign('languages', $this->pg_language->languages);
        $this->view->assign('cur_lang', $this->pg_language->current_lang_id);
        $this->view->assign('section_gid', $section_gid);

        $this->view->assign('data', $data);
        echo $this->view->fetch('ajax_form_add_section');
    }

    /**
     *
     */
    public function ajax_get_add_field_form($id, $section_gid = '')
    {
        $this->load->model('field_editor/models/Field_editor_forms_model');
        $data = $this->Field_editor_forms_model->get_form_by_id($id);

        list($disallowed_sections, $disallowed_fields) = $this->getDisallowedData($data["field_data"]);

        $this->Field_editor_model->initialize($data["editor_type_gid"]);

        if (!$section_gid) {
            $sections = $this->Field_editor_model->get_section_list();

            $this->view->assign('sections', $sections);
            $this->view->assign('form_type', 'sections');
        } else {
            if (!empty($disallowed_fields)) {
                $params["where_sql"][] = "gid NOT IN ('" . implode("', '", $disallowed_fields) . "')";
            } else {
                $params = array();
            }
            $params["where"]["section_gid"] = $section_gid;
            $params["where"]["editor_type_gid"] = $data['editor_type_gid'];
            $fields = $this->Field_editor_model->get_fields_list($params);
            $this->view->assign('fields', $fields);
            $this->view->assign('form_type', 'fields');
        }

        echo $this->view->fetch('ajax_form_add_field');
    }

    /**
     *
     */
    public function ajax_get_section_data($id_form, $section_gid = '')
    {
        $this->load->model('field_editor/models/Field_editor_forms_model');
        $data = $this->Field_editor_forms_model->get_form_by_id($id_form);
        $return["action"] = (!empty($section_gid)) ? 'update' : "add";

        $langs = $this->input->post("langs", true);
        if (!empty($langs)) {
            $section_gid = $this->Field_editor_forms_model->form_section_save($data["editor_type_gid"], $section_gid, $langs);
        }
        if (empty($section_gid)) {
            return false;
        }

        $return["data"] = array(
            "type"    => 'section',
            "section" => array("gid" => $section_gid),
            "fields"  => array(),
        );
        $return["names"]["section_" . $section_gid] = $this->Field_editor_forms_model->get_form_section($data["editor_type_gid"], $section_gid);

        $this->view->assign($return);
    }

    /**
     *
     */
    public function ajax_get_field_data($id_form, $field_gid)
    {
        $this->load->model('field_editor/models/Field_editor_forms_model');
        $data = $this->Field_editor_forms_model->get_form_by_id($id_form);
        list($disallowed_sections, $disallowed_fields) = $this->getDisallowedData($data["field_data"]);

        if (!empty($disallowed_fields) && in_array($field_gid, $disallowed_fields)) {
            echo false;
        }
        $this->Field_editor_model->initialize($data["editor_type_gid"]);
        $field = $this->Field_editor_model->get_field_by_gid($field_gid);

        $settings = array();
        $default = $this->Field_editor_forms_model->get_form_field_settings($field["field_type"]);
        if (!empty($default)) {
            foreach ($default as $pname => $pdata) {
                $settings[$pname] = $pdata["default"];
            }
        }

        $return["data"] = array(
            'type'  => 'field',
            'field' => array(
                'gid'         => $field["gid"],
                'section_gid' => $field["section_gid"],
                'type'        => $field["field_type"],
            ),
            'settings' => $settings,
        );
        $return["names"]["field_" . $field_gid] = $field["name"];

        if (!empty($return["data"])) {
            $this->view->assign($return);
        }

        echo false;
    }

    /**
     *
     */
    public function ajax_delete_form_section($id_form, $section_gid)
    {
        $this->load->model('field_editor/models/Field_editor_forms_model');
        $data = $this->Field_editor_forms_model->get_form_by_id($id_form);
        $this->Field_editor_forms_model->form_section_delete($data["editor_type_gid"], $section_gid);

        return;
    }

    /**
     *
     */
    public function ajax_get_field_settings_form($id_form, $field_gid)
    {
        $this->load->model('field_editor/models/Field_editor_forms_model');
        $data = $this->Field_editor_forms_model->get_form_by_id($id_form);

        $field = array();
        if (!empty($data["field_data"])) {
            foreach ($data["field_data"] as $item) {
                if ($item["type"] == 'section') {
                    if (!empty($item["section"]["fields"])) {
                        foreach ($item["section"]["fields"] as $f) {
                            if ($f["field"]['gid'] == $field_gid) {
                                $field = $f;
                                break;
                            }
                        }
                    }
                } elseif ($item["type"] == 'field' && $item["field"]['gid'] == $field_gid) {
                    $field = $item;
                    break;
                }
            }
        }

        if (empty($field) || !in_array($field["field"]["type"], array('select', 'text', 'range'))) {
            echo "";

            return;
        }

        $this->view->assign('settings', $this->Field_editor_forms_model->get_form_field_settings($field["field"]["type"]));
        $this->view->assign('field_type', $field["field"]["type"]);
        $this->view->assign('field', $field);
        echo $this->view->fetch('ajax_form_field_settings');
    }

    /**
     *
     */
    private function getDisallowedData($field_data)
    {
        $this->load->model('field_editor/models/Field_editor_forms_model');

        return $this->Field_editor_forms_model->get_form_field_gids($field_data);
    }
}
