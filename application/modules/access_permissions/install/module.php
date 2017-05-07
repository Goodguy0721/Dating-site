<?php

/**
 * Module description
 */
$module['module'] = 'access_permissions';
$module['install_name']  = 'Access permissions';
$module['install_descr'] = 'Access permissions for guests vs authorized members, or men vs women, etc.';
$module['version'] = '1.01';

/**
 * Files and folders module
 */
$module['files']         = [
    ['file', 'read', 'application/modules/access_permissions/views/gentelella/index.twig'],
    ['file', 'read', 'application/modules/access_permissions/views/flatty/index.twig'],
    ['dir', 'read', 'application/modules/access_permissions/langs/'],
];

/**
 *  Module dependencies
 */
$module['dependencies'] = [
    'start' => ['version' => '1.03'],
    'menu' => ['version' => '2.03'],
    'users' => ['version' => '5.02'],
    'users_payments' => ['version' => '1.04'],
];

/**
 * Linked modules
 */
$module['linked_modules'] = [
    'install' => [
        'menu' => 'install_menu',
        'payments'        => 'install_payments',
        'cronjob'         => 'install_cronjob',
    ],
    'deinstall' => [
        'menu' => 'deinstall_menu',
        'payments' => 'deinstall_payments',
        'cronjob' => 'deinstall_cronjob',
    ],
];
