<?php

namespace Ebox\Enterprise\Core\Contracts;

use Ebox\Enterprise\Core\ValueObjects\BelgianIdentity;
use Ebox\Enterprise\Core\Enums\IdentityType;

/**
 * Interface for Belgian identity resolution
 */
interface IdentityResolverInterface
{
    /**
     * Resolve a Belgian identity (CBE/NRN)
     */
    public function resolve(string $identifier, IdentityType $type): ?BelgianIdentity;
    
    /**
     * Validate a Belgian identity
     */
    public function validate(string $identifier, IdentityType $type): bool;
    
    /**
     * Get complete identity information
     */
    public function getIdentityInfo(string $identifier, IdentityType $type): array;
}

