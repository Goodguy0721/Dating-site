<?php

namespace Pg\Modules\Memberships\Models\Events;

use Symfony\Component\EventDispatcher\Event;

class EventMemberships extends Event
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
