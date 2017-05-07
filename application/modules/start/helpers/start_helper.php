<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

if (!function_exists('demo_panel')) {

    function demo_panel($params)
    {
        if (DEMO_MODE || TRIAL_MODE) {
            $ci = &get_instance();

            if (empty($params['place'])) {
                $params['place'] = 'bottom';
            }

            $ci->view->assign('place', $params['place']);
            $html = $ci->view->fetch("demo_panel", $params['type'], 'start');

            return $html;
        }
    }
}

if (!function_exists('product_version')) {

    function product_version()
    {
        if (INSTALL_MODULE_DONE) {
            $ci = &get_instance();

            if ($ci->pg_module->is_module_installed('start')) {
                $current_version_code     = $ci->pg_module->get_module_config('start',
                    'product_version_code');
                $current_version_name     = $ci->pg_module->get_module_config('start',
                    'product_version_name');
                $formated_current_version = str_replace('_', '.',
                    $current_version_name);

                $cache_new_version_code = $ci->pg_module->get_module_config('start',
                    'product_version_code_update');
                $cache_new_version_name = $ci->pg_module->get_module_config('start',
                    'product_version_name_update');

                $last_update = $ci->pg_module->get_module_config('start',
                    'product_version_last_update');
                if ((!$cache_new_version_code || $cache_new_version_code == $current_version_code) && (!$last_update || (time() - strtotime($last_update) > 24 * 60 * 60))) {
                    try {
                        $new_version            = get_new_version();
                        $cache_new_version_code = intval($new_version['code']);
                        $cache_new_version_name = $new_version['name'];
                        $ci->pg_module->set_module_config('start',
                            'product_version_last_update', date('Y-m-d H:i:s'));
                    } catch (Exception $e) {

                    }
                }

                if ($cache_new_version_code && $current_version_code < $cache_new_version_code) {
                    $formated_new_version = str_replace('_', '.',
                        $cache_new_version_name);
                } else {
                    $formated_new_version = '';
                }

                $html = l('system_version', 'start') . ": " . $formated_current_version . " ";
                if ($formated_new_version) {
                    $html .= str_replace('[version]', $formated_new_version,
                        l('system_version_available', 'start'));
                }
                return $html;
            }
        } else {
            return "Dating Pro : Installation";
        }
    }
}

if (!function_exists('get_new_version')) {

    function get_new_version()
    {
        if (SOCIAL_MODE) {
            $feed_url = 'http://www.pilotgroup.net/feeder/socialbiz/version.php';
        } else {
            $feed_url = 'http://www.pilotgroup.net/feeder/datingpro/version.php';
        }

        $ci                               = &get_instance();
        $ci->load->library('Snoopy');
        $ci->snoopy->read_timeout         = 5;
        $ci->snoopy->rawheaders["Accept"] = "application/json";
        $ci->snoopy->fetch($feed_url);
        if ($ci->snoopy->status == '200') {
            $new_version = (array) json_decode($ci->snoopy->results);
            $ci->pg_module->set_module_config('start',
                'product_version_code_update', $new_version['code']);
            $ci->pg_module->set_module_config('start',
                'product_version_name_update', $new_version['name']);

            return $new_version;
        } else {
            throw new Exception('error');
        }
    }
}

if (!function_exists('main_search_form')) {

    function main_search_form($object = 'user', $type = 'line', $show_data = false, $params
    = array())
    {
        $ci = &get_instance();

        // если пользователь незалогинен  то отображаем  вкладки и кнопки
        $user_id   = $ci->session->userdata('user_id');
        $auth_type = $ci->session->userdata('auth_type');

        if ('user' === $object && $ci->pg_module->is_module_installed('users')) {
            $ci->load->helper('users');
            $form_block = users_search_form($object, $type, $show_data);
        }

        if ($form_block) {
            $page_data = array(
                'form_id' => $object . '_' . $type,
                'show_tabs' => false,
                'show_resume_button' => false,
                'show_vacancy_button' => false,
                'object' => $object,
                'type' => $type,
                'hide_popup' => !empty($params['hide_popup']),
                'popup_autoposition' => !empty($params['popup_autoposition']),
            );
            if ($auth_type !== 'user' && $type != 'line') {
                $ci->load->helper('seo');
            }
            $ci->view->assign('form_settings', $page_data);
            $ci->view->assign('form_block', $form_block);
        }

        return $ci->view->fetch("helper_search_form", 'user', 'start');
    }
}

if (!function_exists('selectbox')) {

    function selectbox($params)
    {
        $ci = &get_instance();
        if (empty($params['class'])) {
            $params['class'] = '';
        }
        foreach ($params as $key => $value) {
            $ci->view->assign("sb_" . $key, $value);
        }

        return $ci->view->fetch("helper_selectbox", 'user', 'start');
    }
}

if (!function_exists('hlbox')) {

    function hlbox($params)
    {
        $ci = &get_instance();
        foreach ($params as $key => $value) {
            $ci->view->assign("hlb_" . $key, $value);
        }

        return $ci->view->fetch("helper_hlbox", 'user', 'start');
    }
}

if (!function_exists('checkbox')) {

    function checkbox($params)
    {
        $ci = &get_instance();

        $cb_count = (!empty($params['value'])) ? count($params['value']) : 0;
        $ci->view->assign("cb_count", $cb_count);

        $params['display_group_methods'] = false;
        if (isset($params['value'])) {
            $values   = array();
            $selected = (!empty($params['selected'])) ? $params['selected'] : array();
            if (!is_array($selected)) {
                $selected = array($selected);
            }

            if (is_array($params['value'])) {
                foreach ($params['value'] as $key => $value) {
                    $values[$key] = array('name' => $value, 'checked' => (in_array($key,
                            $selected)) ? 1 : 0);
                }
            } else {
                $values[1] = array('name' => '', 'checked' => $params['value']);
            }

            if (count($values) > 1 && !empty($params['group_methods'])) {
                $params['display_group_methods'] = true;
            }

            $params['value'] = $values;
        } else {
            $params['value'] = [];
        }

        unset($params['selected']);

        foreach ($params as $key => $value) {
            $ci->view->assign("cb_" . $key, $value);
        }

        return $ci->view->fetch("helper_checkbox", 'user', 'start');
    }
}

if (!function_exists('slider')) {

    function slider($params)
    {
        $ci = &get_instance();

        $slider['id']            = isset($params['id']) ? $params['id'] : 'slider' . substr(md5(microtime()),
                0, 5);
        usleep(2);
        $slider['single']        = isset($params['single']) ? intval($params['single']) : 0;
        $slider['active_always'] = isset($params['active_always']) ? intval($params['active_always']) : 0;
        $slider['min']           = !is_null($params['min']) ? floatval($params['min']) : 0;
        $slider['max']           = !is_null($params['max']) ? floatval($params['max']) : 1000;
        $slider['value']         = isset($params['value']) ? floatval($params['value']) : floor(($slider['max'] - $slider['min']) / 2);
        $slider['value_min']     = isset($params['value_min']) ? floatval($params['value_min']) : $slider['min'];
        $slider['value_max']     = isset($params['value_max']) ? floatval($params['value_max']) : $slider['max'];
        $slider['use']           = (!empty($params['value_min']) || !empty($params['value_max']));
        if ($slider['value'] < $slider['min']) {
            $slider['value'] = $slider['min'];
        }
        if ($slider['value'] > $slider['max']) {
            $slider['value'] = $slider['max'];
        }
        if ($slider['value_min'] < $slider['min']) {
            $slider['value_min'] = $slider['min'];
        }
        if ($slider['value_max'] > $slider['max']) {
            $slider['value_max'] = $slider['max'];
        }
        $slider['field_name']     = isset($params['field_name']) ? $params['field_name'] : 'slider_field';
        $slider['field_name_min'] = isset($params['field_name_min']) ? $params['field_name_min'] : 'slider_field_min';
        $slider['field_name_max'] = isset($params['field_name_max']) ? $params['field_name_max'] : 'slider_field_max';

        $ci->view->assign('slider_data', $slider);

        return $ci->view->fetch('helper_slider', 'user', 'start');
    }
}

if (!function_exists('pagination')) {

    function pagination($params)
    {
        $ci = &get_instance();
        foreach ($params as $key => $value) {
            $ci->view->assign("page_" . $key, $value);
        }

        return $ci->view->fetch("helper_pagination", 'user', 'start');
    }
}

if (!function_exists('sorter')) {

    function sorter($params)
    {
        $ci = &get_instance();
        if (empty($params["module"])) {
            $params["module"] = "start";
        }

        $params["rand"] = rand(0, 9999);
        foreach ($params as $key => $value) {
            $ci->view->assign("sort_" . $key, $value);
        }

        return $ci->view->fetch("helper_sorter", 'user', 'start');
    }
}

if (!function_exists('available_browsers')) {

    function available_browsers()
    {
        $ci   = &get_instance();
        $html = $ci->view->fetch("available_browsers", 'user');
        echo $html;
    }
}

if (!function_exists('currency_format_output')) {

    /**
     * Returns formatted currency string
     * Or unformatted if payments is not installed
     *
     * @param int    $params['cur_id']                                                         currency id
     * @param string $params['cur_gid']                                                        currency gid
     * @param int    $params['value']                                                          amount
     * @param string $params['template']&nbsp;[abbr][value|dec_part:2|dec_sep:.|gr_sep:&nbsp;]
     * @param bool   $params['no_tags']
     *
     * @return string
     */
    function currency_format_output($params = array())
    {
        $ci = &get_instance();
        if ($ci->pg_module->is_module_installed('payments')) {
            $ci->load->helper('payments');

            return currency_format($params);
        } elseif (empty($params['no_tags'])) {
            return '<span dir="ltr">' . (float) $params['value'] . '&nbsp;USD</span>';
        } else {
            return (float) $params['value'] . '&nbsp;USD';
        }
    }
}

if (!function_exists('lang_inline_editor')) {

    function lang_inline_editor($params)
    {
        $ci = &get_instance();
        $ci->view->assign('multiple', (!empty($params['multiple']) ? 1 : 0));
        if (isset($params['textarea']) && $params['textarea']) {
            $ci->view->assign('textarea', true);
        }
        $ci->view->assign('rand', rand(100000, 999999));

        return $ci->view->fetch("helper_lang_inline_editor_js", null, 'start');
    }
}

if (!function_exists('currency_output')) {

    /**
     * Returns unformatted currency string
     * Or unformatted if payments is not installed
     *
     * @param int    $params['cur_id']                                                         currency id
     * @param string $params['cur_gid']                                                        currency gid
     * @param int    $params['value']                                                          amount
     * @param string $params['template']&nbsp;[abbr][value|dec_part:2|dec_sep:.|gr_sep:&nbsp;]
     *
     * @return string
     */
    function currency_output($params = array())
    {
        $ci = &get_instance();
        if ($ci->pg_module->is_module_installed('payments')) {
            $ci->load->helper('payments');

            return currency($params);
        } else {
            return '<span dir="ltr">' . $params['value'] . '&nbsp;USD</span>';
        }
    }
}

if (!function_exists('currency_format_regexp_output')) {

    /**
     * Returns formatted currency string
     * Or unformatted if payments is not installed
     *
     * @param int    $params['cur_id']                                                         currency id
     * @param string $params['cur_gid']                                                        currency gid
     * @param int    $params['value']                                                          amount
     * @param string $params['template']&nbsp;[abbr][value|dec_part:2|dec_sep:.|gr_sep:&nbsp;]
     *
     * @return string
     */
    function currency_format_regexp_output($params = array())
    {
        $ci = &get_instance();
        if ($ci->pg_module->is_module_installed('payments')) {
            $ci->load->helper('payments');

            return currency_format_regexp($params);
        } else {
            return 'function(value){return value+\' USD\'}';
        }
    }
}

if (!function_exists('multiselect')) {

    function multiselect($params)
    {
        $ci   = &get_instance();
        $rand = rand(100000, 999999);

        $filtered_params = filter_var_array($params,
            array(
            'fields' => array('flags' => FILTER_REQUIRE_ARRAY),
            'selected' => array('flags' => FILTER_REQUIRE_ARRAY),
            'limits' => array('flags' => FILTER_REQUIRE_ARRAY),
            'all_text' => FILTER_SANITIZE_STRING,
            'all_value' => FILTER_SANITIZE_STRING,
            'min' => FILTER_VALIDATE_INT,
            'max' => FILTER_VALIDATE_INT,
        ));
        if (!isset($filtered_params['all_text'])) {
            $filtered_params['all_text'] = l('multiselect_all_text', 'start');
        }
        if (!isset($filtered_params['all_value'])) {
            $filtered_params['all_value'] = 'all';
        }
        $selected        = array();
        $fields          = array_keys($filtered_params['fields']);
        $all_selected    = array();
        $selected_values = array();
        foreach (array_filter($filtered_params['selected']) as $field => $values) {
            if (!in_array($field, $fields)) {
                continue;
            }
            foreach ((array) $values as $value) {
                $selected_values[$field][$value] = $value;
                if ($filtered_params['all_value'] === $value) {
                    $selected[$field][$value] = $filtered_params['all_text'];
                    $all_selected[$field]     = true;
                    break;
                } elseif (!empty($filtered_params['fields'][$field]['option'][$value])) {
                    $selected[$field][$value] = $filtered_params['fields'][$field]['option'][$value];
                }
            }
        }
        $has_selected = array_keys($selected ? : $filtered_params['fields']);
        $helper_data  = array_merge($filtered_params,
            array(
            'rand' => $rand,
            'selected' => $selected,
            'selected_keys' => $selected_values,
            'active_field' => array_shift($has_selected),
            'all_selected' => $all_selected,
        ));
        $ci->view->assign('multiselect_helper_data', $helper_data);

        return $ci->view->fetch('helper_multiselect', null, 'start');
    }
}

if (!function_exists('ad')) {

    function ad()
    {
        if (FREE_VERSION) {
            $ci      = &get_instance();
            $langs   = $ci->pg_language->languages;
            $lang_id = $ci->pg_language->current_lang_id;
            $ci->view->assign('lang_code', $langs[$lang_id]['code']);

            return $ci->view->fetch('helper_admin_banner', null, 'start');
        }
    }
}

if (!function_exists('widgets')) {

    function widgets($side = 'left')
    {
        $ci = &get_instance();

        return $ci->view->fetch('left_panel', 'user', 'start');
    }
}

if (!function_exists('donate')) {

    function donate($action)
    {
        $ci = &get_instance();
        if ($ci->pg_module->is_module_installed('send_money')) {
            $send_money = true;
        } else {
            $send_money = false;
        }
        if ($ci->pg_module->is_module_installed('send_vip')) {
            $send_vip = true;
        } else {
            $send_vip = false;
        }
        if ($send_money || $send_vip) {
            $html = '<a data-pjax-no-scroll="1" href="' .
                seolink('users', 'account', 'donate') . '"';

            if ($action == 'donate') {
                $html .= ' class="active"';
            }
            $html .= ">" . l('donate', 'start') . '</a>';

            return $html;
        } else {
            return '&nbsp;';
        }
    }
}

if (!function_exists('donate_view_block')) {

    function donate_view_block()
    {
        $ci = &get_instance();
        $ci->view->assign('user_id', $ci->session->userdata('user_id'));
        $ci->load->model('payments/models/Payment_currency_model');
        $ci->view->assign('currency',
            $ci->Payment_currency_model->get_currency_default(true));
        if ($ci->pg_module->is_module_installed('send_money')) {
            $ci->load->helper('send_money');
            $transactions       = $money_transactions = send_money_view_block();
            $send_money         = true;
        }
        if ($ci->pg_module->is_module_installed('send_vip')) {
            $ci->load->helper('send_vip');
            $transactions     = $vip_transactions = send_vip_view_block();
            $send_vip         = true;
        }
        if ($send_money && $send_vip) {
            if (!empty($money_transactions) && !empty($vip_transactions)) {
                $transactions = array_merge($money_transactions,
                    $vip_transactions);
                foreach ($transactions as $value) {
                    $date[] = $value['date_created'];
                }
                if (!empty($date)) {
                    array_multisort($date, SORT_DESC, $transactions);
                }
            } elseif (!isset($vip_transactions) || empty($vip_transactions)) {
                $transactions = $money_transactions;
            } elseif (!isset($money_transactions) || empty($money_transactions)) {
                $transactions = $vip_transactions;
            }
            if (!empty($transactions)) {
                foreach ($transactions as $key => $value) {
                    switch ($value['declined_by_sender']) {
                        case '1': {
                                if ($ci->session->userdata('user_id') == $value['id_sender']) {
                                    $transactions[$key]['declined_by_me'] = 1;
                                } else {
                                    $transactions[$key]['declined_by_me'] = 0;
                                }
                                break;
                            }
                        default: {
                                if ($ci->session->userdata('user_id') == $value['id_sender']) {
                                    $transactions[$key]['declined_by_me'] = 0;
                                } else {
                                    $transactions[$key]['declined_by_me'] = 1;
                                }
                                break;
                            }
                    }
                    /* if($value['declined_by_sender'] == '1' && $ci->session->userdata('user_id') == $value['id_sender']){
                      $transactions[$key]['declined_by_me'] = 1;
                      } elseif($value['declined_by_sender'] == '1' && $ci->session->userdata('user_id') != $value['id_sender']) {
                      $transactions[$key]['declined_by_me'] = 0;
                      } */
                }
            }
        }
        if ($send_money || $send_vip) {
            $page_data['date_format']      = $ci->pg_date->get_format('date_literal',
                'st');
            $page_data['date_time_format'] = $ci->pg_date->get_format('date_time_literal',
                'st');

            $ci->view->assign('transactions', $transactions);
            $ci->view->assign('rand', mt_rand(1, 999999));
            $ci->view->assign('send_money', $send_money);
            $ci->view->assign('send_vip', $send_vip);
            $ci->view->assign('page_data', $page_data);

            return $ci->view->fetch('helper_donate_view_block', 'user', 'start');
        } else {
            return '&nbsp;';
        }
    }
}

if (!function_exists('getErrorPage')) {

    function getErrorPage()
    {
        $ci = &get_instance();
        $ci->load->model('Menu_model');
        $ci->Menu_model->breadcrumbs_set_active(l('header_error', 'start'));
        $ci->view->assign('user_session_data', $ci->session->userdata);

        return $ci->view->fetch('error', 'user', 'start');
    }
}

if (!function_exists('getCalendarInput')) {

    function getCalendarInput($name, $value, array $attrs = [])
    {
        $ci = &get_instance();
        $ci->view->assign('name', $name);
        $ci->view->assign('value', $value);
        $ci->view->assign('attrs', $attrs);
        return $ci->view->fetch('helper_calendar_input', 'admin', 'start');
    }
}

if (!function_exists('intercom')) {

    function intercom()
    {
        $ci = &get_instance();
         if (INSTALL_MODULE_DONE) {
            $auth_type = $ci->session->userdata('auth_type');
            if (in_array($auth_type, [ 'admin', 'module'])) {
                if (!empty($_ENV['INTERCOM'])) {
                    $ci->load->library('intercom');
                    if ($ci->intercom->isUsed) {
                        $product_id = hash_hmac('sha256', SITE_VIRTUAL_PATH, $ci->intercom->INTERCOM_USER_HASH);
                        $trackevent = (PACKAGE_NAME == 'all') ? 'trial_admin' : 'license_admin';
                        $app = [
                            'user_id' => $product_id,
                            'email' => $ci->session->userdata['email'],
                            'name' => $ci->session->userdata['name'],
                            'custom_domain' => SITE_SERVER,
                            'language_override' => $ci->pg_language->current_lang['name'],
                            'trackEvent' => $trackevent,
                            'user_hash' => hash_hmac('sha256', $product_id, $ci->intercom->INTERCOM_USER_HASH)
                        ];
                        $ci->intercom->createUser($app);
                        $ci->intercom->createEvent('dating-' . $trackevent, $ci->session->userdata['email'], $app);
                    }
                    $ci->view->assign('app_id', $ci->intercom->INTERCOM_USER);
                    $ci->view->assign('app', $app);
                    return $ci->view->fetch('helper_intercom', 'admin', 'start');
                }
            }
         }
        return false;
    }
}

if (!function_exists('moduleInstructions')) {

        function moduleInstructions($params=[])
        {
            $ci = &get_instance();
            $display_instructions = include MODULEPATH . 'start/install/display_instructions.php';
            $module = $ci->router->class;
            $method = $ci->router->method;

            if($ci->router->is_admin_class) {
                $uri_string = '/admin/' . $module . '/' . $method . '/';
                $uri_index = '/admin/' . $module . '/index/';
            } else {
                $uri_string = '';
            }

            if($uri_string) {
                if(isset($display_instructions[$module])) {
                    if($display_instructions[$module]['display_on_all'] ||
                        in_array($uri_string, $display_instructions[$module]['pages'])) {

                        $instruction_text = l('admin_' . $module . '_' . $method . '_instruction_text', 'admin_instructions_page');

                        if(!$instruction_text) {
                            $instruction_text = l('admin_' . $module . '_' . $method . '_' . $ci->uri->segments[4] . '_instruction_text', 'admin_instructions_page');
                        }

                        if(!$instruction_text) {
                            $instruction_text = l('admin_' . $module . '_index_instruction_text', 'admin_instructions_page');
                        }

                        if($instruction_text) {
                            $ci->view->assign('instruction_text',  $instruction_text);
                            return $ci->view->fetch('helper_module_instructions', null, 'start');
                        }

                    }
                }

            }

            return;
        }
    }
