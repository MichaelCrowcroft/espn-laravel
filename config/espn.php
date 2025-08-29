<?php

return [
    // Global request options
    'timeout' => 30, // seconds
    'connect_timeout' => 10, // seconds

    // ESPN Core Sports API (v3)
    'core' => [
        'base_url' => env('ESPN_CORE_BASE_URL', 'https://sports.core.api.espn.com/v3'),
    ],

    // ESPN Fantasy API (reads)
    'fantasy' => [
        'base_url' => env('ESPN_FANTASY_BASE_URL', 'https://lm-api-reads.fantasy.espn.com/apis/v3'),
        // Default limit for X-Fantasy-Filter when using helpers
        'default_limit' => 2000,
    ],
];
