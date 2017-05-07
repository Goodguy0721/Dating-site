<?php

namespace Pg\Modules\Questions\Controllers;

/**
 * Admin questions controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Konstantin Rozhentsov
 *
 * @version $Revision: 1
 * */
class Admin_Questions extends \Controller
{
    /**
     * Controller
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('questions/models/Questions_model');
        $this->load->model('menu/models/Menu_model');
        $this->Menu_model->set_menu_active_item('admin_menu', 'add_ons_items');
    }

    public function index($page = 1)
    {
        $this->admin_questions($page);
    }

    public function settings()
    {
        $data    = $this->Questions_model->getSettings();
        $langs   = $this->pg_language->languages;
        $lang_id = $this->pg_language->current_lang_id;

        $this->Questions_model->validateDescription($langs);

        if ($this->input->post('btn_save')) {
            $post_data     = array(
                'is_active'                => $this->input->post('is_active', true),
                'allow_own_question'       => $this->input->post('allow_own_question', true),
                'action_for_communication' => $this->input->post('action_for_communication', true),
                'action_description'       => $this->input->post('action_description', true),
            );
            $validate_data = $this->Questions_model->validateSettings($post_data);
            if (!empty($validate_data["errors"])) {
                $this->system_messages->add_message('error', $validate_data['errors']);
            } else {
                $this->Questions_model->setSettings($validate_data["data"]);
                $this->system_messages->add_message('success', l('success_settings_save', 'questions'));
                $this->view->setRedirect(site_url() . "admin/questions/settings");
            }
        }

        $this->view->assign('data', $data);
        $this->view->assign('langs', $langs);
        $this->view->assign('current_lang_id', $lang_id);
        $this->view->setHeader(l('admin_header_settings_edit', 'questions'));
        $this->view->setBackLink(site_url() . "admin/start/menu/add_ons_items");
        $this->view->render('settings');

        $this->Menu_model->set_menu_active_item('admin_questions_menu', 'questions_settings_item');
    }

    public function admin_questions($page = 1)
    {
        $items_on_page = $this->pg_module->get_module_config('start', 'admin_items_per_page');
        $langs         = $this->pg_language->languages;
        $lang_id       = $this->pg_language->current_lang_id;
        $data          = $this->Questions_model->getQuestionsByUserId($lang_id, 0);
        $data          = array_slice($data, $items_on_page * ($page - 1), $items_on_page);
        $this->Menu_model->set_menu_active_item('admin_questions_menu', 'admin_questions_item');

        $questions_count = $this->Questions_model->get_count_admin_questions();

        $this->load->helper("navigation");
        $url                      = site_url() . "admin/questions/index/";
        $page_data                = get_admin_pages_data($url, $questions_count, $items_on_page, $page, 'briefPage');
        $page_data["date_format"] = $this->pg_date->get_format('date_time_literal', 'st');
        $this->view->assign('page_data', $page_data);

        $this->view->assign('questions', $data);
        $this->view->setHeader(l('admin_header_admin_questions_edit', 'questions'));
        $this->view->setBackLink(site_url() . "admin/start/menu/add_ons_items");
        $this->view->render('list');
    }

    public function users_questions($page = 1)
    {
        $items_on_page   = $this->pg_module->get_module_config('start', 'admin_items_per_page');
        $data            = $this->Questions_model->getUsersQuestions();
        $questions_count = 0;
        if (isset($data) && !empty($data)) {
            $data            = array_slice($data, $items_on_page * ($page - 1), $items_on_page);
            $questions_count = count($data);
        }

        $this->load->helper("navigation");
        $url                      = site_url() . "admin/questions/users_questions/";
        $page_data                = get_admin_pages_data($url, $questions_count, $items_on_page, $page, 'briefPage');
        $page_data["date_format"] = $this->pg_date->get_format('date_time_literal', 'st');
        $this->view->assign('page_data', $page_data);

        $this->view->assign('questions', $data);
        $this->view->assign('back', 'user');
        $this->view->setHeader(l('admin_header_users_questions_edit', 'questions'));
        $this->view->setBackLink(site_url() . "admin/start/menu/add_ons_items");
        $this->view->render('users_list');
        $this->Menu_model->set_menu_active_item('admin_questions_menu', 'users_questions_item');
    }

    public function ajax_activate_question()
    {
        $id               = $this->input->post('id', true);
        $status           = $this->input->post('status', true) ? '1' : '0';
        $this->Questions_model->save_question($id, array('status' => $status));
        $return['status'] = $status;
        $this->view->assign($return);

        return;
    }

    public function edit_question($id = 0, $back = 'admin')
    {
        $langs           = $this->pg_language->languages;
        $lang_id         = $this->pg_language->current_lang_id;
        $default_lang_id = $this->pg_language->get_default_lang_id();
        if ($id != 0) {
            $data = $this->Questions_model->getQuestionsById($id, $lang_id, $langs);
        } else {
            $data = array();
        }

        $fields = $this->input->post('field', true);

        if ($this->input->post('btn_save', true)) {
            foreach ($langs as $key => $value) {
                if (!empty($fields[$value['id']])) {
                    $params['name_' . $value['id']] = $fields[$value['id']];
                } else {
                    $params['name_' . $value['id']] = $fields[$default_lang_id];
                }
            }

            $validate_data = $this->Questions_model->validateQuestion($id, $params);
            if (!empty($validate_data['errors'])) {
                $this->system_messages->add_message('error', $validate_data['errors']);
            } else {
                $this->Questions_model->save_question($id, $validate_data['data']);

                $this->system_messages->add_message('success', l('success_question_save', 'questions'));

                redirect(site_url() . "admin/questions/admin_questions");
            }

            foreach ($params as $key => $value) {
                $data[substr($key, 5)] = $value;
            }
        }

        $this->view->assign('question', $data);
        $this->view->assign('langs', $langs);
        $this->view->assign('current_lang_id', $lang_id);
        $this->view->setHeader(l('link_edit_question', 'questions'));
        if ($back == 'user') {
            $this->view->setBackLink(site_url() . "admin/questions/users_questions");
            $this->view->assign('back_link', site_url() . "admin/questions/users_questions");
        } else {
            $this->view->setBackLink(site_url() . "admin/questions/admin_questions");
            $this->view->assign('back_link', site_url() . "admin/questions/admin_questions");
        }
        $this->view->render('edit');
    }

    public function delete_admin_question($id = array(0))
    {
        if ($this->input->post('delete_questions')) {
            $id = array_keys($this->input->post('delete_questions'));
        }

        $this->Questions_model->delete_admin_question($id);
        $this->system_messages->add_message('success', l('success_question_delete', 'questions'));

        redirect(site_url() . "admin/questions/admin_questions");
    }

    public function delete_user_question($id = array(0))
    {
        if ($this->input->post('delete_questions')) {
            $id = array_keys($this->input->post('delete_questions'));
        }

        $this->Questions_model->delete_users_question($id);
        $this->system_messages->add_message('success', l('success_question_delete', 'questions'));

        redirect(site_url() . "admin/questions/users_questions");
    }
}
