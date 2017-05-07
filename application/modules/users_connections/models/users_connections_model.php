<?php

namespace Pg\Modules\Users_connections\Models;

/**
 * User connections(social networking) model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * */
if (!defined('USER_CONNECTIONS_TABLE')) {
    define('USER_CONNECTIONS_TABLE', DB_PREFIX . 'user_connections');
}

class Users_connections_model extends \Model
{
    public $ci;
    public $db;
    public $fields_all = array(
        'id',
        'user_id',
        'service_id',
        'access_token',
        'access_token_secret',
        'data',
        'date_end',
    );

    public function __construct()
    {
        parent::__construct();
        $this->ci = &get_instance();
        $this->db = &$this->ci->db;
    }

    public function save_connection($connection_id = false, $data = array())
    {
        if (!$connection_id) {
            $this->db->insert(USER_CONNECTIONS_TABLE, $data);
            $connection_id = $this->db->insert_id();
        } else {
            $this->db->where('id', $connection_id);
            $this->db->update(USER_CONNECTIONS_TABLE, $data);
        }

        return $connection_id;
    }

    public function get_connection_by_user_id($service_id = false, $user_id = false)
    {
        $data = array();
        $select_attrs = $this->fields_all;
        $this->db->select(implode(", ", $select_attrs))->from(USER_CONNECTIONS_TABLE)->where(array('service_id' => $service_id, 'user_id' => $user_id))->order_by('id ASC');
        $result = $this->db->get()->result_array();
        if (!empty($result)) {
            $data = $result[0];
        }

        return $data;
    }

    public function get_connections_user_id($user_id = false, $services)
    {
        $this->db->select(implode(", ", $this->fields_all))->from(USER_CONNECTIONS_TABLE)
                 ->where(['user_id' => $user_id])
                 ->order_by('id ASC');
        $result = $this->db->get()->result_array();
        return $this->formatUserConnections($result, $services);
    }

    public function get_connection_by_id($connection_id = false)
    {
        $data = array();
        $select_attrs = $this->fields_all;
        $this->db->select(implode(", ", $select_attrs))->from(USER_CONNECTIONS_TABLE)->where(array('id' => $connection_id))->order_by('id ASC');
        $result = $this->db->get()->result_array();
        if (!empty($result)) {
            $data = $result[0];
        }

        return $data;
    }

    public function get_connection_by_data($service_id = false, $odata = false)
    {
        $data = array();
        $select_attrs = $this->fields_all;
        $this->db->select(implode(", ", $select_attrs))->from(USER_CONNECTIONS_TABLE)->where(array('service_id' => $service_id, 'data' => $odata))->order_by('id ASC');
        $result = $this->db->get()->result_array();
        if (!empty($result)) {
            $data = $result[0];
        }

        return $data;
    }

    public function delete_connection($connection_id = false)
    {
        $data = $this->get_connection_by_id($connection_id);
        if (empty($data)) {
            return false;
        }
        $this->db->where('id', $connection_id);
        $this->db->delete(USER_CONNECTIONS_TABLE);

        return;
    }

    public function delete_user_connections($user_id)
    {
        $this->db->where('user_id', $user_id)->delete(USER_CONNECTIONS_TABLE);

        return;
    }

    public function getMobileApps($user_id = null)
    {
        $this->ci->load->model('social_networking/models/Social_networking_services_model');
        $this->ci->load->model('Users_connections_model');
        $used_apps = array('vkontakte', 'facebook');
        $services = $this->ci->Social_networking_services_model->get_services_list(
            null, array(
            'where'    => array('oauth_status' => 1),
            'where_in' => array('gid'          => $used_apps),
            )
        );
        $apps = array();
        foreach ($services as $id => $val) {
            if (!empty($user_id)) {
                $connection = $this->ci->Users_connections_model->get_connection_by_user_id($val['id'], $user_id);
            }
            if (!isset($connection['id'])) {
                $apps[$id] = $val;
            }
            $apps[$id]['link'] = site_url() . 'mobile/oauth_login/' . $id;
        }

        return $apps;
    }

    private function formatUserConnections($data, $services)
    {
        foreach ($services as $key => $service) {
            $services[$key] = $service;
            $services[$key]['is_disabled'] = false;
            foreach ($data as $connection) {
                if ($service['id'] == $connection['service_id']) {
                    $services[$key]['is_disabled'] = true;
                }
            }
        }
        return $services;
    }

}
