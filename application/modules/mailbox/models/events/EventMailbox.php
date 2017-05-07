<?php

namespace Pg\Modules\Mailbox\Models\Events;

class EventMailbox extends \Symfony\Component\EventDispatcher\Event
{
    protected $sendFrom;
    protected $sendTo;

    public function getSendFrom()
    {
        return $this->sendFrom;
    }

    public function setSendFrom($value)
    {
        $this->sendFrom = $value;
    }

    public function getSendTo()
    {
        return $this->sendTo;
    }

    public function setSendTo($value)
    {
        $this->sendTo = $value;
    }
}
