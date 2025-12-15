<?php

namespace Ebox\Enterprise\Services\Messaging;

use Ebox\Enterprise\Models\EboxMessage;
use Ebox\Enterprise\Core\Enums\MessageStatus;

/**
 * Service pour la gestion des statuts de messages
 */
class MessageStatusService
{
    /**
     * Mise à jour du statut d'un message
     */
    public function updateStatus(EboxMessage $message, MessageStatus $status, array $metadata = []): bool
    {
        $updateData = [
            'status' => $status,
            'status_updated_at' => now(),
        ];
        
        if ($status === MessageStatus::DELIVERED && !$message->delivered_at) {
            $updateData['delivered_at'] = now();
        }
        
        if ($status === MessageStatus::READ && !$message->read_at) {
            $updateData['read_at'] = now();
        }
        
        return $message->update($updateData);
    }
    
    /**
     * Récupération de l'historique des statuts
     */
    public function getStatusHistory(EboxMessage $message): array
    {
        return $message->auditLogs()
            ->where('action', 'like', 'status_%')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($log) {
                return [
                    'status' => $log->details['status'] ?? null,
                    'timestamp' => $log->created_at->toISOString(),
                    'actor' => $log->actor_identifier,
                ];
            })
            ->toArray();
    }
}

