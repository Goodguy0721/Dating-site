<?php

return array(
    'registration_login_form' => array(
        'gid'    => 'registration_login_form',
        'module' => 'users',
        'model'  => 'Users_model',
        'method' => '_dynamic_block_get_registration_login_form',
        'views'  => array(
            array(
                'gid' => 'registration_form',
            ),
            array(
                'gid' => 'login_form',
            ),
        ),
        'area'    => array(),
        'presets' => array(),
    ),
    'new_users' => array(
        'gid'    => 'new_users',
        'module' => 'users',
        'model'  => 'Users_model',
        'method' => '_dynamic_block_get_new_users',
        'params' => array(
            'title' => array(
                'type'    => 'text',
                'default' => '',
                'gid'     => 'title',
            ),
            'count' => array(
                'type'    => 'int',
                'default' => '8',
                'gid'     => 'count',
            ),
            'user_type' => array(
                'type'    => 'int',
                'default' => '0',
                'gid'     => 'user_type',
            ),
        ),
        'views' => array(
            array(
                'gid' => 'big_thumbs',
            ),
            array(
                'gid' => 'medium_thumbs',
            ),
            array(
                'gid' => 'small_thumbs',
            ),
            array(
                'gid' => 'small_thumbs_w_descr',
            ),
            array(
                'gid' => 'carousel',
            ),
            array(
                'gid' => 'carousel_w_descr',
            ),
        ),
        'area'    => array(),
        'presets' => array(),
    ),
    'active_users' => array(
        'gid'    => 'active_users',
        'module' => 'users',
        'model'  => 'Users_model',
        'method' => '_dynamic_block_get_active_users',
        'params' => array(
            'title' => array(
                'type'    => 'text',
                'default' => 'Last active users',
                'gid'     => 'title',
            ),
            'count' => array(
                'type'    => 'int',
                'default' => '8',
                'gid'     => 'count',
            ),
            'user_type' => array(
                'type'    => 'int',
                'default' => '0',
                'gid'     => 'user_type',
            ),
        ),
        'views' => array(
            array(
                'gid' => 'big_thumbs',
            ),
            array(
                'gid' => 'medium_thumbs',
            ),
            array(
                'gid' => 'small_thumbs',
            ),
            array(
                'gid' => 'small_thumbs_w_descr',
            ),
            array(
                'gid' => 'carousel',
            ),
            array(
                'gid' => 'carousel_w_descr',
            ),
        ),
        'area'    => array(),
        'presets' => array(),
    ),
    'featured_users' => array(
        'gid'    => 'featured_users',
        'module' => 'users',
        'model'  => 'Users_model',
        'method' => '_dynamic_block_get_featured_users',
        'params' => array(
            'title' => array(
                'type'    => 'text',
                'default' => 'Featured users',
                'gid'     => 'title',
            ),
            'count' => array(
                'type'    => 'int',
                'default' => '8',
                'gid'     => 'count',
            ),
            'user_type' => array(
                'type'    => 'int',
                'default' => '0',
                'gid'     => 'user_type',
            ),
        ),
        'views' => array(
            array(
                'gid' => 'big_thumbs',
            ),
            array(
                'gid' => 'medium_thumbs',
            ),
            array(
                'gid' => 'small_thumbs',
            ),
            array(
                'gid' => 'small_thumbs_w_descr',
            ),
            array(
                'gid' => 'carousel',
            ),
            array(
                'gid' => 'carousel_w_descr',
            ),
        ),
        'area'    => array(),
        'presets' => array(),
    ),
    'auth_links' => array(
        'gid'    => 'auth_links',
        'module' => 'users',
        'model'  => 'Users_model',
        'method' => '_dynamic_block_get_auth_links',
        'params' => array(
            'right_align' => array(
                'type'    => 'checkbox',
                'default' => '1',
                'gid'     => 'right_align',
            ),
        ),
        'views' => array(
            array(
                'gid' => 'default',
            ),
        ),
        'area' => array(
            array(
                'gid'        => 'index-page',
                'params'     => 'a:1:{s:11:"right_align";s:1:"1";}',
                'view_str'   => 'default',
                'width'      => '50',
                'cache_time' => '0',
                'sorter'     => '4',
                'x'          => '6',
                'y'          => '2',
                'w'          => '6',
                'h'          => '1',
            ),
        ),
        'presets' => array(
            array(
                'gid_preset' => 'social',
                'gid_area'   => 'index-page',
                'params'     => 'a:1:{s:11:"right_align";s:1:"1";}',
                'view_str'   => 'default',
                'width'      => '50',
                'cache_time' => '0',
                'sorter'     => '4',
                'x'          => '6',
                'y'          => '2',
                'w'          => '6',
                'h'          => '1',
            ),
        ),
    ),
    'lang_select' => array(
        'gid'    => 'lang_select',
        'module' => 'users',
        'model'  => 'Users_model',
        'method' => '_dynamic_block_get_lang_select',
        'params' => array(
            'right_align' => array(
                'type'    => 'checkbox',
                'default' => '1',
                'gid'     => 'right_align',
            ),
        ),
        'views' => array(
            array(
                'gid' => 'default',
            ),
        ),
        'area' => array(
            array(
                'gid'        => 'index-page',
                'params'     => 'a:1:{s:11:"right_align";s:1:"1";}',
                'view_str'   => 'default',
                'width'      => '70',
                'cache_time' => '0',
                'sorter'     => '3',
                'x'          => '4',
                'y'          => '1',
                'w'          => '8',
                'h'          => '1',
            ),
        ),
        'presets' => array(
            array(
                'gid_preset' => 'default',
                'gid_area'   => 'index-page',
                'params'     => 'a:1:{s:11:"right_align";s:1:"1";}',
                'view_str'   => 'default',
                'width'      => '70',
                'cache_time' => '0',
                'sorter'     => '3',
                'x'          => '4',
                'y'          => '1',
                'w'          => '8',
                'h'          => '1',
            ),
        ),
    ),
);
