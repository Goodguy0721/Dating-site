<?php

namespace Pg\modules\access_permissions\Events;

use Pg\libraries\EventDispatcher;
use Pg\libraries\EventHandler;

/**
 * Access_permissions event handler
 *
 * @copyright	Copyright (c) 2000-2016
 * @author	Pilot Group Ltd <http://www.pilotgroup.net/>
 */

class EventAccessPermissionsHandler extends EventHandler
{
    /**
     * Init handler
     *
     * @return void
     */
    public function init()
    {
        $event_handler = EventDispatcher::getInstance();
        $event_handler->addListener('users_buy_group',
            function ($event) {
            $ci = &get_instance();
            if ($ci->pg_module->is_module_installed('special_offers')) {
                $ci->load->model("Special_offers_model");
                $ci->Special_offers_model->makeSpecialOffers(
                    $event->getData()
                 );
            }
            if ($_ENV['DEMO_MODE'] || TRIAL_MODE) {
                $ci->load->library('Analytics');
                $event = $ci->analytics->getEvent('payments', 'access_permissions', 'user');
                $ci->analytics->track($event);
            }
        });

    }
}
