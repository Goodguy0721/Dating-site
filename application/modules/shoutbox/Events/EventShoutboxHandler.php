<?php

namespace Pg\Modules\Shoutbox\Events;

use Pg\Modules\Shoutbox\Models\Shoutbox_model;
use Pg\Libraries\EventDispatcher;
use Pg\Libraries\EventHandler;

class EventShoutboxHandler extends EventHandler
{
    /**
     *  Init hanler
     *
     * @return void
     */
    public function init()
    {
        $event_handler = EventDispatcher::getInstance();
        $event_handler->addListener(Shoutbox_model::MESSAGE_BONUS_ACTION, function ($params) {
            $data = $params->getData();
            $ci = &get_instance();
            $ci->load->model("Shoutbox_model");
            $ci->Shoutbox_model->{$data['callback']}($data);
        });
    }
}
