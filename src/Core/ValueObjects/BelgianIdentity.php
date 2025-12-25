<?php

namespace Ebox\Enterprise\Core\ValueObjects;

use Ebox\Enterprise\Core\Enums\IdentityType;
use Ebox\Enterprise\Core\Exceptions\InvalidIdentityException;

/**
 * Value Object for Belgian identifiers (CBE/NRN)
 * Compliant with e-Box documentation on strong authentication
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
            IdentityType::CBE => '/^\d{10}$/', // CBE: 10 digits
            IdentityType::NRN => '/^\d{11}$/', // NRN: 11 digits
        };
        
        if (!preg_match($pattern, $identifier)) {
            throw new InvalidIdentityException(
                "Invalid {$type->value} identifier: {$identifier}"
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

