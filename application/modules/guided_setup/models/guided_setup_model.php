<?php

/**
 * GuidedSetup module
 *
 * @package 	PG_Dating
 *
 * @copyright 	Copyright (c) 2000-2014 PG Dating Pro - php dating software
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
namespace Pg\modules\guided_setup\models;

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

define("GUIDED_SETUP_MENU_TABLE", DB_PREFIX . "guided_setup_menu");
define("GUIDED_SETUP_PAGES_TABLE", DB_PREFIX . "guided_setup_pages");

/**
 * SecretGifts main model
 *
 * @package 	PG_Dating
 * @subpackage 	GuidedSetup
 *
 * @category	models
 *
 * @copyright 	Copyright (c) 2000-2014 PG Dating Pro - php dating software
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
class Guided_setup_model extends \Model
{
    /**
     * Module GUID
     *
     * @var string
     */
    const MODULE_GID      = 'guided_setup';

    /**
     * Link to CodeIgniter object
     *
     * @var object
     */
    protected $ci;

    /**
     * Link to DB object
     *
     * @var type
     */
    protected $db;

    /**
     * GuidedSetup object properties
     *
     * @var array
     */
    protected $fields_pages = array(
            'id',
            'guided_menu_id',
            'url',
            'sorter',
            'is_active',
            'is_configured',
            'is_new',
    );
    
    protected $fields_menu = array(
            'id',
            'gid',
    );

    /**
     * Class constructor
     *
     * @return SecretGifts_model
     */
    public function __construct()
    {
        parent::__construct();
        $this->ci = &get_instance();
        $this->db = &$this->ci->db;
    }
    
    public function getPages($params = array(), $format = true)
    {
        $this->db->select();
        $this->db->from(GUIDED_SETUP_PAGES_TABLE);

        if (isset($params["where"]) && is_array($params["where"]) && count($params["where"])) {
            foreach ($params["where"] as $field => $value) {
                $this->db->where($field, $value);
            }
        }

        if (isset($params["like"]) && is_array($params["like"]) && count($params["like"])) {
            foreach ($params["like"] as $field => $value) {
                $this->db->like($field, $value);
            }
        }

        if (isset($params["where_in"]) && is_array($params["where_in"]) && count($params["where_in"])) {
            foreach ($params["where_in"] as $field => $value) {
                $this->db->where_in($field, $value);
            }
        }

        if (isset($params["where_sql"]) && is_array($params["where_sql"]) && count($params["where_sql"])) {
            foreach ($params["where_sql"] as $value) {
                $this->db->where($value, null, false);
            }
        }

        if (!empty($filter_object_ids) && is_array($filter_object_ids)) {
            $this->db->where_in("id", $filter_object_ids);
        }

        if (isset($order_by) && is_array($order_by) && count($order_by) > 0) {
            foreach ($order_by as $field => $dir) {
                if (in_array($field, $this->fields_all) || $field == 'fields') {
                    $this->db->order_by($field . " " . $dir);
                }
            }
        }
        
        if (isset($params['limit']) && $params['limit'] > 0) {
            $this->db->limit($params['limit']);
        }

        $results = $this->db->get()->result_array();
        if (!empty($results) && is_array($results)) {
            if ($format) {
                $results = $this->formatPages($results);
            }
        }
        
        return $results;
    }
    
    public function savePage($page_id, $data = array())
    {
        $this->db->where('id', $page_id);
        $this->db->update(GUIDED_SETUP_PAGES_TABLE, $data);
    }

    private function formatPages($pages)
    {
        $formatted = array();
        $this->ci->load->model('Menu_model');
        $lang_id = $this->ci->pg_language->current_lang_id;
        foreach($pages as $key => $page) {
            $page_link = $this->formatLink($page['url']);
            if($page_link) {
                $formatted[$key] = array(
                    'name' => $page['name_' . $lang_id],
                    'description' => $page['name_description_' . $lang_id],
                    'page_id' => $page['id'],
                    'link' => $page_link,
                    'is_configured' => $page['is_configured'],
                    'is_new' => $page['is_new'],
                );                
            }

        }
        
        return $formatted;
    }
    
    private function formatLink($url)
    {
        if(strpos($url, 'http') !== false) {
            return $url;
        } else {
            $url_arr = explode('/', trim($url, '/'));
            $segments = $this->ci->router->_validate_request($url_arr);
            
            if($this->ci->router->is_admin_class) {
                if($this->ci->pg_module->is_module_installed($segments[0])) {
                    switch($segments[0]) {
                        case 'themes':
                            $active_settings = $this->ci->pg_theme->return_active_settings();
                            $scheme = $active_settings['user']['scheme_data'];
                            $url = str_replace(array('[id_theme]', '[id_colorset]'), array($scheme['id_theme'], $scheme['id']), $url); 
                            break;
                    }
                    
                    return site_url() . $url;
                } else {
                    switch($segments[0]) {
                        case 'tickets':
                            return site_url() . 'admin/contact_us/index'; 
                            break;
                    }
                }

            }
            
            return '';
        }
    }
    
    public function getMenuByGid($gid = 'guided_pages') {
        $this->db->select();
        $this->db->from(GUIDED_SETUP_MENU_TABLE);
        $this->db->where('gid', $gid);
        $results = $this->db->get()->result_array();
        
        return $results[0];
    }

}
