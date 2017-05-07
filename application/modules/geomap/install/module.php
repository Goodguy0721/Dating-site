<?php

$module['module'] = 'geomap';
$module['install_name'] = 'Geo maps';
$module['install_descr'] = 'This module manages the use of maps on the site';
$module['version'] = '3.03';
$module['files'] = array(
    array('file', 'read', "application/modules/geomap/controllers/admin_geomap.php"),
    array('file', 'read', "application/modules/geomap/controllers/api_geomap.php"),
    array('file', 'read', "application/modules/geomap/controllers/geomap.php"),
    array('file', 'read', "application/modules/geomap/helpers/geomap_helper.php"),
    array('file', 'read', "application/modules/geomap/install/module.php"),
    array('file', 'read', "application/modules/geomap/install/permissions.php"),
    array('file', 'read', "application/modules/geomap/install/structure_deinstall.sql"),
    array('file', 'read', "application/modules/geomap/install/structure_install.sql"),
    array('file', 'read', "application/modules/geomap/js/bingmapsv7.js"),
    array('file', 'read', "application/modules/geomap/js/geomap-amenity-select.js"),
    array('file', 'read', "application/modules/geomap/js/googlemapsv3.js"),
    array('file', 'read', "application/modules/geomap/js/markerclusterer.js"),
    array('file', 'read', "application/modules/geomap/js/yandexmapsv2.js"),
    array('file', 'read', "application/modules/geomap/models/bingmapsv7_model.php"),
    array('file', 'read', "application/modules/geomap/models/geomap_install_model.php"),
    array('file', 'read', "application/modules/geomap/models/geomap_model.php"),
    array('file', 'read', "application/modules/geomap/models/geomap_settings_model.php"),
    array('file', 'read', "application/modules/geomap/models/googlemapsv3_model.php"),
    array('file', 'read', "application/modules/geomap/models/yandexmapsv2_model.php"),
    array('dir', 'read', 'application/modules/geomap/langs'),
);
$module['dependencies'] = array(
    'start'       => array('version' => '1.03'),
    'menu'        => array('version' => '2.03'),
    'uploads'     => array('version' => '1.03'),
);
$module['linked_modules'] = array(
    'install' => array(
        'menu'        => 'install_menu',
        "uploads"     => "install_uploads",
    ),
    'deinstall' => array(
        'menu'        => 'deinstall_menu',
        "uploads"     => "deinstall_uploads",
    ),
);
