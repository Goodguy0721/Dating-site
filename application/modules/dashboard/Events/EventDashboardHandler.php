<?php

namespace Pg\Modules\Dashboard\Events;

use Pg\Libraries\EventDispatcher;
use Pg\Libraries\EventHandler;

class EventDashboardHandler extends EventHandler
{
    private $modules = [
        'moderation',
        'users',
        'payments',
        'spam',
        'store',
        'banners',
    ];
    
    public function init()
    {
        $ci = &get_instance();
        
        $event_handler = EventDispatcher::getInstance();
        
        foreach ($this->modules as $module) {
            if ($ci->pg_module->is_module_active($module)) {
                $model = ucfirst($module . '_model');
                $ci->load->model($model);
                $events = $ci->{$model}->dashboard_events;                
                foreach ($events as $event_gid) {
                    $event_handler->addListener($event_gid, function ($event) { 
                        $ci = &get_instance();
                        $ci->load->model("Dashboard_model");
                        $ci->Dashboard_model->processEvent($event->getData());
                    });
                }
            }        
        }
    }
}
