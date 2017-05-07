<?php

namespace Pg\Modules\Like_me\Controllers;

use Pg\Libraries\View;
use Pg\Modules\Like_me\Models\Like_me_model;

/**
 * Api Like me controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category    modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Nikita Savanaev <nsavanaev@pilotgroup.net>
 **/
class Api_Like_me extends \Controller
{
    /**
     * Constructor
     *
     * @return Api_Like_me
     */
    public function __construct()
    {
        parent::__construct();

        $this->load->model('users/models/Users_utils_model');
        if (!$this->Users_utils_model->isActived()) {
            $this->system_messages->addMessage(View::MSG_ERROR, l('text_inactive_in_search', 'users'));
            $this->view->render();
            return;
        }

        $this->load->model('like_me/models/Like_me_model');
    }

    public function play($play_location=Like_me_model::PLAY_LOCATION_GLOBAL)
    {
        $this->Like_me_model->setPlayLocation($play_location);
        $this->view->assign('users', $this->Like_me_model->getUsers($play_location));
        $this->view->render();
    }

    public function matches()
    {
        $page = (int)$this->input->post('page');

        $user_id = intval($this->session->userdata('user_id'));

        $count = $this->Like_me_model->getCountMatchesList($user_id);

        if ($count > 0) {
            $items_on_page = $this->pg_module->get_module_config('like_me', 'matches_per_page');

            $this->load->helper('sort_order');
            $page = get_exists_page_number($page, $count, $items_on_page);

            $users = $this->Like_me_model->getMatchesList($page, $items_on_page, $user_id);
        } else {
            $users = [];
        }

        $this->view->assign('count', $count);
        $this->view->assign('users', $users);
        $this->view->render();
    }

    public function settings()
    {
        $this->view->assign($this->Like_me_model->getSettings());
        $this->view->render();
    }

    public function like()
    {
        $post_data = array(
            'type'       => trim(strip_tags($this->input->post('type', true))),
            'action'     => trim(strip_tags($this->input->post('action', true))),
            'profile_id' => intval($this->input->post('profile_id', true)),
        );

        $validate_data = $this->Like_me_model->validatePlayAction($post_data);
        if (!empty($validate_data['errors'])) {
            $this->view->assign('errors', $validate_data['errors']);
        } else {
            $like_id = $this->Like_me_model->savePlayAction($validate_data['data']);
            if (isset($like_id)) {
                if ($validate_data['data']['status_match'] == 0) {
                    $data['user'] = $this->_selAction($post_data);
                    $this->view->assign('match', false);
                    $this->view->assign('data', $post_data);
                } else {
                    $this->Like_me_model->changeStatus($validate_data['data']);
                    $this->_sendingMessage($validate_data['data']);
                    $this->view->assign('match', true);
                }
            }
        }

        $this->view->render();
    }

    /**
     *  Selected actions
     *
     *  @param array $action
     *
     *  @return array
     */
    private function _selAction($action = array(), $page = 1)
    {
        switch ($action['type']) {
            case 'play_local':
                $data = $this->_userListLocal($action);
                break;
            case 'matches':
                $data = $this->_userListMatches($page);
                break;
            default:
                $data = $this->_userListGlobal($action);
                break;
        }

        return $data;
    }

    /**
     * Play global list
     *
     * @param array $data
     *
     * @return array
     */
    private function _userListGlobal($data = array())
    {
        $params = $this->_getParams($data);
        $order = array('id' => 'ASC');
        $user_list = $this->Users_model->get_users_list(1, 1, $order, $params);
        $return = (!empty($user_list[0])) ? $user_list[0] : array();

        return $return;
    }

   /**
    * Play matches list
    *
    * @param array $data
    *
    * @return array
    */
   private function _userListMatches($page = 1)
   {
       $user_id = intval($this->session->userdata("user_id"));
       $count_data = $this->Like_me_model->getCountMatchesList($user_id);
       $items_on_page = $this->pg_module->get_module_config('like_me', 'matches_per_page');
       $this->load->helper('sort_order');
       $exists_page = get_exists_page_number($page, $count_data, $items_on_page);
       $next_page = get_exists_page_number($exists_page + 1, $count_data, $items_on_page);
       if ($next_page > $exists_page) {
           $user_list = array('have_more' => 1);
       }
       $user_list['content'] = $this->Like_me_model->getMatchesList($page, $items_on_page, $user_id);

       return $user_list;
   }

   /**
    * Play local list
    *
    * @param array $data
    *
    * @return array
    */
   private function _userListLocal($data = array())
   {
       $params = $this->_getParams($data);
       $area = $this->pg_module->get_module_config('like_me', 'play_local_area');
       $user_id = intval($this->session->userdata('user_id'));
       $this->load->model('Users_model');
       $user_data = $this->Users_model->get_user_by_id($user_id);
       $params['where'][$area] = $user_data[$area];
       $order = array('id' => 'ASC');
       $user_list = $this->Users_model->get_users_list(1, 1, $order, $params);
       $return = (!empty($user_list[0])) ? $user_list[0] : array();
       return $return;
   }

   /**
    * Send message
    *
    * @param array $data
    *
    * @return void
    */
   private function _sendingMessage($data = array())
   {
       $this->load->model('Users_model');
       $this->load->model('Notifications_model');
       $user = $this->Users_model->get_user_by_id($data['id_profile']);
       $alert = array(
            "user_nickname"    => $user['fname'] . " " . $user['sname'],
            "profile_nickname" => $this->session->userdata('output_name'),
        );
       $this->Notifications_model->send_notification($user['email'], 'like_me_overlap', $alert, '', $user['lang_id']);
   }

   /**
    * Querying
    *
    * @param array $data
    *
    * @return array
    */
   private function _getParams($data = array())
   {

        $params = array();

        $profile_ids = array();

        $user_id = intval($this->session->userdata('user_id'));

        $this->load->model('Users_model');

        if($this->pg_module->is_module_installed('perfect_match')){
            $this->load->model('Perfect_match_model');
            $search_data = $this->Perfect_match_model->getUserParams($user_id);
        } else {
            $search_data['full_criteria'] = array();
        }

        $params = $this->Users_model->get_common_criteria($search_data['full_criteria']);

        if (!empty($data['profile_id'])) {
            $liked = $this->session->userdata('like_me_selected');
            if (!is_array($liked)) {
                $liked = array();
            }
            $liked = array_unique(array_merge($liked, $profile_ids));
            if (!empty($liked)) {
                $params['where_sql'][] = "id NOT IN (" . implode(', ', $liked) . ")";
            }
            $params['where']['id >'] = intval($data['profile_id']);
            $params['where']['id !='] = intval($user_id);
        } else {
            $selected = array($user_id);
            $liked = $this->Like_me_model->getLikedProfileIds($user_id);
            $liked = array_merge($liked, $profile_ids);
            if (!empty($liked)) {
                $selected = array_unique(array_merge($liked, $selected));
            }
            $this->session->set_userdata(array('like_me_selected' => $selected));
            $params['where_sql'][] = "id NOT IN (" . implode(', ', $selected) . ")";
        }

       return $params;
   }
}
