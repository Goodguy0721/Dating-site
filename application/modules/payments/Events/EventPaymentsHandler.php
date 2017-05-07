<?php

namespace Pg\Modules\Payments\Events;

use Pg\Libraries\EventDispatcher;
use Pg\Libraries\EventHandler;
use Pg\Modules\Payments\Models\Payments_model;

class EventPaymentsHandler extends EventHandler
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
            $events = $ci->Statistics_model->getSystemEvents('payments');
            
            if (isset($events['payment_send']) && $events['payment_send'] == '1') {
                $event_handler->addListener('payments_payment_changed', function ($params) {
                    $params = $params->getData();
                    
                    if ($params['status'] != Payments_model::STATUS_PAYMENT_PROCESSED) {
                        return;
                    }
                    
                    $ci= &get_instance();
                    $ci->load->model("Statistics_model");
                    $file = $ci->Statistics_model->get_statistics_file('payments');
                    $log_path = TEMPPATH . 'logs/statistics/' . $file;
                
                    $stat_point_arr['gid'] = 'payment_send';
                    $stat_point_arr['params']['amount'] = $params['amount'];

                    $stat_point = json_encode($stat_point_arr);
                    $fp = fopen($log_path, "a");
                    fputs($fp, $stat_point . "\r\n");
                    fclose($fp);
                });
            }            
        }     
        
        if ($ci->pg_module->is_module_installed('special_offers')) {
            $event_handler->addListener('receive_payment_event', function ($event) {
                $ci = &get_instance();
                $ci->load->model("special_offers/models/Special_offers_model");
                $data = $event->getData();
                $ci->Special_offers_model->makeSpecialOffers($data);

            }); 
        }
        
        if(DEMO_MODE) {
            $event_handler->addListener('receive_payment_event', function ($event) {
                $ci = &get_instance();
                $ci->load->library('Analytics'); 
                $ci->analytics->track("payment completed");
            });
        }
        
    }
}
