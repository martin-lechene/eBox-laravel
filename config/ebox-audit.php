<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration de l'audit e-Box
    |--------------------------------------------------------------------------
    */
    
    'enabled' => env('EBOX_AUDIT_ENABLED', true),
    
    'retention_days' => env('EBOX_AUDIT_RETENTION_DAYS', 365 * 5),
    
    'log_events' => [
        'message_sent' => true,
        'message_delivered' => true,
        'message_read' => true,
        'status_check' => true,
        'message_access' => true,
    ],
];

