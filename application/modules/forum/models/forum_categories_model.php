<?php
namespace Pg\Modules\Forum\Models;

/**
* Forum categories model
*
* @package PG_Dating
* @subpackage application
* @category	modules
* @copyright Pilot Group <http://www.pilotgroup.net/>
* @author Renat Gabdrakhmanov <renatgab@pilotgroup.net>
**/

if (!defined('FORUM_CATEGORIES_TABLE'))
	define('FORUM_CATEGORIES_TABLE', DB_PREFIX . 'forum_categories');

class Forum_categories_model extends \Model 
{

	private $ci;
	
	private $fields = array(
		'id',
		'category',
		'description',
		'sorter',
		'cat_type',
		'item_id',
		'subcategory_count',
		'messages_count',
	);

	function __construct() 
	{
		parent::__construct();
		$this->ci = & get_instance();
	}
	

	public function get_by_id($id = 0) 
	{
		$this->ci->db->from(FORUM_CATEGORIES_TABLE)->where("id", $id);
		$results = $this->ci->db->get()->result_array();
		if (!empty($results) && is_array($results)) {
			$results = $this->format_items($results);
			return $results[0];
		}
		return array();
	}

	function get_count($params = array(), $filter_object_ids = null) 
	{
		$this->ci->db->select("COUNT(*) AS cnt");
		$this->ci->db->from(FORUM_CATEGORIES_TABLE);
		if (isset($params["where"]) && is_array($params["where"]) && count($params["where"])) {
			foreach ($params["where"] as $field => $value) {
				$this->ci->db->where($field, $value);
			}
		}
		if (isset($params["where_in"]) && is_array($params["where_in"]) && count($params["where_in"])) {
			foreach ($params["where_in"] as $field => $value) {
				$this->ci->db->where_in($field, $value);
			}
		}
		if (isset($params["where_not_in"]) && is_array($params["where_not_in"]) && count($params["where_not_in"])) {
			foreach ($params["where_not_in"] as $field => $value) {
				$this->ci->db->where_not_in($field, $value);
			}
		}
		if (isset($params["where_sql"]) && is_array($params["where_sql"]) && count($params["where_sql"])) {
			foreach ($params["where_sql"] as $value) {
				$this->ci->db->where($value, null, false);
			}
		}
		if (isset($params["like"]) && is_array($params["like"]) && count($params["like"])) {
			foreach ($params["like"] as $field => $value) {
				$this->ci->db->or_like($field, $value);
			}
		}
		if (isset($filter_object_ids) && is_array($filter_object_ids) && count($filter_object_ids)) {
			$this->ci->db->where_in("id", $filter_object_ids);
		}
		$result = $this->ci->db->get()->result();
		if (!empty($result)) {
			return intval($result[0]->cnt);
		} else {
			return 0;
		}
	}

	function get_list($page = null, $items_on_page = null, $order_by = null, $params = array(), $filter_object_ids = null) 
	{
		$this->ci->db->from(FORUM_CATEGORIES_TABLE);
		if (isset($params["where"]) && is_array($params["where"]) && count($params["where"])) {
			foreach ($params["where"] as $field => $value) {
				$this->ci->db->where($field, $value);
			}
		}
		if (isset($params["where_in"]) && is_array($params["where_in"]) && count($params["where_in"])) {
			foreach ($params["where_in"] as $field => $value) {
				$this->ci->db->where_in($field, $value);
			}
		}
		if (isset($params["where_not_in"]) && is_array($params["where_not_in"]) && count($params["where_not_in"])) {
			foreach ($params["where_not_in"] as $field => $value) {
				$this->ci->db->where_not_in($field, $value);
			}
		}
		if (isset($params["where_sql"]) && is_array($params["where_sql"]) && count($params["where_sql"])) {
			foreach ($params["where_sql"] as $value) {
				$this->ci->db->where($value, null, false);
			}
		}
		if (isset($filter_object_ids) && is_array($filter_object_ids) && count($filter_object_ids)) {
			$this->ci->db->where_in("id", $filter_object_ids);
		}
		if (is_array($order_by) && count($order_by) > 0) {
			foreach ($order_by as $field => $dir) {
				if (in_array($field, $this->fields)) {
					$this->ci->db->order_by($field . " " . $dir);
				}
			}
		} else if ($order_by) {
			$this->ci->db->order_by($order_by);
		}
		if (!is_null($page)) {
			$page = intval($page) ? intval($page) : 1;
			$this->ci->db->limit($items_on_page, $items_on_page * ($page - 1));
		}
		$results = $this->ci->db->get()->result_array();

		if (!empty($results) && is_array($results)) {
			$data = $this->format_items($results);
			return $data;
		}
		return false;
	}

	public function format_items($data) 
	{
		
		/*$categories = ld('blog_categories', 'forum');
		$this->ci->load->model('Users_model');
        $users_ids = array();
		
		foreach ($data as $key=>$blog){
			$data[$key]['tags'] = $this->get_tags('blog', $blog['id']);
			$data[$key]['tags_str'] = implode(',', $data[$key]['tags']);
			$data[$key]['category_name'] = $categories['option'][$blog['category']];
			if ($blog['is_hidden']==1) $data[$key]['type'] = l('private', 'forum'); else $data[$key]['type'] = l('public', 'forum');
			$users_ids[$blog['user_id']] = $blog['user_id'];
		}
        if ($users_ids) {
			$users = $this->ci->Users_model->get_users_list_by_key(null, null, null, array(), $users_ids, false);
			$users = $this->ci->Users_model->format_users($users, false);
			$users[0] = $this->ci->Users_model->format_default_user();
            foreach ($data as $key => $blog) {
				$data[$key]['user'] = !empty($users[$blog['user_id']]) ? $users[$blog['user_id']] : $users[0];
            }
        }*/
		return $data;
	}
	
	public function validate($data = array()) 
	{
		$return = array('data' => array(), 'errors' => array());

        if (isset($data['category'])) {
            $return['data']['category'] = trim(strip_tags($data['category']));
            if (empty($return['data']['category'])) {
                $return['errors'][] = l('error_category_invalid', 'forum');
            }
		}
		
        if (isset($data['description'])) {
            $return['data']['description'] = trim(strip_tags($data['description']));
            if (empty($return['data']['description'])) {
                $return['errors'][] = l('error_subcategory_description_invalid', 'forum');
            }
        }

		return $return;
	}

	public function save($id = null, $data) 
	{
		if (!empty($id)) {
			$this->ci->db->where('id', $id);
			$this->ci->db->update(FORUM_CATEGORIES_TABLE, $data);
		} else {
			/*if (empty($data['date_created']))
				$data['date_created'] = date("Y-m-d H:i:s");*/
			$this->ci->db->insert(FORUM_CATEGORIES_TABLE, $data);
			$id = $this->ci->db->insert_id();
		}

		return $id;
	}
		
	
	public function delete($id, $user_id) 
	{
		$this->ci->db->where('id', $id);
		$this->ci->db->delete(FORUM_CATEGORIES_TABLE);
		return;
	}

	public function get_seo_settings($method = '', $lang_id = '') 
	{
		if (!empty($method)) {
			return $this->_get_seo_settings($method, $lang_id);
		} else {
			$actions = array('index', 'calendar', 'friends', 'categories');
			$return = array();
			foreach ($actions as $action) {
				$return[$action] = $this->_get_seo_settings($action, $lang_id);
			}
			return $return;
		}
	}

	private function _get_seo_settings($method, $lang_id = '') 
	{
		switch($method){
			case 'index':
				return array(
					'templates' => array(),
					'url_vars' => array(),
					'url_postfix' => array(
					),
					'optional' => array(),
				); break;
			case 'calendar':
				return array(
					'templates' => array(),
					'url_vars' => array(),
					'url_postfix' => array(
					),
					'optional' => array(),
				); break;
			case 'friends':
				return array(
					'templates' => array(),
					'url_vars' => array(),
					'url_postfix' => array(
					),
					'optional' => array(),
				); break;
			case 'categories':
				return array(
					'templates' => array(),
					'url_vars' => array(),
					'url_postfix' => array(
					),
					'optional' => array(),
				); break;
		}
	}

	public function request_seo_rewrite($var_name_from, $var_name_to, $value) 
	{
		if ($var_name_from == $var_name_to) {
			return $value;
		}
		
		show_404();
	}

	public function get_sitemap_xml_urls() 
	{
		$this->ci->load->helper('seo');
		$return = array();
		return $return;
	}

	public function get_sitemap_urls() 
	{
		$this->ci->load->helper('seo');
		$auth = $this->ci->session->userdata("auth_type");

		$block[] = array(
			"name" => l('header_my_blog', 'forum'),
			"link" => rewrite_link('forum', 'index'),
			"clickable" => ($auth=="user"),
			"items" => array(
				array(
					"name" => l('header_blog_calendar', 'forum'),
					"link" => rewrite_link('forum', 'calendar'),
					"clickable" => ($auth == "user"),
				),
				array(
					"name" => l('header_blog_friends', 'forum'),
					"link" => rewrite_link('forum', 'friends'),
					"clickable" => ($auth == "user"),
				),
				array(
					"name" => l('header_blog_categories', 'forum'),
					"link" => rewrite_link('forum', 'categories'),
					"clickable" => ($auth == "user"),
				),
			)
		);
		return $block;
	}
	
	public function _banner_available_pages() 
	{
		$return[] = array('link' => 'forum/index', 'name' => l('header_my_blog', 'forum'));
		$return[] = array('link' => 'forum/calendar', 'name' => l('header_blog_calendar', 'forum'));
		$return[] = array('link' => 'forum/friends', 'name' => l('header_blog_friends', 'forum'));
		$return[] = array('link' => 'forum/categories', 'name' => l('header_blog_categories', 'forum'));
		$return[] = array('link' => 'forum/view_category', 'name' => l('header_my_blog', 'forum'));
		return $return;
	}

}
