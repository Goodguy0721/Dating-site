<?php
namespace Pg\Modules\Forum\Models;

/**
* Forum subcategories model
*
* @package PG_Dating
* @subpackage application
* @category	modules
* @copyright Pilot Group <http://www.pilotgroup.net/>
* @author Renat Gabdrakhmanov <renatgab@pilotgroup.net>
**/

if (!defined('FORUM_SUBCATEGORIES_TABLE'))
	define('FORUM_SUBCATEGORIES_TABLE', DB_PREFIX . 'forum_subcategories');


class Forum_subcategories_model extends \Model 
{

	protected $ci;
	protected $fields = array(
		'id',
		'user_id',
		'is_admin',
		'category_id',
		'subcategory',
		'date_created',
		'messages_count',
	);

	function __construct() 
	{
		parent::__construct();
		$this->ci = & get_instance();
	}
	

	public function get_by_id($id = 0) 
	{
		$this->ci->db->from(FORUM_SUBCATEGORIES_TABLE)->where("id", $id);
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
		$this->ci->db->from(FORUM_SUBCATEGORIES_TABLE);
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
		$this->ci->db->from(FORUM_SUBCATEGORIES_TABLE);
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
		
		$this->ci->load->model('forum/models/Forum_messages_model');
		foreach ($data as $key=>$item){
			$where['where']['subcategory_id'] = $item['id'];
			$latest = $this->ci->Forum_messages_model->get_list(1, 1, array('id'=>'DESC'), $where);
			$data[$key]['latest'] = $latest[0];
		}
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

        if (isset($data['subcategory'])) {
            $return['data']['subcategory'] = trim(strip_tags($data['subcategory']));
            if (empty($return['data']['subcategory'])) {
                $return['errors'][] = l('error_subcategory_invalid', 'forum');
            }
		}
        if (isset($data['subject'])) {
            $return['data']['subject'] = trim(strip_tags($data['subject']));
            if (empty($return['data']['subject'])) {
                $return['errors'][] = l('error_subject_invalid', 'forum');
            }
		}
		
        if (isset($data['message'])) {
            $return['data']['message'] = trim($data['message']);
            if (empty($return['data']['message'])) {
                $return['errors'][] = l('error_message_invalid', 'forum');
            }
        }

		return $return;
	}

	public function save($id = null, $data) 
	{
		if (!empty($id)) {
			$this->ci->db->where('id', $id);
			$this->ci->db->update(FORUM_SUBCATEGORIES_TABLE, $data);
		} else {
			if (empty($data['date_created']))
				$data['date_created'] = date("Y-m-d H:i:s");
			$this->ci->db->insert(FORUM_SUBCATEGORIES_TABLE, $data);
			$id = $this->ci->db->insert_id();
		}

		return $id;
	}
		
	
	public function delete($where=array()) 
	{
		foreach ($where as $field=>$value)
			$this->ci->db->where($field, $value);
		$this->ci->db->delete(FORUM_SUBCATEGORIES_TABLE);
		return;
	}

}
