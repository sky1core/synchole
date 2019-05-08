<?php

return [

    'github' => [
        'app_id' => env('GITHUB_APP_ID'),
        'username' => env('GITHUB_USERNAME'),
    ],

    'main_domain' => env('MAIN_DOMAIN', 'localhost'),
    'google_auth' => [
        'use' => env('USE_GOOGLE_AUTH', false),
    ],
    'protocols' => env('PROTOCOLS', 'http,https'),
    'gc_hours' => env('GC_HOURS', 48),
];