<?php

namespace Pg\Modules\Get_token\Models;

use Pg\Libraries\EventDispatcher;
use Pg\Modules\Get_token\Models\Events\EventGet_token;

class Get_token_model extends \Model
{
    public function mobileAuth($id = null)
    {
        if ($id) {
            $event_handler = EventDispatcher::getInstance();
            $event = new EventGet_token();
            $event_data = array();
            $event_data['id'] = $id;
            $event_data['action'] = 'get_token_mobile_auth';
            $event_data['module'] = 'get_token';
            $event->setData($event_data);
            $event_handler->dispatch('get_token_mobile_auth', $event);
        }
    }

    public function bonusCounterCallback($counter = array())
    {
        $event_handler = EventDispatcher::getInstance();
        $event = new EventGet_token();
        $event->setData($counter);
        $event_handler->dispatch('bonus_counter', $event);
    }

    public function bonusActionCallback($data = array())
    {
        $counter = array();
        if (!empty($data)) {
            $counter = $data['counter'];
            $action = $data['action'];
            $counter['count'] = $counter['count'] + 1;
            $counter['is_new_counter'] = $data['is_new_counter'];
            $counter['repetition'] = $data['bonus']['repetition'];
            $this->bonusCounterCallback($counter);
        }
    }
}
