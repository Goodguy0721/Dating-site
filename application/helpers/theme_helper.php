<?php

/**
 * Add css and js files on any page
 *
 * @package PG_Core
 * @subpackage application
 *
 * @category	helpers
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Mikhail Makeev <mmakeev@pilotgroup.net>
 * */
if (!function_exists('css')) {
    function css($load_type = '')
    {
        $CI = &get_instance();

        $preview_theme = '';
        $preview_scheme = '';
        if (!($CI->use_pjax && $CI->is_pjax)) {
            $CI->pg_theme->add_css('application/js/jquery-ui/jquery-ui.custom.css');
            $CI->pg_theme->add_css('application/js/jquery.imgareaselect/css/imgareaselect-default.css');
            // preview mode
            if (isset($_SESSION['change_color_scheme']) && $_SESSION['change_color_scheme']) {
                $preview_theme = $_SESSION["preview_theme"];
                $preview_scheme = $_SESSION["preview_scheme"];
                unset($_SESSION['change_color_scheme']);
            }
        }

        if (INSTALL_DONE && SOCIAL_MODE) {
            $controller = $CI->router->fetch_class(true);
            if (substr($controller, 0, 6) != "admin_") {
                $language_data = $CI->pg_language->get_lang_by_id($CI->pg_language->current_lang_id);
                $CI->pg_theme->add_theme_css('social-' . $language_data['rtl'] . '.css');
            }
            $CI->view->assign('SOCIAL_MODE', 1);
        }

        //demo mode change scheme
        if (DEMO_MODE && !empty($_SESSION["preview_theme"]) && !empty($_SESSION["preview_scheme"]) && !$CI->router->is_admin_class) {
            $preview_theme = $_SESSION["preview_theme"];
            $preview_scheme = $_SESSION["preview_scheme"];
        }

        $css_html = $CI->pg_theme->get_include_css_code($preview_theme, $preview_scheme);
        echo $css_html;
    }
}

if (!function_exists('include_css')) {
    function include_css($file, $media = 'all')
    {
        $CI = &get_instance();
        if ('.css' != substr($file, strlen($file) - 4, 4)) {
            $file .= '-[rtl].css';
        }
        $CI->pg_theme->add_theme_css($file, $media);
    }
}

if (!function_exists('include_js')) {
    function include_js($module, $file, $return)
    {
        $path = '/' . SITE_SUBFOLDER . APPLICATION_FOLDER;
        if (!empty($module)) {
            $CI = &get_instance();
            if ('install' === $module || (INSTALL_MODULE_DONE && $CI->pg_module->is_module_installed($module))) {
                $path .= 'modules/' . $module . '/js/';
            } else {
                return false;
            }
        } else {
            $path .= 'js/';
        }

        // Add an extension if necessary
        if ('.js' != substr($file, strlen($file) - 3, 3)) {
            $file .= '.js';
        }
        if (!empty($return) && $return === 'path') {
            return $path . $file;
        } else {
            return '	<script type="text/javascript" src="' . $path . $file . '"></script>' . "\n";
        }
    }
}

if (!function_exists('js')) {
    function js($load_type = '')
    {
        $CI = &get_instance();
        $load_type_array = explode("|", $load_type);

        //$CI->pg_theme->add_js('jquery.js');
        $CI->pg_theme->add_js('functions.js');
        $CI->pg_theme->add_js('errors.js');
        $CI->pg_theme->add_js('loading.js');
        $CI->pg_theme->add_js('pginfo.js');
        $CI->pg_theme->add_js('alerts.js');
        if (!$CI->router->is_admin_class) {
            $CI->pg_theme->add_js('jquery.imgareaselect/jquery.imgareaselect.js');
            $CI->pg_theme->add_js('jquery.placeholder.js');
            $CI->pg_theme->add_js('notifications.js');
            $CI->pg_theme->add_js('jquery.gritter.js');
            $CI->pg_theme->add_js('jquery.notification.js');
            $CI->pg_theme->add_js('multi_request.js');
            $CI->load->model('Install_model');
            foreach ($CI->pg_module->return_modules() as $module) {
                $file_name = $module['module_gid'] . '_multi_request.js';
                if (file_exists(MODULEPATH . $module['module_gid'] . '/js/' . $file_name)) {
                    $CI->pg_theme->add_js($file_name, $module['module_gid']);
                }
            }
        }

        if ((is_array($load_type_array) && in_array("ui", $load_type_array)) || (INSTALL_DONE && !$CI->router->is_admin_class)) {
            $CI->pg_theme->add_js('jquery-ui.custom.min.js');
            // Dateppicker langs
            $lang = $CI->pg_language->get_lang_by_id($CI->pg_language->current_lang_id);
            $CI->pg_theme->add_js("datepicker-langs/jquery.ui.datepicker-{$lang['code']}.js");
        }
        if (is_array($load_type_array) && in_array("editable", $load_type_array)) {
            $CI->pg_theme->add_js('jquery.jeditable.mini.js');
        }

        $css_html = $CI->pg_theme->get_include_js_code();
        echo $css_html;
    }
}
