<?php

namespace Pg\Modules\Kisses\Controllers;

use Pg\Libraries\View;

/**
 * Kisses admin side controller
 *
 * @package PG_DatingPro
 * @subpackage Kisses
 *
 * @category	controllers
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * */
class Kisses extends \Controller
{
    private $_user_id;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Menu_model');
        $this->load->model('Kisses_model');
        $this->load->model('Uploads_model');
        $this->load->model('Users_model');
        if ('user' === $this->session->userdata('auth_type')) {
            $this->_user_id = intval($this->session->userdata('user_id'));
        }
    }

    /**
     * Return page with list kisses
     *
     * @param string  $folder type page
     * @param integer $page   number page
     *
     * @return string
     */
    private function _view($folder, $page = 1)
    {
        $user_id = $this->session->userdata('user_id');
        $this->view->assign('user_id', $user_id);

        /// breadcrumbs
        $this->Menu_model->breadcrumbs_set_parent('kisses_item');
        $this->Menu_model->breadcrumbs_set_active(l($folder, 'kisses'));

        switch ($folder) {
            case 'inbox' : $params['where']['user_to'] = $user_id; break;
            case 'outbox' : $params['where']['user_from'] = $user_id; break;
        }

        if (!$this->pg_module->get_module_config('kisses', 'system_settings_page')) {
            $items_on_page = $this->pg_module->get_module_config('kisses', 'items_per_page');
        } else {
            $items_on_page = $this->pg_module->get_module_config('start', 'index_items_per_page');
        }

        $order_by = array('date_created' => 'DESC');

        $kisses = $this->Kisses_model->get_list_user_kisses($params, $page, $items_on_page, $order_by);

        foreach ($kisses as $key => $media) {
            $kisses[$key]['images'] = $this->Uploads_model->format_upload($this->Kisses_model->image_upload_gid, $media['user_from'], $media['image']);
        }

        $upload_config_id = $this->Users_model->upload_config_id;

        $i = 0;
        foreach ($kisses as $row) {
            switch ($folder) {
                case 'inbox' : $user = $row["user_from"]; $kisses[$i]['user_id'] = $user; $kisses[$i]['img_path'] = $user; break;
                case 'outbox' : $user = $row["user_to"]; $kisses[$i]['user_id'] = $user; $kisses[$i]['img_path'] = $user_id; break;
            }

            $userinfo = $this->Users_model->get_user_by_id($user);

            if (!empty($userinfo["user_logo"])) {
                $userinfo["user_logo"] = $this->Uploads_model->format_upload($upload_config_id, $user, $userinfo["user_logo"]);
                $kisses[$i]["user_logo"] = $userinfo["user_logo"]['thumbs'];
            } else {
                $userinfo["user_logo"] = $this->Uploads_model->format_default_upload($upload_config_id);
                $kisses[$i]["user_logo"] = $userinfo["user_logo"]['thumbs'];
            }

            $kisses[$i]["image"] = "kisses-" . $kisses[$i]["image"];

            if (!$row['mark'] && $folder == 'inbox') {
                $this->Kisses_model->_mark_as_read($row['id'], array('mark' => 1));
            }
            ++$i;
        }

        $kisses_count = $this->Kisses_model->get_count_kisses_users($params);

        $this->load->helper("navigation");
        $url = site_url() . 'kisses/' . $folder . '/';
        $page_data = get_user_pages_data($url, $kisses_count, $items_on_page, $page, 'briefPage');
        $this->config->load('date_formats', true);
        $page_data["date_format"] = $this->pg_date->get_format('date_literal', 'st');

        $this->view->assign('page_data', $page_data);
        $this->view->assign('kiss_section', $folder);
        $this->view->assign('kisses', $kisses);
        $this->view->render('index');
    }

    public function index()
    {
        $this->inbox();
    }
    public function inbox($page = 1)
    {
        $this->_view('inbox', $page);
    }
    public function outbox($page = 1)
    {
        $this->_view('outbox', $page);
    }

    /**
     * Return list kisses
     *
     * @return string
     */
    public function ajax_get_kisses($object_id = null)
    {
        if (!$object_id) {
            return array();
        }

        $kisses = $this->Kisses_model->get_list(1, 0); // 0 - unlimited

        $image_upload_config = $this->Uploads_model->get_config($this->Kisses_model->image_upload_gid);

        $mediafile = array();
        $file_url = '';

        foreach ($kisses as $media) {
            $mediafile[0] = $this->Uploads_model->format_upload($image_upload_config['gid'], '', $media['image']);
            $file_url = $mediafile[0]['url']  . "kisses-";
            break;
        }

        $maxlength = $this->pg_module->get_module_config('kisses', 'number_max_symbols');
        $this->view->assign('maxlength', $maxlength);

        $lang_id = $this->session->userdata('lang_id');
        $this->view->assign('lang_id', $lang_id);

        $this->view->assign('kisses', $kisses);
        $this->view->assign('file_url', $file_url);
        $this->view->assign('object_id', $object_id);
        $this->view->render("list_kisses", "user", 'kisses');
    }

    /**
     * Save cheked kiss
     *
     * @echo json array
     */
    public function ajax_set_kisses()
    {
        $return = array();

        $post_data['id'] = $this->input->post("kiss", true);
        $post_data['object_id'] = $this->input->post("object_id", true);
        $post_data['message'] = trim(strip_tags($this->input->post("message", true)));

        $validate_data = $this->Kisses_model->validate_kisses($post_data['id'], $post_data);

        if (empty($validate_data['errors'])) {
            $user_id = $this->session->userdata("user_id");

            $image_upload_config = $this->Uploads_model->get_config($this->Kisses_model->image_upload_gid);

            $file_path = $this->Uploads_model->format_upload($image_upload_config['gid'], '', $validate_data['data']['kisses']['image']);

            $return = $this->Uploads_model->upload_exist($this->Kisses_model->image_upload_gid, $user_id, $file_path['file_path']);

            if (empty($return['error']) && !empty($return['file'])) {
                $data['image'] = $return['file'];
                $data['user_to'] = $validate_data['data']['object_id'];
                $data['user_from'] = $user_id;
                if (!empty($validate_data['data']['message'])) {
                    $data['message'] = $validate_data['data']['message'];
                }
                $return['save'] = $this->Kisses_model->save_user_kisses($data);
                if ($return['save']) {
                    $return['success'] = l('success_send_kiss', 'kisses');
                } else {
                    $return['error'] = l('error_send_kiss', 'kisses');
                }
            }
        } else {
            $return['error'] = implode('<br>', $validate_data['errors']);
        }

        $this->view->assign($return);
        $this->view->render();
    }
}
