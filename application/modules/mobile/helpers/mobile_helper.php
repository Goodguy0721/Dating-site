<?php

if (!function_exists('mobile_app_links')) {
    function mobile_app_links()
    {
        $CI = &get_instance();

        $ios_app_link = $CI->pg_module->get_module_config('mobile', 'ios_url');
        $CI->view->assign('ios_url', $ios_app_link);

        $android_app_link = $CI->pg_module->get_module_config('mobile', 'android_url');
        $CI->view->assign('android_url', $android_app_link);

        return $CI->view->fetch('helper_app_links', 'user', 'mobile');
    }
}

if (!function_exists('mobileVersion')) {
    function mobileVersion()
    {
        $CI = &get_instance();
        $CI->view->assign("site_url", $CI->config->site_url());
        
        return $CI->view->fetch('helper_mobile_link', 'user', 'mobile');
    }
}
