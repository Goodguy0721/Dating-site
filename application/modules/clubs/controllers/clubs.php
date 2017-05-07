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
 * Clubs user side controller
 * 
 * @package     PG_Dating
 * @subpackage  Clubs
 * @category    controllers
 * @copyright   Copyright (c) 2000-2014 PG Dating Pro - php dating software
 * @author      Renat Gabdrakhmanov <renatgab@pilotgroup.net>
 */
class Clubs extends \Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['Clubs_model', 'Menu_model']);
    }

    public function index($order = 'date_created', $order_direction = 'DESC', $page = 1)
    {
        if (empty($_POST)) {
            if ($this->session->userdata('clubs_list')) {
                $filters = $this->session->userdata('clubs_list');
            } else {
                $filters = array();
            }
            $data = (!empty($filters)) ? $filters : array();
        } else {
            foreach ($_POST as $key => $val) {
                $value = $this->input->post($key, true);
                if (is_string($value)) {
                    $data[$key] = trim(strip_tags($value));
                } else {
                    $data[$key] = $value;
                }
            }
        }
        $this->view->assign('block', $this->listBlock($data, $order, $order_direction, $page));

        if (!empty($data['keyword'])) {
            $this->view->assign('search_text', $data['keyword']);
        }

        $this->session->set_userdata('clubs_list', $data);
        $this->view->assign('search_filters', $data);

        $view_mode = (!empty($_SESSION['clubs_view_mode']) && $_SESSION['clubs_view_mode'] == 'gallery') ? 'gallery' : 'list';
        $this->view->assign('view_mode', $view_mode);

        $this->Menu_model->breadcrumbs_set_parent('clubs_item');
        $this->view->render('clubs_list');
    }

    protected function listBlock($data = array(), $order = 'date_created', $order_direction = 'DESC', $page = 1)
    {
        $this->view->assign('user_id', $this->session->userdata('user_id'));

        $filters = $this->session->userdata('clubs_list') ? $this->session->userdata('clubs_list') : [];
        if (!empty($data)) {
            $filters = $data;
        }
        $filters = $this->Clubs_model->searchCriteria($filters);

        $search_url = site_url() . 'clubs/index';
        $url        = site_url() . 'clubs/index/' . $order . '/' . $order_direction . '/';

        $order = trim(strip_tags($order));
        if (!$order) {
            $order = 'date_created';
        }
        $this->view->assign('order', $order);

        $order_direction = strtoupper(trim(strip_tags($order_direction)));
        if ($order_direction != 'DESC' && $order_direction != 'ASC') {
            $order_direction = 'DESC';
        }
        $this->view->assign('order_direction', $order_direction);

        $items_count = $this->Clubs_model->getCount($filters);

        if (!$page) {
            $page = 1;
        }
        $items_on_page = $this->pg_module->get_module_config('users', 'items_per_page');
        $this->load->helper('sort_order');
        $page          = get_exists_page_number($page, $items_count, $items_on_page);

        $sort_data = array(
            'url'       => $search_url,
            'order'     => $order,
            'direction' => $order_direction,
            'links'     => array(
                // 'default'      => l('field_default_sorter', 'clubs'),
                'date_created' => l('field_date_created', 'clubs'),
                'title'        => l('field_title', 'clubs'),
                'users_count'  => l('field_users_count', 'clubs'),
            ),
        );
        $this->view->assign('sort_data', $sort_data);

        if ($items_count > 0) {
            $order_array = [
                $order => $order_direction,
            ];

            $clubs = $this->Clubs_model->getList($filters, $page, $items_on_page, $order_array);
            $clubs = $this->Clubs_model->formatArray($clubs);
            $this->view->assign('clubs', $clubs);
            $this->view->assign('clubs_count', count($clubs));
        }

        $this->load->helper('navigation');
        $page_data                     = get_user_pages_data($url, $items_count, $items_on_page, $page, 'briefPage');
        $page_data['date_format']      = $this->pg_date->get_format('date_literal', 'st');
        $page_data['date_time_format'] = $this->pg_date->get_format('date_time_literal', 'st');
        $page_data['view_type']        = isset($_SESSION['clubs_view_mode']) ? $_SESSION['clubs_view_mode'] : 'gallery';
        $this->view->assign('page_data', $page_data);

        return $this->view->fetch('clubs_list_block');
    }

    public function setViewMode($view_mode)
    {
        if (in_array($view_mode, array('list', 'gallery'))) {
            $_SESSION['clubs_view_mode'] = $view_mode;
        }
    }

    public function view($club_id)
    {
        $user_id = $this->session->userdata('user_id');
        
        $this->load->model('Field_editor_model');
        $this->Field_editor_model->initialize($this->Clubs_model->form_editor_type);
        $sections          = $this->Field_editor_model->get_section_list();
        $sections_gids     = array_keys($sections);
        $fields_for_select = $this->Field_editor_model->get_fields_for_select($sections_gids);
        $this->Clubs_model->setAdditionalFields($fields_for_select);

        $club = $this->Clubs_model->getObject(['id' => $club_id]);
        if (empty($club) || !$club['is_active']) {
            $this->view->setRedirect(site_url() . 'clubs/index');
        }

        $club = $this->Clubs_model->format($club);

        foreach ($sections as $sgid => $sdata) {
            $params['where']['section_gid'] = $sgid;
            $sections[$sgid]['fields']      = $this->Field_editor_model->format_item_fields_for_view($params, $club);
        }
        $this->view->assign('sections', $sections);
        // echo '<pre>'; print_r($club); exit;
        $page_data['date_format']      = $this->pg_date->get_format('date_literal', 'st');
        $page_data['date_time_format'] = $this->pg_date->get_format('date_time_literal', 'st');
        $this->view->assign('page_data', $page_data);

        $this->view->assign('profile_section', 'profile');
        $this->view->assign('user_id', $user_id);
        $this->view->assign('club', $club);
        $this->Menu_model->breadcrumbs_set_parent('clubs_item');
        $this->Menu_model->breadcrumbs_set_active($club['title']);
        $this->view->render('view');
    }

    public function join($club_id, $action = 'join')
    {
        $user_id = $this->session->userdata('user_id');

        $club = $this->Clubs_model->getObject(['id' => $club_id]);
        if (empty($club) || !$club['is_active']) {
            $this->view->setRedirect(site_url() . 'clubs/index');
        }

        $this->load->model('clubs/models/Clubs_users_model');
        $is_joined = $this->Clubs_users_model->getCount([
            'user_id'   => $user_id,
            'club_id'  => $club_id,
        ]);

        if ($action == 'join') {
            $this->Clubs_users_model->joinToClub($user_id, $club_id);

            $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_joined', 'clubs'));
            $this->view->setRedirect(site_url() . 'clubs/view/' . $club_id);
        } else {
            if (!$is_joined) {
                $this->view->setRedirect(site_url() . 'clubs/view/' . $club_id);
            }
            $this->Clubs_users_model->leaveClub($user_id, $club_id);

            $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_leaved', 'clubs'));
            $this->view->setRedirect(site_url() . 'clubs/view/' . $club_id);
        }        

        $this->view->setRedirect(site_url() . 'clubs/index');
    }

    public function media($club_id, $param = 'all', $page = 1) 
    {
        $club = $this->Clubs_model->getObject(['id' => $club_id]);
        if (empty($club) || !$club['is_active']) {
            $this->view->setRedirect(site_url() . 'clubs/index');
        }
        $club = $this->Clubs_model->format($club);
        $this->view->assign('club', $club);

        $this->view->assign('media_block', $this->getMediaBlock($club_id, $param, $page));

        $media_filters = array(
            'all'       => array('link' => site_url() . 'clubs/media/' . $club_id . '/all', 'name' => l('all', 'media')),
            'photo'     => array('link' => site_url() . 'clubs/media/' . $club_id . '/photo', 'name' => l('photo', 'media')),
            'video'     => array('link' => site_url() . 'clubs/media/' . $club_id . '/video', 'name' => l('video', 'media')),
        );
        $this->view->assign('media_filters', $media_filters);

        $this->view->assign('profile_section', 'gallery');
        $this->view->assign('gallery_param', $param);
        $this->view->assign('page', $page);
        $this->Menu_model->breadcrumbs_set_parent('clubs_item');
        $this->Menu_model->breadcrumbs_set_active($club['title']);
        $this->view->render('gallery', 'user', 'clubs');
    }

    public function ajaxMedia($club_id, $param = 'all', $page = 1)
    {
        $media_block = $this->getMediaBlock($club_id, $param, $page);
        $this->view->assign('content', $media_block['content']);
        $this->view->assign('have_more', $media_block['have_more']);
        $this->view->render();
    }

    protected function getMediaBlock($club_id, $param = 'all', $page = 1)
    {
        $return = ['content' => '', 'have_more' => 0];

        $this->load->model('clubs/models/Clubs_media_model');

        $filters = [
            'club_id'  => $club_id,
            'is_active' => 1,
        ];
        
        if ($param == 'photo') {
            $filters['upload_gid'] = $this->Clubs_media_model->image_config_gid;
        } else if ($param == 'video') {
            $filters['upload_gid'] = $this->Clubs_media_model->video_config_gid;
        }

        $items_count = $this->Clubs_media_model->getCount($filters);

        if (!$page) {
            $page = 1;
        }
        $items_on_page = $this->pg_module->get_module_config('users', 'items_per_page');
        $this->load->helper('sort_order');
        $page = get_exists_page_number($page, $items_count, $items_on_page);

        if ($items_count > 0) {
            $order_array = ['date_add' => 'DESC'];

            $media_list = $this->Clubs_media_model->getList($filters, $page, $items_on_page, $order_array);
            $media_list = $this->Clubs_media_model->formatArray($media_list);
            $this->view->assign('media_list', $media_list);
            $this->view->assign('media_count', count($media_list));
        }

        $this->load->helper('navigation');
        $url = site_url() . 'clubs/media/' . $club_id . '/' . $param . '/';
        $page_data                     = get_user_pages_data($url, $items_count, $items_on_page, $page, 'briefPage');
        $page_data['date_format']      = $this->pg_date->get_format('date_literal', 'st');
        $page_data['date_time_format'] = $this->pg_date->get_format('date_time_literal', 'st');
        $this->view->assign('page_data', $page_data);

        $next_page = get_exists_page_number($page + 1, $items_count, $items_on_page);
        if ($next_page > $page) {
            $return['have_more'] = 1;
        }

        $return['content'] = $this->view->fetchFinal('gallery_block');

        return $return;
    }

    public function ajaxViewMedia($id, $club_id, $param = 'all')
    {
        $return = array('content' => '', 'position_info' => '');

        $this->load->model('clubs/models/Clubs_media_model');
        $id = intval($id);
        $club_id = intval($club_id);
        $param = trim(strip_tags($param));
        $order = trim(strip_tags($this->input->post('order')));
        $direction = trim(strip_tags($this->input->post('direction')));
        if (!$order) {
            $order = 'date_add';
        }
        $order_by[$order] = ($direction == 'asc') ? 'ASC' : 'DESC';

        $media = $this->Clubs_media_model->format(
            $this->Clubs_media_model->getObject(['id' => $id]));

        $gallery_name = $this->input->post('gallery_name', true) ? trim(strip_tags($this->input->post('gallery_name', true))) : 'club_mediagallery';

        $date_formats['date_format'] = $this->pg_date->get_format('date_literal', 'st');
        $date_formats['date_time_format'] = $this->pg_date->get_format('date_time_literal', 'st');
        $this->view->assign('date_formats', $date_formats);

        $rand_param = intval($this->input->post('rand_param'));
        if ($rand_param) {
            $this->view->assign('vers', '?' . $rand_param);
        }

        $this->view->assign('media', $media);
        $return['content'] = $this->view->fetchFinal('view_media');

        $return['position_info'] = $this->Clubs_media_model->getMediaPositionInfo($id, $param, $club_id, $order_by);
        
        $this->view->assign($return);
        $this->view->render();
    }

    public function forum($club_id, $page = 1) 
    {
        $club = $this->Clubs_model->getObject(['id' => $club_id]);
        if (empty($club) || !$club['is_active']) {
            $this->view->setRedirect(site_url() . 'clubs/index');
        }
        $club = $this->Clubs_model->format($club);
        $this->view->assign('club', $club);

        $this->load->model('clubs/models/Clubs_forum_model');
        $filters = [
            'club_id'  => $club_id,
            'is_active' => 1,
        ];
        $topics_count = $this->Clubs_forum_model->getCount($filters);

        $this->load->helper('sort_order');
        $items_on_page = $this->pg_module->get_module_config('users', 'items_per_page');
        $page = get_exists_page_number($page, $topics_count, $items_on_page);

        if ($topics_count > 0) {
            $topics = $this->Clubs_forum_model->getList($filters, $page, $items_on_page, array($order => $order_direction));
            $topics = $this->Clubs_forum_model->formatArray($topics);
            $this->view->assign('topics', $topics);
        }

        $this->load->helper('navigation');
        $page_data = get_admin_pages_data(site_url() . 'clubs/forum/' . $club_id . '/', $topics_count, $items_on_page, $page, 'briefPage');
        $this->view->assign('page_data', $page_data);

        $this->load->library('pg_date');
        $date_format = $this->pg_date->get_format('date_time_literal', 'st');
        $this->view->assign('date_format', $date_format);

        $this->view->assign('profile_section', 'forum');
        $this->view->assign('page', $page);
        $this->Menu_model->breadcrumbs_set_parent('clubs_item');
        $this->Menu_model->breadcrumbs_set_active($club['title']);
        $this->view->render('forum_topics', 'user', 'clubs');
    }

    public function topic($topic_id, $page = 1) 
    {
        $this->load->model('clubs/models/Clubs_forum_model');

        $user_id = $this->session->userdata('user_id');

        $topic = $this->Clubs_forum_model->getObject(['id' => $topic_id]);
        if (empty($topic)) {
            $this->view->setRedirect(site_url() . 'clubs/index');
        }
        $topic = $this->Clubs_forum_model->format($topic);
        $this->view->assign('topic', $topic);

        $club = $this->Clubs_model->getObject(['id' => $topic['club_id']]);
        if (empty($club) || !$club['is_active']) {
            $this->view->setRedirect(site_url() . 'clubs/index');
        }
        $club = $this->Clubs_model->format($club);
        $this->view->assign('club', $club);

        if ($this->input->post('btn_add_post')) {
            $message = trim(strip_tags($this->input->post('message', true)));
            if (empty($message)) {
                $this->system_messages->addMessage(View::MSG_ERROR, l('error_empty_post_message', 'clubs'));
            } else {
                $save_data = [
                    'club_id' => $topic['club_id'],
                    'topic_id' => $topic['id'],
                    'user_id'  => $user_id,
                    'message'  => $message,
                ];

                $res = $this->Clubs_forum_model->savePost(null, $save_data);

                $posts_count = $this->Clubs_forum_model->getPostsCount(['topic_id' => $topic['id']]);
                $this->Clubs_forum_model->save($topic['id'], ['posts_count' => $posts_count]);                

                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_post_added', 'clubs'));
                $this->view->setRedirect(site_url() . 'clubs/topic/' . $topic['id']);
            }
        }

        $filters = [
            'club_id'  => $topic['club_id'],
            'topic_id'  => $topic['id'],
            'is_active' => 1,
        ];
        $posts_count = $this->Clubs_forum_model->getPostsCount($filters);

        $this->load->helper('sort_order');
        $items_on_page = $this->pg_module->get_module_config('users', 'items_per_page');
        $page = get_exists_page_number($page, $posts_count, $items_on_page);

        if ($posts_count > 0) {
            $posts = $this->Clubs_forum_model->getPostsList($filters, $page, $items_on_page, array('date_added' => 'DESC'));
            $posts = $this->Clubs_forum_model->formatPostsArray($posts);
            $this->view->assign('posts', $posts);
        }

        $this->load->helper('navigation');
        $page_data = get_admin_pages_data(site_url() . 'clubs/topic/' . $topic_id . '/', $posts_count, $items_on_page, $page, 'briefPage');
        $this->view->assign('page_data', $page_data);

        $this->load->library('pg_date');
        $date_format = $this->pg_date->get_format('date_time_literal', 'st');
        $this->view->assign('date_format', $date_format);

        $this->view->assign('profile_section', 'forum');
        $this->view->assign('page', $page);
        $this->view->assign('user_id', $user_id);
        $this->Menu_model->breadcrumbs_set_parent('clubs_item');
        $this->Menu_model->breadcrumbs_set_active($club['title']);
        $this->view->render('forum_posts', 'user', 'clubs');
    }

    public function deletePost($post_id)
    {
        $this->load->model('clubs/models/Clubs_forum_model');
        $post = $this->Clubs_forum_model->getPostObject(['id' => $post_id]);
        if (!empty($post) && $post['user_id'] == $this->session->userdata('user_id')) {
            $result = $this->Clubs_forum_model->deletePost($post_id);

            $posts_count = $this->Clubs_forum_model->getPostsCount(['topic_id' => $post['topic_id']]);
            $this->Clubs_forum_model->save($post['topic_id'], ['posts_count' => $posts_count]);

            $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_delete_topic_post', 'clubs'));
            $this->view->setRedirect(site_url() . 'clubs/topic/' . $post['topic_id']);
        }
        
        $this->view->setRedirect(site_url() . 'clubs/index');
    }

    public function ajaxGetUsersForm()
    {
        $this->view->render('ajax_users_form');
    }

    public function ajaxGetUsersData($club_id, $page = 1)
    {
        $return = array();
        $params = array();
        if (!$page) {
            $page = intval($this->input->post('page', true));
            if (!$page) {
                $page = 1;
            }
        }

        $search_string = trim(strip_tags($this->input->post('search', true)));
        if (!empty($search_string)) {
            $hide_user_names = $this->pg_module->get_module_config('users', 'hide_user_names');
            if ($hide_user_names) {
                $params['where']['nickname LIKE'] = "%" . $search_string . "%";
            } else {
                $search_string_escape  = $this->db->escape("%" . $search_string . "%");
                $params["where_sql"][] = "(nickname LIKE " . $search_string_escape
                        . " OR fname LIKE " . $search_string_escape
                        . " OR sname LIKE " . $search_string_escape . ")";
            }
        }
        

        $this->load->model('clubs/models/Clubs_users_model');
        $items_on_page = $this->pg_module->get_module_config('start', 'admin_items_per_page');
        $filters = [
            'club_id'  => $club_id,
        ];

        $club_users = $this->Clubs_users_model->getList($filters);
        $users_ids_arr = [];
        foreach ($club_users as $key => $value) {
            $users_ids_arr[] = $value['user_id'];
        }

        $this->load->model('Users_model');
        $users = $this->Users_model->get_users_list_by_key($page, $items_on_page, array("nickname" => "asc"), $params, array_unique($users_ids_arr), true);

        $items = [];
        foreach ($users as $key => $user) {
            $items[] = [
                'id'          => $user['id'],
                'output_name' => $user['output_name'],
                'age'         => $user['age'],
                'location'    => $user['location'],
                'link'        => $user['link'],
                'image'       => $user['media']['user_logo']['thumbs']['big'],
            ];
        }

        $return['all']          = $this->Users_model->get_users_count($params, $users_ids_arr);
        $return['items']        = $items;
        $return['current_page'] = $page;
        $return['pages']        = ceil($return['all'] / $items_on_page);

        exit(json_encode($return));
    }
}
