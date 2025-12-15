<?php

namespace Ebox\Enterprise\Services\Messaging;

use Ebox\Enterprise\Core\Contracts\MessagingInterface;
use Ebox\Enterprise\Core\Contracts\RegistryInterface;
use Ebox\Enterprise\Core\ValueObjects\BelgianIdentity;
use Ebox\Enterprise\Core\Enums\IntegrationProfile;
use Ebox\Enterprise\Core\Enums\ConfidentialityLevel;
use Ebox\Enterprise\Core\Enums\MessageStatus;
use Ebox\Enterprise\Models\EboxMessage;
use Ebox\Enterprise\Models\MessageRegistry;
use Ebox\Enterprise\Services\Audit\AuditLogger;
use Ebox\Enterprise\Core\Exceptions\RegistryNotFoundException;
use Ebox\Enterprise\Core\Exceptions\MessageDeliveryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class EboxMessagingService implements MessagingInterface
{
    private RegistryInterface $registryService;
    private AuditLogger $auditLogger;
    private Client $httpClient;
    
    public function __construct(
        RegistryInterface $registryService,
        AuditLogger $auditLogger
    ) {
        $this->registryService = $registryService;
        $this->auditLogger = $auditLogger;
        $this->httpClient = new Client([
            'timeout' => 30,
            'connect_timeout' => 10,
        ]);
    }
    
    /**
     * Envoi d'un message selon les profils d'intégration e-Box
     */
    public function sendMessage(array $messageData, IntegrationProfile $profile): EboxMessage
    {
        return DB::transaction(function () use ($messageData, $profile) {
            // 1. Création du message en base
            $message = EboxMessage::create([
                'sender_identifier' => $messageData['sender_identifier'],
                'sender_type' => $messageData['sender_type'],
                'sender_name' => $messageData['sender_name'] ?? null,
                'recipient_identifier' => $messageData['recipient_identifier'],
                'recipient_type' => $messageData['recipient_type'],
                'recipient_name' => $messageData['recipient_name'] ?? null,
                'subject' => $messageData['subject'],
                'body' => $messageData['body'],
                'message_type' => $messageData['message_type'] ?? 'official',
                'confidentiality_level' => $messageData['confidentiality_level'] ?? ConfidentialityLevel::STANDARD,
                'metadata' => $messageData['metadata'] ?? [],
            ]);
            
            // 2. Sélection du registre selon le profil
            $confidentialityLevel = isset($messageData['confidentiality_level']) 
                ? $messageData['confidentiality_level'] 
                : null;
            $registry = $this->registryService->selectRegistry($profile->value, $confidentialityLevel);
            $message->message_registry_id = $registry->id;
            $message->registry_endpoint = $registry->endpoint_url;
            $message->save();
            
            // 3. Envoi au registre
            try {
                $externalId = $this->registryService->sendToRegistry($message, $registry);
                $message->external_message_id = $externalId;
                $message->status = MessageStatus::SENT;
                $message->status_updated_at = now();
                $message->save();
            } catch (\Exception $e) {
                $this->logFailedDelivery($message, $registry, $e->getMessage());
                throw new MessageDeliveryException(
                    "Échec de l'envoi au registre: " . $e->getMessage(),
                    $e->getCode() ?: 500,
                    $e
                );
            }
            
            // 4. Audit
            $this->auditLogger->logMessageSent($message, [
                'profile' => $profile->value,
                'registry' => $registry->name,
                'confidentiality' => $message->confidentiality_level->value,
            ]);
            
            // 5. Event
            event(new \Ebox\Enterprise\Events\MessageSent($message));
            
            return $message;
        });
    }
    
    
    /**
     * Récupération du statut d'un message
     * Conforme à l'API d'audit e-Box
     */
    public function getMessageStatus(string $messageId): array
    {
        $message = EboxMessage::where('external_message_id', $messageId)
            ->orWhere('id', $messageId)
            ->firstOrFail();
        
        $registry = $message->registry;
        
        // Si le message a un registre, on interroge l'API e-Box
        if ($registry && $message->external_message_id) {
            $status = $this->fetchStatusFromRegistry($message, $registry);
            
            // Mise à jour du statut local
            if ($status['status'] !== $message->status->value) {
                $message->update([
                    'status' => $status['status'],
                    'read_at' => isset($status['read_at']) ? \Carbon\Carbon::parse($status['read_at']) : null,
                    'delivered_at' => isset($status['delivered_at']) ? \Carbon\Carbon::parse($status['delivered_at']) : null,
                    'status_updated_at' => now(),
                ]);
            }
            
            $this->auditLogger->logStatusCheck($message, [
                'source' => 'registry_api',
                'remote_status' => $status['status'],
            ]);
            
            return $status;
        }
        
        // Sinon, on retourne le statut local
        $this->auditLogger->logStatusCheck($message, [
            'source' => 'local_database',
        ]);
        
        return [
            'message_id' => $message->external_message_id ?? $message->id,
            'status' => $message->status->value,
            'read_at' => $message->read_at?->toISOString(),
            'delivered_at' => $message->delivered_at?->toISOString(),
            'last_updated' => $message->status_updated_at?->toISOString(),
        ];
    }
    
    private function fetchStatusFromRegistry(EboxMessage $message, MessageRegistry $registry): array
    {
        try {
            $status = $this->registryService->fetchStatusFromRegistry($message, $registry);
            return $status;
        } catch (\Exception $e) {
            // Fallback au statut local en cas d'erreur
            Log::warning("Impossible de récupérer le statut du registre", [
                'message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'status' => $message->status->value,
                'read_at' => $message->read_at?->toISOString(),
                'delivered_at' => $message->delivered_at?->toISOString(),
                'source' => 'local_fallback',
            ];
        }
    }
    
    private function logFailedDelivery(EboxMessage $message, MessageRegistry $registry, string $error): void
    {
        $message->failedDeliveries()->create([
            'registry_id' => $registry->id,
            'error_message' => $error,
            'retry_count' => 1,
            'next_retry_at' => now()->addMinutes(5),
        ]);
    }
}

