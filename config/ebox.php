<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration e-Box Enterprise
    |--------------------------------------------------------------------------
    |
    | Configuration principale du package e-Box pour Laravel
    | Conforme à la documentation technique de dev.eboxenterprise.be
    |
    */
    
    'version' => '1.0.0',
    
    /*
    |--------------------------------------------------------------------------
    | Profil d'intégration par défaut
    |--------------------------------------------------------------------------
    |
    | central: Utilise le registre de messages centralisé e-Box
    | private: Configure un registre de messages privé pour confidentialité maximale
    |
    */
    'default_integration_profile' => env('EBOX_INTEGRATION_PROFILE', 'central'),
    
    /*
    |--------------------------------------------------------------------------
    | Configuration des registres
    |--------------------------------------------------------------------------
    */
    'registries' => [
        'central' => [
            'name' => 'Registre Central e-Box',
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
    | Configuration de confidentialité
    |--------------------------------------------------------------------------
    */
    'confidentiality' => [
        'default_level' => env('EBOX_DEFAULT_CONFIDENTIALITY', 'standard'),
        
        'levels' => [
            'standard' => [
                'description' => 'Confidentialité standard avec passage par serveurs tiers',
                'encryption' => 'optional',
            ],
            'high' => [
                'description' => 'Confidentialité élevée avec chiffrement de bout en bout',
                'encryption' => 'required',
            ],
            'maximum' => [
                'description' => 'Confidentialité maximale sans passage par serveurs tiers',
                'encryption' => 'required',
                'requires_private_registry' => true,
            ],
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Configuration de l'audit
    |--------------------------------------------------------------------------
    */
    'audit' => [
        'enabled' => env('EBOX_AUDIT_ENABLED', true),
        'retention_days' => env('EBOX_AUDIT_RETENTION_DAYS', 365 * 5), // 5 ans
        'log_read_events' => env('EBOX_LOG_READ_EVENTS', true),
        'log_status_checks' => env('EBOX_LOG_STATUS_CHECKS', true),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Configuration des files d'attente
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
    | Configuration des webhooks
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
    | Validation des identifiants
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
        'ttl' => env('EBOX_CACHE_TTL', 3600), // 1 heure
        'identity_ttl' => env('EBOX_IDENTITY_CACHE_TTL', 86400), // 24 heures
    ],
    
    /*
    |--------------------------------------------------------------------------
    | API CBE (Crossroads Bank for Enterprises)
    |--------------------------------------------------------------------------
    */
    'cbe_api_endpoint' => env('EBOX_CBE_API_ENDPOINT', 'https://api.cbe.be/v1'),
];

