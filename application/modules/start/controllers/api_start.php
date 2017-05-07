<?php

namespace Pg\Modules\Start\Controllers;

/**
 * Start api controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category    modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * */
class Api_Start extends \Controller
{
    public function backend()
    {
        $id_user = intval($this->session->userdata('user_id'));
        $result = array();
        $post_data = (array) $this->input->post('data');

        foreach ($post_data as $gid => $params) {
            if (empty($params['module']) || empty($params['model']) || empty($params['method'])) {
                continue;
            } elseif ($this->pg_module->is_module_installed($params['module']) &&
                      $this->load->model($params['module'] . '/models/' . $params['model'], $gid . '_backend_model', false, true, true) &&
                      method_exists($this->{$gid . '_backend_model'}, 'backend_' . $params['method'])) {
                $method = $params['method'];
                unset($params['module'], $params['model'], $params['method']);
                $params['id_user'] = $id_user;
                $result[$gid] = $this->{$gid . '_backend_model'}->{'backend_' . $method}($params);
            }
        }
        $this->set_api_content('data', $result);
    }
}
