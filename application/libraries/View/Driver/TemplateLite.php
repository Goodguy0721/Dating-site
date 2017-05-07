<?php

namespace Pg\Libraries\View\Driver;

require_once BASEPATH . 'libraries/Template_lite.php';

// TODO: Переработать и перенести сюда содержимое Template_lite.php
class TemplateLite implements IDriver
{
    private $template_lite;
    private $tpl_extension = 'tpl';

    public function __construct()
    {
        $this->template_lite = new \CI_Template_lite();
    }

    public function getName()
    {
        return 'templateLite';
    }

    public function getTplExtension()
    {
        return $this->tpl_extension;
    }

    public function setTplExtension($extension)
    {
        $this->tpl_extension = $extension;

        return $this;
    }

    public function setDebugging($is_debug)
    {
        $this->template_lite->debugging = false;
    }

    public function assign($key, $value = null)
    {
        $this->template_lite->assign($key, $value);
    }

    public function useCache($use_cache)
    {
        $this->template_lite->force_compile = !$use_cache;
    }

    public function view($template, $module, array $theme)
    {
        if (isset($theme['type'])) {
            $theme_type = $theme['type'];
        } else {
            $theme_type = null;
        }
        ob_start();
        $this->template_lite->view($template, $theme_type, $module, null, true, true);
        $contents = ob_get_contents();
        ob_end_clean();

        return $contents;
    }

    public function clearCache($module = null)
    {
        $this->template_lite->delete_compiled($module);
    }
}
