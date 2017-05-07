<?php

namespace Pg\Modules\Blogs\Controllers;

use Pg\Libraries\View;

/**
* Blogs user side controller
*
* @package PG_Dating
* @subpackage application
* @category modules
* @copyright Pilot Group <http://www.pilotgroup.net/>
* @author Renat Gabdrakhmanov <renatgab@pilotgroup.net>
**/

Class Blogs extends \Controller 
{
    public function __construct() 
    {
        parent::__construct();
        $this->load->model('Blogs_model');
    }

    public function index($page = 1) 
    {
        $user_id = $this->session->userdata('user_id');
        
        if ($this->input->post('btn_save')) {
            $post_data = array(
                "title" => $this->input->post('title', true),
                "category" => $this->input->post('category', true),
                "is_hidden" => $this->input->post('is_hidden', true),
                "description" => $this->input->post('description'),
                "tags" => $this->input->post('tags', true),
            );
            
            $validate_data = $this->Blogs_model->validate_blog($post_data);
            if (!empty($validate_data["errors"])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data["errors"]);
                $this->view->assign('data', $validate_data["data"]);
            } else {
                $save_blog = $validate_data["data"];
                unset($save_blog['tags']);
                $save_blog['user_id'] = $user_id;
                $save_blog['posts_count'] = 1;
                $blog_id = $this->Blogs_model->save_blog(null, $save_blog);
                
                $tags = $validate_data["data"]['tags'];
                $this->Blogs_model->save_tags('blog', $blog_id, $tags);
                
                $save_post['blog_id'] = $blog_id;
                $save_post['user_id'] = $user_id;
                $save_post['title'] = l('first_blog_title' ,'blogs');
                $save_post['body'] = l('first_blog_post_1' ,'blogs')." <a href='".site_url().'blogs'."'>".l('first_blog_post_2' ,'blogs')."</a>!";
                $this->Blogs_model->save_post(null, $save_post);        
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_added_blog' ,'blogs'));    
                redirect(site_url().'blogs', 'hard');   
            }
        }
        
        
        $params = array();
        $params['where']['user_id'] = $user_id;
        $blog = $this->Blogs_model->get_blogs_list(null, null, array(), $params);
        if (empty($blog)) {
            $this->view->assign('create_form', 1);
            $categories = ld('blog_categories', 'blogs');
            $this->view->assign('categories', $categories);
            
            /*$this->load->plugin('fckeditor');
            $content_fck = create_editor("description", isset($data["description"]) ? $data["description"] : "", 300, 200, 'Middle');
            $this->view->assign('content_fck', $content_fck);*/
        } else {
            $blog = $blog[0];
            $this->view->assign('blog', $blog);
            $attrs["where"]['blog_id'] = $blog['id'];
            $items_on_page = $this->pg_module->get_module_config('start', 'index_items_per_page');
            $blog_posts_count = $this->Blogs_model->get_posts_count($attrs);
            $this->load->helper('sort_order');
            $page = get_exists_page_number($page, $blog_posts_count, $items_on_page);
            
            if ($blog_posts_count > 0) {
                $blog_posts = $this->Blogs_model->get_posts_list($page, $items_on_page, array('date_created' => 'ASC'), $attrs);            
                $this->view->assign('blog_posts', $blog_posts);
                $url = site_url()."blogs/";
                $this->load->helper("navigation");
                $page_data = get_user_pages_data($url, $blog_posts_count, $items_on_page, $page, 'briefPage');
            }
            
            $page_data['date_format'] = $this->pg_date->get_format('date_literal', 'st');
            $page_data["date_time_format"] = $this->pg_date->get_format('date_time_literal', 'st');
            $this->view->assign('page_data', $page_data);
        }
        
        $this->load->model('Menu_model');
        $this->Menu_model->breadcrumbs_set_active(l('header_blog' ,'blogs'));
        
        $this->view->assign('action', 'my_blog');
        $this->view->render('my_blog');
    }

    public function edit_blog() 
    {
        $user_id = $this->session->userdata('user_id');
        
        $params = array();
        $params['where']['user_id'] = $user_id;
        $blog = $this->Blogs_model->get_blogs_list(null, null, array(), $params);
        $blog = $blog[0];
        $this->view->assign('blog', $blog);
        
        if ($this->input->post('btn_save')) {
            $post_data = array(
                "title" => $this->input->post('title', true),
                "category" => $this->input->post('category', true),
                "is_hidden" => $this->input->post('is_hidden', true),
                "description" => $this->input->post('description'),
                "tags" => $this->input->post('tags', true),
            );
            
            $validate_data = $this->Blogs_model->validate_blog($post_data);
            if (!empty($validate_data["errors"])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data["errors"]);
                $validate_data['data']['tags_str'] = implode($validate_data['data']['tags']);
                $this->view->assign('blog', $validate_data['data']);
            } else {
                $save_blog = $validate_data["data"];
                unset($save_blog['tags']);
                $blog_id = $this->Blogs_model->save_blog($blog['id'], $save_blog);
                
                $tags = $validate_data["data"]['tags'];
                $this->Blogs_model->save_tags('blog', $blog_id, $tags); 
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_updated_blog' ,'blogs'));  
                redirect(site_url().'blogs', 'hard');
            }
        }
        
        
        $this->view->assign('create_form', 1);
        $categories = ld('blog_categories', 'blogs');
        $this->view->assign('categories', $categories);
        
        $this->load->plugin('fckeditor');
        $content_fck = create_editor("description", isset($blog["description"]) ? $blog["description"] : "", 300, 200, 'Middle');
        $this->view->assign('content_fck', $content_fck);
        
        $this->load->model('Menu_model');
        $this->Menu_model->breadcrumbs_set_active(l('header_blog' ,'blogs'));
        
        $this->view->assign('action', 'my_blog');
        $this->view->render('edit_blog');
    }

    public function delete_blog() 
    {
        $user_id = $this->session->userdata('user_id');
        
        $params = array();
        $params['where']['user_id'] = $user_id;
        $blog = $this->Blogs_model->get_blogs_list(null, null, array(), $params);
        $blog = $blog[0];
        
        if (empty($blog)) redirect(site_url().'blogs', 'hard');
        
        $this->Blogs_model->delete_blog($blog['id'], $user_id);     
        $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_deleted_blog', 'blogs'));
        
        redirect(site_url().'blogs', 'hard');
    }

    public function edit_post($id = null) 
    {
        $user_id = $this->session->userdata('user_id');
        if ($id) {
            $blog_post_data = $this->Blogs_model->get_post_by_id($id);
            if ((empty($blog_post_data)) || ($blog_post_data['user_id']!=$user_id)) redirect(site_url()."blogs", 'hard');
            $this->view->assign('post', $blog_post_data);
        }
        
        $user_id = $this->session->userdata('user_id');
        
        
        if ($this->input->post('btn_save')) {
            $post_data = array(
                "title" => $this->input->post('title', true),
                "body" => $this->input->post('body', true),
                "is_hidden" => $this->input->post('is_hidden', true),
                "can_comment" => $this->input->post('can_comment'),
                "tags" => $this->input->post('tags', true),
            );
            $validate_data = $this->Blogs_model->validate_post($post_data);
            if (!empty($validate_data["errors"])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data["errors"]);
                $validate_data['data']['tags_str'] = implode($validate_data['data']['tags']);
                $this->view->assign('post', $validate_data['data']);
            } else {
                $save_post = $validate_data["data"];
                $save_post['user_id'] = $user_id;
                
                $params = array();
                $params['where']['user_id'] = $user_id;
                $blog = $this->Blogs_model->get_blogs_list(null, null, array(), $params);
                $blog = $blog[0];
                
                $save_post['blog_id'] = $blog['id'];
                unset($save_post['tags']);
                $post_id = $this->Blogs_model->save_post($id, $save_post);
                
                $tags = $validate_data["data"]['tags'];
                $this->Blogs_model->save_tags('blog_post', $post_id, $tags);
                $this->system_messages->addMessage(View::MSG_SUCCESS, (!$id)?l('success_added_post' ,'blogs'):l('success_updated_post' ,'blogs'));  
                redirect(site_url().'blogs', 'hard');
            }
        }
        
        $this->load->plugin('fckeditor');
        $content_fck = create_editor("body", isset($blog_post_data["body"]) ? $blog_post_data["body"] : "", 300, 200, 'Middle');
        $this->view->assign('content_fck', $content_fck);
        
        $this->load->model('Menu_model');
        $this->Menu_model->breadcrumbs_set_active(l('header_blog' ,'blogs'));
        
        $this->view->assign('action', 'my_blog');
        $this->view->render('edit_post');
    }

    public function view_post($id = null, $page = 1) 
    {
        $user_id = $this->session->userdata('user_id');
        if ($id) {
            $blog_post_data = $this->Blogs_model->get_post_by_id($id);
        }
        if (empty($blog_post_data)) redirect(site_url()."blogs", 'hard');
        
        $blog = $this->Blogs_model->get_blog_by_id($blog_post_data['blog_id']);
        $this->view->assign('blog', $blog);
                
        $this->view->assign('post', $blog_post_data);
        
        if ($this->input->post('btn_save')) {
            $post_data = array(
                "title" => $this->input->post('title', true),
                "body" => $this->input->post('body', true),
                "comment_id" => $this->input->post('comment_id', true),
            );
            $validate_data = $this->Blogs_model->validate_comment($post_data);
            if (!empty($validate_data["errors"])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data["errors"]);
                $comment = $validate_data['data'];
                $this->view->assign('comment', $validate_data['data']);
            } else {
                $save_post = $validate_data["data"];
                $save_post['user_id'] = $user_id;
                $save_post['post_id'] = $id;
                $save_post['blog_id'] = $blog['id'];
                $post_id = $this->Blogs_model->save_comment(null, $save_post);
                
                $this->Blogs_model->save_blog($blog['id'], array('comments_count' => $this->Blogs_model->get_comments_count(array('where'=>array('blog_id'=>$blog['id'])))));
                
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_added_comment' ,'blogs'));
                $url = site_url()."blogs/view_post/".$id."/";
                redirect($url, 'hard');
            }
        }
        if ($blog_post_data['can_comment'] == 1) {
            $params['where']['post_id'] = $id;
            $comments_count = $this->Blogs_model->get_comments_count($params);
            $this->load->helper('sort_order');
            $items_on_page = $this->pg_module->get_module_config('start', 'index_items_per_page');
            $page = get_exists_page_number($page, $comments_count, $items_on_page);
            if ($comments_count > 0) {
                $comments_data = $this->Blogs_model->get_comments_list($page, $items_on_page, array('date_created' => 'ASC'), $params);         
                $this->view->assign('comments', $comments_data);
            }
            $url = site_url()."blogs/view_post/".$id."/";
            $this->load->helper("navigation");
            $page_data = get_user_pages_data($url, $comments_count, $items_on_page, $page, 'briefPage');
            $this->view->assign('comments_count', $comments_count);
        }
            
        $this->load->model('Menu_model');
        $this->Menu_model->breadcrumbs_set_active(l('header_blog' ,'blogs'));
        
        $page_data["date_time_format"] = $this->pg_date->get_format('date_time_literal', 'st');
        $this->view->assign('page_data', $page_data);
        
        $this->load->plugin('fckeditor');
        $content_fck = create_editor("body", isset($comment["body"]) ? $comment["body"] : "", 300, 200, 'Middle');
        $this->view->assign('content_fck', $content_fck);
        
        $this->view->assign('menu_first_tab_name', $blog['user']['output_name'].' '.l('header_blog' ,'blogs'));
        
        $this->view->assign('action', 'my_blog');
        $this->view->render('view_post');
    }

    public function delete_post($id = null) 
    {
        $user_id = $this->session->userdata('user_id');
        
        $blog_post_data = $this->Blogs_model->get_post_by_id($id);
        if ((empty($blog_post_data)) || ($blog_post_data['user_id']!=$user_id)) redirect(site_url()."blogs", 'hard');
        
        $this->Blogs_model->delete_post($blog_post_data['id']);     
        
        $this->Blogs_model->save_blog($blog_post_data['blog_id'], array('comments_count' => $this->Blogs_model->get_comments_count(array('where' => array('blog_id' => $blog_post_data['blog_id']))), 'posts_count' => $this->Blogs_model->get_posts_count(array('where' => array('blog_id' => $blog_post_data['blog_id'])))));
        $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_deleted_post', 'blogs'));
        
        redirect(site_url().'blogs', 'hard');
    }

    public function delete_comment($id = null) 
    {
        $user_id = $this->session->userdata('user_id');
        
        $comment_data = $this->Blogs_model->get_comment_by_id($id);
        
        $blog_post_data = $this->Blogs_model->get_post_by_id($comment_data['post_id']);
        if ((empty($blog_post_data)) || (($blog_post_data['user_id'] != $user_id) && ($comment_data['user_id'] != $user_id))) {
            redirect(site_url()."blogs", 'hard');
        }
        
        $this->Blogs_model->delete_comment($comment_data['id']);        
        
        $this->Blogs_model->save_blog($blog_post_data['blog_id'], array('comments_count' => $this->Blogs_model->get_comments_count(array('where' => array('blog_id' => $blog_post_data['blog_id'])))));
        $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_deleted_comment', 'blogs'));
        
        redirect(site_url().'blogs/view_post/'.$comment_data['post_id'], 'hard');
    }

    public function friends($page = 1) 
    {
        $user_id = $this->session->userdata('user_id');

        $this->load->model('friendlist/models/Friendlist_model');
        $friendlist_count = $this->Friendlist_model->get_list_count($user_id, 'accept');
        if ($friendlist_count) {
            $friendlist = $this->Friendlist_model->get_list($user_id, 'accept');
            $friends_ids = array();
            foreach ($friendlist as $list) {
                $friends_ids[] = $list['id_dest_user'];
            }
            
            $attrs = array('where_in' => array('user_id' => $friends_ids));
            $attrs['where']['is_hidden'] = 0;
            $items_on_page = $this->pg_module->get_module_config('start', 'index_items_per_page');
            $blog_posts_count = $this->Blogs_model->get_posts_count($attrs);
            $this->load->helper('sort_order');
            $page = get_exists_page_number($page, $blog_posts_count, $items_on_page);
            
            if ($blog_posts_count > 0) {
                $blog_posts = $this->Blogs_model->get_posts_list($page, $items_on_page, array('date_created' => 'ASC'), $attrs, null, true);            
                $this->view->assign('blog_posts', $blog_posts);
                $url = site_url()."blogs/";
                $this->load->helper("navigation");
                $page_data = get_user_pages_data($url, $blog_posts_count, $items_on_page, $page, 'briefPage');
            }
            
            $page_data['date_format'] = $this->pg_date->get_format('date_literal', 'st');
            $page_data["date_time_format"] = $this->pg_date->get_format('date_time_literal', 'st');
            $this->view->assign('page_data', $page_data);
        }
            
        $this->load->model('Menu_model');
        $this->Menu_model->breadcrumbs_set_active(l('header_blog' ,'blogs'));
        
        $this->view->assign('action', 'friends');
        $this->view->render('friends_blog');
    }

    public function categories() 
    {
        $categories_ds = ld('blog_categories', 'blogs');
        $categories = array();
        $i=0;
        
        $user_id = $this->session->userdata('user_id');

        $this->load->model('friendlist/models/Friendlist_model');
        $friendlist_count = $this->Friendlist_model->get_list_count($user_id, 'accept');
        if ($friendlist_count) {
            $friendlist = $this->Friendlist_model->get_list($user_id, 'accept');
            $friends_ids = array();
            foreach ($friendlist as $list) {
                $friends_ids[] = $list['id_dest_user'];
            }
            $friends_str = "'".implode("','", $friends_ids)."'";
            $attrs["where_sql"][] = " (is_hidden='0' OR user_id IN (".$friends_str."))";
        } else {
            $attrs["where"]['is_hidden'] = 0;
        }
        foreach ($categories_ds['option'] as $gid => $name) {
            $categories[$i]['gid'] = $gid;
            $categories[$i]['name'] = $name;
            $attrs["where"]['category'] = $gid;
            $categories[$i]['blogs_count'] = $this->Blogs_model->get_blogs_count($attrs);
            $i++;
        }
        $this->view->assign('categories', $categories);
        
        $tags = $this->Blogs_model->get_all_tags();
        $this->view->assign('tags', $tags);
        $this->view->assign('tags_count', count($tags));
        
        $this->load->model('Menu_model');
        $this->Menu_model->breadcrumbs_set_active(l('header_blog' ,'blogs'));
        
        $this->view->assign('action', 'categories');
        $this->view->render('categories');
    }

    public function searh_by_tag() 
    {
        $tag = $this->input->post('tag', true);
        if (empty($tag)) return '';
        $this->view->assign('tag', $tag);
        
        $user_id = $this->session->userdata('user_id');
        
        $result = $this->Blogs_model->get_by_tag($tag);
        $blog_ids = array();
        $blog_post_ids = array();
        foreach ($result as $item) {
            if ($item['tag_type'] == 'blog') $blog_ids[] = $item['item_id'];
            if ($item['tag_type'] == 'blog_post') $blog_post_ids[] = $item['item_id'];
        }
        
        if (!empty($blog_ids)) {
            $this->load->model('friendlist/models/Friendlist_model');
            $friendlist_count = $this->Friendlist_model->get_list_count($user_id, 'accept');
            if ($friendlist_count) {
                $friendlist = $this->Friendlist_model->get_list($user_id, 'accept');
                $friends_ids = array();
                foreach ($friendlist as $list) {
                    $friends_ids[] = $list['id_dest_user'];
                }
                $friends_str = "'".implode("','", $friends_ids)."'";
                $attrs["where_sql"][] = " (is_hidden='0' OR user_id IN (".$friends_str."))";
            } else{
                $attrs["where"]['is_hidden'] = 0;
            }
            $attrs["where"]['active'] = 1;
            $attrs["where_in"]['id'] = $blog_ids;
            $blogs = $this->Blogs_model->get_blogs_list(null, null, null, $attrs);
            if (!empty($blogs))
                $this->view->assign('blogs', $blogs);
        }
        
        if (!empty($blog_post_ids)){
            $params["where_in"]['id'] = $blog_post_ids;
            $posts = $this->Blogs_model->get_posts_list(null, null, null, $params, null, true);
            if (!empty($posts))
                $this->view->assign('posts', $posts);
        }
        
        echo $this->view->fetch('tag_search_result');
    }

    public function view_blog($id = 0, $page = 1) 
    {
        $user_id = $this->session->userdata('user_id');
        
        $blog = $this->Blogs_model->get_blog_by_id($id);
        if (empty($blog) || ($blog['user_id'] == $user_id)){
            redirect(site_url().'blogs', 'hard');
        } else{         
            $this->view->assign('blog', $blog);
            $attrs["where"]['blog_id'] = $blog['id'];
            $items_on_page = $this->pg_module->get_module_config('start', 'index_items_per_page');
            $blog_posts_count = $this->Blogs_model->get_posts_count($attrs);
            $this->load->helper('sort_order');
            $page = get_exists_page_number($page, $blog_posts_count, $items_on_page);
            
            if ($blog_posts_count > 0) {
                $blog_posts = $this->Blogs_model->get_posts_list($page, $items_on_page, array('date_created' => 'ASC'), $attrs);            
                $this->view->assign('blog_posts', $blog_posts);
                $url = site_url() . "blogs/";
                $this->load->helper("navigation");
                $page_data = get_user_pages_data($url, $blog_posts_count, $items_on_page, $page, 'briefPage');
            }
            
            $page_data['date_format'] = $this->pg_date->get_format('date_literal', 'st');
            $page_data["date_time_format"] = $this->pg_date->get_format('date_time_literal', 'st');
            $this->view->assign('page_data', $page_data);
        }
        
        $this->load->model('Menu_model');
        $this->Menu_model->breadcrumbs_set_active(l('header_blog' ,'blogs'));
        
        $this->view->assign('menu_first_tab_name', $blog['user']['output_name'].' '.l('header_blog' ,'blogs'));
        
        $this->view->assign('action', 'my_blog');
        $this->view->render('view_blog');
    }

    public function view_category($gid = '', $page = 1) 
    {
        $user_id = $this->session->userdata('user_id');
        
        if (empty($gid)) redirect(site_url().'blogs/categories', 'hard');
        
        $categories_ds = ld('blog_categories', 'blogs');
        $this->view->assign('category_name', $categories_ds['option'][$gid]);
        $this->load->model('friendlist/models/Friendlist_model');
        $friendlist_count = $this->Friendlist_model->get_list_count($user_id, 'accept');
        if ($friendlist_count) {
            $friendlist = $this->Friendlist_model->get_list($user_id, 'accept');
            $friends_ids = array();
            foreach ($friendlist as $list) {
                $friends_ids[] = $list['id_dest_user'];
            }
            $friends_str = "'".implode("','", $friends_ids)."'";
            $attrs["where_sql"][] = " (is_hidden='0' OR user_id IN (".$friends_str."))";
        } else{
            $attrs["where"]['is_hidden'] = 0;
        }
        $attrs["where"]['active'] = 1;
        $attrs["where"]['category'] = $gid;         
        $items_on_page = $this->pg_module->get_module_config('start', 'index_items_per_page');
        $blogs_count = $this->Blogs_model->get_blogs_count($attrs);
        $this->load->helper('sort_order');
        $page = get_exists_page_number($page, $blogs_count, $items_on_page);
        
        if ($blogs_count > 0) {
            $blogs = $this->Blogs_model->get_blogs_list(null, null, null, $attrs);      
            $this->view->assign('blogs', $blogs);
            $url = site_url()."blogs/view_category/" . $gid . "/";
            $this->load->helper("navigation");
            $page_data = get_user_pages_data($url, $blogs_count, $items_on_page, $page, 'briefPage');
        }
        
        $page_data['date_format'] = $this->pg_date->get_format('date_literal', 'st');
        $page_data["date_time_format"] = $this->pg_date->get_format('date_time_literal', 'st');
        $this->view->assign('page_data', $page_data);
        
        $this->load->model('Menu_model');
        $this->Menu_model->breadcrumbs_set_active(l('header_blog' ,'blogs'));
        
        $this->view->assign('menu_first_tab_name', $blog['user']['output_name'].' '.l('header_blog' ,'blogs'));
        
        $this->view->assign('action', 'categories');
        $this->view->render('view_category');
    }

    public function calendar($show_period = "month", $move = "this", $year = "", $month = "", $week = "", $day = "") 
    {
        if (empty($year)) $year = date("Y", mktime());
        if (empty($month)) $month = date("n", mktime());
        if (empty($week)) $week = date("W", mktime());
        if (empty($day)) $day = date("j", mktime());
        $actions = array(
            "show_period"   => $show_period,
            "move"          => $move,
            "year"          => $year,
            "month"         => $month,
            "week"          => $week,
            "day"           => $day
        );
        if ( $actions["move"] != "this" ) {
            $parts = explode("_", $actions["move"]);
            if ( $parts[1] == "month" ) {
                switch ( $parts[0] ) {
                    case "back": {
                        if ( $actions["month"] == 1 ) {
                            $actions["year"] = $actions["year"] - 1;
                            $actions["month"] = 12;
                        } else {
                            $actions["month"] = $actions["month"] - 1;
                        }
                    }
                    break;

                    case "next": {
                        if ( $actions["month"] == 12 ) {
                            $actions["year"] = $actions["year"] + 1;
                            $actions["month"] = 1;
                        } else {
                            $actions["month"] = $actions["month"] + 1;
                        }
                    }
                    break;

                    default: {
                    }
                    break;
                }
            } elseif ( $parts[1] == "day" ) {
                switch ( $parts[0] ) {
                    case "back": {
                        $selected_day = getdate( mktime( 0, 0, 1, $actions["month"], $actions["day"], $actions["year"] ) - 86400 );
                        $actions["day"] = $selected_day["mday"];
                        $actions["month"] = $selected_day["mon"];
                        $actions["year"] = $selected_day["year"];
                    }
                    break;

                    case "next": {
                        $selected_day = getdate( mktime( 0, 0, 1, $actions["month"], $actions["day"], $actions["year"] ) + 86400 );
                        $actions["day"] = $selected_day["mday"];
                        $actions["month"] = $selected_day["mon"];
                        $actions["year"] = $selected_day["year"];
                    }
                    break;

                    default: {
                    }
                    break;
                }
            }
        }
        switch ( $actions["show_period"] ) {
            //calendar month
            case "month": {
                $current_day = getdate(mktime());
                $amount_days = date( "t", mktime(0, 0, 0, $actions["month"], $actions["day"], $actions["year"]) );
                $selected_month = getdate( mktime(0, 0, 0, $actions["month"], 1, $actions["year"]) );
                //$selected_month['month'] = $lang["month"][$selected_month['mon']];
                $first_day = getdate( mktime(0, 0, 0, $actions["month"], 1, $actions["year"]) );
                $calendar = array();
                $week = array(  0 => "false",
                                1 => "false",
                                2 => "false",
                                3 => "false",
                                4 => "false",
                                5 => "false",
                                6 => "false");


                $week[$first_day["wday"]] = array(  "mday" => $first_day["mday"],
                                                    "wday" => $first_day["wday"],
                                                    "mon" => $first_day["mon"],
                                                    "year" => $first_day["year"],
                                                    "blog" => $this->Blogs_model->get_blog_post_by_day($first_day["year"]."-".$first_day["mon"]."-".$first_day["mday"], $this->session->userdata('user_id')) 
                                                );
                if ( $current_day["mday"] == $first_day["mday"] && $current_day["mon"] == $first_day["mon"] && $current_day["year"] == $first_day["year"] ){
                    $week[$first_day["wday"]]["current_day"] = "true";
                } else {
                    $week[$first_day["wday"]]["current_day"] = "false";
                }
                for ( $days_cnt = 2; $days_cnt <= $amount_days; $days_cnt++ ) {
                    $this_day = getdate( mktime(0, 0, 0, $first_day["mon"], $days_cnt, $first_day["year"]) );

                    if ( $this_day["wday"] == 1 ) {
                        $calendar[] = $week;
                        $week = array(
                                        0 => "false",
                                        1 => "false",
                                        2 => "false",
                                        3 => "false",
                                        4 => "false",
                                        5 => "false",
                                        6 => "false");
                    }
                    $week[$this_day["wday"]] = array(   "mday" => $this_day["mday"],
                                                        "wday" => $this_day["wday"],
                                                        "mon" => $this_day["mon"],
                                                        "year" => $this_day["year"],
                                                        "blog" => $this->Blogs_model->get_blog_post_by_day($this_day["year"]."-".$this_day["mon"]."-".$this_day["mday"], $this->session->userdata('user_id')) 
                                                    );
                    if ( $current_day["mday"] == $this_day["mday"] && $current_day["mon"] == $this_day["mon"] && $current_day["year"] == $this_day["year"] ){
                        $week[$this_day["wday"]]["current_day"] = "true";
                    } else {
                        $week[$this_day["wday"]]["current_day"] = "false";
                    }
                }
                $calendar[] = $week;
                $this->view->assign("current_month", $calendar);
                $this->view->assign("current_day", $current_day);
                $this->view->assign("selected_month", $selected_month);
                $this->view->assign('action', 'calendar');
                
                $this->view->render('calendar');
            }
            break;
            default: {
            }
            break;
        }
        
    }

}
