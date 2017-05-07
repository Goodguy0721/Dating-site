<?php

namespace Pg\Modules\Network\Models;

/**
 * Network actions model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * */

/**
 * Network model
 */
class Network_actions_model extends \Model
{
    private $ci;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('network/models/Network_users_model');
    }

    public function get_settings()
    {
        $this->ci->load->model('Network_model');

        return $this->ci->Network_model->get_config($this->ci->Network_model->cfg_filter);
    }

    public function get_profiles($count = null)
    {
        $data = $this->ci->Network_users_model->get_profiles($count);

        return $data;
    }

    public function get_temp_profiles($action, $field = 'net_id')
    {
        if (!is_array($field)) {
            $field = array($field);
        }
        $records = $this->ci->Network_users_model->get_temp_records(null, null, $action, 'out', $field);

        return $records;
    }

    public function get_processed_net_ids($action, $field = 'net_id')
    {
        $temp_profiles = $this->get_temp_profiles($action, $field);
        $net_ids = array();
        foreach ($temp_profiles as $temp_profile) {
            $net_ids[] = $temp_profile[$field];
        }

        return $net_ids;
    }

    public function set_temp_profiles_add($profiles)
    {
        foreach ($profiles as &$profile) {
            $profile['net_id'] = $profile['id'];
            unset($profile['id']);
            $profile['photos'] = serialize($profile['photos']);
            $profile['profile_data'] = serialize($profile['profile_data']);
        }

        return $this->ci->Network_users_model->set_temp_profiles(
                $profiles,
                Network_users_model::ACTION_ADD,
                Network_users_model::TYPE_IN);
    }

    public function set_temp_profiles_remove($net_ids)
    {
        $profiles = array();
        foreach ($net_ids as $net_id) {
            $profiles[]['net_id'] = $net_id;
        }

        return $this->ci->Network_users_model->set_temp_profiles(
                $profiles,
                Network_users_model::ACTION_REMOVE,
                Network_users_model::TYPE_IN);
    }

    public function set_temp_profiles_update($profiles)
    {
        return $this->ci->Network_users_model->set_temp_profiles(
                $profiles,
                Network_users_model::ACTION_UPDATE,
                Network_users_model::TYPE_IN);
    }

    public function set_profiles_status($data)
    {
        return $this->ci->Network_users_model->set_profiles_status($data);
    }

    public function get_last_id()
    {
        return $this->ci->Network_users_model->get_last_id();
    }

    public function process_temp()
    {
        return $this->ci->Network_users_model->process_temp();
    }

    public function delete_temp_records($net_id = null, $local_id = null, $action = null, $type = null)
    {
        return $this->ci->Network_users_model->delete_temp_records($net_id, $local_id, $action, $type);
    }
}
