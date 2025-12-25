<?php

namespace Ebox\Enterprise\Core\Contracts;

use Ebox\Enterprise\Models\EboxMessage;
use Ebox\Enterprise\Core\Enums\IntegrationProfile;

/**
 * Interface for e-Box messaging service
 */
interface MessagingInterface
{
    /**
     * Send a message according to e-Box integration profiles
     */
    public function sendMessage(array $messageData, IntegrationProfile $profile): EboxMessage;
    
    /**
     * Retrieve message status
     */
    public function getMessageStatus(string $messageId): array;
}

