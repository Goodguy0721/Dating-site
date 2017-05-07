<?php

namespace Pg\Libraries\View\Driver;

interface IDriver
{
    public function view($template, $module, array $theme);

    public function assign($key, $value);

    public function setTplExtension($ext);

    public function getName();

    public function getTplExtension();

    public function setDebugging($is_debugging);

    public function useCache($use_cache);

    public function clearCache($module = null);
}
