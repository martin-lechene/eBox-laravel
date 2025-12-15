<?php

namespace Ebox\Enterprise\Core\Contracts;

use Ebox\Enterprise\Models\EboxMessage;
use Ebox\Enterprise\Core\Enums\IntegrationProfile;

/**
 * Interface pour le service de messagerie e-Box
 */
interface MessagingInterface
{
    /**
     * Envoi d'un message selon les profils d'intégration e-Box
     */
    public function sendMessage(array $messageData, IntegrationProfile $profile): EboxMessage;
    
    /**
     * Récupération du statut d'un message
     */
    public function getMessageStatus(string $messageId): array;
}

