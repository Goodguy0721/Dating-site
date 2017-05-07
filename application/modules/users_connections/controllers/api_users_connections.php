<?php

namespace Pg\Modules\Users_connections\Controllers;

/**
 * Users connections user side controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category    modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * */
class Api_users_connections extends \Controller
{
    public function socialApps()
    {
        $this->load->model('Users_connections_model');
        $apps = $this->Users_connections_model->getMobileApps(
            $this->session->userdata('user_id')
        );
        $this->view->assign('social_apps', $apps);
    }

    public function oauthLogin()
    {
        $service_gid = filter_input(INPUT_POST, 'service_gid');
        $service_user_id = filter_input(INPUT_POST, 'service_user_id');
        $data = array();
        $errors = array();
        if (empty($service_gid)) {
            $errors[] = 'empty_service_gid';
        }
        if (empty($service_user_id)) {
            $errors[] = 'empty_service_user_id';
        }
        if (empty($errors)) {
            $this->load->model('social_networking/models/Social_networking_services_model');
            $service = $this->Social_networking_services_model->get_service_by_gid($service_gid);
        }
        if (empty($service)) {
            $errors[] = 'wrong_service_gid';
        }
        if (empty($errors)) {
            $this->load->model('users_connections/models/Users_connections_model');
            $connection = $this->Users_connections_model->get_connection_by_data(
                $service['id'],
                $service_user_id
            );
            if (empty($connection)) {
                $errors[] = 'connection_not_found';
            } else {
                $this->load->model('users/models/Auth_model');
                $auth_data = $this->Auth_model->login($connection['user_id']);
                $data['auth_data'] = $auth_data;
                if (!empty($auth_data['errors'])) {
                    $errors = $auth_data['errors'];
                } elseif ($auth_data['invalid_data']) {
                    $errors[] = 'invalid_data';
                } elseif ($auth_data['blocked']) {
                    $errors[] = 'blocked';
                } elseif (!empty($auth_data["login"])) {
                    $data['token'] = $this->session->sess_create_token();
                }
            }
        }

        $this->view->assign('data', $data);
        $this->view->assign('errors', $errors);
    }

    public function oauthRegister()
    {
        $errors = array();
        $service_gid = filter_input(INPUT_POST, 'service_gid');
        $access_token = filter_input(INPUT_POST, 'access_token');
        $access_token_secret = filter_input(INPUT_POST, 'access_token_secret');
        $date_end = filter_input(INPUT_POST, 'date_end');
        $service_user_id = filter_input(INPUT_POST, 'service_user_id');

        if (empty($service_gid)) {
            $errors['empty_service_gid'] = 'empty_service_gid';
        }
        if (empty($access_token)) {
            $errors['empty_access_token'] = 'empty_access_token';
        }
        if (empty($date_end)) {
            $errors['empty_date_end'] = 'empty_date_end';
        }
        if (empty($service_user_id)) {
            $errors['empty_service_user_id'] = 'empty_service_user_id';
        }
        if (!empty($errors)) {
            $this->view->assign('errors', $errors);

            return false;
        }

        // Fetch service
        $this->load->model('social_networking/models/Social_networking_services_model');
        $service = $this->Social_networking_services_model->getServiceByGid($service_gid);
        if (empty($service)) {
            $this->view->assign('errors', array('service_not_found'));

            return false;
        }

        $this->load->model('users_connections/models/Users_connections_model');
        $existing_connection = $this->Users_connections_model->get_connection_by_data(
            $service['id'],
            $service_user_id
        );
        if ($existing_connection) {
            $existing_connection['token'] = $this->session->sess_create_token();
            $existing_connection['registered'] = false;
            $this->view->assign('data', $existing_connection);

            return false;
        }

        /*$this->load->library('Translit');
        $service_user_fname = $this->translit->convert(
            'ru',
            filter_input(INPUT_POST, 'service_user_fname') ?: 'fname'
        );
        $service_user_sname = $this->translit->convert(
            'ru',
            filter_input(INPUT_POST, 'service_user_sname') ?: 'sname'
        );*/

        $service_user_fname = filter_input(INPUT_POST, 'service_user_fname') ?: '';
        $service_user_sname = filter_input(INPUT_POST, 'service_user_sname') ?: '';

        $service_user_fname = trim($service_user_fname);
        $service_user_sname = trim($service_user_sname);

        $service_user_email = filter_input(INPUT_POST, 'service_user_email');
        if (empty($service_user_email)) {
            $service_user_email = $service_user_fname . $service_user_sname . '@mail.com';
        }
        $user_type = filter_input(INPUT_POST, 'user_type');

        $birth_date = filter_input(INPUT_POST, 'birth_date');

        if (empty($birth_date)) {
            $age_min = $tis->pg_module->get_module_config('users', 'age_min');
            $birth_date = date_format('Y-m-d', time() - $age_min * 86400);
        }

        $country_code = filter_input(INPUT_POST, 'country_code');
        $region_id = (int)filter_input(INPUT_POST, 'region_id');
        $city_id = (int)filter_input(INPUT_POST, 'city_id');

        // Check email uniqueness
        $this->load->model('Users_model');
        $this->load->model('users/models/Groups_model');
        $count = $this->Users_model->get_users_count(
            array('where' => array('email' => $service_user_email))
        );
        if ($count > 0) {
            $service_user_email = rand(100, 300) . $service_user_email;
        }

        $save_data = array(
            //'nickname'      => $service_user_fname . $service_user_sname,
            'email'         => $service_user_email,
            'confirm'       => 1,
            'user_type'     => $user_type,
            'birth_date'    => $birth_date,
            'lang_id'       => $this->pg_language->current_lang_id,
            'group_id'      => $this->Groups_model->getDefaultGroupId(),
            'approved'      => (intval($this->pg_module->get_module_config('users', 'user_approve')) ? 0 : 1),
            'id_country'    => $country_code,
            'id_region'     => $region_id,
            'id_city'     => $city_id,
        );

        if (!empty($service_user_fname)) {
            $save_data['fname'] = $service_user_fname;
        }

        if (!empty($service_user_sname)) {
            $save_data['sname'] = $service_user_sname;
        }

        // Save user
        $user_id = $this->Users_model->save_user(null, $save_data);

        // Authorize user
        $this->load->model('users/models/Auth_model');
        $auth_data = $this->Auth_model->login($user_id);
        if (!empty($auth_data["errors"])) {
            $this->view->assign('errors', $auth_data["errors"]);

            return false;
        }

        // Save connection with social app
        $connection = array(
            'service_id'          => $service['id'],
            'user_id'             => $user_id,
            'access_token'        => $access_token,
            'access_token_secret' => $access_token_secret,
            'data'                => $service_user_id,
            'date_end'            => $date_end,
        );
        $this->Users_connections_model->save_connection(null, $connection);

        $result = $connection;
        $result['token'] = $this->session->sess_create_token();
        $result['registered'] = true;
        $messages[] = l('please_set_email', 'users');

        $this->view->assign('data', $result);
        $this->view->assign('errors', $errors);
        $this->view->assign('messages', $messages);
    }
}
