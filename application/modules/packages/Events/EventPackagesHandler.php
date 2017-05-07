<?php

namespace Pg\Modules\Packages\Events;

use Pg\Libraries\EventDispatcher;
use Pg\Libraries\EventHandler;

class EventPackagesHandler extends EventHandler
{
    /**
     * Init handler
     *
     * @return void
     */
    public function init()
    {        
        $event_handler = EventDispatcher::getInstance();
        if(DEMO_MODE) {
            $event_handler->addListener('users_view_package_form', function ($event) {
                $ci = &get_instance();
                $ci->load->library('Analytics'); 
                $ci->analytics->track("payment initiated");
            });   
            
            $event_handler->addListener('users_buy_package', function ($event) {
                $ci = &get_instance();
                $ci->load->library('Analytics'); 
                $ci->analytics->track("payment completed");
            });            
        }
    }
}
