<?php

use Pg\Modules\Fast_navigation\Models\Fast_navigation_model;

$module['module'] = Fast_navigation_model::MODULE_GID;
$module['install_name']   = 'Fast navigation';
$module['install_descr']  = 'Quickly find admin panel sections by headline keywords';
$module['version']  = '1.01';
$module['files'] = [
    ['file', 'read', "application/modules/fast_navigation/controllers/admin_fast_navigation.php"],
    ['file', 'read', "application/modules/fast_navigation/helpers/fast_navigation_helper.php"],
    ['file', 'read', "application/modules/fast_navigation/install/structure_install.sql"],
    ['file', 'read', "application/modules/fast_navigation/install/module.php"],
    ['file', 'read', "application/modules/fast_navigation/install/" . PRODUCT_NAME . "/all/permissions.php"],
    ['file', 'read', "application/modules/fast_navigation/install/" . PRODUCT_NAME . "/all/settings.php"],
    ['file', 'read', "application/modules/fast_navigation/install/structure_deinstall.sql"],
    ['dir', 'read', "application/modules/fast_navigation/langs"],
    ['file', 'read', "application/modules/fast_navigation/models/fast_navigation_model.php"],
];
$module['linked_modules'] = [
    'install' => [
        'moderators' => 'install_moderators',
        'start' => 'install_menu',
        'cronjob' => 'install_cronjob',
    ],
    'deinstall' => [
        'moderators' => 'deinstall_moderators',
        'start' => 'deinstall_menu',
        'cronjob' => 'deinstall_cronjob',
    ],
];
