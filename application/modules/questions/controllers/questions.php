<?php

namespace Pg\Modules\Questions\Controllers;

/**
 * Questions controller
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
 **/
class Questions extends \Controller
{
    /**
     * Controller
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('questions/models/Questions_model');
        $this->load->model('uploads/models/Uploads_model');
        $this->load->model('Menu_model');
    }

    public function index($page = 1)
    {
        $user_id = $this->session->userdata('user_id');
        $this->view->assign('logged_user_id', $user_id);

        /// breadcrumbs
        $this->Menu_model->breadcrumbs_set_parent('questions_item');

        $params['where']['id_user_to'] = $user_id;
        $params['or_where']['id_user'] = $user_id;

        $items_on_page = $this->pg_module->get_module_config('start', 'index_items_per_page');

        $order_by = array('date_created' => 'DESC');

        $questions = $this->Questions_model->get_user_questions($params, $page, $items_on_page, $order_by);

        $upload_config_id = $this->Users_model->upload_config_id;

        $i = 0;
        foreach ($questions as $row) {
            $date = date_create_from_format('Y-m-d H:i:s', $row['date_created']);
            $questions[$i]["date_created"] = date_format($date, 'D, M d');

            $user = $row["id_user"];
            $userinfo = $this->Users_model->get_user_by_id($user);

            if (!empty($userinfo['fname']) || (!empty($userinfo['sname']))) {
                $tmp_name = $userinfo['fname'] . " " . $userinfo['sname'];
            } else {
                $tmp_name = $userinfo['nickname'];
            }
            $questions[$i]["user_info"] = $tmp_name;

            $questions[$i]["name"] = nl2br($questions[$i]["name"]);
            $questions[$i]["answer"] = nl2br($questions[$i]["answer"]);
            
            if($user == $user_id && $questions[$i]["answer"] && $row['is_new']) {
                $this->Questions_model->updateNotificationsCount($row['id']);
            }
		
            if (!empty($userinfo["user_logo"])) {
                $userinfo["user_logo"] = $this->Uploads_model->format_upload($upload_config_id, $user, $userinfo["user_logo"]);
                $questions[$i]["user_logo"] = $userinfo["user_logo"]['thumbs'];
            } else {
                $userinfo["user_logo"] = $this->Uploads_model->format_default_upload($upload_config_id);
                $questions[$i]["user_logo"] = $userinfo["user_logo"]['thumbs'];
            }

            $user_to = $row["id_user_to"];
            $userinfo_to = $this->Users_model->get_user_by_id($user_to);

            if (!empty($userinfo_to['fname']) || (!empty($userinfo_to['sname']))) {
                $tmp_name = $userinfo_to['fname'] . " " . $userinfo_to['sname'];
            } else {
                $tmp_name = $userinfo_to['nickname'];
            }
            $questions[$i]["user_info_to"] = $tmp_name;

            if (!empty($userinfo_to["user_logo"])) {
                $userinfo_to["user_logo"] = $this->Uploads_model->format_upload($upload_config_id, $user_to, $userinfo_to["user_logo"]);
                $questions[$i]["user_logo_to"] = $userinfo_to["user_logo"]['thumbs'];
            } else {
                $userinfo_to["user_logo"] = $this->Uploads_model->format_default_upload($upload_config_id);
                $questions[$i]["user_logo_to"] = $userinfo_to["user_logo"]['thumbs'];
            }

            ++$i;
        }

        $questions_count = $this->Questions_model->get_count_questions_users($params);
        $data = $this->Questions_model->getSettings();
        foreach ($data['action_for_communication'] as $key => $value) {
            if ($value['selected'] == 1) {
                $action = $key;
                $helper_func = $value['helper'];
            }
        }

        $lang_id = $this->pg_language->current_lang_id;
        $action_description = $data['action_description'][$lang_id];

        $this->load->helper("navigation");
        $url = site_url() . 'questions/index/';
        $page_data = get_user_pages_data($url, $questions_count, $items_on_page, $page, 'briefPage');
        $this->config->load('date_formats', true);
        $page_data["date_format"] = $this->pg_date->get_format('date_literal', 'st');

        $this->view->assign('action_module', $action);
        $this->view->assign('action_descr', $action_description);
        $this->view->assign('helper_func', $helper_func);
        $this->view->assign('page', $page);
        $this->view->assign('page_data', $page_data);
        $this->view->assign('questions', $questions);
        $this->view->render('questions');
    }

    public function ajax_get_questions($object_id = null, $page = 1)
    {
        $lang_id = $this->pg_language->current_lang_id;
        $questions = $this->Questions_model->getQuestionsByUserId($lang_id, 'all');
        $settings = $this->Questions_model->getSettings();

        if (is_array($questions)) {
            $questions_count = count($questions);
            $this->view->assign('questions_count', $questions_count);
            shuffle($questions);
            $questions = array_slice($questions, 0, 5);
        } else {
            $this->view->assign('no_questions', 1);
        }

        $this->view->assign('allow_users_question', $settings['allow_own_question']);
        $this->view->assign('questions', $questions);

        $this->view->render('list');
    }

    public function ajax_refresh_questions($object_id = null)
    {
        $lang_id = $this->pg_language->current_lang_id;
        $questions = $this->Questions_model->getQuestionsByUserId($lang_id, 'all');
        $settings = $this->Questions_model->getSettings();

        shuffle($questions);
        $questions = array_slice($questions, 0, 5);
        $data['items'] = $questions;
        $this->view->assign($data);
    }

    public function ajax_set_questions($id_user_to)
    {
        $return = array();

        $lang_id = $this->pg_language->current_lang_id;
        $user_id = $this->session->userdata("user_id");
        $post_data['question'] = $this->input->post("question", true);
        if ($post_data['question'] == 0) {
            $post_data['message'] = trim(strip_tags($this->input->post("message", true)));
        }

        $uid = $return = $this->Questions_model->save_user_question($user_id, $id_user_to, $post_data, $lang_id);
//        if (!is_array($return) || empty($return['error'])) {
//            $this->Questions_model->set_indicator($id_user_to, $uid);
//        }

        if (!is_array($return) || empty($return['error'])) {
            unset($return);
            $return['success'] = l('success_question_sent', 'questions');
        }

        $this->view->assign($return);
    }

    public function ajax_save_answer()
    {
        if ($this->input->post('question_id', true)) {
            $question_id = intval($this->input->post('question_id', true));
            $params['answer'] = trim(strip_tags($this->input->post('answer', true)));

            $return = $this->Questions_model->save_answer($question_id, $params);
            $return['answer'] = nl2br($params['answer']);
        }

        $this->view->assign($return);
    }

    public function delete_question($id, $page)
    {
        $page = intval($page);

        if (intval($id)) {
            $this->Questions_model->delete_users_question($id);
        }

        //calculate questions count
        $user_id = $this->session->userdata('user_id');
        $params['where']['id_user_to'] = $user_id;
        $params['or_where']['id_user'] = $user_id;
        $questions_count = $this->Questions_model->get_count_questions_users($params);
        $items_on_page = $this->Questions_model->items_on_page;
        if (isset($page) && $page > 1 && $items_on_page * ($page - 1) >= $questions_count) {
            $page = $page - 1;
        }

        redirect(site_url() . "questions/index/" . $page);
    }
}
