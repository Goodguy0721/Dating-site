<?php

$module['module'] = 'dashboard';
$module['install_name'] = 'Admin dashboard';
$module['install_descr'] = 'The admin dashboard lets you review and moderate all types of content that require moderation or your approval (user profiles, photos, payments, etc.)';
$module['version'] = '1.01';
$module['files'] = array(
    array('dir', 'read', "application/modules/dashboard/langs"),
);
$module['dependencies'] = array(
    'start' => array('version' => '1.01'),
);
$module['libraries'] = array(
);
$module['linked_modules'] = array(
    "install" => array(
        "cronjob" => "install_cronjob",
    ), 
    "deinstall" => array(
        "cronjob" => "deinstall_cronjob",
    ), 
);
