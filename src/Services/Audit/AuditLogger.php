<?php

namespace Ebox\Enterprise\Services\Audit;

use Ebox\Enterprise\Models\EboxMessage;
use Ebox\Enterprise\Models\MessageAuditLog;
use Illuminate\Support\Facades\Request;

/**
 * Audit logging service for e-Box
 */
class AuditLogger
{
    /**
     * Log message sent
     */
    public function logMessageSent(EboxMessage $message, array $details = []): void
    {
        $this->log($message, 'sent', $details);
    }
    
    /**
     * Log message delivered
     */
    public function logMessageDelivered(EboxMessage $message, array $details = []): void
    {
        $this->log($message, 'delivered', $details);
    }
    
    /**
     * Log message read
     */
    public function logMessageRead(EboxMessage $message, array $details = []): void
    {
        $this->log($message, 'read', $details);
    }
    
    /**
     * Log status check
     */
    public function logStatusCheck(EboxMessage $message, array $details = []): void
    {
        $this->log($message, 'status_check', $details);
    }
    
    /**
     * Generic log
     */
    private function log(EboxMessage $message, string $action, array $details = []): void
    {
        if (!config('ebox.audit.enabled', true)) {
            return;
        }
        
        MessageAuditLog::create([
            'ebox_message_id' => $message->id,
            'action' => $action,
            'actor_identifier' => auth()->user()?->belgian_identity ?? 'system',
            'actor_type' => auth()->user()?->identity_type ?? 'system',
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'details' => array_merge($details, [
                'timestamp' => now()->toISOString(),
            ]),
        ]);
    }
}

