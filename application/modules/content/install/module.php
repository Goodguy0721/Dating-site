<?php

$module['module'] = 'content';
$module['install_name'] = 'Text content management';
$module['install_descr'] = 'Creating and editing info pages (posts and articles with media content on your site)';
$module['version'] = '4.03';
$module['files'] = array(
    array('file', 'read', "application/modules/content/controllers/api_content.php"),
    array('file', 'read', "application/modules/content/controllers/admin_content.php"),
    array('file', 'read', "application/modules/content/controllers/content.php"),
    array('file', 'read', "application/modules/content/install/demo_content_dating.php"),
    array('file', 'read', "application/modules/content/install/demo_content_social.php"),
    array('file', 'read', "application/modules/content/install/module.php"),
    array('file', 'read', "application/modules/content/install/permissions.php"),
    array('file', 'read', "application/modules/content/install/structure_deinstall.sql"),
    array('file', 'read', "application/modules/content/install/structure_install.sql"),
    array('file', 'read', "application/modules/content/models/content_install_model.php"),
    array('file', 'read', "application/modules/content/models/content_model.php"),
    array('file', 'read', "application/modules/content/models/content_promo_model.php"),
    array('file', 'read', "application/modules/content/helpers/content_helper.php"),
    array('dir', 'read', "application/modules/content/langs"),
    array('dir', 'write', "uploads/info-page-logo/"),
    array('dir', 'write', "uploads/promo-content-img/"),
    array('dir', 'write', "uploads/promo-content-img/0"),
    array('dir', 'write', "uploads/promo-content-img/0/0"),
    array('dir', 'write', "uploads/promo-content-img/0/0/0"),
);
$module['dependencies'] = array(
    'start'         => array('version' => '1.03'),
    'menu'          => array('version' => '2.03'),
    'uploads'       => array('version' => '1.03'),
    'file_uploads'  => array('version' => '1.03'),
    'video_uploads' => array('version' => '1.01'),
);
$module['linked_modules'] = array(
    'install' => array(
        'menu'              => 'install_menu',
        'site_map'          => 'install_site_map',
        'banners'           => 'install_banners',
        'moderators'        => 'install_moderators',
        'uploads'           => 'install_uploads',
        'file_uploads'      => 'install_file_uploads',
        'social_networking' => 'install_social_networking',
        'dynamic_blocks'    => 'install_dynamic_blocks',
        'video_uploads'     => 'install_video_uploads',
    ),
    'deinstall' => array(
        'menu'              => 'deinstall_menu',
        'site_map'          => 'deinstall_site_map',
        'banners'           => 'deinstall_banners',
        'moderators'        => 'deinstall_moderators',
        'uploads'           => 'deinstall_uploads',
        'file_uploads'      => 'deinstall_file_uploads',
        'social_networking' => 'deinstall_social_networking',
        'dynamic_blocks'    => 'deinstall_dynamic_blocks',
        'video_uploads'     => 'deinstall_video_uploads',
    ),
);
