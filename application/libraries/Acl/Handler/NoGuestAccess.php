<?php

namespace Pg\Libraries\Acl\Handler;

use Pg\Libraries\Acl\Handler;

class NoGuestAccess extends Handler
{
    public function render()
    {
        if (!$this->ci->router->is_api_class) {
            if ($this->ci->input->is_ajax_request() && !$this->ci->is_pjax) {
                $return = array('errors' => 'ajax_login_link');
                exit(json_encode($return));
            } else {
                $this->ci->setRedirect(site_url() . 'users/login_form');
            }
        } else {
            $this->ci->set_api_content('code', $this->ci);
        }
    }
}
