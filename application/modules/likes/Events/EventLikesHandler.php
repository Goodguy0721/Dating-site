<?php

namespace Pg\Modules\Likes\Events;

use Pg\Libraries\EventDispatcher;
use Pg\Libraries\EventHandler;

class EventLikesHandler extends EventHandler
{

    /**
     * Init handler
     *
     * @return void
     */
    public function init()
    {
        $event_handler = EventDispatcher::getInstance();
        $event_handler->addListener('likes_add_like_bonus_action',
            function ($params) {
            $ci       = &get_instance();
            $ci->load->model("Likes_model");
            $data     = $params->getData();
            $callback = $data['callback'];
            $ci->Likes_model->{$callback}($data);
        });
    }
}