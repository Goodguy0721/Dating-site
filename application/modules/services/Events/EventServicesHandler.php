<?php

namespace Pg\Modules\Services\Events;

use Pg\Libraries\EventDispatcher;
use Pg\Libraries\EventHandler;

class EventServicesHandler extends EventHandler
{
    /**
     * Init handler
     *
     * @return void
     */
    public function init()
    {
        $event_handler = EventDispatcher::getInstance();
        $event_handler->addListener('users_get_service', function ($event) {
                $ci = &get_instance();
                if ($ci->pg_module->is_module_installed('special_offers')) {
                    $ci->load->model("special_offers/models/Special_offers_model");
                    $data = $event->getData();
                    $ci->Special_offers_model->makeSpecialOffers($data);
                }
        });
        
        if(DEMO_MODE) {
            $event_handler->addListener('users_view_service_form', function ($event) {
                $ci = &get_instance();
                $ci->load->library('Analytics'); 
                $ci->analytics->track("payment initiated");
            });   
            
            $event_handler->addListener('users_get_service', function ($event) {
                $ci = &get_instance();
                $ci->load->library('Analytics'); 
                $ci->analytics->track("payment completed");
            });            
        }

        
        
        
        

    }
}
