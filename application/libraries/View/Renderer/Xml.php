<?php

namespace Pg\Libraries\View\Renderer;

use Pg\Libraries\View\ARenderer;

class Xml extends ARenderer
{
    const FORMAT_NAME = 'xml';
    const FORMAT_EXTENSION = 'xml';

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
        $CI = &get_instance();
        $CI->load->library('array2xml');

        return $CI->array2xml->convert($this->view->aggregateOutputContent());
    }
}
