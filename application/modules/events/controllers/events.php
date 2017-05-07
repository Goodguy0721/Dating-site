<?php

namespace Pg\Modules\Events\Controllers;

use Pg\Libraries\View;

/**
 * Events controller
 *
 * @package PG_Dating
 * @subpackage application
 *
 * @category	modules
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Maxim Kislitsyn <mkislitsyn@pilotgroup.net>
 * */
class Events extends \Controller
{
    protected $event_date_format = '%a, %b %d';
    protected $event_time_format = '%H:%M';
    
    private $view_types = array(
        'list_gid' => 'list',
        'calendar_gid' => 'calendar',
        'map_gid' => 'map',
    );
    
    public $participant_statuses = array();

    /**
     * Constructor
     *
     * @return Events
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Events_model');
        $this->participant_statuses = $this->Events_model->participant_statuses;
    }
    
    public function index() {
        $this->view->setRedirect(site_url() . 'events/search/');
    }
    
    public function search($search_type = 'upcoming', $order = "default", $order_direction = "DESC", $page = 1)
    {
        $data = $this->getSearchData();
        
        $this->view->assign('block', $this->searchListBlock($data, $order, $order_direction, $page, $search_type));
        $this->view->assign('view_type', $this->getCurrentViewType());
        $this->view->assign('view_types', $this->view_types);
        $this->view->assign('search_type', $search_type);
        $this->view->assign('page_data', $data);
        $this->view->assign('header_text', l('text_' . $search_type, 'events'));
        
        $this->Menu_model->breadcrumbs_set_parent('user_events_item');
        $this->view->render('events_list');
    }
    
    private function getCurrentViewType() {
        $view = $this->session->userdata("events_view_mode");
        return $view ? $view : $this->view_types['list_gid'];
    }
    
    public function setViewType($type)
    {
        $view_types = $this->view_types;
        if (in_array($type, $view_types)) {
            $this->session->set_userdata('events_view_mode', $type);
        }
    }
    
    private function getSearchData() {
        
        $data = array(
            'category' => $this->input->post('category', true),
            'date_started_from' => $this->input->post('date_started_from', true),
            'date_started_to' => $this->input->post('date_started_to', true),
            'search' => $this->input->post('search', true),
        );
        
        return $data;
        //return (array)$this->session->userdata("events_search");
    }
    
    private function setSearchData($data = array()) {
        $this->session->set_userdata("events_search", $data);
    }

    private function searchListBlock($data = array(), $order = "default", $order_direction = "DESC", $page = 1, $search_type = 'upcoming', $calendar_date = null)
    {
        $user_id = $this->session->userdata('user_id');
        $lang_id = $this->pg_language->current_lang_id;

        //search settings
        if (!empty($data)) {
            $current_settings = $data;
        } else {
            $current_settings = array();//$this->getSearchData();
        }
        
        $view_type = $this->getCurrentViewType();
        if($view_type == $this->view_types['calendar_gid'] && $calendar_date) {
            $date = new \DateTime($calendar_date);
            $data['date_started_from'] = $date->format( 'Y-m-01' );
            $data['date_started_to'] = $date->format( 'Y-m-t' );
        }
        
        $this->setSearchData($current_settings);

        $criteria = array();
        $search_criteria = $this->Events_model->getSearchCriteria($current_settings);
        $advanced_criteria = $this->Events_model->getAdvancedCriteria($search_type, $current_settings);
        $criteria = array_merge_recursive($search_criteria, $advanced_criteria);
        $criteria = $this->getCriteria($search_type, $criteria);

        $events = array();
        if (!$criteria['empty']) {
            $items_count = $this->Events_model->getEventsCount($criteria);
            
            if ($items_count > 0) {
                $items_on_page = $items_count;
                if ($view_type == $this->view_types['list_gid']) {
                    $items_on_page = $this->pg_module->get_module_config('start', 'index_items_per_page');
                }
                
                $this->load->helper('sort_order');
                $page = get_exists_page_number($page, $items_count, $items_on_page);
                $events = $this->Events_model->getListEvents($page, $items_on_page, array(), $criteria, $lang_id);
                $this->load->helper("navigation");
                $page_data = get_user_pages_data(site_url() . "events/search/" . $search_type . "/" . $order . "/" . $order_direction . "/", $items_count, $items_on_page, $page, 'briefPage');
            }
            
            if($view_type == $this->view_types['calendar_gid']) {
                $current_month = $this->input->post('month', true);
                $current_month = $current_month ? $current_month : 0;

                $calendar = $this->getCallendar($calendar_date);
                if($items_count > 0) {
                    foreach($calendar['date'] as $item => $date) {
                        if(!isset($date['other_month'])) {
                            $date_time = strtotime($date['full_date']);
                            foreach($events as $key => $event) {
                                $date_started = strtotime($event['date_started']);
                                $date_ended = strtotime($event['date_ended']);

                                if($date_time >= $date_started && $date_time <= $date_ended) {
                                    $calendar['date'][$item]['events'][] = $events[$key];
                                }
                            }

                        }
                    }                    
                }
                
                $this->view->assign('month', $current_month);
                $this->view->assign('calendar', $calendar);  
            }
            $this->view->assign('events', $events);
        } else {
            if($view_type == $this->view_types['calendar_gid']) {
                $current_month = $this->input->post('month', true);
                $current_month = $current_month ? $current_month : 0;

                $calendar = $this->getCallendar($calendar_date);
                
                $this->view->assign('month', $current_month);
                $this->view->assign('calendar', $calendar);  
            }
        }

        $page_data["date_format"] = $this->pg_date->get_format('date_literal', 'st');
        $page_data["date_time_format"] = $this->pg_date->get_format('date_time_literal', 'st');
        $page_data['event_date_format'] = $this->event_date_format;
        $page_data['event_time_format'] = $this->event_time_format;
        $this->view->assign('page_data', $page_data);

        $this->view->assign('search_type', $search_type);
        $this->view->assign('data', $current_settings);

        //categories list
        $this->load->helper('events');
        $options = get_events_categories(true);
        $this->view->assign('category_options', $options);

        $use_save_search = ($this->session->userdata("auth_type") == "user") ? true : false;
        $this->view->assign('use_save_search', $use_save_search);
        $this->view->assign('module_settings', $this->Events_model->getSettings());

        $this->view->assign('user_id', $user_id);
        return $this->view->fetch('events_' . $view_type . '_block');
    }

    public function ajaxSearchEvents($search_type = 'upcoming')
    {
        $return = array();
        $data = $this->getSearchData();

        $return['content'] = $this->searchListBlock($data, null, null, null, $search_type);

        $this->view->assign($return);
        $this->view->render();
    }

    public function ajaxSearchUsers($type = "search", $order = "default", $order_direction = "DESC", $page = 1)
    {
        $this->load->model("Users_model");

        $data = array(
            'age_max' => $this->input->post('age_max', true),
            'age_min' => $this->input->post('age_min', true),
            'id_city' => $this->input->post('id_city', true),
            'id_country' => $this->input->post('id_country', true),
            'id_region' => $this->input->post('id_region', true),
            'region_name' => $this->input->post('region_name', true),
            'user_type' => $this->input->post('user_type', true),
        );
        
        $data = array_merge($this->Users_model->get_minimum_search_data(), $data);

        echo $this->searchUsersListBlock($data, $order, $order_direction, $page, 'advanced');
    }

    public function searchUsersListBlock($data = array(), $order = "default", $order_direction = "DESC", $page = 1, $search_type = 'advanced')
    {
        $this->load->model('Users_model');

        $current_settings = $data;
        $criteria = $this->getAdvancedSearchCriteria($current_settings);
        
        $search_params = $this->getUsersSearchParams();
        $event = $this->Events_model->getEventById($search_params['event_id']);
        if (isset($event['id'])) {
            $participants_ids = $this->Events_model->getListParticipantsIds($event['id']);
            
            $excluded_ids[] = $event['fk_user_id'];
            $excluded_ids[] = $this->session->userdata('user_id');
            $criteria['where_sql'][] = "id NOT IN (" . implode(',', $excluded_ids) . ")";
            
            $this->view->assign('event_id', $event['id']);
        }
        

        $search_url = site_url() . "users/search";
        $url        = site_url() . "users/search/" . $order . "/" . $order_direction . "/";

        $this->view->assign('search_type', $search_type);
        $order = trim(strip_tags($order));
        if (!$order) {
            $order = "date_created";
        }
        $this->view->assign('order', $order);

        $order_direction = strtoupper(trim(strip_tags($order_direction)));
        if ($order_direction != 'DESC') {
            $order_direction = "ASC";
        }
        $this->view->assign('order_direction', $order_direction);

        $items_count = $this->Users_model->get_users_count($criteria);

        if (!$page) {
            $page = 1;
        }
        $items_on_page = $this->pg_module->get_module_config('users', 'items_per_page');
        $this->load->helper('sort_order');
        $page          = get_exists_page_number($page, $items_count, $items_on_page);

        $sort_data = array(
            "url"       => $search_url,
            "order"     => $order,
            "direction" => $order_direction,
            "links"     => array(
                "default"      => l('field_default_sorter', 'users'),
                "name"         => l('field_name', 'users'),
                "views_count"  => l('field_views_count', 'users'),
                "date_created" => l('field_date_created', 'users'),
            ),
        );
        $this->view->assign('sort_data', $sort_data);

        $use_leader = false;
        if ($items_count > 0) {
            $order_array = array();
            if ($order == 'default') {
                if (!empty($data['id_region']) && intval($data['id_region'])) {
                    $order_array['leader_bid'] = 'DESC';
                }
                if (!empty($criteria['fields']) && intval($criteria['fields'])) {
                    $order_array["fields"] = 'DESC';
                } else {
                    $order_array["up_in_search_end_date"] = 'DESC';
                    $order_array["date_created"]          = $order_direction;
                }
                $use_leader = true;
            } else {
                if ($order == 'name') {
                    if ($this->pg_module->get_module_config('users', 'hide_user_names')) {
                        $order_array['nickname'] = $order_direction;
                    } else {
                        $order_array['fname'] = $order_direction;
                        $order_array['sname'] = $order_direction;
                    }
                } else {
                    $order_array[$order] = $order_direction;
                }
            }

            $lang_id = $this->pg_language->current_lang_id;
            $users     = $this->Users_model->get_users_list($page, $items_on_page, $order_array, $criteria, array(), true, false, $lang_id);
            foreach($users as $key => $user) {
                if(in_array($user['id'], $participants_ids)) {
                    $users[$key]['participant'] = true; 
                }
            }

            $this->view->assign('users', $users);
        }

        $this->load->helper("navigation");
        $page_data                     = get_user_pages_data($url, $items_count, $items_on_page, $page, 'briefPage');
                
        $page_data["date_format"]      = $this->pg_date->get_format('date_literal', 'st');
        $page_data["date_time_format"] = $this->pg_date->get_format('date_time_literal', 'st');
        $page_data["use_leader"]       = $use_leader;
        
        $this->view->assign('page_data', $page_data);

        $use_save_search = ($this->session->userdata("auth_type") == "user") ? true : false;
        $this->view->assign('use_save_search', $use_save_search);

        $pm_installed = $this->pg_module->is_module_installed('perfect_match');
        $this->view->assign('pm_installed', $pm_installed);

        return $this->view->fetch('invite_users_block');
    }

    private function getAdvancedSearchCriteria($data)
    {
        $this->load->model('Users_model');
        $this->load->model('field_editor/models/Field_editor_forms_model');
        $fe_criteria = $this->Field_editor_forms_model->get_search_criteria($this->Users_model->advanced_search_form_gid, $data, $this->Users_model->form_editor_type, false);

        if (!empty($data["search"])) {
            $data["search"] = trim(strip_tags($data["search"]));
            $this->load->model('Field_editor_model');
            $this->Field_editor_model->initialize($this->Users_model->form_editor_type);
            if (strlen($data["search"]) > 3) {
                $temp_criteria              = $this->Field_editor_model->return_fulltext_criteria($data["search"], 'BOOLEAN MODE');
                $fe_criteria['fields'][]    = $temp_criteria['user']['field'];
                $fe_criteria['where_sql'][] = $temp_criteria['user']['where_sql'];
            } else {
                $search_text_escape         = $this->db->escape($data["search"] . "%");
                $fe_criteria['where_sql'][] = "(nickname LIKE " . $search_text_escape . ")";
            }
        }
        $common_criteria   = $this->Users_model->get_common_criteria($data);
        $advanced_criteria = $this->Users_model->get_advanced_search_criteria($data);

        $criteria          = array_merge_recursive($fe_criteria, $common_criteria, $advanced_criteria, $data);

        return $criteria;
    }

    private function getCallendar($date = false)
    {
        if (!$date) {
            $date = date('Y-m-d');
        }

        $callendar = array();
        $calendar['current_date_format'] = $this->pg_date->strftime('%B, %Y', strtotime($date));
        $calendar['months'] = ld('week_day_short', 'start');

        $current_date = explode('-', $date);
        $calendar['year'] = $current_date[0];
        $calendar['month'] = $current_date[1];
        $calendar['day'] = $current_date[2];

        $calendar['q_days'] = date('t', strtotime($date));

        //previous month
        $start_date = strtotime($calendar['year'] . '-' . $calendar['month'] . '-' . '01');
        $start_shift = date('w', $start_date);
        if ($start_shift == 0) {
            $start_shift = 7;
        }
        if ($start_shift) {
            for ($i = 1; $i < $start_shift; ++$i) {
                $day = date('d', strtotime(($i - $start_shift) . " day", $start_date));

                $calendar['date'][] = array(
                    'day'         => $day,
                    'other_month' => true,
                );
            }
        }

        //current month
        $calendar['current_day'] = date('Y-m-j');
        for ($i = 1; $i <= $calendar['q_days']; ++$i) {
            $full_date = $calendar['year'] . '-' . $calendar['month'] . '-' . $i;

            $calendar['date'][] = array(
                'full_date' => $full_date,
                'day'       => $i,
                'current'   => $calendar['current_day'] == $full_date ? true : false,
            );
        }

        //next month
        $end_date = strtotime($calendar['year'] . '-' . $calendar['month'] . '-' . $calendar['q_days']);
        $end_shift = date('w', $end_date);
        if ($end_shift) {
            $end_shift = 7 - $end_shift;

            for ($i = 1; $i <= $end_shift; ++$i) {
                $day = date('d', strtotime("+ " . $i . " day", $end_date));

                $calendar['date'][] = array(
                    'day'         => $day,
                    'other_month' => true,
                );
            }
        }

        return $calendar;
    }

    public function ajaxChangeMonth()
    {
        $month = $this->input->post('month', true);
        $search_type = $this->input->post('search_type', true);

        if ($month) {
            if ($month > 0) {
                $format = "+ " . $month . " month";
            } else {
                $format = $month . " month";
            }
        } else {
            $format = "this month";
        }

        
        $date = new \DateTime();
        $date->modify( 'first day of ' . $format );
        $calendar_date =  $date->format( 'Y-m-d' );

        $return['content'] = $this->searchListBlock(array(), null, null, null, $search_type, $calendar_date);
        $this->view->assign($return);
        $this->view->render();
    }

    public function view($event_id)
    {
        $event_id = intval($event_id);
        if($event_id) {
            $lang_id = $this->pg_language->current_lang_id;
            $event = $this->Events_model->getEventById($event_id, $lang_id);
        }
        
        if(!$event_id || empty($event) || (!$event['status'] && !$event['is_owner'])) {
            show_404();
        }

        $this->load->helper('events');
        $categories = get_events_categories();
        $this->view->assign('category', $categories['option'][$event['category']]);

        $page_data['event_date_format'] = $this->event_date_format;
        $page_data['event_time_format'] = $this->event_time_format;
        $this->view->assign('page_data', $page_data);

        //media album
        $album_id = $event['album_id'];
        $this->load->model('media/models/Media_model');
        $this->load->model('media/models/Media_album_model');
        $this->load->model('media/models/Album_types_model');
        $this->Media_model->initialize($this->Events_model->album_type);

        if ($album_id != 0) {
            $media_ids = $this->Media_album_model->get_media_ids_in_album($album_id);
            if (!empty($media_ids)) {
                $media = $this->Media_model->get_media(''/* $exists_page */, 6, ''/* $order_by */, ''/* $where */, $media_ids);
                $this->view->assign('media', $media);
                $this->view->assign('media_count', count($media));
            }
        }

        $settings = $this->eventActionsSettings($event_id);
        $this->view->assign('settings', $settings);
        $this->view->assign('event', $event);

        //user
        $user_create = $this->Events_model->getEventUser($event['fk_user_id'], $event['is_admin']);
        $this->view->assign('user_create', $user_create);
        
        $user_id = $this->session->userdata('user_id');
        $this->view->assign('user_id', $user_id);

        $this->Menu_model->breadcrumbs_set_parent('user_events_item');
        $this->Menu_model->breadcrumbs_set_active($event['name']);
        $this->view->render('view');
    }

    private function eventActionsSettings($event_id)
    {
        $settings = array();       
        $event = $this->Events_model->getEventSettins($event_id);

        $settings['event_settings'] = array(
            'join' => false,
            'leave' => false,
            'decline' => false
        );
        
        $settings['is_owner'] = $event['is_owner'];
        $settings['participants_count'] = $this->Events_model->getApprovedUsersCount($event_id);
        $settings['participants_out'] = $this->Events_model->isFreeSpots($event['max_participants'], $settings['participants_count']);
            
        //user access
        if(!$event['is_owner']) {
            $user_id = $this->session->userdata('user_id');
            $participant = $this->Events_model->getParticipantByID($event_id, $user_id);

            if($participant) {
                switch($participant['status']) {
                    case $this->participant_statuses['approve_gid']:
                        $settings['event_settings']['leave'] = true;
                        $settings['event_settings'] = array_merge($settings['event_settings'], $event['event_settings']);
                        break;
                    
                    case $this->participant_statuses['decline_gid']:
                        if($settings['participants_out']) {
                            $settings['event_settings']['join'] = true;
                        }
                        break;
                        
                    case $this->participant_statuses['pending_gid']:
                        if($settings['participants_out']) {
                            $settings['event_settings']['join'] = true;
                        }
                        $settings['event_settings']['decline'] = true;
                        break;
                }
            } else {
                if($event['event_settings']['is_user_can_join'] && $settings['participants_out']) {
                    $settings['event_settings']['join'] = true;
                }
            }
        }
        
        return $settings;
    }

    public function ajaxChangeStatus($event_id = null, $status)
    {
        $return = array();
        $data = array('status' => '');
        $user_id = $this->session->userdata('user_id');
        $event_id = intval($event_id);

        if ($event_id) {
            $event_permissions = $this->eventActionsSettings($event_id);
            
            switch ($status) {
                case 'approve':
                    if($event_permissions['event_settings']['join']) {
                        $data['status'] = $this->participant_statuses['approve_gid'];
                        $return['success'] = l('success_participant_join', 'events');

                        $this->Events_model->sendNotificationJoin($user_id, $event_id);
                    } else {
                        $return['error'] = l('error_participant_join', 'events');
                    }
                    break;
                    
                case 'decline':
                    $data['status'] = $this->participant_statuses['decline_gid'];
                    $return['success'] = l('success_participant_leave', 'events');
                    break;
            }
            
            
            $user = $this->Events_model->getParticipantByID($event_id, $user_id);
            if($data['status']) {
                if($user) {
                    $this->Events_model->saveParticipantStatus($event_id, $user_id, $data);
                } else {
                    $data['is_invite'] = 0;
                    $data['is_new'] = 0;
                    $this->Events_model->saveParticipant($event_id, $user_id, $data);                    
                }
            }
        }

        $this->view->assign($return);
        $this->view->render();
    }

    /**
     *  Add/Edit event
     *
     *  @param integer $id
     *
     *  @return void
     */
    public function edit($id = null)
    {
        $event = array();

        $id = intval($id);
        $user_id = $this->session->userdata('user_id');
        $lang_id = $this->pg_language->current_lang_id;

        if ($id) {
            $event = $this->Events_model->getEventById($id, $lang_id);
            if($event['is_owner'] !=1) {
                show_404();
            }
            
            $this->Menu_model->breadcrumbs_set_active(l('link_edit_event', 'events'));
            $this->view->assign('header_text', l('header_edit', 'events'));
        } else {
            $settings = $this->Events_model->getSettings();
            if (!$settings['is_active']) {
                show_404();
            }
            
            $this->Menu_model->breadcrumbs_set_active(l('link_add', 'events'));
            $this->view->assign('header_text', l('header_create', 'events'));
        }

        
        if ($this->input->post('btn_save')) {
            $post_data = array(
                'name'  => $this->input->post('name'),
                'description' => $this->input->post('description', true),
                'category' => $this->input->post('category', true),
                'region_name' => $this->input->post('region_name', true),
                'country_code' => $this->input->post('id_country', true),
                'fk_region_id' => $this->input->post('id_region', true),
                'fk_city_id' => $this->input->post('id_city', true),
                'address' => $this->input->post('address', true),
                'venue' => $this->input->post('venue', true),
                'max_participants' => $this->input->post('max_participants', true),
                'event_settings' => $this->input->post('event_settings', true),
                
                'date_started' => $this->input->post('date_started', true),
                'alt_date_started' => $this->input->post('alt_date_started', true),
                'time_started' => $this->input->post('time_started', true),
                
                'date_ended' => $this->input->post('date_ended', true),
                'alt_date_ended' => $this->input->post('alt_date_ended', true),
                'time_ended' => $this->input->post('time_ended', true),
                
                'deadline_date' => $this->input->post('deadline_date', true),
                'alt_deadline_date' => $this->input->post('alt_deadline_date', true),
                'deadline_time' => $this->input->post('deadline_time', true),
            );

            $langs = $this->pg_language->languages;
            foreach ($langs as $value) {
                $post_data['name_' . $value['id']] = $post_data['name'];
                $post_data['description_' . $value['id']] = $post_data['description'];
            }

            $validate_data = $this->Events_model->validateEvent($post_data);
            
            if (!empty($validate_data['errors'])) {
                $this->system_messages->addMessage(View::MSG_ERROR, $validate_data['errors']);
                $event = $post_data;
            } else {
                $this->load->model('countries/models/Countries_model');
                $city_arr = $this->Countries_model->get_city($validate_data['data']['fk_city_id']);
                if (!empty($city_arr)) {
                    $validate_data['data']['lat'] = $city_arr['latitude'];
                    $validate_data['data']['lon'] = $city_arr['longitude'];
                }

                $validate_data['data']['fk_user_id'] = $user_id;

                $this->load->model('Moderation_model');
                $validate_data['data']['status'] = intval($this->Moderation_model->get_moderation_type_status($this->Events_model->moderation_type[0]));
                $validate_data['data']['is_active'] = ($validate_data['data']['status'] == 1) ? 1 : 0;

                $message = $id ? l('success_item_updated', 'events') : l('success_item_created', 'events');
                $id = $this->Events_model->saveEvent($id, $validate_data['data']);

                $is_moderated = $this->Moderation_model->add_moderation_item($this->Events_model->moderation_type[0], $id);

                if ($is_moderated) {
                    $this->system_messages->addMessage(View::MSG_INFO, l('file_uploaded_and_moderated', 'media'));
                }
                
                $mtype = $this->Moderation_model->get_moderation_type($this->Events_model->moderation_type[0]);
                if ($mtype['mtype'] > 0) {
                    $this->load->model('menu/models/Indicators_model');
                    $this->Indicators_model->add('new_moderation_item', $id, $validate_data['data']['fk_user_id']);
                }

                $event = $this->Events_model->getEventById($id);
                if ($event['album_id'] == 0) {
                    $this->load->model('media/models/Albums_model');
                    $this->load->model('media/models/Album_types_model');
                    $album_data['id_album_type'] = $this->Album_types_model->getTypeIdByGid($this->Events_model->album_type);
                    $album_data['name'] = 'event_album_' . $event['id'];
                    $album_data['id_user'] = $event['fk_user_id'];

                    $album_id = $this->Albums_model->save(null, $album_data);

                    $this->Events_model->saveEvent($event['id'], array('album_id' => $album_id));
                }
                
                $this->Events_model->saveEventLogo($event['id']);

                $this->system_messages->addMessage(View::MSG_SUCCESS, $message);
                $this->view->setRedirect(site_url() . 'events/edit/' . $id);
            }
        }
        
        $this->load->helper('events');
        $categories = get_events_categories();
        $this->view->assign('categories', $categories);

        $this->view->assign('event_id', $id);
        $this->view->assign('user_id', $user_id);
        $this->view->assign('event', $event);

        $this->Menu_model->breadcrumbs_set_parent('user_events_item');
        $this->view->render('edit');
    }
    
    public function deleteLogo($event_id = null)
    {
        if (!is_null($event_id)) {
            $this->Events_model->deleteEventLogo($event_id);
            $this->system_messages->addMessage(View::MSG_SUCCESS, l('success_item_updated', 'events'));
        }
        $this->view->setRedirect(site_url() . 'events/edit/' . $event_id);
    }

    public function ajaxDelete($id = null) {
        $return = array('success' => '');
        
        if($id) {
            $event = $this->Events_model->getEventById($id);
            if($event['is_owner']) {
                $this->Events_model->sendDeleteEventMessage($event);
                $this->Events_model->delete($id);
                
                $return['success'] = l('success_delete_event', 'events');
            }
        }   
        
        $this->view->assign($return);
        $this->view->render();
    }
    
    public function ajaxDeleteUser($event_id = null, $user_id = null) {
        $return = array('success' => '');
        
        if($event_id && $user_id) {
            $event = $this->Events_model->getEvent(array('id' => $event_id));
            $is_owner = $this->Events_model->isOwnerEvent(null, $event);
            if($is_owner) {
                $this->Events_model->deleteParticipantByUser($event_id, $user_id);
                $return['success'] = l('success_delete_user', 'users');
            }
        }
        
        $this->view->assign($return);
        $this->view->render();
    }

    public function ajaxLoadAvatar()
    {
        $result = array('status' => 1, 'errors' => array(), 'msg' => array(), 'data' => array());
        
        $id_event = $this->input->post('id_event', true);
        if ($id_event) { 
            $id_user = $this->session->userdata('user_id');
            $data['event'] = $this->Events_model->getEventById($id_event);

            if (!$id_user || !$data['event']['fk_user_id'] || !$data['event']['is_owner']) {
                $result['status']   = 0;
                $result['errors'][] = l('error_access_denied', 'events');
                $this->view->assign($result);
                $this->view->render();
                return;
            }

            $data['have_avatar'] = $data['event']['img'];
            if ($data['event']['is_owner']) {
                $this->load->model('uploads/models/Uploads_config_model');
                $data['upload_config'] = $this->Uploads_model->get_config($this->Events_model->upload_gid);
                $data['selections']    = array();
                foreach ($data['upload_config']['thumbs'] as $thumb_config) {
                    $data['selections'][$thumb_config['prefix']] = array(
                        'width'  => $thumb_config['width'],
                        'height' => $thumb_config['height'],
                    );
                }
            }
            
            $data['upload_gid'] = $this->Events_model->upload_gid;
            $this->view->assign('avatar_data', $data);
            $result['data']['html'] = $this->view->fetchFinal('ajax_event_avatar');
            if (isset($data['selections'])) {
                $result['data']['selections'] = $data['selections'];
            }
        }

        $this->view->assign($result);
        $this->view->render();
    }

    public function ajaxRecropAvatar()
    {
        $result = array('status' => 1, 'errors' => array(), 'msg' => array(), 'data' => array());
        $event_id = $this->input->post('event_id', true);
        if (!$event_id) {
            return;
        }

        $event = $this->Events_model->formatEvent($this->Events_model->getEventById($event_id));
        if (!$event || !$event['img']) {
            $result['status']   = 0;
            $result['errors'][] = l('error_access_denied', 'users');
        } else {
            $selection = $this->input->post('selections', true);

            $recrop_data['x1']         = $selection['x1'];
            $recrop_data['y1']         = $selection['y1'];
            $recrop_data['width']      = $selection['width'];
            $recrop_data['height']     = $selection['height'];
            $thumb_prefix              = trim(strip_tags($selection['prefix']));
            $this->load->model('Uploads_model');

            $this->Uploads_model->recrop_upload($this->Events_model->upload_gid, $event_id, $event['img'], $recrop_data, $thumb_prefix);
            $result['data']['img_url'] = $event['image']['thumbs'][$thumb_prefix];
            $result['data']['rand']    = rand(0, 999999);
            $result['msg'][]           = l('photo_successfully_saved', 'users');            
        }
        
        $this->view->assign($result);
        $this->view->render();
    }

    public function ajaxUploadAvatar($event_id)
    {
        $return = array();
        if($event_id) {
            $return = $this->Events_model->saveEventLogo($event_id);
            if (empty($return['errors'])) {
                $event = $this->Events_model->getEventById($event_id);
                $return['logo'] = $event['image'];
            }            
        }
        
        $this->view->assign($return);
        $this->view->render();
    }

    public function ajaxGetForm($type)
    {
        $this->load->model('Media_model');
        $this->Media_model->initialize($this->Events_model->album_type);

        $id_album = intval($this->input->post('id_album', true));
        $this->load->model('media/models/Albums_model');

        $this->view->assign('id_album', $id_album);

        switch ($type) {
            case 'video':
                $this->load->model('Video_uploads_model');
                $media_config = $this->Video_uploads_model->get_config($this->Media_model->video_config_gid);
                $this->view->assign('media_config', $media_config);
                $tpl = $this->view->fetch('add_images_form');
                break;
            case 'audio':
                if ($this->pg_module->is_module_installed('audio_uploads')) {
                    $this->load->model('Audio_uploads_model');
                    $media_config = $this->Audio_uploads_model->get_config($this->Media_model->audio_config_gid);
                    $this->view->assign('media_config', $media_config);
                    $tpl = $this->view->fetch('add_images_form');
                } else {
                    $tpl = '';
                }
                break;
            case 'image':
            default:
                $this->load->model('Uploads_model');
                $media_config = $this->Uploads_model->get_config($this->Media_model->file_config_gid);
                $this->view->assign('media_config', $media_config);
                $tpl = $this->view->fetch('add_images_form');
                break;
        }

        $this->view->output($tpl);
        $this->view->render();
    }

    public function ajaxSaveImage()
    {
        $return = array('errors' => array(), 'warnings' => array(), 'name' => '');

        $this->load->model('Media_model');
        $this->Media_model->initialize($this->Events_model->album_type);

        $image_data = array();
        $image_data['data']["id_user"] = $image_data['data']["id_owner"] = $this->session->userdata('user_id');
        $image_data['data']["type_id"] = $this->Media_model->album_type_id;
        $image_data['data']["upload_gid"] = $this->Media_model->file_config_gid;
        $image_data['data']['status'] = 1;

        $save_data = $this->Media_model->save_image(null, $image_data["data"], 'multiUpload', false, false);

        $id_album = intval($this->input->post('album_id', true));
        if ($id_album) {
            $this->load->model('media/models/Albums_model');
            $this->load->model('media/models/Media_album_model');
            $add_status = $this->Media_album_model->add_media_in_album($save_data['id'], $id_album);
        }

        $return["form_error"] = array();

        if (!empty($image_data['errors'])) {
            $return['errors'] = $image_data['errors'];
        }
        if (empty($save_data['errors'])) {
            if (!empty($save_data['file'])) {
                $return['name'] = $save_data['file'];
            }
        } else {
            $return['errors'][] = $save_data['errors'];
        }
        if (!empty($is_user_can_add['error'])) {
            $return['warnings'][] = $is_user_can_add['error'];
        }

        $this->view->assign($return);
        $this->view->render();
    }

    public function ajaxLoadMailbox($event_id)
    {
        $return = array("content" => '', 'msg' => '', 'send_url' => '', 'success' => '');

        $event = $this->Events_model->getEventById($event_id);
        if ($event['is_admin']) {
            if ($this->pg_module->is_module_active("tickets")) {
                //tickets form
                $this->load->model('Tickets_model');
                $reasons = $this->Tickets_model->get_reason_list();
                $this->view->assign('reasons', $reasons);
                $is_auth = ($this->session->userdata('auth_type') == 'user');
                $this->view->assign('is_auth', $is_auth);
                $settings = $this->Tickets_model->get_settings();
                $this->view->assign('settings', $settings);

                if (!$is_auth || !$settings['status_personal_communication']) {
                    $this->load->plugin('captcha');
                    $vals = array(
                        'img_path'   => TEMPPATH . '/captcha/',
                        'img_url'    => SITE_VIRTUAL_PATH . 'temp/captcha/',
                        'font_path'  => BASEPATH . 'fonts/arial.ttf',
                        'img_width'  => '200',
                        'img_height' => '30',
                        'expiration' => 7200,
                    );

                    $cap = create_captcha($vals);
                    $data["captcha"] = $cap['image'];
                    $_SESSION["captcha_word"] = $cap['word'];
                    $this->view->assign('data', $data);
                }

                $return['send_url'] = site_url() . 'tickets/ajax_send_message';
                $return['content'] = $this->view->fetch('write_ticket');
            } elseif ($this->pg_module->is_module_active("contact_us")) {
                //contact us form
                $this->load->model('Contact_us_model');
                $reasons = $this->Contact_us_model->get_reason_list();
                $this->view->assign('reasons', $reasons);

                $this->load->plugin('captcha');
                $vals = array(
                    'img_path'   => TEMPPATH . '/captcha/',
                    'img_url'    => SITE_VIRTUAL_PATH . 'temp/captcha/',
                    'font_path'  => BASEPATH . 'fonts/arial.ttf',
                    'img_width'  => '200',
                    'img_height' => '30',
                    'expiration' => 7200,
                );

                $cap = create_captcha($vals);
                $data["captcha"] = $cap['image'];
                $_SESSION["captcha_word"] = $cap['word'];
                $this->view->assign('data', $data);

                $return['send_url'] = site_url() . 'contact_us/ajax_send_messages';
                $return['content'] = $this->view->fetch('write_contact_us');
            } else {
                $this->load->model('Ausers_model');
                $admin = $this->Ausers_model->get_user_by_id($event['fk_user_id']);
                $return['msg'] = str_replace("[admin_email]", $admin['email'], l('info_admin_contact', 'events'));
                //$this->system_messages->addMessage(View::MSG_SUCCESS, $return['success']);
            }
        } else {
            $this->load->model('Mailbox_model');

            $user_id = $this->session->userdata('user_id');

            $message = array();

            $this->view->assign('dest_user_id', $event['fk_user_id']);
            $this->view->assign('message', $message);
            $this->view->assign('write_message', 1);

            $attach_settings = $this->Mailbox_model->get_attach_settings();
            $this->view->assign('attach_settings', $attach_settings);

            $this->view->assign('rand', rand(100000, 999999));

            $this->Menu_model->breadcrumbs_set_parent('mailbox_item');
            $this->Menu_model->breadcrumbs_set_active(l('write_message', 'mailbox'));

            $inbox_new_message = $this->Mailbox_model->get_new_messages_count($user_id, 'inbox');
            $this->view->assign('inbox_new_message', $inbox_new_message);

            $spam_new_message = $this->Mailbox_model->get_new_messages_count($user_id, 'spam');
            $this->view->assign('spam_new_message', $spam_new_message);

            $trash_new_message = $this->Mailbox_model->get_new_messages_count($user_id, 'trash');
            $this->view->assign('trash_new_message', $trash_new_message);

            $return['send_url'] = site_url() . 'contact_us/ajax_send_message';
            $return['content'] = $this->view->fetch('write');
        }

        $this->view->assign($return);
        $this->view->render();
    }

    public function ajaxLoadUsers($event_id)
    {
        $return = array();
        
        $params = array(
            'event_id' => $event_id,
        );
        $this->setUsersSearchParams($params);
        
        $this->view->assign('event_id', $event_id);
        $this->view->assign('users_block', $this->searchUsersListBlock());
        $return['content'] = $this->view->fetch('invite_block');
        
        $this->view->assign($return);
        $this->view->render();
    }
    
    private function setUsersSearchParams($params = array()) {
        $this->session->set_userdata('events_users_search', $params);
    }
    
    private function getUsersSearchParams() {
        return $this->session->userdata('events_users_search');
    }

    
    public function ajaxInviteSelect($event_id = null) 
    {
        $return = array('success' => '');
        
        if($event_id) {

            $users_ids = (array) $this->input->post('invited', true);
            
            if(!empty($users_ids)) {
                $user_settings = $this->eventActionsSettings($event_id);
                
                if($user_settings['is_owner'] || $user_settings['event_settings']['is_user_invite']) {
                    $participants_ids = $this->Events_model->getListParticipantsIds($event_id);
                    $formatted_users = array();
                    foreach($users_ids as $key => $id) {
                        if(!in_array($id, $participants_ids)) {
                           $formatted_users[] = $id;
                        }
                    }
                    
                    if(!empty($formatted_users)) {
                        $users_setting = array(
                            'is_invite'     => 1,
                            'response_date' => date('Y-m-d H:i:s'),
                            'status'        => 'pending',
                        );
                        $this->Events_model->saveAllParticipants($event_id, $formatted_users, $users_setting);  
                        $return['data']['users_ids'] = $formatted_users;
                        $return['success'] = l('success_participant_invite', 'events');
                    }
                }
            }
        }
        
        $this->view->assign($return);
        $this->view->render();
    }
//    public function ajaxInviteUsers($event_id)
//    {
//        $event_id = intval($event_id);
//        $is_invite = $this->userCanInvite($event_id);
//
//        if ($is_invite) {
//            $post_data['invited'] = $this->input->post('invited', true);
//            $users_ids = explode(',', $post_data['invited']);
//            $users_setting = array(
//                'is_invite'     => 1,
//                'response_date' => date('Y-m-d H:i:s'),
//                'status'        => 'pending',
//            );
//            $this->Events_model->saveAllParticipants($event_id, $users_ids, $users_setting);
//            $return['success'] = l('success_participant_added', 'events');
//            $this->view->assign($return);
//        }
//
//        $this->view->render();
//    }

    public function ajaxViewMedia($id, $user_id, $param = 'all', $album_id = 0)
    {
        $id = intval($id);
        $user_id = intval($user_id);
        $album_id = intval($album_id);
        $param = trim(strip_tags($param));
        $order = trim(strip_tags($this->input->post('order', true)));
        $direction = trim(strip_tags($this->input->post('direction', true)));
        if (!$order) {
            $order = 'date_add';
        }
        $order_by[$order] = ($direction == 'asc') ? 'ASC' : 'DESC';
        $filter_duplicate = intval($this->input->post('filter_duplicate', true));

        $this->load->model('Media_model');
        $this->Media_model->initialize($this->Events_model->album_type);
        $media = $this->Media_model->get_media_by_id($id);

        $gallery_name = $this->input->post('gallery_name', true) ? trim(strip_tags($this->input->post('gallery_name', true))) : 'eventsgallery';
        $is_access_permitted = $this->Media_model->is_access_permitted($id, $media);
        $selections = array();
        $this->load->model('Uploads_model');
        $upload_config = $this->Uploads_model->get_config($this->Media_model->file_config_gid);
        foreach ($upload_config['thumbs'] as $thumb_config) {
            $selections[$thumb_config['prefix']] = array(
                'width'  => $thumb_config['width'],
                'height' => $thumb_config['height'],
            );
        }
        $this->view->assign('selections', $selections);
        $this->view->assign('gallery_name', $gallery_name);
        $this->view->assign('media_id', $id);
        $this->view->assign('param', $param);
        $this->view->assign('album_id', $album_id);
        $this->view->assign('rand', rand(0, 999999));
        $this->view->output($this->view->fetchFinal('view_media'));
        $this->view->render();
    }

    public function ajaxGetMediaContent($media_id, $gallery_param = 'all', $album_id = 0)
    {
        $return = array('content' => '', 'position_info' => '', 'media_type' => '', 'views_num' => '');
        $media_id = intval($media_id);
        $album_id = intval($album_id);
        $place = trim(strip_tags($this->input->post('place', true)));
        $gallery_param = trim(strip_tags($gallery_param));
        $without_position = intval($this->input->post('without_position', true));
        $user_id = $this->session->userdata('user_id');

        $order = trim(strip_tags($this->input->post('order', true)));
        $direction = trim(strip_tags($this->input->post('direction', true)));
        if (!$order) {
            $order = 'date_add';
        }
        $order_by[$order] = ($direction == 'asc') ? 'ASC' : 'DESC';
        $filter_duplicate = ($place == 'site_gallery') ? 1 : intval($this->input->post('filter_duplicate', true));

        $this->load->model('Media_model');
        $this->Media_model->initialize($this->Events_model->album_type);
        $media = $this->Media_model->get_media_by_id($media_id, true, true);
        if(!isset($media['owner_info']['id'])) {
            $this->load->model("Ausers_model");
            $user_create = $this->Ausers_model->get_user_by_id(1);
            $media['owner_info']['output_name'] = $user_create['name'];
        }
        $media_user_id = ($place == 'site_gallery') ? 0 : $media['id_user'];
        $is_user_media_owner = ($media['id_owner'] == $user_id);
        $is_user_media_user = ($media['id_user'] == $user_id);
        $is_access_permitted = $this->Media_model->is_access_permitted($media_id, $media);
        $date_formats['date_format'] = $this->pg_date->get_format('date_literal', 'st');
        $date_formats['date_time_format'] = $this->pg_date->get_format('date_time_literal', 'st');

        if ($is_access_permitted) {
            $this->Media_model->increment_media_views($media_id);
            $this->view->assign('media', $media);
            $return['views_num'] = $media['views'] + 1;
        }

        $this->view->assign('is_user_media_owner', $is_user_media_owner);
        $this->view->assign('is_user_media_user', $is_user_media_user);
        $this->view->assign('is_access_permitted', $is_access_permitted);
        $this->view->assign('date_formats', $date_formats);

        $aviary_post_data = array(
            'type'    => $media['type_id'],
            'id'      => $media_id,
            'user_id' => $media_user_id,
        );
        $this->view->assign('aviary_post_data', $aviary_post_data);

        $this->view->assign('responder_id', $media['id_user']);

        $this->view->assign('user_id', $user_id);

        $this->view->assign('responder_id', $media['id_user']);

        $rand_param = intval($this->input->post('rand_param', true));
        if ($rand_param) {
            $this->view->assign('vers', '?' . $rand_param);
        }

        $return['content'] = $this->view->fetchFinal('media_content');

        if (!$without_position) {
            $return['position_info'] = $this->Media_model->get_media_position_info($media_id, $gallery_param, $album_id, 0, true, $order_by, $filter_duplicate);
        }

        $return['media_type'] = $media['upload_gid'];

        $this->view->assign($return);
        $this->view->render();
    }
    
    public function ajaxGetApprovedList($id = null) {
        $this->load->helper('events');
        $return = array();
        $return['content'] = approve_users_block(array('event_id' => $id));
        
        $this->view->assign($return);
        $this->view->render();
    }
    
    public function deleteImage($image_id)
    {
        $result = ['status' => 0];
        if (!empty($image_id)) {
            $this->load->model('Media_model');
            $this->Media_model->delete_media($image_id);
            $result['status'] = 1;
            $result['message'] = l('success_delete_media', 'media');
        }
        
        $this->view->assign($result);
        $this->view->render();
    }
    
    private function getCriteria($search_type = '', $criteria = array())
    {
        if($search_type == 'my_events') {
            unset($criteria['where']['status']);
            unset($criteria['where']['is_active']);
        }
        
        return $criteria;
    }
    
}
