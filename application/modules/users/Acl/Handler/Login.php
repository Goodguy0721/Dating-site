<?php

namespace Pg\Modules\Users\Acl\Handler;

use Pg\Libraries\Acl\Handler;

class Login extends Handler
{

    public function render()
    {
        $this->ci->view->setRedirect(site_url() . 'users/login_form');
    }

}
