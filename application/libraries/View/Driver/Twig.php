<?php

namespace Pg\Libraries\View\Driver;

class Twig implements IDriver
{
    const COMMON_NAMESPACE = 'app';

    private $cache_dir = 'twig/compiled';
    private $cache_enabled = true;
    private $is_debugging = false;
    private $tpl_extension = 'twig';
    private $twig;
    private $vars = array();

    public function __construct()
    {
        if (!empty($_ENV['TPL_CLEAR_CACHE'])) {
            $this->clearCache();
        }
    }

    public function getName()
    {
        return 'twig';
    }

    public function setDebugging($is_debugging)
    {
        $this->is_debugging = $is_debugging;
    }

    public function useCache($use_cache)
    {
        $this->cache_enabled = (bool) $use_cache;
    }

    private function getLoader(array $theme)
    {
        $loader = new \Twig_Loader_Filesystem();
        $loader->setPaths(SITE_PHYSICAL_PATH . $theme['theme_module_path']);
        $loader->addPath(SITE_PHYSICAL_PATH . $theme['theme_path'], self::COMMON_NAMESPACE);
        return $loader;
    }

    private function init($theme)
    {
        $this->twig = new \Twig_Environment($this->getLoader($theme), array(
            'cache'         => $this->cache_enabled ? (TEMPPATH . $this->cache_dir) : false,
            'debug'         => $this->is_debugging,
            'autoescape'    => false,
            'auto_reload'   => $_ENV['TPL_AUTO_RELOAD'],
        ));

        $this->twig->registerUndefinedFunctionCallback(function ($function_name) {
            if (!function_exists($function_name)) {
                $function = function () use ($function_name) {
                    return "twig function '$function_name' not exists";
                };
            } else {
                $function = $function_name;
            }

            return new \Twig_SimpleFunction($function_name, $function);
        });

        $this->twig->addExtension(new Twig\Extension());
    }

    public function getTplExtension()
    {
        return $this->tpl_extension;
    }

    public function setTplExtension($ext)
    {
        $this->tpl_extension = $ext;

        return $this;
    }

    public function assign($key, $value)
    {
        $this->vars[$key] = $value;
    }

    public function view($resource_name, $module_gid, array $theme)
    {
        $this->init($theme);

        try {
            $tpl_name = $resource_name . '.' . $this->tpl_extension;

            return $this->twig->resolveTemplate(array($tpl_name, '@' . self::COMMON_NAMESPACE . '/' . $tpl_name))
                              ->render($this->vars);
        } catch (\Twig_Error_Loader $ex) {
            if ($this->twig->isDebug()) {
                return $ex->getMessage() . '<br>' . $ex->getFile() . ':' . $ex->getLine();
            }
        }
    }

    public function clearCache($module = null)
    {
        $twig = new \Twig_Environment(null, array(
            'cache' => TEMPPATH . $this->cache_dir,
        ));
        // TODO: Clean by module
        return $twig->clearCacheFiles();
    }
}
