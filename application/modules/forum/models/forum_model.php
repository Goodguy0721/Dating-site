<?php
namespace Pg\Modules\Forum\Models;

/**
* Forum main model
*
* @package PG_Dating
* @subpackage application
* @category	modules
* @copyright Pilot Group <http://www.pilotgroup.net/>
* @author Renat Gabdrakhmanov <renatgab@pilotgroup.net>
**/

if (!defined('BLOGS_TABLE'))
	define('BLOGS_TABLE', DB_PREFIX . 'forum');

if (!defined('BLOG_POST_TABLE'))
	define('BLOG_POST_TABLE', DB_PREFIX . 'blog_post');

if (!defined('BLOG_COMMENTS_TABLE'))
	define('BLOG_COMMENTS_TABLE', DB_PREFIX . 'blog_comments');

if (!defined('BLOG_TAGS_TABLE'))
	define('BLOG_TAGS_TABLE', DB_PREFIX . 'blog_tags');

class Forum_model extends \Model 
{
	protected $ci;
	protected $moderation_type = 'forum';
	protected $blog_attrs = array(
		'id',
		'user_id',
		'title',
		'description',
		'date_created',
		'is_hidden',
		'active',
		'category',
		'posts_count',
		'comments_count',
	);
	protected $blog_post_attrs = array(
		'id',
		'blog_id',
		'title',
		'body',
		'date_created',
		'is_hidden',
		'can_comment',
	);
	protected $blog_comments_attrs = array(
		'id',
		'user_id',
		'post_id',
		'comment_id',
		'title',
		'body',
		'date_created',
		'deleted',
	);
	protected $blog_tags_attrs = array(
		'id',
		'tag',
		'tag_type',
		'date_created',
		'item_id',
	);
	protected $results_format_settings = array(
		'use_format' => true,
		'get_user' => true,
	);

	function __construct() 
	{
		parent::__construct();
		$this->ci = & get_instance();
	}
	

	public function get_blog_by_id($id = 0) 
	{
		$this->ci->db->from(BLOGS_TABLE)->where("id", $id);
		$results = $this->ci->db->get()->result_array();
		if (!empty($results) && is_array($results)) {
			$results = $this->format_blog($results);
			return $results[0];
		}
		return array();
	}

	public function get_post_by_id($id = 0) 
	{
		$this->ci->db->from(BLOG_POST_TABLE)->where("id", $id);
		$results = $this->ci->db->get()->result_array();
		if (!empty($results) && is_array($results)) {
			$results = $this->format_post($results);
			return $results[0];
		}
		return array();
	}

	public function get_comment_by_id($id = 0) 
	{
		$this->ci->db->from(BLOG_COMMENTS_TABLE)->where("id", $id);
		$results = $this->ci->db->get()->result_array();
		if (!empty($results) && is_array($results)) {
			$results = $this->format_comment($results);
			return $results[0];
		}
		return array();
	}

	function get_forum_count($params = array(), $filter_object_ids = null) 
	{
		$this->ci->db->select("COUNT(*) AS cnt");
		$this->ci->db->from(BLOGS_TABLE);
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

	function get_forum_list($page = null, $items_on_page = null, $order_by = null, $params = array(), $filter_object_ids = null) 
	{
		$this->ci->db->from(BLOGS_TABLE);
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
				if (in_array($field, $this->blog_attrs)) {
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
			$data = $this->format_blog($results);
			return $data;
		}
		return false;
	}

	function get_posts_count($params = array(), $filter_object_ids = null) 
	{
		$this->ci->db->select("COUNT(*) AS cnt");
		$this->ci->db->from(BLOG_POST_TABLE);
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

	function get_posts_list($page = null, $items_on_page = null, $order_by = null, $params = array(), $filter_object_ids = null, $user_format=false, $by_key=false) 
	{
		$this->ci->db->from(BLOG_POST_TABLE);
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
				if (in_array($field, $this->blog_post_attrs)) {
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
			$results = $this->format_post($results, $user_format, $by_key);
			return $results;
		}
		return false;
	}
	

	function get_comments_count($params = array(), $filter_object_ids = null) 
	{
		$this->ci->db->select("COUNT(*) AS cnt");
		$this->ci->db->from(BLOG_COMMENTS_TABLE);
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

	function get_comments_list($page = null, $items_on_page = null, $order_by = null, $params = array(), $filter_object_ids = null) 
	{
		$this->ci->db->from(BLOG_COMMENTS_TABLE);
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
				if (in_array($field, $this->blog_attrs)) {
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
			$data = $this->format_comment($results);
			return $data;
		}
		return false;
	}
	
	public function get_tags($tag_type = '', $item_id = '') 
	{
		if (!empty($tag_type) && !empty($item_id)){			
			$this->ci->db->from(BLOG_TAGS_TABLE);
			$this->ci->db->where('tag_type', $tag_type);
			$this->ci->db->where('item_id', $item_id);
			$results = $this->ci->db->get()->result_array();

			if (!empty($results) && is_array($results)) {
				$tags = array();
				foreach ($results as $tag){
					$tags[] = $tag['tag'];
				}
				return $tags;
			}
			return array();
		}
		return array();
	}
	
	public function get_all_tags() 
	{
		$this->ci->db->from(BLOG_TAGS_TABLE);
		$results = $this->ci->db->get()->result_array();

		if (!empty($results) && is_array($results)) {
			$tags = array();
			foreach ($results as $tag){
				if (!in_array($tag['tag'], $tags))
					$tags[] = $tag['tag'];
			}
			return $tags;
		}
		return array();
	}
	
	public function get_by_tag($tag) 
	{
		$this->ci->db->where('tag', $tag);
		$this->ci->db->from(BLOG_TAGS_TABLE);
		$results = $this->ci->db->get()->result_array();

		if (!empty($results) && is_array($results)) {
			return $results;
		}
		return array();
	}

	public function format_blog($data) 
	{
		
		$categories = ld('blog_categories', 'forum');
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
        }
		return $data;
	}

	public function format_post($data, $user_format = false, $by_key = false) 
	{
		if ($user_format) {
			$this->ci->load->model('Users_model');
			$users_ids = array();
		}
		if ($by_key){
			$formated_data = array();
		}
		foreach ($data as $key=>$post){
			$data[$key]['tags'] = $this->get_tags('blog_post', $post['id']);
			$data[$key]['tags_str'] = implode(',', $data[$key]['tags']);
			$users_ids[$post['user_id']] = $post['user_id'];
			if ($by_key) $formated_data[$post['id']] = $data[$key];
		}
		if ($by_key) $data = $formated_data;
		if ($user_format) {
			if ($users_ids) {
				$users = $this->ci->Users_model->get_users_list_by_key(null, null, null, array(), $users_ids, false);
				$users = $this->ci->Users_model->format_users($users, false);
				$users[0] = $this->ci->Users_model->format_default_user();
				foreach ($data as $key => $post) {
					$data[$key]['user'] = !empty($users[$post['user_id']]) ? $users[$post['user_id']] : $users[0];
				}
			}
		}
		return $data;
	}

	public function format_comment($data) 
	{
		
		$this->ci->load->model('Users_model');
        $users_ids = array();
		
		foreach ($data as $key=>$comment){
			$users_ids[$comment['user_id']] = $comment['user_id'];
		}
        if ($users_ids) {
			$users = $this->ci->Users_model->get_users_list_by_key(null, null, null, array(), $users_ids, false);
			$users = $this->ci->Users_model->format_users($users, false);
			$users[0] = $this->ci->Users_model->format_default_user();
            foreach ($data as $key => $comment) {
				$data[$key]['user'] = !empty($users[$comment['user_id']]) ? $users[$comment['user_id']] : $users[0];
            }
        }
		return $data;
	}
	
	public function validate_blog($data = array()) 
	{
		$return = array('data' => array(), 'errors' => array());

        if (isset($data['title'])) {
            $return['data']['title'] = trim(strip_tags($data['title']));
            if (empty($return['data']['title'])) {
                $return['errors'][] = l('error_blog_title_invalid', 'forum');
            }
		}
		
        if (isset($data['category'])) {
            $return['data']['category'] = trim(strip_tags($data['category']));
		}
		
		if (isset($data['is_hidden'])) {
			$return['data']['is_hidden'] = $data['is_hidden'] ? 1 : 0;
		}
		
        if (isset($data['description'])) {
            $return['data']['description'] = trim($data['description']);
            if (empty($return['data']['description'])) {
                $return['errors'][] = l('error_blog_short_description_invalid', 'forum');
            }
        }
		
        if (isset($data['tags'])) {
			$return['data']['tags'] = array();
			$tags = trim(strip_tags($data['tags']));
			$tags = explode(',', $tags);
			if (!empty($tags)){
				foreach ($tags as $tag) {
					$tag = trim(strip_tags($tag));
					if (!empty($tag)) {
						$return['data']['tags'][] = $tag;
					}
				}
			}
		}

		return $return;
	}
	
	public function validate_post($data = array()) 
	{
		$return = array('data' => array(), 'errors' => array());

        if (isset($data['title'])) {
            $return['data']['title'] = trim(strip_tags($data['title']));
            if (empty($return['data']['title'])) {
                $return['errors'][] = l('error_post_title_invalid', 'forum');
            }
		}
		
		if (isset($data['is_hidden'])) {
			$return['data']['is_hidden'] = $data['is_hidden'] ? 1 : 0;
		}
		
		if (isset($data['can_comment'])) {
			$return['data']['can_comment'] = $data['can_comment'] ? 1 : 0;
		}
		
        if (isset($data['body'])) {
            $return['data']['body'] = trim($data['body']);
            if (empty($return['data']['body'])) {
                $return['errors'][] = l('error_post_body_invalid', 'forum');
            }
        }
		
        if (isset($data['tags'])) {
			$return['data']['tags'] = array();
			$tags = trim(strip_tags($data['tags']));
			$tags = explode(',', $tags);
			if (!empty($tags)){
				foreach ($tags as $tag) {
					$tag = trim(strip_tags($tag));
					if (!empty($tag)) {
						$return['data']['tags'][] = $tag;
					}
				}
			}
		}

		return $return;
	}
	
	public function validate_comment($data = array()) 
	{
		$return = array('data' => array(), 'errors' => array());

        if (isset($data['title'])) {
            $return['data']['title'] = trim(strip_tags($data['title']));
            if (empty($return['data']['title'])) {
                $return['errors'][] = l('error_post_title_invalid', 'forum');
            }
		}
		
        if (isset($data['body'])) {
            $return['data']['body'] = trim($data['body']);
            if (empty($return['data']['body'])) {
                $return['errors'][] = l('error_post_body_invalid', 'forum');
            }
        }
		if (isset($data['comment_id'])) {
			$return['data']['comment_id'] = intval($data['comment_id']);
		}

		return $return;
	}

	public function save_blog($id = null, $data) 
	{
		if (!empty($id)) {
			$this->ci->db->where('id', $id);
			$this->ci->db->update(BLOGS_TABLE, $data);
		} else {
			if (empty($data['date_created']))
				$data['date_created'] = date("Y-m-d H:i:s");
			$this->ci->db->insert(BLOGS_TABLE, $data);
			$id = $this->ci->db->insert_id();
		}

		return $id;
	}
	
	public function save_post($id = null, $data) 
	{
		if (!empty($id)) {
			$this->ci->db->where('id', $id);
			$this->ci->db->update(BLOG_POST_TABLE, $data);
		} else {
			if (empty($data['date_created']))
				$data['date_created'] = date("Y-m-d H:i:s");
			$this->ci->db->insert(BLOG_POST_TABLE, $data);
			$id = $this->ci->db->insert_id();
			$id_user = $data['user_id'];
			$this->_create_wall_event('blog_post_created', $id_user, $id_user, $id);
		}

		return $id;
	}
	
	public function save_comment($id = null, $data) 
	{
		if (!empty($id)) {
			$this->ci->db->where('id', $id);
			$this->ci->db->update(BLOG_COMMENTS_TABLE, $data);
		} else {
			if (empty($data['date_created']))
				$data['date_created'] = date("Y-m-d H:i:s");
			$this->ci->db->insert(BLOG_COMMENTS_TABLE, $data);
			$id = $this->ci->db->insert_id();
		}

		return $id;
	}
	
	public function save_tags($tag_type = '', $item_id = '', $tags = array()) 
	{
		if (!empty($tag_type) && !empty($item_id)){
			$this->ci->db->where('tag_type', $tag_type);
			$this->ci->db->where('item_id', $item_id);
			$this->ci->db->delete(BLOG_TAGS_TABLE);
			$data['date_created'] = date("Y-m-d H:i:s");
			$data['tag_type'] = $tag_type;
			$data['item_id'] = $item_id;
			foreach ($tags as $tag){
				$data['tag'] = $tag;
				$this->ci->db->insert(BLOG_TAGS_TABLE, $data);
			}
		}
		return;
	}
	
	
	
	public function delete_blog($id, $user_id) 
	{
		$this->ci->db->where('id', $id);
		$this->ci->db->delete(BLOGS_TABLE);
		$this->ci->db->where('blog_id', $id);
		$this->ci->db->delete(BLOG_POST_TABLE);
		$this->ci->db->where('blog_id', $id);
		$this->ci->db->delete(BLOG_COMMENTS_TABLE);
		$this->ci->db->where('tag_type', 'blog');
		$this->ci->db->where('item_id', $id);
		$this->ci->db->delete(BLOG_TAGS_TABLE);
		$this->_delete_wall_event(array('event_type_gid'=>'blog_post_created', 'id_poster'=>$user_id));
		return;
	}
	
	public function delete_post($id) 
	{
		$this->ci->db->where('id', $id);
		$this->ci->db->delete(BLOG_POST_TABLE);
		$this->ci->db->where('post_id', $id);
		$this->ci->db->delete(BLOG_COMMENTS_TABLE);
		$this->ci->db->where('tag_type', 'blog_post');
		$this->ci->db->where('item_id', $id);
		$this->ci->db->delete(BLOG_TAGS_TABLE);
		$this->_delete_wall_event(array('event_type_gid'=>'blog_post_created', 'id_object'=>$id));
		return;
	}
	
	public function delete_comment($id) 
	{
		$this->ci->db->where('id', $id);
		$this->ci->db->delete(BLOG_COMMENTS_TABLE);
		$this->ci->db->where('comment_id', $id);
		$this->ci->db->delete(BLOG_COMMENTS_TABLE);
		return;
	}

	protected function _create_wall_event($gid, $id_wall, $id_poster, $id_object) 
	{
		$this->ci->load->helper('wall_events_default');
		$data['id_post'] = $id_object;
		$result = add_wall_event($gid, $id_wall, $id_poster, $data, $id_object);
	}

	protected function _delete_wall_event($params) 
	{
		$this->ci->load->helper('wall_events_default');
		$result = delete_wall_events($params);
	}

	public function _format_wall_events($events) 
	{
		$formatted_events = array();
		$posts_ids = array();
		foreach ($events as $key => $e) {
			foreach ($e['data'] as $e_data) {
				$posts_ids[$e_data['id_post']] = $e_data['id_post'];
			}
		}
		if ($posts_ids) {
			$posts = $this->get_posts_list(null, null, null, array(), $posts_ids, true, true);
		}
		foreach ($events as $key => $e) {
			$this->ci->view->assign('posts', $posts);
			$this->ci->view->assign('event', $e);
			$e['html'] = $this->ci->view->fetch('wall_events_posts', 'user', 'forum');
			$formatted_events[$key] = $e;
		}
		return $formatted_events;
	}
	
	public function get_blog_post_by_day($date, $user_id = 0) 
	{
		$this->ci->db->from(BLOG_POST_TABLE);
		$this->ci->db->where('user_id', $user_id);
		$this->ci->db->where("DATE_FORMAT(date_created, '%Y-%m-%d')=DATE_FORMAT('".$date."','%Y-%m-%d')", null, false);
		$this->ci->db->limit(3);
		$results = $this->ci->db->get()->result_array();
		
		$i = 0;
		$blog_post = array();
		foreach ($results as $item){
			$blog_post[$i]["id_post"] = $item["id"];
			$blog_post[$i]["post_link"] = site_url()."forum/view_post/".$item['id'];
			$blog_post[$i]["title"] = stripslashes($item["title"]);
			$i++;
		}
		return $blog_post;
		
	}

}
