<?php

namespace Pg\Modules\Forum\Controllers;

use Pg\Libraries\View;

/**
* Forum admin side controller
*
* @package PG_Dating
* @subpackage application
* @category	modules
* @copyright Pilot Group <http://www.pilotgroup.net/>
* @author Renat Gabdrakhmanov <renatgab@pilotgroup.net>
**/

Class Admin_Forum extends \Controller 
{

	function __construct() 
	{
		parent::__construct();
		$this->load->model('Menu_model');
		$this->Menu_model->set_menu_active_item('admin_menu', 'add_ons_items');
	}

	public function index($type = 'public', $page = 1) 
	{
		$this->load->model('forum/models/Forum_categories_model');	
		
		if (!$type)
			$type = 'public';
		if (!$page)
			$page = 1;
		$page = $page < 0 ? 1 : $page;
		$page = floor($page);
		$attrs = $search_params = array();
		
		$current_settings = isset($_SESSION["forum_categories_list"]) ? $_SESSION["forum_categories_list"] : array();
		if (!isset($current_settings["type"]))
			$current_settings["type"] = $type;
		if (!isset($current_settings["page"]))
			$current_settings["page"] = $page;
		
		$attrs["where"]['cat_type'] = $type;

		// Формируем пагинацию
		$items_on_page = $this->pg_module->get_module_config('start', 'admin_items_per_page');
		$categories_count = $this->Forum_categories_model->get_count($attrs);
		$this->load->helper('sort_order');
		$page = get_exists_page_number($page, $categories_count, $items_on_page);
		$current_settings["page"] = $page;
		// Сохраняем настройки
		$_SESSION["forum_categories_list"] = $current_settings;
		
		// Получаем опросы
		if ($categories_count > 0) {
			$categories = $this->Forum_categories_model->get_list($page, $items_on_page, array('sorter' => 'ASC'), $attrs);			
			$this->view->assign('categories', $categories);
		}
		
		$this->load->helper("navigation");
		$url = site_url() . "admin/forum/index/" . $type . "/";
		$page_data = get_admin_pages_data($url, $categories_count, $items_on_page, $page, 'briefPage');
		$page_data["date_format"] = $this->pg_date->get_format('date_time_literal', 'st');
		
		// Отображаем все		
		$this->view->assign('page_data', $page_data);
		$this->view->assign('type', $type);
		$this->view->assign('page', $page);
		$this->view->assign('categories_count', $categories_count);
		$this->view->setHeader(l('admin_header_forum', 'forum'));
		$this->view->setBackLink(site_url() . 'admin/start/menu/add_ons_items');
		$this->view->render('list_categories');
	}

	public function sorting() 
	{
		$this->load->model('forum/models/Forum_categories_model');	
		
		$type = 'public';
		$attrs["where"]['cat_type'] = $type;
		$categories = $this->Forum_categories_model->get_list(null, null, array('sorter' => 'ASC'), $attrs);			
		$this->view->assign('categories', $categories);
		
		$this->view->setHeader(l('admin_header_forum', 'forum'));
		$this->Menu_model->set_menu_active_item('admin_forum_menu', 'forum-index-item');
		$this->view->render('sorting_categories');
	}

	public function ajax_category_sort() 
	{
		$item_data = $this->input->post('sorter');
		$item_data = $item_data["parent_0"];
		if(empty($item_data)) return false;
		$this->load->model('forum/models/Forum_categories_model');	

		foreach($item_data as $key => $sorter){
			$section_id = intval(str_replace("item_", "", $key));
			if(empty($section_id)) continue;
			$this->Forum_categories_model->save($section_id, array('sorter'=>$sorter));
		}
		return true;
	}

	public function edit_category($id = null) 
	{
		$this->load->model('forum/models/Forum_categories_model');			
		
		if ($id) {
			$caterory = $this->Forum_categories_model->get_by_id($id);
			$this->view->assign('data', $caterory);
		}
		
		if ($this->input->post('btn_save', true)) {
			$post_data = array(
				"category" => $this->input->post('category', true),
				"description" => $this->input->post('description', true),
			);
			$validate_data = $this->Forum_categories_model->validate($post_data);
			if (!empty($validate_data["errors"])) {
				$this->system_messages->addMessage(View::MSG_ERROR, $validate_data["errors"]);
				$this->view->assign('data', $validate_data["data"]);
			} else {
				$save_data = $validate_data["data"];
				if (!$id){
					$save_data['cat_type'] = 'public';
					$attrs["where"]['cat_type'] = $type;
					$categories_count = $this->Forum_categories_model->get_count($attrs);
					$save_data['sorter'] = $categories_count+1;
				}
				$this->Forum_categories_model->save($id, $save_data);
				
				$this->system_messages->addMessage(View::MSG_SUCCESS, ($id)?l('success_updated_category', 'forum'):l('success_added_category', 'forum'));
				$url = site_url() . "admin/forum/";
				redirect($url);
			}
		}
		
		$this->view->setHeader(l('admin_header_forum', 'forum'));
		$this->view->render('edit_category');
	}

	public function delete_category($id = null) 
	{
		$this->load->model('forum/models/Forum_categories_model');	
		$this->Forum_categories_model->delete($id);
		
		$this->system_messages->addMessage(View::MSG_SUCCESS, l('success_deleted_category', 'forum'));
		$url = site_url()."admin/forum";
		redirect($url);
	}

	public function subcategories($category_id = 0, $page = 1) 
	{
		$this->load->model('forum/models/Forum_categories_model');	
		
		$category = $this->Forum_categories_model->get_by_id($category_id);
		if (empty($category)) redirect(site_url()."admin/forum");
		
		if (!$page) $page = 1;
		$page = $page < 0 ? 1 : $page;
		$page = floor($page);
		$attrs = array();
		
		$current_settings = isset($_SESSION["forum_subcategories_list"]) ? $_SESSION["forum_subcategories_list"] : array();
		$current_settings["category_id"] = $category_id;
		if (!isset($current_settings["page"]))
			$current_settings["page"] = $page;
		
		$attrs["where"]['category_id'] = $category_id;

		// Формируем пагинацию
		$this->load->model('forum/models/Forum_subcategories_model');	
		$items_on_page = $this->pg_module->get_module_config('start', 'admin_items_per_page');
		$subcategories_count = $this->Forum_subcategories_model->get_count($attrs);
		$this->load->helper('sort_order');
		$page = get_exists_page_number($page, $subcategories_count, $items_on_page);
		$current_settings["page"] = $page;
		// Сохраняем настройки
		$_SESSION["forum_subcategories_list"] = $current_settings;
		
		// Получаем опросы
		if ($subcategories_count > 0) {
			$subcategories = $this->Forum_subcategories_model->get_list($page, $items_on_page, array('id' => 'ASC'), $attrs);			
			$this->view->assign('subcategories', $subcategories);
		}
		
		$this->load->helper("navigation");
		$url = site_url() . "admin/forum/subcategories/" . $category_id . "/";
		$page_data = get_admin_pages_data($url, $subcategories_count, $items_on_page, $page, 'briefPage');
		$page_data["date_format"] = $this->pg_date->get_format('date_time_literal', 'st');
		
		// Отображаем все		
		$this->view->assign('page_data', $page_data);
		$this->view->assign('category', $category);
		$this->view->assign('page', $page);
		$this->view->assign('subcategories_count', $subcategories_count);
		$this->view->setHeader(l('admin_header_forum', 'forum'));
		
		$this->Menu_model->set_menu_active_item('admin_forum_menu', 'forum-index-item');
		$this->view->setBackLink(site_url() . "admin/forum");
		
		$this->view->render('list_subcategories');
	}

	public function edit_subcategory($category_id, $id = null) 
	{
		$this->load->model('forum/models/Forum_subcategories_model');	
		$this->view->assign('category_id', $category_id);		
		
		if ($id) {
			$subcaterory = $this->Forum_subcategories_model->get_by_id($id);
		}
		
		if ($this->input->post('btn_save', true)) {
			if ($id){
				$post_data = array(
					"subcategory" => $this->input->post('subcategory', true),
				);
			} else{
				$post_data = array(
					"subcategory" => $this->input->post('subcategory', true),
					"subject" => $this->input->post('subject', true),
					"message" => $this->input->post('message'),
				);
			}
			$validate_data = $this->Forum_subcategories_model->validate($post_data);
			if (!empty($validate_data["errors"])) {
				$subcaterory = $validate_data["data"];
				$this->system_messages->addMessage(View::MSG_ERROR, $validate_data["errors"]);
			} else {
				$save_category = array();
				$save_subcategory = array();
				$save_message = array();
				if ($id){
					$save_subcategory = $validate_data["data"];
				} else{
					$save_subcategory['subcategory'] = $validate_data["data"]['subcategory'];
					$save_subcategory['category_id'] = $category_id;
					$save_subcategory['user_id'] = 0;
					$save_subcategory['is_admin'] = 1;
					$save_subcategory['messages_count'] = 1;
					
					$save_message['subject'] = $validate_data["data"]['subject'];
					$save_message['message'] = $validate_data["data"]['message'];
					$save_message['category_id'] = $category_id;
					$save_message['user_id'] = 0;
					$save_message['is_admin'] = '1';
				}
				$new_id = $this->Forum_subcategories_model->save($id, $save_subcategory);
				$this->load->model('forum/models/Forum_categories_model');
				$attrs["where"]['category_id'] = $category_id;
				$save_category['subcategory_count'] = $this->Forum_subcategories_model->get_count($attrs);
				if (!$id) {
					$this->load->model('forum/models/Forum_messages_model');
					$save_message['subcategory_id'] = $new_id;
					$this->Forum_messages_model->save(null, $save_message);
					$save_category['messages_count'] = $this->Forum_messages_model->get_count($attrs);
				}
				$this->Forum_categories_model->save($category_id, $save_category);
				
				$this->system_messages->addMessage(View::MSG_SUCCESS, ($id)?l('success_updated_subcategory', 'forum'):l('success_added_subcategory', 'forum'));
				$url = site_url() . "admin/forum/subcategories/".$category_id;
				redirect($url);
			}
		}
		
		$this->load->plugin('fckeditor');
		$content_fck = create_editor("message", isset($subcaterory["message"]) ? $subcaterory["message"] : "", 300, 200, 'Middle');
		$this->view->assign('content_fck', $content_fck);
		$this->view->assign('data', $subcaterory);	
		
		$this->view->setHeader(l('admin_header_forum', 'forum'));
		$this->view->render('edit_subcategory');
	}

	public function delete_subcategory($id = null, $category_id) 
	{
		$this->load->model('forum/models/Forum_categories_model');	
		$this->load->model('forum/models/Forum_subcategories_model');	
		$this->load->model('forum/models/Forum_messages_model');
		$where['id'] = $id;
		$this->Forum_subcategories_model->delete($where);
		$where_messages['subcategory_id'] = $id;
		$this->Forum_messages_model->delete($where_messages);
		$attrs["where"]['category_id'] = $category_id;
		$save_category = array();
		$save_category['subcategory_count'] = $this->Forum_subcategories_model->get_count($attrs);
		$save_category['messages_count'] = $this->Forum_messages_model->get_count($attrs);
		
		$this->Forum_categories_model->save($category_id, $save_category);

		$this->system_messages->addMessage(View::MSG_SUCCESS, l('success_deleted_subcategory', 'forum'));
		$url = site_url()."admin/forum/subcategories/".$category_id;
		redirect($url);
	}

	public function messages($subcategory_id = 0, $page = 1) 
	{
		$this->load->model('forum/models/Forum_subcategories_model');
		
		$subcategory = $this->Forum_subcategories_model->get_by_id($subcategory_id);
		if (empty($subcategory)) redirect(site_url()."admin/forum");
		
		$this->load->model('forum/models/Forum_categories_model');	
		$category = $this->Forum_categories_model->get_by_id($subcategory['category_id']);
		if (empty($category)) redirect(site_url()."admin/forum");
		
		if (!$page) $page = 1;
		$page = $page < 0 ? 1 : $page;
		$page = floor($page);
		$attrs = array();
		
		$current_settings = isset($_SESSION["forum_messages_list"]) ? $_SESSION["forum_messages_list"] : array();
		$current_settings["subcategory_id"] = $subcategory_id;
		if (!isset($current_settings["page"]))
			$current_settings["page"] = $page;
		
		$attrs["where"]['subcategory_id'] = $subcategory_id;

		// Формируем пагинацию
		$this->load->model('forum/models/Forum_messages_model');	
		$items_on_page = $this->pg_module->get_module_config('start', 'admin_items_per_page');
		$messages_count = $this->Forum_messages_model->get_count($attrs);
		$this->load->helper('sort_order');
		$page = get_exists_page_number($page, $messages_count, $items_on_page);
		$current_settings["page"] = $page;
		// Сохраняем настройки
		$_SESSION["forum_messages_list"] = $current_settings;
		
		// Получаем опросы
		if ($messages_count > 0) {
			$messages = $this->Forum_messages_model->get_list($page, $items_on_page, array('id' => 'ASC'), $attrs);			
			$this->view->assign('messages', $messages);
		}
		
		$this->load->helper("navigation");
		$url = site_url() . "admin/forum/messages/" . $subcategory_id . "/";
		$page_data = get_admin_pages_data($url, $messages_count, $items_on_page, $page, 'briefPage');
		$page_data["date_format"] = $this->pg_date->get_format('date_time_literal', 'st');
		
		// Отображаем все		
		$this->view->assign('page_data', $page_data);
		$this->view->assign('category', $category);
		$this->view->assign('subcategory', $subcategory);
		$this->view->assign('page', $page);
		$this->view->assign('categories_count', $categories_count);
		$this->view->setHeader(l('admin_header_forum', 'forum'));
		
		$this->Menu_model->set_menu_active_item('admin_forum_menu', 'forum-index-item');
		$this->view->setBackLink(site_url()."admin/forum/subcategories/".$subcategory['category_id']);
		
		$this->view->render('list_messages');
	}

	public function edit_message($category_id, $subcategory_id, $id = null) 
	{
		$this->load->model('forum/models/Forum_messages_model');	
		$this->view->assign('subcategory_id', $subcategory_id);	
		$this->view->assign('category_id', $category_id);		
		
		if ($id) {
			$message = $this->Forum_messages_model->get_by_id($id);
		}
		
		if ($this->input->post('btn_save', true)) {
				$post_data = array(
					"subject" => $this->input->post('subject', true),
					"message" => $this->input->post('message'),
				);
			$validate_data = $this->Forum_messages_model->validate($post_data);
			if (!empty($validate_data["errors"])) {
				$message = $validate_data["data"];
				$this->system_messages->addMessage(View::MSG_ERROR, $validate_data["errors"]);
			} else {
				$save_category = array();
				$save_subcategory = array();
				$save_message = array();
				$this->load->model('forum/models/Forum_subcategories_model');
				$this->load->model('forum/models/Forum_categories_model');
				if ($id){
					$save_message = $validate_data["data"];
				} else{					
					$save_message['subject'] = $validate_data["data"]['subject'];
					$save_message['message'] = $validate_data["data"]['message'];
					$save_message['subcategory_id'] = $subcategory_id;
					$save_message['category_id'] = $category_id;
					$save_message['user_id'] = 0;
					$save_message['is_admin'] = '1';
				}
				$new_id = $this->Forum_messages_model->save($id, $save_message);
				if (!$id){
					$attrs = array();
					$attrs['subcategory_id'] = $subcategory_id;
					$save_subcategory['messages_count'] = $this->Forum_messages_model->get_count($attrs);
					$this->Forum_subcategories_model->save($subcategory_id, $save_subcategory);
					
					$attrs = array();
					$attrs["where"]['category_id'] = $category_id;
					$save_category['subcategory_count'] = $this->Forum_subcategories_model->get_count($attrs);
					$save_category['messages_count'] = $this->Forum_messages_model->get_count($attrs);
					$this->Forum_categories_model->save($category_id, $save_category);
				}
				
				$this->system_messages->addMessage(View::MSG_SUCCESS, ($id)?l('success_updated_message', 'forum'):l('success_added_message', 'forum'));
				$url = site_url() . "admin/forum/messages/".$subcategory_id;
				redirect($url);
			}
		}
		
		$this->load->plugin('fckeditor');
		$content_fck = create_editor("message", isset($message["message"]) ? $message["message"] : "", 300, 200, 'Middle');
		$this->view->assign('content_fck', $content_fck);
		$this->view->assign('data', $message);	
		
		$this->view->setHeader(l('admin_header_forum', 'forum'));
		$this->view->render('edit_message');
	}

	public function delete_message($category_id, $subcategory_id, $id = null) 
	{
		$this->load->model('forum/models/Forum_categories_model');	
		$this->load->model('forum/models/Forum_subcategories_model');	
		$this->load->model('forum/models/Forum_messages_model');
		$where['id'] = $id;
		$this->Forum_messages_model->delete($where);
		
		$attrs = array();
		$attrs['subcategory_id'] = $subcategory_id;
		$save_subcategory['messages_count'] = $this->Forum_messages_model->get_count($attrs);
		$this->Forum_subcategories_model->save($subcategory_id, $save_subcategory);
		
		$attrs = array();
		$attrs["where"]['category_id'] = $category_id;
		$save_category['messages_count'] = $this->Forum_messages_model->get_count($attrs);
		$this->Forum_categories_model->save($category_id, $save_category);

		$this->system_messages->addMessage(View::MSG_SUCCESS, l('success_deleted_message', 'forum'));
		$url = site_url()."admin/forum/messages/".$subcategory_id;
		redirect($url);
	}
	
	public function edit_group_category($group_id, $category_id = null) 
	{
		$this->load->model('forum/models/Forum_categories_model');			
		
		if ($category_id){
			$caterory = $this->Forum_categories_model->get_by_id($category_id);
			$this->view->assign('data', $caterory);
		}
		
		if ($this->input->post('btn_save', true)) {
			$post_data = array(
				"category" => $this->input->post('category', true),
				"description" => $this->input->post('description', true),
			);
			$validate_data = $this->Forum_categories_model->validate($post_data);
			if (!empty($validate_data["errors"])) {
				$this->system_messages->addMessage(View::MSG_ERROR, $validate_data["errors"]);
				$this->view->assign('data', $validate_data["data"]);
			} else {
				$save_data = $validate_data["data"];
				if (!$category_id){
					$save_data['cat_type'] = 'club';
					$save_data['item_id'] = $group_id;
					$attrs["where"]['cat_type'] = 'club';
					$attrs["where"]['item_id'] = $group_id;
					$categories_count = $this->Forum_categories_model->get_count($attrs);
					$save_data['sorter'] = $categories_count+1;
				}
				
				$this->Forum_categories_model->save($category_id, $save_data);
				
				$this->system_messages->addMessage(View::MSG_SUCCESS, ($category_id)?l('success_updated_category', 'forum'):l('success_added_category', 'forum'));
			}
		}
		
		redirect(site_url().'admin/groups/view/'.$group_id);	
	}

}
