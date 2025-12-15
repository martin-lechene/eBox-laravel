<?php

namespace Ebox\Enterprise\Core\Enums;

/**
 * Conforme à la documentation e-Box : deux profils d'intégration
 */
enum IntegrationProfile: string
{
    case CENTRAL_REGISTRY = 'central';
    case PRIVATE_REGISTRY = 'private';
    
    public function description(): string
    {
        return match($this) {
            self::CENTRAL_REGISTRY => 'Utilise le registre de messages centralisé e-Box',
            self::PRIVATE_REGISTRY => 'Configure un registre de messages privé pour confidentialité maximale',
        };
    }
    
    public function supportsHighConfidentiality(): bool
    {
        return $this === self::PRIVATE_REGISTRY;
    }
}

