<?php

namespace Pg\Libraries\View\Renderer;

use Pg\Libraries\View;
use Pg\Libraries\View\ARenderer;

class Html extends ARenderer
{
    const FORMAT_NAME = 'html';

    public function getName()
    {
        return self::FORMAT_NAME;
    }

    public function getExtension()
    {
        return $this->view->getDriver()->getTplExtension();
    }

    private function checkRedirect()
    {
        $redirect = $this->view->getRedirect();
        if ($redirect) {
            redirect($redirect['url'], $redirect['method'], $redirect['code']);
            return true;
        }

        return false;
    }

    protected function render()
    {
        $driver = $this->view->getDriver();
        assert($driver);

        $driver->setDebugging($this->view->getDebugging());

        $this->setMessages();
        $this->checkRedirect();

        $template = $this->view->getTemplate();

        if ($this->view->isTemplateRendered($template)) {
            $driver->assign("_PREDEFINED", $this->getMessages());
        }

        foreach ($this->view->getVars() as $key => $val) {
            $driver->assign($key, $val);
        }

        if ($template) {
            $output = $driver->view(
                $template, $this->view->getModule(), $this->view->getThemeSettings()
            );
        } else {
            $output = '';
        }

        // TODO: рассмотреть возможность добавления raw в любом случае
        if (!$output) {
            $output = implode('', $this->view->getRaw());
        }

        if (TPL_PRINT_NAMES) {
            $raw_vars = $this->view->getRaw();
            if (!empty($raw_vars)) {
                $raw_vars_str = '';
                foreach ($raw_vars as $raw_var) {
                    $raw_vars_str .= var_export($raw_var, true);
                }
                $output = $raw_vars_str . $output;
            }
        }

        return $output;
    }

    private function setMessages()
    {
        $CI = &get_instance();
        foreach ($this->view->getMessages() as $type => $messages) {
            $CI->system_messages->add_message($type, $messages);
        }

        $headers = $this->view->getHeader();
        if (isset($headers[0])) {
            $CI->system_messages->set_data('header', $headers[0]);
        }
        if (isset($headers[1])) {
            $CI->system_messages->set_data('subheader', $headers[1]);
        }
        $CI->system_messages->set_data('help', $this->view->getHelp());
        $CI->system_messages->set_data('back_link', $this->view->getBackLink());
    }

    private function getMessages()
    {
        $ci = get_instance();

        return array(
            View::MSG_ERROR   => $ci->system_messages->get_messages('error'),
            View::MSG_INFO    => $ci->system_messages->get_messages('info'),
            View::MSG_SUCCESS => $ci->system_messages->get_messages('success'),
            "header"          => $ci->system_messages->get_data('header'),
            "subheader"       => $ci->system_messages->get_data('subheader'),
            "help"            => $ci->system_messages->get_data('help'),
            "back_link"       => $ci->system_messages->get_data('back_link'),
        );
    }
}
