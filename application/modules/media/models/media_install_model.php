<?php

namespace Pg\Modules\Media\Models;

/**
 * Media install model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Mikhail Chernov <katya@pilotgroup.net>
 *
 * @version $Revision: 2 $ $Date: 2010-04-02 15:07:07 +0300 (Ср, 02 апр 2010) $ $Author: mchernov $
 * */
class Media_install_model extends \Model
{
    protected $CI;

    protected $action_config = array(
        'media_upload_image' => array(
            'is_percent' => 0,
            'once' => 0,
            'available_period' => array(
                'all'),
            ),
        'media_upload_video' => array(
            'is_percent' => 0,
            'once' => 0,
            'available_period' => array(
                'all'),
            ),
        'media_upload_audio' => array(
            'is_percent' => 0,
            'once' => 0,
            'available_period' => array(
                'all'),
            ),  
    );

    protected $menu = array(
        'admin_menu' => array(
            'action' => 'none',
            'items'  => array(
                'system_items' => array(
                    'action' => 'none',
                    'items'  => array(
                        'media_menu_item' => array('action' => 'create', 'link' => 'admin/media', 'icon' => 'picture-o', 'status' => 1, 'sorter' => 2),
                    ),
                ),
            ),
        ),
        'media_menu_item' => array(
            'action' => 'create',
            'name'   => 'Media section menu',
            'items'  => array(
                'photo_list_item'    => array('action' => 'create', 'link' => 'admin/media/index', 'status' => 1, 'sorter' => 1),
                'video_list_item'    => array('action' => 'create', 'link' => 'admin/media/index/video', 'status' => 1, 'sorter' => 2),
                'album_list_item'    => array('action' => 'create', 'link' => 'admin/media/index/album', 'status' => 1, 'sorter' => 4),
                'common_albums_item' => array('action' => 'create', 'link' => 'admin/media/common_albums', 'status' => 1, 'sorter' => 5),
            ),
        ),
        'user_top_menu' => array(
            'action' => 'none',
            'items'  => array(
                'user-menu-activities' => array(
                    'action' => 'none',
                    'items'  => array(
                        'media_gallery_item' => array('action' => 'create', 'link' => 'media/all', 'status' => 1, 'sorter' => 10),
                    ),
                ),
            ),
        ),
    );

    /**
     * Ratings configuration
     *
     * @var array
     */
    protected $ratings = array(
        "ratings_fields" => array(
            "rating_data"      => array("type" => "TEXT", "null" => true),
            "rating_count"     => array("type" => "smallint(5)", "null" => false),
            "rating_sorter"    => array("type" => "decimal(5,3)", "null" => false),
            "rating_value"     => array("type" => "decimal(5,3)", "null" => false),
            "rating_type"      => array("type" => "varchar(20)", "null" => false),
        ),

        "ratings" => array(
            array("gid" => "media_object", "name" => "Ratings for media content", "rate_type" => "stars", "module" => "media", "model" => "media", "callback" => "callback_ratings"),
        ),

        "rate_types" => array(
            "stars" => array(
                "main" => array(1, 2, 3, 4, 5),
                "dop1" => array(1, 2, 3, 4, 5),
                "dop2" => array(1, 2, 3, 4, 5),
            ),
            /*
            "hands" => array(
                "main" => array(1, 5),
                "dop1" => array(1, 5),
                "dop2" => array(1, 5),
            ),
            */
        ),
    );

    protected $moderation_types = array(
        array(
            'name'                 => 'media_content',
            'mtype'                => '0',
            'module'               => 'media',
            'model'                => 'Media_model',
            'check_badwords'       => '1',
            'method_get_list'      => '_moder_get_list',
            'method_set_status'    => '_moder_set_status',
            'method_delete_object' => '',
            'method_mark_adult'    => '_moder_mark_adult',
            'allow_to_decline'     => '1',
            'template_list_row'    => 'moder_block',
        ),
    );

    protected $wall_events = array(
        'video_upload' => array(
            'gid'      => 'video_upload',
            'settings' => array(
                'join_period' => 10, // minutes, do not use
                'permissions' => array(
                    'permissions' => 3, // permissions 0 - only for me, 1 - for me and friends, 2 - for registered, 3 - for all
                    'feed'        => 1, // show friends events in user feed
                ),
            ),
        ),
        'image_upload' => array(
            'gid'      => 'image_upload',
            'settings' => array(
                'join_period' => 10, // minutes, do not use
                'permissions' => array(
                    'permissions' => 3, // permissions 0 - only for me, 1 - for me and friends, 2 - for registered, 3 - for all
                    'feed'        => 1, // show friends events in user feed
                ),
            ),
        ),
    );

    /**
     * Spam configuration
     *
     * @var array
     */
    protected $spam = array(
        array("gid" => "media_object", "form_type" => "select_text", "send_mail" => true, "status" => true, "module" => "media", "model" => "Media_model", "callback" => "spam_callback"),
    );

    /**
     * Aviary configuration
     *
     * @var array
     */
    protected $aviary = array(
        array(
            'module_gid' => 'media',
            'model_name' => 'Media_model',
            'method'     => 'save_aviary',
        ),
    );

    /**
     * Indicators configuration
     *
     * @var
     */
    protected $menu_indicators = array(
        array(
            'gid'               => 'new_moderation_item',
            'delete_by_cron'    => false,
            'auth_type'         => 'admin',
        ),
    );

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
        $this->CI->load->model('Install_model');

        if (SOCIAL_MODE) {
            $this->dynamic_blocks = include MODULEPATH . 'media/install/dynamic_blocks_social.php';
        } else {
            $this->dynamic_blocks = include MODULEPATH . 'media/install/dynamic_blocks_dating.php';
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
        $this->CI->load->model("bonuses/models/Bonuses_util_model");
        $langs_file = $this->CI->Install_model->language_file_read("bonuses", "ds", $langs_ids);

        if (!$langs_file) {
            log_message("info", "Empty bonuses langs data");
            return false;
        }
        $this->CI->Bonuses_util_model->update_langs($langs_file);
        $this->CI->load->model("bonuses/models/Bonuses_actions_config_model");
        $this->CI->Bonuses_actions_config_model->setActionsConfig($this->action_config);
        
        return true;
    }

    public function install_bonuses_lang_export()
    {

    }

    public function uninstall_bonuses()
    {

    }


    public function install_uploads()
    {
        // upload config
        $this->CI->load->model('uploads/models/Uploads_config_model');
        $config_data = array(
            'gid'          => 'gallery_image',
            'name'         => 'Gallery image',
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
            'prefix'       => 'great',
            'width'        => 305,
            'height'       => 305,
            'effect'       => 'none',
            'watermark_id' => $wm_id,
            'crop_param'   => 'crop',
            'crop_color'   => 'ffffff',
            'date_add'     => date('Y-m-d H:i:s'),
        );
        $this->CI->Uploads_config_model->save_thumb(null, $thumb_data);

        $thumb_data = array(
            'config_id'    => $config_id,
            'prefix'       => 'hgreat',
            'width'        => 305,
            'height'       => 200,
            'effect'       => 'none',
            'watermark_id' => $wm_id,
            'crop_param'   => 'crop',
            'crop_color'   => 'ffffff',
            'date_add'     => date('Y-m-d H:i:s'),
        );
        $this->CI->Uploads_config_model->save_thumb(null, $thumb_data);

        $thumb_data = array(
            'config_id'    => $config_id,
            'prefix'       => 'vgreat',
            'width'        => 200,
            'height'       => 305,
            'effect'       => 'none',
            'watermark_id' => $wm_id,
            'crop_param'   => 'crop',
            'crop_color'   => 'ffffff',
            'date_add'     => date('Y-m-d H:i:s'),
        );
        $this->CI->Uploads_config_model->save_thumb(null, $thumb_data);

        $thumb_data = array(
            'config_id'    => $config_id,
            'prefix'       => 'big',
            'width'        => 200,
            'height'       => 200,
            'effect'       => 'none',
            'watermark_id' => $wm_id,
            'crop_param'   => 'crop',
            'crop_color'   => 'ffffff',
            'date_add'     => date('Y-m-d H:i:s'),
        );
        $this->CI->Uploads_config_model->save_thumb(null, $thumb_data);

        $thumb_data = array(
            'config_id'    => $config_id,
            'prefix'       => 'middle',
            'width'        => 100,
            'height'       => 100,
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
        $this->CI->load->model('uploads/models/Uploads_config_model');
        $config_data = $this->CI->Uploads_config_model->get_config_by_gid('gallery_image');
        if (!empty($config_data['id'])) {
            $this->CI->Uploads_config_model->delete_config($config_data['id']);
        }
    }

    public function install_comments()
    {
        $this->CI->load->model('comments/models/Comments_types_model');
        $comment_type = array(
            'gid'           => 'media',
            'module'        => 'media',
            'model'         => 'Media_model',
            'method_count'  => 'comments_count_callback',
            'method_object' => 'comments_object_callback',
        );
        $this->CI->Comments_types_model->add_comments_type($comment_type);
    }

    public function install_comments_lang_update($langs_ids = null)
    {
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }
        $langs_file = $this->CI->Install_model->language_file_read('media', 'comments', $langs_ids);

        if (!$langs_file) {
            log_message('info', 'Empty moderation langs data');

            return false;
        }
        $this->CI->load->model('comments/models/Comments_types_model');
        $this->CI->Comments_types_model->update_langs(array('media'), $langs_file);
    }

    public function install_comments_lang_export($langs_ids = null)
    {
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }
        $this->CI->load->model('comments/models/Comments_types_model');

        return array('comments' => $this->CI->Comments_types_model->export_langs(array('media'), $langs_ids));
    }

    public function deinstall_comments()
    {
        $this->CI->load->model('comments/models/Comments_types_model');
        $this->CI->Comments_types_model->delete_comments_type('media');
    }

    /**
     * Install users data to ratings module
     *
     * @return void
     */
    public function install_ratings()
    {
        $this->CI->load->model("Media_model");

        // add ratings type
        $this->CI->load->model("ratings/models/Ratings_type_model");

        $this->CI->Media_model->install_ratings_fields((array) $this->ratings["ratings_fields"]);

        foreach ((array) $this->ratings["ratings"] as $rating_data) {
            $validate_data = $this->CI->Ratings_type_model->validate_type(null, $rating_data);
            if (!empty($validate_data["errors"])) {
                continue;
            }
            $this->CI->Ratings_type_model->save_type(null, $validate_data["data"]);
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
        $this->CI->load->model("Ratings_model");

        $langs_file = $this->CI->Install_model->language_file_read("media", "ratings", $langs_ids);
        if (!$langs_file) {
            log_message("info", "Empty ratings langs data");

            return false;
        }

        foreach ((array) $this->ratings["ratings"] as $rating_data) {
            $this->CI->Ratings_model->update_langs($rating_data, $langs_file, $langs_ids);
        }

        foreach ($langs_ids as $lang_id) {
            foreach ((array) $this->ratings["rate_types"] as $type_gid => $type_data) {
                $types_data = array();
                foreach ($type_data as $rate_type => $votes) {
                    $votes_data = array();
                    foreach ($votes as $vote) {
                        $votes_data[$vote] = isset($langs_file[$type_gid . '_' . $rate_type . "_votes_" . $vote][$lang_id]) ?
                            $langs_file[$type_gid . '_' . $rate_type . "_votes_" . $vote][$lang_id] : $vote;
                    }
                    $types_data[$rate_type] = array(
                        "header" => $langs_file[$type_gid . '_' . $rate_type . "_header"][$lang_id],
                        "votes"  => $votes_data,
                    );
                }
                $this->CI->Ratings_model->add_rate_type($type_gid, $types_data, $lang_id);
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
        $this->CI->load->model("Ratings_model");
        $langs = array();
        foreach ((array) $this->ratings["ratings"] as $rating_data) {
            $langs = array_merge($langs, $this->CI->Ratings_model->export_langs($rating_data['gid'], $langs_ids));
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
        $this->CI->load->model("Media_model");

        //add ratings type
        $this->CI->load->model("ratings/models/Ratings_type_model");

        foreach ((array) $this->ratings["ratings"] as $rating_data) {
            $this->CI->Ratings_type_model->delete_type($rating_data["gid"]);
        }

        $this->CI->Media_model->deinstall_ratings_fields(array_keys((array) $this->ratings["ratings_fields"]));
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
            'gid'             => 'gallery_video',
            'name'            => 'Gallery video',
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
        $config_data = $this->CI->Video_uploads_config_model->get_config_by_gid('gallery_video');
        if (!empty($config_data["id"])) {
            $this->CI->Video_uploads_config_model->delete_config($config_data["id"]);
        }
    }

    public function install_audio_uploads()
    {
        $this->CI->load->model('audio_uploads/models/Audio_uploads_config_model');
        $config_data = array(
                        array(
                                'gid'           => 'gallery_audio',
                                'name'          => 'Gallery audio',
                                'max_size'      => 10000000,
                                'file_formats'  => 'a:2:{i:0;s:3:"mp3";i:1;s:3:"wav";}',
                                'upload_type'   => 'local',
                                'date_add'      => date('Y-m-d H:i:s'),
                                'module'        => 'wall_events',
                                'model'         => 'Wall_events_model',
                                'method_status' => 'audio_callback',
                        ),
                        array(
                                'gid'           => 'wall-audio',
                                'name'          => 'Wall audio',
                                'max_size'      => 10000000,
                                'file_formats'  => 'a:2:{i:0;s:3:"mp3";i:1;s:3:"wav";}',
                                'upload_type'   => 'local',
                                'date_add'      => date('Y-m-d H:i:s'),
                                'module'        => 'wall_events',
                                'model'         => 'Wall_events_model',
                                'method_status' => 'audio_callback',
                        ),
                    );
        $this->CI->Audio_uploads_config_model->save_config(null, $config_data);
    }

    public function install_menu()
    {
        $this->CI->load->helper('menu');

        foreach ($this->menu as $gid => $menu_data) {
            $this->menu[$gid]['id'] = linked_install_set_menu($gid, $menu_data["action"], $menu_data["name"]);
            linked_install_process_menu_items($this->menu, 'create', $gid, 0, $this->menu[$gid]["items"]);
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
        $langs_file = $this->CI->Install_model->language_file_read('media', 'menu', $langs_ids);

        if (!$langs_file) {
            log_message('info', 'Empty menu langs data');

            return false;
        }

        $this->CI->load->model('Menu_model');
        $this->CI->load->helper('menu');

        foreach ($this->menu as $gid => $menu_data) {
            linked_install_process_menu_items($this->menu, 'update', $gid, 0, $this->menu[$gid]["items"], $gid, $langs_file);
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
        $this->CI->load->model('Menu_model');
        $this->CI->load->helper('menu');

        $return = array();
        foreach ($this->menu as $gid => $menu_data) {
            $temp = linked_install_process_menu_items($this->menu, 'export', $gid, 0, $this->menu[$gid]["items"], $gid, $langs_ids);
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
        $langs_file = $this->CI->Install_model->language_file_read('media', 'moderation', $langs_ids);

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

    public function install_wall_events()
    {
        $this->CI->load->model('wall_events/models/Wall_events_types_model');
        foreach ($this->wall_events as $wall_event) {
            $attrs = array(
                'gid'                 => $wall_event['gid'],
                'status'              => '1',
                'module'              => 'media',
                'model'               => 'media_model',
                'method_format_event' => '_format_wall_events',
                'date_add'            => date("Y-m-d H:i:s"),
                'date_update'         => date("Y-m-d H:i:s"),
                'settings'            => $wall_event['settings'],
            );
            $this->CI->Wall_events_types_model->add_wall_events_type($attrs);
        }

        return;
    }

    public function install_wall_events_lang_update($langs_ids = null)
    {
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }
        $langs_file = $this->CI->Install_model->language_file_read('media', 'wall_events', $langs_ids);
        if (!$langs_file) {
            log_message('info', 'Empty moderation langs data');

            return false;
        }
        $this->CI->load->model('wall_events/models/Wall_events_types_model');
        $this->CI->Wall_events_types_model->update_langs(array_keys($this->wall_events), $langs_file);
    }

    public function install_wall_events_lang_export($langs_ids = null)
    {
        if (!is_array($langs_ids)) {
            $langs_ids = (array) $langs_ids;
        }
        $this->CI->load->model('wall_events/models/Wall_events_types_model');

        return array('wall_events' => $this->CI->Wall_events_types_model->export_langs(array_keys($this->wall_events), $langs_ids));
    }

    public function deinstall_wall_events()
    {
        $this->CI->load->model('wall_events/models/Wall_events_types_model');
        foreach ($this->wall_events as $wall_event) {
            $this->CI->Wall_events_types_model->delete_wall_events_type($wall_event['gid']);
        }
    }

    public function install_site_map()
    {
        $this->CI->load->model('Site_map_model');
        $site_map_data = array(
            'module_gid'      => 'media',
            'model_name'      => 'Media_model',
            'get_urls_method' => 'get_sitemap_urls',
        );
        $this->CI->Site_map_model->set_sitemap_module('media', $site_map_data);
    }

    public function deinstall_site_map()
    {
        $this->CI->load->model('Site_map_model');
        $this->CI->Site_map_model->delete_sitemap_module('media');
    }

    public function _arbitrary_installing()
    {
        $seo_data = array(
            'module_gid'              => 'media',
            'model_name'              => 'Media_model',
            'get_settings_method'     => 'get_seo_settings',
            'get_rewrite_vars_method' => 'request_seo_rewrite',
            'get_sitemap_urls_method' => 'get_sitemap_xml_urls',
        );
        $this->CI->pg_seo->set_seo_module('media', $seo_data);

        $lang_dm_data = array(
            'module'        => 'media',
            'model'         => 'Albums_model',
            'method_add'    => 'lang_dedicate_module_callback_add',
            'method_delete' => 'lang_dedicate_module_callback_delete',
        );
        $this->CI->pg_language->add_dedicate_modules_entry($lang_dm_data);

        $this->CI->load->model('media/models/Albums_model');

        foreach ($this->CI->pg_language->languages as $id => $value) {
            $this->CI->Albums_model->lang_dedicate_module_callback_add($value['id']);
        }

        $this->add_demo_content();

        return;
    }

    public function add_demo_content()
    {
        $demo_content = include MODULEPATH . 'media/install/demo_content.php';

        $langs = $this->CI->pg_language->languages;

        // Albums
        $this->CI->load->model('media/models/Albums_model');
        foreach ($demo_content['albums'] as $album) {
            // Replace language code with ID
            foreach ($langs as $lid => $lang_data) {
                if (empty($album['langs'][$lang_data['code']])) {
                    $album['langs'][$lang_data['code']] = $album['name'];
                }
                $album['lang_' . $lid] = $album['langs'][$lang_data['code']];
            }
            unset($album['langs']);
            $this->CI->Albums_model->save(null, $album);
        }

        return true;
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
        $langs_file = $this->CI->Install_model->language_file_read("media", "arbitrary", $langs_ids);
        if (!$langs_file) {
            log_message("info", "Empty media arbitrary langs data");

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
            "priority"       => 0.6,
        );
        $this->CI->pg_seo->set_settings("user", "media", "index", $post_data);

        $post_data = array(
            "title"          => $langs_file["seo_tags_all_title"],
            "keyword"        => $langs_file["seo_tags_all_keyword"],
            "description"    => $langs_file["seo_tags_all_description"],
            "header"         => $langs_file["seo_tags_all_header"],
            "og_title"       => $langs_file["seo_tags_all_og_title"],
            "og_type"        => $langs_file["seo_tags_all_og_type"],
            "og_description" => $langs_file["seo_tags_all_og_description"],
            "priority"       => 0.6,
        );
        $this->CI->pg_seo->set_settings("user", "media", "all", $post_data);

        $post_data = array(
            "title"          => $langs_file["seo_tags_photo_title"],
            "keyword"        => $langs_file["seo_tags_photo_keyword"],
            "description"    => $langs_file["seo_tags_photo_description"],
            "header"         => $langs_file["seo_tags_photo_header"],
            "og_title"       => $langs_file["seo_tags_photo_og_title"],
            "og_type"        => $langs_file["seo_tags_photo_og_type"],
            "og_description" => $langs_file["seo_tags_photo_og_description"],
            "priority"       => 0.6,
        );
        $this->CI->pg_seo->set_settings("user", "media", "photo", $post_data);

        $post_data = array(
            "title"          => $langs_file["seo_tags_video_title"],
            "keyword"        => $langs_file["seo_tags_video_keyword"],
            "description"    => $langs_file["seo_tags_video_description"],
            "header"         => $langs_file["seo_tags_video_header"],
            "og_title"       => $langs_file["seo_tags_video_og_title"],
            "og_type"        => $langs_file["seo_tags_video_og_type"],
            "og_description" => $langs_file["seo_tags_video_og_description"],
            "priority"       => 0.6,
        );
        $this->CI->pg_seo->set_settings("user", "media", "video", $post_data);

        $post_data = array(
            "title"          => $langs_file["seo_tags_albums_title"],
            "keyword"        => $langs_file["seo_tags_albums_keyword"],
            "description"    => $langs_file["seo_tags_albums_description"],
            "header"         => $langs_file["seo_tags_albums_header"],
            "og_title"       => $langs_file["seo_tags_albums_og_title"],
            "og_type"        => $langs_file["seo_tags_albums_og_type"],
            "og_description" => $langs_file["seo_tags_albums_og_description"],
            "priority"       => 0.6,
        );
        $this->CI->pg_seo->set_settings("user", "media", "albums", $post_data);

        $post_data = array(
            "title"          => $langs_file["seo_tags_audio_title"],
            "keyword"        => $langs_file["seo_tags_audio_keyword"],
            "description"    => $langs_file["seo_tags_audio_description"],
            "header"         => $langs_file["seo_tags_audio_header"],
            "og_title"       => $langs_file["seo_tags_audio_og_title"],
            "og_type"        => $langs_file["seo_tags_audio_og_type"],
            "og_description" => $langs_file["seo_tags_audio_og_description"],
            "priority"       => 0.6,
        );
        $this->CI->pg_seo->set_settings("user", "media", "audio", $post_data);
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

        //// arbitrary
        $settings = $this->CI->pg_seo->get_settings("user", "media", "index", $langs_ids);
        $arbitrary_return["seo_tags_index_title"] = $settings["title"];
        $arbitrary_return["seo_tags_index_keyword"] = $settings["keyword"];
        $arbitrary_return["seo_tags_index_description"] = $settings["description"];
        $arbitrary_return["seo_tags_index_header"] = $settings["header"];
        $arbitrary_return["seo_tags_index_og_title"] = $settings["og_title"];
        $arbitrary_return["seo_tags_index_og_type"] = $settings["og_type"];
        $arbitrary_return["seo_tags_index_og_description"] = $settings["og_description"];

        $settings = $this->CI->pg_seo->get_settings("user", "media", "all", $langs_ids);
        $arbitrary_return["seo_tags_all_title"] = $settings["title"];
        $arbitrary_return["seo_tags_all_keyword"] = $settings["keyword"];
        $arbitrary_return["seo_tags_all_description"] = $settings["description"];
        $arbitrary_return["seo_tags_all_header"] = $settings["header"];
        $arbitrary_return["seo_tags_all_og_title"] = $settings["og_title"];
        $arbitrary_return["seo_tags_all_og_type"] = $settings["og_type"];
        $arbitrary_return["seo_tags_all_og_description"] = $settings["og_description"];

        $settings = $this->CI->pg_seo->get_settings("user", "media", "photo", $langs_ids);
        $arbitrary_return["seo_tags_photo_title"] = $settings["title"];
        $arbitrary_return["seo_tags_photo_keyword"] = $settings["keyword"];
        $arbitrary_return["seo_tags_photo_description"] = $settings["description"];
        $arbitrary_return["seo_tags_photo_header"] = $settings["header"];
        $arbitrary_return["seo_tags_photo_og_title"] = $settings["og_title"];
        $arbitrary_return["seo_tags_photo_og_type"] = $settings["og_type"];
        $arbitrary_return["seo_tags_photo_og_description"] = $settings["og_description"];

        $settings = $this->CI->pg_seo->get_settings("user", "media", "video", $langs_ids);
        $arbitrary_return["seo_tags_video_title"] = $settings["title"];
        $arbitrary_return["seo_tags_video_keyword"] = $settings["keyword"];
        $arbitrary_return["seo_tags_video_description"] = $settings["description"];
        $arbitrary_return["seo_tags_video_header"] = $settings["header"];
        $arbitrary_return["seo_tags_video_og_title"] = $settings["og_title"];
        $arbitrary_return["seo_tags_video_og_type"] = $settings["og_type"];
        $arbitrary_return["seo_tags_video_og_description"] = $settings["og_description"];

        $settings = $this->CI->pg_seo->get_settings("user", "media", "albums", $langs_ids);
        $arbitrary_return["seo_tags_albums_title"] = $settings["title"];
        $arbitrary_return["seo_tags_albums_keyword"] = $settings["keyword"];
        $arbitrary_return["seo_tags_albums_description"] = $settings["description"];
        $arbitrary_return["seo_tags_albums_header"] = $settings["header"];
        $arbitrary_return["seo_tags_albums_og_title"] = $settings["og_title"];
        $arbitrary_return["seo_tags_albums_og_type"] = $settings["og_type"];
        $arbitrary_return["seo_tags_albums_og_description"] = $settings["og_description"];

        return array("arbitrary" => $arbitrary_return);
    }

    public function _arbitrary_deinstalling()
    {
        $this->CI->pg_seo->delete_seo_module('media');

        $lang_dm_data['where'] = array(
            'module' => 'media',
            'model'  => 'Albums_model',
        );
        $this->CI->pg_language->delete_dedicate_modules_entry($lang_dm_data);
    }

    /**
     * Install spam links
     */
    public function install_spam()
    {
        // add spam type
        $this->CI->load->model("spam/models/Spam_type_model");

        foreach ((array) $this->spam as $spam_data) {
            $validate_data = $this->CI->Spam_type_model->validate_type(null, $spam_data);
            if (!empty($validate_data["errors"])) {
                continue;
            }
            $this->CI->Spam_type_model->save_type(null, $validate_data["data"]);
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

        $this->CI->load->model("spam/models/Spam_type_model");

        $langs_file = $this->CI->Install_model->language_file_read("media", "spam", $langs_ids);
        if (!$langs_file) {
            log_message("info", "Empty spam langs data");

            return false;
        }

        $this->CI->Spam_type_model->update_langs($this->spam, $langs_file, $langs_ids);

        return true;
    }

    /**
     * Export spam languages
     *
     * @param array $langs_ids
     */
    public function install_spam_lang_export($langs_ids = null)
    {
        $this->CI->load->model("spam/models/Spam_type_model");
        $langs = $this->CI->Spam_type_model->export_langs((array) $this->spam, $langs_ids);

        return array("spam" => $langs);
    }

    /**
     * Uninstall spam links
     */
    public function deinstall_spam()
    {
        //add spam type
        $this->CI->load->model("spam/models/Spam_type_model");

        foreach ((array) $this->spam as $spam_data) {
            $this->CI->Spam_type_model->delete_type($spam_data["gid"]);
        }
    }

    public function install_users()
    {
        $this->CI->load->model('users/models/Users_delete_callbacks_model');
        $this->CI->Users_delete_callbacks_model->add_callback('media', 'Media_model', 'callback_user_delete', 'id_user', 'media_user');
        $this->CI->Users_delete_callbacks_model->add_callback('media', 'Media_model', 'callback_user_delete', 'id_owner', 'media_owner');
        $this->CI->Users_delete_callbacks_model->add_callback('media', 'Media_model', 'callback_user_delete', 'gallery', 'media_gallery');
    }

    public function deinstall_users()
    {
        $this->CI->load->model('users/models/Users_delete_callbacks_model');
        $this->CI->Users_delete_callbacks_model->delete_callbacks_by_module('media');
    }

    /**
     * Install upload gallery data to aviary module
     *
     * @return void
     */
    public function install_aviary()
    {
        $this->CI->load->model('Aviary_model');
        foreach ($this->aviary as $aviary) {
            $this->CI->Aviary_model->save_module(null, $aviary);
        }
    }

    /**
     * Uninstall upload gallery data from aviary module
     *
     * @return void
     */
    public function deinstall_aviary()
    {
        $this->CI->load->model('Aviary_model');
        foreach ($this->aviary as $aviary) {
            $this->CI->Aviary_model->delete_module($aviary['module_gid']);
        }
    }

    public function install_friendlist()
    {
        //lang ds for friendlist
        if ($this->CI->pg_module->is_module_installed("media")) {
            $this->CI->load->model('Media_model');
            $this->CI->Media_model->addFriendsMenu();
        }
    }

    public function deinstall_friendlist()
    {
        //lang ds for friendlist
        if ($this->CI->pg_module->is_module_installed("media")) {
            $this->CI->load->model('Media_model');
            $this->CI->Media_model->deleteFriendsMenu();
        }
    }
}
