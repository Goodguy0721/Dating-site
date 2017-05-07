<?php

namespace Pg\Modules\Chats\Controllers;

/**
 * Chats controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Alexander Batukhtin <abatukhtin@pilotgroup.net>
 * */
class Chats extends \Controller
{
    /**
     * Controller
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Chats_model');
    }

    public function call($chat_gid, $method_name)
    {
        $method_name = 'call_' . $method_name;
        $chat = $this->Chats_model->get($chat_gid);
        if (!$chat || !method_exists($chat, $method_name)) {
            show_404();
        } else {
            if (func_num_args() > 2) {
                $args = array_slice(func_get_args(), 2);
            } else {
                $args = array();
            }
            call_user_func_array(array($chat, $method_name), $args);
            exit;
        }
    }

    public function index($subpage = '')
    {
        redirect(site_url() . 'chat/cometchat/cometchat_embedded.php', '', 'hard');
        
        $chat = $this->Chats_model->get_active();
        if (empty($chat)) {
            show_404();
        }
        $this->Menu_model->breadcrumbs_set_active(l('chat', 'chats'));
        $this->view->assign('chat_block', $chat->user_page($subpage));
        $this->view->render('chat');
    }

    public function ajax_invite_form($user_id = '')
    {
        $chat = $this->Chats_model->get_active();
        if (empty($chat)) {
            show_404();
        }

        $contact_user = $this->Users_model->get_user_by_id($user_id);
        if ($contact_user['online_status'] == 0) {
            $this->view->assign('error_online');
            $this->view->render();

            return;
        }

        $settings = $chat->get_settings();

        if ($settings['fee_type'] == 'payed') {
            $this->load->model('Users_model');

            $user_data = $this->Users_model->get_user_by_id($this->session->userdata('user_id'));

            if (floatval($user_data['account']) < floatval($settings['amount'])) {
                $this->load->helper('seo');

                $link = rewrite_link('users', 'account', array('action' => 'update'));
                $lang_add_money = str_replace('[link]', $link, l('pg_videochat_text_add_funds', 'chats'));

                $this->view->assign('not_money', 1);
                $this->view->assign('lang_add_money', $lang_add_money);
            } elseif ($settings['chat_type'] == 'now') {
                $chat_key = $chat->generateKey();
            }
        } elseif ($settings['chat_type'] == 'now') {
            $chat_key = $chat->generateKey();
        }

        if (isset($chat_key)) {
            $save_data = array(
                'invite_user_id'                => $this->session->userdata('user_id'),
                'invited_user_id'               => $user_id,
                'date_time'                     => date('Y-m-d H:i:00'),
                'date_created'                  => date('Y:m:d H:i:s'),
                'chat_key'                      => $chat_key,
                'status'                        => 'approve',
                'last_change_date_time_user_id' => $this->session->userdata('user_id'),
            );
            $chat_id = $chat->save_chat(null, $save_data);

            //$this->view->assign($chat_key);
            $this->view->render();

            return;
        }

        $timezone_offset = timezone_offset_get(timezone_open(date_default_timezone_get()), new \DateTime());
        if ($timezone_offset) {
            $timezone_offset = sprintf('%02d', ceil($timezone_offset / 3600));
        }
        $this->view->assign('timezone_offset', $timezone_offset);

        $proposed_time = time() + 3600;

        $this->view->assign('proposedDate', date('Y-m-d', $proposed_time));
        $this->view->assign('proposedHours', date('H', $proposed_time));
        $this->view->assign('proposedMinutes', floor(((int) date('i', $proposed_time)) / 15) * 15);

        $this->view->assign('minDate', date('Y-m-d'));
        $this->view->assign('settings', $settings);
        $this->view->assign('user_id', $user_id);

        $this->view->render('ajax_invite_form');
    }

    public function ajax_check_invite($user_id = '')
    {
        $return = array();

        $chat = $this->Chats_model->get_active();
        if (empty($chat) || (!in_array($chat->get_gid(), array('pg_videochat', 'oovoochat')))) {
            show_404();
        }

        $post_data = array(
            'date' => $this->input->post("date", true),
            'time' => $this->input->post("time", true),
            //'message' => $this->input->post("message", true),
        );

        $validate_data = $chat->validate_invite_form($post_data);
        if (!empty($validate_data['errors'])) {
            $return['error'] = $validate_data['errors'];
        }

        $this->view->assign($return);

        $this->view->render();
    }

    public function invite($user_id = '')
    {
        $return = array();

        $chat = $this->Chats_model->get_active();
        if (empty($chat) || empty($user_id) || (!in_array($chat->get_gid(), array('pg_videochat', 'oovoochat')))) {
            show_404();
        }

        $date = $this->input->post("date", true);
        $time = $this->input->post("time", true);

        $save_data = array(
            'invite_user_id'  => $this->session->userdata('user_id'),
            'invited_user_id' => $user_id,
            'date_time'       => $date . ' ' . $time . ':00',
            'date_created'    => date('Y:m:d H:i:s'),
        );

        $message = l('message_new_invite', 'chats');
        $user_name = '<a href="' . site_url() . 'user/view/' . $this->session->userdata('user_id') . '">'
                   . $this->session->userdata('output_name') . '</a>';
        $message = str_replace(array('[user_name]', '[date_time]', '[invite_link]'),
                               array($user_name, $save_data['date_time'], site_url() . 'chats/index/inbox'),
                               $message);

        $this->load->model("Mailbox_model");

        $message_data = array(
            'id_to_user'   => $user_id,
            'subject'      => l('subject_new_invite', 'chats'),
            'message'      => $message,
            'id_user'      => $this->session->userdata('user_id'),
            'id_from_user' => $this->session->userdata('user_id'),
            'is_new'       => 0,
            'folder'       => 'drafts',
        );
        $message_id = $this->Mailbox_model->save_message(null, $message_data);
        $is_send = $this->Mailbox_model->send_message($message_id);

        $save_data['invite_message_id'] = $is_send;
        $save_data['last_change_date_time_user_id'] = $this->session->userdata('user_id');
        $chat_id = $chat->save_chat(null, $save_data);

        $this->system_messages->addMessage('success', l('success_add_chat', 'chats'));

        redirect($_SERVER["HTTP_REFERER"], 'hard');
    }

    public function discuss($chat_id = '')
    {
        $return = array();

        $chat = $this->Chats_model->get_active();
        if (empty($chat) || empty($chat_id) || (!in_array($chat->get_gid(), array('pg_videochat', 'oovoochat')))) {
            show_404();
        }

        $user_id = $this->session->userdata('user_id');
        $last_chat = $chat->get_last_chat_by_id($chat_id, false);
        if (!empty($last_chat) && ($last_chat['invited_user_id'] == $user_id &&
            ($last_chat['status'] == 'send' || $last_chat['status'] == 'discussed'))) {
            if ($last_chat['status'] == 'send') {
                $save_data['status'] = 'discussed';
                $chat->save_chat($chat_id, $save_data);
            }
            redirect(site_url() . 'mailbox/view/' . $last_chat['invite_message_id'], 'hard');
        } else {
            show_404();
        }
    }

    public function accept($chat_id = '')
    {
        $return = array();

        $chat = $this->Chats_model->get_active();
        if (empty($chat) || empty($chat_id) || (!in_array($chat->get_gid(), array('pg_videochat', 'oovoochat')))) {
            show_404();
        }

        $user_id = $this->session->userdata('user_id');

        $last_chat = $chat->get_last_chat_by_id($chat_id, false);
        if (!empty($last_chat) && ($last_chat['last_change_date_time_user_id'] != $user_id &&
            ($last_chat['invite_user_id'] == $user_id || $last_chat['invited_user_id'] == $user_id) &&
            ($last_chat['status'] == 'send' || $last_chat['status'] == 'discussed' || $last_chat['status'] == 'decline'))) {
            $save_data['status'] = 'approve';
            $chat->save_chat($chat_id, $save_data);
            redirect($_SERVER["HTTP_REFERER"], 'hard');
        } else {
            show_404();
        }
    }

    public function decline($chat_id = '')
    {
        $return = array();

        $chat = $this->Chats_model->get_active();
        if (empty($chat) || empty($chat_id) || (!in_array($chat->get_gid(), array('pg_videochat', 'oovoochat')))) {
            show_404();
        }

        $user_id = $this->session->userdata('user_id');
        $last_chat = $chat->get_last_chat_by_id($chat_id, false);
        if (!empty($last_chat) && ($last_chat['invited_user_id'] == $user_id &&
            ($last_chat['status'] == 'send' || $last_chat['status'] == 'discussed' || $last_chat['status'] == 'approve'))) {
            $save_data['status'] = 'decline';
            $chat->save_chat($chat_id, $save_data);
            redirect($_SERVER["HTTP_REFERER"], 'hard');
        } else {
            show_404();
        }
    }

    public function delete($chat_id = '')
    {
        $return = array();

        $chat = $this->Chats_model->get_active();
        if (empty($chat) || empty($chat_id) || (!in_array($chat->get_gid(), array('pg_videochat', 'oovoochat')))) {
            show_404();
        }

        $user_id = $this->session->userdata('user_id');
        $last_chat = $chat->get_last_chat_by_id($chat_id, false);
        if (!empty($last_chat) && ($last_chat['invite_user_id'] == $user_id &&
            ($last_chat['status'] != 'completed' || $last_chat['status'] != 'current' || $last_chat['status'] != 'paused'))) {
            $chat->delete_chat($chat_id);
            $this->view->setRedirect($_SERVER["HTTP_REFERER"], 'hard');
        } else {
            show_404();
        }
    }

    public function ajax_change_time_form($chat_id = '')
    {
        $chat = $this->Chats_model->get_active();
        if (empty($chat) || empty($chat_id) || (!in_array($chat->get_gid(), array('pg_videochat', 'oovoochat')))) {
            show_404();
        }
        $user_id = $this->session->userdata('user_id');
        $last_chat = $chat->get_last_chat_by_id($chat_id, false);
        //if (!empty($last_chat) && ($last_chat['invite_user_id']==$user_id &&
        //    ($last_chat['status']=='send' || $last_chat['status']=='discussed'))){
            $this->view->assign('minDate', date('Y-m-d'));
        $data_time_array = explode(' ', $last_chat['date_time']);

        $timezone_offset = timezone_offset_get(timezone_open(date_default_timezone_get()), new \DateTime());
        if ($timezone_offset) {
            $timezone_offset = sprintf('%02d', ceil($timezone_offset / 3600));
        }
        $this->view->assign('timezone_offset', $timezone_offset);

        $this->view->assign('current_date', $data_time_array[0]);
        $time_array  = explode(':', $data_time_array[1]);
        $this->view->assign('current_hours', intval($time_array[0]));
        $this->view->assign('current_minutes', intval($time_array[1]));
        $this->view->assign('chat_id', $chat_id);
        $this->view->render('ajax_change_time_form');
        /*} else{
            show_404();
        }*/
    }

    public function ajax_check_change($chat_id = '')
    {
        $return = array('error' => array());

        $chat = $this->Chats_model->get_active();
        if (empty($chat) || (!in_array($chat->get_gid(), array('pg_videochat', 'oovoochat')))) {
            show_404();
        }

        $post_data = array(
            'date' => $this->input->post("date", true),
            'time' => $this->input->post("time", true),
        );

        $validate_data = $chat->validate_invite_form($post_data);
        if (!empty($validate_data['errors'])) {
            $return['error'] = $validate_data['errors'];
        }

        $this->view->assign($return);

        $this->view->render();
    }

    public function change($chat_id = '')
    {
        $return = array();

        $chat = $this->Chats_model->get_active();
        if (empty($chat) || empty($chat_id) || (!in_array($chat->get_gid(), array('pg_videochat', 'oovoochat')))) {
            show_404();
        }

        $date = $this->input->post('date', true);
        $time = $this->input->post("time", true);

        $save_data = array();
        $save_data['date_time'] = $date . ' ' . $time . ':00';

        $last_chat = $chat->get_last_chat_by_id($chat_id, false);
        if (!empty($last_chat) && ($last_chat['status'] == 'send' || $last_chat['status'] == 'discussed')) {
            $my_user_id = $this->session->userdata('user_id');

            if ($last_chat['invite_user_id'] == $my_user_id) {
                $user_id = $last_chat['invited_user_id'];
            } elseif ($last_chat['invited_user_id'] == $my_user_id) {
                $user_id = $last_chat['invite_user_id'];
            }

            if (!$user_id) {
                show_404();
            }

            $message = l('message_change_time_invite', 'chats');
            $user_name = '<a href="' . site_url() . 'user/view/' . $my_user_id . '">' . $this->session->userdata("output_name") . '</a>';
            $message = str_replace(array('[user_name]', '[date_time]', '[invite_link]'), array($user_name, $save_data['date_time'], site_url() . "chats/index"), $message);

            $this->load->model("Mailbox_model");

            $message_data = array(
                'id_to_user'   => $user_id,
                'subject'      => l('subject_change_time_invite', 'chats'),
                'message'      => $message,
                'id_user'      => $my_user_id,
                'id_from_user' => $my_user_id,
                'is_new'       => 0,
                'folder'       => 'drafts',
            );

            $message_id = $this->Mailbox_model->save_message(null, $message_data);
            $is_send = $this->Mailbox_model->send_message($message_id);

            $save_data['invite_message_id'] = $is_send;
            $save_data['last_change_date_time_user_id'] = $my_user_id;

            if ($last_chat['status'] == 'send') {
                $save_data['status'] = 'discussed';
            }

            $chat_id = $chat->save_chat($chat_id, $save_data);

            $this->system_messages->addMessage('success', l('success_updated_chat', 'chats'));

            redirect(site_url() . 'chats/index/inbox/');
        } else {
            show_404();
        }
    }

    public function complete($chat_id = '')
    {
        $return = array();
        $chat = $this->Chats_model->get_active();
        if (empty($chat) || empty($chat_id) || (!in_array($chat->get_gid(), array('pg_videochat', 'oovoochat')))) {
            show_404();
        }

        $last_chat = $chat->get_last_chat_by_id($chat_id);
        if (!empty($last_chat) && ($last_chat['status'] == 'current' || $last_chat['status'] == 'paused')) {
            $my_user_id = $this->session->userdata('user_id');

            $is_inviter = true;
            if ($last_chat['invite_user_id'] == $my_user_id) {
                $user_id = $last_chat['invited_user_id'];
            } elseif ($last_chat['invited_user_id'] == $my_user_id) {
                $user_id = $last_chat['invite_user_id'];
                $is_inviter = false;
            }
            if (!$user_id) {
                show_404();
            }

            $save_data['status'] = 'completed';
            if (isset($last_chat['amount_per_second'])) {
                $current_price = floatval($last_chat['duration'] * $last_chat['amount_per_second']);
                if ($current_price > $last_chat['invite']['account']) {
                    $current_price = $last_chat['invite']['account'];
                }

                $message = str_replace(array('[with_name]', '[date_chat]'),
                                       array($last_chat['invited']['output_name'],
                                       date('Y-m-d'), ), l('service_payment', 'chats'));

                $chat->write_off_for_chat($last_chat['invite_user_id'], $current_price, $message);

                $save_data['amount'] = $current_price;
            }

            $chat_id = $chat->save_chat($chat_id, $save_data);

            $this->system_messages->addMessage('success', l('success_updated_chat', 'chats'));

            $this->view->setRedirect(site_url() . 'chats');
        } else {
            show_404();
        }
    }

    public function go_to_chat($chat_id = null, $is_oovoo_session = 0)
    {
        $return = array();

        $chat = $this->Chats_model->get_active();

        $chat_gid = $chat->get_gid();

        $user_id = $this->session->userdata('user_id');
        if (empty($chat) || empty($chat_id) || empty($user_id) || (!in_array($chat_gid, array('pg_videochat', 'oovoochat')))) {
            redirect(site_url() . 'chats');
        }

        $settings = $chat->get_settings();
        if ($settings['chat_type'] == 'now') {
            $last_chat = $chat->get_last_chat_by_key($chat_id);
        } else {
            $last_chat = $chat->get_last_chat_by_id($chat_id);
        }

        if (!empty($last_chat) && $last_chat['status'] == 'completed') {
            redirect(site_url() . 'chats');
        }

        if (!empty($last_chat) && ($last_chat['invite_user_id'] == $user_id || $last_chat['invited_user_id'] == $user_id) &&
            ($last_chat['status'] == 'approve' || $last_chat['status'] == 'current' || $last_chat['status'] == 'paused')) {
            $params = array(
                'where'     => array('id !=' => $chat_id),
                'where_sql' => array(
                    " (invite_user_id='" . $user_id . "' OR invited_user_id='" . $user_id . "')",
                    " (status='current' OR status='paused')",
                ),
            );

            $count_sessions = $chat->get_last_chats_count($params);
            if ($count_sessions > 0) {
                $this->system_messages->addMessage('error', l('error_max_sessions', 'chats'));
                redirect(site_url() . 'chats');
            }

            if ($last_chat['invite_user_id'] == $user_id) {
                $save_data['inviter_is_online'] = 1;
            } else {
                $save_data['invited_is_online'] = 1;
            }

            $save_data['status'] = 'current';
            $chat->save_chat($chat_id, $save_data);

            $this->view->assign('settings_chat', $settings);
            $this->view->assign('settings_json_data', json_encode($settings));

            $this->view->assign('last_chat', $last_chat);
            $this->view->assign('chat_json_data', json_encode($last_chat));
            $complete_lang = str_replace('[link]', site_url() . 'chats', l('text_complete_lang', 'chats'));
            $this->view->assign('complete_lang', $complete_lang);

            if ($chat_gid == 'pg_videochat') {
                $this->view->render('show_chat');
            } elseif ($chat_gid == 'oovoochat') {
                $this->view->assign('participantId', $user_id);
                $this->view->assign('participantName', $this->session->userdata('output_name'));

                if ($is_oovoo_session) {
                    $session_token = urlencode($last_chat['session_token'][$user_id]);
                } else {
                    $session_token = '';
                }

                $this->view->assign('sessionToken', $session_token);

                $this->view->render('oovoochat');
            }
        }
    }

    public function ajax_check_status($chat_id = '')
    {
        $return = array();

        $chat = $this->Chats_model->get_active();
        $user_id = $this->session->userdata('user_id');
        $last_chat = $chat->get_last_chat_by_id($chat_id);

        $this->view->assign($last_chat);

        $this->view->render();
    }

    public function ajax_change_status($chat_id = '')
    {
        $return = array();

        $post_data = array(
            'status' => $this->input->post("status", true),
        );

        $chat = $this->Chats_model->get_active();

        $chat_gid = $chat->get_gid();

        switch ($chat_gid) {
            case 'pg_videochat':
                $post_data['inviter_peer_id'] = $this->input->post("inviter_peer_id", true);
                $post_data['invited_peer_id'] = $this->input->post("invited_peer_id", true);
                break;
        }

        $user_id = $this->session->userdata('user_id');
        $last_chat = $chat->get_last_chat_by_id($chat_id);

        // если это инициализатор чата
        if ($last_chat['invite_user_id'] == $user_id) {
            $save_data['inviter_is_online'] = 1;

            // если чат активен
            if ($post_data['status'] == 'start') {
                $save_data['duration'] = 0;
                if ($last_chat['date_time_start'] == '') {
                    $last_chat['date_time_start'] = $save_data['date_time_start'] = time();
                } else {
                    $save_data['date_time_start'] = time();
                    $last_chat['duration'] = $save_data['duration'] = $last_chat['duration'] + ($save_data['date_time_start'] - intval($last_chat['date_time_start']));
                    $last_chat['date_time_start'] = $save_data['date_time_start'];
                }

                $settings = $chat->get_settings();

                if ($settings['fee_type'] == 'payed') {
                    if (isset($last_chat['no_money']) && $last_chat['no_money'] == 1) {
                        $last_chat['status'] = $save_data['status'] = 'completed';

                        $message = str_replace(array('[with_name]', '[date_chat]'), array($last_chat['invited']['output_name'], date('Y-m-d')), l('service_payment', 'chats'));
                        $chat->write_off_for_chat($user_id, $last_chat['invite']['account']);
                        $last_chat['amount'] = $save_data['amount'] = $last_chat['invite']['account'];
                    } elseif (isset($last_chat['available_time'])) {
                        $available_time = $last_chat['available_time'] - $save_data['duration'];
                        if ($available_time <= 0) {
                            $last_chat['status'] = $save_data['status'] = 'completed';

                            $message = str_replace(array('[with_name]', '[date_chat]'), array($last_chat['invited']['output_name'], date('Y-m-d')), l('service_payment', 'chats'));
                            $chat->write_off_for_chat($user_id, $last_chat['invite']['account']);
                            $last_chat['amount'] = $save_data['amount'] = $last_chat['invite']['account'];
                        } elseif ($available_time > 30 && $available_time < 33) {
                            $last_chat['errors'][] = str_replace('[available_time]', $available_time, l('pg_videochat_text_add_funds_current', 'chats'));
                        }
                    }
                }

                $save_data['invited_is_paused'] = 0;
            } else {
                if ($last_chat['date_time_start'] != '') {
                    $last_chat['duration'] = $save_data['duration'] = $last_chat['duration'] + (time() - intval($last_chat['date_time_start']));
                    $save_data['date_time_start'] = '';
                }

                if ($post_data['status'] == 'pause') {
                    $last_chat['status'] = $save_data['status'] = 'paused';
                    $save_data['invited_is_paused'] = 1;
                } else {
                    $save_data['invited_is_paused'] = 0;
                }

                if ($post_data['status'] == 'stop') {
                    $last_chat['status'] = $save_data['status'] = 'completed';
                    if (isset($last_chat['amount_per_second']) && $last_chat['amount'] == 0) {
                        $current_price = floatval($last_chat['duration'] * $last_chat['amount_per_second']);
                        if ($current_price > $last_chat['invite']['account']) {
                            $current_price = $last_chat['invite']['account'];
                        }

                        $message = str_replace(array('[with_name]', '[date_chat]'), array($last_chat['invited']['output_name'], date('Y-m-d')), l('service_payment', 'chats'));
                        $chat->write_off_for_chat($user_id, $current_price, $message);
                        $last_chat['amount'] = $save_data['amount'] = $current_price;
                    }
                }
            }
            $last_chat['inviter_peer_id'] = $save_data['inviter_peer_id'] = $post_data['inviter_peer_id'];
        } else {
            $last_chat['invited_peer_id'] = $save_data['invited_peer_id'] = $post_data['invited_peer_id'];
            if ($post_data['status'] == 'start' && $last_chat['status'] == 'paused' &&
                $last_chat['inviter_is_paused'] == 0 && $last_chat['invited_is_paused'] == 0) {
                $last_chat['status'] = $save_data['status'] = 'current';
                $save_data['invited_is_paused'] = 0;
            }
            if ($post_data['status'] == 'stop') {
                $last_chat['status'] = $save_data['status'] = 'completed';
            }
            if ($post_data['status'] == 'pause') {
                $last_chat['status'] = $save_data['status'] = 'paused';
                $save_data['invited_is_paused'] = 1;
            } else {
                $save_data['invited_is_paused'] = 0;
            }
        }
        $chat->save_chat($chat_id, $save_data);

        $this->view->assign($last_chat);

        $this->view->render();
    }

    public function ajax_get_messages($chat_id = null)
    {
        if (!$chat_id) {
            return;
        }

        $return = array();
        $message_max_id = $this->input->post("message_max_id", true);
        $chat = $this->Chats_model->get_active();
        $return['messages'] = $chat->get_messages($chat_id, $message_max_id);

        $this->view->assign($return);
        $this->view->render();
    }

    public function ajax_send_message($chat_id = '')
    {
        $return = array();

        $message = $this->input->post("message", true);

        $message_max_id = $this->input->post('message_max_id', true);

        $chat = $this->Chats_model->get_active();

        $add_message = array(
            'id_chat' => $chat_id,
            'id_user' => $this->session->userdata('user_id'),
            'message' => $message,
        );
        $chat->add_message($add_message);

        $return['messages'] = $chat->get_messages($chat_id, $message_max_id);

        $this->view->assign($return);

        $this->view->render();
    }

    public function saveSession($chat_id)
    {
        $chat = $this->Chats_model->get_active();

        $last_chat = $chat->get_last_chat_by_id($chat_id);
        if (empty($last_chat)) {
            show_404();
        }

        $last_chat['session_token'][$this->session->userdata('user_id')] = $this->input->get('t');

        $save_data = array('session_token' => serialize($last_chat['session_token']));
        $chat->save_chat($chat_id, $save_data);

        $last_chat = $chat->get_last_chat_by_id($chat_id);

        redirect(site_url() . 'chats/go_to_chat/' . $chat_id . '/1');
    }
}
