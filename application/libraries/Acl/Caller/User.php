<?php

namespace Pg\Libraries\Acl\Caller;

use Pg\Libraries\Acl\Caller;

class User extends Caller
{

    const TYPE = 'user';

    protected $privileges = [];
    protected $restrictions = [];
    protected $roles = [];

    public function getCallerId()
    {
        return 0;
    }

    public function getCallerType()
    {
        return self::TYPE;
    }

    public function getCallerRoles()
    {
        return $this->roles;
    }

    public function getCallerPrivileges()
    {
        return $this->privileges;
    }

    public function getCallerRestrictions()
    {
        return $this->restrictions;
    }

}
