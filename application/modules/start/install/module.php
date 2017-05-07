<?php

$module['module'] = 'start';
$module['install_name'] = 'User and Admin index pages';
$module['install_descr'] = 'Index pages for administrator and user area';
$module['version'] = '2.03';
$module['files'] = array(
    array('file', 'read', "application/modules/start/helpers/start_helper.php"),
    array('file', 'read', "application/modules/start/controllers/start.php"),
    array('file', 'read', "application/modules/start/controllers/admin_start.php"),
    array('file', 'read', "application/modules/start/install/module.php"),
    array('file', 'read', "application/modules/start/install/permissions.php"),
    array('file', 'read', "application/modules/start/install/settings.php"),
    array('file', 'read', "application/modules/start/js/admin-banners.js"),
    array('file', 'read', "application/modules/start/js/admin_lang_inline_editor.js"),
    array('file', 'read', "application/modules/start/js/checkbox.js"),
    array('file', 'read', "application/modules/start/js/date_formats.js"),
    array('file', 'read', "application/modules/start/js/hlbox.js"),
    array('file', 'read', "application/modules/start/js/lang_inline_editor.js"),
    array('file', 'read', "application/modules/start/js/search.js"),
    array('file', 'read', "application/modules/start/js/selectbox.js"),
    array('file', 'read', "application/modules/start/js/start_multi_request.js"),
    array('file', 'read', "application/modules/start/models/start_install_model.php"),
    array('file', 'read', "application/modules/start/models/start_model.php"),

    array('dir', 'read', 'application/modules/start/langs'),

    array('dir', 'write', "temp/"),
    array('dir', 'write', "temp/cache/"),
    array('dir', 'write', "temp/captcha/"),
    array('dir', 'write', "temp/logs/"),
    array('dir', 'write', "temp/rss/"),
    array('dir', 'write', "temp/templates_c/"),
    array('dir', 'write', "temp/trash/"),

    array('dir', 'write', "application/libraries/dompdf/lib/fonts/"),

    array('dir', 'write', "uploads/wysiwyg"),
);

if (SOCIAL_MODE) {
    $module['structure_install_file'] = 'structure_install_social.sql';
}

$module['dependencies'] = array(
    'menu' => array('version' => '2.03'),
);

$module['libraries'] = array(
    'dompdf',
);

$module['linked_modules'] = array(
    'install' => array(
        'menu'                => 'install_menu',
        'banners'             => 'install_banners',
        'dynamic_blocks'      => 'install_dynamic_blocks',
    ),
    'deinstall' => array(
        'menu'                => 'deinstall_menu',
        'banners'             => 'deinstall_banners',
        'dynamic_blocks'      => 'deinstall_dynamic_blocks',
    ),
);
