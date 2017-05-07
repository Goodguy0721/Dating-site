<?php

namespace Pg\Libraries\Acl\Resource;

use Pg\Libraries\Acl\Resource;

class Page extends Resource
{

    private $module_is_required = false;

    public function __construct(array $data)
    {
        $this->checkInputData($data);

        $this->type = $this->buildType($data);
        $this->id = 0;
        return $this;
    }

    private function buildType($data)
    {
        if (isset($data['module'])) {
            $prefix = $data['module'] . '_';
        } else {
            $prefix = '';
        }
        return $prefix . $data['controller'] . '_' . $data['action'];
    }

    private function checkInputData($data)
    {
        if (empty($data['controller'])) {
            throw new \BadMethodCallException('"controller" item is required');
        } elseif (empty($data['action'])) {
            throw new \BadMethodCallException('"action" item is required');
        } if ($this->module_is_required && empty($data['module'])) {
            throw new \BadMethodCallException('"module" item is required');
        }
    }

}
