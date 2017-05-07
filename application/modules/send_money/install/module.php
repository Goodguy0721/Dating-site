<?php

$module['module'] = 'send_money';
$module['install_name'] = 'Money gifts';
$module['install_descr'] = 'The module lets users make money gifts to other site members.';
$module['version'] = '2.02';
$module['files'] = array(
    array('file', 'read', "application/modules/send_money/controllers/admin_send_money.php"),
    array('file', 'read', "application/modules/send_money/controllers/api_send_money.php"),
    array('file', 'read', "application/modules/send_money/controllers/send_money.php"),
    array('file', 'read', "application/modules/send_money/install/module.php"),
    array('file', 'read', "application/modules/send_money/install/permissions.php"),
    array('file', 'read', "application/modules/send_money/install/settings.php"),
    array('file', 'read', "application/modules/send_money/install/structure_deinstall.sql"),
    array('file', 'read', "application/modules/send_money/install/structure_install.sql"),
    array('file', 'read', "application/modules/send_money/models/send_money_install_model.php"),
    array('file', 'read', "application/modules/send_money/models/send_money_model.php"),
    array('dir', 'read', "application/modules/send_money/langs"),
);
$module['dependencies'] = array(
    'start'          => array('version' => '1.01'),
    'menu'           => array('version' => '2.05'),
    'users_payments' => array('version' => '1.04'),
    'payments'       => array('version' => '2.03'),
    'notifications'  => array('version' => '1.06'),
    'mailbox'        => array('version' => '2.02'),
);
$module['linked_modules'] = array(
    'install' => array(
        'menu'          => 'install_menu',
        'notifications' => 'install_notifications',
        'payments'      => 'install_payments',
    ),
    'deinstall' => array(
        'menu'          => 'deinstall_menu',
        'notifications' => 'deinstall_notifications',
        'payments'      => 'deinstall_payments',
    ),
);
