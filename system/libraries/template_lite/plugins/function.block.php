<?php

/**
 * Template_Lite {helper} function plugin
 *
 * Type:     function
 * Name:     helper
 * Purpose:  Execute helper function
 * Input:
 */
function tpl_function_block($params, &$tpl)
{
    if (INSTALL_MODULE_DONE) {
        if (isset($params['module'])) {
            $params['module'] = strtolower($params['module']);
            $is_installed = $tpl->CI->pg_module->is_module_active($params['module']);
            if (!$is_installed) {
                return '';
            }
        } else {
            $params['module'] = 'start';
        }

        if (!function_exists($params['name']) && isset($params['module']) && !empty($params['module'])) {
            $tpl->CI->load->helper($params['module']);
        }
    }

    if (function_exists($params['name'])) {
        $return = $params['name']($params);
    } else {
        log_message('error', 'helper not exist (' . $params['name'] . ')');
        $return  = '';
    }

    if ($return) {
        $include_css = function ($css_path) use (&$return) {
            $css_path = 'application/modules/' . $css_path;
            if (file_exists(SITE_PHYSICAL_PATH . $css_path)) {
                $link = '<link href="' . SITE_VIRTUAL_PATH . $css_path . '" rel="stylesheet" type="text/css">';
                $return = $link . $return;
            }
        };
        $theme_type = $tpl->CI->pg_theme->get_current_theme_type();
        $active_settings = $tpl->CI->pg_theme->return_active_settings($theme_type);
        $module_css_dir = $params['module'] . '/views/' . $active_settings["theme"] . '/css/';
        if (is_dir(MODULEPATH . $module_css_dir)) {
            $include_css($module_css_dir . 'style.css');
            $lang = $tpl->CI->pg_language->get_lang_by_id($tpl->CI->pg_language->current_lang_id);
            $include_css($module_css_dir . 'style-' . $lang['rtl'] . '.css');
        }
    }

    return $return;
}
