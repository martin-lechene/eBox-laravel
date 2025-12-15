<?php

namespace Ebox\Enterprise\Services\Audit;

use Ebox\Enterprise\Models\EboxMessage;
use Ebox\Enterprise\Models\MessageAuditLog;
use Illuminate\Support\Facades\Request;

/**
 * Service de logging d'audit pour e-Box
 */
class AuditLogger
{
    /**
     * Log de l'envoi d'un message
     */
    public function logMessageSent(EboxMessage $message, array $details = []): void
    {
        $this->log($message, 'sent', $details);
    }
    
    /**
     * Log de la délivrance d'un message
     */
    public function logMessageDelivered(EboxMessage $message, array $details = []): void
    {
        $this->log($message, 'delivered', $details);
    }
    
    /**
     * Log de la lecture d'un message
     */
    public function logMessageRead(EboxMessage $message, array $details = []): void
    {
        $this->log($message, 'read', $details);
    }
    
    /**
     * Log de vérification de statut
     */
    public function logStatusCheck(EboxMessage $message, array $details = []): void
    {
        $this->log($message, 'status_check', $details);
    }
    
    /**
     * Log générique
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

