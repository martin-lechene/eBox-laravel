<?php

namespace Ebox\Enterprise\Core\Enums;

/**
 * Niveaux de confidentialité conformes à la documentation e-Box
 */
enum ConfidentialityLevel: string
{
    case STANDARD = 'standard';
    case HIGH = 'high';
    case MAXIMUM = 'maximum';
    
    public function description(): string
    {
        return match($this) {
            self::STANDARD => 'Confidentialité standard avec passage par serveurs tiers',
            self::HIGH => 'Confidentialité élevée avec chiffrement de bout en bout',
            self::MAXIMUM => 'Confidentialité maximale sans passage par serveurs tiers',
        };
    }
    
    public function requiresEncryption(): bool
    {
        return in_array($this, [self::HIGH, self::MAXIMUM]);
    }
    
    public function requiresPrivateRegistry(): bool
    {
        return $this === self::MAXIMUM;
    }
}

