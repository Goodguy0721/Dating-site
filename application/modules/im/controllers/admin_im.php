<?php

namespace Pg\Modules\Im\Controllers;

use Pg\Libraries\View;

/**
 * IM admin side controller
 *
 * @package PG_DatingPro
 * @subpackage Kisses
 *
 * @category	controllers
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * */
class Admin_Im extends \Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Menu_model');
        $this->Menu_model->set_menu_active_item('admin_menu', 'add_ons_items');
    }

    /**
     * Main page
     */
    public function index()
    {
        return $this->settings();
    }

    /**
     * Setting module
     *
     * @return template
     */
    public function settings()
    {
        $data = array(
            'status'            => $this->pg_module->get_module_config('im', 'status'),
            'message_max_chars' => $this->pg_module->get_module_config('im', 'message_max_chars'),
        );

        if ($this->input->post('btn_save')) {
            $data['status'] = $this->input->post('status', true);
            $data['message_max_chars'] = $this->input->post('message_max_chars', true);

            $this->load->model('Im_model');
            $validate_data = $this->Im_model->validateSettings($data);
            if (!empty($validate_data['errors'])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data['errors']);
            } else {
                foreach ($validate_data['data'] as $setting => $value) {
                    $this->pg_module->set_module_config('im', $setting, $value);
                }
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_update', 'im'));
                redirect(site_url() . 'admin/im/settings');
            }
        }

        $this->view->setHeader(l('admin_header_im_chat_settings', 'im'));
        $this->Menu_model->set_menu_active_item('admin_im_menu', 'im_settings');
        $this->view->assign('settings_data', $data);
        $this->view->render('settings');
    }
}
