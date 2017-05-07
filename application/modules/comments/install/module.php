<?php

$module['module'] = 'comments';
$module['install_name'] = 'Comments module';
$module['install_descr'] = 'This module lets site members post comments on the site ';
$module['version'] = '3.02';
$module['files'] = array(
    array('file', 'read', "application/modules/comments/controllers/admin_comments.php"),
    array('file', 'read', "application/modules/comments/controllers/api_comments.php"),
    array('file', 'read', "application/modules/comments/controllers/comments.php"),
    array('file', 'read', "application/modules/comments/helpers/comments_helper.php"),
    array('file', 'read', "application/modules/comments/install/demo_structure_install.sql"),
    array('file', 'read', "application/modules/comments/install/module.php"),
    array('file', 'read', "application/modules/comments/install/permissions.php"),
    array('file', 'read', "application/modules/comments/install/settings.php"),
    array('file', 'read', "application/modules/comments/install/structure_deinstall.sql"),
    array('file', 'read', "application/modules/comments/install/structure_install.sql"),
    array('file', 'read', "application/modules/comments/js/comments.js"),
    array('file', 'read', "application/modules/comments/models/comments_install_model.php"),
    array('file', 'read', "application/modules/comments/models/comments_model.php"),
    array('file', 'read', "application/modules/comments/models/comments_types_model.php"),
    array('dir', 'read', 'application/modules/comments/langs'),
);

$module['demo_content'] = array(
    'reinstall' => false,
);

$module['dependencies'] = array(
    'start'            => array('version' => '1.03'),
    'menu'             => array('version' => '2.03'),
    'moderation'       => array('version' => '1.03'),
);

$module['linked_modules'] = array(
    'install' => array(
        'menu'             => 'install_menu',
        'moderation'       => 'install_moderation',
        'spam'             => 'install_spam',
        'users'            => 'install_users',
    ),
    'deinstall' => array(
        'menu'             => 'deinstall_menu',
        'moderation'       => 'deinstall_moderation',
        'spam'             => 'deinstall_spam',
        'users'            => 'deinstall_users',
    ),
);
