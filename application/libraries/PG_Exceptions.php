<?php

/**
 * Extension for the CodeIgniter Exceptions library
 *
 * @package PG_Core
 * @subpackage application
 *
 * @category	libraries
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Irina Lebedeva <irina@pilotgroup.net>
 *
 * @version $Revision: 2 $ $Date: 2009-12-02 15:07:07 +0300 (Ср, 02 дек 2009) $ $Author: irina $
 **/
class PG_Exceptions extends CI_Exceptions
{
    /**
     * General Error Page
     *
     * This function takes an error message as input
     * (either as a string or an array) and displays
     * it using the specified template.
     *
     * @param	string	the heading
     * @param	string	the message
     * @param	string	the template name
     *
     * @return string
     */
    public function show_error($heading, $message, $template = 'error_general', $code = 500)
    {
        $ci = &get_instance();

        if (GENERATE_BACKTRACE && !$ci->router->is_api_class) {
            $ci->load->helper('debug');
            generate_backtrace();
        }

        if (DISPLAY_ERRORS) {
            switch ($code) {
                case '403':
                    header('HTTP/1.0 403 Forbidden', true, 403);
                    print_r($message);
                break;
                case '404':
                    header("HTTP/1.0 404 Not Found", true, 404);
                    print_r($message);
                break;
                case '500':
                    header('HTTP/1.0 500 Internal Server Error', true, 500);
                    print_r($message);
                break;
                default:
                    print_r($message);
                break;
            }
        }

        if ($ci->input->is_ajax_request()) {
            switch ($code) {
                case '403':
                    header('HTTP/1.0 403 Forbidden', true, 403);
                    echo $message;
                break;
                case '404':
                    header("HTTP/1.0 404 Not Found", true, 404);
                    echo $message;
                break;
                case '500':
                    header('HTTP/1.0 500 Internal Server Error', true, 500);
                    echo $message;
                break;
                default:
                    echo $message;
                break;
            }
            exit;
        }

        if ($ci->router->is_api_class) {
            $ci->set_api_content('errors', implode(',', (!is_array($message)) ? array($message) : $message));
            echo $ci->get_api_content();
            exit;
        }

        if (INSTALL_MODULE_DONE && $ci->pg_module->is_module_active('start')) {
            switch ($code) {
                case '403':
                    header('HTTP/1.0 403 Forbidden', true, 403);
                break;
                case '404':
                    header("HTTP/1.0 404 Not Found", true, 404);
                break;
                case '500':
                    header("HTTP/1.0 500 Internal Server Error", true, 500);
                    break;
                default:
                    header("HTTP/1.0 404 Not Found", true, 404);
                break;
            }

            $ci->load->helper('start');
            echo getErrorPage();
        } else {
            $message = '<p>' . implode('</p><p>', (!is_array($message)) ? array($message) : $message) . '</p>';

            if (ob_get_level() > $this->ob_level + 1) {
                ob_end_flush();
            }

            ob_start();
            include APPPATH . 'errors/' . $template . EXT;
            $buffer = ob_get_contents();
            ob_end_clean();

            return $buffer;
        }
    }

    public function show_404($page = '')
    {
        $ci = &get_instance();
        if (INSTALL_MODULE_DONE && $ci->pg_module->is_module_active('start')) {
            $heading = l('header_error', 'start');
            $message = l('error_not_access', 'start');
        } else {
            $heading = "404 Page Not Found";
            $message = "The page you requested was not found.";
        }

        $ci->view->assign('header_type', 'error_page');
        if (empty($page)) {
            $page = filter_input(INPUT_SERVER, 'REQUEST_URI');
        }
        log_message('error', '404 Page Not Found --> ' . $page);
        echo $this->show_error($heading, $message, 'error_404', '404');
        exit;
    }

    public function show_403($page = '')
    {
        $ci = &get_instance();
        if (INSTALL_MODULE_DONE && $ci->pg_module->is_module_active('start')) {
            $heading = l('header_error', 'start');
            $message = l('error_403_forbidden', 'start');
        } else {
            $heading = "403 Forbidden. No Permission to Access";
            $message = "No Permission to Access the page you requested.";
        }
        if (empty($page)) {
            $page = filter_input(INPUT_SERVER, 'REQUEST_URI');
        }
        log_message('error', '403 Forbidden. No Permission to Access --> ' . $page);
        echo $this->show_error($heading, $message, 'error_403', '403');
        exit;
    }
}
