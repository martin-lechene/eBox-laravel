<?php

namespace Ebox\Enterprise\Jobs;

use Ebox\Enterprise\Services\Messaging\EboxMessagingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncMessageStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;

    public $timeout = 60;

    private string $messageId;

    public function __construct(string $messageId)
    {
        $this->messageId = $messageId;
    }

    public function handle(EboxMessagingService $messagingService): void
    {
        try {
            $messagingService->getMessageStatus($this->messageId);
        } catch (\Exception $e) {
            Log::warning('Échec de la synchronisation du statut', [
                'message_id' => $this->messageId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
