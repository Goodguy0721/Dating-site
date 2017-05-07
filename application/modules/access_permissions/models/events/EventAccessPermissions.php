<?php

namespace Pg\modules\access_permissions\models\events;

use Symfony\Component\EventDispatcher\Event;

/**
 * Access_permissions event
 *
 * @copyright	Copyright (c) 2000-2016
 * @author	Pilot Group Ltd <http://www.pilotgroup.net/>
 */

class EventAccessPermissions extends Event
{
    /**
     * Event data
     *
     * @var array
     */
    protected $data = [];

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set data
     *
     * @param array $data
     *
     * @return void
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}
