<?php

namespace Pg\Libraries\View\Driver;

use IDriver;

class Native implements IDriver
{
    protected $tpl_extension;
    protected $vars = array();

    public function setTplExtension($ext)
    {
        $this->tpl_extension = $ext;

        return $this;
    }

    public function getTplExtension()
    {
        return $this->tpl_extension;
    }

    public function assign($key, $val)
    {
        $this->vars[$key] = $val;

        return $this;
    }

    public function view($resource_name, $theme_type = null, $module = true, $cache_id = null, $display = true, $system_messages = true)
    {
    }

    public function clearCache($module = null)
    {
    }
}
