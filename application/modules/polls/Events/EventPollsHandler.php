<?php

namespace Pg\Modules\Polls\Events;

use Pg\Libraries\EventDispatcher;
use Pg\Libraries\EventHandler;

class EventPollsHandler extends EventHandler
{
    /**
     * Init handler
     *
     * @return void
     */
    public function init()
    {
        $event_handler = EventDispatcher::getInstance();
        $event_handler->addListener('polls_user_voted_bonus_action', function ($params) {
            $ci = &get_instance();
            $ci->load->model("Polls_model");
            $data = $params->getData();
            $callback = $data['callback'];
            $ci->Polls_model->{$callback}($data);
        });
    }
}
