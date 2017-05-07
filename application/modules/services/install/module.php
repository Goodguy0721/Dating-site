<?php

$module['module'] = 'services';
$module['install_name'] = 'Services module';
$module['install_descr'] = 'The module stores the settings and logs of paid services';
$module['version'] = '4.03';
$module['files'] = array(
    array('file', 'read', "application/modules/services/controllers/admin_services.php"),
    array('file', 'read', "application/modules/services/controllers/api_services.php"),
    array('file', 'read', "application/modules/services/controllers/services.php"),
    array('file', 'read', "application/modules/services/helpers/services_helper.php"),
    array('file', 'read', "application/modules/services/install/demo_content.php"),
    array('file', 'read', "application/modules/services/install/module.php"),
    array('file', 'read', "application/modules/services/install/permissions.php"),
    array('file', 'read', "application/modules/services/install/structure_deinstall.sql"),
    array('file', 'read', "application/modules/services/install/structure_install.sql"),
    array('file', 'read', "application/modules/services/js/services.js"),
    array('file', 'read', "application/modules/services/models/services_install_model.php"),
    array('file', 'read', "application/modules/services/models/services_model.php"),
    array('file', 'read', "application/modules/services/models/services_users_model.php"),
    array('dir', 'read', 'application/modules/services/langs'),
);

$module['dependencies'] = array(
    'start'    => array('version' => '1.03'),
    'menu'     => array('version' => '2.03'),
    'payments' => array('version' => '2.01'),
    'users'    => array('version' => '3.01'),
);

$module['linked_modules'] = array(
    'install' => array(
        'menu'            => 'install_menu',
        'payments'        => 'install_payments',
        'memberships'     => 'install_memberships',
    ),
    'deinstall' => array(
        'menu'            => 'deinstall_menu',
        'payments'        => 'deinstall_payments',
        'memberships'     => 'deinstall_memberships',
    ),
);
