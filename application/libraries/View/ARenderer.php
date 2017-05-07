<?php

namespace Pg\Libraries\View;

use Pg\Libraries\View;

abstract class ARenderer
{
    protected $view;
    protected $output = '';

    public function __construct(View & $view = null)
    {
        if ($view) {
            $this->setView($view);
        }
    }

    public function setView(&$view)
    {
        $this->view = $view;

        return $this;
    }

    public function getView()
    {
        return $this->view;
    }

    public function setOutput($output)
    {
        $this->output = $output;

        return $this;
    }

    public function getOutput()
    {
        if (empty($this->output)) {
            $this->output = $this->render();
        }

        return (string) $this->output;
    }

    abstract protected function render();

    abstract public function getName();

    abstract public function getExtension();

    public function __toString()
    {
        echo $this->getName();
    }
}
