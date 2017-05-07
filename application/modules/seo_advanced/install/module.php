<?php

$module['module'] = 'seo_advanced';
$module['install_name'] = 'Advanced SEO settings';
$module['install_descr'] = 'Advanced SEO settings including analytics, trackers, robots.txt and site map';
$module['version'] = '2.05';
$module['files'] = array(
    array('file', 'read', "application/modules/seo_advanced/controllers/admin_seo_advanced.php"),
    array('file', 'read', "application/modules/seo_advanced/helpers/seo_advanced_helper.php"),
    array('file', 'read', "application/modules/seo_advanced/helpers/seo_analytics_helper.php"),
    array('file', 'read', "application/modules/seo_advanced/install/module.php"),
    array('file', 'read', "application/modules/seo_advanced/install/permissions.php"),
    array('file', 'read', "application/modules/seo_advanced/install/settings.php"),
    array('file', 'read', "application/modules/seo_advanced/js/seo-url-creator.js"),
    array('file', 'read', "application/modules/seo_advanced/models/seo_advanced_install_model.php"),
    array('file', 'read', "application/modules/seo_advanced/models/seo_advanced_model.php"),
    array('dir', 'read', 'application/modules/seo_advanced/langs'),
    array('file', 'write', "robots.txt"),
    array('file', 'write', "sitemap.xml"),
);

$module['dependencies'] = array(
    'start' => array('version' => '1.01'),
    'menu'  => array('version' => '1.01'),
    'seo'   => array('version' => '2.01'),
);

$module['libraries'] = array(
    'Whois', 'Googlepr',
);

$module['linked_modules'] = array(
    'install' => array(
        'menu'          => 'install_menu',
        'moderators'    => 'install_moderators',
        'cronjob'       => 'install_cronjob',
    ),
    'deinstall' => array(
        'menu'          => 'deinstall_menu',
        'moderators'    => 'deinstall_moderators',
        'cronjob'       => 'deinstall_cronjob',
    ),
);
