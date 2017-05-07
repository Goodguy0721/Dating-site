<?php

namespace Pg\Libraries\View\Renderer;

use Pg\Libraries\View;
use Pg\Libraries\View\ARenderer;

class Json extends ARenderer
{
    const FORMAT_NAME = 'json';
    const FORMAT_EXTENSION = 'json';

    public function getName()
    {
        return self::FORMAT_NAME;
    }

    public function getExtension()
    {
        return self::FORMAT_EXTENSION;
    }

    protected function render()
    {
        $CI = get_instance();
        if (!empty($CI->api_content['data']) || !empty($CI->api_content['errors']) || !empty($CI->api_content['messages']) || !empty($CI->api_content['code']) || count($CI->api_content) > 4) {
            $this->view->assign($CI->api_content);
        }

        $force_json = filter_input(INPUT_POST, 'force_object', FILTER_VALIDATE_BOOLEAN);

        return json_encode(
            array_merge($this->getMessages(), $this->view->aggregateOutputContent(), $this->view->getRaw()),
            $force_json ? JSON_FORCE_OBJECT : null
        );
    }

    private function getMessages()
    {
        $ci = get_instance();

        return array(
            View::MSG_ERROR   => (array) $ci->system_messages->get_messages('error'),
            View::MSG_INFO    => (array) $ci->system_messages->get_messages('info'),
            View::MSG_SUCCESS => (array) $ci->system_messages->get_messages('success'),
        );
    }
}
