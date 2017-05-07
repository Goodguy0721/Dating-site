<?php

namespace Pg\Modules\Blogs\Controllers;

use Pg\Libraries\View;

/**
* Blogs admin side controller
*
* @package PG_Dating
* @subpackage application
* @category modules
* @copyright Pilot Group <http://www.pilotgroup.net/>
* @author Renat Gabdrakhmanov <renatgab@pilotgroup.net>
**/

Class Admin_Blogs extends \Controller 
{

    public function __construct() 
    {
        parent::__construct();
        $this->load->model('Menu_model');
        $this->Menu_model->set_menu_active_item('admin_menu', 'add_ons_items');
        $this->load->model('Blogs_model');
    }

    public function index($category = 'all', $order = 'date_created', $order_direction = 'ASC', $page = 1) 
    {
        // Это изличшне но иногда бывают случаи когда переменные пустые
        if (!$category)
            $category = 'all';
        if (!$order)
            $order = 'date_created';
        if (!$order_direction)
            $order_direction = 'ASC';
        if (!$page)
            $page = 1;
        $page = $page < 0 ? 1 : $page;
        $page = floor($page);
        $attrs = $search_params = array();
        // Грузим настройки
        $current_settings = isset($_SESSION["blogs_list"]) ? $_SESSION["blogs_list"] : array();
        if (!isset($current_settings["category"]))
            $current_settings["category"] = $category;
        if (!isset($current_settings["order"]))
            $current_settings["order"] = $order;
        if (!isset($current_settings["order_direction"]))
            $current_settings["order_direction"] = $order_direction;
        if (!isset($current_settings["page"]))
            $current_settings["page"] = $page;
        // Используем фильтрацию
        if ($category!='all') $attrs["where"]['category'] = $category;

        // Формируем пагинацию
        $items_on_page = $this->pg_module->get_module_config('start', 'admin_items_per_page');
        $blogs_count = $this->Blogs_model->get_blogs_count($attrs);
        $this->load->helper('sort_order');
        $page = get_exists_page_number($page, $blogs_count, $items_on_page);
        $current_settings["page"] = $page;
        // Сохраняем настройки
        $_SESSION["blogs_list"] = $current_settings;
        // Ссылки для сортировки ASC DESC
        $sort_links = array(
            "title" => site_url() . "admin/blogs/index/" . $category . "/title/" . (($order != 'title' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
            "date_created" => site_url() . "admin/blogs/index/" . $category . "/date_created/" . (($order != 'date_created' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
            "posts_count" => site_url() . "admin/blogs/index/" . $category . "/posts_count/" . (($order != 'posts_count' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
            "comments_count" => site_url() . "admin/blogs/index/" . $category . "/comments_count/" . (($order != 'comments_count' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
        );
        $this->view->assign('sort_links', $sort_links);
        // Получаем опросы
        if ($blogs_count > 0) {
            $blogs = $this->Blogs_model->get_blogs_list($page, $items_on_page, array($order => $order_direction), $attrs);          
            $this->view->assign('blogs', $blogs);
        }
        
        $this->load->helper("navigation");
        $url = site_url() . "admin/blogs/index/" . $category . "/" . $order . "/" . $order_direction . "/" . $page;
        $page_data = get_admin_pages_data($url, $blogs_count, $items_on_page, $page, 'briefPage');
        $page_data["date_format"] = $this->pg_date->get_format('date_time_literal', 'st');
        
        $categories = ld('blog_categories', 'blogs');
        $this->view->assign('categories', $categories);
        // Отображаем все       
        $this->view->assign('page_data', $page_data);
        $this->view->assign('category', $category);
        $this->view->assign('order', $order);
        $this->view->assign('order_direction', $order_direction);
        $this->view->assign('page', $page);
        $this->view->setHeader(l('admin_header_blogs_list', 'blogs'));
        $this->view->setBackLink(site_url() . 'admin/start/menu/add_ons_items');
        $this->view->render('list_blogs');
    }

    function activate_blog($blog_id, $status = 0) 
    {
        if (!empty($blog_id)) {
            $this->Blogs_model->save_blogs($blog_id, array('active'=>$status));
            if ($status)
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_activate_blog', 'blogs'));
            else
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_deactivate_blog', 'blogs'));
        }
        $cur_set = $_SESSION["blogs_list"];
        $url = site_url() . "admin/blogs/index/" . (isset($cur_set["category"]) ? $cur_set["category"] : 'all') . "/" . (isset($cur_set["order"]) ? $cur_set["order"] : 'date_created') . "/" . (isset($cur_set["order_direction"]) ? $cur_set["order_direction"] : 'ASC') . "/" . (isset($cur_set["page"]) ? $cur_set["page"] : 1) . "";
        redirect($url);
    }

    public function categories() 
    {
        $categories_ds = ld('blog_categories', 'blogs');
        $categories = array();
        $i=0;
        foreach ($categories_ds['option'] as $gid=>$name){
            $categories[$i]['gid'] = $gid;
            $categories[$i]['name'] = $name;
            $attrs["where"]['category'] = $gid;
            $categories[$i]['blogs_count'] = $this->Blogs_model->get_blogs_count($attrs);
            $i++;
        }
        
        $this->view->assign('categories', $categories);
    
        $this->view->setHeader(l('admin_header_blogs_categories', 'blogs'));
        $this->view->setBackLink(site_url() . 'admin/start/menu/add_ons_items');
        $this->view->render('list_categories');
    }

    public function edit_category($option_gid = null) 
    {
        
        if ($this->input->post('btn_save', true)) {
            $lang_data = $this->input->post('lang_data', true);
            $add_flag = false;

            if(empty($option_gid)){
                $post_option_gid = trim(strip_tags($this->input->post('option_gid', true)));
                $post_option_gid = preg_replace('/[^a-z0-9_\-\s]+/i', '', $post_option_gid);
                $post_option_gid = preg_replace('/[\s]+/i', '-', trim($post_option_gid));
                if(empty($post_option_gid)){
                    $errors[] = l('error_gid_incorrect', 'languages');
                }else{
                    $option_gid = $post_option_gid;
                }
            }

            if(empty($option_gid)){
                $add_flag = true;
                
                if(!empty($reference["option"])){
                    $array_keys = array_keys($reference["option"]);
                }else{
                    $array_keys = array(0);
                }
                $index = max($array_keys) + 1;
            }

            if(!empty($errors)){
                $this->system_messages->addMessage(View::MSG_ERROR, $errors);
            }else{
                foreach($lang_data as $lid => $string){
                    $reference = $this->pg_language->ds->get_reference('blogs', 'blog_categories', $lid);
                    $reference["option"][$option_gid] = $string;
                    $this->pg_language->ds->set_module_reference('blogs', 'blog_categories', $reference, $lid);
                }
                $this->system_messages->addMessage(View::MSG_SUCCESS, ($add_flag)?l('success_added_category', 'blogs'):l('success_updated_category', 'blogs'));
                $url = site_url()."admin/blogs/categories";
                redirect($url);
            }
        }
        
        $lang_data = array();
        if (!empty($option_gid)) {
            foreach($this->pg_language->languages as $lid => $lang){
                $r = $this->pg_language->ds->get_reference('blogs', 'blog_categories', $lid);
                $lang_data[$lid] = $r["option"][$option_gid];
            }
        }
        
        $this->view->assign('option_gid', $option_gid);
        $this->view->assign('lang_data', $lang_data);
        $this->view->assign('langs', $this->pg_language->languages);
        
        
        $this->view->setHeader(l('admin_header_blogs_categories', 'blogs'));
        $this->view->render('edit_categories');
    }

    public function delete_category($option_gid = null) 
    {
        if($option_gid){
            foreach($this->pg_language->languages as $lid => $lang){
                $reference = $this->pg_language->ds->get_reference('blogs', 'blog_categories', $lid);
                if(isset($reference["option"][$option_gid])){
                    unset($reference["option"][$option_gid]);
                    $this->pg_language->ds->set_module_reference('blogs', 'blog_categories', $reference, $lid);
                }
            }
        }
        $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_deleted_category', 'blogs'));
        $url = site_url()."admin/blogs/categories";
        redirect($url);
    }

    public function posts($id = 0, $order = 'date_created', $order_direction = 'ASC', $page = 1) 
    {
        // Получаем опрос и варианты ответов
        $blog_data = $this->Blogs_model->get_blog_by_id($id);
        if (empty($blog_data)) redirect(site_url()."admin/blogs");
        $this->view->assign('blog_data', $blog_data);
        
        if (!$order)
            $order = 'date_created';
        if (!$order_direction)
            $order_direction = 'ASC';
        if (!$page)
            $page = 1;
        $page = $page < 0 ? 1 : $page;
        $page = floor($page);
        $attrs = $search_params = array();
        // Грузим настройки
        $current_settings = isset($_SESSION["blogs_posts_list"]) ? $_SESSION["blogs_posts_list"] : array();
        if (!isset($current_settings["order"]))
            $current_settings["order"] = $order;
        if (!isset($current_settings["order_direction"]))
            $current_settings["order_direction"] = $order_direction;
        if (!isset($current_settings["page"]))
            $current_settings["page"] = $page;
        // Используем фильтрацию
         $attrs["where"]['blog_id'] = $id;

        // Формируем пагинацию
        $items_on_page = $this->pg_module->get_module_config('start', 'admin_items_per_page');
        $blog_posts_count = $this->Blogs_model->get_posts_count($attrs);
        $this->load->helper('sort_order');
        $page = get_exists_page_number($page, $blog_posts_count, $items_on_page);
        $current_settings["page"] = $page;
        // Сохраняем настройки
        $_SESSION["blogs_posts_list"] = $current_settings;
        // Ссылки для сортировки ASC DESC
        $sort_links = array(
            "title" => site_url() . "admin/blogs/posts/" . $id . "/title/" . (($order != 'title' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
            "date_created" => site_url() . "admin/blogs/posts/" . $id . "/date_created/" . (($order != 'date_created' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
        );
        $this->view->assign('sort_links', $sort_links);
        
        if ($blog_posts_count > 0) {
            $blog_posts = $this->Blogs_model->get_posts_list($page, $items_on_page, array($order => $order_direction), $attrs);         
            $this->view->assign('blog_posts', $blog_posts);
        }
        
        $this->load->helper("navigation");
        $url = site_url() . "admin/blogs/posts/" . $id . "/" . $order . "/" . $order_direction . "/" . $page;
        $page_data = get_admin_pages_data($url, $blog_posts_count, $items_on_page, $page, 'briefPage');
        $page_data["date_format"] = $this->pg_date->get_format('date_time_literal', 'st');

        $this->view->setBackLink(site_url()."admin/blogs");
        $this->view->setHeader(l('admin_header_posts_list', 'blogs'));
        $this->view->render('list_posts');
    }

    public function edit_post($id = 0) 
    {
        
        $blog_post_data = $this->Blogs_model->get_post_by_id($id);
        if (empty($blog_post_data)) redirect(site_url()."admin/blogs");
        
        if ($this->input->post('btn_save', true)) {
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
            } else {
                $save_post = $validate_data["data"];
                unset($save_post['tags']);
                $this->Blogs_model->save_post($id, $save_post);
                
                $tags = $validate_data["data"]['tags'];
                $this->Blogs_model->save_tags('blog_post', $id, $tags);
                
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_updated_post', 'blogs'));
                $url = site_url() . "admin/blogs/posts/" . $blog_post_data["blog_id"];
                redirect($url);
            }
        }
        
        $blog_post_data = $this->Blogs_model->get_post_by_id($id);
        $this->view->assign('data', $blog_post_data);
        
        $this->load->plugin('fckeditor');
        $content_fck = create_editor("body", isset($blog_post_data["body"]) ? $blog_post_data["body"] : "", 300, 200, 'Middle');
        $this->view->assign('content_fck', $content_fck);
        
        $this->view->setHeader(l('admin_header_posts_list', 'blogs'));
        $this->view->render('edit_post');
    }

    public function delete_blog($id = null) 
    {   
        $this->Blogs_model->delete_blog($id);
        
        $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_deleted_blog', 'blogs'));
        $url = site_url()."admin/blogs";
        redirect($url);
    }

    public function delete_post($id = null) 
    {
        $blog_post_data = $this->Blogs_model->get_post_by_id($id);
        if (empty($blog_post_data)) redirect(site_url()."admin/blogs");
        
        $this->Blogs_model->delete_post($id);
        
        $this->Blogs_model->save_blog($blog_post_data['blog_id'], array('comments_count'=>$this->Blogs_model->get_comments_count(array('where'=>array('blog_id'=>$blog_post_data['blog_id']))), 'posts_count'=>$this->Blogs_model->get_posts_count(array('where'=>array('blog_id'=>$blog_post_data['blog_id'])))));
        $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_deleted_post', 'blogs'));
        $cur_set = $_SESSION["blogs_posts_list"];
        $url = site_url() . "admin/blogs/posts/" . $blog_post_data['blog_id'] . "/" . (isset($cur_set["order"]) ? $cur_set["order"] : 'date_created') . "/" . (isset($cur_set["order_direction"]) ? $cur_set["order_direction"] : 'ASC') . "/" . (isset($cur_set["page"]) ? $cur_set["page"] : 1) . "";
        redirect($url);
    }

    public function delete_comment($id = null) 
    {
        $comment_data = $this->Blogs_model->get_comment_by_id($id);
        if (empty($comment_data)) redirect(site_url()."admin/blogs");
        
        $this->Blogs_model->delete_comment($id);
        
        $this->Blogs_model->save_blog($comment_data['blog_id'], array('comments_count'=>$this->Blogs_model->get_comments_count(array('where'=>array('blog_id'=>$comment_data['blog_id'])))));
        $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_deleted_comment', 'blogs'));
        $cur_set = $_SESSION["blogs_posts_comments_list"];
        $url = site_url() . "admin/blogs/comments/" . $comment_data['post_id'] . "/" . (isset($cur_set["order"]) ? $cur_set["order"] : 'date_created') . "/" . (isset($cur_set["order_direction"]) ? $cur_set["order_direction"] : 'ASC') . "/" . (isset($cur_set["page"]) ? $cur_set["page"] : 1) . "";
        redirect($url);
    }

    public function comments($id = 0, $order = 'date_created', $order_direction = 'ASC', $page = 1) 
    {
        
        $post_data = $this->Blogs_model->get_post_by_id($id);
        if (empty($post_data)) redirect(site_url()."admin/blogs");
        $this->view->assign('post_data', $post_data);
        
        if (!$order)
            $order = 'date_created';
        if (!$order_direction)
            $order_direction = 'ASC';
        if (!$page)
            $page = 1;
        $page = $page < 0 ? 1 : $page;
        $page = floor($page);
        $attrs = $search_params = array();
        // Грузим настройки
        $current_settings = isset($_SESSION["blogs_posts_comments_list"]) ? $_SESSION["blogs_posts_comments_list"] : array();
        if (!isset($current_settings["order"]))
            $current_settings["order"] = $order;
        if (!isset($current_settings["order_direction"]))
            $current_settings["order_direction"] = $order_direction;
        if (!isset($current_settings["page"]))
            $current_settings["page"] = $page;
        // Используем фильтрацию
         $attrs["where"]['post_id'] = $id;

        // Формируем пагинацию
        $items_on_page = $this->pg_module->get_module_config('start', 'admin_items_per_page');
        $comments_count = $this->Blogs_model->get_comments_count($attrs);
        $this->load->helper('sort_order');
        $page = get_exists_page_number($page, $comments_count, $items_on_page);
        $current_settings["page"] = $page;
        // Сохраняем настройки
        $_SESSION["blogs_posts_comments_list"] = $current_settings;
        // Ссылки для сортировки ASC DESC
        $sort_links = array(
            "title" => site_url() . "admin/blogs/comments/" . $id . "/title/" . (($order != 'title' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
            "date_created" => site_url() . "admin/blogs/comments/" . $id . "/date_created/" . (($order != 'date_created' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
        );
        $this->view->assign('sort_links', $sort_links);
        // Получаем опросы
        if ($comments_count > 0) {
            $comments = $this->Blogs_model->get_comments_list($page, $items_on_page, array($order => $order_direction), $attrs);    
            $this->view->assign('comments', $comments);
        }
        
        $this->load->helper("navigation");
        $url = site_url() . "admin/blogs/comments/" . $id . "/" . $order . "/" . $order_direction . "/" . $page;
        $page_data = get_admin_pages_data($url, $comments_count, $items_on_page, $page, 'briefPage');
        $page_data["date_format"] = $this->pg_date->get_format('date_time_literal', 'st');

        $this->view->setBackLink(site_url()."admin/blogs/posts/".$post_data['blog_id']);
        $this->view->setHeader(l('admin_header_comments_list', 'blogs'));
        $this->view->render('list_comments');
    }

    public function edit_comment($id = 0) 
    {
        $comment_data = $this->Blogs_model->get_comment_by_id($id);
        if (empty($comment_data)) redirect(site_url()."admin/blogs");
        
        if ($this->input->post('btn_save', true)) {
            $post_data = array(
                "title" => $this->input->post('title', true),
                "body" => $this->input->post('body', true),
            );
            $validate_data = $this->Blogs_model->validate_post($post_data);
            if (!empty($validate_data["errors"])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data["errors"]);
            } else {
                $save_post = $validate_data["data"];
                unset($save_post['tags']);
                $this->Blogs_model->save_comment($id, $save_post);
                
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_updated_comment', 'blogs'));
                $url = site_url() . "admin/blogs/comments/" . $comment_data["post_id"];
                redirect($url);
            }
        }
        
        //$comment_data = $this->Blogs_model->get_post_by_id($id);
        $this->view->assign('data', $comment_data);
        
        $this->load->plugin('fckeditor');
        $content_fck = create_editor("body", isset($comment_data["body"]) ? $comment_data["body"] : "", 300, 200, 'Middle');
        $this->view->assign('content_fck', $content_fck);
        
        $this->view->setBackLink(site_url() . 'admin/blogs/comments/' . $comment_data['post_id']);
        $this->view->setHeader(l('admin_header_comments_list', 'blogs'));
        $this->view->render('edit_comment');
    }

}
