<?php

namespace Pg\Modules\Users\Models;

if (!defined('VERIFICATIONS_TABLE')) {
    define('VERIFICATIONS_TABLE', DB_PREFIX . 'verifications');
}

class Verifications_model extends \Model
{
    public $ci;
    
    public $fields = [
        'id',
        'user_id',
        'filename',
        'status',
        'date_created',
    ];
    
    public $user_id = 0;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->ci = &get_instance();
        
        $this->user_id = $this->ci->session->userdata('user_id');
    }
    
    public function getById($verification_id)
    {
        $result = $this->ci->db
            ->select(implode(", ", $this->fields))
            ->from(VERIFICATIONS_TABLE)
            ->where("id", $verification_id)
            ->get()->result_array();
        return !empty($result) ? $result[0] : false;
    }
    
    public function get($page = null, $items_on_page = null, $order_by = null, $params = [], $filter_object_ids = [])
    {
        $this->ci->db
            ->select(implode(", ", $this->fields))
            ->from(VERIFICATIONS_TABLE);

        if (isset($params["where"]) && is_array($params["where"]) && count($params["where"])) {
            foreach ($params["where"] as $field => $value) {
                $this->ci->db->where($field, $value);
            }
        }

        if (!empty($params["where_in"])) {
            foreach ($params["where_in"] as $field => $value) {
                $this->ci->db->where_in($field, $value);
            }
        }

        if (!empty($params["where_sql"])) {
            foreach ($params["where_sql"] as $value) {
                $this->ci->db->where($value, null, false);
            }
        }

        if (!empty($filter_object_ids)) {
            $this->ci->db->where_in("id", $filter_object_ids);
        }

        if (!empty($order_by)) {
            foreach ($order_by as $field => $dir) {
                if (in_array($field, $this->fields)) {
                    $this->ci->db->order_by($field . " " . $dir);
                }
            }
        }

        if (!is_null($page)) {
            $page = intval($page) ? intval($page) : 1;
            $this->ci->db->limit($items_on_page, $items_on_page * ($page - 1));
        }
        $results = $this->ci->db->get()->result_array();
        if (!empty($results)) {
            return $results;
        }

        return [];
    }
    
    public function count($params = [], $filter_object_ids = null)
    {
        if (!empty($params["where"])) {
            foreach ($params["where"] as $field => $value) {
                $this->ci->db->where($field, $value);
            }
        }

        if (!empty($params["where_in"])) {
            foreach ($params["where_in"] as $field => $value) {
                $this->ci->db->where_in($field, $value);
            }
        }

        if (!empty($params["where_sql"])) {
            foreach ($params["where_sql"] as $value) {
                $this->ci->db->where($value, null, false);
            }
        }

        if (!empty($filter_object_ids)) {
            $this->ci->db->where_in("id", $filter_object_ids);
        }
        $result = $this->ci->db->count_all_results(VERIFICATIONS_TABLE);

        return $result;
    }
    
    public function format($verifications)
    {
        $this->ci->load->model('File_uploads_model');
        
        $user_ids = [];
        
        foreach ($verifications as $index => $verification) {
            $verification["prefix"] = $verification["user_id"];
            
            $user_ids[] = $verification['user_id'];
            
            if (!empty($verification["filename"])) {
                $verification["media"]["source"] = $this->ci->File_uploads_model->format_upload('verifications', $verification["prefix"], $verification["filename"]);
            } else {
                $data["media"]["source"] = false;
            }
            
            $verifications[$index] = $verification;
        }
        
        if (!empty($user_ids)) {
            $this->ci->load->model('Users_model');
            $default_user = $this->ci->Users_model->format_default_user();
            $users = $this->ci->Users_model->get_users_list_by_key(null, null, null, [], $user_ids);
            foreach ($verifications as $index => $verification) {
                if (array_key_exists($verification['user_id'], $users)) {
                    $verification['user'] = $users[$verification['user_id']];
                } else {
                    $verification['user'] = $default_user;
                }
                $verifications[$index] = $verification;
            }
        }

        return $verifications;
    }
    
    public function save($verification_id, $file_name)
    {
        $return = array('errors' => array(), 'data' => array());

        $this->ci->load->model('File_uploads_model');

        $validate_upload = $this->ci->File_uploads_model->validate_upload('verifications', $file_name);

        if (!isset($validate_upload['errors']))
            $validate_upload['errors'] = array();

        if (isset($validate_upload['error']) && $validate_upload['error'] != '') {
            if (is_array($validate_upload['error'])) {
                $validate_upload['errors'] = $validate_upload['error'];
            } else {
                $validate_upload['errors'][] = $validate_upload['error'];
            }
        }

        if (!empty($validate_data['errors'])) {
            $return['errors'] = $validate_data['errors'];
        } else {
            $file_return = $this->ci->File_uploads_model->upload('verifications', $this->user_id, $file_name);
            
            $save_data = [
               'user_id' => $this->user_id,
               'filename' => $file_return['file'],
               'status' => 'pending',
               'date_created' => date('Y-m-d H:i:s'),
            ];
            
            $this->ci->db->insert(VERIFICATIONS_TABLE, $save_data);
            $verification_id = $this->ci->db->insert_id();
        }
        return $return;
    }
    
    public function approve($user_id)
    {
        $this->ci->db
            ->set('status', 'approved')
            ->where(['user_id'=>$user_id])
            ->update(VERIFICATIONS_TABLE);
    }
    
    public function decline($verification_id)
    {
        $this->ci->db
            ->set('status', 'declined')
            ->where(['id'=>$verification_id])
            ->update(VERIFICATIONS_TABLE);
    }
    
    public function delete($verification_id)
    {
        $verification = $this->getById($verification_id);
        
        $this->ci->load->model('File_uploads_model');
        $this->ci->File_uploads_model->delete_upload('verifications', $verification['user_id'], $verification['filename']);
        
        $this->ci->db
            ->where(['id'=>$verification_id])
            ->delete(VERIFICATIONS_TABLE);
    }
}
