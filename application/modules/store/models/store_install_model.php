<?php

/**
 * Store install model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Nikita Savanaev <nsavanaev@pilotgroup.net>
 * */
class Store_install_model extends Model
{
    private $CI;
    private $menu = array(
        'admin_menu' => array(
            'action' => 'none',
            'items' => array(
                'other_items' => array(
                    'action' => 'none',
                    'items' => array(
                        "add_ons_items" => array(
                            "action" => "none",
                            "items" => array(
                                'store_menu_item' => array(
                                    'action' => 'create',
                                    'link' => 'admin/store',
                                    'status' => 1,
                                    'sorter' => 2,
                                    'icon' => 'shopping-cart',
                                    'items' => array(
                                        'products_menu_item' => array('action' => 'create',
                                            'link' => 'admin/store/products', 'status' => 1,
                                            'sorter' => 1),
                                        'product_categories_menu_item' => array(
                                            'action' => 'create', 'link' => 'admin/store/product_categories',
                                            'status' => 1, 'sorter' => 2),
                                        'options_menu_item' => array('action' => 'create',
                                            'link' => 'admin/store/options', 'status' => 1,
                                            'sorter' => 3),
                                        'shipping_menu_item' => array('action' => 'create',
                                            'link' => 'admin/store/shipping', 'status' => 1,
                                            'sorter' => 4),
                                        'orders_menu_item' => array('action' => 'create',
                                            'link' => 'admin/store/orders', 'status' => 1,
                                            'sorter' => 5),
                                        'bestsellers_menu_item' => array('action' => 'create',
                                            'link' => 'admin/store/bestsellers',
                                            'status' => 1, 'sorter' => 6),
                                        'settings_menu_item' => array('action' => 'create',
                                            'link' => 'admin/store/settings', 'status' => 1,
                                            'sorter' => 7),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
        'user_top_menu' => array(
            'action' => 'none',
            'items' => array(
                'user-menu-store' => array(
                    'action' => 'create',
                    'status' => 1,
                    'sorter' => 4,
                    'icon' => 'shopping-cart',
                    'items' => array(
                        'user_store_main_item' => array('action' => 'create', 'link' => 'store/index',
                            'status' => 1, 'sorter' => 1),
                        'user_store_orders_item' => array('action' => 'create', 'link' => 'store/order_list',
                            'status' => 1, 'sorter' => 2),
                    ),
                ),
            ),
        ),
    );
    private $lang_dm_data = array(
        array(
            'module' => 'store',
            'model' => 'Store_products_model',
            'method_add' => 'lang_dedicate_module_callback_add',
            'method_delete' => 'lang_dedicate_module_callback_delete',
        ),
        array(
            'module' => 'store',
            'model' => 'Store_categories_model',
            'method_add' => 'lang_dedicate_module_callback_add',
            'method_delete' => 'lang_dedicate_module_callback_delete',
        ),
        array(
            'module' => 'store',
            'model' => 'Store_options_model',
            'method_add' => 'lang_dedicate_module_callback_add',
            'method_delete' => 'lang_dedicate_module_callback_delete',
        ),
        array(
            'module' => 'store',
            'model' => 'Store_shippings_model',
            'method_add' => 'lang_dedicate_module_callback_add',
            'method_delete' => 'lang_dedicate_module_callback_delete',
        ),
    );
    private $_seo_pages = array(
        'category',
        'product',
        'cart',
        'index',
        'search',
        'preorder',
        'order',
        'shipping_address',
        'order_list',
    );
    private $_notifications = array(
        'notifications' => array(
            array('gid' => 'store_pending_payment', 'send_type' => 'simple'),
            array('gid' => 'store_waits_user_consent', 'send_type' => 'simple'),
            array('gid' => 'store_rejected_recipient', 'send_type' => 'simple'),
            array('gid' => 'store_confirmed_recipient', 'send_type' => 'simple'),
            array('gid' => 'store_paid', 'send_type' => 'simple'),
            array('gid' => 'store_rejected_admin', 'send_type' => 'simple'),
            array('gid' => 'store_during_delivery', 'send_type' => 'simple'),
            array('gid' => 'store_delivered_from', 'send_type' => 'simple'),
            array('gid' => 'store_delivered_for', 'send_type' => 'simple'),
        ),
        'templates' => array(
            array('gid' => 'store_pending_payment', 'name' => 'Pending Payment',
                'vars' => array('sender_nickname', 'code', 'status'), 'content_type' => 'text'),
            array('gid' => 'store_waits_user_consent', 'name' => 'Waits user consent',
                'vars' => array('sender_nickname', 'code', 'status', 'recipient_nickname'),
                'content_type' => 'text'),
            array('gid' => 'store_rejected_recipient', 'name' => 'Rejected recipient',
                'vars' => array('sender_nickname', 'code', 'status'), 'content_type' => 'text'),
            array('gid' => 'store_confirmed_recipient', 'name' => 'Confirmed recipient',
                'vars' => array('sender_nickname', 'code', 'status', 'recipient_nickname'),
                'content_type' => 'text'),
            array('gid' => 'store_paid', 'name' => 'Paid', 'vars' => array('sender_nickname',
                    'code', 'status'), 'content_type' => 'text'),
            array('gid' => 'store_rejected_admin', 'name' => 'Administrator has rejected your order',
                'vars' => array('comment'), 'content_type' => 'text'),
            array('gid' => 'store_during_delivery', 'name' => 'During delivery',
                'vars' => array('sender_nickname', 'code', 'status'), 'content_type' => 'text'),
            array('gid' => 'store_delivered_from', 'name' => 'Delivered', 'vars' => array(
                    'sender_nickname', 'code', 'status'), 'content_type' => 'text'),
            array('gid' => 'store_delivered_for', 'name' => 'Delivered', 'vars' => array(
                    'sender_nickname', 'code', 'status', 'recipient_nickname'), 'content_type' => 'text'),
        ),
    );
    private $lang_services = array(
        'service' => array('store_service'),
        'template' => array('store_template'),
    );

    /**
     * Moderators configuration
     *
     * @params
     */
    private $moderators = array(
        array("module" => "store", "method" => "index", "is_default" => 1),
        array("module" => "store", "method" => "products", "is_default" => 0),
        array("module" => "store", "method" => "product", "is_default" => 0),
        array("module" => "store", "method" => "product_categories", "is_default" => 0),
        array("module" => "store", "method" => "edit_category", "is_default" => 0),
        array("module" => "store", "method" => "category", "is_default" => 0),
        array("module" => "store", "method" => "options", "is_default" => 0),
        array("module" => "store", "method" => "options_edit", "is_default" => 0),
        array("module" => "store", "method" => "option_fields", "is_default" => 0),
        array("module" => "store", "method" => "option_fields_edit", "is_default" => 0),
        array("module" => "store", "method" => "shipping", "is_default" => 0),
        array("module" => "store", "method" => "shipping_edit", "is_default" => 0),
        array("module" => "store", "method" => "orders", "is_default" => 0),
        array("module" => "store", "method" => "order", "is_default" => 0),
        array("module" => "store", "method" => "bestsellers", "is_default" => 0),
        array("module" => "store", "method" => "settings", "is_default" => 0),
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
            'width' => 640,
            'height' => 360,
            'audio_freq' => '22050',
            'audio_brate' => '64k',
            'video_brate' => '800k',
            'video_rate' => '100',
        );
        $file_formats   = array('avi', 'flv', 'mkv', 'asf', 'mpeg', 'mpg', 'mov');
        $config_data    = array(
            'gid' => 'store',
            'name' => 'Store_video',
            'max_size' => 1073741824,
            'file_formats' => serialize($file_formats),
            'default_img' => 'media-video-default.png',
            'date_add' => date('Y-m-d H:i:s'),
            'upload_type' => 'local',
            'use_convert' => '1',
            'use_thumbs' => '1',
            'module' => 'store',
            'model' => 'Store_products_model',
            'method_status' => 'video_callback',
            'thumbs_settings' => serialize($thums_settings),
            'local_settings' => serialize($local_settings),
        );
        $this->CI->Video_uploads_config_model->save_config(null, $config_data);
    }

    public function deinstall_video_uploads()
    {
        ///// delete video settings
        $this->CI->load->model('video_uploads/models/Video_uploads_config_model');
        $config_data = $this->CI->Video_uploads_config_model->get_config_by_gid('store_video');
        if (!empty($config_data["id"])) {
            $this->CI->Video_uploads_config_model->delete_config($config_data["id"]);
        }
    }

    public function install_menu()
    {
        $this->CI->load->helper('menu');

        foreach ($this->menu as $gid => $menu_data) {
            $this->menu[$gid]['id'] = linked_install_set_menu($gid,
                $menu_data['action']);
            linked_install_process_menu_items($this->menu, 'create', $gid, 0,
                $this->menu[$gid]['items']);
        }
    }

    public function install_menu_lang_update($langs_ids = null)
    {
        if (empty($langs_ids)) {
            return false;
        }
        $langs_file = $this->CI->Install_model->language_file_read('store',
            'menu', $langs_ids);

        if (!$langs_file) {
            log_message('info', 'Empty menu langs data');

            return false;
        }

        $this->CI->load->helper('menu');

        foreach ($this->menu as $gid => $menu_data) {
            linked_install_process_menu_items($this->menu, 'update', $gid, 0,
                $this->menu[$gid]['items'], $gid, $langs_file);
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
            $temp   = linked_install_process_menu_items($this->menu, 'export',
                $gid, 0, $this->menu[$gid]['items'], $gid, $langs_ids);
            $return = array_merge($return, $temp);
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
                linked_install_delete_menu_items($gid,
                    $this->menu[$gid]['items']);
            }
        }
    }

    public function install_notifications()
    {
        // add notification
        $this->CI->load->model('Notifications_model');
        $this->CI->load->model('notifications/models/Templates_model');

        foreach ($this->_notifications['templates'] as $tpl) {
            $template_data        = array(
                'gid' => $tpl['gid'],
                'name' => $tpl['name'],
                'vars' => serialize($tpl['vars']),
                'content_type' => $tpl['content_type'],
                'date_add' => date('Y-m-d H:i:s'),
                'date_update' => date('Y-m-d H:i:s'),
            );
            $tpl_ids[$tpl['gid']] = $this->CI->Templates_model->save_template(null,
                $template_data);
        }

        foreach ($this->_notifications['notifications'] as $notification) {
            $notification_data = array(
                'gid' => $notification['gid'],
                'send_type' => $notification['send_type'],
                'id_template_default' => $tpl_ids[$notification['gid']],
                'date_add' => date("Y-m-d H:i:s"),
                'date_update' => date("Y-m-d H:i:s"),
            );
            $this->CI->Notifications_model->save_notification(null,
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
        $this->CI->load->model('Notifications_model');

        $langs_file = $this->CI->Install_model->language_file_read('store',
            'notifications', $langs_ids);

        if (!$langs_file) {
            log_message('info', 'Empty notifications langs data');

            return false;
        }

        $this->CI->Notifications_model->update_langs($this->_notifications,
            $langs_file, $langs_ids);

        return true;
    }

    public function install_notifications_lang_export($langs_ids = null)
    {
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }
        $this->CI->load->model('Notifications_model');
        $langs = $this->CI->Notifications_model->export_langs($this->_notifications,
            $langs_ids);

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

    public function install_uploads()
    {
        // upload config
        $this->CI->load->model('uploads/models/Uploads_config_model');
        $config_data                 = array(
            'gid' => 'store',
            'name' => 'Product photo',
            'max_height' => 5000,
            'max_width' => 5000,
            'max_size' => 10000000,
            'name_format' => 'generate',
            'file_formats' => array('jpg', 'jpeg', 'gif', 'png'),
            'default_img' => '',
            'date_add' => date('Y-m-d H:i:s'),
        );
        $config_data['file_formats'] = serialize($config_data['file_formats']);
        $config_id                   = $this->CI->Uploads_config_model->save_config(null,
            $config_data);
        $wm_data                     = $this->CI->Uploads_config_model->get_watermark_by_gid('image-wm');
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
        $this->CI->Uploads_config_model->save_thumb(null, $thumb_data);

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
        $this->CI->Uploads_config_model->save_thumb(null, $thumb_data);

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
        $this->CI->Uploads_config_model->save_thumb(null, $thumb_data);

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
        $this->CI->Uploads_config_model->save_thumb(null, $thumb_data);

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
        $this->CI->Uploads_config_model->save_thumb(null, $thumb_data);

        $thumb_data = array(
            'config_id' => $config_id,
            'prefix' => 'hgreat',
            'width' => 305,
            'height' => 200,
            'effect' => 'none',
            'watermark_id' => $wm_id,
            'crop_param' => 'crop',
            'crop_color' => 'ffffff',
            'date_add' => date('Y-m-d H:i:s'),
        );
        $this->CI->Uploads_config_model->save_thumb(null, $thumb_data);

        $thumb_data = array(
            'config_id' => $config_id,
            'prefix' => 'vgreat',
            'width' => 200,
            'height' => 305,
            'effect' => 'none',
            'watermark_id' => $wm_id,
            'crop_param' => 'crop',
            'crop_color' => 'ffffff',
            'date_add' => date('Y-m-d H:i:s'),
        );
        $this->CI->Uploads_config_model->save_thumb(null, $thumb_data);
    }

    public function deinstall_uploads()
    {
        $this->CI->load->model('uploads/models/Uploads_config_model');
        $config_data = $this->CI->Uploads_config_model->get_config_by_gid('store');
        if (!empty($config_data['id'])) {
            $this->CI->Uploads_config_model->delete_config($config_data['id']);
        }
    }

    public function install_site_map()
    {
        $this->CI->load->model('Site_map_model');
        $site_map_data = array(
            'module_gid' => 'store',
            'model_name' => 'Store_model',
            'get_urls_method' => 'get_sitemap_urls',
        );
        $this->CI->Site_map_model->set_sitemap_module('store', $site_map_data);
    }

    /**
     * Install banners links
     */
    public function install_banners()
    {
        ///// add banners module
        $this->CI->load->model("banners/models/Banner_group_model");
        $this->CI->Banner_group_model->set_module("store", "Store_model",
            "_banner_available_pages");
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

        $banners_groups = array('banners_group_store_groups');
        $langs_file     = $this->CI->Install_model->language_file_read('store',
            'pages', $langs_ids);
        $this->CI->load->model('banners/models/Banner_group_model');
        $this->CI->Banner_group_model->update_langs($banners_groups,
            $langs_file, $langs_ids);
    }

    /**
     * Unistall banners links
     */
    public function deinstall_banners()
    {
        // delete banners module
        $this->CI->load->model("banners/models/Banner_group_model");
        $this->CI->Banner_group_model->delete_module("store");
        $this->remove_banners();
    }

    /**
     * Add default banners
     */
    public function add_banners()
    {
        $this->CI->load->model("Users_model");
        $this->CI->load->model("banners/models/Banner_group_model");
        $this->CI->load->model("banners/models/Banner_place_model");

        $group_attrs = array(
            'date_created' => date("Y-m-d H:i:s"),
            'date_modified' => date("Y-m-d H:i:s"),
            'price' => 1,
            'gid' => 'store_groups',
            'name' => 'Store pages',
        );
        $group_id    = $this->CI->Banner_group_model->create_unique_group($group_attrs);
        $all_places  = $this->CI->Banner_place_model->get_all_places();
        if ($all_places) {
            foreach ($all_places as $key => $value) {
                if ($value['keyword'] != 'bottom-banner' && $value['keyword'] != 'top-banner') {
                    continue;
                }
                $this->CI->Banner_place_model->save_place_group($value['id'],
                    $group_id);
            }
        }

        ///add pages in group
        $this->CI->load->model("Store_model");
        $pages = $this->CI->Store_model->_banner_available_pages();
        if ($pages) {
            foreach ($pages as $key => $value) {
                $page_attrs = array(
                    "group_id" => $group_id,
                    "name" => $value["name"],
                    "link" => $value["link"],
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
        $group_id = $this->CI->Banner_group_model->get_group_id_by_gid("store_groups");
        $this->CI->Banner_group_model->delete($group_id);
    }

    public function install_services()
    {
        // add service type and service
        // create service template and service
        $this->CI->load->model("Services_model");
        $template_data = array(
            'gid' => "store_template",
            'callback_module' => "store",
            'callback_model' => "Store_orders_model",
            'callback_buy_method' => "service_buy_store",
            'callback_activate_method' => "service_activate_store",
            'callback_validate_method' => "service_validate_store",
            'price_type' => 3,
            'data_admin' => "",
            'data_user' => serialize(array("id_order_payment" => "hidden", "id_shipping" => "hidden",
                "price" => "hidden")),
            'date_add' => date("Y-m-d H:i:s"),
            'moveable' => 0,
            'alert_activate' => 0,
        );
        $this->CI->Services_model->save_template(null, $template_data);

        $service_data = array(
            "gid" => "store_service",
            "template_gid" => "store_template",
            "pay_type" => 2,
            "status" => 1,
            "price" => 0,
            "type" => "internal",
            "data_admin" => "",
            "date_add" => date("Y-m-d H:i:s"),
        );
        $this->CI->Services_model->save_service(null, $service_data);
    }

    public function install_services_lang_update($langs_ids = null)
    {
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }
        $this->CI->load->model('Services_model');
        $langs_file = $this->CI->Install_model->language_file_read('store',
            'services', $langs_ids);
        $this->CI->Services_model->update_langs($this->lang_services,
            $langs_file);

        return true;
    }

    public function install_services_lang_export($langs_ids = null)
    {
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }
        $this->CI->load->model('Services_model');

        return array('services' => $this->CI->Services_model->export_langs($this->lang_services,
                $langs_ids));
    }

    public function deinstall_services()
    {
        $this->CI->load->model("Services_model");
        $this->CI->Services_model->delete_template_by_gid('store_template');
        $this->CI->Services_model->delete_service_by_gid('store_service');
    }

    /**
     * Install moderators links
     */
    public function install_moderators()
    {
        //install moderators permissions
        $this->CI->load->model("Moderators_model");
        foreach ((array) $this->moderators as $method_data) {
            $validate_data = array("errors" => array(), "data" => $method_data);
            if (!empty($validate_data["errors"])) {
                continue;
            }
            $this->CI->Moderators_model->save_method(null,
                $validate_data["data"]);
        }
    }

    /**
     * Import moderators languages
     *
     * @param array $langs_ids
     */
    public function install_moderators_lang_update($langs_ids = null)
    {
        $langs_file = $this->CI->Install_model->language_file_read("store",
            "moderators", $langs_ids);
        if (!$langs_file) {
            log_message("info", "Empty moderators langs data");

            return false;
        }
        // install moderators permissions
        $this->CI->load->model("Moderators_model");
        $params["where"]["module"] = "store";
        $methods                   = $this->CI->Moderators_model->get_methods_lang_export($params);

        foreach ($methods as $method) {
            if (!empty($langs_file[$method["method"]])) {
                $this->CI->Moderators_model->save_method($method["id"], array(),
                    $langs_file[$method["method"]]);
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
        $params["where"]["module"] = "store";
        $methods                   = $this->CI->Moderators_model->get_methods_lang_export($params,
            $langs_ids);
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
        $params                    = array();
        $params["where"]["module"] = "store";
        $this->CI->Moderators_model->delete_methods($params);
    }

    /**
     * Install fields
     */
    public function _prepare_installing()
    {
        $this->CI->load->model("store/models/Store_shippings_model");
        $this->CI->load->model("store/models/Store_products_model");
        $this->CI->load->model("store/models/Store_categories_model");
        $this->CI->load->model("store/models/Store_options_model");
        foreach ($this->CI->pg_language->languages as $lang_id => $value) {
            $this->CI->Store_shippings_model->lang_dedicate_module_callback_add($lang_id);
            $this->CI->Store_products_model->lang_dedicate_module_callback_add($lang_id);
            $this->CI->Store_categories_model->lang_dedicate_module_callback_add($lang_id);
            $this->CI->Store_options_model->lang_dedicate_module_callback_add($lang_id);
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
            'module_gid' => 'store',
            'model_name' => 'Store_model',
            'get_settings_method' => 'get_seo_settings',
            'get_rewrite_vars_method' => 'request_seo_rewrite',
            'get_sitemap_urls_method' => 'get_sitemap_xml_urls',
        );
        $this->CI->pg_seo->set_seo_module('store', $seo_data);

        // add seo link
        $this->CI->load->model('Seo_advanced_model');
        $xml_data                     = $this->CI->Seo_advanced_model->get_xml_route_file_content();
        $data                         = array(
            'noindex' => 1,
            'title' => '',
            'keyword' => '',
            'description' => '',
            'header' => '',
            'og_title' => '',
            'og_type' => '',
            'og_descrtiption' => '',
            'url_template' => '[text:store/][opt:category_name:literal:empty][text:/][opt:id:literal:empty][text:-][tpl:1:gid:literal:empty]',
        );
        $this->CI->pg_seo->set_settings('user', 'store', 'product', $data);
        $xml_data['store']['product'] = $this->CI->pg_seo->url_template_transform('store',
            'product', $data["url_template"], 'base', 'xml');

        $data                          = array(
            'noindex' => 1,
            'title' => '',
            'keyword' => '',
            'description' => '',
            'header' => '',
            'og_title' => '',
            'og_type' => '',
            'og_descrtiption' => '',
            'url_template' => '[text:store/][text:category-][tpl:1:gid:literal:empty]',
        );
        $this->CI->pg_seo->set_settings('user', 'store', 'category', $data);
        $xml_data['store']['category'] = $this->CI->pg_seo->url_template_transform('store',
            'category', $data["url_template"], 'base', 'xml');

        $data                          = array(
            'noindex' => 1,
            'title' => '',
            'keyword' => '',
            'description' => '',
            'header' => '',
            'og_title' => '',
            'og_type' => '',
            'og_descrtiption' => '',
            'url_template' => '[text:store/][text:preorder-][tpl:1:code:literal:empty]',
        );
        $this->CI->pg_seo->set_settings('user', 'store', 'preorder', $data);
        $xml_data['store']['preorder'] = $this->CI->pg_seo->url_template_transform('store',
            'preorder', $data["url_template"], 'base', 'xml');

        $data                       = array(
            'noindex' => 1,
            'title' => '',
            'keyword' => '',
            'description' => '',
            'header' => '',
            'og_title' => '',
            'og_type' => '',
            'og_descrtiption' => '',
            'url_template' => '[text:store/][text:order-][tpl:1:code:literal:empty]',
        );
        $this->CI->pg_seo->set_settings('user', 'store', 'order', $data);
        $xml_data['store']['order'] = $this->CI->pg_seo->url_template_transform('store',
            'order', $data["url_template"], 'base', 'xml');

        $this->CI->Seo_advanced_model->set_xml_route_file_content($xml_data);
        $this->CI->Seo_advanced_model->rewrite_route_php_file();

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
        $langs_file = $this->CI->Install_model->language_file_read('store',
            'arbitrary', $langs_ids);
        if (!$langs_file) {
            log_message('info', 'Empty store arbitrary langs data');

            return false;
        }
        foreach ($this->_seo_pages as $page) {
            $post_data = array(
                'title' => $langs_file["seo_tags_{$page}_title"],
                'keyword' => $langs_file["seo_tags_{$page}_keyword"],
                'description' => $langs_file["seo_tags_{$page}_description"],
                'header' => $langs_file["seo_tags_{$page}_header"],
                'og_title' => $langs_file["seo_tags_{$page}_og_title"],
                'og_type' => $langs_file["seo_tags_{$page}_og_type"],
                'og_description' => $langs_file["seo_tags_{$page}_og_description"],
            );
            $this->CI->pg_seo->set_settings('user', 'store', $page, $post_data);
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
        $seo_settings = $this->pg_seo->get_all_settings_from_cache('user',
            'store');
        $lang_ids     = array_keys($this->CI->pg_language->languages);
        foreach ($seo_settings as $seo_page) {
            $prefix = 'seo_tags_' . $seo_page['method'];
            foreach ($lang_ids as $lang_id) {
                $meta                                                    = 'meta_' . $lang_id;
                $og                                                      = 'og_' . $lang_id;
                $arbitrary_return[$prefix . '_title'][$lang_id]          = $seo_page[$meta]['title'];
                $arbitrary_return[$prefix . '_keyword'][$lang_id]        = $seo_page[$meta]['keyword'];
                $arbitrary_return[$prefix . '_description'][$lang_id]    = $seo_page[$meta]['description'];
                $arbitrary_return[$prefix . '_header'][$lang_id]         = $seo_page[$meta]['header'];
                $arbitrary_return[$prefix . '_og_title'][$lang_id]       = $seo_page[$og]['og_title'];
                $arbitrary_return[$prefix . '_og_type'][$lang_id]        = $seo_page[$og]['og_type'];
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
        $this->CI->pg_seo->delete_seo_module('store');
    }

    public function deinstall_site_map()
    {
        $this->CI->load->model('Site_map_model');
        $this->CI->Site_map_model->delete_sitemap_module('store');
    }

    /**
     * Install demo content
     *
     * @return void
     */
    public function add_demo_content()
    {
        $this->CI->load->model('store/models/Store_products_model');
        // Associating languages id with codes
        foreach ($this->CI->pg_language->languages as $l) {
            $lang[$l['code']] = $l['id'];
            if (!empty($l['is_default'])) {
                $default_lang = $l;
            }
        }
        $demo_content = include MODULEPATH . 'store/install/demo_content.php';

        //Options
        if (!empty($demo_content['options'])) {
            $this->CI->load->model('store/models/Store_options_model');
            foreach ($demo_content['options'] as $option) {
                foreach ($lang as $key => $id) {
                    if (empty($option['name'][$key])) {
                        $option['name_' . $id] = $option['name']['en'];
                    } else {
                        $option['name_' . $id] = $option['name'][$key];
                    }
                    if (empty($option['description'][$key])) {
                        $option['description_' . $id] = $option['description']['en'];
                    } else {
                        $option['description_' . $id] = $option['description'][$key];
                    }
                }
                unset($option['name']);
                unset($option['description']);
                $id = $this->CI->Store_options_model->save_option(null, $option);
                $this->CI->Store_products_model->option_dedicate_module_callback_add($id);
            }
        }

        //Categories
        $this->CI->load->model('store/models/Store_categories_model');
        if (!empty($demo_content['categories'])) {
            foreach ($demo_content['categories'] as $category) {
                foreach ($lang as $key => $id) {
                    if (empty($category['name'][$key])) {
                        $category['name_' . $id] = $category['name']['en'];
                    } else {
                        $category['name_' . $id] = $category['name'][$key];
                    }
                    if (empty($category['description'][$key])) {
                        $category['description_' . $id] = $category['description']['en'];
                    } else {
                        $category['description_' . $id] = $category['description'][$key];
                    }
                }

                unset($category['name']);
                unset($category['description']);
                $this->CI->Store_categories_model->save_category(null, $category);
            }
        }

        // Products
        if (!empty($demo_content['products'])) {
            foreach ($demo_content['products'] as $product) {
                foreach ($lang as $key => $id) {
                    if (empty($product['name'][$key])) {
                        $product['name_' . $id] = $product['name']['en'];
                    } else {
                        $product['name_' . $id] = $product['name'][$key];
                    }
                    if (empty($product['description'][$key])) {
                        $product['description_' . $id] = $product['description']['en'];
                    } else {
                        $product['description_' . $id] = $product['description'][$key];
                    }
                }
                unset($product['name']);
                unset($product['description']);
                $this->CI->Store_products_model->save_info_product(null,
                    $product);
            }
        }

        // Products categories
        if (!empty($demo_content['products_categories'])) {
            foreach ($demo_content['products_categories'] as $data) {
                $this->CI->Store_categories_model->set_category_by_product_id($data);
            }
        }

        // Bestsellers
        if (!empty($demo_content['bestsellers'])) {
            $this->CI->load->model('store/models/Store_bestsellers_model');
            foreach ($demo_content['bestsellers'] as $bestseller) {
                $this->CI->Store_bestsellers_model->save_bestseller($bestseller);
            }
        }

        // Shippings
        $this->CI->load->model('store/models/Store_shippings_model');
        if (!empty($demo_content['shippings'])) {
            foreach ($demo_content['shippings'] as $shipping) {
                foreach ($lang as $key => $id) {
                    if (empty($shipping['name'][$key])) {
                        $shipping['name_' . $id] = $shipping['name']['en'];
                    } else {
                        $shipping['name_' . $id] = $shipping['name'][$key];
                    }
                    if (empty($shipping['description'][$key])) {
                        $shipping['description_' . $id] = $shipping['description']['en'];
                    } else {
                        $shipping['description_' . $id] = $shipping['description'][$key];
                    }
                }
                unset($shipping['name']);
                unset($shipping['description']);
                $attrs['info'] = $shipping;
                $this->CI->Store_shippings_model->save_shipping(null, $attrs);
            }
        }

        // Shippings countries
        if (!empty($demo_content['shippings_countries'])) {
            foreach ($demo_content['shippings_countries'] as $sh_countries) {
                $this->CI->Store_shippings_model->save_shipping_location($sh_countries['id_shippings'],
                    $sh_countries['locations']);
            }
        }

        return true;
    }
}