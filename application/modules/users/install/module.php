<?php

$module['module'] = 'users';
$module['install_name'] = 'Site users management';
$module['install_descr'] = 'This module lets you manage site users including their contact info, profile details and so on';
$module['version'] = '6.01';
$module['files'] = array(
    array('file', 'read', "application/modules/users/controllers/admin_users.php"),
    array('file', 'read', "application/modules/users/controllers/api_users.php"),
    array('file', 'read', "application/modules/users/controllers/users.php"),
    array('file', 'read', "application/modules/users/helpers/users_helper.php"),
    array('file', 'read', "application/modules/users/install/demo_structure_install.sql"),
    array('file', 'read', "application/modules/users/install/module.php"),
    array('file', 'read', "application/modules/users/install/permissions.php"),
    array('file', 'read', "application/modules/users/install/settings.php"),
    array('file', 'read', "application/modules/users/install/structure_deinstall.sql"),
    array('file', 'read', "application/modules/users/install/structure_install.sql"),
    array('file', 'read', "application/modules/users/install/user_fields_data_social.php"),
    array('file', 'read', "application/modules/users/install/user_fields_data_dating.php"),
    array('file', 'read', "application/modules/users/models/auth_model.php"),
    array('file', 'read', "application/modules/users/models/groups_model.php"),
    array('file', 'read', "application/modules/users/models/users_install_model.php"),
    array('file', 'read', "application/modules/users/models/users_model.php"),
    array('file', 'read', "application/modules/users/models/users_deleted_model.php"),
    array('file', 'read', "application/modules/users/models/users_delete_callbacks_model.php"),
    array('file', 'read', "application/modules/users/models/users_statuses_model.php"),
    array('file', 'read', "application/modules/users/models/users_views_model.php"),
    //array('file', 'read', "application/config/openid.php"),	// Don't delete (openid)
    array('file', 'read', "application/modules/users/js/users-avatar.js"),
    array('file', 'read', "application/modules/users/js/users-input.js"),
    array('file', 'read', "application/modules/users/js/users-list.js"),
    array('file', 'read', "application/modules/users/js/users-select.js"),
    array('file', 'read', "application/modules/users/js/users_multi_request.js"),
    array('dir', 'write', "uploads/user-logo"),
    array('dir', 'write', "uploads/user-logo/0"),
    array('dir', 'write', "uploads/user-logo/0/0"),
    array('dir', 'write', "uploads/user-logo/0/0/0"),

    array('dir', 'read', 'application/modules/start/langs'),
);

$module['demo_content'] = array(
    'reinstall' => false,
);

$module['dependencies'] = array(
    'start'         => array('version' => '1.03'),
    'menu'          => array('version' => '2.03'),
    'uploads'       => array('version' => '1.03'),
    'moderation'    => array('version' => '1.03'),
    'properties'    => array('version' => '1.03'),
    'countries'     => array('version' => '2.03'),
    'notifications' => array('version' => '1.04'),
    'linker'        => array('version' => '1.01'),
    'field_editor'  => array('version' => '2.01'),
    'cronjob'       => array('version' => '1.04'),
);

$module['linked_modules'] = array(
    'install' => array(
        'menu'                  => 'install_menu',
        'uploads'               => 'install_uploads',
        'site_map'              => 'install_site_map',
        'banners'               => 'install_banners',
        'linker'                => 'install_linker',
        'moderation'            => 'install_moderation',
        'moderators'            => 'install_moderators',
        'notifications'         => 'install_notifications',
        'dynamic_blocks'        => 'install_dynamic_blocks',
        'social_networking'     => 'install_social_networking',
        'field_editor'          => 'install_field_editor',
        'cronjob'               => 'install_cronjob',
        'services'              => 'install_services',
        'geomap'                => 'install_geomap',
        'comments'              => 'install_comments',
        'spam'                  => 'install_spam',
        'network'               => 'install_network',
        'ratings'               => 'install_ratings',
        'bonuses'               => 'install_bonuses',
        'aviary'                => 'install_aviary',
    ),
    'deinstall' => array(
        'menu'                  => 'deinstall_menu',
        'uploads'               => 'deinstall_uploads',
        'content'               => 'deinstall_content',
        'site_map'              => 'deinstall_site_map',
        'banners'               => 'deinstall_banners',
        'linker'                => 'deinstall_linker',
        'moderation'            => 'deinstall_moderation',
        'moderators'            => 'deinstall_moderators',
        'notifications'         => 'deinstall_notifications',
        'dynamic_blocks'        => 'deinstall_dynamic_blocks',
        'social_networking'     => 'deinstall_social_networking',
        'field_editor'          => 'deinstall_field_editor',
        'cronjob'               => 'deinstall_cronjob',
        'services'              => 'deinstall_services',
        'geomap'                => 'deinstall_geomap',
        'comments'              => 'deinstall_comments',
        'spam'                  => 'deinstall_spam',
        'network'               => 'deinstall_network',
        'ratings'               => 'deinstall_ratings',
        'bonuses'               => 'deinstall_bonuses',
    ),
);
