<?php
return [
    'env' => env('OFFICE_ALLY_ENV', 'local'),
    'proxy' => env('OFFICE_ALLY_PROXY'), 
    'proxy_enabled' => env('OFFICE_ALLY_PROXY_ENABLED', false),
    'login_script' => env('OFFICE_ALLY_LOGIN_SCRIPT', 'playwright-login.js'),
    'log_to_table_enabled' => env('OFFICE_ALLY_LOG_TO_TABLE_ENABLED', true),

    // 'requests_log_enabled' => env('OFFICE_ALLY_REQUESTS_LOG_ENABLED', false),
    'slack_log_level' => env('OFFICE_ALLY_SLACK_LOG_LEVEL', 'notice'),
    'sentry_log_level' => env('OFFICE_ALLY_SENTRY_LOG_LEVEL', 'notice'),

    'sentry_dsn' => env('OFFICE_ALLY_SENTRY_DSN', env('SENTRY_LARAVEL_DSN', env('SENTRY_DSN')))
];