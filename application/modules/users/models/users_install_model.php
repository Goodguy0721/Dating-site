<?php

namespace Pg\Modules\Users\Models;

use Pg\Libraries\Setup;

/**
 * Users install model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * */
class Users_install_model extends \Model
{
    protected $ci;
    protected $modules_data = [];



    /**
     * Dynamic blocks configuration
     *
     * @var array
     */
    protected $dynamic_blocks = [];

    /**
     * Constructor
     *
     * @return Install object
     */
    public function __construct()
    {
        parent::__construct();
        $this->ci = &get_instance();
        $this->modules_data = Setup::getModuleData('users', Setup::TYPE_MODULES_DATA);

        if (SOCIAL_MODE) {
            $this->dynamic_blocks = include MODULEPATH . 'users/install/dynamic_blocks_social.php';
        }
    }

    public function install_menu()
    {
        $this->ci->load->helper('menu');
        foreach ($this->modules_data['menu'] as $gid => $menu_data) {
            $this->modules_data['menu'][$gid]['id'] = linked_install_set_menu($gid,
                $menu_data["action"], $menu_data["name"]);
            linked_install_process_menu_items($this->modules_data['menu'], 'create', $gid, 0,
                $this->modules_data['menu'][$gid]["items"]);
        }
    }

    public function install_menu_lang_update($langs_ids = null)
    {
        if (empty($langs_ids)) {
            return false;
        }
        $langs_file = $this->ci->Install_model->language_file_read('users',
            'menu', $langs_ids);
        if (!$langs_file) {
            log_message('info', 'Empty menu langs data');

            return false;
        }
        $this->ci->load->model('Menu_model');
        $this->ci->load->helper('menu');

        foreach ($this->modules_data['menu'] as $gid => $menu_data) {
            linked_install_process_menu_items($this->modules_data['menu'], 'update', $gid, 0,
                $this->modules_data['menu'][$gid]["items"], $gid, $langs_file);
        }

        return true;
    }

    public function install_menu_lang_export($langs_ids)
    {
        if (empty($langs_ids)) {
            return false;
        }
        $this->ci->load->model('Menu_model');
        $this->ci->load->helper('menu');
        $return = array();
        foreach ($this->modules_data['menu'] as $gid => $menu_data) {
            $temp   = linked_install_process_menu_items($this->modules_data['menu'], 'export',
                $gid, 0, $this->modules_data['menu'][$gid]["items"], $gid, $langs_ids);
            $return = array_merge($return, $temp);
        }

        return array('menu' => $return);
    }

    public function deinstall_menu()
    {
        $this->ci->load->helper('menu');
        foreach ($this->modules_data['menu'] as $gid => $menu_data) {
            if ($menu_data['action'] == 'create') {
                linked_install_set_menu($gid, 'delete');
            } else {
                linked_install_delete_menu_items($gid,
                    $this->modules_data['menu'][$gid]['items']);
            }
        }
    }

    public function install_network()
    {
        $this->ci->load->model('network/models/Network_events_model');
        foreach ($this->modules_data['network_event_handlers'] as $handler) {
            $this->ci->Network_events_model->add_handler($handler);
        }
    }

    public function deinstall_network()
    {
        $this->ci->load->model('network/models/Network_events_model');
        foreach ($this->modules_data['network_event_handlers'] as $handler) {
            $this->ci->Network_events_model->delete($handler['event']);
        }
    }

    public function install_uploads()
    {
        // upload config
        $this->ci->load->model('uploads/models/Uploads_config_model');
        $config_data                 = array(
            'gid' => 'user-logo',
            'name' => 'User icon',
            'max_height' => 5000,
            'max_width' => 5000,
            'max_size' => 10000000,
            'name_format' => 'generate',
            'file_formats' => array('jpg', 'jpeg', 'gif', 'png'),
            'default_img' => 'default-user-logo.png',
            'date_add' => date('Y-m-d H:i:s'),
        );
        $config_data['file_formats'] = serialize($config_data['file_formats']);
        $config_id                   = $this->ci->Uploads_config_model->save_config(null,
            $config_data);
        $wm_data                     = $this->ci->Uploads_config_model->get_watermark_by_gid('image-wm');
        $wm_id                       = isset($wm_data["id"]) ? $wm_data["id"] : 0;

        $thumb_data = array(
            'config_id' => $config_id,
            'prefix' => 'grand',
            'width' => 960,
            'height' => 720,
            'effect' => 'none',
            'watermark_id' => $wm_id,
            'crop_param' => 'resize',
            'crop_color' => 'ffffff',
            'date_add' => date('Y-m-d H:i:s'),
        );
        $this->ci->Uploads_config_model->save_thumb(null, $thumb_data);

        $thumb_data = array(
            'config_id' => $config_id,
            'prefix' => 'great',
            'width' => 305,
            'height' => 305,
            'effect' => 'none',
            'watermark_id' => $wm_id,
            'crop_param' => 'crop',
            'crop_color' => 'ffffff',
            'date_add' => date('Y-m-d H:i:s'),
        );
        $this->ci->Uploads_config_model->save_thumb(null, $thumb_data);

        $thumb_data = array(
            'config_id' => $config_id,
            'prefix' => 'big',
            'width' => 200,
            'height' => 200,
            'effect' => 'none',
            'watermark_id' => $wm_id,
            'crop_param' => 'crop',
            'crop_color' => 'ffffff',
            'date_add' => date('Y-m-d H:i:s'),
        );
        $this->ci->Uploads_config_model->save_thumb(null, $thumb_data);

        $thumb_data = array(
            'config_id' => $config_id,
            'prefix' => 'middle',
            'width' => 100,
            'height' => 100,
            'effect' => 'none',
            'watermark_id' => 0,
            'crop_param' => 'crop',
            'crop_color' => 'ffffff',
            'date_add' => date('Y-m-d H:i:s'),
        );
        $this->ci->Uploads_config_model->save_thumb(null, $thumb_data);

        $thumb_data = array(
            'config_id' => $config_id,
            'prefix' => 'small',
            'width' => 60,
            'height' => 60,
            'effect' => 'none',
            'watermark_id' => 0,
            'crop_param' => 'crop',
            'crop_color' => 'ffffff',
            'date_add' => date('Y-m-d H:i:s'),
        );
        $this->ci->Uploads_config_model->save_thumb(null, $thumb_data);
    }

    public function install_site_map()
    {
        // Site Map
        $this->ci->load->model('Site_map_model');
        $site_map_data = array(
            'module_gid' => 'users',
            'model_name' => 'Users_model',
            'get_urls_method' => 'get_sitemap_urls',
        );
        $this->ci->Site_map_model->set_sitemap_module('users', $site_map_data);
    }

    public function install_banners()
    {
        // add banners module
        $this->ci->load->model("banners/models/Banner_group_model");
        $this->ci->Banner_group_model->set_module("users", "Users_model",
            "_banner_available_pages");

        $this->add_banners();
    }

    public function add_banners()
    {
        $this->ci->load->model('Users_model');
        $this->ci->load->model('banners/models/Banner_group_model');
        $this->ci->load->model('banners/models/Banner_place_model');

        $group_id = $this->ci->Banner_group_model->get_group_id_by_gid('users_groups');
        // add pages in group
        $pages    = $this->ci->Users_model->_banner_available_pages();
        if ($pages) {
            foreach ($pages as $key => $value) {
                $page_attrs = array(
                    'group_id' => $group_id,
                    'name' => $value['name'],
                    'link' => $value['link'],
                );
                $this->ci->Banner_group_model->add_page($page_attrs);
            }
        }
    }

    public function install_linker()
    {
        // add linker entry
        $this->ci->load->model('linker/models/linker_type_model');
        $this->ci->linker_type_model->create_type('users_contacts');
    }

    public function install_moderation()
    {
        // Moderation
        $this->ci->load->model('moderation/models/Moderation_type_model');
        foreach ($this->modules_data['moderation_types'] as $mtype) {
            $mtype['date_add'] = date("Y-m-d H:i:s");
            $this->ci->Moderation_type_model->save_type(null, $mtype);
        }
    }

    public function install_moderation_lang_update($langs_ids = null)
    {
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }
        $langs_file = $this->ci->Install_model->language_file_read('users',
            'moderation', $langs_ids);
        if (!$langs_file) {
            log_message('info', 'Empty moderation langs data');

            return false;
        }
        $this->ci->load->model('moderation/models/Moderation_type_model');
        $this->ci->Moderation_type_model->update_langs($this->modules_data['moderation_types'],
            $langs_file);
    }

    public function install_moderation_lang_export($langs_ids = null)
    {
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }
        $this->ci->load->model('moderation/models/Moderation_type_model');

        return array('moderation' => $this->ci->Moderation_type_model->export_langs($this->modules_data['moderation_types'],
                $langs_ids));
    }

    public function deinstall_moderation()
    {
        // Moderation
        $this->ci->load->model('moderation/models/Moderation_type_model');
        foreach ($this->modules_data['moderation_types'] as $mtype) {
            $type = $this->ci->Moderation_type_model->get_type_by_name($mtype["name"]);
            $this->ci->Moderation_type_model->delete_type($type['id']);
        }
    }

    /**
     * Moderators module methods
     */
    public function install_moderators()
    {
        // install moderators permissions
        $this->ci->load->model('moderators/models/Moderators_model');

        foreach ($this->modules_data['moderators_methods'] as $method) {
            $this->ci->Moderators_model->save_method(null, $method);
        }
    }

    public function install_moderators_lang_update($langs_ids = null)
    {
        $langs_file                = $this->ci->Install_model->language_file_read('users',
            'moderators', $langs_ids);
        // install moderators permissions
        $this->ci->load->model('moderators/models/Moderators_model');
        $params['where']['module'] = 'users';
        $methods                   = $this->ci->Moderators_model->get_methods_lang_export($params);
        foreach ($methods as $method) {
            if (!empty($langs_file[$method['method']])) {
                $this->ci->Moderators_model->save_method($method['id'], array(),
                    $langs_file[$method['method']]);
            }
        }
    }

    public function install_moderators_lang_export($langs_ids)
    {
        $this->ci->load->model('moderators/models/Moderators_model');
        $params['where']['module'] = 'users';
        $methods                   = $this->ci->Moderators_model->get_methods_lang_export($params,
            $langs_ids);
        foreach ($methods as $method) {
            $return[$method['method']] = $method['langs'];
        }

        return array('moderators' => $return);
    }

    public function deinstall_moderators()
    {
        // delete moderation methods in moderators
        $this->ci->load->model('moderators/models/Moderators_model');
        $params['where']['module'] = 'users';
        $this->ci->Moderators_model->delete_methods($params);
    }

    public function install_notifications()
    {
        // add notification
        $this->ci->load->model('Notifications_model');
        $this->ci->load->model('notifications/models/Templates_model');

        foreach ($this->modules_data['notifications']['templates'] as $tpl) {
            $template_data        = array(
                'gid' => $tpl['gid'],
                'name' => $tpl['name'],
                'vars' => serialize($tpl['vars']),
                'content_type' => $tpl['content_type'],
                'date_add' => date('Y-m-d H:i:s'),
                'date_update' => date('Y-m-d H:i:s'),
            );
            $tpl_ids[$tpl['gid']] = $this->ci->Templates_model->save_template(null,
                $template_data);
        }

        foreach ($this->modules_data['notifications']['notifications'] as $notification) {
            $notification_data = array(
                'gid' => $notification['gid'],
                'send_type' => $notification['send_type'],
                'id_template_default' => $tpl_ids[$notification['gid']],
                'date_add' => date("Y-m-d H:i:s"),
                'date_update' => date("Y-m-d H:i:s"),
            );
            $this->ci->Notifications_model->save_notification(null,
                $notification_data);
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
        $this->ci->load->model('Notifications_model');

        $langs_file = $this->ci->Install_model->language_file_read('users',
            'notifications', $langs_ids);

        if (!$langs_file) {
            log_message('info', 'Empty notifications langs data');

            return false;
        }

        $this->ci->Notifications_model->update_langs($this->modules_data['notifications'],
            $langs_file, $langs_ids);

        return true;
    }

    public function install_notifications_lang_export($langs_ids = null)
    {
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }
        $this->ci->load->model('Notifications_model');
        $langs = $this->ci->Notifications_model->export_langs($this->modules_data['notifications'],
            $langs_ids);

        return array('notifications' => $langs);
    }

    public function deinstall_notifications()
    {
        $this->ci->load->model('Notifications_model');
        $this->ci->load->model('notifications/models/Templates_model');
        foreach ($this->modules_data['notifications']['templates'] as $tpl) {
            $this->ci->Templates_model->delete_template_by_gid($tpl['gid']);
        }
        foreach ($this->modules_data['notifications']['notifications'] as $notification) {
            $this->ci->Notifications_model->delete_notification_by_gid($notification['gid']);
        }
    }

    public function install_social_networking()
    {
        // add social netorking page
        $this->ci->load->model('social_networking/models/Social_networking_pages_model');

        $data = array(
            'like' => array(
                'facebook' => 'on',
                'vkontakte' => 'on',
                'google' => 'on',
            ),
            'share' => array(
                'facebook' => 'on',
                'vkontakte' => 'on',
                'linkedin' => 'on',
                'twitter' => 'on',
            ),
            'comments' => '1',
        );

        $page_data = array(
            'controller' => 'users',
            'method' => 'registration',
            'name' => 'Registration page',
            'data' => serialize($data),
        );
        $this->ci->Social_networking_pages_model->save_page(null, $page_data);
    }

    public function deinstall_social_networking()
    {
        $this->ci->load->model('social_networking/models/Social_networking_pages_model');
        $this->ci->Social_networking_pages_model->delete_pages_by_controller('users');
    }

    public function _arbitrary_installing()
    {
        // SEO
        $seo_data = array(
            'module_gid' => 'users',
            'model_name' => 'Users_model',
            'get_settings_method' => 'get_seo_settings',
            'get_rewrite_vars_method' => 'request_seo_rewrite',
            'get_sitemap_urls_method' => 'get_sitemap_xml_urls',
        );
        $this->ci->pg_seo->set_seo_module('users', $seo_data);

        $this->ci->load->model('Users_model');
        $this->ci->Users_model->update_age();
        $this->ci->Users_model->update_profile_completion();

        $this->ci->load->model('users/models/Users_delete_callbacks_model');
        $this->ci->Users_delete_callbacks_model->add_callback('users',
            'Users_model', 'callback_user_delete', '', 'users_delete');
        $this->ci->Users_delete_callbacks_model->add_callback('users',
            'Users_model', 'callback_user_delete', '', 'users_uploads');

        $this->addDemoContent();
        $this->adddLangCallback();
        $this->addUserTypes();

        return;
    }

     /**
     * Install fields of dedicated languages
     *
     * @return void
     */
    public function _prepare_installing()
    {
        $this->ci->load->model("users/models/Groups_model");
        foreach ($this->ci->pg_language->languages as $lang_id => $value) {
            $this->ci->Groups_model->langDedicateModuleCallbackAdd($lang_id);
        }
    }

    private function adddLangCallback()
    {
        $lang_dm_data = array(
            'module' => 'users',
            'model' => 'Users_model',
            'method_add' => 'lang_dedicate_module_callback_add',
            'method_delete' => 'lang_dedicate_module_callback_delete',
        );
        $this->ci->pg_language->add_dedicate_modules_entry($lang_dm_data);
    }

    public function addDemoContent()
    {
        if (SOCIAL_MODE) {
            $demo_content = include MODULEPATH . 'users/install/demo_content_social.php';
        } else {
            $demo_content = include MODULEPATH . 'users/install/demo_content_dating.php';
        }
        // Associating languages id with codes
        foreach ($this->ci->pg_language->languages as $l) {
            $lang[$l['code']] = $l['id'];
            if (!empty($l['is_default'])) {
                $default_lang = $l;
            }
        }
        // Users
        $this->ci->load->model('Users_model');
        foreach ($demo_content['users'] as $user) {
            // Replace language code with ID
            if (empty($lang[$user['lang_code']])) {
                $user['lang_id'] = $default_lang['id'];
            } else {
                $user['lang_id'] = $lang[$user['lang_code']];
            }
            unset($user['lang_code']);
            $this->ci->Users_model->save_user(null, $user);
        }
        
        if (empty($demo_content['groups'])) {
            return false;
        } else {

            if (!empty($demo_content['groups'])) {
                $this->ci->load->model('users/models/Groups_model');
                foreach ($demo_content['groups'] as $group) {
                    foreach ($lang as $key => $id) {
                        if (empty($group['name'][$key])) {
                            $group['name_' . $id] = $group['name']['en'];
                        } else {
                            $group['name_' . $id] = $group['name'][$key];
                        }
                        if (empty($group['description'][$key])) {
                            $group['description_' . $id] = $group['description']['en'];
                        } else {
                            $group['description_' . $id] = $group['description'][$key];
                        }
                    }

                    unset($group['name']);
                    unset($group['description']);
                    $this->ci->Groups_model->saveGroup(null, $group);
                }
            }
           
        }

        return true;
    }

    protected function addUserTypes()
    {
        $this->ci->load->model('users/models/Users_types_model');
        $this->ci->Users_types_model->addTypes($this->modules_data['user_types']);
    }

    public function _arbitrary_lang_install($langs_ids = null)
    {
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }
        $langs_file = $this->ci->Install_model->language_file_read('users',
            'arbitrary');
        if (!$langs_file) {
            log_message('info', 'Empty arbitrary langs data');

            return false;
        }

        $post_data = array(
            "title" => $langs_file["seo_tags_account_title"],
            "keyword" => $langs_file["seo_tags_account_keyword"],
            "description" => $langs_file["seo_tags_account_description"],
            "header" => $langs_file["seo_tags_account_header"],
            "og_title" => $langs_file["seo_tags_account_og_title"],
            "og_type" => $langs_file["seo_tags_account_og_type"],
            "og_description" => $langs_file["seo_tags_account_og_description"],
            "priority" => 0.8,
        );
        $this->ci->pg_seo->set_settings("user", "users", "account", $post_data);

        $post_data = array(
            "title" => $langs_file["seo_tags_account_delete_title"],
            "keyword" => $langs_file["seo_tags_account_delete_keyword"],
            "description" => $langs_file["seo_tags_account_delete_description"],
            "header" => $langs_file["seo_tags_account_delete_header"],
            "og_title" => $langs_file["seo_tags_account_delete_og_title"],
            "og_type" => $langs_file["seo_tags_account_delete_og_type"],
            "og_description" => $langs_file["seo_tags_account_delete_og_description"],
            "priority" => 0.8,
        );
        $this->ci->pg_seo->set_settings("user", "users", "account_delete",
            $post_data);

        $post_data = array(
            "title" => $langs_file["seo_tags_settings_title"],
            "keyword" => $langs_file["seo_tags_settings_keyword"],
            "description" => $langs_file["seo_tags_settings_description"],
            "header" => $langs_file["seo_tags_settings_header"],
            "og_title" => $langs_file["seo_tags_settings_og_title"],
            "og_type" => $langs_file["seo_tags_settings_og_type"],
            "og_description" => $langs_file["seo_tags_settings_og_description"],
            "priority" => 0.8,
        );
        $this->ci->pg_seo->set_settings("user", "users", "settings", $post_data);

        $post_data = array(
            "title" => $langs_file["seo_tags_login_form_title"],
            "keyword" => $langs_file["seo_tags_login_form_keyword"],
            "description" => $langs_file["seo_tags_login_form_description"],
            "header" => $langs_file["seo_tags_login_form_header"],
            "og_title" => $langs_file["seo_tags_login_form_og_title"],
            "og_type" => $langs_file["seo_tags_login_form_og_type"],
            "og_description" => $langs_file["seo_tags_login_form_og_description"],
            "priority" => 0.8,
        );
        $this->ci->pg_seo->set_settings("user", "users", "login_form",
            $post_data);

        $post_data = array(
            "title" => $langs_file["seo_tags_restore_title"],
            "keyword" => $langs_file["seo_tags_restore_keyword"],
            "description" => $langs_file["seo_tags_restore_description"],
            "header" => $langs_file["seo_tags_restore_header"],
            "og_title" => $langs_file["seo_tags_restore_og_title"],
            "og_type" => $langs_file["seo_tags_restore_og_type"],
            "og_description" => $langs_file["seo_tags_restore_og_description"],
            "priority" => 0.8,
        );
        $this->ci->pg_seo->set_settings("user", "users", "restore", $post_data);

        $post_data = array(
            "title" => $langs_file["seo_tags_profile_title"],
            "keyword" => $langs_file["seo_tags_profile_keyword"],
            "description" => $langs_file["seo_tags_profile_description"],
            "header" => $langs_file["seo_tags_profile_header"],
            "og_title" => $langs_file["seo_tags_profile_og_title"],
            "og_type" => $langs_file["seo_tags_profile_og_type"],
            "og_description" => $langs_file["seo_tags_profile_og_description"],
            "priority" => 0.8,
        );
        $this->ci->pg_seo->set_settings("user", "users", "profile", $post_data);

        $post_data = array(
            "title" => $langs_file["seo_tags_view_title"],
            "keyword" => $langs_file["seo_tags_view_keyword"],
            "description" => $langs_file["seo_tags_view_description"],
            "header" => $langs_file["seo_tags_view_header"],
            "og_title" => $langs_file["seo_tags_view_og_title"],
            "og_type" => $langs_file["seo_tags_view_og_type"],
            "og_description" => $langs_file["seo_tags_view_og_description"],
            "priority" => 0.8,
        );
        $this->ci->pg_seo->set_settings("user", "users", "view", $post_data);

        $post_data = array(
            "title" => $langs_file["seo_tags_registration_title"],
            "keyword" => $langs_file["seo_tags_registration_keyword"],
            "description" => $langs_file["seo_tags_registration_description"],
            "header" => $langs_file["seo_tags_registration_header"],
            "og_title" => $langs_file["seo_tags_registration_og_title"],
            "og_type" => $langs_file["seo_tags_registration_og_type"],
            "og_description" => $langs_file["seo_tags_registration_og_description"],
            "priority" => 0.8,
        );
        $this->ci->pg_seo->set_settings("user", "users", "registration",
            $post_data);

        $post_data = array(
            "title" => $langs_file["seo_tags_confirm_title"],
            "keyword" => $langs_file["seo_tags_confirm_keyword"],
            "description" => $langs_file["seo_tags_confirm_description"],
            "header" => $langs_file["seo_tags_confirm_header"],
            "og_title" => $langs_file["seo_tags_confirm_og_title"],
            "og_type" => $langs_file["seo_tags_confirm_og_type"],
            "og_description" => $langs_file["seo_tags_confirm_og_description"],
            "priority" => 0.8,
        );
        $this->ci->pg_seo->set_settings("user", "users", "confirm", $post_data);

        $post_data = array(
            "title" => $langs_file["seo_tags_search_title"],
            "keyword" => $langs_file["seo_tags_search_keyword"],
            "description" => $langs_file["seo_tags_search_description"],
            "header" => $langs_file["seo_tags_search_header"],
            "og_title" => $langs_file["seo_tags_search_og_title"],
            "og_type" => $langs_file["seo_tags_search_og_type"],
            "og_description" => $langs_file["seo_tags_search_og_description"],
            "priority" => 0.8,
        );
        $this->ci->pg_seo->set_settings("user", "users", "search", $post_data);

        $post_data = array(
            "title" => $langs_file["seo_tags_my_visits_title"],
            "keyword" => $langs_file["seo_tags_my_visits_keyword"],
            "description" => $langs_file["seo_tags_my_visits_description"],
            "header" => $langs_file["seo_tags_my_visits_header"],
            "og_title" => $langs_file["seo_tags_my_visits_og_title"],
            "og_type" => $langs_file["seo_tags_my_visits_og_type"],
            "og_description" => $langs_file["seo_tags_my_visits_og_description"],
            "priority" => 0.8,
        );
        $this->ci->pg_seo->set_settings("user", "users", "my_visits", $post_data);

        $post_data = array(
            "title" => $langs_file["seo_tags_my_guests_title"],
            "keyword" => $langs_file["seo_tags_my_guests_keyword"],
            "description" => $langs_file["seo_tags_my_guests_description"],
            "header" => $langs_file["seo_tags_my_guests_header"],
            "og_title" => $langs_file["seo_tags_my_guests_og_title"],
            "og_type" => $langs_file["seo_tags_my_guests_og_type"],
            "og_description" => $langs_file["seo_tags_my_guests_og_description"],
            "priority" => 0.8,
        );
        $this->ci->pg_seo->set_settings("user", "users", "my_guests", $post_data);
    }

    public function _arbitrary_lang_export($langs_ids = null)
    {
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }

        $settings = $this->ci->pg_seo->get_settings("user", "users", "account", $langs_ids);
        $arbitrary_return["seo_tags_account_title"]          = $settings["title"];
        $arbitrary_return["seo_tags_account_keyword"]        = $settings["keyword"];
        $arbitrary_return["seo_tags_account_description"]    = $settings["description"];
        $arbitrary_return["seo_tags_account_header"]         = $settings["header"];
        $arbitrary_return["seo_tags_account_og_title"]       = $settings["og_title"];
        $arbitrary_return["seo_tags_account_og_type"]        = $settings["og_type"];
        $arbitrary_return["seo_tags_account_og_description"] = $settings["og_description"];

        $settings                                                   = $this->ci->pg_seo->get_settings("user",
            "users", "account_delete", $langs_ids);
        $arbitrary_return["seo_tags_account_delete_title"]          = $settings["title"];
        $arbitrary_return["seo_tags_account_delete_keyword"]        = $settings["keyword"];
        $arbitrary_return["seo_tags_account_delete_description"]    = $settings["description"];
        $arbitrary_return["seo_tags_account_delete_header"]         = $settings["header"];
        $arbitrary_return["seo_tags_account_delete_og_title"]       = $settings["og_title"];
        $arbitrary_return["seo_tags_account_delete_og_type"]        = $settings["og_type"];
        $arbitrary_return["seo_tags_account_delete_og_description"] = $settings["og_description"];

        $settings                                             = $this->ci->pg_seo->get_settings("user",
            "users", "settings", $langs_ids);
        $arbitrary_return["seo_tags_settings_title"]          = $settings["title"];
        $arbitrary_return["seo_tags_settings_keyword"]        = $settings["keyword"];
        $arbitrary_return["seo_tags_settings_description"]    = $settings["description"];
        $arbitrary_return["seo_tags_settings_header"]         = $settings["header"];
        $arbitrary_return["seo_tags_settings_og_title"]       = $settings["og_title"];
        $arbitrary_return["seo_tags_settings_og_type"]        = $settings["og_type"];
        $arbitrary_return["seo_tags_settings_og_description"] = $settings["og_description"];

        $settings                                               = $this->ci->pg_seo->get_settings("user",
            "users", "login_form", $langs_ids);
        $arbitrary_return["seo_tags_login_form_title"]          = $settings["title"];
        $arbitrary_return["seo_tags_login_form_keyword"]        = $settings["keyword"];
        $arbitrary_return["seo_tags_login_form_description"]    = $settings["description"];
        $arbitrary_return["seo_tags_login_form_header"]         = $settings["header"];
        $arbitrary_return["seo_tags_login_form_og_title"]       = $settings["og_title"];
        $arbitrary_return["seo_tags_login_form_og_type"]        = $settings["og_type"];
        $arbitrary_return["seo_tags_login_form_og_description"] = $settings["og_description"];

        $settings                                            = $this->ci->pg_seo->get_settings("user",
            "users", "restore", $langs_ids);
        $arbitrary_return["seo_tags_restore_title"]          = $settings["title"];
        $arbitrary_return["seo_tags_restore_keyword"]        = $settings["keyword"];
        $arbitrary_return["seo_tags_restore_description"]    = $settings["description"];
        $arbitrary_return["seo_tags_restore_header"]         = $settings["header"];
        $arbitrary_return["seo_tags_restore_og_title"]       = $settings["og_title"];
        $arbitrary_return["seo_tags_restore_og_type"]        = $settings["og_type"];
        $arbitrary_return["seo_tags_restore_og_description"] = $settings["og_description"];

        $settings                                            = $this->ci->pg_seo->get_settings("user",
            "users", "profile", $langs_ids);
        $arbitrary_return["seo_tags_profile_title"]          = $settings["title"];
        $arbitrary_return["seo_tags_profile_keyword"]        = $settings["keyword"];
        $arbitrary_return["seo_tags_profile_description"]    = $settings["description"];
        $arbitrary_return["seo_tags_profile_header"]         = $settings["header"];
        $arbitrary_return["seo_tags_profile_og_title"]       = $settings["og_title"];
        $arbitrary_return["seo_tags_profile_og_type"]        = $settings["og_type"];
        $arbitrary_return["seo_tags_profile_og_description"] = $settings["og_description"];

        $settings                                         = $this->ci->pg_seo->get_settings("user",
            "users", "view", $langs_ids);
        $arbitrary_return["seo_tags_view_title"]          = $settings["title"];
        $arbitrary_return["seo_tags_view_keyword"]        = $settings["keyword"];
        $arbitrary_return["seo_tags_view_description"]    = $settings["description"];
        $arbitrary_return["seo_tags_view_header"]         = $settings["header"];
        $arbitrary_return["seo_tags_view_og_title"]       = $settings["og_title"];
        $arbitrary_return["seo_tags_view_og_type"]        = $settings["og_type"];
        $arbitrary_return["seo_tags_view_og_description"] = $settings["og_description"];

        $settings                                                 = $this->ci->pg_seo->get_settings("user",
            "users", "registration", $langs_ids);
        $arbitrary_return["seo_tags_registration_title"]          = $settings["title"];
        $arbitrary_return["seo_tags_registration_keyword"]        = $settings["keyword"];
        $arbitrary_return["seo_tags_registration_description"]    = $settings["description"];
        $arbitrary_return["seo_tags_registration_header"]         = $settings["header"];
        $arbitrary_return["seo_tags_registration_og_title"]       = $settings["og_title"];
        $arbitrary_return["seo_tags_registration_og_type"]        = $settings["og_type"];
        $arbitrary_return["seo_tags_registration_og_description"] = $settings["og_description"];

        $settings                                            = $this->ci->pg_seo->get_settings("user",
            "users", "confirm", $langs_ids);
        $arbitrary_return["seo_tags_confirm_title"]          = $settings["title"];
        $arbitrary_return["seo_tags_confirm_keyword"]        = $settings["keyword"];
        $arbitrary_return["seo_tags_confirm_description"]    = $settings["description"];
        $arbitrary_return["seo_tags_confirm_header"]         = $settings["header"];
        $arbitrary_return["seo_tags_confirm_og_title"]       = $settings["og_title"];
        $arbitrary_return["seo_tags_confirm_og_type"]        = $settings["og_type"];
        $arbitrary_return["seo_tags_confirm_og_description"] = $settings["og_description"];

        $settings                                           = $this->ci->pg_seo->get_settings("user",
            "users", "search", $langs_ids);
        $arbitrary_return["seo_tags_search_title"]          = $settings["title"];
        $arbitrary_return["seo_tags_search_keyword"]        = $settings["keyword"];
        $arbitrary_return["seo_tags_search_description"]    = $settings["description"];
        $arbitrary_return["seo_tags_search_header"]         = $settings["header"];
        $arbitrary_return["seo_tags_search_og_title"]       = $settings["og_title"];
        $arbitrary_return["seo_tags_search_og_type"]        = $settings["og_type"];
        $arbitrary_return["seo_tags_search_og_description"] = $settings["og_description"];

        $settings                                              = $this->ci->pg_seo->get_settings("user",
            "users", "my_visits", $langs_ids);
        $arbitrary_return["seo_tags_my_visits_title"]          = $settings["title"];
        $arbitrary_return["seo_tags_my_visits_keyword"]        = $settings["keyword"];
        $arbitrary_return["seo_tags_my_visits_description"]    = $settings["description"];
        $arbitrary_return["seo_tags_my_visits_header"]         = $settings["header"];
        $arbitrary_return["seo_tags_my_visits_og_title"]       = $settings["og_title"];
        $arbitrary_return["seo_tags_my_visits_og_type"]        = $settings["og_type"];
        $arbitrary_return["seo_tags_my_visits_og_description"] = $settings["og_description"];

        $settings                                              = $this->ci->pg_seo->get_settings("user",
            "users", "my_guests", $langs_ids);
        $arbitrary_return["seo_tags_my_guests_title"]          = $settings["title"];
        $arbitrary_return["seo_tags_my_guests_keyword"]        = $settings["keyword"];
        $arbitrary_return["seo_tags_my_guests_description"]    = $settings["description"];
        $arbitrary_return["seo_tags_my_guests_header"]         = $settings["header"];
        $arbitrary_return["seo_tags_my_guests_og_title"]       = $settings["og_title"];
        $arbitrary_return["seo_tags_my_guests_og_type"]        = $settings["og_type"];
        $arbitrary_return["seo_tags_my_guests_og_description"] = $settings["og_description"];

        return array('arbitrary' => $arbitrary_return);
    }

    public function deinstall_uploads()
    {
        $this->ci->load->model('uploads/models/Uploads_config_model');
        $config_data = $this->ci->Uploads_config_model->get_config_by_gid('user-logo');
        if (!empty($config_data['id'])) {
            $this->ci->Uploads_config_model->delete_config($config_data['id']);
        }
    }

    public function deinstall_site_map()
    {
        $this->ci->load->model('Site_map_model');
        $this->ci->Site_map_model->delete_sitemap_module('users');
    }

    public function deinstall_banners()
    {
        // delete banners module
        $this->ci->load->model("banners/models/Banner_group_model");
        $this->ci->Banner_group_model->delete_module("users");
        $this->remove_banners();
    }

    public function remove_banners()
    {
        $this->ci->load->model('banners/models/Banner_group_model');
        $group_id = $this->ci->Banner_group_model->get_group_id_by_gid('users_groups');
        $this->ci->Banner_group_model->delete($group_id);
    }

    public function deinstall_linker()
    {
        $this->ci->load->model('linker/models/linker_type_model');
        $this->ci->linker_type_model->delete_type('users_contacts');
    }

    public function _arbitrary_deinstalling()
    {
        $this->ci->pg_seo->delete_seo_module('users');
        $this->ci->load->model('users/models/Users_delete_callbacks_model');
        $this->ci->Users_delete_callbacks_model->delete_callbacks_by_module('users_delete');
    }

    // looks like not in use
    public function get_menu_lang_delete($langs, $menu_gid, $item_gid)
    {
        $lang_data = array();
        foreach ($this->ci->pg_language->languages as $lang) {
            $lang_data[$lang["id"]] = $langs[$lang["code"]][$menu_gid][$item_gid];
        }

        return $lang_data;
    }

    public function install_field_editor()
    {
        $this->ci->load->model('Users_model');
        $this->ci->load->model('Field_editor_model');
        $this->ci->Field_editor_model->initialize('users');

        if (SOCIAL_MODE) {
            include MODULEPATH . 'users/install/user_fields_data_social.php';
        } else {
            include MODULEPATH . 'users/install/user_fields_data_dating.php';
        }

        $this->ci->Field_editor_model->import_type_structure('users',
            $fe_sections, $fe_fields, $fe_forms);

        $users_id = $this->ci->Users_model->get_all_users_id();
        foreach ($users_id as $uid) {
            $this->ci->Field_editor_model->update_fulltext_field($uid);
        }
    }

    public function install_field_editor_lang_update()
    {
        $langs_file = $this->ci->Install_model->language_file_read('users',
            'field_editor');

        if (!$langs_file) {
            log_message('info', 'Empty dynamic_blocks langs data');

            return false;
        }

        $this->ci->load->model('Field_editor_model');
        $this->ci->Field_editor_model->initialize('users');

        if (SOCIAL_MODE) {
            include MODULEPATH . 'users/install/user_fields_data_social.php';
        } else {
            include MODULEPATH . 'users/install/user_fields_data_dating.php';
        }

        $this->ci->Field_editor_model->update_sections_langs($fe_sections,
            $langs_file);
        $this->ci->Field_editor_model->update_fields_langs('users', $fe_fields,
            $langs_file);

        return true;
    }

    public function install_field_editor_lang_export($langs_ids = null)
    {
        $this->ci->load->model('Field_editor_model');
        $this->ci->Field_editor_model->initialize('users');
        list($fe_sections, $fe_fields, $fe_forms) = $this->ci->Field_editor_model->export_type_structure('users',
            'application/modules/users/install/user_fields_data.php');
        $sections = $this->ci->Field_editor_model->export_sections_langs($fe_sections,
            $langs_ids);
        $fields   = $this->ci->Field_editor_model->export_fields_langs('users',
            $fe_fields, $langs_ids);

        return array('field_editor' => array_merge($sections, $fields));
    }

    public function deinstall_field_editor()
    {
        $this->ci->load->model('Field_editor_model');
        $this->ci->load->model('field_editor/models/Field_editor_forms_model');

        if (SOCIAL_MODE) {
            include MODULEPATH . 'users/install/user_fields_data_social.php';
        } else {
            include MODULEPATH . 'users/install/user_fields_data_dating.php';
        }

        foreach ($fe_fields as $field) {
            $this->ci->Field_editor_model->delete_field_by_gid($field['data']['gid']);
        }
        $this->ci->Field_editor_model->initialize('users');
        foreach ($fe_sections as $section) {
            $this->ci->Field_editor_model->delete_section_by_gid($section['data']['gid']);
        }
        foreach ($fe_forms as $form) {
            $this->ci->Field_editor_forms_model->delete_form_by_gid($form['data']['gid']);
        }

        return;
    }

    public function install_cronjob()
    {
        $this->ci->load->model('Cronjob_model');
        foreach ($this->modules_data['cron_jobs'] as $cron) {
            $this->ci->Cronjob_model->save_cron(null, $cron);
        }
    }

    public function deinstall_cronjob()
    {
        $this->ci->load->model('Cronjob_model');
        $cron_data                    = array();
        $cron_data["where"]["module"] = "users";
        $this->ci->Cronjob_model->delete_cron_by_param($cron_data);
    }

    /**
     * Install data of services module
     *
     * @return void
     */
    public function install_services()
    {
        $this->ci->load->model("Services_model");

        foreach ($this->modules_data['services'] as $services) {
            $validate_data = $this->ci->Services_model->validate_template(null,
                $services['template']);
            if (!empty($validate_data['errors'])) {
                continue;
            }
            $id_tpl = $this->ci->Services_model->save_template(null,
                $validate_data['data']);

            foreach ($services['services'] as $service) {
                $service['id_template'] = $id_tpl;
                $service['data_admin']  = $service['data_admin'];

                if (SOCIAL_MODE && $service['gid'] == 'user_activate_in_search') {
                    $service['status'] = 0;
                }

                $validate_data = $this->ci->Services_model->validate_service(null,
                    $service);
                if (!empty($validate_data['errors'])) {
                    continue;
                }
                $this->ci->Services_model->save_service(null,
                    $validate_data['data']);
            }
        }
    }

    /**
     * Import data of services module depended on language
     *
     * @param array $langs_ids languages identifiers
     *
     * @return boolean
     */
    public function install_services_lang_update($langs_ids = null)
    {
        $langs_file = $this->ci->Install_model->language_file_read('users',
            'services', $langs_ids);
        if (!$langs_file) {
            log_message('info', 'Empty services langs data');

            return false;
        }
        $this->ci->load->model('Services_model');
        $this->ci->Services_model->update_langs($this->modules_data['lang_services'],
            $langs_file);

        return true;
    }

    /**
     * Export data of services module depended on language
     *
     * @param array $langs_ids languages identifiers
     *
     * @return array
     */
    public function install_services_lang_export($langs_ids = null)
    {
        $this->ci->load->model('Services_model');

        return array('services' => $this->ci->Services_model->export_langs($this->modules_data['lang_services'],
                $langs_ids));
    }

    /**
     * Uninstall data of services module
     *
     * @return void
     */
    public function deinstall_services()
    {
        $this->ci->load->model("Services_model");

        foreach ($this->modules_data['services'] as $services) {
            $this->ci->Services_model->delete_template_by_gid($services['template']['gid']);
            foreach ($services['services'] as $service) {
                $this->ci->Services_model->delete_service_by_gid($service['gid']);
            }
        }
    }
    /**
     * Install memberships data
     *
     * @return void
     */
    /* public function install_memberships () {
      $this->ci->load->model("Services_model");

      foreach($this->memberships as $membership){
      foreach($membership['services'] as $service){
      $service['id_template'] = $id_tpl;
      $service['data_admin'] = $service['data_admin'];
      $validate_data = $this->ci->Services_model->validate_service(null, $service);
      if(!empty($validate_data['errors'])) continue;
      $this->ci->Services_model->save_service(null, $validate_data['data']);
      }
      }
      } */

    /**
     * Import memberships languages data
     *
     * @param array $langs_ids languages identifiers
     *
     * @return boolean
     */
    /* public function install_memberships_lang_update($langs_ids = null) {
      $langs_file = $this->ci->Install_model->language_file_read('users', 'memberships', $langs_ids);
      if(!$langs_file) {
      log_message('info', 'Empty memberships langs data');
      return false;
      }
      $this->ci->load->model('Services_model');
      $this->ci->Services_model->update_langs($this->_lang_memberships, $langs_file);
      return true;
      } */

    /**
     * Export memberships languages data
     *
     * @param array $langs_ids languages identifiers
     *
     * @return array
     */
    /* public function install_memberships_lang_export($langs_ids = null) {
      $this->ci->load->model('Services_model');
      return array('services' => $this->ci->Services_model->export_langs($this->_lang_memberships, $langs_ids));
      } */

    /**
     * Uninstall memberships data
     *
     * @return void
     */
    /* public function deinstall_memberships () {
      $this->ci->load->model("Services_model");

      foreach($this->memberships as $membership){
      foreach($membership['services'] as $service){
      $this->ci->Services_model->delete_service_by_gid($service['gid']);
      }
      }
      } */

    /**
     * Install geomap links
     */
    public function install_geomap()
    {
        // add geomap settings
        $this->ci->load->model('geomap/models/Geomap_settings_model');
        foreach ($this->modules_data['geomap'] as $geomap) {
            $validate_data = $this->ci->Geomap_settings_model->validate_settings($geomap['map_settings']);
            if (!empty($validate_data['errors'])) {
                continue;
            }
            $this->ci->Geomap_settings_model->save_settings($geomap['settings']['map_gid'],
                $geomap['settings']['id_user'],
                $geomap['settings']['id_object'], $geomap['settings']['gid'],
                $validate_data['data']);
        }
    }

    /**
     * Install languages
     *
     * @param array $langs_ids
     */
    public function install_geomap_lang_update($langs_ids = null)
    {
        if (empty($langs_ids)) {
            return false;
        }

        $langs_file = $this->ci->Install_model->language_file_read('users',
            'geomap', $langs_ids);
        if (!$langs_file) {
            log_message('info', 'Empty geomap langs data');

            return false;
        }

        $this->ci->load->model('geomap/models/Geomap_settings_model');

        $gids = array();
        foreach ($this->modules_data['geomap'] as $geomap) {
            $gids[$geomap['settings']['gid']] = 'map_' . $geomap['settings']['gid'];
        }
        $this->ci->Geomap_settings_model->update_lang($gids, $langs_file,
            $langs_ids);
    }

    /**
     * Import languages
     *
     * @param array $langs_ids
     */
    public function install_geomap_lang_export($langs_ids = null)
    {
        $this->ci->load->model('geomap/models/Geomap_settings_model');

        $gids = array();
        foreach ($this->modules_data['geomap'] as $geomap) {
            $gids[$geomap['settings']['gid']] = 'map_' . $geomap['settings']['gid'];
        }
        $langs = $this->ci->Geomap_settings_model->export_lang($gids, $langs_ids);

        return array('geomap' => $langs);
    }

    /**
     * Uninstall geomap links
     */
    public function deinstall_geomap()
    {
        $this->ci->load->model("geomap/models/Geomap_settings_model");
        foreach ($this->modules_data['geomap'] as $geomap) {
            $this->ci->Geomap_settings_model->delete_settings($geomap['settings']['map_gid'],
                $geomap['settings']['id_user'],
                $geomap['settings']['id_object'], $geomap['settings']['gid']);
        }
    }

    public function install_dynamic_blocks()
    {
        $this->ci->load->model('Dynamic_blocks_model');
        $this->ci->Dynamic_blocks_model->installBatch($this->dynamic_blocks);
    }

    public function install_dynamic_blocks_lang_update($langs_ids = null)
    {
        $this->ci->load->model('Dynamic_blocks_model');

        return $this->ci->Dynamic_blocks_model->updateLangsByModuleBlocks($this->dynamic_blocks,
                $langs_ids);
    }

    public function install_dynamic_blocks_lang_export($langs_ids = null)
    {
        $this->ci->load->model('Dynamic_blocks_model');

        return array(
            'dynamic_blocks' => $this->ci->Dynamic_blocks_model->export_langs($this->dynamic_blocks,
                $langs_ids),
        );
    }

    public function deinstall_dynamic_blocks()
    {
        $this->ci->load->model('Dynamic_blocks_model');
        foreach ($this->dynamic_blocks as $block) {
            $this->ci->Dynamic_blocks_model->delete_block_by_gid($block['gid']);
        }
    }

    public function install_comments()
    {
        $this->ci->load->model('comments/models/Comments_types_model');
        $comment_type = array(
            'gid' => 'user_avatar',
            'module' => 'users',
            'model' => 'Users_model',
            'method_count' => 'comments_count_callback',
            'method_object' => 'comments_object_callback',
        );
        $this->ci->Comments_types_model->add_comments_type($comment_type);
    }

    public function install_comments_lang_update($langs_ids = null)
    {
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }
        $langs_file = $this->ci->Install_model->language_file_read('users',
            'comments', $langs_ids);

        if (!$langs_file) {
            log_message('info', 'Empty moderation langs data');

            return false;
        }
        $this->ci->load->model('comments/models/Comments_types_model');
        $this->ci->Comments_types_model->update_langs(array('user_avatar'),
            $langs_file);
    }

    public function install_comments_lang_export($langs_ids = null)
    {
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }
        $this->ci->load->model('comments/models/Comments_types_model');

        return array('comments' => $this->ci->Comments_types_model->export_langs(array(
                'user_avatar'), $langs_ids));
    }

    public function deinstall_comments()
    {
        $this->ci->load->model('comments/models/Comments_types_model');
        $this->ci->Comments_types_model->delete_comments_type('user_avatar');
    }

    /**
     * Install users data to ratings module
     *
     * @return void
     */
    public function install_ratings()
    {
        $this->ci->load->model("Users_model");

        // add ratings type
        $this->ci->load->model("ratings/models/Ratings_type_model");

        $this->ci->Users_model->install_ratings_fields((array) $this->modules_data['ratings']["ratings_fields"]);

        foreach ((array) $this->modules_data['ratings']["ratings"] as $rating_data) {
            $validate_data = $this->ci->Ratings_type_model->validate_type(null,
                $rating_data);
            if (!empty($validate_data["errors"])) {
                continue;
            }
            $this->ci->Ratings_type_model->save_type(null,
                $validate_data["data"]);
        }
    }

    /**
     * Import users languages to ratings module
     *
     * @param array $langs_ids languages identifiers
     *
     * @return boolean
     */
    public function install_ratings_lang_update($langs_ids = null)
    {
        if (empty($langs_ids)) {
            return false;
        }
        $this->ci->load->model("Ratings_model");

        $langs_file = $this->ci->Install_model->language_file_read("users",
            "ratings", $langs_ids);
        if (!$langs_file) {
            log_message("info", "Empty ratings langs data");

            return false;
        }

        foreach ((array) $this->modules_data['ratings']["ratings"] as $rating_data) {
            $this->ci->Ratings_model->update_langs($rating_data, $langs_file,
                $langs_ids);
        }

        foreach ($langs_ids as $lang_id) {
            foreach ((array) $this->modules_data['ratings']["rate_types"] as $type_gid => $type_data) {
                $types_data = array();
                foreach ($type_data as $rate_type => $votes) {
                    $votes_data = array();
                    foreach ($votes as $vote) {
                        $votes_data[$vote] = isset($langs_file[$type_gid . '_' . $rate_type . "_votes_" . $vote][$lang_id])
                                ?
                            $langs_file[$type_gid . '_' . $rate_type . "_votes_" . $vote][$lang_id]
                                : $vote;
                    }
                    $types_data[$rate_type] = array(
                        "header" => $langs_file[$type_gid . '_' . $rate_type . "_header"][$lang_id],
                        "votes" => $votes_data,
                    );
                }
                $this->ci->Ratings_model->add_rate_type($type_gid, $types_data,
                    $lang_id);
            }
        }

        return true;
    }

    /**
     * Export users languages from ratings module
     *
     * @param array $langs_ids languages identifiers
     *
     * @return array
     */
    public function install_ratings_lang_export($langs_ids)
    {
        if (empty($langs_ids)) {
            return false;
        }
        $this->ci->load->model("Ratings_model");
        $langs = array();
        foreach ((array) $this->modules_data['ratings']["ratings"] as $rating_data) {
            $langs = array_merge($langs,
                $this->ci->Ratings_model->export_langs($rating_data['gid'],
                    $langs_ids));
        }

        return array("ratings" => $langs);
    }

    /**
     * Uninstall users data of ratings module
     *
     * @return void
     */
    public function deinstall_ratings()
    {
        $this->ci->load->model("Users_model");

        //add ratings type
        $this->ci->load->model("ratings/models/Ratings_type_model");

        foreach ((array) $this->modules_data['ratings']["ratings"] as $rating_data) {
            $this->ci->Ratings_type_model->delete_type($rating_data["gid"]);
        }

        $this->ci->Users_model->deinstall_ratings_fields(array_keys((array) $this->modules_data['ratings']["ratings_fields"]));
    }

    /**
     * Install spam links
     */
    public function install_spam()
    {
        // add spam type
        $this->ci->load->model("spam/models/Spam_type_model");

        foreach ((array) $this->modules_data['spam'] as $spam_data) {
            $validate_data = $this->ci->Spam_type_model->validate_type(null,
                $spam_data);
            if (!empty($validate_data["errors"])) {
                continue;
            }
            $this->ci->Spam_type_model->save_type(null, $validate_data["data"]);
        }
    }

    /**
     * Import spam languages
     *
     * @param array $langs_ids
     */
    public function install_spam_lang_update($langs_ids = null)
    {
        if (empty($langs_ids)) {
            return false;
        }

        $this->ci->load->model("spam/models/Spam_type_model");

        $langs_file = $this->ci->Install_model->language_file_read("users",
            "spam", $langs_ids);
        if (!$langs_file) {
            log_message("info", "Empty spam langs data");

            return false;
        }

        $this->ci->Spam_type_model->update_langs($this->modules_data['spam'], $langs_file,
            $langs_ids);

        return true;
    }

    /**
     * Export spam languages
     *
     * @param array $langs_ids
     */
    public function install_spam_lang_export($langs_ids = null)
    {
        $this->ci->load->model("spam/models/Spam_type_model");
        $langs = $this->ci->Spam_type_model->export_langs((array) $this->modules_data['spam'],
            $langs_ids);

        return array("spam" => $langs);
    }

    /**
     * Uninstall spam links
     */
    public function deinstall_spam()
    {
        //add spam type
        $this->ci->load->model("spam/models/Spam_type_model");

        foreach ((array) $this->modules_data['spam'] as $spam_data) {
            $this->ci->Spam_type_model->delete_type($spam_data["gid"]);
        }
    }

    public function install_bonuses()
    {

    }

    public function install_bonuses_lang_update($langs_ids = null)
    {
        if (empty($langs_ids)) {
            return false;
        }
        $this->ci->load->model("bonuses/models/Bonuses_util_model");
        $langs_file = $this->ci->Install_model->language_file_read("bonuses", "ds", $langs_ids);

        if (!$langs_file) {
            log_message("info", "Empty bonuses langs data");
            return false;
        }
        $this->ci->Bonuses_util_model->update_langs($langs_file);

        $this->ci->load->model("bonuses/models/Bonuses_actions_config_model");
        $this->ci->Bonuses_actions_config_model->setActionsConfig($this->modules_data['action_config']);

        return true;
    }

    public function install_bonuses_lang_export()
    {

    }

    public function uninstall_bonuses()
    {

    }
    
    /**
     * Install upload gallery data to aviary module
     *
     * @return void
     */
    public function install_aviary()
    {
        $this->ci->load->model('Aviary_model');
        foreach ($this->modules_data['aviary'] as $aviary) {
            $this->ci->Aviary_model->save_module(null, $aviary);
        }
    }

    /**
     * Uninstall upload gallery data from aviary module
     *
     * @return void
     */
    public function deinstall_aviary()
    {
        $this->ci->load->model('Aviary_model');
        foreach ($this->modules_data['aviary'] as $aviary) {
            $this->ci->Aviary_model->delete_module($aviary['module_gid']);
        }
    }
}
