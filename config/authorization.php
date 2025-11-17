<?php

return [
    'cache' => [
        'user_permissions_ttl' => (int) env('AUTH_PERMISSION_CACHE_TTL', 3600),
        'role_permissions_prefix' => 'auth:role',
        'user_permissions_prefix' => 'auth:user',
    ],

    'audit' => [
        'retention_days' => (int) env('AUDIT_LOG_RETENTION_DAYS', 365),
    ],
];
