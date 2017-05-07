<?php

$module["module"] = "guided_setup";
$module["install_name"] = "Guided setup";
$module["install_descr"] = "This quick setup tool makes it easier to manage the most important site options, directly from the admin dashboard";
$module['category'] = 'action';
$module["version"] = "1.01";
$module["files"] = array(
    array('file', 'read', "application/modules/guided_setup/controllers/admin_guided_setup.php"),
    array('file', 'read', "application/modules/guided_setup/helpers/guided_setup_helper.php"),
    array('file', 'read', "application/modules/guided_setup/install/module.php"),
    array('file', 'read', "application/modules/guided_setup/install/permissions.php"),
    array('file', 'read', "application/modules/guided_setup/install/settings.php"),
    array('file', 'read', "application/modules/guided_setup/install/structure_deinstall.sql"),
    array('file', 'read', "application/modules/guided_setup/install/structure_install.sql"),
    array('file', 'read', "application/modules/guided_setup/js/guided_setup.js"),
    array('file', 'read', "application/modules/guided_setup/langs/en/pages.php"),
    array('file', 'read', "application/modules/guided_setup/models/guided_setup_install_model.php"),
    array('file', 'read', "application/modules/guided_setup/models/guided_setup_model.php"),
    array('file', 'read', "application/modules/guided_setup/views/gentelella/scss/style.scss"),
);

$module["dependencies"] = array(
    "menu"          => array("version" => "1.01"),
    "start"         => array("version" => "1.01"),
);

$module["libraries"] = array();

$module["linked_modules"] = array();
