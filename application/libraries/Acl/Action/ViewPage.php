<?php

namespace Pg\Libraries\Acl\Action;

use Pg\Libraries\Acl\Action;
use Pg\Libraries\Acl\Resource;
use Pg\Libraries\View;

class ViewPage extends Action
{
    /**
     * Action gid
     *
     * @var string
     */
    protected $gid = 'view_page';

    /**
     * Denied callback
     *
     */
    public function onDenied()
    {
        $ci = get_instance();
        if ($ci->router->is_admin_class) {
            // TODO: Add onDeniedINstaller or something
            $this->onDeniedAdmin();
        } else {
            $this->onDeniedUser();
        }
    }

    /**
     * Callback for admin
     *
     */
    protected function onDeniedAdmin()
    {
        $ci = get_instance();
        $res_login_page = new Resource\Page(
            ['module' => 'ausers', 'controller' => 'admin_controller', 'action' => 'login',]
        );
        if ($ci->acl->checkSimple($this->gid, $res_login_page->getResourceType(), $res_login_page->getResourceId())) {
            $ci->view->setRedirect(site_url() . 'admin/ausers/login');
        } else {
            $ci->view->setRedirect();
        }
    }

    protected function onDeniedUser()
    {
        $ci = get_instance();
        $res_login_page = new Resource\Page(
            ['module' => 'users', 'controller' => 'users', 'action' => 'login',]
        );

        if ($ci->router->is_api_class || $ci->input->is_ajax_request()) {
            if ($ci->session->userdata['auth_type'] == 'user') {
                header('HTTP/1.0 403 Forbidden', true, 403);
                exit(l('info_action_change_group','access_permissions'));
            } else {
                header('HTTP/1.0 403 Forbidden', true, 403);
                exit(json_encode(['errors' => 'ajax_login_link']));
            }
        } elseif ($ci->acl->checkSimple($this->gid, $res_login_page->getResourceType(), $res_login_page->getResourceId())) {
            if ($ci->session->userdata['auth_type'] == 'user') {
                $ci->system_messages->addMessage(View::MSG_INFO,  l('info_view_change_group','access_permissions'));
                $ci->view->setRedirect(site_url() . 'access_permissions/index', 'hard');
            } else {
                $lang_id = $ci->session->userdata('lang_id');
                $ci->load->model('users/models/Auth_model');
                $ci->session->sess_destroy();
                $ci->session->sess_create();
                $ci->session->set_userdata('lang_id', $lang_id);
                $ci->system_messages->addMessage(View::MSG_INFO, l('info_authorized_user','access_permissions'));
                $ci->view->setRedirect(site_url() . 'users/login_form', 'hard');
            }
        } else {
            if ($ci->session->userdata['auth_type'] == 'user') {
                    $ci->system_messages->addMessage(View::MSG_INFO,  l('info_view_change_group','access_permissions'));
                    $ci->view->setRedirect(site_url() . 'access_permissions/index', 'hard');
             } else {
                $error = &load_class('Exceptions');
                $error->show_403();
             }
        }
    }
}
