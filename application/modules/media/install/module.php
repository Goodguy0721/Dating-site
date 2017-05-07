<?php

$module['module'] = 'media';
$module['install_name'] = 'Media module';
$module['install_descr'] = 'Multimedia uploads and site galleries';
$module['version'] = '4.01';
$module['files'] = array(
    array('file', 'read', 'application/modules/media/controllers/admin_media.php'),
    array('file', 'read', 'application/modules/media/controllers/api_media.php'),
    array('file', 'read', 'application/modules/media/controllers/media.php'),
    array('file', 'read', 'application/modules/media/helpers/media_helper.php'),
    array('file', 'read', 'application/modules/media/install/demo_structure_install.sql'),
    array('file', 'read', 'application/modules/media/install/module.php'),
    array('file', 'read', 'application/modules/media/install/permissions.php'),
    array('file', 'read', 'application/modules/media/install/settings.php'),
    array('file', 'read', 'application/modules/media/install/structure_deinstall.sql'),
    array('file', 'read', 'application/modules/media/install/structure_install.sql'),
    array('file', 'read', 'application/modules/media/js/albums.js'),
    array('file', 'read', 'application/modules/media/js/edit_media.js'),
    array('file', 'read', 'application/modules/media/js/gallery.js'),
    array('file', 'read', 'application/modules/media/js/media.js'),
    array('file', 'read', 'application/modules/media/models/album_types_model.php'),
    array('file', 'read', 'application/modules/media/models/albums_model.php'),
    array('file', 'read', 'application/modules/media/models/media_album_model.php'),
    array('file', 'read', 'application/modules/media/models/media_install_model.php'),
    array('file', 'read', 'application/modules/media/models/media_model.php'),
    array('dir', 'read', 'application/modules/media/langs'),
    array('dir', 'write', "temp/gallery_image/"),
    array('dir', 'write', "uploads/video-default"),
    array('file', 'write', "uploads/video-default/media-video-default.png"),
    array('dir', 'write', "uploads/default"),
    array('file', 'write', "uploads/default/default-gallery-image.png"),
    array('dir', 'write', "uploads/video/gallery_video"),
    array('dir', 'write', "uploads/gallery_image"),
    array('dir', 'write', "uploads/gallery_image/0"),
    array('dir', 'write', "uploads/gallery_image/0/0"),
    array('dir', 'write', "uploads/gallery_image/0/0/0"),
);

$module['demo_content'] = array(
    'reinstall' => false, // install demo content on module reinstall
);

$module['dependencies'] = array(
    'start' => array('version' => '1.03'),
    'menu'  => array('version' => '2.03'),
    'users' => array('version' => '3.01'),
);

$module['linked_modules'] = array(
    'install' => array(
        'menu'               => 'install_menu',
        'uploads'            => 'install_uploads',
        'comments'           => 'install_comments',
        'video_uploads'      => 'install_video_uploads',
        'moderation'         => 'install_moderation',
        'dynamic_blocks'     => 'install_dynamic_blocks',
        'wall_events'        => 'install_wall_events',
        'site_map'           => 'install_site_map',
        'spam'               => 'install_spam',
        'users'              => 'install_users',
        "aviary"             => "install_aviary",
        'ratings'            => 'install_ratings',
        'friendlist'         => 'install_friendlist',
        'bonuses'            => 'install_bonuses',
    ),
    'deinstall' => array(
        'menu'               => 'deinstall_menu',
        'uploads'            => 'deinstall_uploads',
        'comments'           => 'deinstall_comments',
        'video_uploads'      => 'deinstall_video_uploads',
        'moderation'         => 'deinstall_moderation',
        'dynamic_blocks'     => 'deinstall_dynamic_blocks',
        'wall_events'        => 'deinstall_wall_events',
        'site_map'           => 'deinstall_site_map',
        'spam'               => 'deinstall_spam',
        'users'              => 'deinstall_users',
        "aviary"             => "deinstall_aviary",
        'ratings'            => 'deinstall_ratings',
        'friendlist'         => 'deinstall_friendlist',
        'bonuses'            => 'deinstall_bonuses',
    ),
);
