<?php

namespace Ebox\Enterprise\Services\Identity;

use Ebox\Enterprise\Core\Contracts\IdentityResolverInterface;
use Ebox\Enterprise\Core\ValueObjects\BelgianIdentity;
use Ebox\Enterprise\Core\Enums\IdentityType;
use Ebox\Enterprise\Models\IdentityMapping;
use Ebox\Enterprise\Core\Exceptions\IdentityNotFoundException;

/**
 * Service de résolution des identités belges
 */
class IdentityResolverService implements IdentityResolverInterface
{
    /**
     * Résolution d'une identité belge (CBE/NRN)
     */
    public function resolve(string $identifier, IdentityType $type): ?BelgianIdentity
    {
        $mapping = IdentityMapping::where('identifier', $identifier)
            ->where('type', $type->value)
            ->first();
        
        if (!$mapping) {
            return null;
        }
        
        return new BelgianIdentity(
            $identifier,
            $type,
            $mapping->name
        );
    }
    
    /**
     * Validation d'une identité belge
     */
    public function validate(string $identifier, IdentityType $type): bool
    {
        $pattern = $type->pattern();
        return preg_match($pattern, $identifier) === 1;
    }
    
    /**
     * Récupération des informations complètes d'une identité
     */
    public function getIdentityInfo(string $identifier, IdentityType $type): array
    {
        $mapping = IdentityMapping::where('identifier', $identifier)
            ->where('type', $type->value)
            ->first();
        
        if (!$mapping) {
            throw new IdentityNotFoundException($identifier, $type->value);
        }
        
        return [
            'identifier' => $mapping->identifier,
            'type' => $mapping->type,
            'name' => $mapping->name,
            'email' => $mapping->email,
            'phone' => $mapping->phone,
            'address' => $mapping->address,
            'is_validated' => $mapping->is_validated,
            'validated_at' => $mapping->validated_at?->toISOString(),
            'cached_data' => $mapping->cached_data,
        ];
    }
}

