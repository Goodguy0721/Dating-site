<?php

namespace Pg\Modules\Questions\Models;

/**
 * Questions main model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Konstantin Rozhentsov
 **/
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
if (!defined('TABLE_QUESTIONS')) {
    define('TABLE_QUESTIONS', DB_PREFIX . 'questions');
}
if (!defined('TABLE_QUESTIONS_ANSWERS')) {
    define('TABLE_QUESTIONS_ANSWERS', DB_PREFIX . 'questions_answers');
}

class Questions_model extends \Model
{
    const DB_DATE_FORMAT = 'Y-m-d H:i:s';
    const DB_DATE_FORMAT_SEARCH = 'Y-m-d H:i';
    const DB_DEFAULT_DATE = '0000-00-00 00:00:00';

    protected $CI;
    protected $DB;

    public $items_on_page = 5;

    private $modules_category = array(
        'communication',
        'action',
    );

    private $_fields_user_questions = array(
        'id',
        'id_user',
        'id_user_to',
        'name',
        'answer',
        'is_new',
        'date_created',
    );

    /**
     * Moderation type
     *
     * @var string
     */
    private $moderation_type = 'questions';

    /**
     *  Constructor
     *
     *  @return Questions_model
     */
    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
        $this->DB = &$this->CI->db;
    }

    /**
     *  Language users table field
     *
     *  @param integer $lang_id
     *
     *  @return void
     */
    private function _fields_users_all($lang_id = null)
    {
        $fields = array();
        if (is_null($lang_id)) {
            $lang_ids = $this->CI->pg_language->languages;
            foreach ($lang_ids as $lang) {
                $fields[] = 'name_' . $lang['id'];
            }
        } else {
            $default_lang_id = $this->CI->pg_language->current_lang_id;
            $fields[] = 'name_' . $default_lang_id;
        }

        return $fields;
    }

    /**
     *  Settings
     *
     *  @return array
     */
    public function getSettings()
    {
        $data = array(
            'is_active'                => $this->CI->pg_module->get_module_config('questions', 'is_active'),
            'allow_own_question'       => $this->CI->pg_module->get_module_config('questions', 'allow_own_question'),
            'action_for_communication' => $this->CI->pg_module->get_module_config('questions', 'action_for_communication'),
        );

        $_data = array();
        foreach ($this->pg_language->languages as $key => $value) {
            $_data[$value['id']] = $this->CI->pg_module->get_module_config('questions', 'action_description_' . $value['id']);
        }
        $data['action_description'] = $_data;

        return $this->_formatSettings($data);
    }

    /**
     *	Questions
     *
     *	@return array
     */
    public function getQuestionsByUserId($lang_id = 1, $user = 0)
    {
        if ($user === 'all') {
            $this->DB->select('id, name_' . $lang_id);
            $this->DB->from(TABLE_QUESTIONS);
            $this->DB->where("status !=", 0);
            $result = $this->DB->get()->result_array();

            if (!empty($result)) {
                foreach ($result as $key => $value) {
                    $data[] = array(
                        "name"        => $result[$key]["name_" . $lang_id],
                        "id"          => $result[$key]["id"],
                    );
                }

                return $data;
            }
        } else {
            $this->DB->select("id, status, name_" . $lang_id);
            $this->DB->from(TABLE_QUESTIONS);
            $this->DB->where("id_user", $user);
            $this->DB->order_by("id DESC");
            $result = $this->DB->get()->result_array();

            if (!empty($result)) {
                foreach ($result as $key => $value) {
                    $data[] = array(
                        "name"        => $result[$key]["name_" . $lang_id],
                        "id"          => $result[$key]["id"],
                        "status"      => $result[$key]["status"],
                    );
                }

                return $data;
            }
        }

        return 0;
    }

    /**
     *	Questions
     *
     *	@return array
     */
    public function getNotificationsCount($user_id = 0)
    {
        $new_questions = $this->getNewUserQuestions($user_id);
        $new_answers = $this->getNewUserAnswers($user_id);
        
        return $new_questions + $new_answers;
    }
    
    public function updateNotificationsCount($id = null) {
        if($id) {
            $data = array(
                'is_new' => 0
            );
            $this->db->where('id', $id);
            $this->db->update(TABLE_QUESTIONS_ANSWERS, $data); 
        }
    }
    
    public function getNewUserQuestions($user_id = 0) {
        $this->DB->select("count(id) as count");
        $this->DB->from(TABLE_QUESTIONS_ANSWERS);
        $this->DB->where('is_new', 1);
        $this->DB->where('id_user_to', $user_id);
        $this->DB->where('answer', '');    
        $result = $this->DB->get()->result_array();
        if (!empty($result)) {
            return $result[0]['count'];
        }
        return 0;
    }
    
    public function getNewUserAnswers($user_id = 0) {
        $this->DB->select("count(id) as count");
        $this->DB->from(TABLE_QUESTIONS_ANSWERS);
        $this->DB->where('is_new', 1);
        $this->DB->where('id_user', $user_id);
        $this->DB->where('answer !=', '');    
        $result = $this->DB->get()->result_array();
        if (!empty($result)) {
            return $result[0]['count'];
        }

        return 0;        
    }

    public function getUsersQuestions()
    {
        $this->DB->select('t1.id, t1.name, t1.own_question, t2.nickname as user_from, t3.nickname as user_to');
        $this->DB->from(TABLE_QUESTIONS_ANSWERS . " AS t1");
        $this->DB->join(USERS_TABLE . " AS t2", "t1.id_user = t2.id");
        $this->DB->join(USERS_TABLE . " AS t3", "t1.id_user_to = t3.id");
        $this->DB->where('t1.own_question', '1');
        $this->DB->order_by('t1.id DESC');
        $result = $this->DB->get()->result_array();

        if (!empty($result)) {
            foreach ($result as $key => $value) {
                $data[] = array(
                    "name"             => $result[$key]["name"],
                    "id"               => $result[$key]["id"],
                    "user_to"          => $result[$key]["user_to"],
                    "user_from"        => $result[$key]["user_from"],
                );
            }

            return $data;
        }

        return false;
    }

    public function getQuestionsById($id = 0, $lang_id = 1, $langs = array())
    {
        if (!empty($langs)) {
            foreach ($langs as $value => $key) {
                $fields[] = "name_" . $key['id'];
            }
        } else {
            return;
        }

        $this->DB->select(implode(", ", $fields));
        $this->DB->from(TABLE_QUESTIONS);
        $this->DB->where("id", $id);
        $result = $this->DB->get()->result_array();

        if (!empty($result)) {
            foreach ($langs as $value => $key) {
                $question[$key['id']] = $result[0]['name_' . $key['id']];
            }
        };

        return $question;
    }

    public function getCompared($user_to)
    {
        $user_id = $this->CI->session->userdata("user_id");
        if ($user_to) {
            $this->DB->select("id, answer");
            $this->DB->from(TABLE_QUESTIONS_ANSWERS);
            $this->DB->where("id_user", $user_id);
            $this->DB->where("id_user_to", $user_to);
            $this->DB->order_by("id DESC");
            $result = $this->DB->get()->result_array();
            if (!empty($result) && !$result[0]['answer'] && $result[0]['id']) {
                return 1;
            }
        }

        return false;
    }

    public function save_question($id = 0, $params = array())
    {
        if ($id == 0) {
            $this->DB->insert(TABLE_QUESTIONS, $params);
        } else {
            $this->DB->where('id', $id);
            $this->DB->update(TABLE_QUESTIONS, $params);
        }

        return;
    }

    public function save_answer($id, $params = array())
    {
        if ($id) {
            if ($params['answer'] == '') {
                $return['error'] = l('error_answer_save', 'questions');

                return $return;
            }

            $this->DB->where('id', $id);
            $this->DB->update(TABLE_QUESTIONS_ANSWERS, $params);

            $this->CI->load->model('menu/models/Indicators_model');
            $this->CI->Indicators_model->delete('new_questions_item', array($id), false);

            $this->DB->select("id_user, id_user_to, name, answer");
            $this->DB->from(TABLE_QUESTIONS_ANSWERS);
            $this->DB->where("id", $id);
            $result = $this->DB->get()->result_array();

            $this->load->model('Users_model');
            $recipient = $this->Users_model->get_user_by_id($result[0]['id_user']);
            $sender = $this->Users_model->get_user_by_id($result[0]['id_user_to']);
            $mail_data = array(
                'sender_name'         => $sender['nickname'],
                'recipient_name'      => $recipient['nickname'],
                'question'            => $result[0]['name'],
                'answer'              => $result[0]['answer'],
                'link'                => site_url() . 'questions/index',
            );

            $this->load->model('Notifications_model');
            $this->CI->Notifications_model->send_notification($recipient['email'], 'questions_answer', $mail_data, '');

            $return['success'] = l('success_answer_save', 'questions');

            return $return;
        }

        $return['error'] = l('error_answer_save', 'questions');

        return $return;
    }

    public function save_user_question($id_user, $id_user_to, $data, $lang_id)
    {
        if ($data['question'] != 0) {
            $this->DB->select("name_" . $lang_id);
            $this->DB->from(TABLE_QUESTIONS);
            $this->DB->where("id", $data['question']);

            $result = $this->DB->get()->result_array();
            $question = $result[0]["name_" . $lang_id];
            $own_question = 0;
        } else {
            $question = $data['message'];
            $own_question = 1;
        }

        if ($question == '') {
            $return['error'][] = l('empty_question', 'questions');

            return $return;
        }

        $this->CI->load->model('moderation/models/Moderation_badwords_model');
        $bw_count = $this->CI->Moderation_badwords_model->check_badwords($this->moderation_type, $question);
        if ($bw_count) {
            $return['error'][] = l('error_badwords_question', 'questions');

            return $return;
        }

        $attrs = array(
            'id_user'         => $id_user,
            'id_user_to'      => $id_user_to,
            'name'            => $question,
            'own_question'    => $own_question,
        );
        $this->DB->insert(TABLE_QUESTIONS_ANSWERS, $attrs);
        $id = $attrs['id_answers_table'] = $this->DB->insert_id();

        $this->load->model('Users_model');
        $recipient = $this->Users_model->get_user_by_id($id_user_to);
        $sender = $this->Users_model->get_user_by_id($id_user);
        $mail_data = array(
            'sender_name'    => $sender['nickname'],
            'recipient_name' => $recipient['nickname'],
            'question'       => $question,
            'link'           => site_url() . 'questions/index',
        );

        $this->load->model('Notifications_model');
        $this->CI->Notifications_model->send_notification($recipient['email'], 'questions_new_question', $mail_data, '');

        return $id;
    }

    public function set_indicator($id_user, $uid)
    {
        $this->CI->load->model('menu/models/Indicators_model');
        $this->CI->Indicators_model->add('new_questions_item', $uid, $id_user);

        return;
    }

    public function get_count_admin_questions($params = array())
    {
        if (isset($params['where']) && is_array($params['where']) && count($params['where'])) {
            foreach ($params['where'] as $field => $value) {
                $this->DB->where($field, $value);
            }
        }

        if (!empty($params['or_where']) && is_array($params['or_where']) && count($params['or_where'])) {
            foreach ($params['or_where'] as $field => $value) {
                $this->DB->or_where($field, $value);
            }
        }

        return $this->DB->count_all_results(TABLE_QUESTIONS);
    }

    public function get_count_questions_users($params = array())
    {
        if (isset($params['where']) && is_array($params['where']) && count($params['where'])) {
            foreach ($params['where'] as $field => $value) {
                $this->DB->where($field, $value);
            }
        }

        if (!empty($params['or_where']) && is_array($params['or_where']) && count($params['or_where'])) {
            foreach ($params['or_where'] as $field => $value) {
                $this->DB->or_where($field, $value);
            }
        }

        return $this->DB->count_all_results(TABLE_QUESTIONS_ANSWERS);
    }

    public function get_count_questions_answers($params = array())
    {
        if (isset($params['where']) && is_array($params['where']) && count($params['where'])) {
            foreach ($params['where'] as $field => $value) {
                $this->DB->where($field, $value);
            }
        }

        return $this->DB->count_all_results(TABLE_QUESTIONS_ANSWERS);
    }

    public function delete_admin_question($id = array(0))
    {
        $this->DB->where_in('id', $id);
        $this->DB->delete(TABLE_QUESTIONS);

        return;
    }

    public function delete_users_question($id = array(0))
    {
        $this->DB->where_in('id', $id);
        $this->DB->delete(TABLE_QUESTIONS_ANSWERS);

        $this->CI->load->model('menu/models/Indicators_model');
        $this->CI->Indicators_model->delete('new_questions_item', $id, true);

        return;
    }

    /**
     *  Validate settings
     *
     *  @param array $data
     *
     *  @return array
     */
    public function validateSettings($data)
    {
        $return = array("errors" => array(), "data" => array());

        if (isset($data['is_active'])) {
            $return["data"]["is_active"] = intval($data["is_active"]);
        }

        if (isset($data['allow_own_question'])) {
            $return["data"]["allow_own_question"] = intval($data["allow_own_question"]);
        }

        if (isset($data['action_for_communication'])) {
            $return["data"]["action_for_communication"] = trim(strip_tags($data["action_for_communication"]));
        }

        if (isset($data['action_description'])) {
            foreach ($this->CI->pg_language->languages as $key => $value) {
                if (!empty($data['action_description'][$value['id']])) {
                    $return['data']['action_description_' . $value['id']] = strip_tags($data['action_description'][$value['id']]);
                } else {
                    $return['data']['action_description_' . $value['id']] = "";
                }
            }
        }

        return $return;
    }

    public function get_user_questions($params = array(), $page = 1, $items_on_page = 20, $order_by = null)
    {
        $this->DB->select(implode(', ', $this->_fields_user_questions))->from(TABLE_QUESTIONS_ANSWERS);

        if (!empty($params['where']) && is_array($params['where']) && count($params['where'])) {
            foreach ($params['where'] as $field => $value) {
                $this->DB->where($field, $value);
            }
        }

        if (!empty($params['or_where']) && is_array($params['or_where']) && count($params['or_where'])) {
            foreach ($params['or_where'] as $field => $value) {
                $this->DB->or_where($field, $value);
            }
        }

        if (is_array($order_by) && count($order_by) > 0) {
            foreach ($order_by as $field => $dir) {
                if (in_array($field, $this->_fields_user_questions)) {
                    $this->DB->order_by($field . " " . $dir);
                }
            }
        }

        $page = intval($page);
        if (!empty($page)) {
            $this->DB->limit($items_on_page, $items_on_page * ($page - 1));
        }
        $results = $this->DB->get()->result_array();

        return $results;
    }

    /**
     *  Include module
     *
     *  @param integer $select
     *
     *  @return array
     */
    public function getActionModules($select = null)
    {
        $return = array();
        $data = ($this->CI->session->userdata("auth_type") == "user") ? array($select) : $this->_getModules();

        foreach ($data as $value) {
            if ($value == 'questions') {
                continue;
            }
            $this->CI->load->model($value . '/models/' . ucfirst($value . '_model'));
            if (method_exists($this->CI->{ucfirst($value . '_model')}, 'moduleCategoryAction')) {
                $return[$value] = $this->CI->{ucfirst($value . '_model')}->moduleCategoryAction();
                $return[$value]['selected'] = ($select == $value) ? 1 : 0;
            }
        }

        return $return;
    }

    /**
     *  Load modules
     *
     *  @return array
     */
    private function _getModules()
    {
        $result = array();
        $modules = $this->CI->pg_module->get_modules();

        foreach ($modules as $module) {
            if (in_array($module['category'], $this->modules_category)) {
                $result[] = $module['module_gid'];
            }
        }

        return $result;
    }

    /**
     *  Format settings
     *
     *  @param array $data
     *
     *  @return array
     */
    private function _formatSettings($data = array())
    {
        $data['is_active'] = intval($data['is_active']);
        $data['action_for_communication'] = $this->getActionModules($data['action_for_communication']);

        return $data;
    }

    public function validateDescription($langs)
    {
        foreach ($langs as $key => $value) {
            $this->DB->select("id");
            $this->DB->from(MODULES_CONFIG_TABLE);
            $this->DB->where("module_gid", "questions");
            $this->DB->where("config_gid", "action_description_" . $value['id']);
            $result = $this->DB->get()->result_array();

            if (empty($result)) {
                $this->CI->pg_module->set_module_config('questions', "action_description_" . $value['id'], "");
            }
        }
    }

    /**
     *  Save settings
     *
     *  @param array $data
     *
     *  @return void
     */
    public function setSettings($data)
    {
        foreach ($data as $setting => $value) {
            $this->CI->pg_module->set_module_config('questions', $setting, $value);
        }

        return;
    }

    /**
     *  Banner pages
     *
     *  @return array
     */
    public function bannerAvailablePages()
    {
        $return[] = array("link" => "questions/index", "name" => l('header_main', 'questions'));

        return $return;
    }

    /**
     *  Callback languages add
     *
     *  @param integer $lang_id
     *
     *  @return void
     */
    public function langDedicateModuleCallbackAdd($lang_id = false)
    {
        if (!$lang_id) {
            return;
        }

        $this->CI->load->dbforge();

        $fields_n = array();

        $fields_n['name_' . $lang_id] = array('type' => 'VARCHAR', 'constraint' => '255', 'null' => false);
        $this->CI->dbforge->add_column(TABLE_QUESTIONS, $fields_n);

        $default_lang_id = $this->CI->pg_language->get_default_lang_id();
        if ($lang_id != $default_lang_id) {
            $this->CI->db->set('name_' . $lang_id, 'name_' . $default_lang_id, false);
            $this->CI->db->update(TABLE_QUESTIONS);
        }
    }

    /**
     *  Callback languages delete
     *
     *  @param integer $lang_id
     *
     *  @return void
     */
    public function langDedicateModuleCallbackDelete($lang_id = false)
    {
        if (!$lang_id) {
            return;
        }

        $this->CI->load->dbforge();

        $table_query = $this->CI->db->get(TABLE_QUESTIONS);
        $fields_exists = $table_query->list_fields();

        $fields = array('name_' . $lang_id);
        foreach ($fields as $field_name) {
            if (!in_array($field_name, $fields_exists)) {
                continue;
            }
            $this->CI->dbforge->drop_column(TABLE_QUESTIONS, $field_name);
        }
    }

    /**
     *  Module category action
     *
     *  @return array
     */
    public function moduleCategoryAction()
    {
        $action = array(
            'name'   => l('link_add', 'questions'),
            'helper' => 'questions_list',
        );

        return $action;
    }

    /**
     *  Seo settings
     *
     *  @param string $method
     *  @param integer $lang_id
     *
     *  @return void
     */
    public function getSeoSettings($method = '', $lang_id = '')
    {
        if (!empty($method)) {
            return $this->_getSeoSettings($method, $lang_id);
        } else {
            $actions = array('index');
            $return = array();
            foreach ($actions as $action) {
                $return[$action] = $this->_getSeoSettings($action, $lang_id);
            }

            return $return;
        }
    }

    /**
     *  Seo settings
     *
     *  @param string $method
     *  @param integer $lang_id
     *
     *  @return array
     */
    private function _getSeoSettings($method, $lang_id = '')
    {
        switch ($method) {
            case 'index':
                return array(
                    "templates" => array(),
                    "url_vars"  => array(),
                );
                break;
        }
    }

    /**
     *  Seo rewrite
     *
     *  @param string $var_name_from
     *  @param string $var_name_to
     *  @param string $value
     *
     *  @return string
     */
    public function requestSeoRewrite($var_name_from, $var_name_to, $value)
    {
        if ($var_name_from == $var_name_to) {
            return $value;
        }

        return $value;
    }

    /**
     *  Site map xml
     *
     *  @return array
     */
    public function getSitemapXmlUrls()
    {
        $this->CI->load->helper('seo');
        $return = array();

        return $return;
    }

    /**
     *  Site map url
     *
     *  @return array
     */
    public function getSitemapUrls()
    {
        $this->CI->load->helper('seo');
        $auth = $this->CI->session->userdata("auth_type");
        $block = array();

        $block[] = array(
            "name"      => l('header_main_sections', 'questions'),
            "link"      => rewrite_link('questions', 'index'),
            "clickable" => ($auth == "user"),
            "items"     => array(),
        );

        return $block;
    }

    public function backend_getNotifications()
    {
        $user_id = $this->CI->session->userdata('user_id');

        $result['new_questions'] = $this->getNotificationsCount($user_id);

        return $result;
    }

    /**
     *  Validate question
     *
     *  @param integer $question_id
     *  @param array $data
     *
     *  @return void
     */
    public function validateQuestion($question_id = null, $question_data = array())
    {
        $return = array("errors" => array(), "data" => array());

        $default_lang_id = $this->CI->pg_language->current_lang_id;
        $languages = $this->CI->pg_language->languages;

        if (isset($question_data['name_' . $default_lang_id])) {
            $return['data']['name_' . $default_lang_id] = trim($question_data['name_' . $default_lang_id]);
            if (empty($return['data']['name_' . $default_lang_id])) {
                $return['errors'][] = l('empty_question', 'questions');
            } else {
                foreach ($languages as $lid => $lang_data) {
                    if ($lid == $default_lang_id) {
                        continue;
                    }
                    if (!isset($question_data['name_' . $lid]) || empty($question_data['name_' . $lid])) {
                        $return['data']['name_' . $lid] = $return['data']['name_' . $default_lang_id];
                    } else {
                        $return['data']['name_' . $lid] = trim($question_data['name_' . $lid]);
                    }
                }
            }
        }

        return $return;
    }
}
