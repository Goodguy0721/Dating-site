<?php

namespace Pg\Modules\Ausers\Acl\Handler;

use Pg\Libraries\Acl\Handler;

class Login extends Handler
{

    public function render()
    {
        $this->ci->view->setRedirect(site_url() . 'admin/ausers/login');
    }

}
