<?php

$module['module'] = 'notifications';
$module['install_name'] = 'Site alerts management';
$module['install_descr'] = 'Managing templates, texts and settings of email notifications';
$module['version'] = '2.03';
$module['files'] = array(
    array('file', 'read', "application/modules/notifications/controllers/admin_notifications.php"),
    array('file', 'read', "application/modules/notifications/install/module.php"),
    array('file', 'read', "application/modules/notifications/install/permissions.php"),
    array('file', 'read', "application/modules/notifications/install/settings.php"),
    array('file', 'read', "application/modules/notifications/install/structure_deinstall.sql"),
    array('file', 'read', "application/modules/notifications/install/structure_install.sql"),
    array('file', 'read', "application/modules/notifications/models/notifications_install_model.php"),
    array('file', 'read', "application/modules/notifications/models/notifications_model.php"),
    array('file', 'read', "application/modules/notifications/models/templates_model.php"),
    array('file', 'read', "application/modules/notifications/models/sender_model.php"),
    array('dir', 'read', 'application/modules/notifications/langs'),
);
$module['dependencies'] = array(
    'start' => array('version' => '1.03'),
    'menu'  => array('version' => '2.03'),
);
$module['linked_modules'] = array(
    'install' => array(
        'menu'          => 'install_menu',
        'cronjob'       => 'install_cronjob',
        'moderators'    => 'install_moderators',
    ),
    'deinstall' => array(
        'menu'          => 'deinstall_menu',
        'cronjob'       => 'deinstall_cronjob',
        'moderators'    => 'deinstall_moderators',
    ),
);
