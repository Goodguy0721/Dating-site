<?php

/**
 * Clubs module
 *
 * @package     PG_Dating
 * @copyright   Copyright (c) 2000-2014 PG Dating Pro - php dating software
 * @author      Renat Gabdrakhmanov <renatgab@pilotgroup.net>
 */
 
namespace Pg\Modules\Clubs\Models;

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

define('CLUBS_MEDIA_TABLE', DB_PREFIX . 'clubs_media');

/**
 * Clubs media model
 * 
 * @package     PG_Dating
 * @subpackage  Clubs
 * @category    models
 * @copyright   Copyright (c) 2000-2014 PG Dating Pro - php dating software
 * @author      Renat Gabdrakhmanov <renatgab@pilotgroup.net>
 */
class Clubs_media_model extends \Model
{
    const DB_DATE_FORMAT = 'Y-m-d H:i:s';
    const DB_DATE_FORMAT_SEARCH = 'Y-m-d H:i';
    const DB_DEFAULT_DATE = '0000-00-00 00:00:00';
    /**
     * Module GUID
     * 
     * @var string
     */
    const MODULE_GID = 'clubs';
    /**
     * Link to CodeIgniter object
     * 
     * @var object
     */
    protected $ci;

    protected $fields = [
        CLUBS_MEDIA_TABLE => [
            'id',
            'club_id',
            'filename',
            'upload_gid',
            'mime',
            'date_add',
            'media_video',
            'media_video_image',
            'media_video_data',
            'description',
            'is_active',
            'settings',
        ],
    ];

    /**
     * Settings for formatting clubs object
     * 
     * @var array
     */
    protected $format_settings = [];
    public $image_config_gid = 'club-image';
    public $video_config_gid = 'club-video';

    /**
     * Class constructor
     * 
     * @return Clubs_media_model
     */
    public function __construct()
    {
        parent::__construct();
        $this->ci = & get_instance();
    }

    public function getObject($data = [])
    {
        $fields = $this->fields[CLUBS_MEDIA_TABLE];
        $fields_str = implode(', ', $fields);

        $this->ci->db->select($fields_str)
            ->from(CLUBS_MEDIA_TABLE);

        foreach ($data as $field => $value) {
            $this->ci->db->where($field, $value);
        }
        
        $results = $this->ci->db->get()->result_array();

        if (!empty($results) && is_array($results)) {
            return $results[0];
        }

        return false;
    }

    public function getMediaByClubId($club_id)
    {
        $result = $this->ci->db->select(implode(', ', $this->fields[CLUBS_MEDIA_TABLE]))->from(CLUBS_MEDIA_TABLE)->where('club_id', $club_id)->get()->result_array();
        if (empty($result)) {
            return array();
        } else {
            return $result;
        }
    }

    public function setFormatSettings($name, $value = false)
    {
        if (!is_array($name)) {
            $name = [$name => $value];
        }
        foreach ($name as $key => $item) {
            $this->format_settings[$key] = $item;
        }
    }

    public function format($data)
    {
        return current($this->formatArray([$data]));
    }

    public function formatArray($data)
    { 
        $return = [];

        if (empty($data) || !is_array($data)) {
            return [];
        }

        $this->ci->load->model(['Uploads_model', 'Video_uploads_model']);
        foreach ($data as $key => $item) {
            if ($item['upload_gid'] == $this->image_config_gid) {
                if (!empty($item['filename'])) {
                    $item['media'] = $this->ci->Uploads_model->format_upload($item['upload_gid'], $item['club_id'], $item['filename']);
                } else {
                    $item['media'] = $this->ci->Uploads_model->format_default_upload($item['upload_gid']);
                }
                $item['media']['photo_title'] = str_replace('[id]', $item['id'], l('text_media_photo', self::MODULE_GID));
                $item['media']['photo_alt'] = str_replace('[id]', $item['id'], l('text_media_photo', self::MODULE_GID));
            } elseif ($item['upload_gid'] == $this->video_config_gid) {
                if (!empty($item['media_video_data'])) {
                    $item['media_video_data'] = unserialize($item['media_video_data']);
                }

                if (!empty($item['media_video'])) {
                    if (!isset($item['media_video_data']['data']['isHTML5'])) {
                        $item['media_video_data']['data']['isHTML5'] = 0;
                    }
                    $media_video = ($item['media_video'] == 'embed') ? $item['media_video_data']['data'] : $item['media_video'];
                    $item['video_content'] = $this->ci->Video_uploads_model->format_upload($this->video_config_gid, $item['club_id'], $media_video, $item['media_video_image'], $item['media_video_data']['data']['upload_type'], $item['media_video_data']['data']['isHTML5']);
                }
            }

            $item['settings'] = $item['settings'] ? (array) unserialize($item['settings']) : array();
            if (!empty($item['date_add'])) {
                $item['date_add_ts'] = strtotime($item['date_add']);
            }

            $return[$key] = $item;
        }

        return $return;
    }

    public function validateImage($data, $file_name = '')
    {
        $return = array('errors' => array(), 'data' => array(), 'form_error' => 0);

        if (!empty($data['filename'])) {
            $return['data']['filename'] = trim(strip_tags($data['filename']));
        }

        if (isset($data['description'])) {
            $return['data']['description'] = trim(strip_tags($data['description']));
        }

        if (!empty($file_name)) {
            if (isset($_FILES[$file_name]) && is_array($_FILES[$file_name]) && !($_FILES[$file_name]['error'])) {
                $this->ci->load->model('Uploads_model');
                $file_return = $this->ci->Uploads_model->validate_upload($this->image_config_gid, $file_name);
                if (!empty($file_return['error'])) {
                    $return['errors'][] = (is_array($file_return['error'])) ? implode('<br>', $file_return['error']) : $file_return['error'];
                }
                $return['data']['mime'] = $_FILES[$file_name]['type'];
            } elseif ($_FILES[$file_name]['error']) {
                $return['errors'][] = $_FILES[$file_name]['error'];
            } else {
                $return['errors'][] = 'empty file';
            }
        }

        return $return;
    }

    public function validateVideo($data, $video_name = '')
    {
        $return = array('errors' => array(), 'data' => array(), 'form_error' => 0);

        if (isset($data['filename'])) {
            $return['data']['filename'] = strip_tags($data['filename']);

            if (empty($return['data']['filename'])) {
                $return['errors'][] = l('error_fname_empty', self::MODULE_GID);
            }
        }

        if (isset($data['description'])) {
            $return['data']['description'] = strip_tags($data['description']);
        }

        $embed_data = array();
        if (!empty($data['embed_code'])) {
            $this->load->library('VideoEmbed');
            $embed_data = $this->videoembed->get_video_data($data['embed_code']);
            if ($embed_data !== false) {
                $embed_data['string_to_save'] = $this->videoembed->get_string_from_video_data($embed_data);
                $embed_data['upload_type'] = 'embed';
                $this->ci->load->model('uploads/models/Uploads_model');
                $return['data']['media_video_image'] = $this->ci->Uploads_model->generate_filename('.jpg');
                $return['data']['media_video_data'] = serialize(array('data' => $embed_data));
                $return['data']['media_video'] = 'embed';
                $return['data']['filename'] = $embed_data['service'];
            } else {
                $return['errors'][] = l('error_embed_wrong', self::MODULE_GID);
            }
        }

        if (!empty($video_name) && !$embed_data) {
            if (isset($_FILES[$video_name]) && is_array($_FILES[$video_name]) && !($_FILES[$video_name]['error'])) {
                $this->ci->load->model('Video_uploads_model');
                $video_return = $this->ci->Video_uploads_model->validate_upload($this->video_config_gid, $video_name);
                if (!empty($video_return['error'])) {
                    $return['errors'][] = (is_array($video_return['error'])) ? implode('<br>', $video_return['error']) : $video_return['error'];
                }
                $return['data']['mime'] = $_FILES[$video_name]['type'];
            } elseif (!empty($_FILES[$video_name]['error'])) {
                $return['errors'][] = $_FILES[$video_name]['error'];
            } else {
                $return['errors'][] = l('error_file_empty', self::MODULE_GID);
            }
        }

        return $return;
    }

    public function saveImage($id, $data, $file_name = '')
    {
        $return = array('errors' => '');

        if (!empty($id)) {
            $this->ci->db->where('id', $id);
            $this->ci->db->update(CLUBS_MEDIA_TABLE, $data);
        } else {
            $data['date_add'] = date('Y-m-d H:i:s');
            $this->ci->db->insert(CLUBS_MEDIA_TABLE, $data);
            $id = $this->ci->db->insert_id();
        }
        $return['id'] = $id;

        if (!empty($file_name) && !empty($id)) {
            $this->ci->load->model('Uploads_model');
            if (isset($_FILES[$file_name]) && is_array($_FILES[$file_name]) && is_uploaded_file($_FILES[$file_name]['tmp_name'])) {
                $upload_method = 'upload';
            } elseif (file_exists($file_name)) {
                $upload_method = 'upload_exist';
            }
            $img_return = $this->ci->Uploads_model->{$upload_method}($this->image_config_gid, $data['club_id'], $file_name);
            if (empty($img_return['errors'])) {

                $img_data['filename'] = (string) $img_return['file'];
                $upload = $this->ci->Uploads_model->format_upload($this->image_config_gid, $data['club_id'], $img_return['file']);
                $image_size = getimagesize($upload['file_path']);
                $img_data['settings']['width'] = $image_size[0];
                $img_data['settings']['height'] = $image_size[1];
                $img_data['settings'] = serialize($img_data['settings']);
                $this->saveImage($id, $img_data);
                $return['file'] = $img_return['file'];

            } else {
                $return['errors'] = $img_return['errors'];
            }
        }

        return $return;
    }

    public function saveVideo($id, $data, $video_name = '')
    {
        $return = ['errors' => ''];

        if (!empty($data['media_video']) && $data['media_video'] == 'embed') {
            $this->ci->load->model('Video_uploads_model');
            $data = $this->ci->Video_uploads_model->upload_embed_video_image($this->video_config_gid, $data, $data['club_id']);
        }

        if (!empty($id)) {
            $this->ci->db->where('id', $id);
            $this->ci->db->update(CLUBS_MEDIA_TABLE, $data);
        } else {
            $data['date_add'] = date('Y-m-d H:i:s');
            $this->ci->db->insert(CLUBS_MEDIA_TABLE, $data);
            $id = $this->ci->db->insert_id();
        }
        
        $return['id'] = $id;
        if (!empty($video_name) && !empty($id) && isset($_FILES[$video_name]) && is_array($_FILES[$video_name]) && is_uploaded_file($_FILES[$video_name]['tmp_name'])) {
            $this->ci->load->model('Video_uploads_model');
            $video_return = $this->ci->Video_uploads_model->upload($this->video_config_gid, $data['club_id'], $video_name, $id, $data, 'generate');

            if (empty($video_return['errors'])) {
                $video_data['filename'] = $video_return['file'];
                $this->saveVideo($id, $video_data);
                $return['file'] = $video_return['file'];
            } else {
                $return['errors'] = $video_return['errors'];
            }
          
        }

        return $return;
    }

    public function getList($filters = [], $page = null, $items_on_page = null, $order_by = null)
    {
        $params = $this->_getCriteria($filters);
        return $this->_getList($page, $items_on_page, $order_by, $params);
    }

    public function getListByKey($filters = [], $page = null, $items_on_page = null, $order_by = null)
    {
        $return = [];

        $params = $this->_getCriteria($filters);
        $list   = $this->_getList($page, $items_on_page, $order_by, $params);
        foreach ($list as $key => $item) {
            $return[$item['id']] = $item;
        }
        return $return;
    }

    public function getCount($filters = [])
    {
        $params = $this->_getCriteria($filters);
        return $this->_getCount($params);
    }

    public function _getCriteria($filters)
    {
        $filters = ['data' => $filters, 'table' => CLUBS_MEDIA_TABLE, 'type' => ''];

        $params = [];

        $params['table'] = !empty($filters['table']) ? $filters['table'] : CLUBS_MEDIA_TABLE;

        $fields = array_flip($this->fields[CLUBS_MEDIA_TABLE]);
        foreach ($filters['data'] as $filter_name => $filter_data) {
            if (!is_array($filter_data)) {
                $filter_data = trim($filter_data);
            }
            switch ($filter_name) {
                default: {
                    if (isset($fields[$filter_name])) {
                        if (is_array($filter_data)) {
                            $params = array_merge_recursive($params, ['where_in' => [$filter_name => $filter_data]]);
                        } else {
                            $params = array_merge_recursive($params, ['where' => [$filter_name => $filter_data]]);
                        }
                    }
                    break;
                }
            }
        }

        return $params;
    }

    protected function _getList($page = null, $limits = null, $order_by = null, $params = [])
    {   
        $table = CLUBS_MEDIA_TABLE;
        $fields = $this->fields[$table];
        
        $fields_str = implode(', ', $fields);

        if (isset($params['table']) && $params['table'] != $table) {
            $table = $params['table'];
            $fields_str = $table . '.' . implode(', ' . $table . '.', $fields);
        }

        $this->ci->db->select($fields_str);
        $this->ci->db->from($table);

        if (isset($params['where']) && is_array($params['where']) && count($params['where'])) {
            foreach ($params['where'] as $field => $value) {
                $this->ci->db->where($field, $value);
            }
        }

        if (isset($params['where_in']) && is_array($params['where_in']) && count($params['where_in'])) {
            foreach ($params['where_in'] as $field => $value) {
                $this->ci->db->where_in($field, $value);
            }
        }

        if (isset($params['where_not_in']) && is_array($params['where_not_in']) && count($params['where_not_in'])) {
            foreach ($params['where_not_in'] as $field => $value) {
                $this->ci->db->where_not_in($field, $value);
            }
        }

        if (isset($params['where_sql'])) {
            if (!is_array($params['where_sql'])) {
                $params['where_sql'] = array($params['where_sql']);
            }
            foreach ($params['where_sql'] as $value) {
                $this->ci->db->where($value, null, false);
            }
        }

        if (is_array($order_by) && count($order_by) > 0) {
            foreach ($order_by as $field => $dir) {
                if (in_array($field, $this->fields[$table])) {
                    $this->ci->db->order_by($field . ' ' . $dir);
                }
            }
        } elseif ($order_by) {
            $this->ci->db->order_by($order_by);
        }

        if (!is_null($page)) {
            $page = intval($page) ? intval($page) : 1;
            $this->ci->db->limit($limits, $limits * ($page - 1));
        }

        $results = $this->ci->db->get()->result_array();
        if (!empty($results) && is_array($results)) {
            return $results;
        }
        return [];
    }

    protected function _getCount($params = null)
    {
        $table = isset($params['table']) ? $params['table'] : CLUBS_MEDIA_TABLE;

        $this->ci->db->select('COUNT(*) AS cnt');
        $this->ci->db->from($table);

        if (isset($params['where']) && is_array($params['where']) && count($params['where'])) {
            foreach ($params['where'] as $field => $value) {
                $this->ci->db->where($field, $value);
            }
        }

        if (isset($params['where_in']) && is_array($params['where_in']) && count($params['where_in'])) {
            foreach ($params['where_in'] as $field => $value) {
                $this->ci->db->where_in($field, $value);
            }
        }

        if (isset($params['where_not_in']) && is_array($params['where_not_in']) && count($params['where_not_in'])) {
            foreach ($params['where_not_in'] as $field => $value) {
                $this->ci->db->where_not_in($field, $value);
            }
        }

        if (isset($params['where_sql']) && is_array($params['where_sql']) && count($params['where_sql'])) {
            foreach ($params['where_sql'] as $value) {
                $this->ci->db->where($value, null, false);
            }
        }

        $results = $this->ci->db->get()->result_array();
        if (!empty($results) && is_array($results)) {
            return intval($results[0]['cnt']);
        }
        return 0;
    }

    public function delete($id) 
    {
        if (is_array($id)) {
            foreach ($id as $i) {
                $this->delete($i);
            }
        } else {
            $media = $this->getObject(['id' => $id]);
            if (!$media) {
                return;
            }

            $this->ci->db->where('id', $media['id'])->delete(CLUBS_MEDIA_TABLE);

            if (!empty($media['filename'])) {
                $this->ci->load->model('Uploads_model');
                $this->ci->Uploads_model->delete_upload($this->image_config_gid, $media['club_id'], $media['filename']);
            }

            if (!empty($media['media_video'])) {
                if ($media['media_video'] !== 'embed') {
                    $this->ci->load->model('Video_uploads_model');
                    $this->ci->Video_uploads_model->delete_upload($this->video_config_gid, $media['club_id'], $media['media_video'], $media['media_video_image']);
                } elseif ($media['media_video'] === 'embed') {
                    $this->ci->load->model('Video_uploads_model');
                    $this->ci->Video_uploads_model->delete_embed_video_image($this->video_config_gid, $media['club_id'], $media['media_video_image']);
                }
            }
        }

        return true;
    }

    public function callbackClubDelete($club_id)
    {
        $this->deleteMediaByClubId($club_id);
        return true;
    }

    private function deleteMediaByClubId($club_id)
    {
        $media_data = $this->getMediaByClubId($club_id);
        foreach ($media_data as $media) {
            $this->delete($media['id']);
        }
        return true;
    }

    // video callback
    public function videoCallback($id, $status, $data, $errors)
    {
        $media_data = $this->getObject(['id' => $id]);
        $media_data = $this->format($media_data);

        if (isset($data['video'])) {
            $update['media_video'] = $data['video'];
        }
        if (isset($data['image'])) {
            $update['media_video_image'] = $data['image'];
        }

        $update['media_video_data'] = $media_data['media_video_data'];

        if ($status == 'start') {
            $update['media_video_data'] = array();
        }

        if (!isset($update['media_video_data']['data'])) {
            $update['media_video_data']['data'] = array();
        }

        if (!empty($data)) {
            $update['media_video_data']['data'] = array_merge($update['media_video_data']['data'], $data);
        }

        $update['media_video_data']['status'] = $status;
        $update['media_video_data']['errors'] = $errors;
        $update['media_video_data'] = serialize($update['media_video_data']);
        $this->saveVideo($id, $update);

        return;
    }

    public function getMediaIds($filters = [], $page = null, $items_on_page = null, $order_by = null)
    {
        $objects = array();
        $this->ci->db->select('id');
        $this->ci->db->from(CLUBS_MEDIA_TABLE);

        $params = $this->_getCriteria($filters);

        if (isset($params['where']) && is_array($params['where']) && count($params['where'])) {
            foreach ($params['where'] as $field => $value) {
                $this->ci->db->where($field, $value);
            }
        }

        if (isset($params['where_in']) && is_array($params['where_in']) && count($params['where_in'])) {
            foreach ($params['where_in'] as $field => $value) {
                $this->ci->db->where_in($field, $value);
            }
        }

        if (isset($params['where_sql']) && is_array($params['where_sql']) && count($params['where_sql'])) {
            foreach ($params['where_sql'] as $value) {
                $this->ci->db->where($value);
            }
        }

        if (is_array($order_by) && count($order_by) > 0) {
            foreach ($order_by as $field => $dir) {
                if (in_array($field, $this->fields[CLUBS_MEDIA_TABLE])) {
                    $this->ci->db->order_by($field . ' ' . $dir);
                }
            }
        }

        if (!is_null($page)) {
            $page = intval($page) ? intval($page) : 1;
            $this->ci->db->limit($items_on_page, $items_on_page * ($page - 1));
        }
        $results = $this->ci->db->get()->result_array();
        if (!empty($results) && is_array($results)) {
            foreach ($results as $result) {
                $objects[] = $result['id'];
            }
        }

        return $objects;
    }

    public function getMediaPositionInfo($media_id, $param = 'all', $club_id = 0, $order_by = ['date_add' => 'DESC'])
    {
        $return = ['position' => 0, 'count' => 0];
        $filters = ['is_active' => 1];

        switch ($param) {
            case 'photo' : $filters['upload_gid'] = $this->image_config_gid;
                break;
            case 'video' : $filters['upload_gid'] = $this->video_config_gid;
                break;
        }
        if ($club_id) {
            $filters['club_id'] = $club_id;
        }

        $media_ids = $this->getMediaIds($filters, null, null, $order_by);

        $return['position'] = array_search($media_id, $media_ids) + 1;
        $return['count'] = count($media_ids);
        $return['next'] = $return['position'] < $return['count'] ? $media_ids[$return['position']] : 0;
        $return['previous'] = $return['position'] > 1 ? $media_ids[$return['position'] - 2] : 0;
        $return['club_id'] = $club_id;

        $media_ids = array($return['next'], $return['previous']);
        $medias = $this->getListByKey(['id' => $media_ids], null, null, null);
        $return['next_type'] = !empty($medias[$return['next']]) ? $medias[$return['next']]['upload_gid'] : null;
        $return['previous_type'] = !empty($medias[$return['previous']]) ? $medias[$return['previous']]['upload_gid'] : null;
        $return['next_image']['image'] = (!empty($medias[$return['next']]) && $return['next_type'] == $this->image_config_gid) ? $medias[$return['next']]['media']['file_url'] : null;

        $return['next_image']['thumb'] = (!empty($medias[$return['next']]) && $return['next_type'] == $this->image_config_gid) ? $medias[$return['next']]['media']['thumbs']['grand'] : null;

        $return['previous_image']['image'] = ((isset($medias[$return['previous']]['media']) && !empty($medias[$return['previous']])) && $return['next_type'] == $this->image_config_gid) ? $medias[$return['previous']]['media']['file_url'] : null;

        $return['previous_image']['thumb'] = ((isset($medias[$return['previous']]['media']) && !empty($medias[$return['previous']])) && $return['next_type'] == $this->image_config_gid) ? $medias[$return['previous']]['media']['thumbs']['grand'] : null;

        return $return;
    }
}
