<?php

namespace Pg\Libraries\Acl\Action;

use Pg\Libraries\Acl\Action;

class Admin extends Action
{

    protected $gid = 'admin';

    public function getGid()
    {
        return $this->gid;
    }

}
