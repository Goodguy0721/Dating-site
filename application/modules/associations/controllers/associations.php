<?php

namespace Pg\Modules\Associations\Controllers;

use Pg\Librarties\View;

/**
 * Associations controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Nikita Savanaev <nsavanaev@pilotgroup.net>
 * */
class Associations extends \Controller
{
    /**
     * Constructor
     *
     * @return Associations
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Associations_model');
        $this->load->model('Menu_model');
    }

    /**
     * Index
     *
     * @param string $page
     *
     * @return void
     */
    public function index($page = 1)
    {
        $settings = $this->Associations_model->getSettings();
        if (empty($settings['is_active'])) {
            show_404();
        }
        $user_id = intval($this->session->userdata('user_id'));

        $params = array();
        $params['where_sql'][] = 'id_profile = ' . $user_id . ' OR id_user = ' . $user_id . ' ';
        $page = intval($page);
        $this->load->helper('sort_order');
        $items_on_page = $this->pg_module->get_module_config('start', 'index_items_per_page');

        $associations_count = $this->Associations_model->getAssociationsUserCount($params);

        $this->load->helper('sort_order');
        $page = get_exists_page_number($page, $associations_count, $items_on_page);

        $order_array = array('id' => 'DESC');
        $lang_id = $this->pg_language->current_lang_id;

        if ($associations_count > 0) {
            $associations = $this->Associations_model->getAssociationsUser($page, $items_on_page, $order_array, $params, $lang_id);
            $this->view->assign('associations', $associations);
            $this->pg_seo->set_seo_data($associations);
        }

        $this->load->helper("navigation");
        $url = site_url() . "associations/index/";
        $page_data = get_user_pages_data($url, $associations_count, $items_on_page, $page, 'briefPage');
        $page_data["date_format"] = $this->pg_date->get_format('date_literal', 'st');
        $page_data["date_time_format"] = $this->pg_date->get_format('date_time_literal', 'st');

        $this->view->assign('page_data', $page_data);
        $this->Menu_model->breadcrumbs_set_active(l('header_main', 'associations'));
        $this->view->assign('settings', $settings);
        $this->view->assign('lang_id', $lang_id);
        $this->view->setHeader(l('header_main', 'associations'));
        $this->view->render('list');
    }

    /**
     *  Ajax template Associations
     *
     *  @return void
     */
    public function ajaxLoadAssociations()
    {
        return $this->loadAssociations();
    }

    /**
     *  Ajax list Associations
     *
     *  @return void
     */
    public function ajaxViewAssociations()
    {
        $post_data = array(
            'limits'     => intval($this->input->post('limits', true)),
            'profile_id' => intval($this->input->post('profile_id', true)),
            'last_id'    => intval($this->input->post('last_id', true)),
        );

        $result = $this->viewAssociations($post_data);
        $this->view->assign($result);
        $this->view->render();
    }

    /**
     *  Ajax compare
     *
     *  @return void
     */
    public function ajaxSetCompare()
    {
        $post_data = array(
            'association_id' => intval($this->input->post('association_id', true)),
            'profile_id'     => intval($this->input->post('profile_id', true)),
        );

        $this->setCompare($post_data);
    }

    /**
     *  Ajax answer
     *
     *  @return void
     */
    public function ajaxSetAnswer()
    {
        $post_data = array(
            'id'     => intval($this->input->post('association_id', true)),
            'answer' => trim(strip_tags($this->input->post('answer', true))),
        );

        $result = $this->setAnswer($post_data);
        $this->view->assign($result);
        $this->view->render();
    }

    /**
     *  Compare
     *
     *  @param array $data
     *
     *  @return void
     */
    private function setCompare($data = array())
    {
        $params['id'] = $data['association_id'];
        $association = $this->Associations_model->getImage($params);
        if (!empty($association)) {
            $validate_data = $this->Associations_model->validateAssociationUser($data['profile_id'], $association);
            if (!empty($validate_data["errors"])) {
                $result["error"] = $validate_data["errors"];
            } else {
                $result = $this->Associations_model->saveAssociationUser(null, $validate_data['data']);
                $this->sendingMessage($validate_data['data']);
            }
        } else {
            $result = array('error' => l('error_no_association', 'associations'));
        }
        $this->view->assign($result);
        $this->view->render();
    }

    /**
     *  View list associations
     *
     *  @param array $data
     *
     *  @return void
     */
    private function viewAssociations($data = array())
    {
        $lang_id = $this->pg_language->current_lang_id;
        $user_data = $this->getUserData($data['profile_id']);
        $order = array('id' => 'ASC');
        $params = array();
        $params['where']['is_active'] = 1;
        $params['limit']['count'] = $data['limits'];
        $params['limit']['from'] = $data['last_id'];
        $associations = array(
            'profile_id' => $data['profile_id'],
            'images'     => $this->Associations_model->getListImages(null, null, $order, $params, $lang_id, $user_data),
        );
        $this->view->assign($associations);

        return;
    }

    /**
     *  User profile info
     *
     *  @param integer $profile_id
     *
     *  @return void
     */
    private function getUserData($profile_id)
    {
        $this->load->model('Users_model');
        $profile = $this->Users_model->get_user_by_id($profile_id);
        $user_data = array($profile['nickname'], $profile['fname'], $profile['sname']);

        return $user_data;
    }

    /**
     *  Associations template
     *
     *  @return void
     */
    private function loadAssociations()
    {
        $this->view->render('ajax_associations');
    }

    /**
     *  Save answer
     *
     *  @param array $data
     *
     *  @return void
     */
    private function setAnswer($data = array())
    {
        if (!empty($data)) {
            $validate_data = $this->Associations_model->validateAnswer($data);
            if (!empty($validate_data["errors"])) {
                $this->view->assign($validate_data);
            } else {
                $result = $this->Associations_model->saveAssociationUser($data['id'], $validate_data['data']);
                $this->view->assign($result);
            }

            return;
        }
    }

    /**
     * Send message
     *
     * @param array $data
     *
     * @return boolean
     */
    private function sendingMessage($data = array())
    {
        $this->load->model('Users_model');
        $this->load->model('Notifications_model');
        $profile = $this->Users_model->get_user_by_id($data['id_profile']);
        $association = $this->Associations_model->formatTextAssociation($data['name_' . $profile['lang_id']], array($profile['nickname'], $profile['fname'], $profile['sname']));
        $alert = array(
            'user_nickname'    => $this->session->userdata('output_name'),
            'profile_nickname' => $profile['fname'] . " " . $profile['sname'],
            'association'      => $association,
            'link'             => site_url() . 'associations/index',
        );
        $this->Notifications_model->send_notification($profile['email'], 'association', $alert, '', $profile['lang_id']);

        return false;
    }
}
