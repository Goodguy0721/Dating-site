<?php

namespace Pg\Libraries\Acl\Driver\Decorator;

use BeatSwitch\Lock\Drivers\Driver as IDriver;

abstract class Decorator implements IDriver
{

    protected $driver;

    public function __construct(IDriver $driver)
    {
        $this->driver = $driver;
    }

}
