<?php

/**
 * Clubs module
 *
 * @package     PG_Dating
 * @copyright   Copyright (c) 2000-2014 PG Dating Pro - php dating software
 * @author      Renat Gabdrakhmanov <renatgab@pilotgroup.net>
 */

namespace Pg\Modules\Clubs\Controllers;

use Pg\Libraries\View;

/**
 * Clubs admin side controller
 * 
 * @package     PG_Dating
 * @subpackage  Clubs
 * @category    controllers
 * @copyright   Copyright (c) 2000-2014 PG Dating Pro - php dating software
 * @author      Renat Gabdrakhmanov <renatgab@pilotgroup.net>
 */
class Admin_Clubs extends \Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['Clubs_model', 'Menu_model']);
        $this->Menu_model->set_menu_active_item('admin_menu', 'add_ons_items');
    }

    public function index($order = null, $order_direction = null, $page = null)
    {
        $clubs_settings = isset($_SESSION['clubs_list']) ? $_SESSION['clubs_list'] : [];
        if (!isset($clubs_settings['order'])) {
            $clubs_settings['order'] = 'date_add';
        }
        if (!isset($clubs_settings['order_direction'])) {
            $clubs_settings['order_direction'] = 'DESC';
        }
        if (!isset($clubs_settings['page'])) {
            $clubs_settings['page'] = 1;
        }

        $sort_links = array(
            'date_add' => site_url() . 'admin/clubs/index/date_add/' . (($order != 'date_add' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),            
        );
        $this->view->assign('sort_links', $sort_links);
        
        if (!$order) {
            $order = $clubs_settings['order'];
        }
        if (!$order_direction) {
            $order_direction = $clubs_settings['order_direction'];
        }
        if (!$page) {
            $page = $clubs_settings['page'];
        }
        
        $clubs_settings['order'] = $order;
        $clubs_settings['order_direction'] = $order_direction;
        $clubs_settings['page'] = $page;
        
        $this->view->assign('order', $order);
        $this->view->assign('order_direction', $order_direction);

        $clubs_count = $this->Clubs_model->getCount();

        $this->load->helper('sort_order');
        $items_on_page = $this->pg_module->get_module_config('start', 'admin_items_per_page');
        $page = get_exists_page_number($page, $clubs_count, $items_on_page);
        $clubs_settings['page'] = $page;
        
        $_SESSION['clubs_list'] = $clubs_settings;

        if ($clubs_count > 0) {
            $data = $this->Clubs_model->getList([], $page, $items_on_page, array($order => $order_direction));
            $data = $this->Clubs_model->formatArray($data);
            $this->view->assign('clubs_list', $data);
        }

        $this->load->helper('navigation');
        $page_data = get_admin_pages_data(site_url() . 'admin/clubs/index/' . $order . '/' . $order_direction . '/', $clubs_count, $items_on_page, $page, 'briefPage');
        $this->view->assign('page_data', $page_data);

        $this->load->library('pg_date');
        $date_format = $this->pg_date->get_format('date_time_literal', 'st');
        $this->view->assign('date_format', $date_format);

        $this->view->setHeader(l('admin_header_list', 'clubs'));
        $this->view->setBackLink(site_url() . 'admin/start/menu/add_ons_items');
        $this->view->render('index');
    }

    public function edit($id = null) 
    {
        $this->load->model('Field_editor_model');
        $this->Field_editor_model->initialize($this->Clubs_model->form_editor_type);
        $sections   = $this->Field_editor_model->get_section_list();
        $sections_gids = $fields_for_select = [];
        if (!empty($sections)) {
            foreach ($sections as $key => $section) {
                $sections_gids[] = $section['gid'];
            }
            $fields_for_select = $this->Field_editor_model->get_fields_for_select($sections_gids);
            $this->Clubs_model->setAdditionalFields($fields_for_select);
        }

        if (!is_null($id)) {
            $club = $this->Clubs_model->getObject(['id' => $id]);
        } else {
            $club = [];
        }

        if ($this->input->post('btn_save')) {
            $post_data = [
                'title'               => $this->input->post('title', true), 
                'description'         => $this->input->post('description', true), 
                'category_gid'        => $this->input->post('category_gid', true), 
                'country_code'        => $this->input->post('country_code', true), 
                'region_id'           => $this->input->post('region_id', true), 
                'city_id'             => $this->input->post('city_id', true), 
                'address'             => $this->input->post('address', true), 
                'lat'                 => $this->input->post('lat', true), 
                'lon'                 => $this->input->post('lon', true), 
            ];

            foreach ($fields_for_select as $field) {
                $post_data[$field] = $this->input->post($field, true);
            }

            $validate_data = $this->Clubs_model->validate($id, $post_data, 'image', $sections_gids);

            if (!empty($validate_data['errors'])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data['errors']);
                $club = $validate_data['data'];
            } else {
                $save_data = $validate_data['data'];

                if (empty($id)) {
                    $save_data['is_active'] = 0;
                }

                if ($this->input->post('club_icon_delete') || (isset($_FILES['image'])
                    && is_array($_FILES['image']) && is_uploaded_file($_FILES['image']['tmp_name']))) {
                    $this->load->model('Uploads_model');
                    if (isset($club['image']) && $club['image']) {
                        $this->Uploads_model->delete_upload($this->Clubs_model->upload_config_gid,
                            $id, $club['image']);
                        $save_data['image'] = '';
                    }
                }

                $id = $this->Clubs_model->save($id, $save_data, 'image');

                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_save_clubs', 'clubs'));
                $this->view->setRedirect(site_url() . 'admin/clubs/index');
            }
        }

        // $this->load->plugin('fckeditor');
        // $description_fck = create_editor(
        //     'description', 
        //     array_key_exists('description', $club) ? $club['description'] : '', 
        //     300, 
        //     200, 
        //     'Middle'
        // );
        // $this->view->assign('clubs_description_fck', $description_fck);

        if (!empty($sections_gids)) {
            $params = [];
            $params['where_in']['section_gid'] = $sections_gids;
            $fields_data = $this->Field_editor_model->get_form_fields_list($club, $params);
            $this->view->assign('fields_data', $fields_data);
        }

        if (!empty($club)) {
            $club = $this->Clubs_model->format($club);
        }
        $this->view->assign('club', $club);

        $this->view->setHeader(l('admin_header_edit', 'clubs'));
        $this->view->setBackLink(site_url() . 'admin/clubs/index');
        $this->view->render('edit');
    }

    public function delete($id = null) 
    {
        if ($id) {
            $this->Clubs_model->delete($id);
            $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_delete_clubs', 'clubs'));
        }

        $this->view->setRedirect(site_url() . 'admin/clubs/index');
    }

    public function media($id = 0, $param = 'photo', $page = 1) 
    {
        $club = $this->Clubs_model->getObject(['id' => $id]);
        if (empty($club)) {
            $this->view->setRedirect(site_url() . 'admin/clubs');
        }

        $this->load->model('clubs/models/Clubs_media_model');
        $filters = array();
        switch ($param) {
            case 'photo' : $filters['upload_gid'] = $this->Clubs_media_model->image_config_gid;
                break;
            case 'video' : $filters['upload_gid'] = $this->Clubs_media_model->video_config_gid;
                break;
        }
        $filters['club_id'] = $id;

        $this->load->helper('sort_order');
        $items_on_page = $this->pg_module->get_module_config('start', 'admin_items_per_page');
        $media_count = $this->Clubs_media_model->getCount($filters);
        $page = get_exists_page_number($page, $media_count, $items_on_page);

        $order_by = array('date_add' => 'DESC');
        $this->Clubs_media_model->format_user = true;
        $media_list = $this->Clubs_media_model->getList($filters, $page, $items_on_page, $order_by);
        $media_list = $this->Clubs_media_model->formatArray($media_list);
        $this->view->assign('media_list', $media_list);

        $this->load->helper('navigation');
        $page_data = get_admin_pages_data(site_url() . 'admin/clubs/media/' . $id . '/' . $param . '/', $media_count, $items_on_page, $page, 'briefPage');
        $page_data['date_format'] = $this->pg_date->get_format('date_time_literal', 'st');
        $this->view->assign('page_data', $page_data);

        $club = $this->Clubs_model->format($club);
        $this->view->assign('club', $club);
        $this->view->assign('param', $param);

        $this->view->setHeader(l('admin_header_edit', 'clubs'));
        $this->view->setBackLink(site_url() . 'admin/clubs/index');
        $this->view->render('media');
    }

    public function editMediaPhoto($club_id, $media_id = null) 
    {
        $club = $this->Clubs_model->getObject(['id' => $club_id]);
        if (empty($club)) {
            $this->view->setRedirect(site_url() . 'admin/clubs');
        }

        $this->load->model('clubs/models/Clubs_media_model');
        $club = $this->Clubs_model->format($club);
        $this->view->assign('club', $club);

        if (!empty($media_id)) {
            $media = $this->Clubs_media_model->getObject(['id' => $media_id]);
            $media = $this->Clubs_media_model->format($media);
            $this->view->assign('media', $media);

            $this->view->render('edit_media_photo');
        } else {
            $this->load->model('Uploads_model');
            $media_config = $this->Uploads_model->get_config($this->Clubs_media_model->image_config_gid);
            $this->view->assign('media_config', $media_config);

            $this->view->render('add_media_photo');
        }
    }

    public function mediaUploadImage($club_id) 
    {
        $return = ['errors' => [], 'warnings' => [], 'name' => ''];

        $club = $this->Clubs_model->getObject(['id' => $club_id]);
        if (empty($club)) {
            $return['errors'][] = 'empty club';
            exit(json_encode($return));
        }

        if ($_POST) {
            $post_data = array(
                'description' => $this->input->post('description', true),
            );
        }

        $this->load->model('clubs/models/Clubs_media_model');
        $validate_data = $this->Clubs_media_model->validateImage($post_data, 'multiUpload');
        $validate_data['data']['club_id']   = $club_id;
        $validate_data['data']['upload_gid'] = $this->Clubs_media_model->image_config_gid;
        $validate_data['data']['is_active']  = 1;

        if (empty($validate_data['errors'])) {
            $save_data = $this->Clubs_media_model->saveImage(null, $validate_data['data'], 'multiUpload');

            $photos_count = $this->Clubs_media_model->getCount(['club_id' => $club_id, 'upload_gid' => $this->Clubs_media_model->image_config_gid]);
            $this->Clubs_model->save($club_id, ['photos_count' => $photos_count]);
        }

        $return['form_error'] = $validate_data['form_error'];

        if (!empty($validate_data['errors'])) {
            $return['errors'] = $validate_data['errors'];
        }
        if (empty($save_data['errors'])) {
            if (!empty($save_data['file'])) {
                $return['name'] = $save_data['file'];
            }
        } else {
            $return['errors'][] = $save_data['errors'];
        }

        exit(json_encode($return));
    }

    public function mediaSaveImage($media_id) 
    {
        $return = ['errors' => ''];

        $this->load->model('clubs/models/Clubs_media_model');
        $media = $this->Clubs_media_model->getObject(['id' => $media_id]);
        if (empty($media)) {
            $return['errors'][] = 'empty media';
            exit(json_encode($return));
        }

        $post_data = array(
            'description' => $this->input->post('description', true),
        );

        $validate_data = $this->Clubs_media_model->validateImage($post_data);

        if (!empty($validate_data['errors'])) {
            $return['errors'] = implode('<br/>', $validate_data['errors']);
        } else {
            $this->Clubs_media_model->saveImage($media_id, $validate_data['data']);
        }

        exit(json_encode($return));
    }

    public function editMediaVideo($club_id, $media_id = null) 
    {
        $club = $this->Clubs_model->getObject(['id' => $club_id]);
        if (empty($club)) {
            $this->view->setRedirect(site_url() . 'admin/clubs');
        }

        $this->load->model('clubs/models/Clubs_media_model');
        $club = $this->Clubs_model->format($club);
        $this->view->assign('club', $club);

        if (!empty($media_id)) {
            $media = $this->Clubs_media_model->getObject(['id' => $media_id]);
            $media = $this->Clubs_media_model->format($media);
            $this->view->assign('media', $media);

            $this->view->render('edit_media_video');
        } else {
            $this->load->model('Video_uploads_model');
            $media_config = $this->Video_uploads_model->get_config($this->Clubs_media_model->video_config_gid);
            $this->view->assign('media_config', $media_config);

            $this->view->render('add_media_video');
        }
    }

    public function mediaUploadVideo($club_id) 
    {
        $return = array('errors' => array(), 'warnings' => array(), 'name' => '');
        
        $club = $this->Clubs_model->getObject(['id' => $club_id]);
        if (empty($club)) {
            $return['errors'][] = 'empty club';
            exit(json_encode($return));
        }

        if ($_POST) {
            $post_data = array(
                'description' => $this->input->post('description', true),
                'embed_code'  => $this->input->post('embed_code', true),
            );
        }

        $this->load->model('clubs/models/Clubs_media_model');
        $validate_data = $this->Clubs_media_model->validateVideo($post_data, 'videofile');
        $validate_data['data']['club_id']   = $club_id;
        $validate_data['data']['upload_gid'] = $this->Clubs_media_model->video_config_gid;
        $validate_data['data']['is_active']  = 1;

        if (empty($validate_data['errors'])) {
            $videofile = (!empty($validate_data['data']['media_video']) && $validate_data['data']['media_video'] === 'embed') ? '' : 'videofile';
            $save_data = $this->Clubs_media_model->saveVideo(null, $validate_data['data'], $videofile);

            $videos_count = $this->Clubs_media_model->getCount(['club_id' => $club_id, 'upload_gid' => $this->Clubs_media_model->video_config_gid]);
            $this->Clubs_model->save($club_id, ['videos_count' => $videos_count]);
        }

        $return['form_error'] = $validate_data['form_error'];

        if (!empty($validate_data['errors'])) {
            $return['errors'] = $validate_data['errors'];
        }
        if (empty($save_data['errors'])) {
            if (!empty($save_data['file'])) {
                $return['name'] = $save_data['file'];
            }
        } else {
            $return['errors'][] = $save_data['errors'];
        }

        exit(json_encode($return));
    }

    public function mediaSaveVideo($media_id) 
    {
        $return = ['errors' => ''];

        $this->load->model('clubs/models/Clubs_media_model');
        $media = $this->Clubs_media_model->getObject(['id' => $media_id]);
        if (empty($media)) {
            $return['errors'][] = 'empty media';
            exit(json_encode($return));
        }

        $post_data = array(
            'description' => $this->input->post('description', true),
        );

        $validate_data = $this->Clubs_media_model->validateVideo($post_data);

        if (!empty($validate_data['errors'])) {
            $return['errors'] = implode('<br/>', $validate_data['errors']);
        } else {
            $this->Clubs_media_model->saveVideo($media_id, $validate_data['data']);
        }

        exit(json_encode($return));
    }

    public function deleteMedia($media_id = null)
    {
        $this->load->model('clubs/models/Clubs_media_model');
        $media = $this->Clubs_media_model->getObject(['id' => $media_id]);
        if (!empty($media)) {
            $result = $this->Clubs_media_model->delete($media_id);
            $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_delete_media', 'clubs'));

            if ($media['upload_gid'] == $this->Clubs_media_model->image_config_gid) {
                $photos_count = $this->Clubs_media_model->getCount(['club_id' => $media['club_id'], 'upload_gid' => $this->Clubs_media_model->image_config_gid]);
                $this->Clubs_model->save($media['club_id'], ['photos_count' => $photos_count]);

                $this->view->setRedirect(site_url() . 'admin/clubs/media/' . $media['club_id'] . '/photo');
            } else {
                $videos_count = $this->Clubs_media_model->getCount(['club_id' => $media['club_id'], 'upload_gid' => $this->Clubs_media_model->video_config_gid]);
                $this->Clubs_model->save($media['club_id'], ['videos_count' => $videos_count]);

                $this->view->setRedirect(site_url() . 'admin/clubs/media/' . $media['club_id'] . '/video');
            }
        }
        
        $this->view->setRedirect(site_url() . 'admin/clubs/index');
    }

    public function activate($club_id, $status = 0)
    {
        if (!empty($club_id)) {
            $this->Clubs_model->setStatus($club_id, $status);
            if ($status) {
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_activate_club', 'clubs'));
            } else {
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_deactivate_club', 'clubs'));
            }
        }
        redirect(site_url() . 'admin/clubs/index');
    }


    // FORUM METODS
    public function forum($club_id, $order = null, $order_direction = null, $page = null)
    {
        $club = $this->Clubs_model->getObject(['id' => $club_id]);
        if (empty($club)) {
            $this->view->setRedirect(site_url() . 'admin/clubs');
        }
        $club = $this->Clubs_model->format($club);
        $this->view->assign('club', $club);

        $this->load->model('clubs/models/Clubs_forum_model');
        
        if ($this->input->post('btn_category_add')) {
            $post_data = [
                'club_id'    => $club_id,
                'name'        => $this->input->post('name', true),
                'description' => $this->input->post('description', true),
            ];

            $validate_data = $this->Clubs_forum_model->validate(null, $post_data);
            if (!empty($validate_data['errors'])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data['errors']);
            } else {
                $save_data = $validate_data['data'];
                $save_data['is_active'] = 1;
                $this->Clubs_forum_model->save(null, $save_data);

                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_forum_topic_added', 'clubs'));
                $this->view->setRedirect(site_url() . 'admin/clubs/forum/' . $club_id);
            }
        }

        if (!$order) {
            $order = 'name';
        }
        if (!$order_direction) {
            $order_direction = 'ASC';
        }
        if (!$page) {
            $page = 1;
        }
        
        $this->view->assign('order', $order);
        $this->view->assign('order_direction', $order_direction);

        $filters = [
            'club_id' => $club_id,
        ];

        $topics_count = $this->Clubs_forum_model->getCount($filters);

        $this->load->helper('sort_order');
        $items_on_page = $this->pg_module->get_module_config('start', 'admin_items_per_page');
        $page = get_exists_page_number($page, $topics_count, $items_on_page);

        if ($topics_count > 0) {
            $topics = $this->Clubs_forum_model->getList($filters, $page, $items_on_page, array($order => $order_direction));
            $topics = $this->Clubs_forum_model->formatArray($topics);
            $this->view->assign('topics', $topics);
        }

        $this->load->helper('navigation');
        $page_data = get_admin_pages_data(site_url() . 'admin/clubs/forum/' . $club_id . '/' . $order . '/' . $order_direction . '/', $topics_count, $items_on_page, $page, 'briefPage');
        $this->view->assign('page_data', $page_data);

        $this->load->library('pg_date');
        $date_format = $this->pg_date->get_format('date_time_literal', 'st');
        $this->view->assign('date_format', $date_format);

        $this->view->setHeader(l('admin_header_edit', 'clubs'));
        $this->view->setBackLink(site_url() . 'admin/clubs/index');
        $this->view->render('forum');
    }

    public function deleteForumTopic($topic_id)
    {
        $this->load->model('clubs/models/Clubs_forum_model');
        $topic = $this->Clubs_forum_model->getObject(['id' => $topic_id]);
        if (!empty($topic)) {
            $result = $this->Clubs_forum_model->delete($topic_id);
            $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_delete_topic', 'clubs'));
            $this->view->setRedirect(site_url() . 'admin/clubs/forum/' . $topic['club_id']);
        }
        
        $this->view->setRedirect(site_url() . 'admin/clubs/index');
    }

    public function forumPosts($club_id, $topic_id, $order = null, $order_direction = null, $page = null)
    {
        $club = $this->Clubs_model->getObject(['id' => $club_id]);
        if (empty($club)) {
            $this->view->setRedirect(site_url() . 'admin/clubs');
        }
        $club = $this->Clubs_model->format($club);
        $this->view->assign('club', $club);

        $this->load->model('clubs/models/Clubs_forum_model');
        $topic = $this->Clubs_forum_model->getObject(['id' => $topic_id]);
        if (empty($topic) || $topic['club_id'] != $club_id) {
            $this->view->setRedirect(site_url() . 'admin/clubs');
        }
        $topic = $this->Clubs_forum_model->format($topic);
        $this->view->assign('topic', $topic);

        if (!$order) {
            $order = 'date_add';
        }
        if (!$order_direction) {
            $order_direction = 'DESC';
        }
        if (!$page) {
            $page = 1;
        }
        
        $filters = [
            'club_id'  => $club_id,
            'topic_id'  => $topic_id,
            'is_active' => 1,
        ];

        $this->view->assign('order', $order);
        $this->view->assign('order_direction', $order_direction);

        $posts_count = $this->Clubs_forum_model->getPostsCount($filters);

        $this->load->helper('sort_order');
        $items_on_page = $this->pg_module->get_module_config('start', 'admin_items_per_page');
        $page = get_exists_page_number($page, $topics_count, $items_on_page);

        if ($topics_count > 0) {
            $posts = $this->Clubs_forum_model->getPostsList($filters, $page, $items_on_page, array($order => $order_direction));
            $posts = $this->Clubs_forum_model->formatPostsArray($posts);
            $this->view->assign('posts', $posts);
        }

        $this->load->helper('navigation');
        $page_data = get_admin_pages_data(site_url() . 'admin/clubs/forumPosts/' . $club_id . '/' . $topic_id . '/' . $order . '/' . $order_direction . '/', $posts_count, $items_on_page, $page, 'briefPage');
        $this->view->assign('page_data', $page_data);

        $this->load->library('pg_date');
        $date_format = $this->pg_date->get_format('date_time_literal', 'st');
        $this->view->assign('date_format', $date_format);

        $this->view->setHeader(l('admin_header_edit', 'clubs') . ': ' . $topic['name']);
        $this->view->setBackLink(site_url() . 'admin/clubs/forum/' . $club_id);
        $this->view->render('forum_posts');
    }

    public function deleteForumPost($post_id)
    {
        $this->load->model('clubs/models/Clubs_forum_model');
        $post = $this->Clubs_forum_model->getPostObject(['id' => $post_id]);
        if (!empty($post)) {
            $result = $this->Clubs_forum_model->deletePost($post_id);

            $posts_count = $this->Clubs_forum_model->getPostsCount(['topic_id' => $post['topic_id']]);
            $this->Clubs_forum_model->save($post['topic_id'], ['posts_count' => $posts_count]);

            $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_delete_topic_post', 'clubs'));
            $this->view->setRedirect(site_url() . 'admin/clubs/forumPosts/' . $post['club_id'] . '/' . $post['topic_id']);
        }
        
        $this->view->setRedirect(site_url() . 'admin/clubs/index');
    }
}
