<?php

/**
* Forum helper
*
* @package PG_Dating
* @subpackage application
* @category	helpers
* @copyright Pilot Group <http://www.pilotgroup.net/>
* @author Renat Gabdrakhmanov <renatgab@pilotgroup.net>
**/

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

if ( ! function_exists('group_discussions')) {
	function group_discussions($params = array()) 
	{
		$ci = & get_instance();

		if (empty($params['group_id'])) return '';
		
		$data['group_id'] = intval($params['group_id']);
		$data['is_leader'] = isset($params['is_leader']) ? intval($params['is_leader']) : 0;

		$ci->load->model('forum/models/Forum_categories_model');
		$attrs["where"]['cat_type'] = 'club';
		$attrs["where"]['item_id'] = $data['group_id'];
		$categories = $ci->Forum_categories_model->get_list(null, null, array('sorter' => 'ASC'), $attrs);			
		$ci->view->assign('categories', $categories);
		
		$ci->view->assign('group_discussions_data', $data);
		return $ci->view->fetch('helper_group_discussions', 'user', 'forum');
	}
}
if ( ! function_exists('admin_group_discussions')) {
	function admin_group_discussions($params = array()) 
	{
		$ci = & get_instance();
		if (empty($params['group_id'])) return '';
		
		$data['group_id'] = intval($params['group_id']);
		$data['is_leader'] = isset($params['is_leader']) ? intval($params['is_leader']) : 0;

		$ci->load->model('forum/models/Forum_categories_model');
		$attrs["where"]['cat_type'] = 'club';
		$attrs["where"]['item_id'] = $data['group_id'];
		$categories = $ci->Forum_categories_model->get_list(null, null, array('sorter' => 'ASC'), $attrs);			
		$ci->view->assign('categories', $categories);
		
		$ci->view->assign('group_discussions_data', $data);
		return $ci->view->fetch('helper_admin_group_discussions', 'admin', 'forum');
	}
}
