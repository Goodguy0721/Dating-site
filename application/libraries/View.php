<?php

namespace Pg\Libraries;

class View extends View\AView
{

    const MSG_ERROR = 'error';
    const MSG_INFO = 'info';
    const MSG_SUCCESS = 'success';

    private $is_rendered = array();
    public $output;

    /**
     * Get singleton
     *
     * @return \Pg\Libraries\View
     */
    public static function &getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Set theme settings
     *
     * @param string $module
     * @param string $theme_type
     *
     * @return \Pg\Libraries\View
     */
    public function initModuleTheme($module, $theme_type)
    {
        $this->setThemeType($theme_type);
        $this->setModule($module);
        $theme_settings = $this->getTheme()->format_theme_settings($module, $theme_type);
        if (!is_array($theme_settings)) {
            throw new \RuntimeException('Couldn\'t load theme settings for module "' . $module . '" and theme type "' . $theme_type . '"');
        }
        $this->setThemeSettings($theme_settings);
        $this->assignThemeVars();

        return $this;
    }

    /**
     * Assign common theme vars
     *
     * @return \Pg\Libraries\View
     */
    private function assignThemeVars()
    {
        $theme_data = $this->getThemeSettings();
        $this->assignGlobal(array(
            'theme' => $theme_data['theme'],
            'color_scheme' => $theme_data['scheme'],
            'img_folder' => $theme_data['img_path'],
            'css_folder' => $theme_data['css_path'],
            'logo_settings' => $theme_data['logo'],
            'mini_logo_settings' => $theme_data['mini_logo'],
        ));

        return $this;
    }

    /**
     * Get available message types
     *
     * @return array
     */
    public function getMessageTypes()
    {
        return array(
            self::MSG_ERROR,
            self::MSG_INFO,
            self::MSG_SUCCESS,
        );
    }

    public function setFormat($format)
    {
        if (empty($this->output_formats)) {
            $this->setRenderer(
                $this->pickRenderer($format)
            );
        }
        parent::setFormat($format);

        return $this;
    }

    /**
     * Get output format for the next render.
     * If template is specified, format always will be html
     *
     * @return string
     */
    protected function shiftFormat()
    {
        if ($this->getTemplate()) {
            parent::setFormat('html');
        }

        return parent::shiftFormat();
    }

    /**
     * Get renderer by format
     *
     * @param string $format
     *
     * @throws \Exception
     *
     * @return \Pg\Libraries\renderers
     */
    private function pickRenderer($format)
    {
        if (empty($format)) {
            throw new \Exception("Empty format");
        }
        $renderers = $this->getRenderers();
        if (!in_array($format, array_keys($renderers))) {
            throw new \Exception("No renderer for '$format' format");
        }

        return new $renderers[$format]($this);
    }

    /**
     * Find available renderers
     *
     * @return array
     */
    private function findRenderers()
    {
        $renderer_path = LIBPATH . 'View/Renderer';
        $renderer_namespace = NS_LIB . 'View\\Renderer\\';
        $postfix = '.php';
        $renderers = array();
        foreach (new \DirectoryIterator($renderer_path) as $file_info) {
            if ($file_info->isDot() || $file_info->isDir()) {
                continue;
            }
            $filename = $file_info->getFilename();
            $className = substr($filename, 0, strrpos($filename, $postfix));
            $renderer = strtolower($className);
            $renderers[$renderer] = $renderer_namespace . $className;
        }

        return $renderers;
    }

    /**
     * Get available renderers. Cached.
     *
     * @return array
     */
    public function &getRenderers()
    {
        if (empty($this->renderers)) {
            $this->setRenderers($this->findRenderers());
        }

        return $this->renderers;
    }

    public function setRedirect($url = '', $method = 'location', $code = 302)
    {
        parent::setRedirect($url, $method, $code);
        $this->render();
    }

    /**
     * Aggregate output content in one array.
     *
     * @return array
     */
    public function aggregateOutputContent()
    {
        $output_content = $this->getVars();
        foreach ($this->getMessageTypes() as $msg_type) {
            $messages = $this->getMessages($msg_type);
            if (!empty($messages)) {
                $output_content[$msg_type] = $messages;
            }
        }

        return $output_content;
    }

    /**
     * Assign variable. After the next rendering, these variables will be cleaned.<br>
     * (string, mixed) — assign var <br>
     * (string, null) — echo string<br>
     * (array, mixed) == (string, mixed) for each element
     *
     * @param mixed $name
     * @param mixed $value
     *
     * @return \Pg\Libraries\View
     */
    public function assign($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $name => $val) {
                $this->assign($name, $val);
            }
        } elseif (is_null($value)) {
            $this->addRaw($name);
        } else {
            $this->setVar($name, $value);
        }

        return $this;
    }

    /**
     * Assign variable by reference
     *
     * @throws Exception
     */
    public function assignByRef()
    {
        throw new Exception('Not implemented yet');
    }

    /**
     * Assign global variable, which is not to be cleaned after the next rendering.<br>
     * See assign($name, $value).
     *
     * @param mixed $name
     * @param mixed $value
     *
     * @return \Pg\Libraries\View
     */
    public function assignGlobal($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $name => $val) {
                $this->assignGlobal($name, $val);
            }
        } elseif (is_null($value)) {
            $this->addGlobalRaw($value);
        } else {
            $this->setGlobalVar($name, $value);
        }

        return $this;
    }

    /**
     * Assign global variable by reference
     *
     * @throws Exception
     */
    public function assignGlobalByRef()
    {
        throw new Exception('Not implemented yet');
    }

    /**
     * Add raw data to render
     *
     * @param mixed $var
     *
     * @return \Pg\Libraries\View
     */
    public function output($var)
    {
        $this->addRaw($var);

        return $this;
    }

    /**
     * Set options for the current theme, module and template.
     *
     * @param string $template
     * @param string $theme_type
     * @param string $module
     *
     * @return \Pg\Libraries\View
     */
    private function setOptions($template, $theme_type = 'user', $module = null)
    {
        // TODO: перенести в рендерер
        $reinit_theme = false;
        if ($template !== $this->getTemplate()) {
            // New template
            $this->setTemplate($template);
        }
        if ($theme_type && $theme_type !== $this->getThemeType()) {
            // New theme type (user/admin)
            $this->setThemeType($theme_type);
            $reinit_theme = true;
        }
        if ($module && $module !== $this->getModule()) {
            // New module
            $this->setModule($module);
            $reinit_theme = true;
        } elseif (!$module && $this->getModule()) {
            // Back to previous module
            $CI = &get_instance();
            // TODO: Запоминать при инициализации и не использовать $CI
            $module = $CI->router->class;
            $this->setModule($module);
            $reinit_theme = true;
        }
        if ($reinit_theme) {
            $this->initModuleTheme($module, $theme_type);
        }

        return $this;
    }

    /**
     * Delete compiled templates
     *
     * @param mixed $module_gid
     *
     * @return \Pg\Libraries\View
     */
    public function clearCache($module_gid = null)
    {
        if ($this->driver) {
            $this->driver->clearCache($module_gid);
        } else {
            // TODO: Do something
        }

        return $this;
    }

    /**
     * Reset view
     *
     * @return \Pg\Libraries\View
     */
    private function reset()
    {
        $this->setTemplate(null);

        return $this;
    }

    /**
     * Get output data.
     *
     * @param string  $template   Template name
     * @param string  $theme_type Theme type
     * @param string  $module     Module guid
     * @param boolean $final      If true, hooks will be called
     *
     * @return string
     */
    public function fetch($template = null, $theme_type = null, $module = null, $final = false)
    {
        $this->setOptions($template, $theme_type, $module);
        $this->setRenderer(
            $this->pickRenderer($this->shiftFormat())
        );
        if ($final) {
            load_class('Hooks')->_call_hook('pre_render');
        }

        $this->setOutput($this->getRenderer()->getOutput());

        if ($final) {
            load_class('Hooks')->_call_hook('pre_view');
        }
        $this->clearVars();
        $this->reset();

        return $this->getOutput();
    }

    /**
     * Get output data.
     *
     * @param string $template   Template name
     * @param string $theme_type Theme type
     * @param string $module     Module guid
     *
     * @return string
     */
    public function fetchFinal($template = null, $theme_type = null, $module = null)
    {
        return $this->fetch($template, $theme_type, $module, true);
    }

    /**
     * Render output data.
     *
     * @param string $template   Template name
     * @param string $theme_type Theme type
     * @param string $module     Module guid
     *
     * @return \Pg\Libraries\View
     */
    public function render($template = null, $theme_type = null, $module = null)
    {
        if (!empty($template)) {
            if (is_bool($this->is_rendered)) {
                $this->is_rendered = [];
            }
            $this->is_rendered[$template] = true;
        } else {
            $this->is_rendered = true;
        }

        echo $this->fetch($template, $theme_type, $module, true);

        return $this;
    }

    /**
     * Is template rendered
     *
     * @param string $template
     *
     * @return boolean
     */
    public function isTemplateRendered($template)
    {
        return !empty($this->is_rendered[$template]);
    }

    /**
     * Is rendered
     *
     * @return boolean
     */
    public function isRendered()
    {
        return !empty($this->is_rendered);
    }

    public function templateExists($template)
    {
        $themedata = $this->getThemeSettings();

        return file_exists(SITE_PHYSICAL_PATH . $themedata['theme_module_path'] . $template . '.' . $this->getRenderer()->getExtension());
    }

}
