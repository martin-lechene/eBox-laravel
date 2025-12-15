<?php

namespace Ebox\Enterprise\Listeners;

use Ebox\Enterprise\Events\MessageSent;
use Ebox\Enterprise\Events\MessageDelivered;
use Ebox\Enterprise\Events\MessageRead;

class UpdateMessageTimeline
{
    public function handleSent(MessageSent $event): void
    {
        // Mise à jour de la timeline si nécessaire
        // Par exemple, mise à jour d'un cache ou d'une table de timeline
    }
    
    public function handleDelivered(MessageDelivered $event): void
    {
        // Mise à jour de la timeline
    }
    
    public function handleRead(MessageRead $event): void
    {
        // Mise à jour de la timeline
    }
}

