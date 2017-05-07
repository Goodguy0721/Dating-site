<?php

namespace Pg\Modules\Network\Controllers;

use Pg\Modules\Network\Models\Network_users_model;

/**
 * Network user side controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category    modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * */
class Network extends \Controller
{
    private $user_id;

    /**
     * Class constructor
     *
     * @return Network
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('network/models/Network_users_model');
        $this->user_id = (int) $this->session->userdata('user_id');
    }

    private function getContactUrl()
    {
        if ($this->pg_module->is_module_installed('tickets')) {
            return site_url() . 'tickets';
        } elseif ($this->pg_module->is_module_installed('contact_us')) {
            return site_url() . 'contact_us';
        } else {
            return;
        }
    }

    /**
     * Register user to network
     *
     * @return void
     */
    public function register()
    {
        if ($this->input->post('btn_agree')) {
            // Agree
            $this->Network_users_model->saveUserDecision(
                $this->user_id,
                Network_users_model::DECISION_AGREE);
            $this->view->setRedirect($this->input->post('redirect'), 'hard');
        } elseif ($this->input->post('btn_not_agree')) {
            // Not agree
            $this->Network_users_model->saveUserDecision(
                $this->user_id,
                Network_users_model::DECISION_DISAGREE);
            $this->Network_users_model->user_deleted(array('id' => $this->user_id));
            $this->Network_users_model->saveNotAgree($this->user_id);
            $this->view->setRedirect($this->input->post('redirect'), 'hard');
        } elseif ($this->input->post('btn_skip')) {
            $this->view->setRedirect(site_url() . 'tickets/index/', 'hard');
        } elseif (Network_users_model::DECISION_UNKNOWN !== $this->Network_users_model->getUserDecision($this->user_id)) {
            // Already answered
            if (SOCIAL_MODE) {
                $this->view->setRedirect(site_url() . 'start/homepage', 'hard');
            } else {
                $this->view->setRedirect(site_url() . 'users/search', 'hard');
            }
        } else {
            // Show form
            if (SOCIAL_MODE) {
                $this->view->assign('redirect', site_url() . 'start/homepage');
            } else {
                $this->view->assign('redirect', site_url() . 'users/search');
            }
            $this->view->assign('header_type', 'network');
            $this->view->assign('contact_url', $this->getContactUrl());
            $this->view->render('register');
        }
    }
    /**
     * Check network state
     *
     * @return void
     */
    public function cron()
    {
        $this->load->model('Network_model');
        $this->Network_model->cronCheckStarted();
    }
}
