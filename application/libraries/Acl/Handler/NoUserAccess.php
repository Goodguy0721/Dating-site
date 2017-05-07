<?php

namespace Pg\Libraries\Acl\Handler;

use Pg\Libraries\Acl\Handler;

class NoUserAccess extends Handler
{
    public function render()
    {
        echo 'User is authenticated';
        exit;
    }
}
