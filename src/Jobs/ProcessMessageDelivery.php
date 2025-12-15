<?php

namespace Ebox\Enterprise\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Ebox\Enterprise\Models\EboxMessage;
use Ebox\Enterprise\Services\Messaging\EboxMessagingService;
use Ebox\Enterprise\Core\Enums\IntegrationProfile;
use Illuminate\Support\Facades\Log;

class ProcessMessageDelivery implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $tries = 3;
    public $backoff = [60, 300, 600]; // Retry after 1, 5, 10 minutes
    public $timeout = 120;
    
    private string $messageId;
    private string $profile;
    
    public function __construct(string $messageId, string $profile = 'central')
    {
        $this->messageId = $messageId;
        $this->profile = $profile;
    }
    
    public function handle(EboxMessagingService $messagingService): void
    {
        $message = EboxMessage::findOrFail($this->messageId);
        
        if ($message->status->value !== 'draft') {
            Log::warning("Message déjà traité", ['message_id' => $this->messageId]);
            return;
        }
        
        try {
            $messagingService->sendMessage(
                [
                    'sender_identifier' => $message->sender_identifier,
                    'sender_type' => $message->sender_type,
                    'sender_name' => $message->sender_name,
                    'recipient_identifier' => $message->recipient_identifier,
                    'recipient_type' => $message->recipient_type,
                    'recipient_name' => $message->recipient_name,
                    'subject' => $message->subject,
                    'body' => $message->body,
                    'message_type' => $message->message_type,
                    'confidentiality_level' => $message->confidentiality_level->value,
                    'metadata' => $message->metadata,
                ],
                IntegrationProfile::from($this->profile)
            );
            
        } catch (\Exception $e) {
            Log::error("Échec de l'envoi du message e-Box", [
                'message_id' => $this->messageId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Réessayer le job
            $this->fail($e);
        }
    }
    
    public function failed(\Throwable $exception): void
    {
        $message = EboxMessage::find($this->messageId);
        
        if ($message) {
            $message->update(['status' => \Ebox\Enterprise\Core\Enums\MessageStatus::FAILED]);
            
            $message->failedDeliveries()->create([
                'error_message' => $exception->getMessage(),
                'error_code' => $exception->getCode(),
                'retry_count' => $this->attempts(),
            ]);
        }
        
        Log::critical("Job d'envoi e-Box échoué", [
            'message_id' => $this->messageId,
            'attempts' => $this->attempts(),
            'error' => $exception->getMessage(),
        ]);
    }
}

