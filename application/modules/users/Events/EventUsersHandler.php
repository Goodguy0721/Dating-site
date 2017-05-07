<?php

namespace Pg\Modules\Users\Events;

use Pg\Libraries\EventDispatcher;
use Pg\Libraries\EventHandler;

class EventUsersHandler extends EventHandler
{
    /**
     * Init handler
     *
     * @return void
     */
    public function init()
    {
        $event_handler = EventDispatcher::getInstance();
        $ci = &get_instance();

        if ($ci->pg_module->is_module_installed('statistics')) {
            $ci->load->model("Statistics_model");
            $events = $ci->Statistics_model->getSystemEvents('users');

            if (isset($events['profile_view']) && $events['profile_view'] == '1') {
                $event_handler->addListener('profile_view', function ($params) {
                    $ci = &get_instance();
                    $ci->load->model("Statistics_model");
                    $file = $ci->Statistics_model->get_statistics_file('users');
                    $log_path = TEMPPATH . 'logs/statistics/' . $file;
                    $stat_point_arr['gid'] = 'profile_view';
                    $stat_point_arr['params']['from'] = $params->getProfileViewFrom();
                    $stat_point_arr['params']['to'] = $params->getProfileViewTo();
                    $stat_point_arr['params']['date'] = date('Y-m-d H:i:s');

                    $stat_point = json_encode($stat_point_arr);
                    $fp = fopen($log_path, "a");
                    fputs($fp, $stat_point . "\r\n");
                    fclose($fp);
                });
            }
            if (isset($events['user_search']) && $events['user_search'] == '1') {
                $event_handler->addListener('user_search', function ($params) {
                    $ci = &get_instance();
                    $ci->load->model("Statistics_model");
                    $file = $ci->Statistics_model->get_statistics_file('users');
                    $log_path = TEMPPATH . 'logs/statistics/' . $file;
                    $stat_point_arr['gid'] = 'user_search';
                    $stat_point_arr['params']['from'] = $params->getSearchFrom();
                    $stat_point_arr['params']['date'] = date('Y-m-d H:i:s');

                    $stat_point = json_encode($stat_point_arr);
                    $fp = fopen($log_path, "a");
                    fputs($fp, $stat_point . "\r\n");
                    fclose($fp);
                });
            }
            if (isset($events['user_register']) && $events['user_register'] == '1') {
                $event_handler->addListener('user_register', function ($params) {
                    $ci = &get_instance();
                    $ci->load->model("Statistics_model");
                    $file = $ci->Statistics_model->get_statistics_file('users');
                    $log_path = TEMPPATH . 'logs/statistics/' . $file;
                    $stat_point_arr['gid'] = 'user_register';
                    $stat_point_arr['params']['date'] = date('Y-m-d H:i:s');

                    $stat_point = json_encode($stat_point_arr);
                    $fp = fopen($log_path, "a");
                    fputs($fp, $stat_point . "\r\n");
                    fclose($fp);
                });
            }            
        }
        
        if(DEMO_MODE) {
            $event_handler->addListener('profile_view', function ($params) {
                $ci = &get_instance();
                $ci->load->library('Analytics'); 
                $ci->analytics->track("profile visit");
            });
            
            $event_handler->addListener('user_register', function ($params) {
                $ci = &get_instance();
                $ci->load->library('Analytics'); 
                $ci->analytics->track("registration");
            });
            
            $event_handler->addListener('users_add_profile_logo', function ($params) {
                $ci = &get_instance();
                $ci->load->library('Analytics'); 
                $ci->analytics->track("profile photo upload");
            });
        }

        $event_handler->addListener('users_site_visit_bonus_action', function ($params) {
                $ci = &get_instance();
                $ci->load->model("Users_model");
                $data = $params->getData();
                $callback = $data['callback'];
        $ci->Users_model->{$callback}($data);
        });

        $event_handler->addListener('users_update_user_profile_bonus_action', function ($params) {
                $ci = &get_instance();
                $ci->load->model("Users_model");
                $data = $params->getData();
                $callback = $data['callback'];
                $ci->Users_model->{$callback}($data);
        });

        $event_handler->addListener('users_add_profile_logo_bonus_action', function ($params) {
                $ci = &get_instance();
                $ci->load->model("Users_model");
                $data = $params->getData();
                $callback = $data['callback'];
        $ci->Users_model->{$callback}($data);
        });

        $event_handler->addListener('users_add_location_bonus_action', function ($params) {
                $ci = &get_instance();
                $ci->load->model("Users_model");
                $data = $params->getData();
                $callback = $data['callback'];
                $ci->Users_model->{$callback}($data);
        });
    }
}
