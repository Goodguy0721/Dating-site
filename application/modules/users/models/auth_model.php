<?php

namespace Pg\Modules\Users\Models;

/**
 * User auth model
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category    modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Katya Kashkova <katya@pilotgroup.net>
 *
 * @version $Revision: 2 $ $Date: 2010-04-02 15:07:07 +0300 (Ср, 02 апр 2010) $ $Author: kkashkova $
 * */
class Auth_model extends \Model
{
    public $ci;

    public function __construct()
    {
        parent::__construct();
        $this->ci = &get_instance();
        $this->ci->load->model("Users_model");
    }

    public function login($id)
    {
        $data["errors"] = array();
        $data["user_data"] = array();
        $data["login"] = false;
        $data["invalid_data"] = false;
        $data["blocked"] = false;
        $user_data = $this->ci->Users_model->get_user_by_id($id);

        if (empty($user_data)) {
            $data["errors"][] = l('error_login_invalid_data', 'users');
        } else {
            /*if ($user_data["confirm"] == 0) {
                $data["errors"][] = l('error_unconfirmed_user', 'users', $user_data['lang_id']);
                $data["user_data"]["confirm"] = 0;
            } else*/if ($user_data["approved"] == 0 &&
                    $this->pg_module->get_module_config('users', 'user_approve') != 2) {
                if (!$this->ci->router->is_api_class) {
                    $this->view->setRedirect(site_url() . 'users/inactive');
                }
                $data["blocked"] = true;
            } else {
                $this->update_user_session_data($id);
                if (!$this->ci->router->is_api_class &&
                        $this->ci->pg_module->is_module_installed('network') &&
                        \Pg\Modules\Network\Models\Network_users_model::DECISION_UNKNOWN === $user_data['net_user_decision']) {
                    if ($this->pg_module->is_module_active('network')) {
                        //$network_requirements = \Pg\Modules\Network\Models\Network_users_model::validateRequirements();
                        $this->ci->load->model('Network_model');
                        $network_requirements = $this->ci->Network_model->validateRequirementsCli(); 
                        foreach ($network_requirements['data'] as $requirement) {
                            $is_set[] = $requirement['value'];
                        }
                        $value_set = array_search('No', $is_set);
                        if ($value_set === false) {
                            $this->view->setRedirect(site_url() . 'network/register', 'hard');
                        }
                    }
                }
                $data["user_data"] = $user_data;
                $data["login"] = true;
            }
        }

        return $data;
    }

    public function update_user_session_data($id)
    {
        $user_data = $this->ci->Users_model->get_user_by_id($id, true);

        if ($user_data) {
            $this->ci->Users_model->set_user_output_name($user_data);

            $logo_field = !empty($user_data['user_logo_moderation']) ? 'user_logo_moderation' : 'user_logo';

            $session = array(
                "auth_type"     => 'user',
                "approved"      => $user_data['approved'],
                "user_id"       => $user_data["id"],
                "user_type"     => $user_data["user_type"],
                "fname"         => $user_data["fname"],
                "sname"         => $user_data["sname"],
                "nickname"      => $user_data["nickname"],
                "output_name"   => $user_data["output_name"],
                "lang_id"       => $this->ci->pg_language->current_lang_id,
                "online_status" => $user_data["online_status"],
                "site_status"   => $user_data["site_status"],
                "show_adult"    => $user_data["show_adult"],
                "logo"          => $user_data['media'][$logo_field]['thumbs']['small'],
                'activity' => $user_data['activity'],
            );
        } else {
            $session = array();
        }

        $this->ci->session->set_userdata($session);
        $this->ci->view->assign('user_session_data', $this->ci->session->all_userdata());

        return $session;
    }

    public function login_by_email_password($email, $password)
    {
        $user_data = $this->ci->Users_model->get_user_by_email_password($email, $password);
        if (empty($user_data)) {
            $data["errors"][] = l('error_login_invalid_data', 'users');
        } else {
            $data = $this->login($user_data["id"]);
        }

        return $data;
    }

    public function logoff()
    {
        $this->ci->session->sess_destroy();
    }

    public function validate_login_data($data)
    {
        $return = array("errors" => array(), "data" => array());

        if (!empty($data["email"]) && !empty($data["password"])) {
            $this->ci->config->load('reg_exps', true);

            $email_expr = $this->ci->config->item('email', 'reg_exps');
            $return["data"]["email"] = strip_tags($data["email"]);

            $password_expr = $this->ci->config->item('password', 'reg_exps');
            $data["password"] = trim(strip_tags($data["password"]));
            $return["data"]["password"] = $data["password"];

            if ((empty($return["data"]["email"]) || !preg_match($email_expr, $return["data"]["email"])) && !preg_match($password_expr, $data["password"])) {
                $return["errors"] = array(l('error_login_invalid_data', 'users'));
            } else {
                if (empty($return["data"]["email"]) || !preg_match($email_expr, $return["data"]["email"])) {
                    $return["errors"][] = l('error_email_incorrect', 'users');
                }
                if (!preg_match($password_expr, $data["password"])) {
                    $return["errors"][] = l('error_login_invalid_data', 'users');
                }
            }
        } else {
            $return["errors"][] = l('error_login_invalid_data', 'users');
        }

        return $return;
    }
}
