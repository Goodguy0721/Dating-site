<?php

$module["module"] = "statistics";
$module["install_name"] = "Statistics";
$module["install_descr"] = "Statistics management";
$module["version"] = "1.03";
$module["files"] = array(
    array('file', 'read', "application/modules/statistics/controllers/admin_statistics.php"),
    array('file', 'read', "application/modules/statistics/controllers/api_statistics.php"),
    array('file', 'read', "application/modules/statistics/controllers/statistics.php"),
    array('file', 'read', "application/modules/statistics/install/module.php"),
    array('file', 'read', "application/modules/statistics/install/permissions.php"),
    array('file', 'read', "application/modules/statistics/install/settings.php"),
    array('file', 'read', "application/modules/statistics/install/structure_deinstall.sql"),
    array('file', 'read', "application/modules/statistics/install/structure_install.sql"),
    array('file', 'read', "application/modules/statistics/models/statistics_install_model.php"),
    array('file', 'read', "application/modules/statistics/models/statistics_model.php"),
    array("dir", 'read', "application/modules/statistics/langs"),
    array("dir", 'write', "temp/logs/statistics"),
);

$module["dependencies"] = array(
    "menu"  => array("version" => "1.01"),
    "start" => array("version" => "1.01"),
);

$module["libraries"] = array(
);

$module["linked_modules"] = array(
    "install" => array(
        "menu"      => "install_menu",
        "start"     => "install_start",
        'cronjob'   => 'install_cronjob',
    ),
    "deinstall" => array(
        "menu"      => "deinstall_menu",
        "start"     => "deinstall_start",
        'cronjob'   => 'deinstall_cronjob',
    ),
);
