<?php

$config["editor_type"]["users"] = array(
    "gid"    => "users",
    "module" => "users",
    "name"   => "Users",
    "tables" => array(
        "user" => DB_PREFIX . 'users',
    ),
    "field_prefix"   => 'fe_',
    'fulltext_use'   => true,
    'fulltext_field' => array(
        "user" => 'search_field',
    ),
    'fulltext_model'    => "Users_model",
    'fulltext_callback' => "get_fulltext_data",
);

/* <custom_M> */
$config['editor_type']['clubs'] = array(
    'gid'    => 'clubs',
    'module' => 'clubs',
    'name'   => 'Clubs',
    'tables' => array(
        'user' => DB_PREFIX . 'clubs',
    ),
    'field_prefix'   => 'fe_',
    'fulltext_use'   => true,
    'fulltext_field' => array(
        'user' => 'search_field',
    ),
    'fulltext_model'    => 'Clubs_model',
    'fulltext_callback' => 'getFulltextData',
);
/* </custom_M> */