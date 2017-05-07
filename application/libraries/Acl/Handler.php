<?php

namespace Pg\Libraries\Acl;

abstract class Handler
{
    protected $ci;

    public function __construct($auto_call = false)
    {
        $this->ci = &get_instance();
        if ($auto_call) {
            $this->render();
        }
    }

    abstract public function render();
}
