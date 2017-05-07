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
        'presets' => array(),
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
