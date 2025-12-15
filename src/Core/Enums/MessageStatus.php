<?php

namespace Ebox\Enterprise\Core\Enums;

/**
 * Statuts des messages conformes à l'API e-Box
 */
enum MessageStatus: string
{
    case DRAFT = 'draft';
    case SENT = 'sent';
    case DELIVERED = 'delivered';
    case READ = 'read';
    case FAILED = 'failed';
    case ARCHIVED = 'archived';
    
    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Brouillon',
            self::SENT => 'Envoyé',
            self::DELIVERED => 'Délivré',
            self::READ => 'Lu',
            self::FAILED => 'Échec',
            self::ARCHIVED => 'Archivé',
        };
    }
    
    public function isFinal(): bool
    {
        return in_array($this, [self::READ, self::FAILED, self::ARCHIVED]);
    }
}

