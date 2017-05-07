<?php

/**
 * Template_Lite  function plugin
 */
function tpl_function_start_search_form($params, &$tpl)
{
    $tpl->CI->load->helper('start');

    if (empty($params['object']) && $tpl->CI->pg_module->is_module_active('resumes')) {
        $params['object'] = 'resume';
    } elseif (empty($params['object']) && $tpl->CI->pg_module->is_module_active('vacancies')) {
        $params['object'] = 'vacancy';
    } elseif (empty($params['object']) && $tpl->CI->pg_module->is_module_active('listings')) {
        $tpl->CI->load->model('Listings_model');
        $operation_types = $tpl->CI->Listings_model->get_operation_types();
        if (count($operation_types)) {
            $params['object'] = current($operation_types);
        }
    } elseif (empty($params['object']) && $tpl->CI->pg_module->is_module_active('idxbroker')) {
        $params['object'] = 'idxbroker';
    } elseif (empty($params['object']) && $tpl->CI->pg_module->is_module_active('users')) {
        $params['object'] = 'user';
    }

    if (empty($params['type'])) {
        $params['type'] = 'line';
    }
    if (!isset($params['show_data'])) {
        $params['show_data'] = false;
    }

    return main_search_form($params['object'], $params['type'], $params['show_data']);
}
