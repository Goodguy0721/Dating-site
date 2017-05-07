<?php

use BeatSwitch\Lock\Permissions\Restriction;
use BeatSwitch\Lock\Permissions\Privilege;

return [
    'callers' => [
        'guest' => [
            [
                'type' => Privilege::TYPE,
                'action' => 'login',
                'resource_type' => null,
                'resource_id' => null,
            ]
        ],
        'user' => [
            [
                'type' => Restriction::TYPE,
                'action' => 'login',
                'resource_type' => null,
                'resource_id' => null,
            ]
        ],
        'admin' => [
            
        ],
        'install' => [
            
        ],
    ],
    'roles' => [
        
    ],
];
