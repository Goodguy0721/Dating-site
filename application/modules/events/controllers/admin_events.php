<?php

namespace Pg\Modules\Events\Controllers;

use Pg\Libraries\View;
use Pg\Modules\Events\Models\Events_model;

/**
 * Admin Events controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Alexey Chulkov <nsavanaev@pilotgroup.net>
 * */
class Admin_Events extends \Controller
{
    
    protected $default_date = '0000-00-00 00:00:00';
    
    /**
     * Controller
     *
     * @return Admin_Events
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Events_model');
        $this->load->model('Menu_model');
        $this->Menu_model->set_menu_active_item('admin_menu', 'add_ons_items');
    }
    
    private function getEventsSearchParams() 
    {
        return (array)$this->session->userdata("events");
    }
    
    private function setEventsSearchParams($data = array()) 
    {
        $this->session->set_userdata("events", $data);
    }
    
    private function addEventsSearchParams($data = array()) 
    {
        $events_settings = $this->getEventsSearchParams();
        $events_settings = array_merge($events_settings, $data);
        $this->setEventsSearchParams($events_settings);
    }

    /**
     *  List of events
     *
     *  @param string $type
     *  @param integer $page
     *
     *  @return void
     */
    public function index($filter = 'all', $order = 'name', $order_direction = "DESC", $page = 1)
    {
        $lang_id = $this->pg_language->current_lang_id;
        $events_settings = $this->getEventsSearchParams();

        if (!isset($events_settings["filter"])) {
            $events_settings["filter"] = $filter;
        }
        if (!isset($events_settings['order'])) {
            $events_settings['order'] = $order;
        }
        if (!isset($events_settings['order_direction'])) {
            $events_settings['order_direction'] = $order_direction;
        }
        if (!isset($events_settings['page'])) {
            $events_settings['page'] = $page;
        }

        $sort_links = array(
            'name'         => site_url() . 'admin/events/index/' . $filter . '/' . 'name' . '/' . (($order != 'name' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
            'category'     => site_url() . 'admin/events/index/' . $filter . '/' . 'category' . '/' . (($order != 'category' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
            'date_created' => site_url() . 'admin/events/index/' . $filter . '/' . 'date_created' . '/' . (($order != 'date_created' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
            'author'       => site_url() . 'admin/events/index/' . $filter . '/' . 'author' . '/' . (($order != 'author' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
        );
        $this->view->assign('sort_links', $sort_links);
        
        if (!$order) {
            $order = $events_settings['order'];
        }
        if (!$order_direction) {
            $order_direction = $events_settings['order_direction'];
        }
        if (!$page) {
            $page = $events_settings["page"];
        }
        
        $events_settings['order'] = $order;
        $events_settings['order_direction'] = $order_direction;
        $events_settings["page"] = $page;
        
        $this->view->assign('order', $order);
        $this->view->assign('order_direction', $order_direction);

        if($order == 'author') {
            $order = 'fk_user_id';
        }

        $attrs = $search_params = array();
        $filter_data["all"] = $this->Events_model->getEventsCount();
        $search_params["where"]["is_admin"] = 1;
        $filter_data["admin"] = $this->Events_model->getEventsCount($search_params);
        $search_params["where"]["is_admin"] = 0;
        $filter_data["users"] = $this->Events_model->getEventsCount($search_params);

        switch ($filter) {
            case 'admin' : $attrs["where"]['is_admin'] = 1;
                break;
            case 'users' : $attrs["where"]['is_admin'] = 0;
                break;
            case 'all' : break;
            default: $filter = $events_settings["filter"];
        }
        $events_settings["filter"] = $filter;

        $this->load->helper('sort_order');
        $items_on_page = $this->pg_module->get_module_config('start', 'admin_items_per_page');
        $events_count = $filter_data[$filter];
        $page = get_exists_page_number($page, $events_count, $items_on_page);
        $events_settings['page'] = $page;
        
        $this->setEventsSearchParams($events_settings);

        if ($events_count > 0) {
            $data = $this->Events_model->getListEvents($page, $items_on_page, array($order => $order_direction), $attrs, $lang_id, true);
            $this->view->assign('events', $data);
        }

        $this->view->assign('filter', $filter);
        $this->view->assign('filter_data', $filter_data);

        $this->load->helper('navigation');
        $page_data = get_admin_pages_data(site_url() . 'admin/events/index/' . $filter . '/' . $order . '/' . $order_direction . '/', $events_count, $items_on_page, $page, 'briefPage');
        $this->view->assign('page_data', $page_data);

        $this->load->library('pg_date');
        $date_format = $this->pg_date->get_format('date_time_literal', 'st');
        $this->view->assign('date_format', $date_format);

        $this->view->assign('current_lang_id', $lang_id);
        $this->view->setHeader(l('admin_header_list', 'events'));
        $this->view->render('list');
    }
    
    /**
     *  Add/Edit event
     *
     *  @param integer $id
     *
     *  @return void
     */
    public function edit_main($id = null)
    {
        $new_search_data = array(
            'type' => 'edit_main',
            'event_id' => $id
        );
        $this->addEventsSearchParams($new_search_data);

        if (is_null($id)) {
            $data = array();
            $this->system_messages->set_data('header', l('admin_header_create', 'events'));
        } else {
            $data = $this->Events_model->getEventById($id);
            if (empty($data['id'])) {
                $this->view->setRedirect(site_url() . "admin/events/index/");
            }
            $this->system_messages->set_data('header', l('admin_header_edit', 'events'));
        }

        $langs = $this->pg_language->languages;
        $lang_id = $this->pg_language->current_lang_id;
        if ($this->input->post('save')) {
            $post_data = array(
                'category' => $this->input->post('category'),
                'region_name' => $this->input->post('region_name'),
                'country_code' => $this->input->post('id_country'),
                'fk_region_id' => $this->input->post('id_region'),
                'fk_city_id' => $this->input->post('id_city'),
                'address' => $this->input->post('address'),
                'venue' => $this->input->post('venue'),
                'max_participants' => $this->input->post('max_participants'),
                'event_settings' => $this->input->post('event_settings'),
                'date_started' => $this->input->post('date_started'),
                'alt_date_started' => $this->input->post('alt_date_started'),
                'time_started' => $this->input->post('time_started'),
                'date_ended' => $this->input->post('date_ended'),
                'alt_date_ended' => $this->input->post('alt_date_ended'),
                'time_ended' => $this->input->post('time_ended'),
                'deadline_date' => $this->input->post('deadline_date'),
                'alt_deadline_date' => $this->input->post('alt_deadline_date'),
                'deadline_time' => $this->input->post('deadline_time')
            );

            foreach ($langs as $value) {
                $post_data['name_' . $value['id']] = $this->input->post('name_' . $value['id'], true);
                $post_data['description_' . $value['id']] = $this->input->post('description_' . $value['id'], true);
            }

            $validate_data = $this->Events_model->validateEvent($post_data);

            if (!empty($validate_data['errors'])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data["errors"]);
                $data = $validate_data['data'];
            } else {
                $this->load->model('countries/models/Countries_model');
                $city_arr = $this->Countries_model->get_city($validate_data['data']['fk_city_id']);
                if (!empty($city_arr)) {
                    $validate_data['data']['lat'] = $city_arr['latitude'];
                    $validate_data['data']['lon'] = $city_arr['longitude'];
                }

                if (is_null($id)) {
                    $validate_data['data']['fk_user_id'] = $this->session->userdata('user_id');
                    $validate_data['data']['is_admin'] = 1;
                }
                $validate_data['data']['status'] = 1;
                $id = $this->Events_model->saveEvent($id, $validate_data['data']);
                $this->Events_model->saveEventLogo($id);

                $event = $this->Events_model->getEventById($id);

                if ($event['album_id'] == 0) {
                    $this->load->model('media/models/Albums_model');
                    $this->load->model('media/models/Album_types_model');
                    $album_data['id_album_type'] = $this->Album_types_model->getTypeIdByGid($this->Events_model->album_type);
                    $album_data['name'] = 'event_album_' . $event['id'];
                    $album_data['id_user'] = $event['fk_user_id'];
                    if ($event['is_admin']) {
                        $album_data['id_user'] = 0;
                    }
                    
                    $album_id = $this->Albums_model->save(null, $album_data);

                    $this->Events_model->saveEvent($event['id'], array('album_id' => $album_id));
                }

                if (!empty($data)) {
                    $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_item_updated', 'events'));
                } else {
                    $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_item_created', 'events'));
                }

                $this->view->setRedirect(site_url() . "admin/events/edit_main/{$id}");
            }

            $data = array_merge($data, $post_data);
        }

        $this->view->assign('upload_gid', $this->Events_model->upload_gid);
        $this->view->assign('event_id', $id);
        $this->view->assign('event', $data);
        $this->view->assign('langs', $langs);
        $this->view->assign('current_lang_id', $lang_id);
        $this->view->setBackLink(site_url() . "admin/events/index");
        $this->view->render('edit');
    }
    
    /**
     *  Delete event
     *
     *  @param integer $id
     *
     *  @return void
     */
    public function delete($id = null)
    {
        $event = $this->Events_model->getEventById($id);
        $this->Events_model->sendDeleteEventMessage($event, true);
        
        $this->_delete(array($id));
    }

    /**
     *  Delete events
     *
     *  @param array $id
     *
     *  @return void
     */
    private function _delete($ids = array())
    {
        if (!empty($ids)) {
            foreach ($ids as $id) {
                $this->Events_model->delete($id);
            }
            $this->view->assign('success', l('success_item_delete', 'events'));
        }

        $this->view->setRedirect(site_url() . "admin/events/index");
    }

    /**
     *  Ajax delete event
     *
     *  @param integer $id
     *
     *  @return void
     */
    public function deleteSelect()
    {
        $post_data = $this->input->post('ids');
        $this->_delete($post_data);
    }
    
    /**
     *  Change the status of the event
     *
     *  @param integer $id
     *
     *  @return void
     */
    public function activate($id = null, $is_active = null)
    {
        if (!is_null($id)) {
            $attrs = array();
            $attrs['is_active'] = !empty($is_active) ? 1 : 0;
            $this->Events_model->saveEvent($id, $attrs);
            if ($is_active) {
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_activate', 'events'));
            } else {
                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_deactivate', 'events'));
            }
        }
        
        $this->view->setRedirect(site_url() . "admin/events/index/");
    }
    
    private function getUsersSearchParams() 
    {
        return (array)$this->session->userdata("participants");
    }
    
    private function setUsersSearchParams($data = array()) 
    {
        $this->session->set_userdata("participants", $data);
    }
    
    private function addUsersSearchParams($data = array()) 
    {
        $events_settings = $this->getUsersSearchParams();
        $events_settings = array_merge($events_settings, $data);
        $this->setUsersSearchParams($events_settings);
    }
    
    public function edit_participants($event_id = null, $filter = 'all', $order = 'response_date', $order_direction = 'DESC', $page = 1)
    {
        if (!$event_id) {
            $this->view->setRedirect(site_url() . "admin/events/edit_main");
        }

        $this->addEventsSearchParams(array('event_id' => $event_id));
        $participant_settings = $this->getUsersSearchParams();

        if (!isset($participant_settings["filter"])) {
            $participant_settings["filter"] = $filter;
        }
        if (!isset($participant_settings['order'])) {
            $participant_settings['order'] = $order;
        }
        if (!isset($participant_settings['order_direction'])) {
            $participant_settings['order_direction'] = $order_direction;
        }
        if (!isset($participant_settings['page'])) {
            $participant_settings['page'] = $page;
        }

        $sort_links = array(
            'name'          => site_url() . 'admin/events/edit_participants/' . $event_id . '/' . $filter . '/' . 'name' . '/' . (($order != 'name' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
            'status'        => site_url() . 'admin/events/edit_participants/' . $event_id . '/' . $filter . '/' . 'status' . '/' . (($order != 'status' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
            'response_date' => site_url() . 'admin/events/edit_participants/' . $event_id . '/' . $filter . '/' . 'response_date' . '/' . (($order != 'response_date' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
        );
        $this->view->assign('sort_links', $sort_links);

        $default_date = $this->default_date;
        $search_params = array();
        $search_params['where']['fk_event_id'] = $event_id;
        $attrs = $search_params;

        $filter_data["all"] = $this->Events_model->getParticipantsCount($search_params);
        $search_params["where"]["status"] = 'approved';
        $filter_data["approved"] = $this->Events_model->getParticipantsCount($search_params);
        $search_params['where_sql']['response_date'] = "response_date > '" . $default_date . "'";
        $search_params["where"]["status"] = 'pending';
        $filter_data["pending"] = $this->Events_model->getParticipantsCount($search_params);
        $search_params["where"]["status"] = 'declined';
        $filter_data["declined"] = $this->Events_model->getParticipantsCount($search_params);

        $this->view->assign('filter', $filter);
        $this->view->assign('filter_data', $filter_data);

        $this->addUsersSearchParams(array('filter' => $filter));

        switch ($filter) {
            case 'approved' :
                $attrs['where_sql']['response_date'] = "response_date > '" . $default_date . "'";
                $attrs["where"]['status'] = 'approved';
                break;
            case 'pending' :
                $attrs['where_sql']['response_date'] = "response_date > '" . $default_date . "'";
                $attrs["where"]['status'] = 'pending';
                break;
            case 'declined' :
                $attrs['where_sql']['response_date'] = "response_date > '" . $default_date . "'";
                $attrs["where"]['status'] = 'declined';
                break;
            case 'all' : break;
            default: $filter = $participant_settings["filter"];
        }

        $participants_count = $filter_data[$filter];

        $page = intval($page);
        $this->load->helper('sort_order');
        $items_on_page = $this->pg_module->get_module_config('start', 'admin_items_per_page');
        $page = get_exists_page_number($page, $participants_count, $items_on_page);
        $participant_settings['page'] = $page;

        $order_array = array();
        if ($participants_count > 0) {
            $data = $this->Events_model->getListParticipants($page, $items_on_page, array($order => $order_direction), $attrs);
            $this->view->assign('participants', $data);
        }
        
        $this->load->helper('navigation');
        $page_data = get_admin_pages_data(site_url() . 'admin/events/edit_participants/' . $event_id . '/' . $filter . '/' . $order . '/' . $order_direction . '/', $participants_count, $items_on_page, $page, 'briefPage');
        $this->view->assign('page_data', $page_data);

        $this->view->assign('participants_count', $participants_count);
        $this->view->assign('event_id', $event_id);

        $this->view->setHeader(l('admin_header_edit_participants', 'events'));
        $this->view->setBackLink(site_url() . "admin/events/index");

        $this->view->render('edit_participants');
    }

    public function requests($id = null, $type = 'index', $page = null, $order = null, $order_direction = null)
    {
        $lang_id = $this->pg_language->current_lang_id;
        $_SESSION["events"]['type'] = 'requests';
        $params = array();
        $params['where']['status'] = 'pending';
        $page = intval($page);
        $this->load->helper('sort_order');
        $items_on_page = $this->pg_module->get_module_config('start', 'admin_items_per_page');
        $participants_count = $this->Events_model->getParticipantsCount($params);
        $page = get_exists_page_number($page, $participants_count, $items_on_page);
        $events_settings['page'] = $page;
        $this->view->assign('participants_count', $participants_count);

        $order_array = array();
        if ($participants_count > 0) {
            $data = $this->Events_model->getListParticipants($page, $items_on_page, $order_array, $params);
            $this->view->assign('participants', $data);
        }

        if ($this->input->post('save')) {
            $post_data = array();
            $post_data['fk_user_id'] = $this->input->post('id_user');
            $post_data['fk_event_id'] = $id;

            $id = $this->Events_model->saveParticipant($post_data);

            $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_item_created', 'events'));

            $url = site_url() . "admin/events/edit_participants/" . $id;
            redirect($url);
        }

        $this->load->helper('navigation');
        $page_data = get_admin_pages_data(site_url() . 'admin/events/' . $type . '/' . $order . '/' . $order_direction . '/', $participants_count, $items_on_page, $page, 'briefPage');
        $this->view->assign('page_data', $page_data);

        $this->view->assign('current_lang_id', $lang_id);
        $this->view->assign('event_name', 'name_' . $lang_id);
        $this->view->assign('event_id', $id);

        $this->system_messages->set_data('header', l('admin_header_requests', 'events'));
        $this->system_messages->set_data('back_link', site_url() . "admin/start/menu/add_ons_items");
        $this->view->render('requests');
    }

    public function add_participants($event_id = null, $order = "nickname", $order_direction = "DESC", $page = 1)
    {
        $_SESSION["events"]['type'] = 'add_participants';
        $_SESSION["events"]['event_id'] = $event_id;
        $cur_set = $_SESSION["events"];
        
        $search_params = array();
        $search_params["where"]['approved'] = 1;
        $attrs = $search_params;

        if ($this->input->post('save')) {
            $url = site_url() . "admin/events/edit_participants/" . $event_id;
            if (!$this->input->post('id_user')) {
                redirect($url);
            }

            $users_ids = $this->input->post('id_user', true);
            $users_setting = array(
                'is_invite'     => 1,
                'response_date' => date('Y-m-d H:i:s'),
                'status'        => 'pending',
            );
            
            $this->Events_model->saveAllParticipants($event_id, $users_ids, $users_setting);
            $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_participant_added', 'events'));
            redirect($url);
        }

        $event = $this->Events_model->getEventById($event_id);
        $current_users = $this->Events_model->getListParticipantsIds($event_id);
        if(!$event['is_admin']) {
            $current_users[] = $event['fk_user_id'];
        }
        
        if (count($current_users) > 0) {
            $attrs['where_sql']['id'] = "id NOT IN (" . implode(", ", $current_users) . ")";
        }

        $current_settings = isset($_SESSION["users_list"]) ? $_SESSION["users_list"] : array();

        if (!isset($current_settings["order"])) {
            $current_settings["order"] = $order;
        }
        if (!isset($current_settings["order_direction"])) {
            $current_settings["order_direction"] = $order_direction;
        }
        if (!isset($current_settings["page"])) {
            $current_settings["page"] = $page;
        }

        $user_type = 'all';
        if ($this->input->post('btn_search', true)) {
            $user_type = $this->input->post('user_type', true);
            $current_settings["search_type"] = $this->input->post('type_text', true);
            $current_settings["search_text"] = $this->input->post('val_text', true);
        }
        $current_settings["user_type"] = $user_type;

        if (isset($current_settings["search_text"])) {
            $search_text_escape = $this->db->escape("%" . $current_settings["search_text"] . "%");
            if ($current_settings["search_type"] != 'all') {
                $attrs["where_sql"][] = $search_params["where_sql"][] = $current_settings["search_type"] . " LIKE " . $search_text_escape;
            } else {
                $attrs["where_sql"][] = $search_params["where_sql"][] = "(nickname LIKE " . $search_text_escape . " OR fname LIKE " . $search_text_escape . " OR sname LIKE " . $search_text_escape . " OR email LIKE " . $search_text_escape . ")";
            }
        }

        if ($user_type != 'all' && $user_type) {
            $attrs["where"]["user_type"] = $search_params["where"]["user_type"] = $user_type;
        }
        
        $users_count = $this->Users_model->get_users_count($attrs);
        
        $search_param = array(
            'text' => isset($current_settings["search_text"]) ? $current_settings["search_text"] : '',
            'type' => isset($current_settings["search_type"]) ? $current_settings["search_type"] : '',
        );

        $this->load->model('Properties_model');
        $user_types = $this->Properties_model->get_property('user_type');
        $this->view->assign('user_types', $user_types);

        $this->view->assign('search_param', $search_param);
        $this->view->assign('user_type', $user_type);

        $current_settings["page"] = $page;

        if (!$order) {
            $order = $current_settings["order"];
        }
        $this->view->assign('order', $order);
        $current_settings["order"] = $order;

        if (!$order_direction) {
            $order_direction = $current_settings["order_direction"];
        }
        $this->view->assign('order_direction', $order_direction);
        $current_settings["order_direction"] = $order_direction;

        if (!$page) {
            $page = $current_settings["page"];
        }
        $items_on_page = $this->pg_module->get_module_config('start', 'admin_items_per_page');
        $this->load->helper('sort_order');
        $page = get_exists_page_number($page, $users_count, $items_on_page);
        $current_settings["page"] = $page;

        $this->load->model("Users_model");

        if ($users_count > 0) {
            $users = $this->Users_model->get_users_list($page, $items_on_page, array($order => $order_direction), $attrs);
            $this->view->assign('users', $users);
        }
        
        $sort_links = array(
            'nickname'         => site_url() . 'admin/events/add_participants/' . $event_id . '/' . 'nickname' . '/' . (($order != 'nickname' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
            'email'     => site_url() . 'admin/events/add_participants/' . $event_id . '/' . 'email' . '/' . (($order != 'email' xor $order_direction == 'DESC') ? 'ASC' : 'DESC'),
        );
        $this->view->assign('sort_links', $sort_links);

        $this->load->helper("navigation");
        $url = site_url() . "admin/events/add_participants/{$event_id}/{$order}/{$order_direction}/";
        $page_data = get_admin_pages_data($url, $users_count, $items_on_page, $page, 'briefPage');
        $page_data["date_format"] = $this->pg_date->get_format('date_time_literal', 'st');
        $this->view->assign('page_data', $page_data);

        $this->view->setHeader(l('admin_header_users_list', 'users'));
        $this->view->setBackLink(site_url() . "admin/events/edit_participants/{$event_id}");
        $this->view->render('add_participants');
    }

    public function media_list($event_id = null, $param = 'photo', $page = 1)
    {
        if(!$event_id) {
            $this->view->setRedirect(site_url() . "admin/events/edit_main");
        }
        
        $where = array();
        $this->load->model('media/models/Media_model');
        $this->load->model('media/models/Media_album_model');
        $this->Media_model->initialize($this->Events_model->album_type);
        switch ($param) {
            case 'photo' : $where['where']['upload_gid'] = $this->Media_model->file_config_gid;
                break;
            case 'video' : $where['where']['upload_gid'] = $this->Media_model->video_config_gid;
                break;
        }

        $event = $this->Events_model->getEventById($event_id);

        $media_ids = $this->Media_album_model->get_media_ids_in_album($event['album_id']);
        $this->load->helper('sort_order');
        $items_on_page = $this->pg_module->get_module_config('media', 'items_per_page');
        $media_count = count($media_ids);
        $page = get_exists_page_number($page, $media_count, $items_on_page);

        if ($media_count) {
            $order_by = array('date_add' => 'DESC');
            $this->Media_model->format_user = true;
            $media = $this->Media_model->get_media($page, $items_on_page, $order_by, $where, $media_ids);
            $this->view->assign('media', $media);
        }

        $this->load->helper("navigation");
        $page_data = get_admin_pages_data(site_url() . "admin/events/media_list/" . $event_id . '/' . $param . '/', $media_count, $items_on_page, $page, 'briefPage');
        $this->view->assign('page_data', $page_data);

        $this->view->assign('param', $event_id . "/" . $param . "/" . $page);
        $this->view->assign('event_id', $event_id);
        $this->view->assign('active', $param);

        $this->Menu_model->set_menu_active_item('media_menu_item', $param . '_list_item');
        $this->view->setHeader(l('admin_header_media_list', 'media'));
        $this->view->setBackLink(site_url() . "admin/events/index");
        $this->view->render('media_list');
    }

    public function edit_album($id, $param = 'photo', $page = 1)
    {
        $_SESSION["events"]['type'] = 'edit_album';
        if (!$id) {
            $this->view->setRedirect(site_url() . "admin/events/edit_main");
        }


        $event = $this->Events_model->getEventById($id);
        if ($this->input->post('save')) {
            $this->Events_model->saveMedia($event);
            redirect(site_url() . "admin/events/media_list/{$id}");
        }

        $this->view->assign('images_block', $this->getEventMedia($event['album_id']));

        $this->load->model('Uploads_model');
        $this->load->model('media/models/Album_types_model');
        $album_type = $this->Album_types_model->get_album_type_by_gid($this->Events_model->album_type);

        $photo_config = $this->Uploads_model->get_config($album_type['gid_upload_type']);
        $this->view->assign('photo_config', $photo_config);

        $video_config = $this->Uploads_model->get_config($album_type['gid_upload_video']);
        $this->view->assign('video_config', $video_config);

        $this->view->assign('event_id', $id);
        $this->view->setHeader(l('admin_header_edit_album', 'events'));
        $this->view->setBackLink(site_url() . "admin/events/media_list/{$id}");
        $this->view->render('edit_album');
    }

    private function getEventMedia($album_id)
    {
        $this->view->assign('media', $this->Events_model->getMedia($album_id));

        return $this->view->fetch('images_block');
    }

    public function ajaxSaveMedia($type = 'photo', $event_id)
    {
        $return = array('errors' => array(), 'warnings' => array(), 'name' => '');

        if ($type == 'photo') {
            $event = $this->Events_model->getEventById($event_id);
            $save_data = $this->Events_model->saveMedia($event, $type);
        }

        if (empty($save_data['errors'])) {
            if (!empty($save_data['file'])) {
                $return['name'] = $save_data['file'];
            }
        } else {
            $return['errors'][] = $save_data['errors'];
        }

        exit(json_encode($return));
    }

    public function ajaxGetEventPhotos($event_id, $param = 'all', $size = 'small')
    {
        $event = $this->Events_model->getEventById($event_id);
        $return['content'] = $this->getEventMedia($event['album_id']);
        exit(json_encode($return));
    }

    public function edit_map($id = null)
    {
        $_SESSION["events"]['type'] = 'edit_map';
        $_SESSION["events"]['event_id'] = $id;
        $cur_set = $_SESSION["events"];
        $url = site_url() . "admin/events/{$cur_set["type"]}/{$cur_set["order"]}/{$cur_set["order_direction"]}/{$cur_set["page"]}";
        if (is_null($id)) {
            $url = site_url() . "admin/events/edit_main";
            redirect($url);
        }

        if ($this->input->post('save')) {
            $post_data = array();
            $post_data['lat'] = $this->input->post('lat');
            $post_data['lon'] = $this->input->post('lon');
            $id = $this->Events_model->saveEvent($id, $post_data);

            if (!empty($data)) {
                $message = l('success_item_updated', 'events');
            } else {
                $message = l('success_item_created', 'events');
            }

            $this->view->assign('success', $message);
            $url = site_url() . "admin/events/edit_map/" . $id;
            redirect($url);
        }

        $this->load->model('geomap/models/Geomap_model');
        $this->load->model('geomap/models/Geomap_settings_model');
        $geomap_driver = $this->Geomap_model->get_default_driver();

        $params = array();
        $params['id'] = intval($id);
        $event = $this->Events_model->getEvent($params);
        $this->view->assign('event_id', $id);
        $this->view->assign('event', $event);

        $markers[] = array(
            'gid'      => $id,
            'lat'      => (float) $event['lat'],
            'lon'      => (float) $event['lon'],
            "dragging" => true,
        );
        $current_language = $this->pg_language->get_lang_by_id($this->pg_language->current_lang_id);
        $view_settings = array(
            "zoom_listener" => "get_zoom_data",
            "type_listener" => "get_type_data",
            "drag_listener" => "get_drag_data",
            "lang"          => $current_language["code"],
        );

        $this->view->assign('markers', $markers);
        $this->view->assign('view_settings', $view_settings);
        $this->view->assign('geomap_driver', $geomap_driver);

        $this->system_messages->set_data('header', l('admin_header_edit_map', 'events'));
        $this->system_messages->set_data('back_link', site_url() . "admin/events/index");
        $this->view->render('edit_map');
    }

    /**
     *  Change the status of the participant
     *
     *  @param integer $id
     *
     *  @return void
     */
    public function participant_status($event_id = null, $user_id = null, $status = 'pending')
    {
        $attrs = array();
        $attrs['status'] = $status;
        $url = site_url() . "admin/events/edit_participants/{$event_id}/pending";
        
        
        if($status == $this->Events_model->participant_statuses['approve_gid']) {
            $event = $this->Events_model->getEventSettins($event_id);
            $settings['participants_count'] = $this->Events_model->getApprovedUsersCount($event_id);
            $settings['participants_out'] = $this->Events_model->isFreeSpots($event['max_participants'], $settings['participants_count']);   
            
            if(!$settings['participants_out']) {
                $this->system_messages->addMessage(View::MSG_ERROR, l('error_max_participants_limit', 'events'));
                $this->view->setRedirect($url);
            }
        }

        
        $this->Events_model->saveParticipantStatus($event_id, $user_id, $attrs);
        $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_participant_status_change', 'events'));

        $this->view->setRedirect($url);
    }

    /**
     *  Delete event
     *
     *  @param integer $id
     *
     *  @return void
     */
    public function deleteParticipant($id = null)
    {
        $this->_delete_participant(array($id));
    }

    /**
     *  Delete events
     *
     *  @param array $id
     *
     *  @return void
     */
    private function _delete_participant($ids = array())
    {
        $cur_set = $this->getEventsSearchParams();
        $cur_set_users = $this->getUsersSearchParams();
        if (!empty($ids)) {
            foreach ($ids as $id) {
                $participant = $this->Events_model->getParticipant($id);
                if(!empty($participant)) {
                    $this->Events_model->deleteParticipant($id);
                    $this->Events_model->sendNotificationExclude($participant['fk_user_id'], $cur_set["event_id"]);                    
                }
            }
            $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_participant_delete', 'events'));
        }

        $this->view->setRedirect(site_url() . "admin/events/edit_participants/{$cur_set["event_id"]}/{$cur_set_users["filter"]}");
    }

    /**
     *  Ajax delete event
     *
     *  @param integer $id
     *
     *  @return void
     */
    public function deleteParticipantSelect()
    {
        $post_data = $this->input->post('ids');
        $this->_delete_participant($post_data);
    }

    public function deleteImage($image_id)
    {
        if (!empty($image_id)) {
            $this->load->model('media/models/Media_model');
            $this->Media_model->initialize($this->Events_model->album_type);

            $this->Media_model->delete_media($image_id);
            $this->view->assign('success', l('success_item_delete', 'events'));
        }
        $cur_set = $_SESSION["events"];
        $url = site_url() . "admin/events/{$cur_set["type"]}/{$cur_set["event_id"]}";
        redirect($url);
    }

    public function deleteImages()
    {
        $ids = $this->input->post('ids');
        if (!empty($ids)) {
            $this->load->model('media/models/Media_model');
            $this->Media_model->initialize($this->Events_model->album_type);
            foreach ($ids as $id) {
                $this->Media_model->delete_media($id);
            }
            $this->view->assign('success', l('success_item_delete', 'events'));
        }
        $cur_set = $_SESSION["events"];
        $url = site_url() . "admin/events/{$cur_set["type"]}/{$cur_set["event_id"]}";
        redirect($url);
    }

    public function remindParticipant($user_id = null)
    {
        $event_id = $_SESSION["events"]["event_id"];
        if ($this->pg_module->is_module_active("tickets")) {
            redirect(site_url() . "admin/tickets/answer/" . $user_id);
        } else {
            $this->load->model("users/models/Users_model");
            $this->load->model('notifications/models/Templates_model');
            
            $user_data = $this->Users_model->get_user_by_id($user_id, true);
            $event_data = $this->Events_model->getEventById($event_id);
            $template_data = array(
                'email' => $user_data['email'],
                'nickname' => $user_data['nickname'],
                'event_name' => $event_data['name'],
                'event_link' => site_url() . "events/view/" . $event_id
            );

            $template_content = $this->Templates_model->compile_template('events_remind_participant', $template_data, $user_data['lang_id']);

            
            if ($this->input->post('btn_save')) {
                $post_data = array(
                                    "email"   => $this->input->post('email', true),
                                    "subject" => $this->input->post('subject', true),
                                    "content" => $this->input->post('content', true),
                            );
                
                $template_data = $this->Templates_model->get_template_by_id($template_content['id_template']);

                $this->load->model('notifications/models/Sender_model');
                $status = $this->Sender_model->send_letter($post_data["email"], $post_data["subject"], $post_data["content"], $template_data["content_type"]);

                $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_message_send', 'mailbox'));

                redirect(site_url() . "admin/events/edit_participants/" . $event_id);
            }

            $this->view->setHeader(l('admin_header_notification', 'incomplete_signup'));

            $this->view->assign('participant_id', $user_id);
            $this->view->assign('event_id', $event_id);
            $this->view->assign('email', $user_data['email']);
            $this->view->assign('template', $template_content);
            $this->Menu_model->set_menu_active_item('admin_menu', 'users_menu_item');

            $this->view->render('send_notification');
        }
        //$url = site_url() . "admin/events/{$cur_set["type"]}/{$cur_set["event_id"]}";
        //redirect($url);
    }
    
    public function ajaxConfirmSelect()
    {
        $action = $this->input->post('action', true);
        $this->view->assign('action', $action);
        $return['content'] = $this->view->fetch('ajax_delete_select_block');

        $this->view->assign($return);
        $this->view->render();
    }
    
    public function ajaxDeleteSelectUsers() {
        $users_id = $this->input->post("items_ids", true);
        if (!empty($users_id)) {
            foreach ($users_id as $object_id) {
                $this->Events_model->deleteParticipant($object_id);
            }
        }

        $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_delete_participant', 'events'));        
    }
    
    public function ajaxDeleteSelectEvents() {
        $users_id = $this->input->post("items_ids", true);
        if (!empty($media_id)) {
            foreach ($media_id as $object_id) {
                $this->delete_media($object_id, false);
            }
        }

        $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_delete_media', 'media'));        
    }
    
    /**
     * Settings module
     *
     * @return string
     */
    public function settings()
    {
        $data = $this->Events_model->getSettings();
        if ($this->input->post('btn_save')) {
            $post_data = array(
                'is_active' => $this->input->post('is_active', true),
            );
            $validate_data = $this->Events_model->validateSettings($post_data);
            if (!empty($validate_data["errors"])) {
                $this->view->assign('errors', $validate_data['errors']);
            } else {
                $this->Events_model->setSettings($validate_data["data"]);
                $this->Events_model->setMessageFields($validate_data["ds"]);
                $this->system_messages->add_message(View::MSG_SUCCESS, l('success_settings_save', 'events'));
                $this->view->setRedirect(site_url() . "admin/events/settings");
            }
        }
        $this->view->assign('data', $data);
        
        $this->view->setHeader(l('admin_header_settings', 'events'));
        $this->view->render('settings');
    }
}
