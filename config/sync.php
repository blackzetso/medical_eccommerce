<?php

return [
    /*
     * Incoming events with source === this value are ignored (e.g. echo from same system).
     * Keep as "store" so events from Orga/ERP (source "erp") are processed. Do not set to "erp" or Orga updates will be ignored.
     */
    'source' => env('SYNC_SOURCE', 'store'),
    'default_currency' => env('SYNC_DEFAULT_CURRENCY', 'SAR'),
    'default_target_system' => env('SYNC_TARGET_SYSTEM', 'erp'),
    'outbox' => [
        'enabled' => env('SYNC_OUTBOX_ENABLED', true),
    ],
];
