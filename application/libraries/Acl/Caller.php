<?php

namespace Pg\Libraries\Acl;

use BeatSwitch\Lock\Callers\Caller as iCaller;
use BeatSwitch\Lock\LockAware;

class Caller implements iCaller
{

    use LockAware;

    protected $id = 0;
    protected $type = '';
    protected $roles = [];
    protected $privileges = [];
    protected $restrictions = [];

    public function __construct($caller_type = '', $caller_id = 0, array $caller_roles = null)
    {
        $this->type = (string) $caller_type;
        $this->id = (int) $caller_id;
        $this->roles = (array) $caller_roles;
    }

    public function getCallerId()
    {
        return $this->id;
    }

    public function getCallerType()
    {
        return $this->type;
    }

    public function getCallerRoles()
    {
        return $this->roles;
    }

}
