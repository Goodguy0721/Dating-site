<?php

$module['module'] = 'associations';
$module['install_name'] = 'Associations module';
$module['install_descr'] = 'This module lets site members establish contact by comparing each other to different objects';
$module['category'] = 'action';
$module['version'] = '2.03';
$module['files'] = array(
    array('file', 'read', "application/modules/associations/controllers/admin_associations.php"),
    array('file', 'read', "application/modules/associations/controllers/api_associations.php"),
    array('file', 'read', "application/modules/associations/controllers/associations.php"),
    array('file', 'read', "application/modules/associations/helpers/associations_helper.php"),
    array('file', 'read', "application/modules/associations/install/module.php"),
    array('file', 'read', "application/modules/associations/install/permissions.php"),
    array('file', 'read', "application/modules/associations/install/settings.php"),
    array('file', 'read', "application/modules/associations/install/structure_deinstall.sql"),
    array('file', 'read', "application/modules/associations/install/structure_install.sql"),
    array('file', 'read', "application/modules/associations/js/associations.js"),
    array('file', 'read', "application/modules/associations/models/associations_install_model.php"),
    array('file', 'read', "application/modules/associations/models/associations_model.php"),
    array('dir', 'read', "application/modules/associations/langs"),
    array('dir', 'write', "uploads/associations"),
    array('dir', 'write', "uploads/associations/0"),
    array('dir', 'write', "uploads/associations/0/0"),
    array('dir', 'write', "uploads/associations/0/0/0"),
    array('dir', 'write', "uploads/associations/2"),
    array('dir', 'write', "uploads/associations/2/copy"),
    array('dir', 'write', "uploads/associations/4/"),
    array('dir', 'write', "uploads/associations/4/copy"),
    array('dir', 'write', "uploads/associations/5/"),
    array('dir', 'write', "uploads/associations/5/copy"),
    array('dir', 'write', "uploads/associations/6"),
    array('dir', 'write', "uploads/associations/6/copy"),
);

$module['dependencies'] = array(
    'start'         => array('version' => '1.01'),
    'menu'          => array('version' => '1.01'),
    'users'         => array('version' => '1.01'),
    'notifications' => array('version' => '1.01'),
    'uploads'       => array('version' => '1.01'),
);

$module['linked_modules'] = array(
    'install' => array(
        'banners'       => 'install_banners',
        'menu'          => 'install_menu',
        'moderation'  => 'install_moderation',
        'moderators'   => 'install_moderators',
        'notifications' => 'install_notifications',
        'start'         => 'install_start',
        'uploads'       => 'install_uploads',
        'users'         => 'install_users',
    ),
    'deinstall' => array(
        'banners'       => 'deinstall_banners',
        'menu'          => 'deinstall_menu',
        'moderation'  => 'deinstall_moderation',
        'moderators'   => 'deinstall_moderators',
        'notifications' => 'deinstall_notifications',
        'start'         => 'deinstall_start',
        'uploads'       => 'deinstall_uploads',
        'users'         => 'deinstall_users',
    ),
);
