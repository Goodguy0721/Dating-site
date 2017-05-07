<?php

$module['module'] = 'widgets';
$module['install_name'] = 'Widgets module';
$module['install_descr'] = 'This module is a hub that allows you to install and activate user widgets and other types of widgets on your site';
$module['version'] = '2.02';
$module['files'] = array(
    array('file', 'read', "application/modules/widgets/controllers/admin_widgets.php"),
    array('file', 'read', "application/modules/widgets/controllers/widgets.php"),
    array('file', 'read', "application/modules/widgets/install/module.php"),
    array('file', 'read', "application/modules/widgets/install/permissions.php"),
    array('file', 'read', "application/modules/widgets/install/settings.php"),
    array('file', 'read', "application/modules/widgets/install/structure_deinstall.sql"),
    array('file', 'read', "application/modules/widgets/install/structure_install.sql"),
    array('file', 'read', "application/modules/widgets/models/widgets_install_model.php"),
    array('file', 'read', "application/modules/widgets/models/widgets_model.php"),
);

$module['dependencies'] = array(
    'start' => array('version' => '1.01'),
    'menu'  => array('version' => '1.01'),
);

$module['linked_modules'] = array(
    'install' => array(
        'menu'          => 'install_menu',
        'moderators'    => 'install_moderators',
    ),
    'deinstall' => array(
        'menu'          => 'deinstall_menu',
        'moderators'    => 'deinstall_moderators',
    ),
);
