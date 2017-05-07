<?php

namespace Pg\Libraries;

class Setup
{
    const TYPE_ACL = 'acl';
    const TYPE_ACCESS_PERMISSIONS = 'access_permissions';
    const TYPE_DEMO_CONTENT = 'demo_content';
    const TYPE_MODULES_DATA = 'modules_data';
    const TYPE_SETTINGS = 'settings';
    const TYPE_PERMISSIONS = 'permissions';
    const PACKAGE_ALL = 'all';

    private static $types = [
        self::TYPE_ACL,
        self::TYPE_ACCESS_PERMISSIONS,
        self::TYPE_DEMO_CONTENT,
        self::TYPE_MODULES_DATA,
        self::TYPE_SETTINGS,
        self::TYPE_PERMISSIONS,
    ];

    protected static function isValidType($type)
    {
        return in_array($type, self::$types);
    }

    protected static function getModuleInstallPath($module)
    {
        return MODULEPATH . $module . '/install/' . PRODUCT_NAME . '/';
    }

    protected static function getModulePackageInstallPath($module, $package)
    {
        return self::getModuleInstallPath($module) . $package . '/';
    }

    private static function readFile($file_name)
    {
        if (!is_file($file_name)) {
            log_message('debug', '(Setup) Package settings are empty');
            $data = null;
        } else {
            $data = require $file_name;
        }
        return $data;
    }

    private static function readModulePackageData($module, $package, $data_type)
    {
        return self::readFile(
            self::getModulePackageInstallPath($module, $package) . $data_type . '.php'
        );
    }

    /**
     *
     * @param string $module
     * @param string $data_type
     * @return array
     * @throws \BadMethodCallException
     * @throws \UnexpectedValueException
     */
    public static function getModuleData($module, $data_type)
    {
        if (!self::isValidType($data_type)) {
            throw new \BadMethodCallException('Wrong data type');
        }
        $current_package_data = self::readModulePackageData($module, PACKAGE_NAME, $data_type);
        if (is_array($current_package_data)) {
            return $current_package_data;
        } else {
            return self::readModulePackageData($module, self::PACKAGE_ALL, $data_type);
        }
    }

}
