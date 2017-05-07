<?php

$module['module'] = 'payments';
$module['install_name'] = 'Payments module';
$module['install_descr'] = 'Payments settings including payment systems activation, payments history, manual payments moderation';
$module['version'] = '4.01';
$module['files'] = array(
    array('file', 'read', "application/modules/payments/helpers/payments_helper.php"),
    array('file', 'read', "application/modules/payments/controllers/admin_payments.php"),
    array('file', 'read', "application/modules/payments/controllers/api_payments.php"),
    array('file', 'read', "application/modules/payments/controllers/payments.php"),
    array('file', 'read', "application/modules/payments/install/demo_structure_install.sql"),
    array('file', 'read', "application/modules/payments/install/module.php"),
    array('file', 'read', "application/modules/payments/install/permissions.php"),
    array('file', 'read', "application/modules/payments/install/settings.php"),
    array('file', 'read', "application/modules/payments/install/structure_deinstall.sql"),
    array('file', 'read', "application/modules/payments/install/structure_install.sql"),
    array('file', 'read', "application/modules/payments/js/admin-payments-settings.js"),
    array('file', 'read', "application/modules/payments/js/admin-payments.js"),
    array('file', 'read', "application/modules/payments/js/payment-system-tarifs.js"),
    array('file', 'read', "application/modules/payments/models/systems/offline_model.php"),
    array('file', 'read', "application/modules/payments/models/payment_currency_model.php"),
    array('file', 'read', "application/modules/payments/models/payment_driver_model.php"),
    array('file', 'read', "application/modules/payments/models/payment_systems_model.php"),
    array('file', 'read', "application/modules/payments/models/payments_install_model.php"),
    array('file', 'read', "application/modules/payments/models/payments_model.php"),
    array('file', 'read', "application/modules/payments/models/xe_currency_rates_model.php"),
    array('file', 'read', "application/modules/payments/models/yahoo_currency_rates_model.php"),
    array('dir', 'read', "application/modules/payments/langs"),
    array('dir', 'write', "uploads/payments-logo"),
);

$module['libraries'] = array(
    'jwt',
    'simple_html_dom',
);

$module['dependencies'] = array(
    'start'         => array('version' => '1.03'),
    'menu'          => array('version' => '2.03'),
    'moderation'    => array('version' => '1.01'),
    'users'         => array('version' => '3.01'),
    'notifications' => array('version' => '1.04'),
);

$module['linked_modules'] = array(
    'install' => array(
        'menu'              => 'install_menu',
        'moderation'        => 'install_moderation',
        'moderators'        => 'install_moderators',
        'cronjob'           => 'install_cronjob',
        'notifications'     => 'install_notifications',
    ),
    'deinstall' => array(
        'menu'              => 'deinstall_menu',
        'moderation'        => 'deinstall_moderation',
        'moderators'        => 'deinstall_moderators',
        'cronjob'           => 'deinstall_cronjob',
        'notifications'     => 'deinstall_notifications',
    ),
);
