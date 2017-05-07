<?php

namespace Pg\Modules\Network\Models\Events;

class EventNetwork extends \Symfony\Component\EventDispatcher\Event
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
