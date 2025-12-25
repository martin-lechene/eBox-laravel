<?php

namespace Ebox\Enterprise\Core\Enums;

/**
 * Belgian identity types supported by e-Box
 */
enum IdentityType: string
{
    case CBE = 'CBE';  // Company number (10 digits)
    case NRN = 'NRN';  // National register number (11 digits)
    
    public function description(): string
    {
        return match($this) {
            self::CBE => 'Belgian company number (10 digits)',
            self::NRN => 'National register number (11 digits)',
        };
    }
    
    public function pattern(): string
    {
        return match($this) {
            self::CBE => '/^\d{10}$/',
            self::NRN => '/^\d{11}$/',
        };
    }
}

