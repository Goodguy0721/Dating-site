<?php

namespace Pg\Modules\Users\Acl\Action;

use Pg\Libraries\Acl\Action;

class Login extends Action
{
    const GID = 'login';

    public function getGid()
    {
        return self::GID;
    }
}
