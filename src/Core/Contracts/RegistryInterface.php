<?php

namespace Ebox\Enterprise\Core\Contracts;

use Ebox\Enterprise\Models\MessageRegistry;

/**
 * Interface pour les services de registre e-Box
 */
interface RegistryInterface
{
    /**
     * Sélection d'un registre selon le profil d'intégration
     */
    public function selectRegistry(string $profile, ?string $confidentialityLevel = null): MessageRegistry;
    
    /**
     * Envoi d'un message au registre
     */
    public function sendToRegistry(\Ebox\Enterprise\Models\EboxMessage $message, MessageRegistry $registry): string;
    
    /**
     * Récupération du statut depuis le registre
     */
    public function fetchStatusFromRegistry(\Ebox\Enterprise\Models\EboxMessage $message, MessageRegistry $registry): array;
}

