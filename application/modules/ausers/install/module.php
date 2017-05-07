<?php

$module['module'] = 'ausers';
$module['install_name'] = 'Administrators management';
$module['install_descr'] = 'This module lets you create, edit and delete administrator accounts';
$module['version'] = '6.01';
$module['files'] = array(
    array('file', 'read', "application/hooks/autoload/post_controller_constructor-check_moderator_access.php"),
    array('file', 'read', "application/modules/ausers/controllers/admin_ausers.php"),
    array('file', 'read', "application/modules/ausers/install/module.php"),
    array('file', 'read', "application/modules/ausers/install/permissions.php"),
    array('file', 'read', "application/modules/ausers/install/" . PRODUCT_NAME . "/all/settings.php"),
    array('file', 'read', "application/modules/ausers/install/structure_deinstall.sql"),
    array('file', 'read', "application/modules/ausers/install/structure_install.sql"),
    array('file', 'read', "application/modules/ausers/models/ausers_install_model.php"),
    array('file', 'read', "application/modules/ausers/models/ausers_model.php"),
    array('dir', 'read', "application/modules/ausers/langs"),
);
$module['dependencies'] = array(
    'start' => array('version' => '1.03'),
    'menu'  => array('version' => '2.03'),
);
$module['linked_modules'] = array(
    'install' => array(
        'menu'             => 'install_menu',
        'notifications'    => 'install_notifications',
    ),
    'deinstall' => array(
        'menu'             => 'deinstall_menu',
        'notifications'    => 'deinstall_notifications',
    ),
);
