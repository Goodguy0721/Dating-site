<?php

namespace Pg\Libraries;

/**
 * Module config trait
 */
trait ModuleConfigTrait
{
    /**
     * Keys are: 'key', 'value', 'name', 'lang'
     */
    abstract public function getModuleConfigList();

    /**
     * Get config
     *
     * @param array $custom_cfg
     *
     * @return array
     */
    public function getModuleConfig(array $custom_cfg = null)
    {
        $cfg = $custom_cfg ?: $this->getModuleConfigList();
        foreach ($cfg as &$cfg_settings) {
            $cfg_settings['name'] = l('settings_' . $cfg_settings['key'], self::MODULE_GID);
            $cfg_settings['value'] = $this->ci->pg_module->get_module_config(
                    self::MODULE_GID, $cfg_settings['key']
            );
        }

        return $cfg;
    }

    /**
     * Save settings
     *
     * @param array $new_cfg_values
     *
     * @throws \Exception
     *
     * @return array
     */
    public function setModuleConfig(array $new_cfg_values)
    {
        foreach ($new_cfg_values as $key => $value) {
            $this->ci->pg_module->set_module_config(self::MODULE_GID, $key, $value);
        }

        return true;
    }
}
