<?php

return array(
    'logo_block' => array(
        'gid'    => 'logo_block',
        'module' => 'themes',
        'model'  => 'Themes_model',
        'method' => '_dynamic_block_get_logo_block',
        'views'  => array(
            array(
                'gid' => 'default',
            ),
        ),
        'area' => array(
            array(
                'gid'        => 'index-page',
                'params'     => 'a:0:{}',
                'view_str'   => 'default',
                'width'      => '30',
                'cache_time' => '0',
                'sorter'     => '2',
                'x'          => '0',
                'y'          => '1',
                'w'          => '4',
                'h'          => '1',
            ),
        ),
        'presets' => array(
            array(
                'gid_preset' => 'default',
                'gid_area'   => 'index-page',
                'params'     => 'a:0:{}',
                'view_str'   => 'default',
                'width'      => '30',
                'cache_time' => '0',
                'sorter'     => '2',
                'x'          => '0',
                'y'          => '1',
                'w'          => '4',
                'h'          => '1',
            ),
        ),
    ),
);
