<?php

return array(
    'users_photos' => array(
        'gid'    => 'users_photos',
        'module' => 'media',
        'model'  => 'Media_model',
        'method' => '_dynamic_block_get_users_photos',
        'params' => array(
            'title' => array(
                'type'    => 'text',
                'default' => 'Latest users photos',
                'gid'     => 'title',
            ),
            'count' => array(
                'type'    => 'int',
                'default' => '8',
                'gid'     => 'count',
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
        'presets' => array(
            array(
                'gid_preset' => 'lovers',
                'gid_area'   => 'index-page',
                'params'     => 'a:3:{s:8:"title_en";s:13:"Latest photos";s:7:"html_ru";s:19:"Новые фото";s:5:"count";s:1:"8";}',
                'view_str'   => 'small_thumbs',
                'width'      => '100',
                'cache_time' => '0',
                'sorter'     => '10',
                'x'          => '0',
                'y'          => '7',
                'w'          => '12',
                'h'          => '1',
            ),
            array(
                'gid_preset' => 'persimmon_red',
                'gid_area'   => 'index-page',
                'params'     => 'a:3:{s:8:"title_en";s:13:"Recent photos";s:8:"title_ru";s:19:"Новые фото";s:5:"count";i:8;}',
                'view_str'   => 'medium_thumbs',
                'width'      => '100',
                'sorter'     => '14',
                'cache_time' => '0',
                'x'          => '0',
                'y'          => '9',
                'w'          => '12',
                'h'          => '1',
            ),
        ),
    ),
    'users_videos' => array(
        'gid'    => 'users_videos',
        'module' => 'media',
        'model'  => 'Media_model',
        'method' => '_dynamic_block_get_users_videos',
        'params' => array(
            'title' => array(
                'type'    => 'text',
                'default' => 'Latest users videos',
                'gid'     => 'title',
            ),
            'count' => array(
                'type'    => 'int',
                'default' => '8',
                'gid'     => 'count',
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
);
