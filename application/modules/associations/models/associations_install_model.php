<?php

namespace Pg\Modules\Associations\Models;

/**
 * Associations install model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Nikita Savanaev <nsavanaev@pilotgroup.net>
 * */
class Associations_install_model extends \Model
{
    protected $CI;

    /**
     * Menu configuration
     *
     * @params
     */
    protected $menu = array(
        'admin_menu' => array(
            'action' => 'none',
            'name'   => 'Associations section menu',
            'items'  => array(
                'other_items' => array(
                    'action' => 'none',
                    'name'   => '',
                    'items'  => array(
                        "add_ons_items" => array(
                            "action" => "none",
                            "items"  => array(
                                "associations_menu_item" => array("action" => "create", "link" => "admin/associations", "status" => 1, "sorter" => 10),
                            ),
                        ),
                    ),
                ),
            ),
        ),
        'admin_associations_menu' => array(
            'action' => 'create',
            'name'   => 'Associations section menu',
            'items'  => array(
                'associations_list_item'     => array('action' => 'create', 'link' => 'admin/associations/index', 'status' => 1, 'sorter' => 1),
                'associations_settings_item' => array('action' => 'create', 'link' => 'admin/associations/settings', 'status' => 1, 'sorter' => 3),
            ),
        ),
        'user_top_menu' => array(
            'action' => 'none',
            'name'   => 'Associations section menu',
            'items'  => array(
                'user-menu-activities' => array(
                    'action' => 'none',
                    'items'  => array(
                        'user_associations_item' => array('action' => 'create', 'link' => 'associations/index', 'status' => 1, 'sorter' => 50, 'indicator_gid' => 'new_association_item', 1),
                    ),
                ),
            ),
        ),
    );

    /**
     * Indicators configuration
     */
    private $menu_indicators = array(
        array(
            'gid'            => 'new_association_item',
            'delete_by_cron' => false,
            'auth_type'      => 'user',
        ),
    );

    /**
     * Fields depended on languages
     *
     * @var array
     */
    private $lang_dm_data = array(
        array(
            "module"        => "associations",
            "model"         => "Associations_model",
            "method_add"    => "langDedicateModuleCallbackAdd",
            "method_delete" => "langDedicateModuleCallbackDelete",
        ),
    );

    /**
     * Seo pages configuration
     *
     * @params
     */
    private $_seo_pages = array(
        'index',
    );

    /**
     * Notifications configuration
     *
     * @params
     */
    protected $_notifications = array(
        'notifications' => array(
            array('gid' => 'association', 'send_type' => 'simple'),
        ),
        'templates' => array(
            array('gid' => 'association', 'name' => 'Association', 'vars' => array('avatar', 'user_nickname', 'profile_nickname', 'association', 'link'), 'content_type' => 'html'),
        ),
    );

    /**
     * Moderators configuration
     *
     * @params
     */
    protected $moderators = array(
        array('module' => 'associations', 'method' => 'index', 'is_default' => 1),
        array('module' => 'associations', 'method' => 'edit', 'is_default' => 1),
        array('module' => 'associations', 'method' => 'settings', 'is_default' => 1),
    );

    /**
     * Constructor
     *
     * @return Install object
     */
    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
        $this->CI->load->model('Install_model');
    }

    /**
     *  Menu install
     *
     *  @return void
     */
    public function install_menu()
    {
        $this->CI->load->helper('menu');

        foreach ($this->menu as $gid => $menu_data) {
            $this->menu[$gid]['id'] = linked_install_set_menu($gid, $menu_data['action'], $menu_data['name']);
            linked_install_process_menu_items($this->menu, 'create', $gid, 0, $this->menu[$gid]['items']);
        }

        if (!empty($this->menu_indicators)) {
            $this->CI->load->model('menu/models/Indicators_model');
            foreach ($this->menu_indicators as $data) {
                $this->CI->Indicators_model->save_type(null, $data);
            }
        }
    }

    public function install_menu_lang_update($langs_ids = null)
    {
        if (empty($langs_ids)) {
            return false;
        }
        $langs_file = $this->CI->Install_model->language_file_read('associations', 'menu', $langs_ids);

        if (!$langs_file) {
            log_message('info', 'Empty menu langs data');

            return false;
        }

        $this->CI->load->helper('menu');

        foreach ($this->menu as $gid => $menu_data) {
            linked_install_process_menu_items($this->menu, 'update', $gid, 0, $this->menu[$gid]['items'], $gid, $langs_file);
        }

        // Indicators
        if (!empty($this->menu_indicators)) {
            $langs_file = $this->CI->Install_model->language_file_read('moderation', 'indicators', $langs_ids);
            if (!$langs_file) {
                log_message('info', '(resumes) Empty indicators langs data');

                return false;
            } else {
                $this->CI->load->model('menu/models/Indicators_model');
                $this->CI->Indicators_model->update_langs($this->menu_indicators, $langs_file, $langs_ids);
            }
        }

        return true;
    }

    public function install_menu_lang_export($langs_ids)
    {
        if (empty($langs_ids)) {
            return false;
        }
        $this->CI->load->helper('menu');

        $return = array();
        foreach ($this->menu as $gid => $menu_data) {
            $temp = linked_install_process_menu_items($this->menu, 'export', $gid, 0, $this->menu[$gid]['items'], $gid, $langs_ids);
            $return = array_merge($return, $temp);
        }
        if (!empty($this->menu_indicators)) {
            $this->CI->load->model('menu/models/Indicators_model');
            $indicators_langs = $this->CI->Indicators_model->export_langs($this->menu_indicators, $langs_ids);
        }

        return array('menu' => $return);
    }

    public function deinstall_menu()
    {
        $this->CI->load->helper('menu');
        foreach ($this->menu as $gid => $menu_data) {
            if ($menu_data['action'] == 'create') {
                linked_install_set_menu($gid, 'delete');
            } else {
                linked_install_delete_menu_items($gid, $this->menu[$gid]['items']);
            }
        }
        if (!empty($this->menu_indicators)) {
            $this->CI->load->model('menu/models/Indicators_model');
            foreach ($this->menu_indicators as $data) {
                $this->CI->Indicators_model->delete_type($data['gid']);
            }
        }
    }

    public function install_notifications()
    {
        // add notification
        $this->CI->load->model('Notifications_model');
        $this->CI->load->model('notifications/models/Templates_model');

        foreach ($this->_notifications['templates'] as $tpl) {
            $template_data = array(
                'gid'          => $tpl['gid'],
                'name'         => $tpl['name'],
                'vars'         => serialize($tpl['vars']),
                'content_type' => $tpl['content_type'],
                'date_add'     => date('Y-m-d H:i:s'),
                'date_update'  => date('Y-m-d H:i:s'),
            );
            $tpl_ids[$tpl['gid']] = $this->CI->Templates_model->save_template(null, $template_data);
        }

        foreach ($this->_notifications['notifications'] as $notification) {
            $notification_data = array(
                'gid'                 => $notification['gid'],
                'send_type'           => $notification['send_type'],
                'id_template_default' => $tpl_ids[$notification['gid']],
                'date_add'            => date("Y-m-d H:i:s"),
                'date_update'         => date("Y-m-d H:i:s"),
            );
            $this->CI->Notifications_model->save_notification(null, $notification_data);
        }
    }

    public function install_notifications_lang_update($langs_ids = null)
    {
        if (empty($langs_ids)) {
            return false;
        }
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }
        $this->CI->load->model('Notifications_model');

        $langs_file = $this->CI->Install_model->language_file_read('associations', 'notifications', $langs_ids);

        if (!$langs_file) {
            log_message('info', 'Empty notifications langs data');

            return false;
        }

        $this->CI->Notifications_model->update_langs($this->_notifications, $langs_file, $langs_ids);

        return true;
    }

    public function install_notifications_lang_export($langs_ids = null)
    {
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }
        $this->CI->load->model('Notifications_model');
        $langs = $this->CI->Notifications_model->export_langs($this->_notifications, $langs_ids);

        return array('notifications' => $langs);
    }

    public function deinstall_notifications()
    {
        $this->CI->load->model('Notifications_model');
        $this->CI->load->model('notifications/models/Templates_model');
        foreach ($this->_notifications['templates'] as $tpl) {
            $this->CI->Templates_model->delete_template_by_gid($tpl['gid']);
        }
        foreach ($this->_notifications['notifications'] as $notification) {
            $this->CI->Notifications_model->delete_notification_by_gid($notification['gid']);
        }
    }

    public function install_site_map()
    {
        $this->CI->load->model('Site_map_model');
        $site_map_data = array(
            'module_gid'      => 'associations',
            'model_name'      => 'Associations_model',
            'get_urls_method' => 'getSitemapUrls',
        );
        $this->CI->Site_map_model->set_sitemap_module('associations', $site_map_data);
    }

    /**
     * Install banners links
     */
    public function install_banners()
    {
        $this->CI->load->model("banners/models/Banner_group_model");
        $this->CI->Banner_group_model->set_module("associations", "Associations_model", "bannerAvailablePages");
        $this->add_banners();
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

        $banners_groups = array('banners_group_associations_groups');
        $langs_file = $this->CI->Install_model->language_file_read('associations', 'pages', $langs_ids);
        $this->CI->load->model('banners/models/Banner_group_model');
        $this->CI->Banner_group_model->update_langs($banners_groups, $langs_file, $langs_ids);
    }

    /**
     * Unistall banners links
     */
    public function deinstall_banners()
    {
        $this->CI->load->model("banners/models/Banner_group_model");
        $this->CI->Banner_group_model->delete_module("associations");
        $this->remove_banners();
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
            'gid'           => 'associations_groups',
            'name'          => 'Associations pages',
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
        $this->CI->load->model("Associations_model");
        $pages = $this->CI->Associations_model->bannerAvailablePages();
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
     * Remove banners
     */
    public function remove_banners()
    {
        $this->CI->load->model("banners/models/Banner_group_model");
        $group_id = $this->CI->Banner_group_model->get_group_id_by_gid("associations_groups");
        $this->CI->Banner_group_model->delete($group_id);
    }

    /**
     * Install moderators links
     */
    public function install_moderators()
    {
        //install ausers permissions
        $this->CI->load->model("Moderators_model");
        foreach ((array) $this->moderators as $method_data) {
            $validate_data = array("errors" => array(), "data" => $method_data);
            if (!empty($validate_data["errors"])) {
                continue;
            }
            $this->CI->Moderators_model->save_method(null, $validate_data["data"]);
        }
    }

    /**
     * Import moderators languages
     *
     * @param array $langs_ids
     */
    public function install_moderators_lang_update($langs_ids = null)
    {
        $langs_file = $this->CI->Install_model->language_file_read("associations", "moderators", $langs_ids);
        if (!$langs_file) {
            log_message("info", "Empty moderators langs data");

            return false;
        }
        // install moderators permissions
        $this->CI->load->model("Moderators_model");
        $params["where"]["module"] = "associations";
        $methods = $this->CI->Moderators_model->get_methods_lang_export($params);

        foreach ($methods as $method) {
            if (!empty($langs_file[$method["method"]])) {
                $this->CI->Moderators_model->save_method($method["id"], array(), $langs_file[$method["method"]]);
            }
        }
    }

    /**
     * Export moderators languages
     *
     * @param array $langs_ids
     */
    public function install_moderators_lang_export($langs_ids)
    {
        $this->CI->load->model("Moderators_model");
        $params["where"]["module"] = "associations";
        $methods = $this->CI->Moderators_model->get_methods_lang_export($params, $langs_ids);
        foreach ($methods as $method) {
            $return[$method["method"]] = $method["langs"];
        }

        return array('moderators' => $return);
    }

    /**
     * Uninstall moderators links
     */
    public function deinstall_moderators()
    {
        $this->CI->load->model("Moderators_model");
        $params = array();
        $params["where"]["module"] = "associations";
        $this->CI->Moderators_model->delete_methods($params);
    }

    public function install_uploads()
    {
        // upload config
        $this->CI->load->model('uploads/models/Uploads_config_model');
        $config_data = array(
            'gid'          => 'associations',
            'name'         => 'Associations photo',
            'max_height'   => 5000,
            'max_width'    => 5000,
            'max_size'     => 10000000,
            'name_format'  => 'format',
            'file_formats' => array('jpg', 'jpeg', 'gif', 'png'),
            'default_img'  => '',
            'date_add'     => date('Y-m-d H:i:s'),
        );
        $config_data['file_formats'] = serialize($config_data['file_formats']);
        $config_id = $this->CI->Uploads_config_model->save_config(null, $config_data);
        $wm_data = $this->CI->Uploads_config_model->get_watermark_by_gid('image-wm');
        $wm_id = isset($wm_data["id"]) ? $wm_data["id"] : 0;

        $thumb_data = array(
            'config_id'    => $config_id,
            'prefix'       => 'big',
            'width'        => 150,
            'height'       => 150,
            'effect'       => 'none',
            'watermark_id' => 0,
            'crop_param'   => 'resize',
            'crop_color'   => 'ffffff',
            'date_add'     => date('Y-m-d H:i:s'),
        );
        $this->CI->Uploads_config_model->save_thumb(null, $thumb_data);
    }

    public function deinstall_uploads()
    {
        $this->CI->load->model('uploads/models/Uploads_config_model');
        $config_data = $this->CI->Uploads_config_model->get_config_by_gid('associations');
        if (!empty($config_data['id'])) {
            $this->CI->Uploads_config_model->delete_config($config_data['id']);
        }
    }

    /**
     * Install fields
     */
    public function _prepare_installing()
    {
        $this->CI->load->model("Associations_model");
        foreach ($this->CI->pg_language->languages as $lang_id => $value) {
            $this->CI->Associations_model->langDedicateModuleCallbackAdd($lang_id);
        }
    }

    public function _arbitrary_installing()
    {
        ///// add entries for lang data updates
        foreach ($this->lang_dm_data as $lang_dm_data) {
            $this->CI->pg_language->add_dedicate_modules_entry($lang_dm_data);
        }
        
        $this->CI->pg_seo->set_seo_module('associations', array(
            'module_gid'              => 'associations',
            'model_name'              => 'Associations_model',
            'get_settings_method'     => 'getSeoSettings',
            'get_rewrite_vars_method' => 'requestSeoRewrite',
            'get_sitemap_urls_method' => 'getSitemapXmlUrls',
        ));

        $this->add_demo_content();
    }

    /**
     * Import module languages
     *
     * @param array $langs_ids array languages identifiers
     *
     * @return void
     */
    public function _arbitrary_lang_install($langs_ids = null)
    {
        $langs_file = $this->CI->Install_model->language_file_read('associations', 'arbitrary', $langs_ids);
        if (!$langs_file) {
            log_message('info', 'Empty associations arbitrary langs data');

            return false;
        }
        foreach ($this->_seo_pages as $page) {
            $post_data = array(
                'title'          => $langs_file["seo_tags_{$page}_title"],
                'keyword'        => $langs_file["seo_tags_{$page}_keyword"],
                'description'    => $langs_file["seo_tags_{$page}_description"],
                'header'         => $langs_file["seo_tags_{$page}_header"],
                'og_title'       => $langs_file["seo_tags_{$page}_og_title"],
                'og_type'        => $langs_file["seo_tags_{$page}_og_type"],
                'og_description' => $langs_file["seo_tags_{$page}_og_description"],
            );
            $this->CI->pg_seo->set_settings('user', 'associations', $page, $post_data);
        }
    }

    /**
     * Export module languages
     *
     * @param array $langs_ids languages identifiers
     *
     * @return array
     */
    public function _arbitrary_lang_export($langs_ids = null)
    {
        if (empty($langs_ids)) {
            return false;
        }
        $seo_settings = $this->pg_seo->get_all_settings_from_cache('user', 'associations');
        $lang_ids = array_keys($this->CI->pg_language->languages);
        foreach ($seo_settings as $seo_page) {
            $prefix = 'seo_tags_' . $seo_page['method'];
            foreach ($lang_ids as $lang_id) {
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
        }

        return array('arbitrary' => $arbitrary_return);
    }

    public function _arbitrary_deinstalling()
    {
        foreach ($this->lang_dm_data as $lang_dm_data) {
            $this->CI->pg_language->delete_dedicate_modules_entry(array('where' => $lang_dm_data));
        }
        $this->CI->pg_seo->delete_seo_module('associations');
    }

    public function deinstall_site_map()
    {
        $this->CI->load->model('Site_map_model');
        $this->CI->Site_map_model->delete_sitemap_module('associations');
    }

    /**
     * Install demo content
     *
     * @return void
     */
    public function add_demo_content()
    {
        $this->CI->load->model('Associations_model');
        // Associating languages id with codes
        foreach ($this->CI->pg_language->languages as $l) {
            $lang[$l['code']] = $l['id'];
            if (!empty($l['is_default'])) {
                $default_lang = $l;
            }
        }
        $demo_content = include MODULEPATH . 'associations/install/demo_content.php';

        //add associations
        if (!empty($demo_content['associations'])) {
            foreach ($demo_content['associations'] as $association) {
                foreach ($lang as $key => $id) {
                    if (empty($association['name'][$key])) {
                        $association['name_' . $id] = $association['name']['en'];
                    } else {
                        $association['name_' . $id] = $association['name'][$key];
                    }
                    if (empty($association['view_name'][$key])) {
                        $association['view_name_' . $id] = $association['view_name']['en'];
                    } else {
                        $association['view_name_' . $id] = $association['view_name'][$key];
                    }
                }

                unset($association['name']);
                unset($association['view_name']);
                $this->CI->Associations_model->saveImages(null, $association);
            }
        }

        //add associations users
        if (!empty($demo_content['associations_users'])) {
            foreach ($demo_content['associations_users'] as $assoc_user) {
                foreach ($lang as $key => $id) {
                    if (empty($assoc_user['name'][$key])) {
                        $assoc_user['name_' . $id] = $assoc_user['name']['en'];
                    } else {
                        $assoc_user['name_' . $id] = $assoc_user['name'][$key];
                    }
                }

                unset($assoc_user['name']);
                $this->CI->Associations_model->saveAssociationUser(null, $assoc_user);
            }
        }

        return true;
    }
}
