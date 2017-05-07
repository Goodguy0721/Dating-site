<?php

return array(
    'info_pages' => array(
        'gid'    => 'info_pages',
        'module' => 'content',
        'model'  => 'Content_model',
        'method' => '_dynamic_block_get_info_pages',
        'params' => array(
            'keyword' => array(
                'type'    => 'string',
                'default' => '',
                'gid'     => 'keyword',
            ),
            'transparent' => array(
                'type'    => 'checkbox',
                'default' => '1',
                'gid'     => 'transparent',
            ),
            'show_subsections' => array(
                'type'    => 'checkbox',
                'default' => '',
                'gid'     => 'show_subsections',
            ),
            'trim_subsections_text' => array(
                'type'    => 'checkbox',
                'default' => '1',
                'gid'     => 'trim_subsections_text',
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
    'content_promo' => array(
        'gid'    => 'content_promo',
        'module' => 'content',
        'model'  => 'Content_promo_model',
        'method' => '_dynamic_block_get_content_promo',
        'views'  => array(
            0 => array(
                'gid' => 'default',
            ),
        ),
        'area'    => array(),
        'presets' => array(),
    ),
);
