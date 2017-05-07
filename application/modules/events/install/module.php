<?php

$module['module'] = 'events';
$module['install_name'] = 'Events module';
$module['install_descr'] = 'Let your site members create events, join events and have fun — all the more for better socializing';
$module['category'] = 'action';
$module['version'] = '1.01';
$module['files'] = array(
    array('file', 'read', "application/modules/events/controllers/admin_events.php"),
    array('file', 'read', "application/modules/events/controllers/api_events.php"),
    array('file', 'read', "application/modules/events/controllers/events.php"),
    array('file', 'read', "application/modules/events/helpers/events_helper.php"),
    array('file', 'read', "application/modules/events/install/module.php"),
    array('file', 'read', "application/modules/events/install/permissions.php"),
    array('file', 'read', "application/modules/events/install/settings.php"),
    array('file', 'read', "application/modules/events/install/structure_deinstall.sql"),
    array('file', 'read', "application/modules/events/install/structure_install.sql"),
    array('file', 'read', "application/modules/events/js/events.js"),
    array('file', 'read', "application/modules/events/models/events_install_model.php"),
    array('file', 'read', "application/modules/events/models/events_model.php"),
    array('file', 'read', "application/modules/events/views/admin/settings.tpl"),
    array('file', 'read', "application/modules/events/views/default/index.tpl"),
    array('dir', 'read', "application/modules/events/langs"),
);

$module['dependencies'] = array(
    'start'         => array('version' => '1.01'),
    'menu'          => array('version' => '1.01'),
    'users'         => array('version' => '1.01'),
    'notifications' => array('version' => '1.01'),
    'uploads'       => array('version' => '1.01'),
    'countries'     => array('version' => '1.01'),
);

$module['linked_modules'] = array(
    'install' => array(
        'start'             => 'install_start',
        'menu'              => 'install_menu',
        'notifications'     => 'install_notifications',
        'users'             => 'install_users',
        'moderation'        => 'install_moderation',
        'moderators'        => 'install_moderators',
        'banners'           => 'install_banners',
        'uploads'           => 'install_uploads',
        'video_uploads'     => 'install_video_uploads',
        'media'             => 'install_media',
        'countries'         => 'install_countries',
        'wall_events'       => 'install_wall_events',
        'comments'          => 'install_comments',
    ),
    'deinstall' => array(
        'start'             => 'deinstall_start',
        'menu'              => 'deinstall_menu',
        'notifications'     => 'deinstall_notifications',
        'users'             => 'deinstall_users',
        'moderation'        => 'deinstall_moderation',
        'moderators'        => 'deinstall_moderators',
        'banners'           => 'deinstall_banners',
        'uploads'           => 'deinstall_uploads',
        'video_uploads'     => 'deinstall_video_uploads',
        'media'             => 'deinstall_media',
        'countries'         => 'deinstall_countries',
        'wall_events'       => 'deinstall_wall_events',
        'comments'          => 'deinstall_comments',
    ),
);
