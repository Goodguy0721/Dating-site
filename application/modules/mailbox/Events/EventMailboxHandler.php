<?php

namespace Pg\Modules\Mailbox\Events;

use Pg\Libraries\EventDispatcher;
use Pg\Libraries\EventHandler;

class EventMailboxHandler extends EventHandler
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
            $events = $ci->Statistics_model->getSystemEvents('mailbox');

            if (isset($events['send']) && $events['send'] == '1') {
                $event_handler->addListener('send', function ($params) {
                    $ci = &get_instance();
                    $ci->load->model("Statistics_model");
                    $file = $ci->Statistics_model->get_statistics_file('mailbox');
                    $log_path = TEMPPATH . 'logs/statistics/' . $file;
                    $stat_point_arr['gid'] = 'send';
                    $stat_point_arr['params']['from'] = $params->getSendFrom();
                    $stat_point_arr['params']['to'] = $params->getSendTo();
                    $stat_point_arr['params']['date'] = date('Y-m-d H:i:s');

                    $stat_point = json_encode($stat_point_arr);
                    $fp = fopen($log_path, "a");
                    fputs($fp, $stat_point . "\r\n");
                    fclose($fp);
                });
            }
        }
    }
}
