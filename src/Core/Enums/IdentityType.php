<?php

namespace Ebox\Enterprise\Core\Enums;

/**
 * Types d'identités belges supportées par e-Box
 */
enum IdentityType: string
{
    case CBE = 'CBE';  // Numéro d'entreprise (10 chiffres)
    case NRN = 'NRN';  // Numéro national (11 chiffres)
    
    public function description(): string
    {
        return match($this) {
            self::CBE => 'Numéro d\'entreprise belge (10 chiffres)',
            self::NRN => 'Numéro national de registre (11 chiffres)',
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

