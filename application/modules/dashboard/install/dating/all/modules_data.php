<?php
return [
    'cronjobs' => [
        [
            "name" => "Dashboard items clean",
            "module" => "dashboard",
            "model" => "Dashboard_model",
            "method" => "clear",
            "cron_tab" => "30 1 1 * *",
            "status" => "1",
        ],
    ],
];
