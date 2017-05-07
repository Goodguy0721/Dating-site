<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

if (!function_exists('event_handler')) {
    function event_handler()
    {
        if (!INSTALL_MODULE_DONE) {
            return;
        }
        $CI = &get_instance();
        $modules = $CI->pg_module->return_modules();
        foreach ($modules as $module) {
            $handler_class = NS_MODULES . $module['module_gid'] . '\Events\Event' . ucfirst($module['module_gid']) . 'Handler';
            $handler_file = SITE_PHYSICAL_PATH . 'application/modules/' . $module['module_gid'] . '/Events/Event' . ucfirst($module['module_gid']) . 'Handler' . '.php';
            if (file_exists($handler_file)) {
                $handler = new $handler_class();
                $handler->init();
            }
        }
    }
}
