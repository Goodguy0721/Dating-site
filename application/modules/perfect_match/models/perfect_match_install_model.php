<?php

namespace Pg\Modules\Perfect_match\Models;

/**
 * Perfect_match install model
 *
 * @package 	PG_Dating
 * @subpackage 	Perfect_match
 *
 * @category	models
 *
 * @copyright 	Copyright (c) 2000-2014 PG Dating Pro - php dating software
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
class Perfect_match_install_model extends \Model
{
    /**
     * Link to CodeIgniter object
     *
     * @var object
     */
    private $CI;

    /**
     * Menu configuration
     *
     * @var array
     */
    private $menu = array(
        'user_top_menu' => array(
            'action' => 'none',
            'name'   => 'Perfect match',
            'items'  => array(
                'user-menu-people' => array(
                    'action' => 'none',
                    'items'  => array(
                        'perfectmatch_item' => array('action' => 'create', 'link' => 'perfect_match/index', 'status' => 1, 'sorter' => 20),
                    ),
                ),
            ),
        ),
    );

    protected $seo_pages = array(
        'index',
        'search',
    );

    /**
     * Constructor
     *
     * @return Perfect_match_install_model
     */
    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
    }

    /**
     * Install menu data of perfect_match
     *
     * @return void
     */
    public function install_menu()
    {
        $this->CI->load->helper("menu");
        foreach ($this->menu as $gid => $menu_data) {
            $this->menu[$gid]["id"] = linked_install_set_menu($gid, $menu_data["action"], isset($menu_data["name"]) ? $menu_data["name"] : '');
            linked_install_process_menu_items($this->menu, "create", $gid, 0, $this->menu[$gid]["items"]);
        }
    }

    /**
     * Import menu languages of perfect_match
     *
     * @param array $langs_ids languages identifiers
     *
     * @return void
     */
    public function install_menu_lang_update($langs_ids = null)
    {
        if (empty($langs_ids)) {
            return false;
        }
        $langs_file = $this->CI->Install_model->language_file_read("perfect_match", "menu", $langs_ids);

        if (!$langs_file) {
            log_message("info", "Empty menu langs data");

            return false;
        }

        $this->CI->load->helper("menu");

        foreach ($this->menu as $gid => $menu_data) {
            linked_install_process_menu_items($this->menu, "update", $gid, 0, $this->menu[$gid]["items"], $gid, $langs_file);
        }

        return true;
    }

    /**
     * Export menu languages of perfect_match
     *
     * @param array $langs_ids languages identifiers
     *
     * @return array
     */
    public function install_menu_lang_export($langs_ids)
    {
        if (empty($langs_ids)) {
            return false;
        }
        $this->CI->load->helper("menu");

        $return = array();
        foreach ($this->menu as $gid => $menu_data) {
            $temp = linked_install_process_menu_items($this->menu, "export", $gid, 0, $this->menu[$gid]["items"], $gid, $langs_ids);
            $return = array_merge($return, $temp);
        }

        return array("menu" => $return);
    }

    /**
     * Uninstall menu data of perfect_match
     *
     * @return void
     */
    public function deinstall_menu()
    {
        $this->CI->load->helper("menu");
        foreach ($this->menu as $gid => $menu_data) {
            if ($menu_data["action"] == "create") {
                linked_install_set_menu($gid, "delete");
            } else {
                linked_install_delete_menu_items($gid, $this->menu[$gid]["items"]);
            }
        }
    }

    public function install_field_editor()
    {
        $this->CI->load->model('Users_model');
        $this->CI->load->model('Field_editor_model');
        $this->CI->Field_editor_model->initialize($this->CI->Users_model->form_editor_type);
        include MODULEPATH . 'perfect_match/install/user_fields_data.php';
        $this->CI->Field_editor_model->import_type_structure($this->CI->Users_model->form_editor_type, $fe_sections, $fe_fields, $fe_forms);
    }

    public function install_field_editor_lang_update()
    {
        $langs_file = $this->CI->Install_model->language_file_read('perfect_match', 'field_editor');
        if (!$langs_file) {
            log_message('info', 'Empty field_editor langs data');

            return false;
        }
        $this->CI->load->model('Users_model');
        $this->CI->load->model('Field_editor_model');
        $this->CI->Field_editor_model->initialize($this->CI->Users_model->form_editor_type);
        include MODULEPATH . 'perfect_match/install/user_fields_data.php';
        $this->CI->Field_editor_model->update_sections_langs($fe_sections, $langs_file);
        if (isset($fe_fields) && !empty($fe_fields)) {
            $this->CI->Field_editor_model->update_fields_langs($this->CI->Users_model->form_editor_type, $fe_fields, $langs_file);
        }

        return true;
    }

    public function install_field_editor_lang_export($langs_ids = null)
    {
        $this->CI->load->model('Users_model');
        $this->CI->load->model('Field_editor_model');
        $this->CI->Field_editor_model->initialize($this->CI->Users_model->form_editor_type);
        list($fe_sections, $fe_fields, $fe_forms) = $this->CI->Field_editor_model->export_type_structure($this->CI->Users_model->form_editor_type, 'application/modules/perfect_match/install/user_fields_data.php');
        $sections = $this->CI->Field_editor_model->export_sections_langs($fe_sections, $langs_ids);
        $fields = $this->CI->Field_editor_model->export_fields_langs($this->CI->Users_model->form_editor_type, $fe_fields, $langs_ids);

        return array('field_editor' => array_merge($sections, $fields));
    }

    public function deinstall_field_editor()
    {
        $this->CI->load->model('Field_editor_model');
        $this->CI->load->model('field_editor/models/Field_editor_forms_model');

        include MODULEPATH . 'perfect_match/install/user_fields_data.php';

        foreach ($fe_fields as $field) {
            $this->CI->Field_editor_model->delete_field_by_gid($field['data']['gid']);
        }
        $this->CI->load->model('Users_model');
        $this->CI->Field_editor_model->initialize($this->CI->Users_model->form_editor_type);
        foreach ($fe_sections as $section) {
            $this->CI->Field_editor_model->delete_section_by_gid($section['data']['gid']);
        }

        foreach ($fe_forms as $form) {
            $this->CI->Field_editor_forms_model->delete_form_by_gid($form['data']['gid']);
        }

        return;
    }

    public function install_users()
    {
        $this->CI->load->model('users/models/Users_delete_callbacks_model');
        $this->CI->Users_delete_callbacks_model->add_callback('perfect_match', 'Perfect_match_model', 'callback_user_delete', '', 'perfect_match');

        // add fields
        $this->CI->load->model('Field_editor_model');
        $fields_list = $this->CI->Field_editor_model->get_fields_list();

        if (!empty($fields_list)) {
            foreach ($fields_list as $fields => $field) {
                $this->CI->Field_editor_model->pm_field_create(DB_PREFIX . 'perfect_match', 'looking_' . $field['field_name'], $field);
                $fields_arr[] = $this->CI->Field_editor_model->pm_field_create(DB_PREFIX . 'perfect_match', $field['field_name'], $field);
            }
            $this->CI->Field_editor_model->pm_update_fields($fields_arr, DB_PREFIX . 'perfect_match');
        }

        $this->add_demo_content();
    }

    public function add_demo_content()
    {
        include MODULEPATH . 'perfect_match/install/demo_content.php';

        $this->CI->load->model('Users_model');
        foreach ($demo_users as $user) {
            $user_data = $this->CI->Users_model->get_user_by_login($user['nickname']);
            $user_id = array_intersect_key($user_data, array('id' => 0));
            unset($user_data);
            $this->CI->Users_model->save_user($user_id['id'], $user['data']);
        }

        $this->transfer_user_data();
    }

    /**
     * Data transfer from USERS_TABLE in PERFECT_MATCH_TABLE
     */
    public function transfer_user_data()
    {
        $this->CI->load->model('Users_model');
        $this->CI->load->model('Perfect_match_model');

        $this->CI->db->simple_query("UPDATE `" . PERFECT_MATCH_TABLE . "` INNER JOIN `" . USERS_TABLE . "` ON `" . USERS_TABLE . "`.`id` = `" . PERFECT_MATCH_TABLE . "`.`id_user` SET `" . PERFECT_MATCH_TABLE . "`.`looking_user_type`=`" . USERS_TABLE . "`.`looking_user_type`");
        $this->CI->db->simple_query("UPDATE `" . PERFECT_MATCH_TABLE . "` INNER JOIN `" . USERS_TABLE . "` ON `" . USERS_TABLE . "`.`id` = `" . PERFECT_MATCH_TABLE . "`.`id_user` SET `" . PERFECT_MATCH_TABLE . "`.`looking_id_country`=`" . USERS_TABLE . "`.`id_country`");
        $this->CI->db->simple_query("UPDATE `" . PERFECT_MATCH_TABLE . "` INNER JOIN `" . USERS_TABLE . "` ON `" . USERS_TABLE . "`.`id` = `" . PERFECT_MATCH_TABLE . "`.`id_user` SET `" . PERFECT_MATCH_TABLE . "`.`looking_id_region`=`" . USERS_TABLE . "`.`id_region`");
        $this->CI->db->simple_query("UPDATE `" . PERFECT_MATCH_TABLE . "` INNER JOIN `" . USERS_TABLE . "` ON `" . USERS_TABLE . "`.`id` = `" . PERFECT_MATCH_TABLE . "`.`id_user` SET `" . PERFECT_MATCH_TABLE . "`.`looking_id_city`=`" . USERS_TABLE . "`.`id_city`");
        $this->CI->db->simple_query("UPDATE `" . PERFECT_MATCH_TABLE . "` INNER JOIN `" . USERS_TABLE . "` ON `" . USERS_TABLE . "`.`id` = `" . PERFECT_MATCH_TABLE . "`.`id_user` SET `" . PERFECT_MATCH_TABLE . "`.`age_min`=`" . USERS_TABLE . "`.`age_min`");
        $this->CI->db->simple_query("UPDATE `" . PERFECT_MATCH_TABLE . "` INNER JOIN `" . USERS_TABLE . "` ON `" . USERS_TABLE . "`.`id` = `" . PERFECT_MATCH_TABLE . "`.`id_user` SET `" . PERFECT_MATCH_TABLE . "`.`age_max`=`" . USERS_TABLE . "`.`age_max`");
    }

    public function deinstall_users()
    {
        $this->CI->load->model('users/models/Users_delete_callbacks_model');
        $this->CI->Users_delete_callbacks_model->delete_callbacks_by_module('perfect_match');
    }

    public function install_banners()
    {
        // add banners module
        $this->CI->load->model("banners/models/Banner_group_model");
        $this->CI->Banner_group_model->set_module("perfect_match", "Perfect_match_model", "bannerAvailablePages");

        $this->add_banners();
    }

    /**
     * Add default banners
     */
    public function add_banners()
    {
        $this->CI->load->model("banners/models/Banner_group_model");
        $this->CI->load->model("banners/models/Banner_place_model");

        $group_attrs = array(
            'date_created'  => date("Y-m-d H:i:s"),
            'date_modified' => date("Y-m-d H:i:s"),
            'price'         => 1,
            'gid'           => 'perfect_match_groups',
            'name'          => 'Perfect match pages',
        );
        $group_id = $this->CI->Banner_group_model->create_unique_group($group_attrs);
        $all_places = $this->CI->Banner_place_model->get_all_places();
        if ($all_places) {
            foreach ($all_places as $key => $value) {
                if ($value['keyword'] != 'bottom-banner' && $value['keyword'] != 'top-banner') {
                    continue;
                }
                $this->CI->Banner_place_model->save_place_group($value['id'], $group_id);
            }
        }

        ///add pages in group
        $this->CI->load->model("Perfect_match_model");
        $pages = $this->CI->Perfect_match_model->bannerAvailablePages();
        if ($pages) {
            foreach ($pages as $key => $value) {
                $page_attrs = array(
                    "group_id" => $group_id,
                    "name"     => $value["name"],
                    "link"     => $value["link"],
                );
                $this->CI->Banner_group_model->add_page($page_attrs);
            }
        }
    }

    /**
     * Import banners languages
     */
    public function install_banners_lang_update($langs_ids = null)
    {
        if (empty($langs_ids)) {
            return false;
        }
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }

        $banners_groups = array('banners_group_perfect_match_groups');
        $langs_file = $this->CI->Install_model->language_file_read('perfect_match', 'pages', $langs_ids);
        $this->CI->load->model('banners/models/Banner_group_model');
        $this->CI->Banner_group_model->update_langs($banners_groups, $langs_file, $langs_ids);
    }

    public function deinstall_banners()
    {
        // delete banners module
        $this->CI->load->model("banners/models/Banner_group_model");
        $this->CI->Banner_group_model->delete_module("perfect_match");
        $this->remove_banners();
    }

    public function remove_banners()
    {
        $this->CI->load->model('banners/models/Banner_group_model');
        $group_id = $this->CI->Banner_group_model->get_group_id_by_gid('perfect_match_groups');
        $this->CI->Banner_group_model->delete($group_id);
    }

    public function _arbitrary_installing()
    {
        // SEO
        $seo_data = array(
            'module_gid'              => 'perfect_match',
            'model_name'              => 'Perfect_match_model',
            'get_settings_method'     => 'getSeoSettings',
            'get_rewrite_vars_method' => 'requestSeoRewrite',
            'get_sitemap_urls_method' => 'getSitemapXmlUrls',
        );
        $this->CI->pg_seo->set_seo_module('perfect_match', $seo_data);
    }

    /**
     * Unistall data of users module
     *
     * @return void
     */
    public function _arbitrary_deinstalling()
    {
        $this->CI->pg_seo->delete_seo_module("perfect_match");
    }

    public function _arbitrary_lang_install($langs_ids = null)
    {
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }
        $langs_file = $this->CI->Install_model->language_file_read('perfect_match', 'arbitrary');
        if (!$langs_file) {
            log_message('info', 'Empty arbitrary langs data');

            return false;
        }
        foreach ($this->seo_pages as $page) {
            $post_data = array(
                'title'          => $langs_file["seo_tags_{$page}_title"],
                'keyword'        => $langs_file["seo_tags_{$page}_keyword"],
                'description'    => $langs_file["seo_tags_{$page}_description"],
                'header'         => $langs_file["seo_tags_{$page}_header"],
                'og_title'       => $langs_file["seo_tags_{$page}_og_title"],
                'og_type'        => $langs_file["seo_tags_{$page}_og_type"],
                'og_description' => $langs_file["seo_tags_{$page}_og_description"],
            );
            $this->CI->pg_seo->set_settings('user', 'perfect_match', $page, $post_data);
        }
    }

    public function _arbitrary_lang_export($langs_ids = null)
    {
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }

        $settings = $this->CI->pg_seo->get_settings("user", "perfect_match", "index", $langs_ids);
        $arbitrary_return["seo_tags_perfect_match_title"] = $settings["title"];
        $arbitrary_return["seo_tags_perfect_match_keyword"] = $settings["keyword"];
        $arbitrary_return["seo_tags_perfect_match_description"] = $settings["description"];
        $arbitrary_return["seo_tags_perfect_match_header"] = $settings["header"];
        $arbitrary_return["seo_tags_perfect_match_og_title"] = $settings["og_title"];
        $arbitrary_return["seo_tags_perfect_match_og_type"] = $settings["og_type"];
        $arbitrary_return["seo_tags_perfect_match_og_description"] = $settings["og_description"];

        return array('arbitrary' => $arbitrary_return);
    }
}
