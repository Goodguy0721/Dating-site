<?php

namespace Pg\Modules\Events\Models;

/**
 * Events install model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Alexey Chulkov <achulkov@pilotgroup.net>
 **/
class Events_install_model extends \Model
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
            'name'   => '',
            'items'  => array(
                'other_items' => array(
                    'action' => 'none',
                    'name'   => '',
                    'items'  => array(
                        "add_ons_items" => array(
                            "action" => "none",
                            "items"  => array(
                                "events_menu_item" => array("action" => "create", "link" => "admin/events", "status" => 1, "sorter" => 10),
                            ),
                        ),
                    ),
                ),
            ),
        ),
        'admin_events_menu' => array(
            'action' => 'create',
            'name'   => 'Events section menu',
            'items'  => array(
                'admin_events_list_menu_item' => array('action' => 'create', 'link' => 'admin/events/index', 'status' => 1, 'sorter' => 1),
                'admin_events_settings_menu_item' => array('action' => 'create', 'link' => 'admin/events/settings', 'status' => 1, 'sorter' => 2),
            ),
        ),
        'user_top_menu' => array(
            'name'   => 'Events section menu',
            'action' => 'none',
            'items'  => array(
                'user-menu-activities' => array(
                    'action' => 'none',
                    'items'  => array(
                        'user_events_item' => array('action' => 'create', 'link' => 'events/search', 'status' => 1, 'sorter' => 50, 'indicator_gid' => 'new_events_item',1),
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
            'gid'               => 'new_events_item',
            'delete_by_cron'    => false,
            'auth_type'         => 'user',
        ),
    );

    /**
     * Fields depended on languages
     *
     * @var array
     */
    private $lang_dm_data = array(
        array(
            "module"        => "events",
            "model"         => "Events_model",
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
    private $_notifications = array(
        'notifications' => array(
            array('gid' => 'events_remind_participant', "template" => "events_remind_participant", 'send_type' => 'simple'),
            array("gid" => "event_user_entered", "template" => "event_user_entered", "send_type" => "simple"),
            array("gid" => "event_user_excluded", "template" => "event_user_excluded", "send_type" => "simple"),
        ),
        'templates' => array(
            array('gid' => 'events_remind_participant', 'name' => 'Participant remind', 'vars' => array('nickname', 'event_link', 'event_name'), 'content_type' => 'html'),
            array('gid' => 'event_user_entered', 'name' => 'User join to event', 'vars' => array('creator_nickname', 'user_nickname', 'event_link', 'event_name'), 'content_type' => 'html'),
            array('gid' => 'event_user_excluded', 'name' => 'User excluded from the event', 'vars' => array('user_nickname', 'event_link', 'event_name'), 'content_type' => 'html'),
        ),
    );

    /**
     * Moderators configuration
     *
     * @params
     */
    protected $moderators = array(
        array('module' => 'events', 'method' => 'index', 'is_default' => 1),
        array('module' => 'events', 'method' => 'edit', 'is_default' => 1),
        array('module' => 'events', 'method' => 'settings', 'is_default' => 1),
    );

    private $moderation_types = array(
        array(
            'name'                 => 'event_data',
            'mtype'                => '0',
            'module'               => 'events',
            'model'                => 'Events_model',
            'check_badwords'       => '1',
            'method_get_list'      => 'moderGetList',
            'method_set_status'    => 'moderSetStatus',
            'method_delete_object' => '',
            'method_mark_adult'    => 'moderMarkAdult',
            'allow_to_decline'     => '1',
            'template_list_row'    => 'moder_block',
        ),
    );

    /**
     * Wall events configuration
     *
     * @params
     */
    protected $wall_events_types = array(
        'event_add',
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
        $langs_file = $this->CI->Install_model->language_file_read('events', 'menu', $langs_ids);

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

        $langs_file = $this->CI->Install_model->language_file_read('events', 'notifications', $langs_ids);

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

    public function install_wall_events()
    {
        $this->CI->load->model('Events_model');
        $this->CI->load->model('wall_events/models/Wall_events_types_model');
        foreach ($this->CI->Events_model->wall_events as $wall_event) {
            $this->CI->Wall_events_types_model->add_wall_events_type($wall_event);
        }

        return;
    }

    public function install_wall_events_lang_update($langs_ids = null)
    {
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }
        $langs_file = $this->CI->Install_model->language_file_read('events', 'wall_events', $langs_ids);

        if (!$langs_file) {
            log_message('info', 'Empty moderation langs data');

            return false;
        }
        $this->CI->load->model('wall_events/models/Wall_events_types_model');
        $this->CI->Wall_events_types_model->update_langs($this->wall_events_types, $langs_file);
    }

    public function install_wall_events_lang_export($langs_ids = null)
    {
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }
        $this->CI->load->model('wall_events/models/Wall_events_types_model');

        return array('wall_events' => $this->CI->Wall_events_types_model->export_langs($this->wall_events_types, $langs_ids));
    }

    public function deinstall_wall_events()
    {
        $this->CI->load->model('Events_model');
        $this->CI->load->model('wall_events/models/Wall_events_model');
        $this->CI->load->model('wall_events/models/Wall_events_types_model');
        foreach ($this->CI->Events_model->wall_events as $wall_event) {
            $this->CI->Wall_events_model->delete_events(array('event_type_gid' => $wall_event['gid']), false);
            $this->CI->Wall_events_types_model->delete_wall_events_type($wall_event['gid']);
        }
    }

    public function install_site_map()
    {
        $this->CI->load->model('Site_map_model');
        $site_map_data = array(
            'module_gid'      => 'events',
            'model_name'      => 'Events_model',
            'get_urls_method' => 'getSitemapUrls',
        );
        $this->CI->Site_map_model->set_sitemap_module('events', $site_map_data);
    }

    /**
     * Install banners links
     */
    public function install_banners()
    {
        $this->CI->load->model("banners/models/Banner_group_model");
        $this->CI->Banner_group_model->set_module("events", "Events_model", "bannerAvailablePages");
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

        $banners_groups = array('banners_group_events_groups');
        $langs_file = $this->CI->Install_model->language_file_read('events', 'pages', $langs_ids);
        $this->CI->load->model('banners/models/Banner_group_model');
        $this->CI->Banner_group_model->update_langs($banners_groups, $langs_file, $langs_ids);
    }

    /**
     * Unistall banners links
     */
    public function deinstall_banners()
    {
        $this->CI->load->model("banners/models/Banner_group_model");
        $this->CI->Banner_group_model->delete_module("events");
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
            'gid'           => 'events_groups',
            'name'          => 'Events pages',
        );
        $group_id = $this->CI->Banner_group_model->create_unique_group($group_attrs);
        $all_places = $this->CI->Banner_place_model->get_all_places();
        if ($all_places) {
            foreach ($all_places  as $key => $value) {
                if ($value['keyword'] != 'bottom-banner' && $value['keyword'] != 'top-banner') {
                    continue;
                }
                $this->CI->Banner_place_model->save_place_group($value['id'], $group_id);
            }
        }

        ///add pages in group
        $this->CI->load->model("Events_model");
        $pages = $this->CI->Events_model->bannerAvailablePages();
        if ($pages) {
            foreach ($pages  as $key => $value) {
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
        $group_id = $this->CI->Banner_group_model->get_group_id_by_gid("events_groups");
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
        $langs_file = $this->CI->Install_model->language_file_read("events", "moderators", $langs_ids);
        if (!$langs_file) {
            log_message("info", "Empty moderators langs data");

            return false;
        }
        // install moderators permissions
        $this->CI->load->model("Moderators_model");
        $params["where"]["module"] = "events";
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
        $params["where"]["module"] = "events";
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
        $params["where"]["module"] = "events";
        $this->CI->Moderators_model->delete_methods($params);
    }

    public function install_uploads()
    {
        // upload logo config
        $this->CI->load->model('uploads/models/Uploads_config_model');
        $config_data = array(
            'gid'          => 'event-logo',
            'name'         => 'Events logo',
            'max_height'   => 5000,
            'max_width'    => 5000,
            'max_size'     => 10000000,
            'name_format'  => 'generate',
            'file_formats' => array('jpg', 'jpeg', 'gif', 'png'),
            'default_img'  => 'default-gallery-image.png',
            'date_add'     => date('Y-m-d H:i:s'),
        );
        $config_data['file_formats'] = serialize($config_data['file_formats']);
        $config_id = $this->CI->Uploads_config_model->save_config(null, $config_data);
        $wm_data = $this->CI->Uploads_config_model->get_watermark_by_gid('image-wm');
        $wm_id = isset($wm_data["id"]) ? $wm_data["id"] : 0;

        $thumb_data = array(
            'config_id'    => $config_id,
            'prefix'       => 'big',
            'width'        => 200,
            'height'       => 200,
            'effect'       => 'none',
            'watermark_id' => 0,
            'crop_param'   => 'resize',
            'crop_color'   => 'ffffff',
            'date_add'     => date('Y-m-d H:i:s'),
        );
        $this->CI->Uploads_config_model->save_thumb(null, $thumb_data);

        $thumb_data = array(
            'config_id'    => $config_id,
            'prefix'       => 'large',
            'width'        => 305,
            'height'       => 305,
            'effect'       => 'none',
            'watermark_id' => 0,
            'crop_param'   => 'crop',
            'crop_color'   => 'ffffff',
            'date_add'     => date('Y-m-d H:i:s'),
        );
        $this->CI->Uploads_config_model->save_thumb(null, $thumb_data);

        // upload event album config
        $this->CI->load->model('uploads/models/Uploads_config_model');
        $config_data = array(
            'gid'          => 'events_image',
            'name'         => 'Events image',
            'max_height'   => 8000,
            'max_width'    => 8000,
            'max_size'     => 10000000,
            'name_format'  => 'generate',
            'file_formats' => serialize(array('jpg', 'jpeg', 'gif', 'png')),
            'default_img'  => 'default-gallery-image.png',
            'date_add'     => date('Y-m-d H:i:s'),
        );
        $config_id = $this->CI->Uploads_config_model->save_config(null, $config_data);
        $wm_data = $this->CI->Uploads_config_model->get_watermark_by_gid('image-wm');
        $wm_id = isset($wm_data["id"]) ? $wm_data["id"] : 0;

        $thumb_data = array(
            'config_id'    => $config_id,
            'prefix'       => 'grand',
            'width'        => 960,
            'height'       => 720,
            'effect'       => 'none',
            'watermark_id' => $wm_id,
            'crop_param'   => 'resize',
            'crop_color'   => 'ffffff',
            'date_add'     => date('Y-m-d H:i:s'),
        );
        $this->CI->Uploads_config_model->save_thumb(null, $thumb_data);

        $thumb_data = array(
            'config_id'    => $config_id,
            'prefix'       => 'big',
            'width'        => 150,
            'height'       => 150,
            'effect'       => 'none',
            'watermark_id' => 0,
            'crop_param'   => 'crop',
            'crop_color'   => 'ffffff',
            'date_add'     => date('Y-m-d H:i:s'),
        );
        $this->CI->Uploads_config_model->save_thumb(null, $thumb_data);

        $thumb_data = array(
            'config_id'    => $config_id,
            'prefix'       => 'small',
            'width'        => 60,
            'height'       => 60,
            'effect'       => 'none',
            'watermark_id' => 0,
            'crop_param'   => 'crop',
            'crop_color'   => 'ffffff',
            'date_add'     => date('Y-m-d H:i:s'),
        );
        $this->CI->Uploads_config_model->save_thumb(null, $thumb_data);
    }

    public function deinstall_uploads()
    {
        $this->CI->load->model('Events_model');
        $events = $this->CI->Events_model->getListEvents();
        foreach ($events as $event) {
            $this->CI->Events_model->delete($event['id']);
        }

        $this->CI->load->model('uploads/models/Uploads_config_model');
        $config_data = $this->CI->Uploads_config_model->get_config_by_gid('event-logo');
        if (!empty($config_data['id'])) {
            $this->CI->Uploads_config_model->delete_config($config_data['id']);
        }
        $config_data = $this->CI->Uploads_config_model->get_config_by_gid('events_image');
        if (!empty($config_data['id'])) {
            $this->CI->Uploads_config_model->delete_config($config_data['id']);
        }
    }

    public function install_video_uploads()
    {
        ///// add video settings
        $this->CI->load->model('video_uploads/models/Video_uploads_config_model');
        $thums_settings = array(
            array('gid' => 'small', 'width' => 60, 'height' => 60, 'animated' => 0),
            array('gid' => 'middle', 'width' => 100, 'height' => 100, 'animated' => 0),
            array('gid' => 'big', 'width' => 200, 'height' => 200, 'animated' => 0),
            array('gid' => 'great', 'width' => 305, 'height' => 305, 'animated' => 0),
            array('gid' => 'hgreat', 'width' => 305, 'height' => 200, 'animated' => 0),
            array('gid' => 'vgreat', 'width' => 200, 'height' => 305, 'animated' => 0),
            array('gid' => 'grand', 'width' => 740, 'height' => 500, 'animated' => 0),
        );
        $local_settings = array(
            'width'       => 640,
            'height'      => 360,
            'audio_freq'  => '22050',
            'audio_brate' => '64k',
            'video_brate' => '800k',
            'video_rate'  => '100',
        );
        $file_formats = array('avi', 'flv', 'mkv', 'asf', 'mpeg', 'mpg', 'mov', 'mp4');
        $config_data = array(
            'gid'             => 'events_video',
            'name'            => 'Events video',
            'max_size'        => 1073741824,
            'file_formats'    => serialize($file_formats),
            'default_img'     => 'media-video-default.png',
            'date_add'        => date('Y-m-d H:i:s'),
            'upload_type'     => 'local',
            'use_convert'     => '1',
            'use_thumbs'      => '1',
            'module'          => 'media',
            'model'           => 'Media_model',
            'method_status'   => 'video_callback',
            'thumbs_settings' => serialize($thums_settings),
            'local_settings'  => serialize($local_settings),
        );
        $this->CI->Video_uploads_config_model->save_config(null, $config_data);
    }

    public function deinstall_video_uploads()
    {
        ///// delete video settings
        $this->CI->load->model('video_uploads/models/Video_uploads_config_model');
        $config_data = $this->CI->Video_uploads_config_model->get_config_by_gid('events_video');
        if (!empty($config_data["id"])) {
            $this->CI->Video_uploads_config_model->delete_config($config_data["id"]);
        }
    }

    /**
     * Install fields
     */
    public function _prepare_installing()
    {
        $this->CI->load->model("Events_model");
        foreach ($this->CI->pg_language->languages as $lang_id => $value) {
            $this->CI->Events_model->langDedicateModuleCallbackAdd($lang_id);
        }
    }

    public function _arbitrary_installing()
    {
        ///// add entries for lang data updates
        foreach ($this->lang_dm_data as $lang_dm_data) {
            $this->CI->pg_language->add_dedicate_modules_entry($lang_dm_data);
        }
        // SEO
        $seo_data = array(
            'module_gid'              => 'events',
            'model_name'              => 'Events_model',
            'get_settings_method'     => 'getSeoSettings',
            'get_rewrite_vars_method' => 'requestSeoRewrite',
            'get_sitemap_urls_method' => 'getSitemapXmlUrls',
        );
        $this->CI->pg_seo->set_seo_module('events', $seo_data);
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
        $langs_file = $this->CI->Install_model->language_file_read('events', 'arbitrary', $langs_ids);
        if (!$langs_file) {
            log_message('info', 'Empty events arbitrary langs data');

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
            $this->CI->pg_seo->set_settings('user', 'events', $page, $post_data);
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
        $seo_settings = $this->pg_seo->get_all_settings_from_cache('user', 'events');
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
        $this->CI->pg_seo->delete_seo_module('events');
    }

    public function deinstall_site_map()
    {
        $this->CI->load->model('Site_map_model');
        $this->CI->Site_map_model->delete_sitemap_module('events');
    }

    public function install_moderation()
    {
        // Moderation
        $this->CI->load->model('moderation/models/Moderation_type_model');
        foreach ($this->moderation_types as $mtype) {
            $mtype['date_add'] = date("Y-m-d H:i:s");
            $this->CI->Moderation_type_model->save_type(null, $mtype);
        }
    }

    public function install_moderation_lang_update($langs_ids = null)
    {
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }
        $langs_file = $this->CI->Install_model->language_file_read('events', 'moderation', $langs_ids);
        if (!$langs_file) {
            log_message('info', 'Empty moderation langs data');

            return false;
        }
        $this->CI->load->model('moderation/models/Moderation_type_model');
        $this->CI->Moderation_type_model->update_langs($this->moderation_types, $langs_file);
    }

    public function install_moderation_lang_export($langs_ids = null)
    {
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }
        $this->CI->load->model('moderation/models/Moderation_type_model');

        return array('moderation' => $this->CI->Moderation_type_model->export_langs($this->moderation_types, $langs_ids));
    }

    public function deinstall_moderation()
    {
        // Moderation
        $this->CI->load->model('moderation/models/Moderation_type_model');
        foreach ($this->moderation_types as $mtype) {
            $type = $this->CI->Moderation_type_model->get_type_by_name($mtype["name"]);
            $this->CI->Moderation_type_model->delete_type($type['id']);
        }
    }

    /**
     * Install data of comments module
     *
     * @return void
     */
    public function install_comments()
    {
        $this->CI->load->model('comments/models/Comments_types_model');
        $comment_type = array(
            'gid'           => 'events',
            'module'        => 'events',
            'model'         => 'Events_model',
            'method_count'  => 'comments_count_callback',
            'method_object' => 'comments_object_callback',
            'settings'      => array('comments_author_can_edit' => 1, 'comments_get_object_author_method' => 'comments_get_author_module_object'),
        );
        $this->CI->Comments_types_model->add_comments_type($comment_type);

        $comment_type = array(
            'gid'           => 'events_avatar',
            'module'        => 'events',
            'model'         => 'Events_model',
            'method_count'  => 'comments_avatar_count_callback',
            'method_object' => 'comments_avatar_object_callback',
        );
        $this->CI->Comments_types_model->add_comments_type($comment_type);
    }

    /**
     * Import languages of comments module
     *
     * @param array $langs_ids languages identifiers
     *
     * @return boolean
     */
    public function install_comments_lang_update($langs_ids = null)
    {
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }
        $langs_file = $this->CI->Install_model->language_file_read('events', 'comments', $langs_ids);

        if (!$langs_file) {
            log_message('info', 'Empty moderation langs data');

            return false;
        }
        $this->CI->load->model('comments/models/Comments_types_model');
        $ctypes = array ('events', 'events_avatar');
        $this->CI->Comments_types_model->update_langs($ctypes, $langs_file);
    }

    /**
     * Export languages of comments module
     *
     * @param array $langs_ids languages identifiers
     *
     * @return array
     */
    public function install_comments_lang_export($langs_ids = null)
    {
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }
        $this->CI->load->model('comments/models/Comments_types_model');

        return array('comments' => $this->CI->Comments_types_model->export_langs(array('events'), $langs_ids));
    }

    /**
     * Unistall data of comments module
     *
     * @return void
     */
    public function deinstall_comments()
    {
        $this->CI->load->model('comments/models/Comments_types_model');
        $this->CI->Comments_types_model->delete_comments_type('events');
    }
}
