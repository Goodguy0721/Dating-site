<?php

namespace Pg\Modules\Users\Models;

/**
 * User types model
 *
 * @package PG_Dating
 *
 * @author Pilot Group Ltd <http://www.pilotgroup.net/>
 */
define('GROUPS_TABLE', DB_PREFIX . 'groups');

/**
 * Users types main model
 *
 * @package 	PG_Dating
 * @subpackage 	Users
 *
 * @category	models
 *
 * @copyright 	Copyright (c) 2000-2016 PG Dating Pro
 * @author 		Pilot Group Ltd <http://www.pilotgroup.net/>
 */
class Groups_model extends \Model
{
    protected $ci;
    protected $db;
    protected $fields_all = [
        'id',
        'gid',
        'is_default',
        'is_active',
        'priority',
        'date_created',
        'date_modified',
    ];

    /**
     * Constructor
     *
     * @return users object
     */
    public function __construct()
    {
        parent::__construct();
        $this->ci = &get_instance();
        $this->db = &$this->ci->db;
        $this->db->memcache_tables(['GROUPS_TABLE']);
    }

    public function getGroupById($group_id)
    {
        $result = $this->db->select(implode(", ", $this->fields_all))->from(GROUPS_TABLE)->where("id",
                $group_id)->get()->result_array();
        if (empty($result)) {
            return false;
        } else {
            $data = $result[0];
            return $data;
        }
    }

    public function getGroupByGid($group_gid)
    {
        $result = $this->db->select(implode(", ", $this->fields_all))->from(GROUPS_TABLE)->where("gid",
                $group_gid)->get()->result_array();
        if (empty($result)) {
            return false;
        } else {
            $data = $result[0];
            return $data;
        }
    }

    public function getDefaultGroupId()
    {
        $result = $this->db->select("id")->from(GROUPS_TABLE)->where("is_default",
                '1')->get()->result_array();
        if (empty($result)) {
            return 0;
        } else {
            return $result[0]["id"];
        }
    }

    public function getGroupsList($params = [], $langs_ids = [])
    {

        if (empty($langs_ids)) {
            $langs_ids = array($this->ci->pg_language->current_lang_id);
        }

        $fields = $this->fields_all;

        foreach ($langs_ids as $lang_id) {
            $fields[] = 'name_' . $lang_id;
            $fields[] = 'description_' . $lang_id;
        }

        $this->db->select(implode(", ", $fields));
        $this->db->from(GROUPS_TABLE);

        if (isset($params["where"]) && is_array($params["where"]) && count($params["where"])) {
            foreach ($params["where"] as $field => $value) {
                $this->db->where($field, $value);
            }
        }

        if (isset($params["where_in"]) && is_array($params["where_in"]) && count($params["where_in"])) {
            foreach ($params["where_in"] as $field => $value) {
                $this->db->where_in($field, $value);
            }
        }

        if (isset($params["where_sql"]) && is_array($params["where_sql"]) && count($params["where_sql"])) {
            foreach ($params["where_sql"] as $value) {
                $this->db->where($value);
            }
        }
        
        $this->db->order_by('priority ASC');

        $results = $this->db->get()->result_array();
        if (!empty($results) && is_array($results)) {
            return $results;
        }

        return false;
    }

    public function getGroupsCount($params = array(), $filter_object_ids = null)
    {
        $this->db->select("COUNT(*) AS cnt");
        $this->db->from(GROUPS_TABLE);

        if (isset($params["where"]) && is_array($params["where"]) && count($params["where"])) {
            foreach ($params["where"] as $field => $value) {
                $this->db->where($field, $value);
            }
        }

        if (isset($params["where_in"]) && is_array($params["where_in"]) && count($params["where_in"])) {
            foreach ($params["where_in"] as $field => $value) {
                $this->db->where_in($field, $value);
            }
        }

        if (isset($params["where_sql"]) && is_array($params["where_sql"]) && count($params["where_sql"])) {
            foreach ($params["where_sql"] as $value) {
                $this->db->where($value);
            }
        }

        if (isset($filter_object_ids) && is_array($filter_object_ids) && count($filter_object_ids)) {
            $this->db->where_in("id", $filter_object_ids);
        }

        $result = $this->db->get()->result();
        if (!empty($result)) {
            return intval($result[0]->cnt);
        } else {
            return 0;
        }
    }

    public function saveGroup($group_id = null, $attrs = [])
    {
        if (is_null($group_id)) {
            $attrs["date_created"]  = $attrs["date_modified"] = date("Y-m-d H:i:s");
            $this->db->insert(GROUPS_TABLE, $attrs);
            $group_id               = $this->db->insert_id();
        } else {
            $attrs["date_modified"] = date("Y-m-d H:i:s");
            $this->db->where('id', $group_id);
            $this->db->update(GROUPS_TABLE, $attrs);
        }
        return $group_id;
    }

    public function deleteGroup($group_id)
    {
        $this->db->where('id', $group_id);
        $this->db->delete(GROUPS_TABLE);
        $this->ci->pg_language->pages->delete_string("groups_langs",
            "group_item_" . $group_id);

        return;
    }

    public function validateGroup($group_id = null, $data = array())
    {
        $return = array("errors" => array(), "data" => array());

        $default_lang_id = $this->ci->pg_language->current_lang_id;
        $languages       = $this->ci->pg_language->languages;

        //$return["data"]["is_default"] = isset($data["is_default"]) ? 1 : 0;
        $return["data"]["is_active"]  = isset($data["is_active"]) ? 1 : 0;

        if (isset($data["priority"])) {
            $return["data"]["priority"] = intval($data["priority"]);
        }

        if (isset($data['name_' . $default_lang_id])) {
            $return['data']['name_' . $default_lang_id] = trim(strip_tags($data['name_' . $default_lang_id]));
            if (empty($return['data']['name_' . $default_lang_id])) {
                $return['errors'][] = l('error_name_invalid', 'users');
            } else {
                foreach ($languages as $lid => $lang_data) {
                    if ($lid == $default_lang_id) {
                        continue;
                    }
                    if (!isset($data['name_' . $lid]) || empty($data['name_' . $lid])) {
                        $return['data']['name_' . $lid] = $return['data']['name_' . $default_lang_id];
                    } else {
                        $return['data']['name_' . $lid] = trim(strip_tags($data['name_' . $lid]));
                        if (empty($return['data']['name_' . $lid])) {
                            $return['errors'][] = l('error_name_invalid',
                                'users');
                            break;
                        }
                    }
                }
            }
            if (is_null($group_id)) {
                $guid_data = $this->createGUID($return['data']['name_' . $default_lang_id]);
                if (!empty($guid_data["errors"])) {
                    $return['errors'][] = $guid_data['errors'];
                } else {
                    $return['data']['gid'] = $guid_data['data']['gid'];
                }
            }
        } elseif (!$group_id) {
            $return['errors'][] = l('error_name_invalid', 'users');
        }

        if (isset($data['description_' . $default_lang_id])) {
            $return['data']['description_' . $default_lang_id] = trim($data['description_' . $default_lang_id]);
            if (!empty($return['data']['description_' . $default_lang_id])) {
                foreach ($languages as $lid => $lang_data) {
                    if ($lid == $default_lang_id) {
                        continue;
                    }
                    if (!isset($data['description_' . $lid]) || empty($data['description_' . $lid])) {
                        $return['data']['description_' . $lid] = $return['data']['description_' . $default_lang_id];
                    } else {
                        $return['data']['description_' . $lid] = trim($data['description_' . $lid]);
                    }
                }
            }
        }

        return $return;
    }

    public function setDefault($group_id)
    {
        $attrs["is_default"] = '0';
        $this->db->where('is_default', '1');
        $this->db->update(GROUPS_TABLE, $attrs);

        $attrs["is_default"] = '1';
        $this->db->where('id', $group_id);
        $this->db->update(GROUPS_TABLE, $attrs);

        return;
    }

    public function getGroupStringData($group_id)
    {
        $data = array();
        foreach ($this->ci->pg_language->languages as $lang_id => $lang_data) {
            $data[$lang_id] = $this->ci->pg_language->get_string("groups_langs",
                "group_item_" . $group_id, $lang_id);
        }

        return $data;
    }

    public function getGroupCurrentName($group_id)
    {
        return $this->ci->pg_language->get_string("groups_langs",
                "group_item_" . $group_id);
    }

    protected function createGUID($name = null)
    {
        if (!is_null($name)) {
            $this->ci->load->library('Translit');
            $return['data']['gid'] = strip_tags($name);
            $return['data']['gid'] = mb_strtolower($this->ci->translit->convert('ru', $return['data']['gid']));
            $return['data']['gid'] = preg_replace("/[^a-z0-9\-_]+/i", '-', $return['data']['gid']);
            if (empty($return['data']['gid'])) {
                $return['errors'][] = l('error_nickname_incorrect', 'users');
            } elseif (strlen($return['data']['gid']) > 15) {
                $return['errors'][] = l('error_nickname_incorrect', 'users');
            } else {
                $param['where']['gid'] = $return['data']['gid'];
                $gid_counts            = $this->getGroupsCount($param);
                if ($gid_counts > 0) {
                    $return['errors'][] = l('error_nickname_already_exists',  'users');
                }
            }
        }
        return $return;
    }

    public function update_langs($group_gids, $langs_file, $langs_ids)
    {
        foreach ($group_gids as $key => $value) {
            $group = $this->getGroupByGid($value);
            $this->ci->pg_language->pages->set_string_langs('groups_langs',
                'group_item_' . $group['id'],
                $langs_file['groups_demo_' . $value], (array) $langs_ids);
        }
    }

    public function export_langs($group_gids, $langs_ids = null)
    {
        $gids = array();
        foreach ($group_gids as $key => $value) {
            $group  = $this->getGroupByGid($value);
            $gids[] = 'groups_demo_' . $group['id'];
        }

        return $this->ci->pg_language->export_langs('groups_langs', $gids,
                $langs_ids);
    }

    /**
     * Install module fields depended on language
     *
     * @param integer $lang_id language identifier
     *
     * @return void
     */
    public function langDedicateModuleCallbackAdd($lang_id = false)
    {
        if (!$lang_id) {
            return;
        }

        $this->ci->load->dbforge();

        $fields = ['name_' . $lang_id => ['type' => 'TEXT', 'null' => true]];
        $this->ci->dbforge->add_column(GROUPS_TABLE, $fields);

        $default_lang_id = $this->ci->pg_language->get_default_lang_id();
        if ($lang_id != $default_lang_id) {
            $this->ci->db->set('name_' . $lang_id, 'name_' . $default_lang_id,
                false);
            $this->ci->db->update(GROUPS_TABLE);
        }

        $fields = ['description_' . $lang_id => ['type' => 'TEXT', 'null' => true]];
        $this->ci->dbforge->add_column(GROUPS_TABLE, $fields);

        if ($lang_id != $default_lang_id) {
            $this->ci->db->set('description_' . $lang_id,
                'description_' . $default_lang_id, false);
            $this->ci->db->update(GROUPS_TABLE);
        }
    }

    /**
     * Uninstall module fields depended on language
     *
     * @param integer $lang_id language identifier
     *
     * @return void
     */
    public function langDedicateModuleCallbackDelete($lang_id = false)
    {
        if (!$lang_id) {
            return;
        }

        $this->ci->load->dbforge();

        $fields_exists = $this->ci->db->list_fields(GROUPS_TABLE);

        $fields = [
            'name_' . $lang_id,
            'description_' . $lang_id,
        ];
        foreach ($fields as $field_name) {
            if (!in_array($field_name, $fields_exists)) {
                continue;
            }
            $this->ci->dbforge->drop_column(GROUPS_TABLE, $field_name);
        }
    }
}
