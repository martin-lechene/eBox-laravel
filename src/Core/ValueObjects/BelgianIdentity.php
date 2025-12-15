<?php

namespace Ebox\Enterprise\Core\ValueObjects;

use Ebox\Enterprise\Core\Enums\IdentityType;
use Ebox\Enterprise\Core\Exceptions\InvalidIdentityException;

/**
 * Value Object pour les identifiants belges (CBE/NRN)
 * Conforme à la documentation e-Box sur l'authentification forte
 */
class BelgianIdentity
{
    private string $identifier;
    private IdentityType $type;
    private ?string $name;
    
    public function __construct(string $identifier, IdentityType $type, ?string $name = null)
    {
        $this->validateIdentifier($identifier, $type);
        
        $this->identifier = $identifier;
        $this->type = $type;
        $this->name = $name;
    }
    
    private function validateIdentifier(string $identifier, IdentityType $type): void
    {
        $pattern = match($type) {
            IdentityType::CBE => '/^\d{10}$/', // CBE: 10 chiffres
            IdentityType::NRN => '/^\d{11}$/', // NRN: 11 chiffres
        };
        
        if (!preg_match($pattern, $identifier)) {
            throw new InvalidIdentityException(
                "Identifiant {$type->value} invalide: {$identifier}"
            );
        }
    }
    
    public function getIdentifier(): string
    {
        return $this->identifier;
    }
    
    public function getType(): IdentityType
    {
        return $this->type;
    }
    
    public function getName(): ?string
    {
        return $this->name;
    }
    
    public function toString(): string
    {
        return "{$this->type->value}:{$this->identifier}";
    }
    
    public function equals(self $other): bool
    {
        return $this->identifier === $other->identifier 
            && $this->type === $other->type;
    }
}

