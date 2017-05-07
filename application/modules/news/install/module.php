<?php

$module['module'] = 'news';
$module['install_name'] = 'News module';
$module['install_descr'] = 'Managing site news, including RSS feeds';
$module['version'] = '3.03';
$module['files'] = array(
    array('file', 'read', "application/modules/news/controllers/admin_news.php"),
    array('file', 'read', "application/modules/news/controllers/api_news.php"),
    array('file', 'read', "application/modules/news/controllers/news.php"),
    array('file', 'read', "application/modules/news/helpers/news_helper.php"),
    array('file', 'read', "application/modules/news/install/module.php"),
    array('file', 'read', "application/modules/news/install/permissions.php"),
    array('file', 'read', "application/modules/news/install/settings.php"),
    array('file', 'read', "application/modules/news/install/structure_deinstall.sql"),
    array('file', 'read', "application/modules/news/install/structure_install.sql"),
    array('file', 'read', "application/modules/news/models/news_install_model.php"),
    array('file', 'read', "application/modules/news/models/news_model.php"),
    array('file', 'read', "application/modules/news/models/feeds_model.php"),
    array('dir', 'read', 'application/modules/news/langs'),
    array('dir', 'write', "uploads/video-default"),
    array('file', 'write', "uploads/video-default/news-video-default.jpg"),
    array('dir', 'write', "uploads/news-logo/2015"),
    array('dir', 'write', "uploads/news-logo/2015/05"),
    array('dir', 'write', "uploads/news-logo/2015/05/08"),
    array('dir', 'write', "uploads/news-logo/2015/05/08/12"),
);

$module['dependencies'] = array(
    'start'         => array('version' => '1.03'),
    'menu'          => array('version' => '2.03'),
    'uploads'       => array('version' => '1.03'),
    'video_uploads' => array('version' => '1.03'),
);

$module['libraries'] = array(
    'SimplePie', 'Rssfeed',
);

$module['linked_modules'] = array(
    'install' => array(
        'menu'                  => 'install_menu',
        'uploads'               => 'install_uploads',
        'site_map'              => 'install_site_map',
        'cronjob'               => 'install_cronjob',
        'banners'               => 'install_banners',
        'subscriptions'         => 'install_subscriptions',
        'video_uploads'         => 'install_video_uploads',
        'social_networking'     => 'install_social_networking',
        'moderators'            => 'install_moderators',
        'comments'              => 'install_comments',
        'dynamic_blocks'        => 'install_dynamic_blocks',
    ),
    'deinstall' => array(
        'menu'                  => 'deinstall_menu',
        'uploads'               => 'deinstall_uploads',
        'video_uploads'         => 'deinstall_video_uploads',
        'site_map'              => 'deinstall_site_map',
        'cronjob'               => 'deinstall_cronjob',
        'banners'               => 'deinstall_banners',
        'social_networking'     => 'deinstall_social_networking',
        'moderators'            => 'deinstall_moderators',
        'subscriptions'         => 'deinstall_subscriptions',
        'comments'              => 'deinstall_comments',
        'dynamic_blocks'        => 'deinstall_dynamic_blocks',
    ),
);
