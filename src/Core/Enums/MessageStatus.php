<?php

namespace Ebox\Enterprise\Core\Enums;

/**
 * Message statuses compliant with e-Box API
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
            self::DRAFT => 'Draft',
            self::SENT => 'Sent',
            self::DELIVERED => 'Delivered',
            self::READ => 'Read',
            self::FAILED => 'Failed',
            self::ARCHIVED => 'Archived',
        };
    }
    
    public function isFinal(): bool
    {
        return in_array($this, [self::READ, self::FAILED, self::ARCHIVED]);
    }
}

