<?php

namespace Pg\Modules\Chats\Models;

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
define('CHATS_TABLE', DB_PREFIX . 'chats');

class Chats_model extends \Model
{
    private $CI;
    private $DB;
    private $objects   = array();
    public $activities = array('own_page', 'include');
    public $path       = 'chat/';
    public $fields     = array(
        'id',
        'gid',
        'active',
        'installed',
        'activities',
        'settings',
    );

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
        $this->DB = &$this->CI->db;
    }

    /**
     * Save chat
     *
     * @param array $data
     * @param int   $id
     *
     * @return int
     */
    public function save($data, $id = null)
    {
        if ($data instanceof \Pg\Modules\Chats\Models\Chats\Chat_abstract) {
            $data->save();
            $id = $data->get_id();
        } elseif ($id) {
            $this->DB->where('id', $id);
            $this->DB->update(CHATS_TABLE, $data);
        } else {
            $this->DB->insert(CHATS_TABLE, $data);
            $id = $this->DB->insert_id();
        }

        return (int) $id;
    }

    /**
     * Get chat list
     *
     * @return array
     */
    public function get_list($as_array = false)
    {
        $result = $this->DB->select(implode(',', $this->fields))
                        ->from(CHATS_TABLE)
                        ->get()->result_array();
        $chats  = array();
        foreach ($result as $chat) {
            $chat_model = $this->get($chat['gid']);
            if (!$chat_model) {
                continue;
            }
            $chat_model->set_id($chat['id'])
                    ->set_gid($chat['gid'])
                    ->set_installed($chat['installed'])
                    ->set_active($chat['active']);
            $chats[] = $as_array ? $chat_model->as_array() : $chat_model;
        }

        return $chats;
    }

    /**
     * List the chats from chats folder.
     *
     * @return array
     */
    public function get_folder_list()
    {
        if (!is_dir($this->path)) {
            log_message('error', '(chats): Wrong path');

            return array();
        }
        $path  = $this->path;
        $chats = array_filter(scandir($this->path), function ($elem) use ($path) {
            return ('.' !== $elem{0} && is_dir($path . $elem));
        });
        sort($chats);

        return $chats;
    }

    /**
     * Get chat object
     *
     * @param string $gid
     *
     * @return Chat_abstract
     */
    private function getObject($gid)
    {
        if (empty($this->objects[$gid])) {
            $model_name = ucfirst($gid);
            $this->CI->load->model("chats/models/chats/$model_name");
            $chat       = $this->CI->{$model_name};
            if (empty($chat)) {
                return false;
            }
            // Fill chat object with data from db
            $db_data = $this->get($chat->get_gid(), true);
            if ($db_data) {
                $chat->set($db_data);
            } else {
                log_message('warning', 'Chat "' . $gid . '" has no db record');
            }
            $this->objects[$gid] = $chat;
        }

        return $this->objects[$gid];
    }

    /**
     * Get chat
     *
     * @param string $gid
     * @param bool   $from_db Get db record
     *
     * @return boolean
     */
    public function get($gid, $from_db = false)
    {
        if ($from_db) {
            $result = $this->DB->select(implode(',', $this->fields))
                            ->from(CHATS_TABLE)
                            ->where('gid', $gid)
                            ->get()->result_array();
            if ($result) {
                return array_shift($result);
            } else {
                return false;
            }
        } else {
            $chat = $this->getObject($gid);

            return $chat;
        }
    }

    /**
     * Get active chat
     *
     * @return array
     */
    public function get_active()
    {
        $result = $this->DB->select(implode(',', $this->fields))
                        ->from(CHATS_TABLE)
                        ->where('active', true)
                        ->limit(1)
                        ->get()->result_array();
        if (empty($result)) {
            return;
        } else {
            $db_data    = array_shift($result);
            $model_name = ucfirst($db_data['gid']);
            $this->CI->load->model('chats/models/chats/' . $model_name);
            $chat       = $this->CI->{$model_name};
            if (empty($chat)) {
                return;
            } else {
                $chat->set($db_data);
            }

            return $chat;
        }
    }

    /**
     * Set active chat.
     * Only one chat may be active at a time
     *
     * @param type $gid
     * @param type $active
     */
    public function set_active($gid, $active = true)
    {
        if ($active) {
            // Deactivate active chat befor activating another one
            $active_chat = $this->get_active();
            if ($active_chat && $active_chat->get_gid() !== $gid) {
                $this->set_active($active_chat->get_gid(), false);
            }
        }
        $this->DB->where('gid', $gid)
                ->update(CHATS_TABLE, array('active' => $active)
        );
        foreach ($this->getObject($gid)->get_activities() as $activity) {
            $method_name = 'activity_' . $activity;
            if (method_exists($this, $method_name)) {
                $this->{$method_name}($gid, $active);
            }
        }

        return $this;
    }

    /**
     * Activate menu item
     *
     * @param bool $status
     */
    private function activateMenu($status)
    {
        $this->load->model("Menu_model");
        $menu = $this->Menu_model->get_menu_item_by_gid('chat_item');
        $this->Menu_model->save_menu_item($menu['id'], array(
            "status" => intval($status),
        ));
    }

    /**
     * Perform actions, required for chat that has own page
     *
     * @param string $gid
     * @param bool   $active
     */
    public function activity_own_page($gid, $active)
    {
        // TODO: chats callbacks
        $this->activateMenu($active);
    }

    /**
     * Set installed
     *
     * @param int  $gid
     * @param bool $installed
     *
     * @return \Chats_model
     */
    public function set_installed($gid, $installed = true)
    {
        $this->DB->where('gid', $gid)
                ->update(CHATS_TABLE, array(
                    'installed' => $installed,
        ));

        return $this;
    }

    public function get_sitemap_urls()
    {
        $this->CI->load->helper('seo');
        $auth  = $this->CI->session->userdata('auth_type');
        $block = array(
            array(
                'name'      => l('chats', 'chats'),
                'link'      => rewrite_link('chats', 'index'),
                'clickable' => $auth === 'user',
                'items'     => array(),
            ),
        );

        return $block;
    }

    public function get_sitemap_xml_urls()
    {
        $this->CI->load->helper('seo');

        return array();
    }

    public function request_seo_rewrite($var_name_from, $var_name_to, $value)
    {
        if ($var_name_from == $var_name_to) {
            return $value;
        }
    }

    public function get_seo_settings($method = '', $lang_id = '')
    {
        if (!empty($method)) {
            return $this->_get_seo_settings($method, $lang_id);
        } else {
            $actions = array('index');
            $return  = array();
            foreach ($actions as $action) {
                $return[$action] = $this->_get_seo_settings($action, $lang_id);
            }

            return $return;
        }
    }

    public function _get_seo_settings($method, $lang_id = '')
    {
        switch ($method) {
            case 'index':
                return array(
                    "templates" => array(),
                    "url_vars"  => array(),
                );
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
            'name'   => l('chat', 'chats'),
            'helper' => 'helper_btn_chat',
        );

        return $action;
    }

    public function cron_canceled_chats()
    {
        $chat = $this->get_active();
        if (!empty($chat) && ($chat->get_gid() == 'pg_videochat')) {
            $chat->canceled_chats();
        }
    }

    public function cron_send_alert_per_hour()
    {
        $chat = $this->get_active();
        if (!empty($chat) && ($chat->get_gid() == 'pg_videochat')) {
            $settings = $chat->get_settings();
            $chat->send_alert_per_hour(intval($settings['time_alert']));
        }
    }

    public function backend_get_request_notifications()
    {
        $result = array('notifications' => array());

        $chat = $this->get_active();
        if (!empty($chat) && ($chat->get_gid() == 'pg_videochat')) {
            $settings = $chat->get_settings();
            if ($settings['chat_type'] == 'now') {
                $chats_ids                          = array();
                $user_id                            = $this->CI->session->userdata('user_id');
                $attr['where']['status']            = 'approve';
                $attr['where']['invited_user_id']   = $user_id;
                $attr['where']['invited_is_online'] = 0;
                $attr['where']['is_notified']       = 0;
                $chats                              = $chat->get_last_chats_list(null, null, null, $attr);
                foreach ($chats as $chat_item) {
                    $chats_ids[]                = $chat_item['id'];
                    $link                       = site_url() . 'chats/go_to_chat/' . $chat_item['chat_key'];
                    $pg_videochat_invite_letter = str_replace('[inviter_name]', $chat_item['invite']['output_name'], l('pg_videochat_invite_letter', 'chats'));
                    $result['notifications'][]  = array(
                        'title' => '<a href="' . $link . '">' . l('pg_videochat_new_chat_request', 'chats') . '</a>',
                        'text'  => $pg_videochat_invite_letter,
                        'image' => $chat_item['invite']['media']['user_logo']['thumbs']['middle'],
                        'more'  => $link,
                        'link'  => $link,
                    );
                }
                $chat->setNotified($chats_ids);
            } else {
                $chats_ids                          = array();
                $user_id                            = $this->CI->session->userdata('user_id');
                $attr['where']['status']            = 'send';
                $attr['where']['invited_user_id']   = $user_id;
                $attr['where']['invited_is_online'] = 0;
                $attr['where']['is_notified']       = 0;
                $chats                              = $chat->get_last_chats_list(null, null, null, $attr);
                foreach ($chats as $chat_item) {
                    $chats_ids[]                = $chat_item['id'];
                    $link                       = site_url() . 'chats/index/inbox/';
                    $pg_videochat_invite_letter = str_replace('[inviter_name]', $chat_item['invite']['output_name'], l('pg_videochat_invite_letter', 'chats'));
                    $result['notifications'][]  = array(
                        'title' => '<a href="' . $link . '">' . l('pg_videochat_new_chat_request', 'chats') . '</a>',
                        'text'  => $pg_videochat_invite_letter,
                        'image' => $chat_item['invite']['media']['user_logo']['thumbs']['middle'],
                        'more'  => $link,
                        'link'  => $link,
                    );
                }
                $chat->setNotified($chats_ids);
            }
        }

        return $result;
    }
}
