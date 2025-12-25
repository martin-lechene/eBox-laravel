<?php

namespace Ebox\Enterprise\Core\Enums;

/**
 * Compliant with e-Box documentation: two integration profiles
 */
enum IntegrationProfile: string
{
    case CENTRAL_REGISTRY = 'central';
    case PRIVATE_REGISTRY = 'private';
    
    public function description(): string
    {
        return match($this) {
            self::CENTRAL_REGISTRY => 'Uses the centralized e-Box message registry',
            self::PRIVATE_REGISTRY => 'Configures a private message registry for maximum confidentiality',
        };
    }
    
    public function supportsHighConfidentiality(): bool
    {
        return $this === self::PRIVATE_REGISTRY;
    }
}

