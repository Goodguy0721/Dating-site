<?php

namespace Pg\Modules\Packages\Models\Events;

use Symfony\Component\EventDispatcher\Event;

class EventPackages extends Event
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
