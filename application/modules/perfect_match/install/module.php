<?php

$module["module"] = "perfect_match";
$module["install_name"] = "Perfect match";
$module["install_descr"] = "Perfect match adds 'looking for' fields to user profile. It also does pre-search by gender, age, and location. You can add more criteria.";
$module["version"] = "1.02";
$module["files"] = array(
    array('file', 'read', "application/modules/perfect_match/controllers/perfect_match.php"),
    array('file', 'read', "application/modules/perfect_match/helpers/perfect_match_helper.php"),
    array('file', 'read', "application/modules/perfect_match/install/module.php"),
    array('file', 'read', "application/modules/perfect_match/install/permissions.php"),
    array('file', 'read', "application/modules/perfect_match/install/settings.php"),
    array('file', 'read', "application/modules/perfect_match/install/structure_deinstall.sql"),
    array('file', 'read', "application/modules/perfect_match/install/structure_install.sql"),
    array('file', 'read', "application/modules/perfect_match/models/perfect_match_install_model.php"),
    array('file', 'read', "application/modules/perfect_match/models/perfect_match_model.php"),
    array('dir',  'read', 'application/modules/perfect_match/langs'),
);

$module["dependencies"] = array(
    "start"        => array("version" => "1.01"),
    "menu"         => array("version" => "1.01"),
    "field_editor" => array("version" => "2.04"),
    "users"        => array("version" => "4.02"),
);

$module["linked_modules"] = array(
    "install" => array(
        "menu"             => "install_menu",
        'users'            => 'install_users',
        'field_editor'     => 'install_field_editor',
        'banners'          => 'install_banners',
    ),
    "deinstall" => array(
        "menu"             => "deinstall_menu",
        'users'            => 'deinstall_users',
        'field_editor'     => 'deinstall_field_editor',
        'banners'          => 'deinstall_banners',
    ),
);
