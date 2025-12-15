<?php

namespace Ebox\Enterprise\Core\ValueObjects;

/**
 * Value Object pour les métadonnées des messages e-Box
 */
class MessageMetadata
{
    private array $metadata;
    
    public function __construct(array $metadata = [])
    {
        $this->metadata = $metadata;
    }
    
    public function get(string $key, $default = null)
    {
        return $this->metadata[$key] ?? $default;
    }
    
    public function set(string $key, $value): self
    {
        $this->metadata[$key] = $value;
        return $this;
    }
    
    public function has(string $key): bool
    {
        return isset($this->metadata[$key]);
    }
    
    public function toArray(): array
    {
        return $this->metadata;
    }
    
    public function merge(array $metadata): self
    {
        $this->metadata = array_merge($this->metadata, $metadata);
        return $this;
    }
}

