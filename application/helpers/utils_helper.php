<?php

if (!function_exists('depends')) {
    function depends()
    {
        $args = func_get_args();

        $CI = &get_instance();

        $return = array();

        foreach ($args as $arg) {
            $return[$arg] = $CI->pg_module->is_module_installed($arg);
        }

        return $return;
    }
}

if (!function_exists('bytesFormat')) {
    function bytesFormat($string, $to = '', $from = 'b')
    {
        switch ($from) {
            case "kb":
                $string = $string * 1024;
                break;

            case "mb":
                $string = $string * 1024 * 1024;
                break;

            case "gb":
                $string = $string * 1024 * 1024 * 1024;
                break;

            case "b":
            default:
                $string = $string * 1;
                break;
        }

        $ret_arr["b"]  = $string . "b";
        $ret_arr["kb"] = round($string / 1024, 1) . "KB";
        $ret_arr["mb"] = round($string / (1024 * 1024), 1) . "MB";
        $ret_arr["gb"] = round($string / (1024 * 1024 * 1024), 1) . "GB";

        if ($to != '') {
            return $ret_arr[$to];
        } else {
            if ($ret_arr["gb"] > 1) {
                return $ret_arr["gb"];
            }

            if ($ret_arr["mb"] > 1) {
                return $ret_arr["mb"];
            }

            if ($ret_arr["kb"] > 1) {
                return $ret_arr["kb"];
            }

            return $ret_arr["b"];
        }
    }
}

if (!function_exists('inArray')) {
    function inArray($match, $array, $returnvalue, $elsereturnvalue = '')
    {
        if (is_array($array)) {
            if (in_array($match, $array)) {
                return $returnvalue;
            }
            if (isset($elsereturnvalue) && !empty($elsereturnvalue)) {
                return $elsereturnvalue;
            }
        } elseif (isset($elsereturnvalue) && !empty($elsereturnvalue)) {
            return $elsereturnvalue;
        }
    }
}

if (!function_exists('startSearchForm')) {
    function startSearchForm($params)
    {
        $ci = get_instance();

        $ci->load->helper('start');

        if (empty($params['object'])) {
            if (!$ci->pg_module->is_module_installed('users')) {
                return '';
            }
            $params['object'] = 'user';
        }

        if (empty($params['type'])) {
            $params['type'] = 'line';
        }

        if (empty($params['show_data'])) {
            $params['show_data'] = false;
        }
        
        if (empty($params['index'])) {
            $params['index'] = '';
        }

        return main_search_form($params['object'], $params['type'], $params['show_data'], $params['index']);
    }
}

if (!function_exists('jscript')) {
    function jscript($module, $file, $return = null)
    {
        $ci = &get_instance();

        $params['module'] = $module;
        $params['file']   = $file;
        $params['return'] = $return;

        if (empty($params['file'])) {
            log_message('error', 'Empty js file name');
            $ci->trigger_error("js plugin: Empty file name");
        }

        $path = '/' . SITE_SUBFOLDER . APPLICATION_FOLDER;
        if (!empty($params['module'])) {
            if ($params['module'] !== 'install' && INSTALL_MODULE_DONE) {
                if (!$ci->pg_module->is_module_installed($params['module'])) {
                    return false;
                }
            }

            $path .= 'modules/' . $params['module'] . '/js/';
        } else {
            $path .= 'js/';
        }

        // Add an extension if necessary
        if ('.js' != substr($params['file'], strlen($params['file']) - 3, 3)) {
            $params['file'] .= '.js';
        }
        if (!empty($params['return']) && $params['return'] == 'path') {
            return $path . $params['file'];
        } else {
            return '	<script type="text/javascript" src="' . $path . $params['file'] . '"></script>' . "\n";
        }
    }
}

if (!function_exists('jscript_by_theme')) {
    function jscript_by_theme($module, $file, $return, $theme)
    {
        $ci = &get_instance();

        $params['module'] = $module;
        $params['file']   = $file;
        $params['return'] = $return;

        if (empty($params['file'])) {
            log_message('error', 'Empty js file name');
            $ci->trigger_error("js plugin: Empty file name");
        }

        $path = '/' . SITE_SUBFOLDER . APPLICATION_FOLDER;
        if (!empty($params['module'])) {
            if ($params['module'] !== 'install' && INSTALL_MODULE_DONE) {
                if (!$ci->pg_module->is_module_installed($params['module'])) {
                    return false;
                }
            } elseif ('install' !== $params['module']) {
                return false;
            }

            $path .= 'modules/' . $params['module'] . '/views/' . $theme . '/js/';
        } else {
            $path .= 'js/';
        }

        // Add an extension if necessary
        if ('.js' != substr($params['file'], strlen($params['file']) - 3, 3)) {
            $params['file'] .= '.js';
        }
        if (!empty($params['return']) && $params['return'] == 'path') {
            return $path . $params['file'];
        } else {
            return '	<script type="text/javascript" src="' . $path . $params['file'] . '"></script>' . "\n";
        }
    }
}

if (!function_exists('truncate')) {
    function truncate($string, $length = 80, $etc = '...', $break_words = false)
    {
        if ($length == 0) {
            return '';
        }

        $CI = get_instance();
        $CI->load->helper('utf8_string');

        if (UTF8_string_helper::utf8_strlen($string) > $length) {
            $length -= strlen($etc);
            if (!$break_words) {
                $string = preg_replace('/\s+?(\S+)?$/', '', UTF8_string_helper::utf8_substr($string, 0, $length + 1));
            }

            return UTF8_string_helper::utf8_substr($string, 0, $length) . $etc;
        } else {
            return $string;
        }
    }
}

if (!function_exists('template')) {
    function template($module, $file, $theme = '')
    {
        $ci = &get_instance();

        $params['module'] = $module;
        $params['file']   = $file;

        if (empty($params['file'])) {
            log_message('error', 'Empty template file name');
            $ci->trigger_error("template utils: Empty file name");
        }

        $path = '/' . SITE_SUBFOLDER . APPLICATION_FOLDER;
        if (!empty($params['module'])) {
            if (INSTALL_MODULE_DONE) {
                if (!$ci->pg_module->is_module_installed($params['module'])) {
                    return false;
                }
            } elseif ('install' !== $params['module']) {
                return false;
            }

            $path .= 'modules/' . $params['module'] . '/views/';
        } else {
            $path .= 'views/';
        }

        if ($theme) {
            $path .= $theme;
        } else {
            $theme_settings = $ci->view->getThemeSettings();
            $path .= $theme_settings['theme'];
        }

        $path .= '/';

        // Add an extension if necessary
        if ('.twig' != substr($params['file'], strlen($params['file']) - 5, 5)) {
            $params['file'] .= '.twig';
        }

        return $path . $params['file'];
    }
}

if (!function_exists('render')) {
    function render($module, $file, $theme = '', $assign=[])
    {
        $ci = &get_instance();

        $params['module'] = $module;
        $params['file']   = $file;

        if (empty($params['file'])) {
            log_message('error', 'Empty template file name');
            $ci->trigger_error("template utils: Empty file name");
        }
        
        if (!empty($assign)) {
            $ci->view->assign($assign);
        }

        $path = '/' . SITE_SUBFOLDER . APPLICATION_FOLDER;
        if (!empty($params['module'])) {
            if (INSTALL_MODULE_DONE) {
                if (!$ci->pg_module->is_module_installed($params['module'])) {
                    return false;
                }
            } elseif ('install' !== $params['module']) {
                return false;
            }
            
            return $ci->view->fetch($params['file'], null, $params['module']);
        } else {
            return $ci->view->fetch($file);
        }
    }
}

if (!function_exists('addCss')) {
    function addCss($params)
    {
        $ci = &get_instance();

        if (empty($params['file'])) {
            log_message('error', 'Empty css file name');

            return;
        }

        if (empty($params['media'])) {
            $params['media'] = 'all';
        }

        // Add an extension if necessary
        if ('.css' != substr($params['file'], strlen($params['file']) - 4, 4)) {
            $params['file'] .= '-[rtl].css';
        }

        $ci->pg_theme->add_theme_css($params['file'], $params['media']);
    }
}
