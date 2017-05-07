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
        'presets' => array(),
    ),
);
