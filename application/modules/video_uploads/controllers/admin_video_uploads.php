<?php

namespace Pg\Modules\Video_uploads\Controllers;

use Pg\Libraries\View;

/**
 * Video Uploads admin side controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Katya Kashkova <katya@pilotgroup.net>
 *
 * @version $Revision: 2 $ $Date: 2010-04-02 15:07:07 +0300 (Ср, 02 апр 2010) $ $Author: kkashkova $
 * */
class Admin_Video_Uploads extends \Controller
{
    /**
     * link to CodeIgniter object
     *
     * @var object
     */

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Menu_model');
        $this->Menu_model->set_menu_active_item('admin_menu', 'system-items');
    }

    public function index()
    {
        $this->configs();
    }

    private function mainMenu()
    {
        $menu_data  = $this->Menu_model->get_menu_by_gid('admin_video_menu');
        $params = [];
        if (!$_ENV['YOUTUBE_SETTINGS']) {
           $params['where']['gid !='] = 'youtube_settings_item';
        }
        $menu_items = $this->Menu_model->get_menu_active_items_list($menu_data["id"], false, $params, 0);
        $this->view->assign("menu", $menu_items);
        return $this->view->fetch("level1_menu", null, 'menu');
        
    }

    public function configs()
    {
        $this->load->model('video_uploads/models/Video_uploads_config_model');
        $this->view->assign('configs', $this->Video_uploads_config_model->get_config_list());

        $this->Menu_model->set_menu_active_item('admin_uploads_menu', 'configs_list_item');
        $this->view->setHeader(l('admin_header_configs_list', 'video_uploads'));
        $this->view->assign('main_menu', $this->mainMenu());
        $this->view->render('list_settings');
    }

    public function config_edit($config_id)
    {
        $this->load->model('video_uploads/models/Video_uploads_config_model');
        $data = $this->Video_uploads_config_model->get_config_by_id($config_id);

        $this->load->model('video_uploads/models/Video_uploads_settings_model');
        $settings = $this->Video_uploads_settings_model->get_settings();

        if ($this->input->post('btn_save')) {
            $post_data = array(
                "name" => $this->input->post('name', true),
                "max_size" => $this->input->post('max_size', true),
                "upload_type" => $this->input->post('upload_type', true),
                "file_formats" => $this->input->post('file_formats', true),
                "use_convert" => $this->input->post('use_convert', true),
                "local_settings" => $this->input->post('local_settings', true),
                "youtube_settings" => $this->input->post('youtube_settings',
                    true),
                "use_thumbs" => $this->input->post('use_thumbs', true),
                "thumbs_settings" => $this->input->post('thumbs_settings', true),
                "default_img" => $_FILES['default_img'],
            );

            $validate_data = $this->Video_uploads_config_model->validate_config($config_id,
                $post_data);

            if (!empty($validate_data["errors"])) {
                $this->system_messages->addMessage(View::MSG_ERROR,
                    $validate_data["errors"]);
                $validate_data["data"] = $this->Video_uploads_config_model->format_config($validate_data["data"]);
                $data  = array_merge($data, $validate_data["data"]);
            } else {
                if ($this->input->post('default_img_delete', true)) {
                    $this->load->model('Video_uploads_model');
                    $this->Video_uploads_model->delete_thumbs($data["default_img"], $this->Video_uploads_config_model->default_path, $data['thumbs_settings']);
                    $validate_data["data"]["default_img"] = "";
                }

                $this->Video_uploads_config_model->save_config($config_id,
                    $validate_data["data"], 'default_img');
                $this->system_messages->addMessage(View::MSG_SUCCESS,
                    l('success_updated_config', 'video_uploads'));
                $url = site_url() . "admin/video_uploads/index";
                redirect($url);
            }
        }

        $this->view->assign('data', $data);
        $this->view->assign('settings', $settings);

        $this->view->assign('formats',
            $this->Video_uploads_config_model->file_formats);
        $this->view->setHeader(l('admin_header_config_form', 'video_uploads'));
        $this->view->render('edit_settings');
    }

    ///// local converting settings

    public function system_settings()
    {
        $this->load->model('video_uploads/models/Video_uploads_settings_model');

        if ($this->input->post('btn_save')) {
            $this->Video_uploads_settings_model->set_settings('ffmpeg_path',
                trim(strip_tags($this->input->post('ffmpeg_path', true))));
            $this->Video_uploads_settings_model->set_settings('mencoder_path',
                trim(strip_tags($this->input->post('mencoder_path', true))));
            $this->Video_uploads_settings_model->set_settings('flvtool2_path',
                trim(strip_tags($this->input->post('flvtool2_path', true))));
            $this->Video_uploads_settings_model->set_settings('mplayer_path',
                trim(strip_tags($this->input->post('mplayer_path', true))));
            $this->Video_uploads_settings_model->reculc_permission_settings();
            $this->Video_uploads_settings_model->write_settings();
            $this->system_messages->addMessage(View::MSG_SUCCESS,
                l('success_update_system_settings', 'video_uploads'));
            redirect(site_url() . "admin/video_uploads/system_settings");
        }

        //// check  shell_exec || ffmpeg-php changing
        if (!$this->Video_uploads_settings_model->is_shell_exec_exist()) {
            $this->Video_uploads_settings_model->reculc_permission_settings();
            $this->Video_uploads_settings_model->write_settings();
        }

        $settings = $this->Video_uploads_settings_model->get_settings();
        $this->view->assign('settings', $settings);

        if ($settings["local_converting_video_type"]) {
            $coder_path         = $settings[$settings["local_converting_video_type"] . "_path"];
            $get_codecs_method  = "get_" . $settings["local_converting_video_type"] . "_codecs";
            $get_version_method = "get_" . $settings["local_converting_video_type"] . "_codecs";
            if ($coder_path) {
                $codecs = $this->Video_uploads_settings_model->{$get_codecs_method}();
                $this->view->assign('codecs', $codecs);
            }
        }
        $versions = array();
        if ($settings["ffmpeg_path"]) {
            $versions["ffmpeg"] = $this->Video_uploads_settings_model->get_ffmpeg_version();
        }

        if ($settings["mencoder_path"]) {
            $versions["mencoder"] = $this->Video_uploads_settings_model->get_mencoder_version();
        }

        if ($settings["flvtool2_path"]) {
            $versions["flvtool2"] = $this->Video_uploads_settings_model->get_flvtool2_version();
        }

        if ($settings["mplayer_path"]) {
            $versions["mplayer"] = $this->Video_uploads_settings_model->get_mplayer_version();
        }

        $this->view->assign('versions', $versions);

        //// php ini settings
        $php_ini['post_max_size']       = ini_get('post_max_size');
        $php_ini['upload_max_filesize'] = ini_get('upload_max_filesize');
        $php_ini['max_size_notice']     = str_replace('[max_size]',
            $php_ini['upload_max_filesize'],
            l('upload_max_size_notice', 'video_uploads'));

        $this->view->assign('php_ini', $php_ini);
        $this->view->assign('main_menu', $this->mainMenu());

        $this->Menu_model->set_menu_active_item('admin_uploads_menu',   'system_list_itemm');
        $this->view->setHeader(l('admin_header_local_settings_list',
                'video_uploads'));
        $this->view->render('system_settings');
    }

    public function system_settings_reset()
    {
        $this->load->model('video_uploads/models/Video_uploads_settings_model');
        $this->Video_uploads_settings_model->reculc_settings();
        $this->Video_uploads_settings_model->write_settings();

        $this->system_messages->addMessage(View::MSG_SUCCESS,
            l('success_reset_system_settings', 'video_uploads'));
        redirect(site_url() . "admin/video_uploads/system_settings");
    }

    public function youtube_settings()
    {
        if (!empty($_ENV['YOUTUBE_SETTINGS']) && $_ENV['YOUTUBE_SETTINGS'] == 1) {

            $this->load->model('video_uploads/models/Video_uploads_youtube_model');

            if ($this->input->post('btn_save')) {
                $this->Video_uploads_youtube_model->set_settings('youtube_converting_login',
                    trim(strip_tags($this->input->post('youtube_converting_login',
                                true))));
                $this->Video_uploads_youtube_model->set_settings('youtube_converting_password',
                    trim(strip_tags($this->input->post('youtube_converting_password',
                                true))));
                $this->Video_uploads_youtube_model->set_settings('youtube_converting_developer_key',
                    trim(strip_tags($this->input->post('youtube_converting_developer_key',
                                true))));
                $this->Video_uploads_youtube_model->set_settings('youtube_converting_source',
                    trim(strip_tags($this->input->post('youtube_converting_source',
                                true))));
                $this->Video_uploads_youtube_model->reculc_permission_settings();
                $this->Video_uploads_youtube_model->write_settings();
                $this->system_messages->addMessage(View::MSG_SUCCESS,
                    l('success_update_youtube_settings', 'video_uploads'));
                redirect(site_url() . "admin/video_uploads/youtube_settings");
            }

            $settings = $this->Video_uploads_youtube_model->get_settings();
            $this->view->assign('settings', $settings);

            $this->Menu_model->set_menu_active_item('admin_uploads_menu',
                'youtube_settings_item');
            $this->view->setHeader(l('admin_header_youtube_settings_list',
                    'video_uploads'));
            $this->view->render('youtube_settings');
        } else {
            $this->configs();
        }
    }

    public function youtube_settings_check()
    {
        $this->load->model('video_uploads/models/Video_uploads_youtube_model');
        $ret = $this->Video_uploads_youtube_model->youtube_auth();

        if ($ret["error"]) {
            $this->system_messages->addMessage(View::MSG_ERROR, $ret["error"]);
        } else {
            $this->system_messages->addMessage(View::MSG_SUCCESS,
                l('success_youtube_auth', 'video_uploads'));
        }
        redirect(site_url() . "admin/video_uploads/youtube_settings");
    }
}