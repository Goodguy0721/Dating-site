<?php

if (!function_exists('services_buy_list')) {
    function services_buy_list($params = array())
    {
        $CI = &get_instance();
        $CI->load->model('Services_model');
        $CI->load->model('services/models/Services_users_model');

        $where['where']['type'] = 'tariff';
        $where['where']['status'] = 1;

        if (!empty($params['template_gid'])) {
            $where['where']['template_gid'] = $params['template_gid'];
        }

        $services = $CI->Services_model->get_service_list($where);

        $user_id = $CI->session->userdata("user_id");
        $where_my['where_sql'][] = "id_user = {$user_id} AND (id_users_membership = '0' AND (id_users_package = '0' OR status = '0'))";
         $services_my = $CI->Services_users_model->get_services_list($where_my);

        $services_modules = array();
        foreach ($services as $key => $service) {
            if ($service['template']['price_type'] > 2) {
                unset($services[$key]);
            } else {
                $model = strtolower($service['template']['callback_model']);
                $services_modules[$service['template']['callback_module']][$model] = ucfirst($model);
                foreach ($services_my as $service_my) {
                    if ($service['gid'] == $service_my['service_gid']) {
                        foreach ($service_my['service']['template']['data_admin_array'] as $setting_gid => $setting_options) {
                            if ($setting_gid != 'period') {
                                $services[$key]['service_user_data']['count'][$setting_gid] = $service_my['service']['data_admin_array'][$setting_gid];
                                if ($setting_options > 0) {
                                    $service_my['is_expired'] = 0;
                                }
                            }
                        }
                        if ($service_my['is_expired'] === false) {
                            unset($services[$key]['service_user_data']);
                        } else {
                            $services[$key]['service_user_data']['is_expired'] = $service_my['is_expired'];
                            $services[$key]['service_user_data']['date_expires'] = $service_my['date_expires'];
                        }
                    } 
                }
            }
        }

        $CI->view->assign('user_id', $user_id);        
        $CI->view->assign('services_block_services', $services);

        $date_formats = array(
            'date_format'      => $CI->pg_date->get_format('date_literal', 'st'),
            'date_time_format' => $CI->pg_date->get_format('date_time_literal', 'st'),
        );
        $CI->view->assign('services_block_date_formats', $date_formats);

        return $CI->view->fetch('helper_services_buy_list', 'user', 'services');
    }
}

if (!function_exists('user_services_list')) {
    function user_services_list($params)
    {
        $CI = &get_instance();
        $CI->load->model('services/models/Services_users_model');

        if (empty($params['id_user'])) {
            if ($CI->session->userdata('auth_type') == 'user') {
                $params['id_user'] = $CI->session->userdata('user_id');
            } else {
                return '';
            }
        }

        $order_by = array(
            'status'       => 'DESC',
            'count'        => 'DESC',
            'date_created' => 'DESC',
        );
        $where = array();
        $where['where_sql'][] = "id_user = {$params['id_user']} AND (id_users_membership = '0' AND (id_users_package = '0' OR status = '0'))";

        if (!empty($params['template_gid'])) {
            $where['where']['template_gid'] = $params['template_gid'];
        }

        $services = $CI->Services_users_model->get_services_list($where, $order_by);
        $CI->view->assign('services_block_services', $services);
        $date_formats = array(
            'date_format'      => $CI->pg_date->get_format('date_literal', 'st'),
            'date_time_format' => $CI->pg_date->get_format('date_time_literal', 'st'),
        );
        $CI->view->assign('services_block_date_formats', $date_formats);

        return $CI->view->fetch('helper_user_services_list', 'user', 'services');
    }
}

if (!function_exists('service_form')) {
    function service_form($params)
    {
        $CI = &get_instance();
        if (empty($params['gid'])) {
            log_message('error', '(services) Empty $params["gid"]');
            show_404();

            return;
        }
        $CI->load->model('Services_model');
        $user_id = $CI->session->userdata("user_id");
        $CI->load->model('users/models/Auth_model');
        $CI->Auth_model->update_user_session_data($user_id);
        $data = $CI->Services_model->format_service($CI->Services_model->get_service_by_gid($params['gid']));
        if ($data["template"]["price_type"] == "2" || $data["template"]["price_type"] == "3") {
            $data["price"] = $CI->input->post('price', true);
        }
        if (!empty($data["template"]["data_user_array"])) {
            foreach ($data["template"]["data_user_array"] as $gid => $temp) {
                $value = "";
                if ($temp["type"] == "hidden") {
                    $value = $CI->input->get_post($gid, true);
                }
                if (isset($user_form_data[$gid])) {
                    $value = $user_form_data[$gid];
                }
                $data["template"]["data_user_array"][$gid]["value"] = $value;
            }
        }
        //// get payments types
        $data["free_activate"] = false;
        if ($data["price"] <= 0) {
            $data["free_activate"] = true;
        }
        if ($data["pay_type"] == 1 || $data["pay_type"] == 2) {
            $CI->load->model("Users_payments_model");
            $data["user_account"] = $CI->Users_payments_model->get_user_account($user_id);
            if ($data["user_account"] <= 0 && $data["price"] > 0) {
                $data["disable_account_pay"] = true;
            } elseif (($data["template"]["price_type"] == 1 || $data["template"]["price_type"] == 3) && $data["price"] > $data["user_account"]) {
                $data["disable_account_pay"] = true;
            }
        }
        if ($data["pay_type"] == 2 || $data["pay_type"] == 3) {
            $CI->load->model("payments/models/Payment_systems_model");
            $billing_systems = $CI->Payment_systems_model->get_active_system_list();
            $CI->view->assign('billing_systems', $billing_systems);
        }
        $CI->view->assign('is_module_installed', $CI->pg_module->is_module_installed('users_payments'));
        $CI->view->assign('data', $data);

        return $CI->view->fetch('helper_service_form', 'user', 'services');
    }
}

if (!function_exists('services_get_menu')) {
    function services_get_menu()
    {
        $ci = &get_instance();

        if ('user' != $ci->session->userdata('auth_type')) {
            return false;
        }

        $ci->load->model('services/models/Services_users_model');
        $user_services = $ci->Services_users_model->getUserServices(
            $ci->session->userdata('user_id'), $ci->session->userdata('lang_id'), true);
        $ci->view->assign('user_services', $user_services);

        return $ci->view->fetch('helper_services_menu', 'user', 'services');
    }
}
