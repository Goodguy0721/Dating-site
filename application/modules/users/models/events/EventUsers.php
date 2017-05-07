<?php

namespace Pg\Modules\Users\Models\Events;

use Symfony\Component\EventDispatcher\Event;

class EventUsers extends Event
{
    protected $profileViewFrom;
    protected $profileViewTo;
    protected $searchFrom;
    protected $data = array();

    public function getProfileViewFrom()
    {
        return $this->profileViewFrom;
    }

    public function setProfileViewFrom($value)
    {
        $this->profileViewFrom = $value;
    }

    public function getProfileViewTo()
    {
        return $this->profileViewTo;
    }

    public function setProfileViewTo($value)
    {
        $this->profileViewTo = $value;
    }

    public function getSearchFrom()
    {
        return $this->searchFrom;
    }

    public function setSearchFrom($value)
    {
        $this->searchFrom = $value;
    }


    public function getData()
    {
        return $this->data;
    }
    
    public function setData($data)
    {
        $this->data = $data;
    }
}
