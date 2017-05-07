<?php

namespace Pg\Modules\Social_networking\Models\Services;

/**
 * Social networking vkontakte service model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * */
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Vkontakte_service_model extends \Model
{
    public $ci;
    public $api_url = 'https://api.vk.com/method/';

    public function __construct()
    {
        parent::__construct();
        $this->ci = &get_instance();
    }

    public function get_user_data($user_id = 0, $access_key = '')
    {
        $this->ci->load->model('Social_networking_connections_model');
        $params = array(
            'access_key' => $access_key,
            'uid'        => $user_id,
        );
        $response = $this->ci->Social_networking_connections_model->curl_get($this->api_url . 'getProfiles', $params);
        $data = @json_decode($response);
        $data = isset($data->response[0]) ? (array) $data->response[0] : false;
        if (isset($data['uid']) && isset($data['first_name']) && isset($data['last_name'])) {
            $user_data = array(
                'id'    => $data['uid'],
                'fname' => $data['first_name'],
                'sname' => $data['last_name'],
            );

            return $user_data;
        }

        return false;
    }
}
