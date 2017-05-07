<?php

namespace Pg\Modules\Services\Models\Events;

use Symfony\Component\EventDispatcher\Event;

class EventServices extends Event
{
    protected $data = array();

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }
}
