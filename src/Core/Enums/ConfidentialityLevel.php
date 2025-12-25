<?php

namespace Ebox\Enterprise\Core\Enums;

/**
 * Confidentiality levels compliant with e-Box documentation
 */
enum ConfidentialityLevel: string
{
    case STANDARD = 'standard';
    case HIGH = 'high';
    case MAXIMUM = 'maximum';
    
    public function description(): string
    {
        return match($this) {
            self::STANDARD => 'Standard confidentiality with third-party server routing',
            self::HIGH => 'High confidentiality with end-to-end encryption',
            self::MAXIMUM => 'Maximum confidentiality without third-party server routing',
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

