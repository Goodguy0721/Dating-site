<?php
return [
    [
        'module_gid' => 'associations',
        'controller' => 'associations',
        'method' => 'ajaxLoadAssociations',
        'access' => 2,
        'data' => [],
    ],
    [
        'module_gid' => 'favourites',
        'controller' => 'favourites',
        'method' => null,
        'access' => 2,
        'data' => [],
    ],
    [
        'module_gid' => 'friendlist',
        'controller' => 'friendlist',
        'method' => null,
        'access' => 2,
        'data' => [],
    ],
    [
        'module_gid' => 'mailbox',
        'controller' => 'mailbox',
        'method' => null,
        'access' => 2,
        'data' => [
            'write' => 10,
            'view' => 10
        ]
    ],
    [
        'module_gid' => 'im',
        'controller' => 'im',
        'method' => null,
        'access' => 2,
        'data' => [],
    ],
    [
        'module_gid' => 'questions',
        'controller' => 'questions',
        'method' => 'ajax_get_questions',
        'access' => 2,
        'data' => [],
    ],
    [
        'module_gid' => 'horoscope',
        'controller' => 'horoscope',
        'method' => null,
        'access' => 2,
        'data' => [],
    ],
    [
        'module_gid' => 'perfect_match',
        'controller' => 'perfect_match',
        'method' => null,
        'access' => 2,
        'data' => [],
    ],
    [
        'module_gid' => 'nearest_users',
        'controller' => 'nearest_users',
        'method' => null,
        'access' => 2,
        'data' => [],
    ],
    [
        'module_gid' => 'winks',
        'controller' => 'winks',
        'method' => null,
        'access' => 2,
        'data' => [],
    ],
    [
        'module_gid' => 'kisses',
        'controller' => 'kisses',
        'method' => null,
        'access' => 2,
        'data' => [],
    ],
    [
        'module_gid' => 'virtual_gifts',
        'controller' => 'virtual_gifts',
        'method' => null,
        'access' => 2,
        'data' => [],
    ],
    [
        'module_gid' => 'media',
        'controller' => 'media',
        'method' => null,
        'access' => 1,
        'data' => [],
    ],
    [
        'module_gid' => 'polls',
        'controller' => 'polls',
        'method' => null,
        'access' => 2,
        'data' => [],
    ],
    [
        'module_gid' => 'like_me',
        'controller' => 'like_me',
        'method' => null,
        'access' => 2,
        'data' => [],
    ],
    [
        'module_gid' => 'ratings',
        'controller' => 'ratings',
        'method' => 'topRated',
        'access' => 2,
        'data' => [],
    ],
    [
        'module_gid' => 'store',
        'controller' => 'store',
        'method' => null,
        'access' => 1,
        'data' => [],
    ],
    [
        'module_gid' => 'users',
        'controller' => 'users',
        'method' => 'my_guests',
        'access' => 2,
        'data' => [],
    ],
    [
        'module_gid' => 'users',
        'controller' => 'users',
        'method' => 'search',
        'access' => 1,
        'data' => [],
    ],
    [
        'module_gid' => 'users',
        'controller' => 'users',
        'method' => 'view',
        'access' => 1,
        'data' => [
            'view' => 10
        ]
    ],
];

