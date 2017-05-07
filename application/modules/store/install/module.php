<?php

$module['module'] = 'store';
$module['install_name'] = 'Gift Store module';
$module['install_descr'] = 'This module will let you sell merchandise on your site. Users will be able to order gifts for themselves as well as for the other site members';
$module['category'] = 'action';
$module['version'] = '2.04';
$module['files'] = array(
    array('file', 'read', "application/modules/store/controllers/admin_store.php"),
    array('file', 'read', "application/modules/store/controllers/api_store.php"),
    array('file', 'read', "application/modules/store/controllers/store.php"),
    array('file', 'read', "application/modules/store/helpers/store_helper.php"),
    array('file', 'read', "application/modules/store/install/module.php"),
    array('file', 'read', "application/modules/store/install/permissions.php"),
    array('file', 'read', "application/modules/store/install/settings.php"),
    array('file', 'read', "application/modules/store/install/structure_deinstall.sql"),
    array('file', 'read', "application/modules/store/install/structure_install.sql"),
    array('file', 'read', "application/modules/store/js/store_cart.js"),
    array('file', 'read', "application/modules/store/js/store_media.js"),
    array('file', 'read', "application/modules/store/js/store_list.js"),
    array('file', 'read', "application/modules/store/js/store_media.js"),
    array('file', 'read', "application/modules/store/js/store_orders.js"),
    array('file', 'read', "application/modules/store/models/store_install_model.php"),
    array('file', 'read', "application/modules/store/models/store_bestsellers_model.php"),
    array('file', 'read', "application/modules/store/models/store_cart_model.php"),
    array('file', 'read', "application/modules/store/models/store_categories_model.php"),
    array('file', 'read', "application/modules/store/models/store_model.php"),
    array('file', 'read', "application/modules/store/models/store_options_model.php"),
    array('file', 'read', "application/modules/store/models/store_orders_log_model.php"),
    array('file', 'read', "application/modules/store/models/store_orders_model.php"),
    array('file', 'read', "application/modules/store/models/store_products_model.php"),
    array('file', 'read', "application/modules/store/models/store_shippings_model.php"),
    array('file', 'read', "application/modules/store/models/store_statistics_model.php"),
    array('file', 'read', "application/modules/store/models/store_users_shippings_model.php"),
    array('dir', 'read', "application/modules/store/langs"),
);

$module['dependencies'] = array(
    'start'    => array('version' => '1.01'),
    'menu'     => array('version' => '1.01'),
    'users'    => array('version' => '1.01'),
    'payments' => array('version' => '2.01'),
);

$module['linked_modules'] = array(
    'install' => array(
        'menu'                 => 'install_menu',
        'payments'             => 'install_payments',
        'users'                => 'install_users',
        'users_lists'          => 'install_users_lists',
        'notifications'        => 'install_notifications',
        'content'              => 'install_content',
        'uploads'              => 'install_uploads',
        'upload_gallery'       => 'install_upload_gallery',
        'video_uploads'        => 'install_video_uploads',
        'reviews'              => 'install_reviews',
        'moderators'           => 'install_moderators',
        'contact_us'           => 'install_contact_us',
        'countries'            => 'install_countries',
        'dynamic_blocks'       => 'install_dynamic_blocks',
        'banners'              => 'install_banners',
        'seo'                  => 'install_seo',
        'social_networking'    => 'install_social_networking',
        'site_map'             => 'install_site_map',
        'services'             => 'install_services',
    ),
    'deinstall' => array(
        'menu'                 => 'deinstall_menu',
        'payments'             => 'deinstall_payments',
        'users'                => 'deinstall_users',
        'users_lists'          => 'deinstall_users_lists',
        'notifications'        => 'deinstall_notifications',
        'content'              => 'deinstall_content',
        'uploads'              => 'deinstall_uploads',
        'upload_gallery'       => 'deinstall_upload_gallery',
        'video_uploads'        => 'deinstall_video_uploads',
        'reviews'              => 'deinstall_reviews',
        'moderators'           => 'deinstall_moderators',
        'contact_us'           => 'deinstall_contact_us',
        'countries'            => 'deinstall_countries',
        'dynamic_blocks'       => 'deinstall_dynamic_blocks',
        'banners'              => 'deinstall_banners',
        'seo'                  => 'deinstall_seo',
        'social_networking'    => 'deinstall_social_networking',
        'site_map'             => 'deinstall_site_map',
        'services'             => 'deinstall_services',

    ),
);
