<?php

$fe_sections = array(
    array("data" => array("gid" => "about-me", "editor_type_gid" => "users")),
    array("data" => array("gid" => "my-interests", "editor_type_gid" => "users")),
);

$fe_fields = array(
    array("data" => array("gid" => "iphone_android",     "section_gid" => "about-me",     "editor_type_gid" => "users", "field_type" => "select",      "fts" => "1", "settings_data" => 'a:3:{s:13:"default_value";i:0;s:9:"view_type";s:6:"select";s:12:"empty_option";b:1;}', "sorter" => "1", "options" => array('1', '2', '3', '4', '5', '6', '7', '8'))),
    array("data" => array("gid" => "windows_mac",        "section_gid" => "about-me",     "editor_type_gid" => "users", "field_type" => "select",      "fts" => "1", "settings_data" => 'a:3:{s:13:"default_value";i:0;s:9:"view_type";s:6:"select";s:12:"empty_option";b:1;}', "sorter" => "2", "options" => array('1', '2', '3', '4', '5', '6'))),
    array("data" => array("gid" => "facebook_twitter",   "section_gid" => "about-me",     "editor_type_gid" => "users", "field_type" => "select",      "fts" => "1", "settings_data" => 'a:3:{s:13:"default_value";i:0;s:9:"view_type";s:6:"select";s:12:"empty_option";b:1;}', "sorter" => "3", "options" => array('1', '2', '3', '4', '5', '6', '7', '8'))),
    array("data" => array("gid" => "style",              "section_gid" => "about-me",     "editor_type_gid" => "users", "field_type" => "multiselect", "fts" => "1", "settings_data" => 'a:2:{s:13:"default_value";s:0:"";s:9:"view_type";s:8:"checkbox";}',                    "sorter" => "5", "options" => array('1', '2', '3', '4', '5', '6'))),
    array("data" => array("gid" => "favourite_films",    "section_gid" => "my-interests", "editor_type_gid" => "users", "field_type" => "textarea",    "fts" => "1", "settings_data" => 'a:3:{s:13:"default_value";s:0:"";s:8:"min_char";i:0;s:8:"max_char";i:0;}',             "sorter" => "1", "options" => array())),
    array("data" => array("gid" => "favourite_tv_shows", "section_gid" => "my-interests", "editor_type_gid" => "users", "field_type" => "textarea",    "fts" => "1", "settings_data" => 'a:3:{s:13:"default_value";s:0:"";s:8:"min_char";i:0;s:8:"max_char";i:0;}',             "sorter" => "2", "options" => array())),
    array("data" => array("gid" => "favourite_music",    "section_gid" => "my-interests", "editor_type_gid" => "users", "field_type" => "textarea",    "fts" => "1", "settings_data" => 'a:3:{s:13:"default_value";s:0:"";s:8:"min_char";i:0;s:8:"max_char";i:0;}',             "sorter" => "3", "options" => array())),
    array("data" => array("gid" => "favourite_books",    "section_gid" => "my-interests", "editor_type_gid" => "users", "field_type" => "textarea",    "fts" => "1", "settings_data" => 'a:3:{s:13:"default_value";s:0:"";s:8:"min_char";i:0;s:8:"max_char";i:0;}',             "sorter" => "4", "options" => array())),
);

$fe_forms = array(
    array('data' => array(
        'gid'             => 'advanced_search',
        'editor_type_gid' => 'users',
        'name'            => 'Search form',
        'field_data'      => '',
        'is_system'       => 1,
    )),
);
