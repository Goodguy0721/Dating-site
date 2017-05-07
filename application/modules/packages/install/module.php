<?php

$module['module'] = 'packages';
$module['install_name'] = 'Packages module';
$module['install_descr'] = 'The module lets you combine paid services of the site into packages ';
$module['version'] = '3.03';
$module['files'] = array(
    array('file', 'read', 'application/modules/packages/controllers/admin_packages.php'),
    array('file', 'read', 'application/modules/packages/controllers/api_packages.php'),
    array('file', 'read', 'application/modules/packages/controllers/packages.php'),
    array('file', 'read', 'application/modules/packages/helpers/packages_helper.php'),
    array('file', 'read', 'application/modules/packages/install/module.php'),
    array('file', 'read', 'application/modules/packages/install/permissions.php'),
    array('file', 'read', 'application/modules/packages/install/structure_deinstall.sql'),
    array('file', 'read', 'application/modules/packages/install/structure_install.sql'),
    array('file', 'read', 'application/modules/packages/models/packages_install_model.php'),
    array('file', 'read', 'application/modules/packages/models/packages_model.php'),
    array('file', 'read', 'application/modules/packages/models/packages_users_model.php'),
    array('dir', 'read', 'application/modules/packages/langs'),
);

$module['dependencies'] = array(
    'start'    => array('version' => '1.03'),
    'menu'     => array('version' => '2.03'),
    'payments' => array('version' => '2.01'),
    'services' => array('version' => '2.01'),
    'users'    => array('version' => '3.01'),
);

$module['linked_modules'] = array(
    'install' => array(
        'menu'        => 'install_menu',
        'payments'    => 'install_payments',
        'cronjob'     => 'install_cronjob',
    ),
    'deinstall' => array(
        'menu'        => 'deinstall_menu',
        'payments'    => 'deinstall_payments',
        'cronjob'     => 'deinstall_cronjob',
    ),
);
