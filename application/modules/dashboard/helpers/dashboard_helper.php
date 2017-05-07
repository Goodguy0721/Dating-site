<?php

if (!function_exists('dashboard_wall')) {
    function dashboard_wall()
    {
        $ci = &get_instance();
        $ci->load->model('Dashboard_model');
        
        $events_raw = $ci->Dashboard_model->getEventsList();
        $events = $ci->Dashboard_model->formatEvents($events_raw);

        if (empty($events)) {
            return false;
        }
        
        $ci->view->assign('events', $events);
        
        return $ci->view->fetch('helper_wall', null, 'dashboard');
    }
}
