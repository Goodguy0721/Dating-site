<?php

namespace Pg\Modules\Chats\Models\Chats;

/**
 * Chats model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Alexander Batukhtin <abatukhtin@pilotgroup.net>
 * */
class Cometchat extends Chat_abstract
{
    private $admin_url = 'admin/index.php';
    private $install_url = 'install.php';
    protected $_name = 'Cometchat';
    protected $_gid = 'cometchat';
    protected $_activities = array('include');
    protected $_settings = array();

    public function __construct()
    {
        parent::__construct();
        $this->CI->load->model('Chats_model');
        $this->_dir = SITE_VIRTUAL_PATH . $this->CI->Chats_model->path . $this->get_gid() . '/';
    }

    public function user_page()
    {
        return false;
    }

    public function include_block()
    {
        $this->view->assign('chat', $this->as_array());
        $this->view->assign('url', $this->_dir . $this->admin_url);

        return $this->view->fetch('cometchat_include', 'user', 'chats');
    }

    public function admin_page()
    {
        $this->view->assign('chat', $this->as_array());
        $this->view->assign('url', $this->_dir . $this->admin_url);
        $this->view->assign('width', 1014);
        $this->view->assign('height', 737);

        return $this->view->fetch('cometchat_admin', 'admin', 'chats');
    }

    public function install_page()
    {
        $this->view->assign('chat', $this->as_array());
        $this->view->assign('url', $this->_dir . $this->install_url);
        $this->view->assign('width', 380);
        $this->view->assign('height', 456);

        return $this->view->fetch('cometchat_install', 'admin', 'chats');
    }

    public function validate_settings()
    {
        return true;
    }
}
