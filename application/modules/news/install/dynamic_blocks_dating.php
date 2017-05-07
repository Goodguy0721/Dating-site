<?php

return array(
    'news' => array(
        'gid'    => 'news',
        'module' => 'news',
        'model'  => 'News_model',
        'method' => '_dynamic_block_get_news',
        'params' => array(
            'count' => array(
                'type'    => 'int',
                'default' => '3',
                'gid'     => 'count',
            ),
            'transparent' => array(
                'type'    => 'checkbox',
                'default' => '1',
                'gid'     => 'transparent',
            ),
        ),
        'views' => array(
            0 => array(
                'gid' => 'default',
            ),
        ),
        'area'    => array(),
        'presets' => array(
            0 => array(
                'gid_preset' => 'mediumturquoise',
                'gid_area'   => 'index-page',
                'params'     => 'a:2:{s:5:"count";s:1:"2";s:11:"transparent";s:1:"1";}',
                'view_str'   => 'default',
                'width'      => '50',
                'cache_time' => '0',
                'sorter'     => '15',
                'x'          => '6',
                'y'          => '10',
                'w'          => '6',
                'h'          => '1',
            ),
            1 => array(
                'gid_preset' => 'lavender',
                'gid_area'   => 'index-page',
                'params'     => 'a:2:{s:5:"count";s:1:"2";s:11:"transparent";s:1:"1";}',
                'view_str'   => 'default',
                'width'      => '50',
                'cache_time' => '0',
                'sorter'     => '15',
                'x'          => '6',
                'y'          => '10',
                'w'          => '6',
                'h'          => '1',
            ),
            2 => array(
                'gid_preset' => 'jewish',
                'gid_area'   => 'index-page',
                'params'     => 'a:2:{s:5:"count";s:1:"2";s:11:"transparent";s:1:"1";}',
                'view_str'   => 'default',
                'width'      => '70',
                'cache_time' => '0',
                'sorter'     => '8',
                'x'          => '4',
                'y'          => '5',
                'w'          => '8',
                'h'          => '1',
            ),
            3 => array(
                'gid_preset' => 'lovers',
                'gid_area'   => 'index-page',
                'params'     => 'a:2:{s:5:"count";s:1:"2";s:11:"transparent";s:1:"1";}',
                'view_str'   => 'default',
                'width'      => '70',
                'cache_time' => '0',
                'sorter'     => '9',
                'x'          => '4',
                'y'          => '6',
                'w'          => '8',
                'h'          => '1',
            ),
            4 => array(
                'gid_preset' => 'blackonwhite',
                'gid_area'   => 'index-page',
                'params'     => 'a:2:{s:5:"count";s:1:"3";s:11:"transparent";s:1:"1";}',
                'view_str'   => 'default',
                'width'      => '100',
                'cache_time' => '0',
                'sorter'     => '10',
                'x'          => '0',
                'y'          => '7',
                'w'          => '12',
                'h'          => '1',
            ),
            5 => array(
                'gid_preset' => 'companions',
                'gid_area'   => 'index-page',
                'params'     => 'a:2:{s:5:"count";s:1:"3";s:11:"transparent";s:1:"1";}',
                'view_str'   => 'default',
                'width'      => '30',
                'cache_time' => '0',
                'sorter'     => '9',
                'x'          => '4',
                'y'          => '5',
                'w'          => '4',
                'h'          => '1',
            ),
            6 => array(
                'gid_preset' => 'community',
                'gid_area'   => 'index-page',
                'params'     => 'a:2:{s:5:"count";s:1:"2";s:11:"transparent";s:1:"1";}',
                'view_str'   => 'default',
                'width'      => '30',
                'cache_time' => '0',
                'sorter'     => '7',
                'x'          => '8',
                'y'          => '4',
                'w'          => '4',
                'h'          => '1',
            ),
            7 => array(
                'gid_preset' => 'christian',
                'gid_area'   => 'index-page',
                'params'     => 'a:2:{s:5:"count";s:1:"2";s:11:"transparent";s:1:"1";}',
                'view_str'   => 'default',
                'width'      => '50',
                'cache_time' => '0',
                'sorter'     => '12',
                'x'          => '6',
                'y'          => '9',
                'w'          => '6',
                'h'          => '1',
            ),
        ),
    ),
);
