<?php

namespace Pg\Modules\Network\Events;

use Pg\Libraries\EventDispatcher;
use Pg\Libraries\EventHandler;

class EventNetworkHandler extends EventHandler
{
    /**
     * Class constructor
     *
     * @return EventPropertiesHandler
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        $event_handler = EventDispatcher::getInstance();
        $CI = &get_instance();

        $event_handler->addListener('network_join_bonus_action', function ($params) {
            $CI = &get_instance();
            $CI->load->model("network/models/Network_users_model");
            $data = $params->getData();
            $callback = $data['callback'];
            $CI->Network_users_model->{$callback}($data);
        });
    }
}
