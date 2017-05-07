<?php

namespace Pg\Modules\Shoutbox\Controllers;

/**
 * Shoutbox controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Dmitry Popenov
 *
 * @version $Revision: 2 $ $Date: 2013-01-30 10:07:07 +0400 $
 **/
class Shoutbox extends Class_shoutbox
{
    public function ajax_check_new_messages()
    {
        $this->view->assign($this->_check_new_messages());

        return;
    }

    public function ajax_get_messages()
    {
        $this->view->assign($this->_get_messages());

        return;
    }

    public function ajax_post_message()
    {
        $this->view->assign($this->_post_message());

        return;
    }
}
