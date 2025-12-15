<?php

namespace Ebox\Enterprise\Core\Contracts;

use Ebox\Enterprise\Core\ValueObjects\BelgianIdentity;
use Ebox\Enterprise\Core\Enums\IdentityType;

/**
 * Interface pour la résolution des identités belges
 */
interface IdentityResolverInterface
{
    /**
     * Résolution d'une identité belge (CBE/NRN)
     */
    public function resolve(string $identifier, IdentityType $type): ?BelgianIdentity;
    
    /**
     * Validation d'une identité belge
     */
    public function validate(string $identifier, IdentityType $type): bool;
    
    /**
     * Récupération des informations complètes d'une identité
     */
    public function getIdentityInfo(string $identifier, IdentityType $type): array;
}

