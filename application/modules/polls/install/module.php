<?php

$module['module'] = 'polls';
$module['install_name'] = 'Polls management';
$module['install_descr'] = 'Managing polls and polls statistics';
$module['version'] = '2.03';

$module['files'] = array(
    array('file', 'read', "application/modules/polls/helpers/polls_helper.php"),
    array('file', 'read', "application/modules/polls/controllers/admin_polls.php"),
    array('file', 'read', "application/modules/polls/controllers/api_polls.php"),
    array('file', 'read', "application/modules/polls/controllers/polls.php"),
    array('file', 'read', "application/modules/polls/install/demo_content.php"),
    array('file', 'read', "application/modules/polls/install/module.php"),
    array('file', 'read', "application/modules/polls/install/permissions.php"),
    array('file', 'read', "application/modules/polls/install/settings.php"),
    array('file', 'read', "application/modules/polls/install/structure_deinstall.sql"),
    array('file', 'read', "application/modules/polls/install/structure_install.sql"),
    array('file', 'read', "application/modules/polls/js/admin-polls.js"),
    array('file', 'read', "application/modules/polls/js/polls.js"),
    array('file', 'read', "application/modules/polls/models/polls_install_model.php"),
    array('file', 'read', "application/modules/polls/models/polls_model.php"),
    array('dir', 'read', "application/modules/polls/langs"),
);

$module['dependencies'] = array(
    'start'      => array('version' => '1.03'),
    'menu'       => array('version' => '2.03'),
    'moderation' => array('version' => '1.01'),
    'users'      => array('version' => '3.01'),
);

$module['linked_modules'] = array(
    'install' => array(
        'menu'              => 'install_menu',
        'moderation'        => 'install_moderation',
        'site_map'          => 'install_site_map',
        'moderators'        => 'install_moderators',
        'bonuses'           => 'install_bonuses',
    ),
    'deinstall' => array(
        'menu'              => 'deinstall_menu',
        'moderation'        => 'deinstall_moderation',
        'site_map'          => 'deinstall_site_map',
        'moderators'        => 'deinstall_moderators',
        'bonuses'           => 'deinstall_bonuses',
    ),
);
