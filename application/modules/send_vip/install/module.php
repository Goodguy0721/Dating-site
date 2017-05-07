<?php

$module['module'] = 'send_vip';
$module['install_name'] = 'Gift of membership';
$module['install_descr'] = 'The module allows users to make a gift of paid membership to other members of the site.';
$module['version'] = '2.03';
$module['files'] = array(
    array('file', 'read', "application/modules/send_vip/controllers/admin_send_vip.php"),
    array('file', 'read', "application/modules/send_vip/controllers/api_send_vip.php"),
    array('file', 'read', "application/modules/send_vip/controllers/send_vip.php"),
    array('file', 'read', "application/modules/send_vip/install/module.php"),
    array('file', 'read', "application/modules/send_vip/install/permissions.php"),
    array('file', 'read', "application/modules/send_vip/install/settings.php"),
    array('file', 'read', "application/modules/send_vip/install/structure_deinstall.sql"),
    array('file', 'read', "application/modules/send_vip/install/structure_install.sql"),
    array('file', 'read', "application/modules/send_vip/models/send_vip_install_model.php"),
    array('file', 'read', "application/modules/send_vip/models/send_vip_model.php"),
    array('dir', 'read', "application/modules/send_vip/langs"),
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
