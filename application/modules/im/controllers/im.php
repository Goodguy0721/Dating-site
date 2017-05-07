<?php

namespace Pg\Modules\Im\Controllers;

/**
 * IM controller
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
 * */
class Im extends Class_im
{
    public function ajax_check_new_messages()
    {
        $this->view->assign($this->_check_new_messages());
    }

    public function ajax_get_contact_list()
    {
        $this->view->assign($this->_get_contact_list());
    }

    public function ajax_set_site_status()
    {
        $this->view->assign($this->_set_site_status());
    }

    public function ajax_get_messages()
    {
        $this->view->assign($this->_get_messages());
    }

    public function ajax_post_message()
    {
        $this->view->assign($this->_post_message());
    }

    public function ajax_get_history()
    {
        $this->view->assign($this->_get_history());
    }

    public function ajax_clear_history()
    {
        $this->view->assign($this->_clear_history());
    }

    public function ajax_get_im_status()
    {
        $this->view->assign($this->_get_im_status());
    }

    public function ajax_get_init()
    {
        $this->view->assign($this->_get_init());
    }

    public function ajax_available_im()
    {
        $return = array('available' => 0, 'content' => '', 'display_login' => 0);
        $id_user = $this->session->userdata('user_id');
        if ($this->session->userdata('auth_type') != 'user') {
            $return['display_login'] = 1;
        } else {
            $return = $this->Im_model->service_available_im_action($id_user);
            if ($return["content_buy_block"] == true) {
                $this->load->model('services/models/Services_users_model');
                $return["content"] = $this->Services_users_model->available_service_block($id_user, 'im_template');
            }
        }
        $this->view->assign($return);
    }

    public function ajax_activate_im($id_user_service)
    {
        $id_user = $this->session->userdata('user_id');
        $return = $this->Im_model->service_activate_im($id_user, $id_user_service);
        $this->view->assign($return);
    }
}
