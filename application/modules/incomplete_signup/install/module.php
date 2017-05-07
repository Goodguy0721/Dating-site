<?php

$module['module'] = 'incomplete_signup';
$module['install_name'] = 'Incomplete signup';
$module['install_descr'] = 'This module collects available info on the site visitors who started the process of registration and for this or that reason failed to complete it';
$module['version'] = '2.02';
$module['files'] = array(
    array('file', 'read', "application/modules/incomplete_signup/controllers/incomplete_signup.php"),
    array('file', 'read', "application/modules/incomplete_signup/controllers/admin_incomplete_signup.php"),
    array('file', 'read', "application/modules/incomplete_signup/helpers/incomplete_signup_helper.php"),
    array('file', 'read', "application/modules/incomplete_signup/install/module.php"),
    array('file', 'read', "application/modules/incomplete_signup/install/permissions.php"),
    array('file', 'read', "application/modules/incomplete_signup/install/settings.php"),
    array('file', 'read', "application/modules/incomplete_signup/install/structure_deinstall.sql"),
    array('file', 'read', "application/modules/incomplete_signup/install/structure_install.sql"),
    array('file', 'read', "application/modules/incomplete_signup/models/incomplete_signup_install_model.php"),
    array('file', 'read', "application/modules/incomplete_signup/models/incomplete_signup_model.php"),
    array('file', 'read', "application/modules/incomplete_signup/js/incomplete_signup.js"),
    array('dir', 'read', 'application/modules/incomplete_signup/langs'),
);

$module['dependencies'] = array(
    'start'    => array('version' => '1.01'),
    'menu'     => array('version' => '1.01'),
    'users'    => array('version' => '1.01'),
);

$module['linked_modules'] = array(
    'install' => array(
        'start'                                 => 'install_start',
        'menu'                                  => 'install_menu',
                'notifications'                 => 'install_notifications',
    ),
    'deinstall' => array(
        'start'                                 => 'deinstall_start',
        'menu'                                  => 'deinstall_menu',
                'notifications'                 => 'deinstall_notifications',
    ),
);
