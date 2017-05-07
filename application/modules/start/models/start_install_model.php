<?php

namespace Pg\Modules\Start\Models;

class Start_install_model extends \Model
{
    public $CI;

    protected $menu = array();

    /**
     * Dynamic blocks configuration
     *
     * @var array
     */
    protected $dynamic_blocks = array();

    /**
     * Constructor
     *
     * @return Install object
     */
    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();

        // load langs
        $this->CI->load->model('Install_model');
        $this->menu = include MODULEPATH . 'start/install/menu_dating.php';
    }

    public function _validate_requirements()
    {
        $result = array('data' => array(), 'result' => true);

        // php 5.3
        $good = phpversion() >= '5.3.0';
        $result["data"][] = array(
            "name"   => "PHP version >= 5.3.0 ",
            "value"  => $good ? "Yes" : "No",
            "result" => $good,
        );
        $result["result"] = $result["result"] && $good;

        // json
        $good = extension_loaded('json');
        $result["data"][] = array(
            "name"   => "PECL json",
            "value"  => $good ? "Yes" : "No",
            "result" => $good,
        );

        $result["result"] = $result["result"] && $good;

        return $result;
    }

    public function _validate_settings_form($data)
    {
        $return = array("data" => array(), "errors" => array());

        if (isset($data["product_order_key"])) {
            $return["data"]["product_order_key"] = trim(strip_tags($data["product_order_key"]));
            if (empty($return["data"]["product_order_key"])) {
                $this->CI->pg_language->get_string('start', 'error_product_key_incorrect');
            }
        } else {
            $return['errors'][] = $this->CI->pg_language->get_string('start', 'error_product_key_incorrect');
        }

        return $return;
    }

    public function _save_settings_form($data)
    {
        foreach ($data as $setting => $value) {
            $this->CI->pg_module->set_module_config('start', $setting, $value);
        }

        return;
    }

    public function _get_settings_form($submit = false)
    {
        $data = array(
            'product_order_key' => $this->CI->pg_module->get_module_config('start', 'product_order_key'),
        );

        if (empty($data['product_order_key'])) {
            if (file_exists(SITE_PHYSICAL_PATH . 'order_key.txt')) {
                $data['product_order_key'] = file_get_contents(SITE_PHYSICAL_PATH . 'order_key.txt');
                if (!empty($data['product_order_key'])) {
                    $submit = true;
                }
            }
        } elseif ($submit) {
            $data["product_order_key"] = $this->CI->input->post('product_order_key', true);
        }

        if ($submit) {
            $validate = $this->_validate_settings_form($data);
            if (!empty($validate["errors"])) {
                $this->CI->view->assign('settings_errors', $validate["errors"]);
                $data = $validate["data"];
            } else {
                $this->_save_settings_form($validate["data"]);

                return false;
            }
        }

        $this->CI->view->assign('settings_data', $data);
        $html = $this->CI->view->fetch('install_settings_form', 'admin', 'start');

        return $html;
    }

    /*
     * Menu module methods
     *
     */

    public function install_menu()
    {
        $this->CI->load->model('Menu_model');
        $this->CI->load->helper('menu');

        foreach ($this->menu as $gid => $menu_data) {
            $this->menu[$gid]['id'] = linked_install_set_menu($gid, $menu_data["action"], $menu_data["name"]);
            linked_install_process_menu_items($this->menu, 'create', $gid, 0, $this->menu[$gid]["items"]);
        }
    }

    public function install_menu_lang_update($langs_ids = null)
    {
        if (empty($langs_ids)) {
            return false;
        }
        $langs_file = $this->CI->Install_model->language_file_read('start', 'menu', $langs_ids);

        if (!$langs_file) {
            log_message('info', 'Empty menu langs data');

            return false;
        }

        $this->CI->load->model('Menu_model');
        $this->CI->load->helper('menu');

        foreach ($this->menu as $gid => $menu_data) {
            linked_install_process_menu_items($this->menu, 'update', $gid, 0, $this->menu[$gid]["items"], $gid, $langs_file);
        }

        return true;
    }

    public function install_menu_lang_export($langs_ids)
    {
        if (empty($langs_ids)) {
            return false;
        }
        $this->CI->load->model('Menu_model');
        $this->CI->load->helper('menu');

        $return = array();
        foreach ($this->menu as $gid => $menu_data) {
            $temp = linked_install_process_menu_items($this->menu, 'export', $gid, 0, $this->menu[$gid]["items"], $gid, $langs_ids);
            $return = array_merge($return, $temp);
        }

        return array("menu" => $return);
    }

    public function deinstall_menu()
    {
        $this->CI->load->model('Menu_model');

        foreach ($this->menu as $gid => $menu_data) {
            $menu = $this->CI->Menu_model->get_menu_by_gid($gid);
            if ($menu["id"]) {
                $this->CI->Menu_model->delete_menu($menu["id"]);
            }
        }
    }

    /*
     * Arbitrary methods
     *
     */

    public function _arbitrary_installing()
    {
    }

    public function _arbitrary_lang_install($langs_ids)
    {
        // admin_home_page
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }
        $langs_file = $this->CI->Install_model->language_file_read('start', 'admin_home_page', $langs_ids);

        foreach ($langs_file as $gid => $ldata) {
            if (!empty($ldata)) {
                $this->CI->pg_language->pages->set_string_langs('admin_home_page', $gid, $ldata, array_keys($ldata));
            }
        }
        
        //admin_instructions_page
        $langs_file = $this->CI->Install_model->language_file_read('start', 'admin_instructions_page', $langs_ids);

        foreach ($langs_file as $gid => $ldata) {
            if (!empty($ldata)) {
                $this->CI->pg_language->pages->set_string_langs('admin_instructions_page', $gid, $ldata, array_keys($ldata));
            }
        }

        if(SOCIAL_MODE) {
            $filename = 'arbitrary_social';
        } else {
            $filename = 'arbitrary';
        }

        $langs_file = $this->CI->Install_model->language_file_read("start", $filename, $langs_ids);
        if (!$langs_file) {
            log_message("info", "Empty arbitrary langs data");

            return false;
        }

        $post_data = array(
            "title"          => $langs_file["seo_tags_index_title"],
            "keyword"        => $langs_file["seo_tags_index_keyword"],
            "description"    => $langs_file["seo_tags_index_description"],
            "header"         => $langs_file["seo_tags_index_header"],
            "og_title"       => $langs_file["seo_tags_index_og_title"],
            "og_type"        => $langs_file["seo_tags_index_og_type"],
            "og_description" => $langs_file["seo_tags_index_og_description"],
            "url_template"   => "",
        );
        $this->CI->pg_seo->set_settings("user", "", "", $post_data);

        $post_data = array(
            "title"          => $langs_file["seo_tags_admin_title"],
            "keyword"        => $langs_file["seo_tags_admin_keyword"],
            "description"    => $langs_file["seo_tags_admin_description"],
            "header"         => $langs_file["seo_tags_admin_header"],
            "og_title"       => $langs_file["seo_tags_admin_og_title"],
            "og_type"        => $langs_file["seo_tags_admin_og_type"],
            "og_description" => $langs_file["seo_tags_admin_og_description"],
            "url_template"   => "",
        );
        $this->CI->pg_seo->set_settings("admin", "", "", $post_data);
    }

    public function _arbitrary_lang_export($langs_ids = null)
    {
        if (empty($langs_ids)) {
            return false;
        }
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }

        $admin_home_page_return = array();

        // admin_home_page
        foreach ($langs_ids as $lang_id) {
            $mod_langs = $this->CI->pg_language->pages->return_module('admin_home_page', $lang_id);
            foreach ($mod_langs as $gid => $value) {
                $admin_home_page_return[$gid][$lang_id] = $value;
            }
            
            $mod_langs = $this->CI->pg_language->pages->return_module('admin_instructions_page', $lang_id);
            foreach ($mod_langs as $gid => $value) {
                $admin_instructions_page_return[$gid][$lang_id] = $value;
            }
        }

        // arbitrary
        $seo_page = $this->CI->pg_seo->get_settings("user", "", "", $langs_ids);
        $all_lang_ids = array_keys($this->CI->pg_language->languages);

        $prefix = 'seo_tags_index';
        foreach ($all_lang_ids as $lang_id) {
            $meta = 'meta_' . $lang_id;
            $og = 'og_' . $lang_id;
            $arbitrary_return[$prefix . '_title'][$lang_id] = $seo_page[$meta]['title'];
            $arbitrary_return[$prefix . '_keyword'][$lang_id] = $seo_page[$meta]['keyword'];
            $arbitrary_return[$prefix . '_description'][$lang_id] = $seo_page[$meta]['description'];
            $arbitrary_return[$prefix . '_header'][$lang_id] = $seo_page[$meta]['header'];
            $arbitrary_return[$prefix . '_og_title'][$lang_id] = $seo_page[$og]['og_title'];
            $arbitrary_return[$prefix . '_og_type'][$lang_id] = $seo_page[$og]['og_type'];
            $arbitrary_return[$prefix . '_og_description'][$lang_id] = $seo_page[$og]['og_description'];
        }

        $seo_page = $this->CI->pg_seo->get_settings("admin", "", "", $langs_ids);
        $prefix = 'seo_tags_admin';
        foreach ($all_lang_ids as $lang_id) {
            $meta = 'meta_' . $lang_id;
            $og = 'og_' . $lang_id;
            $arbitrary_return[$prefix . '_title'][$lang_id] = $seo_page[$meta]['title'];
            $arbitrary_return[$prefix . '_keyword'][$lang_id] = $seo_page[$meta]['keyword'];
            $arbitrary_return[$prefix . '_description'][$lang_id] = $seo_page[$meta]['description'];
            $arbitrary_return[$prefix . '_header'][$lang_id] = $seo_page[$meta]['header'];
            $arbitrary_return[$prefix . '_og_title'][$lang_id] = $seo_page[$og]['og_title'];
            $arbitrary_return[$prefix . '_og_type'][$lang_id] = $seo_page[$og]['og_type'];
            $arbitrary_return[$prefix . '_og_description'][$lang_id] = $seo_page[$og]['og_description'];
        }
        
        return array("admin_home_page" => $admin_home_page_return, "arbitrary" => $arbitrary_return);
    }

    public function _arbitrary_deinstalling()
    {
        $this->CI->pg_language->pages->delete_module('admin_home_page');
        $this->CI->pg_language->pages->delete_module('admin_instructions_page');
    }

    /*
     * Banners module methods
     *
     */

    public function install_banners()
    {
        // add banners module
        $this->CI->load->model('Start_model');
        $this->CI->load->model('banners/models/Banner_group_model');
        $this->CI->load->model('banners/models/Banner_place_model');
        $this->CI->Banner_group_model->set_module("start", "Start_model", "_banner_available_pages");

        // create banner groups
        $group_attrs = array(
            'date_created'  => date("Y-m-d H:i:s"),
            'date_modified' => date("Y-m-d H:i:s"),
            'price'         => 1,
            'gid'           => 'contact_groups',
            'name'          => 'Contact pages',
        );
        $group_id = $this->CI->Banner_group_model->create_unique_group($group_attrs);
        $all_places = $this->CI->Banner_place_model->get_all_places();
        if ($all_places) {
            foreach ($all_places as $key => $value) {
                if ($value['keyword'] != 'bottom-banner' && $value['keyword'] != 'top-banner' &&
                    $value['keyword'] != 'banner-320x250' && $value['keyword'] != 'banner-320x75') {
                    continue;
                }
                $this->CI->Banner_place_model->save_place_group($value['id'], $group_id);
            }
        }

        $group_attrs = array(
            'date_created'  => date("Y-m-d H:i:s"),
            'date_modified' => date("Y-m-d H:i:s"),
            'price'         => 1,
            'gid'           => 'content_groups',
            'name'          => 'Content pages',
        );
        $group_id = $this->CI->Banner_group_model->create_unique_group($group_attrs);
        $all_places = $this->CI->Banner_place_model->get_all_places();
        if ($all_places) {
            foreach ($all_places as $key => $value) {
                if ($value['keyword'] != 'bottom-banner' && $value['keyword'] != 'top-banner' &&
                    $value['keyword'] != 'banner-185x155' && $value['keyword'] != 'banner-185x75') {
                    continue;
                }
                $this->CI->Banner_place_model->save_place_group($value['id'], $group_id);
            }
        }

        $group_attrs = array(
            'date_created'  => date("Y-m-d H:i:s"),
            'date_modified' => date("Y-m-d H:i:s"),
            'price'         => 1,
            'gid'           => 'users_groups',
            'name'          => 'Users pages',
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

        $group_attrs = array(
            'date_created'  => date("Y-m-d H:i:s"),
            'date_modified' => date("Y-m-d H:i:s"),
            'price'         => 1,
            'gid'           => 'start_groups',
            'name'          => 'Start pages',
        );

        $group_id = $this->CI->Banner_group_model->create_unique_group($group_attrs);
        // add pages in group
        $pages = $this->CI->Start_model->_banner_available_pages();
        if ($pages) {
            foreach ($pages as $key => $value) {
                $page_attrs = array(
                    'group_id' => $group_id,
                    'name'     => $value['name'],
                    'link'     => $value['link'],
                );
                $this->CI->Banner_group_model->add_page($page_attrs);
            }
        }

        // add places in group
        $all_places = $this->CI->Banner_place_model->get_all_places();
        if ($all_places) {
            foreach ($all_places as $key => $value) {
                if ($value['keyword'] != 'bottom-banner') {
                    continue;
                }
                $this->CI->Banner_place_model->save_place_group($value['id'], $group_id);
            }
        }
    }

    public function install_banners_lang_update($langs_ids = null)
    {
        if (empty($langs_ids)) {
            return false;
        }
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }

        $langs_file = $this->CI->Install_model->language_file_read('start', 'banners', $langs_ids);

        if (!$langs_file) {
            log_message('info', 'Empty banners langs data');

            return false;
        }
        $this->CI->load->model('banners/models/Banner_group_model');

        $banners_groups[] = 'banners_group_contact_groups';
        $banners_groups[] = 'banners_group_content_groups';
        $banners_groups[] = 'banners_group_users_groups';
        $banners_groups[] = 'banners_group_start_groups';

        $this->CI->Banner_group_model->update_langs($banners_groups, $langs_file, $langs_ids);

        return true;
    }

    public function install_banners_lang_export($langs_ids)
    {
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }
        $this->CI->load->model('banners/models/Banner_group_model');
        $banners_groups[] = 'banners_group_contact_groups';
        $banners_groups[] = 'banners_group_content_groups';
        $banners_groups[] = 'banners_group_users_groups';
        $banners_groups[] = 'banners_group_start_groups';
        $langs = $this->CI->Banner_group_model->export_langs($banners_groups, $langs_ids);

        return array('banners' => $langs);
    }

    public function deinstall_banners()
    {
        // delete banners module
        $this->CI->load->model("banners/models/Banner_group_model");
        $this->CI->Banner_group_model->delete_module("start");
    }

    /*
     * Dynamic blocks methods
     *
     */

    public function install_dynamic_blocks()
    {
        $this->CI->load->model('Dynamic_blocks_model');
        $this->CI->Dynamic_blocks_model->installBatch($this->dynamic_blocks);
    }

    public function install_dynamic_blocks_lang_update($langs_ids = null)
    {
        $this->CI->load->model('Dynamic_blocks_model');

        return $this->CI->Dynamic_blocks_model->updateLangsByModuleBlocks($this->dynamic_blocks, $langs_ids);
    }

    public function install_dynamic_blocks_lang_export($langs_ids = null)
    {
        $this->CI->load->model('Dynamic_blocks_model');

        return array(
            'dynamic_blocks' => $this->CI->Dynamic_blocks_model->export_langs($this->dynamic_blocks, $langs_ids),
        );
    }

    public function deinstall_dynamic_blocks()
    {
        $this->CI->load->model('Dynamic_blocks_model');
        foreach ($this->dynamic_blocks as $block) {
            $this->CI->Dynamic_blocks_model->delete_block_by_gid($block['gid']);
        }
    }
}
