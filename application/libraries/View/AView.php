<?php

namespace Pg\Libraries\View;

abstract class AView
{
    protected $back_link = null;
    protected $code = null;
    protected $debugging = false;
    protected $driver = null;
    protected $use_cache = true;
    protected $general_path = '';
    protected $global_raw = array();
    protected $global_vars = array();
    protected $headers = array();
    protected $help = null;
    protected $messages = array();
    protected $module = null;
    protected $module_path = '';
    protected $output = null;
    protected $output_formats = array();
    protected $redirect = array();
    protected $renderer = null;
    protected $renderers = array();
    protected $raw = array();
    protected $template = null;
    protected $theme = null;
    protected $theme_settings = array();
    protected $theme_type = null;
    protected $use_profiling = false;
    protected $vars = array();
    protected static $instance;

    protected function __construct($driver = null)
    {
        if ($driver) {
            $this->setDriver($driver);
        }
    }

    public function getBackLink()
    {
        return $this->back_link;
    }

    public function setBackLink($link)
    {
        $this->back_link = $link;

        return $this;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    public function getDebugging()
    {
        return $this->debugging;
    }

    public function setDebugging($is_debugging)
    {
        $this->debugging = $is_debugging;
        return $this;
    }

    /**
     * Get driver
     * @return Driver\IDriver
     */
    public function getDriver()
    {
        return $this->driver;
    }

    public function setDriver(Driver\IDriver $driver)
    {
        $this->driver = $driver;
        return $this;
    }

    public function useCache($use_cache = null)
    {
        if (null !== $use_cache) {
            $this->use_cache = $use_cache;
            if ($this->driver) {
                $this->driver->useCache($use_cache);
            }
        }

        return $this->use_cache;
    }

    protected function shiftFormat()
    {
        if (count($this->output_formats) > 1) {
            $format = array_shift($this->output_formats);
        } else {
            $format = $this->getFormat();
        }

        return $format;
    }

    public function getFormat()
    {
        return current($this->output_formats);
    }

    public function setFormat($format)
    {
        if (!isset($this->output_formats[0]) || $this->output_formats[0] !== $format) {
            array_unshift($this->output_formats, $format);
        }

        return $this;
    }

    public function getGeneralPath()
    {
        return $this->general_path;
    }

    public function setGeneralPath($path)
    {
        $this->general_path = $path;

        return $this;
    }

    public function getHeader()
    {
        return $this->headers;
    }

    public function setHeader($header, $level = 0)
    {
        return $this->setHeaders(array($level => $header));
    }

    public function setHeaders(array $headers)
    {
        $this->headers = $headers;

        return $this;
    }

    public function addHeader($level, $value)
    {
        $this->headers[$level] = $value;

        return $this;
    }

    public function getHelp()
    {
        return $this->help;
    }

    public function setHelp($help)
    {
        $this->help = $help;

        return $this;
    }

    public function getMessages($type = null)
    {
        if (!$type) {
            return $this->messages;
        }
        if (isset($this->messages[$type])) {
            return $this->messages[$type];
        } else {
            return array();
        }
    }

    public function setMessages(array $messages)
    {
        $this->messages = $messages;

        return $this;
    }

    public function addMessage($type, $message)
    {
        if (!isset($this->messages[$type])) {
            $this->messages[$type] = array();
        }
        if (is_array($message)) {
            $this->messages[$type] = array_merge($this->messages[$type], $message);
        } else {
            $this->messages[$type][] = $message;
        }

        return $this;
    }

    public function getModule()
    {
        return $this->module;
    }

    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }

    public function getModulePath()
    {
        return $this->module_path;
    }

    public function setModulePath($path)
    {
        $this->module_path = $path;

        return $this;
    }

    public function getOutput()
    {
        return $this->output;
    }

    public function setOutput($output)
    {
        $this->output = $output;

        return $this;
    }

    public function setRedirect($url = '', $method = 'location', $code = 302)
    {
        $this->redirect = array(
            'url'    => $url,
            'method' => $method,
            'code'   => $code,
        );

        return $this;
    }

    public function getRedirect()
    {
        return $this->redirect;
    }

    public function setRenderer(ARenderer $renderer)
    {
        $this->renderer = $renderer;

        return $this;
    }

    protected function getRenderer()
    {
        return $this->renderer;
    }

    public function setRenderers(array $renderers)
    {
        $this->renderers = $renderers;

        return $this;
    }

    public function getRenderers()
    {
        return $this->renderers;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function setTemplate($tpl)
    {
        $this->template = $tpl;

        return $this;
    }

    public function getTheme()
    {
        return $this->theme;
    }

    public function setTheme(&$theme)
    {
        $this->theme = $theme;

        return $this;
    }

    public function getThemeType()
    {
        return $this->theme_type;
    }

    public function setThemeType($themeType)
    {
        $this->theme_type = $themeType;

        return $this;
    }

    public function getThemeSettings()
    {
        return $this->theme_settings;
    }

    public function setThemeSettings(array $settings)
    {
        $this->theme_settings = $settings;

        return $this;
    }

    public function useProfiling($uses_profiling = null)
    {
        if (isset($uses_profiling)) {
            $this->use_profiling = (bool) $uses_profiling;
        }

        return $this->use_profiling;
    }

    public function setRaw(array $strings)
    {
        $this->raw = $strings;

        return $this;
    }

    public function addRaw($string)
    {
        $this->raw[] = (string) $string;

        return $this;
    }

    public function getRaw($with_global = true)
    {
        if ($with_global) {
            return array_merge($this->global_raw, $this->raw);
        } else {
            return $this->raw;
        }
    }

    public function setGlobalRaw(array $strings)
    {
        $this->global_raw = $strings;

        return $this;
    }

    public function addGlobalRaw($string)
    {
        $this->global_raw[] = (string) $string;

        return $this;
    }

    public function getGlobalRaw()
    {
        return $this->global_raw;
    }

    public function getVar($name)
    {
        if (isset($this->vars[$name])) {
            return $this->vars[$name];
        } elseif (isset($this->global_vars[$name])) {
            return $this->global_vars[$name];
        } else {
            return;
        }
    }

    public function getVars($with_global = true)
    {
        if ($with_global) {
            return array_merge($this->global_vars, $this->vars);
        } else {
            return $this->vars;
        }
    }

    public function setVar($name, $value)
    {
        $this->vars[$name] = $value;

        return $this;
    }

    public function setVars(array $vars)
    {
        $this->vars = $vars;

        return $this;
    }

    public function clearVars()
    {
        $this->vars = array();
        $this->raw = array();

        return $this;
    }

    public function getGlobalVar($name)
    {
        if (isset($this->global_vars[$name])) {
            return $this->global_vars[$name];
        } else {
            return;
        }
    }

    public function getGlobalVars()
    {
        return $this->global_vars;
    }

    public function setGlobalVar($name, $value)
    {
        $this->global_vars[$name] = $value;

        return $this;
    }

    public function setGlobalVars(array $vars)
    {
        $this->global_vars = $vars;

        return $this;
    }

    public function clearGlobalVars()
    {
        $this->global_vars = array();
        $this->global_raw = array();

        return $this;
    }
}
