<?php

$module['module'] = 'questions';
$module['install_name'] = 'Questions module';
$module['install_descr'] = 'This icebreaker tool will let site members ask each other questions';
$module['category'] = 'action';
$module['version'] = '2.03';
$module['files'] = array(
    array('file', 'read', "application/modules/questions/controllers/admin_questions.php"),
    array('file', 'read', "application/modules/questions/controllers/api_questions.php"),
    array('file', 'read', "application/modules/questions/controllers/questions.php"),
    array('file', 'read', "application/modules/questions/helpers/questions_helper.php"),
    array('file', 'read', "application/modules/questions/install/module.php"),
    array('file', 'read', "application/modules/questions/install/permissions.php"),
    array('file', 'read', "application/modules/questions/install/settings.php"),
    array('file', 'read', "application/modules/questions/install/structure_deinstall.sql"),
    array('file', 'read', "application/modules/questions/install/structure_install.sql"),
    array('file', 'read', "application/modules/questions/js/questions_form.js"),
    array('file', 'read', "application/modules/questions/models/questions_install_model.php"),
    array('file', 'read', "application/modules/questions/models/questions_model.php"),
    array('file', 'read', "application/modules/questions/views/admin/edit.tpl"),
    array('file', 'read', "application/modules/questions/views/admin/list.tpl"),
    array('file', 'read', "application/modules/questions/views/admin/users_list.tpl"),
    array('file', 'read', "application/modules/questions/views/admin/settings.tpl"),
    array('file', 'read', "application/modules/questions/views/default/list.tpl"),
    array('file', 'read', "application/modules/questions/views/default/questions.tpl"),
    array('dir', 'read', 'application/modules/questions/langs'),
);

$module['dependencies'] = array(
    'start'            => array('version' => '1.01'),
    'menu'             => array('version' => '1.01'),
    'notifications'    => array('version' => '1.01'),
    'users'            => array('version' => '1.01'),
);

$module['linked_modules'] = array(
    'install' => array(
        'menu'             => 'install_menu',
        'notifications'    => 'install_notifications',
        'moderators'       => 'install_moderators',
        'banners'          => 'install_banners',
        'moderation'       => 'install_moderation',
    ),
    'deinstall' => array(
        'menu'             => 'deinstall_menu',
        'notifications'    => 'deinstall_notifications',
        'moderators'       => 'deinstall_moderators',
        'banners'          => 'deinstall_banners',
        'moderation'       => 'deinstall_moderation',
    ),
);
