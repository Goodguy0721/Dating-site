<?php

namespace Pg\Modules\Get_token\Models\Events;

class EventGet_token extends \Symfony\Component\EventDispatcher\Event
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
