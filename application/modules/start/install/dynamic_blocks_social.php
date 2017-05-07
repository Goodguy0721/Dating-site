<?php

return array(
    'site_stat_block' => array(
        'gid'    => 'site_stat_block',
        'module' => 'start',
        'model'  => 'Start_model',
        'method' => '_dynamic_block_get_stat_block',
        'views'  => array(
            array(
                'gid' => 'default',
            ),
        ),
        'params' => '',
    ),
    'search_form_block' => array(
        'gid'    => 'search_form_block',
        'module' => 'start',
        'model'  => 'Start_model',
        'method' => '_dynamic_block_get_search_form',
        'views'  => array(
            array(
                'gid' => 'default',
            ),
        ),
        'params'  => '',
        'area'    => array(),
        'presets' => array(),
    ),
);
