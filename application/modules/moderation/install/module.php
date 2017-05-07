<?php

$module['module'] = 'moderation';
$module['install_name'] = 'Moderation module';
$module['install_descr'] = 'Moderating different types of content including users\' uploads, comments';
$module['version'] = '3.01';
$module['files'] = array(
    array('file', 'read', "application/modules/moderation/controllers/admin_moderation.php"),
    array('file', 'read', "application/modules/moderation/install/module.php"),
    array('file', 'read', "application/modules/moderation/install/permissions.php"),
    array('file', 'read', "application/modules/moderation/install/structure_deinstall.sql"),
    array('file', 'read', "application/modules/moderation/install/structure_install.sql"),
    array('file', 'read', "application/modules/moderation/models/moderation_badwords_model.php"),
    array('file', 'read', "application/modules/moderation/models/moderation_install_model.php"),
    array('file', 'read', "application/modules/moderation/models/moderation_model.php"),
    array('file', 'read', "application/modules/moderation/models/moderation_type_model.php"),
    array('dir', 'read', 'application/modules/moderation/langs'),
);
$module['dependencies'] = array(
    'start' => array('version' => '1.03'),
    'menu'  => array('version' => '2.03'),
);
$module['linked_modules'] = array(
    'install' => array(
        'moderators'    => 'install_moderators',
        'menu'          => 'install_menu',
    ),
    'deinstall' => array(
        'moderators'    => 'deinstall_moderators',
        'menu'          => 'deinstall_menu',
    ),
);
