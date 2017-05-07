<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

if (!function_exists('output_content')) {
    function output_content()
    {
        $CI = &get_instance();
        // If template has not been rendered yet.
        if (!$CI->view->isTemplateRendered($CI->router->method)) {
            if (!$CI->view->isRendered()) {
                if ($CI->router->is_api_class || stripos($CI->router->method, 'ajax_') === 0) {
                    // If it's api or ajax request, we don't need no template.
                    $CI->view->render(null);
                } elseif ($CI->view->templateExists($CI->router->method)) {
                    // Look for template by name of the action and render it if exists.
                    $CI->view->render($CI->router->method);
                }
            }
            // No autorender
            return false;
        }
        // No autorender
        return true;
    }
}
