<?php

$module['module'] = 'tickets';
$module['install_name'] = 'Tickets module';
$module['install_descr'] = 'Tickets system for authorized site members, including administrator interface';
$module['version'] = '2.03';
$module['files'] = array(
    array('file', 'read', "application/modules/tickets/controllers/api_tickets.php"),
    array('file', 'read', "application/modules/tickets/controllers/admin_tickets.php"),
    array('file', 'read', "application/modules/tickets/controllers/tickets.php"),
    array('file', 'read', "application/modules/tickets/helpers/tickets_helper.php"),
    array('file', 'read', "application/modules/tickets/js/tickets.js"),
    array('file', 'read', "application/modules/tickets/js/tickets_multi_request.js"),
    array('file', 'read', "application/modules/tickets/install/module.php"),
    array('file', 'read', "application/modules/tickets/install/permissions.php"),
    array('file', 'read', "application/modules/tickets/install/settings.php"),
    array('file', 'read', "application/modules/tickets/install/structure_deinstall.sql"),
    array('file', 'read', "application/modules/tickets/install/structure_install.sql"),
    array('file', 'read', "application/modules/tickets/models/tickets_install_model.php"),
    array('file', 'read', "application/modules/tickets/models/tickets_model.php"),
    array('dir', 'read', "application/modules/tickets/langs"),
);
$module['dependencies'] = array(
    'start'         => array('version' => '1.03'),
    'menu'          => array('version' => '2.03'),
    'moderation'    => array('version' => '1.01'),
    'notifications' => array('version' => '1.04'),
);
$module['linked_modules'] = array(
    'install' => array(
        'menu'                 => 'install_menu',
        'moderation'           => 'install_moderation',
        'banners'              => 'install_banners',
        'notifications'        => 'install_notifications',
        'site_map'             => 'install_site_map',
        'social_networking'    => 'install_social_networking',
    ),

    'deinstall' => array(
        'menu'                 => 'deinstall_menu',
        'moderation'           => 'deinstall_moderation',
        'banners'              => 'deinstall_banners',
        'notifications'        => 'deinstall_notifications',
        'site_map'             => 'deinstall_site_map',
        'social_networking'    => 'deinstall_social_networking',
    ),
);
