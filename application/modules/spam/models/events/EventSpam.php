<?php

namespace Pg\Modules\Spam\Models\Events;

class EventSpam extends \Symfony\Component\EventDispatcher\Event
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
