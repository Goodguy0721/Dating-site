<?php

namespace Pg\Modules\Media\Events;

use Pg\Modules\Media\Models\Media_model;
use Pg\Libraries\EventDispatcher;
use Pg\Libraries\EventHandler;

class EventMediaHandler extends EventHandler
{
    /**
     * Init handler
     *
     * return void
     */
    public function init()
    {
        $event_handler = EventDispatcher::getInstance();
        $event_handler->addListener(Media_model::EVENT_UPLOAD_IMAGE, function ($params) {
            $data = $params->getData();
            $ci = &get_instance();
            $ci->load->model("Media_model");
            $ci->Media_model->{$data['callback']}($data);
        });
        $event_handler->addListener(Media_model::EVENT_UPLOAD_AUDIO,  function ($params) {
            $data = $params->getData();
            $ci = &get_instance();
            $ci->load->model("Media_model");
            $ci->Media_model->{$data['callback']}($data);
        });
        $event_handler->addListener(Media_model::EVENT_UPLOAD_VIDEO, function ($params) {
            $data = $params->getData();
            $ci = &get_instance();
            $ci->load->model("Media_model");
            $ci->Media_model->{$data['callback']}($data);
        });

    }
}
