<?php

namespace Pg\Modules\File_uploads\Controllers;

use Pg\Libraries\View;

/**
 * File uploads admin side controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Katya Kashkova <katya@pilotgroup.net>
 * */
class Admin_File_uploads extends \Controller
{
    private $allow_config_add = false;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Menu_model');
        $this->Menu_model->set_menu_active_item('admin_menu', 'system-items');

        $this->load->model('file_uploads/models/File_uploads_config_model');
    }

    public function index()
    {
        $this->configs();
    }

    public function configs()
    {
        $this->view->assign('configs', $this->File_uploads_config_model->get_config_list());
        $this->view->assign('allow_config_add', $this->allow_config_add);

        $this->Menu_model->set_menu_active_item('admin_file_uploads_menu', 'configs_list_item');
        $this->view->setHeader(l('admin_header_configs_list', 'file_uploads'));
        $this->view->render('list_settings');
    }

    public function config_edit($config_id = null)
    {
        if ($config_id) {
            $data = $this->File_uploads_config_model->get_config_by_id($config_id);
        } else {
            $data = array();
        }

        if ($this->input->post('btn_save')) {
            $post_data = array(
                "name"         => $this->input->post('name', true),
                "gid"          => $this->input->post('gid', true),
                "max_size"     => $this->input->post('max_size', true),
                "name_format"  => $this->input->post('name_format', true),
                "file_formats" => $this->input->post('file_formats', true),
            );
            $validate_data = $this->File_uploads_config_model->validate_config($config_id, $post_data);
            if (!empty($validate_data["errors"])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data["errors"]);
                $data = array_merge($data, $validate_data["data"]);
            } else {
                $data = $validate_data["data"];
                $this->File_uploads_config_model->save_config($config_id, $data);

                if ($config_id) {
                    $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_updated_config', 'file_uploads'));
                } else {
                    $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_added_config', 'file_uploads'));
                }

                $url = site_url('admin/file_uploads/configs');
                $this->view->setRedirect($url);
            }
        }

        $this->view->assign('data', $this->File_uploads_config_model->format_config($data));
        $this->view->assign('formats', $this->File_uploads_config_model->file_categories);
        $this->view->assign('lang_name_format', ld('upload_name_format', 'file_uploads'));
        $this->view->setHeader(l('admin_header_configs_list', 'file_uploads'));
        $this->view->render('edit_settings');
    }

    public function config_delete($config_id)
    {
        $this->File_uploads_config_model->delete_config($config_id);
        $url = site_url('admin/file_uploads/configs');
        $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_deleted_config', 'file_uploads'));
        $this->view->setRedirect($url);
    }
}
