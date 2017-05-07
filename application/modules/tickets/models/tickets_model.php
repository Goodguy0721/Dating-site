<?php

/**
 * Tickets main model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Nikita Savanaev <nsavanev@pilotgroup.net>
 **/
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

define('TICKET_REASONS_TABLE', DB_PREFIX . 'tickets');
define('TICKET_MESSAGES_TABLE', DB_PREFIX . 'tickets_messages');
define('TICKET_USERS_TABLE', DB_PREFIX . 'tickets_users');

class Tickets_model extends Model
{
    private $CI;
    private $DB;

    private $fields = array(
        'id',
        'mails',
        'date_add',
    );

    private $fields_messages = array(
        'id',
        'id_user',
        'is_admin_sender',
        'message',
        'is_new',
        'date_created',
    );

    private $fields_tickets = array(
        'id',
        'id_user',
        'admin_new',
        'admin_last',
        'message',
        'date_created',
        'is_new',
    );

    private $moderation_type = 'tickets';
    
    public $indicator_type = 'new_ticket_item';
    public $messages_max_count_header = 3;

    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
        $this->DB = &$this->CI->db;
    }

    public function get_reason_by_id($id)
    {
        $result = $this->DB->select(implode(", ", $this->fields))->from(TICKET_REASONS_TABLE)->where("id", $id)->get()->result_array();
        if (!empty($result)) {
            $result = $this->format_reasons($result);
            $data = $result[0];

            return $data;
        }

        return array();
    }

    public function get_responder_list($page = null, $items_on_page = null, $order_by = null, $params = array(), $filter_object_ids = null)
    {
        $this->DB->select(implode(", ", $this->fields_tickets));
        $this->DB->from(TICKET_USERS_TABLE);

        if (isset($params["where"]) && is_array($params["where"]) && count($params["where"])) {
            foreach ($params["where"] as $field => $value) {
                $this->DB->where($field, $value);
            }
        }

        if (isset($params["where_in"]) && is_array($params["where_in"]) && count($params["where_in"])) {
            foreach ($params["where_in"] as $field => $value) {
                $this->DB->where_in($field, $value);
            }
        }

        if (isset($params["where_sql"]) && is_array($params["where_sql"]) && count($params["where_sql"])) {
            foreach ($params["where_sql"] as $value) {
                $this->DB->where($value);
            }
        }

        if (isset($filter_object_ids) && is_array($filter_object_ids) && count($filter_object_ids)) {
            $this->DB->where_in("id_user", $filter_object_ids);
        }

        if (is_array($order_by) && count($order_by) > 0) {
            foreach ($order_by as $field => $dir) {
                if (in_array($field, $this->fields_tickets)) {
                    $this->DB->order_by($field . " " . $dir);
                }
            }
        }

        if (!is_null($page)) {
            $page = intval($page) ? intval($page) : 1;
            $this->DB->limit($items_on_page, $items_on_page * ($page - 1));
        }

        $results = $this->DB->get()->result_array();
        if (!empty($results) && is_array($results)) {
            foreach ($results as $r) {
                $data[] = $r;
            }

            return $this->format_responder($data);
        }

        return array();
    }

    public function get_messages($params = array(), $page = null, $items_on_page = null, $count_items_on_page = null, $order_by = null)
    {
        $this->DB->select(implode(", ", $this->fields_messages));
        $this->DB->from(TICKET_MESSAGES_TABLE);

        if (isset($params["where"]) && is_array($params["where"]) && count($params["where"])) {
            foreach ($params["where"] as $field => $value) {
                $this->DB->where($field, $value);
            }
        }

        if (is_array($order_by) && count($order_by) > 0) {
            foreach ($order_by as $field => $dir) {
                if (in_array($field, $this->fields_messages)) {
                    $this->DB->order_by($field . " " . $dir);
                }
            }
        }

        if (!is_null($page) && is_null($count_items_on_page)) {
            $page = intval($page) ? intval($page) : 1;
            $this->DB->limit($items_on_page, $items_on_page * ($page - 1));
        } elseif (!is_null($count_items_on_page)) {
            $this->DB->limit($items_on_page, $count_items_on_page);
        }

        $results = $this->DB->get()->result_array();
        if (!empty($results) && is_array($results)) {
            foreach ($results as $r) {
                $data[] = $r;
            }

            return $this->format_messages($data);
        }

        return array();
    }
    public function get_count_new_messages($id_user, $is_admin = 0)
    {
        $this->DB->select("COUNT(*) AS cnt");
        $this->DB->from(TICKET_MESSAGES_TABLE);
        $this->DB->where('id_user', $id_user);
        $this->DB->where('is_new', '1');
        $this->DB->where('is_admin_sender', $is_admin);
        $results = $this->DB->get()->result_array();
        if (!empty($results) && is_array($results)) {
            return intval($results[0]["cnt"]);
        }

        return 0;
    }

    public function get_count_messages($id_user)
    {
        $this->DB->select("COUNT(*) AS cnt");
        $this->DB->from(TICKET_MESSAGES_TABLE);
        $this->DB->where('id_user', $id_user);
        $results = $this->DB->get()->result_array();
        if (!empty($results) && is_array($results)) {
            return intval($results[0]["cnt"]);
        }

        return 0;
    }

    public function get_contact($id_user)
    {
        $results = $this->DB->select("id")->from(TICKET_USERS_TABLE)->where('id_user', $id_user)->get()->result_array();
        if (!empty($results) && is_array($results)) {
            return true;
        }

        return false;
    }

    public function get_user_messages($items_on_page = 10, $count_items_on_page = 0, $params = array(), $order_by = null)
    {
        $auth = $this->CI->session->userdata("auth_type");
        $user_messages = $this->get_messages($params, 1, $items_on_page, $count_items_on_page, $order_by);
        if (!empty($user_messages)) {
            $page_data["date_format"] = $this->pg_date->get_format('date_time_literal', 'st');
            $this->CI->view->assign('page_data', $page_data);
            $this->CI->view->assign('user_messages', $user_messages);

            return $this->CI->view->fetch('list_messages', $auth, 'tickets');
        } else {
            return false;
        }
    }

    public function format_messages($data)
    {
        if (empty($data)) {
            return $data;
        }
        if ($data[0]['id_user']) {
            $this->CI->load->model('Users_model');
            $user = $this->CI->Users_model->get_user_by_id($data[0]['id_user']);
            if (empty($user)) {
                $user = $this->CI->Users_model->format_default_user($data[0]['id_user']);
            }
        }
        foreach ($data as $key => $responder) {
            $responder['output_name'] = (!empty($user["output_name"])) ? $user["output_name"] : $this->CI->Users_model->set_user_output_name($user);
            if (!empty($responder["is_admin_sender"])) {
                $responder['output_name'] = 'admin';

                $this->CI->load->model("Uploads_model");
                $responder['output_logo'] = $this->CI->Uploads_model->format_default_upload('user-logo');
            }
            $responder['user'] = $user;
            $data[$key] = $responder;
        }

        return $data;
    }

    public function format_message($data, $is_admin = 0)
    {
        if (empty($data)) {
            return $data;
        }
        $return['message'] = array(
            'id_user'         => $data['id_user'],
            'is_admin_sender' => $is_admin,
            'message'         => $data['message'],
            'is_new'          => '1',
            'date_created'    => $data['date_created'],
        );
        $return['contact'] = array(
            'id_user'      => $data['id_user'],
            'admin_new'    => $this->get_count_new_messages($data['id_user']) + 1,
            'admin_last'   => $is_admin,
            'message'      => $data['message'],
            'is_new'       => '1',
            'date_created' => $data['date_created'],
        );

        return $return;
    }

    public function save_message($data)
    {
        if ($data['message']) {
            $this->DB->insert(TICKET_MESSAGES_TABLE, $data['message']);
            $id = $this->DB->insert_id();
        }
        if ($data['contact']) {
            $get_contact = $this->get_contact($data['contact']['id_user']);
            if ($get_contact) {
                $this->DB->where('id_user', $data['contact']['id_user']);
                $this->DB->update(TICKET_USERS_TABLE, $data['contact']);
            } else {
                $this->DB->insert(TICKET_USERS_TABLE, $data['contact']);
                $this->DB->insert_id();
            }
        }

        return $id;
    }

    /**
     * Read messages
     *
     * @param int  $id_user
     * @param bool $is_admin_sender
     *
     * @return boolean
     */
    public function read_messages($id_user, $is_admin_sender = false)
    {
        if (!$id_user) {
            return false;
        }
        $data['is_new'] = '0';
        $this->DB->where('id_user', $id_user);
        $this->DB->where('is_new', '1');
        $this->DB->where('is_admin_sender', $is_admin_sender);
        $this->DB->update(TICKET_MESSAGES_TABLE, $data);
        if (!$is_admin_sender) {
            $data['admin_new'] = '0';
            $this->DB->where('id_user', $id_user);
            $this->DB->where('is_new', '1');
            $this->DB->update(TICKET_USERS_TABLE, $data);
            
            $this->CI->load->model('menu/models/Indicators_model');
            $this->CI->Indicators_model->delete($this->indicator_type, $id_user);
            
        }

        return true;
    }

    public function get_responder_count($params = array(), $filter_object_ids = null)
    {
        $this->DB->select("COUNT(*) AS cnt");
        $this->DB->from(TICKET_USERS_TABLE);

        if (isset($params["where"]) && is_array($params["where"]) && count($params["where"])) {
            foreach ($params["where"] as $field => $value) {
                $this->DB->where($field, $value);
            }
        }

        if (isset($params["where_in"]) && is_array($params["where_in"]) && count($params["where_in"])) {
            foreach ($params["where_in"] as $field => $value) {
                $this->DB->where_in($field, $value);
            }
        }

        if (isset($params["where_sql"]) && is_array($params["where_sql"]) && count($params["where_sql"])) {
            foreach ($params["where_sql"] as $value) {
                $this->DB->where($value);
            }
        }

        if (isset($filter_object_ids) && is_array($filter_object_ids) && count($filter_object_ids)) {
            $this->DB->where_in("id_user", $filter_object_ids);
        }

        $results = $this->DB->get()->result_array();
        if (!empty($results) && is_array($results)) {
            return intval($results[0]["cnt"]);
        }

        return 0;
    }

    public function get_reason_list($params = array(), $filter_object_ids = null, $order_by = null)
    {
        $this->DB->select(implode(", ", $this->fields));
        $this->DB->from(TICKET_REASONS_TABLE);

        if (isset($params["where"]) && is_array($params["where"]) && count($params["where"])) {
            foreach ($params["where"] as $field => $value) {
                $this->DB->where($field, $value);
            }
        }

        if (isset($params["where_in"]) && is_array($params["where_in"]) && count($params["where_in"])) {
            foreach ($params["where_in"] as $field => $value) {
                $this->DB->where_in($field, $value);
            }
        }

        if (isset($params["where_sql"]) && is_array($params["where_sql"]) && count($params["where_sql"])) {
            foreach ($params["where_sql"] as $value) {
                $this->DB->where($value);
            }
        }

        if (isset($filter_object_ids) && is_array($filter_object_ids) && count($filter_object_ids)) {
            $this->DB->where_in("id", $filter_object_ids);
        }

        if (is_array($order_by) && count($order_by) > 0) {
            foreach ($order_by as $field => $dir) {
                if (in_array($field, $this->fields_news)) {
                    $this->DB->order_by($field . " " . $dir);
                }
            }
        }

        $results = $this->DB->get()->result_array();
        if (!empty($results) && is_array($results)) {
            foreach ($results as $r) {
                $data[] = $r;
            }

            return $this->format_reasons($data);
        }

        return array();
    }

    public function get_reason_count($params = array(), $filter_object_ids = null)
    {
        $this->DB->select("COUNT(*) AS cnt");
        $this->DB->from(TICKET_REASONS_TABLE);

        if (isset($params["where"]) && is_array($params["where"]) && count($params["where"])) {
            foreach ($params["where"] as $field => $value) {
                $this->DB->where($field, $value);
            }
        }

        if (isset($params["where_in"]) && is_array($params["where_in"]) && count($params["where_in"])) {
            foreach ($params["where_in"] as $field => $value) {
                $this->DB->where_in($field, $value);
            }
        }

        if (isset($params["where_sql"]) && is_array($params["where_sql"]) && count($params["where_sql"])) {
            foreach ($params["where_sql"] as $value) {
                $this->DB->where($value);
            }
        }

        if (isset($filter_object_ids) && is_array($filter_object_ids) && count($filter_object_ids)) {
            $this->DB->where_in("id", $filter_object_ids);
        }

        $results = $this->DB->get()->result_array();
        if (!empty($results) && is_array($results)) {
            return intval($results[0]["cnt"]);
        }

        return 0;
    }

    public function save_reason($id, $data, $langs)
    {
        if (is_null($id)) {
            $data["date_add"] = date("Y-m-d H:i:s");
            $this->DB->insert(TICKET_REASONS_TABLE, $data);
            $id = $this->DB->insert_id();
        } else {
            $this->DB->where('id', $id);
            $this->DB->update(TICKET_REASONS_TABLE, $data);
        }

        if (!empty($langs)) {
            $languages = $this->CI->pg_language->languages;
            if (!empty($languages)) {
                foreach ($languages as $language) {
                    $lang_ids[] = $language["id"];
                }
                $this->CI->pg_language->pages->set_string_langs('tickets', "tickets_reason_" . $id, $langs, $lang_ids);
            }
        }

        return $id;
    }

    public function validate_reason($id, $data, $langs)
    {
        $return = array("errors" => array(), "data" => array(), 'temp' => array(), 'langs' => array());

        if (isset($data["mails"])) {
            if (empty($data["mails"])) {
                $return['errors'][] = l('error_email_mandatory_field', 'tickets');
            }
            $data["mails"] = explode(',', $data["mails"]);
            foreach ($data["mails"] as $k => $mail) {
                $mail = trim(strip_tags($mail));
                if (!empty($mail)) {
                    $data["mails"][$k] = $mail;
                } else {
                    unset($data["mails"][$k]);
                }
            }
            $return["data"]["mails"] = serialize($data["mails"]);
        }

        if (!empty($langs)) {
            $return["langs"] = $langs;
            foreach ($langs as $lang_id => $name) {
                if (empty($name)) {
                    $return["errors"][] = l('error_reason_mandatory_field', 'tickets') . ': ' . $this->pg_language->languages[$lang_id]['name'];
                }
            }
        }

        return $return;
    }

    public function delete_reason($id)
    {
        $this->DB->where("id", $id);
        $this->DB->delete(TICKET_REASONS_TABLE);

        $this->CI->pg_language->pages->delete_string("tickets", "tickets_reason_" . $id);

        return;
    }

    public function format_reasons($data)
    {
        $languages = $this->CI->pg_language->languages;
        foreach ($data as $k => $reason) {
            $reason["name"] = l('tickets_reason_' . $reason["id"], 'tickets');
            foreach ($languages as $lang) {
                $reason["names"][$lang['id']] = l('tickets_reason_' . $reason["id"], 'tickets', $lang['id']);
            }
            $reason["mails"] = unserialize($reason["mails"]);
            if (!empty($reason["mails"]) && is_array($reason["mails"])) {
                $reason["mails_string"] = implode(", ", $reason["mails"]);
            } else {
                $reason["mails_string"] = "";
            }
            $data[$k] = $reason;
        }

        return $data;
    }

    public function remove_contact($params)
    {
        if (isset($params["where"]) && is_array($params["where"]) && count($params["where"])) {
            foreach ($params["where"] as $field => $value) {
                $this->DB->where($field, $value);
            }
        }
        if (isset($params["where_in"]) && is_array($params["where_in"]) && count($params["where_in"])) {
            foreach ($params["where_in"] as $field => $value) {
                $this->DB->where_in($field, $value);
            }
        }
        $this->DB->delete(TICKET_USERS_TABLE);
        $this->remove_message($params);
    }

    public function remove_message($params)
    {
        if (isset($params["where"]) && is_array($params["where"]) && count($params["where"])) {
            foreach ($params["where"] as $field => $value) {
                $this->DB->where($field, $value);
            }
        }
        if (isset($params["where_in"]) && is_array($params["where_in"]) && count($params["where_in"])) {
            foreach ($params["where_in"] as $field => $value) {
                $this->DB->where_in($field, $value);
            }
        }
        $this->DB->delete(TICKET_MESSAGES_TABLE);
    }

    public function format_responder($data)
    {
        if (empty($data)) {
            return $data;
        }
        $user_ids = array();
        foreach ($data as $key => $responder) {
            if (empty($user_ids) || !in_array($responder["id_user"], $user_ids)) {
                $user_ids[] = $responder["id_user"];
            }
            $data[$key] = $responder;
        }
        if (!empty($user_ids)) {
            $this->CI->load->model('Users_model');
            $users = $this->CI->Users_model->get_users_list_by_key(null, null, null, array(), $user_ids, true, true);
        }
        foreach ($data as $key => $responder) {
            if (!empty($users[$responder["id_user"]])) {
                $responder["user"] = $users[$responder["id_user"]];
            } else {
                $responder["user"] = $this->CI->Users_model->format_default_user($responder["id_user"]);
            }
            $responder["answered"] = (empty($responder["admin_last"])) ? $users[$responder["id_user"]]['output_name'] : 'admin';
            $data[$key] = $responder;
        }

        return $data;
    }

    public function validate_settings($data)
    {
        $return = array("errors" => array(), "data" => array());

        if (isset($data["default_alert_email"])) {
            $return["data"]["default_alert_email"] = trim(strip_tags($data["default_alert_email"]));

            $this->CI->config->load('reg_exps', true);
            $email_expr =  $this->CI->config->item('email', 'reg_exps');
            if (empty($return["data"]["default_alert_email"]) || !preg_match($email_expr, $return["data"]["default_alert_email"])) {
                $return["errors"][] = l('error_default_alert_email_incorrect', 'tickets');
            }
        }
        if (isset($data['load_messages'])) {
            $return["data"]["load_messages"] = intval($data["load_messages"]);
            if (empty($return["data"]["load_messages"])) {
                $return["errors"][] = l("error_load_messages_incorrect", "tickets");
            }
        }
        if (isset($data['status_personal_communication'])) {
            $return["data"]["status_personal_communication"] = intval($data["status_personal_communication"]);
        }

        return $return;
    }

    public function get_settings()
    {
        $data = array(
            "default_alert_email"           => $this->CI->pg_module->get_module_config('tickets', 'default_alert_email'),
            "load_messages"                 => $this->CI->pg_module->get_module_config('tickets', 'load_messages'),
            "status_personal_communication" => $this->CI->pg_module->get_module_config('tickets', 'status_personal_communication'),
        );

        return $data;
    }

    public function set_settings($data)
    {
        foreach ($data as $setting => $value) {
            $this->CI->pg_module->set_module_config('tickets', $setting, $value);
        }

        return;
    }

    public function send_contact_form($data)
    {
        $return = array("errors" => array(), "data" => array());
        $lang_id = '';
        $this->CI->load->model('Notifications_model');
        if (!empty($data["id_user"])) {
            if (!empty($data["is_admin_sender"])) {
                $data['link'] = site_url() . 'tickets/index';
                $template_gid = 'tickets_for_user';
                $mails[] = $data["email"];
                $lang_id = $data["lang_id"];
            } else {
                $data['link'] = site_url() . 'admin/tickets/answer/' . $data["id_user"];
                $template_gid = 'tickets_for_admin';
                $mails[] = $this->CI->pg_module->get_module_config('tickets', 'default_alert_email');
            }
        } else {
            $template_gid = 'tickets_form';
            if (!empty($data["reason_data"]) && !empty($data["reason_data"]["mails"])) {
                $mails = $data["reason_data"]["mails"];
            } else {
                $mails[] = $this->CI->pg_module->get_module_config('tickets', 'default_alert_email');
            }
        }

        if (empty($mails)) {
            $return["errors"][] = l('error_no_recipients', 'tickets');
        } else {
            foreach ($mails as $mail) {
                $send_data = $this->CI->Notifications_model->send_notification($mail, $template_gid, $data, '', $lang_id);
                if (!empty($send_data["errors"])) {
                    foreach ($send_data["errors"] as $error) {
                        $return["errors"][] = $error;
                    }
                }
            }
        }

        return $return;
    }

    public function validate_auth_contact_form($data)
    {
        if (isset($data["message"])) {
            $return["data"]["message"] = trim(strip_tags($data["message"]));

            if (empty($return["data"]["message"])) {
                $return["errors"]["message"] = l('error_message_incorrect', 'tickets');
            } else {
                $this->CI->load->model('moderation/models/Moderation_badwords_model');
                $bw_count = $this->CI->Moderation_badwords_model->check_badwords($this->moderation_type, $return['data']['message']);
                if ($bw_count) {
                    $return['errors']["message"] = l('error_badwords_message', 'tickets');
                }
            }
        }
        if (isset($data["id_user"])) {
            $return["data"]["id_user"] = intval($data["id_user"]);
        }
        $return["data"]["date_created"] = date("Y-m-d H:i:s");

        return $return;
    }

    public function validate_contact_form($data)
    {
        $return = array("errors" => array(), "data" => array());

        if (isset($data["user_name"])) {
            $return["data"]["user_name"] = trim(strip_tags($data["user_name"]));

            if (empty($return["data"]["user_name"])) {
                $return["errors"]['user_name'] = l('error_user_name_incorrect', 'tickets');
            } else {
                $this->CI->load->model('moderation/models/Moderation_badwords_model');
                $bw_count = $this->CI->Moderation_badwords_model->check_badwords($this->moderation_type, $return['data']['user_name']);
                if ($bw_count) {
                    $return['errors']['user_name'] = l('error_badwords_message', 'tickets');
                }
            }
        }

        if (isset($data["user_email"])) {
            $return["data"]["user_email"] = trim(strip_tags($data["user_email"]));

            $this->CI->config->load('reg_exps', true);
            $email_expr =  $this->CI->config->item('email', 'reg_exps');
            if (empty($return["data"]["user_email"]) || !preg_match($email_expr, $return["data"]["user_email"])) {
                $return["errors"]['user_email'] = l('error_user_email_incorrect', 'tickets');
            }
        }

        if (isset($data["subject"])) {
            $return["data"]["subject"] = trim(strip_tags($data["subject"]));

            if (empty($return["data"]["subject"])) {
                $return["errors"]['subject'] = l('error_subject_incorrect', 'tickets');
            } else {
                $this->CI->load->model('moderation/models/Moderation_badwords_model');
                $bw_count = $this->CI->Moderation_badwords_model->check_badwords($this->moderation_type, $return['data']['subject']);
                if ($bw_count) {
                    $return['errors']['subject'] = l('error_badwords_message', 'tickets');
                }
            }
        }

        if (isset($data["message"])) {
            $return["data"]["message"] = trim(strip_tags($data["message"]));

            if (empty($return["data"]["message"])) {
                $return["errors"]['message'] = l('error_message_incorrect', 'tickets');
            } else {
                $this->CI->load->model('moderation/models/Moderation_badwords_model');
                $bw_count = $this->CI->Moderation_badwords_model->check_badwords($this->moderation_type, $return['data']['message']);
                if ($bw_count) {
                    $return['errors']['message'] = l('error_badwords_message', 'tickets');
                }
            }
        }

        if (isset($data["id_reason"])) {
            $return["data"]["id_reason"] = intval($data["id_reason"]);
            if (!empty($return["data"]["id_reason"])) {
                $return["data"]["reason_data"] = $this->get_reason_by_id($return["data"]["id_reason"]);
                $return["data"]["reason"] = $return["data"]["reason_data"]["name"];
            } else {
                $return["data"]["reason"] = l('no_reason_filled', 'tickets');
            }
        }

        if (isset($data["captcha_code"])) {
            $return["data"]["captcha_code"] = trim(strip_tags($data["captcha_code"]));

            if (empty($return["data"]["captcha_code"]) || $return["data"]["captcha_code"] != $_SESSION["captcha_word"]) {
                $return["errors"]['captcha_code'] = l('error_captcha_code_incorrect', 'tickets');
            }
        }

        $data["data"]["form_date"] = date("Y-m-d H:i:s");

        return $return;
    }

    ////// seo
    public function get_seo_settings($method = '', $lang_id = '')
    {
        if (!empty($method)) {
            return $this->_get_seo_settings($method, $lang_id);
        } else {
            $actions = array('index');
            $return = array();
            foreach ($actions as $action) {
                $return[$action] = $this->_get_seo_settings($action, $lang_id);
            }

            return $return;
        }
    }

    public function _get_seo_settings($method, $lang_id = '')
    {
        if ($method == "index") {
            return array(
                'templates'   => array(),
                'url_vars'    => array(),
                'url_postfix' => array(),
                'optional'    => array(),
            );
        }
    }

    public function request_seo_rewrite($var_name_from, $var_name_to, $value)
    {
        if ($var_name_from == $var_name_to) {
            return $value;
        }

        show_404();
    }

    public function get_sitemap_xml_urls($generate = true)
    {
        $this->CI->load->helper('seo');

        $lang_canonical = true;

        if ($this->CI->pg_module->is_module_installed('seo')) {
            $lang_canonical = $this->CI->pg_module->get_module_config('seo', 'lang_canonical');
        }
        $languages = $this->CI->pg_language->languages;
        if ($lang_canonical) {
            $default_lang_id = $this->CI->pg_language->get_default_lang_id();
            $default_lang_code = $this->CI->pg_language->get_lang_code_by_id($default_lang_id);
            $langs[$default_lang_id] = $default_lang_code;
        } else {
            foreach ($languages as $lang_id => $lang_data) {
                $langs[$lang_id] = $lang_data['code'];
            }
        }

        $return = array();

        $user_settings = $this->pg_seo->get_settings('user', 'tickets', 'index');
        if (!$user_settings['noindex']) {
            if ($generate === true) { 
                $this->CI->pg_seo->set_lang_prefix('user');
                foreach ($languages as $lang_id => $lang_data) {
                    $lang_code = $this->CI->pg_language->get_lang_code_by_id($lang_id);
                    $this->CI->pg_seo->set_lang_prefix('user', $lang_code);
                    $return[] = array(
                        "url"      => rewrite_link('tickets', 'index', array(), false, $lang_code),
                        "priority" => $user_settings['priority'],
                        "page" => "view",
                    );
                }
            } else {
                $return[] = array(
                    "url"      => rewrite_link('tickets', 'index', array(), false, null, $lang_canonical),
                    "priority" => $user_settings['priority'],
                    "page" => "view",
                );
            }
        }

        return $return;
    }

    public function get_sitemap_urls()
    {
        $this->CI->load->helper('seo');
        $auth = $this->CI->session->userdata("auth_type");

        $block[] = array(
            "name"      => l('header_tickets_form', 'tickets'),
            "link"      => rewrite_link('tickets', 'index'),
            "clickable" => true,
        );

        return $block;
    }

    ////// banners callback method
    public function _banner_available_pages()
    {
        $return[] = array("link" => "tickets/index", "name" => l('header_tickets_form', 'tickets'));

        return $return;
    }

    public function backend_get_request_notifications()
    {
        $result = array('notifications' => array(), 'admin_new_message' => 0);
        $settings = $this->get_settings();
        if ($settings['status_personal_communication']) {
            $user_id = $this->CI->session->userdata('user_id');
            $params['where']['id_user'] = $user_id;
            $params['where']['is_new'] = 1;
            $params['where']['is_admin_sender'] = 1;
            $params['where']['notified'] = 1;
            $messages = $this->get_messages($params);
            $this->CI->load->helper('seo_helper');
            foreach ($messages as $message) {
                $link = site_url() . 'tickets/index';
                $result['notifications'][] = array(
                    'title' => l('notify_title', 'tickets'),
                    'text'  => l('notify_text', 'tickets'),
                    'more'  => str_replace('[more]', $link, l('notify_text', 'tickets')),
                );
            }
            $this->DB->set('notified', '0')->where($params['where'])->update(TICKET_MESSAGES_TABLE);

            $result['admin_new_message'] = $this->get_count_new_messages($user_id, 1);

            //messages block in header alerts
            $params = array();
            $params['where']['id_user'] = $user_id;
            $params['where']['is_new'] = 1;
            $params['where']['is_admin_sender'] = 1;
            $messages = $this->get_messages($params, 1, $this->messages_max_count_header, null, array('date_created' => 'DESC'));

            $this->CI->view->assign('messages', $messages);
            $result['new_message_alert_html'] = $this->CI->view->fetch('admin_new_messages_header', 'user', 'tickets');
            $result['max_messages_count'] = $this->messages_max_count_header;
        }

        return $result;
    }
}
