<?php
return [
    'groups' => [
        'silver' => [
            'name' => [
                'en' => 'Silver',
            ],
            'description' => [
                'en' => 'The silver group',
            ],
            'is_active' => true,
        ],
         'premium' => [
            'name' => [
                'en' => 'Premium',
            ],
            'description' => [
                'en' => 'The premium group',
            ],
            'is_active' => true,
        ],
    ],
    'periods' => [
        30 => [
            'period' => 30,
            'silver_group' => 7,
            'premium_group' => 15,
        ],
        60 => [
            'period' => 60,
            'silver_group' => 12,
            'premium_group' => 29,
        ],
        90 => [
            'period' => 90,
            'silver_group' => 19,
            'premium_group' => 39,
        ],
    ],
    'acl' => [
        'associations' =>[
             'module' => [
                'access' => 2,
                'method' => 'ajaxLoadAssociations',
                'module_gid' => 'associations',
                'permission' => 'associations_associations_ajaxLoadAssociations',
            ],
            'permissions' => [
                'list' => [
                    'associations_associations_ajaxLoadAssociations' => [
                        'default' => ['status' => 0],
                        'silver' => ['status' => 0],
                        'premium' => ['status' => 1],
                    ]
                ],
            ],
        ],
        'favorites' => [
             'module' => [
                'access' => 2,
                'module_gid' => 'favorites',
                'permission' => 'favorites_favorites',
            ],
            'permissions' => [
                'list' => [
                    'favorites_favorites' => [
                        'default' => ['status' => 0],
                        'silver' => ['status' => 1],
                        'premium' => ['status' => 1],
                    ]
                ],
            ],
        ],
        'mailbox' => [
             'module' => [
                'access' => 2,
                'module_gid' => 'mailbox',
                'permission' => 'mailbox_mailbox',
            ],
            'permissions' => [
                'list' => [
                    'mailbox_mailbox' => [
                        'default' => ['status' => 1, 'count' => ['view' => 5, 'write' => 5]],
                        'silver' => ['status' => 1, 'count' => ['view' => 50, 'write' => 50]],
                        'premium' => ['status' => 1, 'count' => ['view' => 0, 'write' => 0]],
                    ]
                ],
            ],
        ],
        'questions' => [
            'module' => [
                'access' => 2,
                'method' => 'ajax_get_questions',
                'module_gid' => 'questions',
                'permission' => 'questions_questions_ajax_get_questions',
            ],
            'permissions' => [
                'list' => [
                    'questions_questions_ajax_get_questions' => [
                        'default' => ['status' => 0],
                        'silver' => ['status' => 1],
                        'premium' => ['status' => 1],
                    ]
                ],
            ],
        ],
        'horoscope' => [
            'module' => [
                'access' => 2,
                'module_gid' => 'horoscope',
                'permission' => 'horoscope_horoscope',
            ],
            'permissions' => [
                'list' => [
                    'horoscope_horoscope' => [
                        'default' => ['status' => 0],
                        'silver' => ['status' => 0],
                        'premium' => ['status' => 1],
                    ]
                ],
            ],
        ],
        'virtual_gifts' => [
            'module' => [
                'access' => 2,
                'module_gid' => 'virtual_gifts',
                'permission' => 'virtual_gifts_virtual_gifts',
            ],
            'permissions' => [
                'list' => [
                    'virtual_gifts_virtual_gifts' => [
                        'default' => ['status' => 0],
                        'silver' => ['status' => 1],
                        'premium' => ['status' => 1],
                    ]
                ],
            ],
        ],
        'ratings' => [
             'module' => [
                'access' => 2,
                'method' => 'topRated',
                'module_gid' => 'ratings',
                'permission' => 'ratings_ratings_topRated',
            ],
            'permissions' => [
                'list' => [
                    'ratings_ratings_topRated' => [
                        'default' => ['status' => 0],
                        'silver' => ['status' => 1],
                        'premium' => ['status' => 1],
                    ]
                ],
            ],
        ]
    ],
];
