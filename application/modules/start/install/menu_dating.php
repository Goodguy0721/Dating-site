<?php
return array(
    // admin menu
    'admin_menu' => array(
        'name' => 'Admin area menu',
        "action" => "create",
        "items" => array(
            'main_items' => array(
                "action" => "create",
                'link' => 'admin/start',
                'status' => 1,
                'sorter' => 1,
                "items" => array(
                    'admin-home-item' => array(
                        "action" => "create",
                        'link' => 'admin/start',
                        'icon' => 'home',
                        'status' => 1,
                        'sorter' => 1,
                    ),
                ),
            ),
            'system_items' => array(
                "action" => "create",
                'link' => 'admin/start',
                'status' => 1,
                'sorter' => 2,
                "items" => 'none',
            ),
            'settings_items' => array(
                "action" => "create",
                'link' => 'admin/start',
                'status' => 1,
                'sorter' => 3,
                "items" => array(
                    'interface-items' => array(
                        "action" => "create",
                        'link' => 'admin/start/menu/interface-items',
                        'icon' => 'paint-brush',
                        'status' => 1,
                        'sorter' => 1,
                    ),
                    'content_items' => array(
                        "action" => "create",
                        'link' => 'admin/start/menu/content_items',
                        'icon' => 'font',
                        'status' => 1,
                        'sorter' => 2,
                    ),
                    'system-items' => array(
                        "action" => "create",
                        'link' => 'admin/start/menu/system-items',
                        'icon' => 'cog',
                        'status' => 1,
                        'sorter' => 3,
                        "items" => array(
                            'system-numerics-item' => array(
                                "action" => "create",
                                'link' => 'admin/start/settings',
                                'icon' => '',
                                'status' => 1,
                                'sorter' => 6,
                            ),
                        /* 'system-geolocation-item' => array(
                          "action" => "create",
                          'link' => 'admin/start/geolocation',
                          'icon' => '',
                          'status' => 1,
                          'sorter' => 6,
                          ), */
                        ),
                    ),
                ),
            ),
            'other_items' => array(
                "action" => "create",
                'link' => 'admin/start',
                'status' => 1,
                'sorter' => 4,
                "items" => array(
                    "add_ons_items" => array(
                        "action" => "create",
                        'link' => 'admin/start/menu/add_ons_items',
                        'icon' => 'puzzle-piece',
                        'status' => 1,
                        'sorter' => 2,
                        'items' => array(
                            'admin-modules-item' => array(
                                "action" => "create",
                                'link' => 'admin/start/mod_login',
                                'icon' => 'power-off',
                                'status' => 1,
                                'sorter' => 1,
                            ),
                        )
                    ),
                ),
            ),
        ),
    ),
    // guest main menu
    'guest_main_menu' => array(
        'name' => 'Guest main menu',
        "action" => "create",
        "items" => array(
            'main-menu-home-item' => array(
                "action" => "create",
                'link' => 'start/index',
                'status' => 1,
                'sorter' => 1,
            ),
        ),
    ),
    // footer menu
    'user_footer_menu' => array(
        'name' => 'Footer menu',
        "action" => "create",
        "items" => array(
            'footer-menu-help-item' => array(
                "action" => "create",
                'link' => '/',
                'status' => 1,
                'sorter' => 1,
            ),
            'footer-menu-about-item' => array(
                "action" => "create",
                'link' => '/',
                'status' => 1,
                'sorter' => 2,
            ),
            'footer-menu-policy-item' => array(
                "action" => "create",
                'link' => '/',
                'status' => 1,
                'sorter' => 3,
            ),
            'footer-menu-links-item' => array(
                "action" => "create",
                'link' => '/',
                'status' => 1,
                'sorter' => 4,
            ),
        ),
    ),
    // user top menu
    'user_top_menu' => array(
        'name' => 'User top menu',
        "action" => "create",
        "items" => array(
            'user-menu-homepage' => array("action" => "create", 'icon' => 'square-o',
                'link' => 'start', 'status' => 1, 'sorter' => 1),
            'user-menu-people' => array("action" => "create", 'icon' => 'users',
                'link' => '', 'status' => 1, 'sorter' => 2),
            'user-menu-communication' => array("action" => "create", 'icon' => 'retweet',
                'link' => '', 'status' => 1, 'sorter' => 3),
            'user-menu-activities' => array("action" => "create", 'icon' => 'bullhorn',
                'link' => '', 'status' => 1, 'sorter' => 4),
        ),
    ),
    'user_top_advanced_menu' => array(
        'name' => 'User top advanced menu',
        "action" => "create",
        "items" => array(
        ),
    ),
    // user homepage menu
    'user_homepage_menu' => array(
        'name' => 'User homepage menu',
        "action" => "create",
        "items" => array(
        ),
    ),
    // settings top menu
    'settings_menu' => array(
        'name' => 'Settings menu',
        "action" => "create",
        "items" => array(
            'settings-menu-home' => array("action" => "create", 'link' => 'start/homepage',
                'status' => 1, 'sorter' => 1),
        ),
    ),
    // alerts for authorized user
    'user_alerts_menu' => array(
        'name' => 'User alerts',
        'action' => 'create',
        'items' => array(
        ),
    ),
    // alerts for guests
    'guest_alerts_menu' => array(
        'name' => 'Guest alerts',
        'action' => 'create',
        'items' => array(
        ),
    ),
);
