<?php

return [
    'source' => env('SYNC_SOURCE', 'store'),
    'default_currency' => env('SYNC_DEFAULT_CURRENCY', 'SAR'),
    'default_target_system' => env('SYNC_TARGET_SYSTEM', 'erp'),
    'outbox' => [
        'enabled' => env('SYNC_OUTBOX_ENABLED', true),
    ],
];
