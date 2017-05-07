<?php
namespace Pg\Modules\Forum\Controllers;

use Pg\Libraries\View;

/**
* Forum user side controller
*
* @package PG_Dating
* @subpackage application
* @category	modules
* @copyright Pilot Group <http://www.pilotgroup.net/>
* @author Renat Gabdrakhmanov <renatgab@pilotgroup.net>
**/

Class Forum extends \Controller 
{

	function __construct() 
	{
		parent::__construct();
	}

	public function index($page = 1) 
	{
		$this->load->model('forum/models/Forum_categories_model');	
		
		$attrs["where"]['cat_type'] = 'public';

		// Формируем пагинацию
		$items_on_page = $this->pg_module->get_module_config('start', 'index_items_per_page');
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
		$url = site_url() . "forum/index/";
		$page_data = get_user_pages_data($url, $categories_count, $items_on_page, $page, 'briefPage');
		$page_data["date_format"] = $this->pg_date->get_format('date_time_literal', 'st');
		
		// Отображаем все		
		$this->view->assign('page_data', $page_data);
		$this->view->assign('page', $page);
		$this->view->assign('categories_count', $categories_count);
		
		$this->view->render('list_categories');
	}

	public function topics($category_id = 0, $page = 1) 
	{
		$this->load->model('forum/models/Forum_categories_model');	
		
		$category = $this->Forum_categories_model->get_by_id($category_id);
		if (empty($category)) redirect(site_url()."forum", 'hard');
		
		if (!$page) $page = 1;
		$page = $page < 0 ? 1 : $page;
		$page = floor($page);
		$attrs = array();
		
		$attrs["where"]['category_id'] = $category_id;

		// Формируем пагинацию
		$this->load->model('forum/models/Forum_subcategories_model');	
		$items_on_page = $this->pg_module->get_module_config('start', 'index_items_per_page');
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
		$url = site_url() . "forum/topics/" . $category_id . "/";
		$page_data = get_user_pages_data($url, $subcategories_count, $items_on_page, $page, 'briefPage');
		$page_data["date_format"] = $this->pg_date->get_format('date_time_literal', 'st');
		
		// Отображаем все		
		$this->view->assign('page_data', $page_data);
		$this->view->assign('category', $category);
		$this->view->assign('page', $page);
		$this->view->assign('subcategories_count', $subcategories_count);
		$this->view->setBackLink(site_url() . 'forum');
		$this->view->render('list_subcategories');
	}
	
	public function edit_topic($category_id, $id = null) 
	{
		$this->load->model('forum/models/Forum_subcategories_model');	
		$this->view->assign('category_id', $category_id);		
		
		if ($id) {
			$subcaterory = $this->Forum_subcategories_model->get_by_id($id);
		} else {
			$subcaterory = array();
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
					$save_subcategory['user_id'] = $this->session->userdata('user_id');
					$save_subcategory['is_admin'] = 0;
					$save_subcategory['messages_count'] = 1;
					
					$save_message['subject'] = $validate_data["data"]['subject'];
					$save_message['message'] = $validate_data["data"]['message'];
					$save_message['category_id'] = $category_id;
					$save_message['user_id'] = $this->session->userdata('user_id');
					$save_message['is_admin'] = 0;
				}
				$new_id = $this->Forum_subcategories_model->save($id, $save_subcategory);
				$this->load->model('forum/models/Forum_categories_model');
				$attrs["where"]['category_id'] = $category_id;
				$save_category['subcategory_count'] = $this->Forum_subcategories_model->get_count($attrs);
				if (!$id){
					$this->load->model('forum/models/Forum_messages_model');
					$save_message['subcategory_id'] = $new_id;
					$this->Forum_messages_model->save(null, $save_message);
					$save_category['messages_count'] = $this->Forum_messages_model->get_count($attrs);
				}
				$this->Forum_categories_model->save($category_id, $save_category);
				
				$this->system_messages->addMessage(View::MSG_SUCCESS, ($id)?l('success_updated_subcategory', 'forum'):l('success_added_subcategory', 'forum'));
				$url = site_url() . "forum/messages/".$new_id;
				redirect($url, 'hard');
			}
		}
		
		$this->view->assign('data', $subcaterory);	
		$this->view->setBackLink(site_url() . 'forum/topics/' . $id);
		$this->view->render('edit_subcategory');
	}

	public function messages($subcategory_id = 0, $page = 1) 
	{
		$this->load->model('forum/models/Forum_subcategories_model');
		
		$subcategory = $this->Forum_subcategories_model->get_by_id($subcategory_id);
		if (empty($subcategory)) redirect(site_url()."forum", 'hard');
		
		$this->load->model('forum/models/Forum_categories_model');	
		$category = $this->Forum_categories_model->get_by_id($subcategory['category_id']);
		if (empty($category)) redirect(site_url()."forum", 'hard');
		
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
		$items_on_page = $this->pg_module->get_module_config('start', 'index_items_per_page');
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
		$url = site_url() . "forum/messages/" . $subcategory_id . "/";
		$page_data = get_user_pages_data($url, $messages_count, $items_on_page, $page, 'briefPage');
		$page_data["date_format"] = $this->pg_date->get_format('date_time_literal', 'st');
		
		// Отображаем все		
		$this->view->assign('page_data', $page_data);
		$this->view->assign('category', $category);
		$this->view->assign('subcategory', $subcategory);
		$this->view->assign('page', $page);
		/*$this->view->assign('categories_count', $categories_count);*/
		/*$this->view->setHeader(l('admin_header_forum', 'forum'));*/
		$this->view->setBackLink(site_url() . 'forum/topics/' . $subcategory['category_id']);
		$this->view->render('list_messages');
	}

	public function edit_message($subcategory_id, $id = null, $quote_id = null) 
	{
		$this->load->model('forum/models/Forum_subcategories_model');	
		
		$subcategory = $this->Forum_subcategories_model->get_by_id($subcategory_id);
		if (empty($subcategory)) redirect(site_url()."forum", 'hard');
		
		$this->load->model('forum/models/Forum_categories_model');	
		$category_id = $subcategory['category_id'];
		$category = $this->Forum_categories_model->get_by_id($category_id);
		if (empty($category)) redirect(site_url()."forum", 'hard');
		
		$this->view->assign('subcategory_id', $subcategory_id);	
		$this->view->assign('category_id', $category_id);		
		
		$this->load->model('forum/models/Forum_messages_model');	
		if ($id){
			$message = $this->Forum_messages_model->get_by_id($id);
		} else {
			$message = array();
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
					$save_message['user_id'] = $this->session->userdata('user_id');
					$save_message['is_admin'] = 0;
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
				$url = site_url() . "forum/messages/".$subcategory_id;
				redirect($url, 'hard');
			}
		}
		if (empty($message) && $quote_id) {
			$quote_message = $this->Forum_messages_model->get_by_id($quote_id);
			if ($quote_message){
				$message['subject'] = "RE: ".$quote_message['subject'];
				if ($quote_message['is_admin']=='1') $quote_message["user"]["output_name"] = l('admin_name', 'forum');
				$message['message'] = '<div style="background-color:#F5F5F5; padding:5px;"><p class="quote_login1" style="background-color:#F5F5F5;"><strong>'.$quote_message["user"]["output_name"].'</strong></p><div class="quote_message1" style="background-color:#F5F5F5; font-style: italic;">'.$quote_message["message"].'</div></div><p></p>';
			}
		}
		$this->view->assign('data', $message);	
		$this->view->render('edit_message');
	}

	public function delete_message($id = null) 
	{
		$this->load->model('forum/models/Forum_categories_model');	
		$this->load->model('forum/models/Forum_subcategories_model');	
		$this->load->model('forum/models/Forum_messages_model');
		
		$message = $this->Forum_messages_model->get_by_id($id);
		if (!empty($message) && ($message['user_id']==$this->session->userdata('user_id'))){
			$category_id = $message['category_id'];
			$subcategory_id = $message['subcategory_id']; 
			
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
			$url = site_url() . "forum/messages/" . $subcategory_id;
		} else{
			$url = site_url() . "forum";
		}
		redirect($url, 'hard');
	}

	/*public function ajax_category_form($group_id, $category_id = 0) 
	{
		$this->view->assign('group_id', $group_id);
		
		if ($category_id) {
			$this->load->model('forum/models/Forum_categories_model');	
			$category = $this->Forum_categories_model->get_by_id($category_id);
			$this->view->assign('category', $category);
		}
		
		$this->view->render('ajax_category_form');
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
				if (!$category_id) {
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
		
		redirect(site_url().'groups/view/'.$group_id, 'hard');	
	}
	
	public function delete_category($id = null, $group_id = null) 
	{
		if (!$id || !$group_id)
			redirect(site_url()."forum");
		
		$this->load->model('forum/models/Forum_categories_model');	
		$this->Forum_categories_model->delete($id);
		
		$this->system_messages->addMessage(View::MSG_SUCCESS, l('success_deleted_category', 'forum'));
		redirect(site_url().'groups/view/'.$group_id, 'hard');	
	}*/

}
