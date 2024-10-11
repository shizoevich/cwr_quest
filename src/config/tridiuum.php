<?php
return [
    'enable_auto_assign_providers' => env('TRIDIUUM_ENABLE_AUTO_ASSIGN_PROVIDERS', false),
    'default_account' => env('TRIDIUUM_DEFAULT_ACCOUNT'),

    // 'requests_log_enabled' => env('TRIDIUUM_REQUESTS_LOG_ENABLED', false),
    'slack_log_level' => env('TRIDIUUM_SLACK_LOG_LEVEL', 'notice'),
    'sentry_log_level' => env('TRIDIUUM_SENTRY_LOG_LEVEL', 'notice'),

    'sentry_dsn' => env('TRIDIUUM_SENTRY_DSN', env('SENTRY_LARAVEL_DSN', env('SENTRY_DSN')))
];