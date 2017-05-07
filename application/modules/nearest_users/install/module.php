<?php

$module['module'] = 'nearest_users';
$module['install_name'] = 'Nearest users';
$module['install_descr'] = 'Nearest users module adds a search page with an interactive map';
$module['version'] = '2.03';
$module['files'] = array(
    array('file', 'read', "application/modules/nearest_users/controllers/admin_nearest_users.php"),
    array('file', 'read', "application/modules/nearest_users/controllers/nearest_users.php"),
    array('file', 'read', "application/modules/nearest_users/install/module.php"),
    array('file', 'read', "application/modules/nearest_users/install/settings.php"),
    array('file', 'read', "application/modules/nearest_users/install/permissions.php"),
    array('file', 'read', "application/modules/nearest_users/models/nearest_users_install_model.php"),
    array('file', 'read', "application/modules/nearest_users/models/nearest_users_model.php"),
    array('dir', 'read', 'application/modules/nearest_users/langs'),
);
$module['dependencies'] = array(
    'start'  => array('version' => '1.01'),
    'geomap' => array('version' => '1.25'),
    'menu'   => array('version' => '2.01'),
);
$module['linked_modules'] = array(
    'install' => array(
        'menu'        => 'install_menu',
        'site_map'    => 'install_site_map',
        'banners'     => 'install_banners',
        "geomap"      => "install_geomap",
    ),
    'deinstall' => array(
        'menu'        => 'deinstall_menu',
        'site_map'    => 'deinstall_site_map',
        'banners'     => 'deinstall_banners',
        "geomap"      => "deinstall_geomap",
    ),
);
