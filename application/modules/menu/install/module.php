<?php

$module['module'] = 'menu';
$module['install_name'] = 'Menu management';
$module['install_descr'] = 'The module allows you to create and edit user and admin menus on the site';
$module['version'] = '3.02';
$module['files'] = array(
    array('file', 'read', "application/modules/menu/controllers/admin_menu.php"),
    array('file', 'read', "application/modules/menu/install/module.php"),
    array('file', 'read', "application/modules/menu/install/permissions.php"),
    array('file', 'read', "application/modules/menu/install/settings.php"),
    array('file', 'read', "application/modules/menu/install/structure_deinstall.sql"),
    array('file', 'read', "application/modules/menu/install/structure_install.sql"),
    array('file', 'read', "application/modules/menu/js/menu-bookmark.js"),
    array('file', 'read', "application/modules/menu/models/menu_install_model.php"),
    array('file', 'read', "application/modules/menu/models/menu_model.php"),
    array('file', 'read', "application/modules/menu/models/indicators_model.php"),
    array('file', 'read', "application/modules/menu/helpers/menu_helper.php"),
    array('dir', 'read', "application/modules/menu/langs"),
    array('file', 'read', "application/hooks/autoload/post_controller_constructor-fetch_menu_indicators.php"),
);
$module['linked_modules'] = array(
    'install' => array(
        'moderators'    => 'install_moderators',
        'start'         => 'install_menu',
    ),
    'deinstall' => array(
        'moderators'    => 'deinstall_moderators',
        'start'         => 'deinstall_menu',
    ),
);
