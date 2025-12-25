<?php

return [
    /*
    |--------------------------------------------------------------------------
    | e-Box Enterprise Configuration
    |--------------------------------------------------------------------------
    |
    | Main configuration for the e-Box Laravel package
    | Compliant with the technical documentation at dev.eboxenterprise.be
    |
    */
    
    'version' => '1.0.0',
    
    /*
    |--------------------------------------------------------------------------
    | Default Integration Profile
    |--------------------------------------------------------------------------
    |
    | central: Uses the centralized e-Box message registry
    | private: Configures a private message registry for maximum confidentiality
    |
    */
    'default_integration_profile' => env('EBOX_INTEGRATION_PROFILE', 'central'),
    
    /*
    |--------------------------------------------------------------------------
    | Registry Configuration
    |--------------------------------------------------------------------------
    */
    'registries' => [
        'central' => [
            'name' => 'e-Box Central Registry',
            'endpoint' => env('EBOX_CENTRAL_ENDPOINT', 'https://api.eboxenterprise.be/v1'),
            'api_key' => env('EBOX_CENTRAL_API_KEY'),
            'api_secret' => env('EBOX_CENTRAL_API_SECRET'),
        ],
        
        'private' => [
            'enabled' => env('EBOX_PRIVATE_REGISTRY_ENABLED', false),
            'endpoint' => env('EBOX_PRIVATE_REGISTRY_ENDPOINT'),
            'api_key' => env('EBOX_PRIVATE_REGISTRY_API_KEY'),
            'api_secret' => env('EBOX_PRIVATE_REGISTRY_SECRET'),
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Confidentiality Configuration
    |--------------------------------------------------------------------------
    */
    'confidentiality' => [
        'default_level' => env('EBOX_DEFAULT_CONFIDENTIALITY', 'standard'),
        
        'levels' => [
            'standard' => [
                'description' => 'Standard confidentiality with third-party server routing',
                'encryption' => 'optional',
            ],
            'high' => [
                'description' => 'High confidentiality with end-to-end encryption',
                'encryption' => 'required',
            ],
            'maximum' => [
                'description' => 'Maximum confidentiality without third-party server routing',
                'encryption' => 'required',
                'requires_private_registry' => true,
            ],
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Audit Configuration
    |--------------------------------------------------------------------------
    */
    'audit' => [
        'enabled' => env('EBOX_AUDIT_ENABLED', true),
        'retention_days' => env('EBOX_AUDIT_RETENTION_DAYS', 365 * 5), // 5 years
        'log_read_events' => env('EBOX_LOG_READ_EVENTS', true),
        'log_status_checks' => env('EBOX_LOG_STATUS_CHECKS', true),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    */
    'queue' => [
        'enabled' => env('EBOX_QUEUE_ENABLED', true),
        'connection' => env('EBOX_QUEUE_CONNECTION', 'redis'),
        'queue' => env('EBOX_QUEUE_NAME', 'ebox_messages'),
        'retry_after' => env('EBOX_QUEUE_RETRY_AFTER', 90),
        'tries' => env('EBOX_QUEUE_TRIES', 3),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Webhooks Configuration
    |--------------------------------------------------------------------------
    */
    'webhooks' => [
        'enabled' => env('EBOX_WEBHOOKS_ENABLED', true),
        'secret' => env('EBOX_WEBHOOK_SECRET'),
        'events' => [
            'message.delivered',
            'message.read',
            'message.failed',
            'status.updated',
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Identifier Validation
    |--------------------------------------------------------------------------
    */
    'validation' => [
        'cbe' => [
            'pattern' => '/^\d{10}$/',
            'checksum' => env('EBOX_VALIDATE_CBE_CHECKSUM', true),
        ],
        'nrn' => [
            'pattern' => '/^\d{11}$/',
            'checksum' => env('EBOX_VALIDATE_NRN_CHECKSUM', true),
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    */
    'routes' => [
        'prefix' => env('EBOX_ROUTES_PREFIX', 'api/ebox'),
        'middleware' => ['api', 'auth:sanctum'],
        'api_version' => 'v1',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => env('EBOX_CACHE_ENABLED', true),
        'ttl' => env('EBOX_CACHE_TTL', 3600), // 1 hour
        'identity_ttl' => env('EBOX_IDENTITY_CACHE_TTL', 86400), // 24 hours
    ],
    
    /*
    |--------------------------------------------------------------------------
    | CBE API (Crossroads Bank for Enterprises)
    |--------------------------------------------------------------------------
    */
    'cbe_api_endpoint' => env('EBOX_CBE_API_ENDPOINT', 'https://api.cbe.be/v1'),
];

