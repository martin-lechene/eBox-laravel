<?php

namespace Ebox\Enterprise\Listeners;

use Ebox\Enterprise\Events\MessageDelivered;
use Ebox\Enterprise\Events\MessageRead;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class NotifyMessageStatus
{
    public function handleDelivered(MessageDelivered $event): void
    {
        // Envoi de notification si configuré
        if (config('ebox.webhooks.enabled', true)) {
            // Log pour webhook
            Log::info("Message délivré", [
                'message_id' => $event->message->id,
                'external_id' => $event->message->external_message_id,
            ]);
        }
    }
    
    public function handleRead(MessageRead $event): void
    {
        // Envoi de notification si configuré
        if (config('ebox.webhooks.enabled', true)) {
            Log::info("Message lu", [
                'message_id' => $event->message->id,
                'external_id' => $event->message->external_message_id,
            ]);
        }
    }
}

