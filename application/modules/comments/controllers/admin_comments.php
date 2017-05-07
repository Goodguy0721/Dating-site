<?php

namespace Pg\Modules\Comments\Controllers;

use Pg\Libraries\View;

/**
 * Admin comments controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Dmitry Popenov
 *
 * @version $Revision: 2 $ $Date: 2013-01-30 10:07:07 +0400 $
 **/
class Admin_Comments extends \Controller
{
    /**
     * Controller
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('comments/models/Comments_types_model');
        $this->load->model('Menu_model');
        $this->Menu_model->set_menu_active_item('admin_menu', 'system-items');
    }

    /*
     * COMMENTS FUNCTIONS
     */

    public function index($page = 1)
    {
        $items_on_page = $this->pg_module->get_module_config('start', 'admin_items_per_page');
        $this->load->helper('sort_order');
        $comments_types_cnt = $this->Comments_types_model->get_comments_types_cnt();
        $page = get_exists_page_number($page, $comments_types_cnt, $items_on_page);

        $this->load->helper("navigation");
        $url = site_url() . "admin/comments/index/";
        $pages_data = get_admin_pages_data($url, $comments_types_cnt, $items_on_page, $page, 'briefPage');
        $this->view->assign('page_data', $pages_data);

        $comments_types = $this->Comments_types_model->get_comments_types($page, $items_on_page);
        $this->view->assign('comments_types', $comments_types);

        $_SESSION["comments_types"]["page"] = $page;

        $this->Menu_model->set_menu_active_item('admin_comments_menu', 'comments_list_item');
        $this->view->setHeader(l('admin_header_list', 'comments'));
        $this->view->render('comments_types');
    }

    public function ajax_activate_type()
    {
        $id = $this->input->post('id', true);
        $status = $this->input->post('status', true) ? '1' : '0';
        $return['status'] = $this->Comments_types_model->save_comments_type($id, array('status' => $status));
        $this->view->assign($return);
    }

    public function edit_type($id)
    {
        if (empty($id)) {
            show_404();
        }

        $comments_type = $this->Comments_types_model->get_comments_type_by_id($id);

        if (empty($comments_type)) {
            show_404();
        }

        if (!empty($_SESSION['comments_types']['page'])) {
            $page = $_SESSION['comments_types']['page'];
        } else {
            $page = 1;
        }

        if ($this->input->post('btn_save')) {
            $post_data = array(
                'status'   => $this->input->post('status', true),
                'settings' => array(
                    'use_likes'      => $this->input->post('use_likes', true),
                    'use_spam'       => $this->input->post('use_spam', true),
                    'use_moderation' => $this->input->post('use_moderation', true),
                    'guest_access'   => $this->input->post('guest_access', true),
                    'char_count'     => $this->input->post('char_count', true),
                ),
            );

            $validate_data = $this->Comments_types_model->validate($id, $post_data);
            if (!empty($validate_data['errors'])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data['errors']);
            } else {
                $result = $this->Comments_types_model->save_comments_type($id, $validate_data['data']);
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_update_comment_data', 'comments'));
                redirect(site_url() . "admin/comments/index/{$page}");
            }

            $comments_type = array_merge($comments_type, $post_data);
        }

        $this->view->assign('comments_type', $comments_type);

        $data['page'] = isset($_SESSION['comments_types']['page']) && $_SESSION['comments_types']['page'] ? $_SESSION['comments_types']['page'] : 1;
        $data['action'] = site_url() . "admin/comments/edit_type/{$id}";
        $this->view->setHeader(l('admin_header_comments_type_change', 'comments') . ': ' . l('ctype_' . $comments_type['gid'], 'comments'));
        $this->view->assign('data', $data);
        $this->view->render('form_comments_type');
    }

    public function save_type($id)
    {
        $id = intval($id);
        if (!empty($_SESSION['comments_types']['page'])) {
            $page = $_SESSION['comments_types']['page'];
        } else {
            $page = 1;
        }
        if (!$id) {
            redirect(site_url() . "admin/comments/index/{$page}");
        }

        $post_data = array(
            'status'   => $this->input->post('status', true),
            'settings' => array(
                'use_likes'      => $this->input->post('use_likes', true),
                'use_spam'       => $this->input->post('use_spam', true),
                'use_moderation' => $this->input->post('use_moderation', true),
                'guest_access'   => $this->input->post('guest_access', true),
                'char_count'     => $this->input->post('char_count', true),
            ),
        );

        $validate_data = $this->Comments_types_model->validate($id, $post_data);
        if (!empty($validate_data['errors'])) {
            $this->system_messages->addMessage(View::MSG_ERROR, $validate_data['errors']);
        } else {
            $result = $this->Comments_types_model->save_comments_type($id, $validate_data['data']);
            $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_update_comment_data', 'comments'));
            redirect(site_url() . "admin/comments/index/{$page}");
        }
    }
}
