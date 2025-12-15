<?php

namespace Ebox\Enterprise\Services\Messaging;

use Ebox\Enterprise\Core\Contracts\RegistryInterface;
use Ebox\Enterprise\Models\MessageRegistry;
use Ebox\Enterprise\Models\EboxMessage;
use Ebox\Enterprise\Core\Exceptions\RegistryNotFoundException;
use Ebox\Enterprise\Core\Exceptions\MessageDeliveryException;
use GuzzleHttp\Exception\RequestException;

/**
 * Service pour le registre central e-Box
 */
class CentralRegistryService implements RegistryInterface
{
    public function selectRegistry(string $profile, ?string $confidentialityLevel = null): MessageRegistry
    {
        $query = MessageRegistry::active()->where('type', 'central');
        
        $registry = $query->orderBy('priority')->first();
        
        if (!$registry) {
            throw new \Ebox\Enterprise\Core\Exceptions\RegistryNotFoundException(
                "Aucun registre central disponible"
            );
        }
        
        return $registry;
    }
    
    public function sendToRegistry(EboxMessage $message, MessageRegistry $registry): string
    {
        $client = new \GuzzleHttp\Client(['timeout' => 30]);
        
        $payload = [
            'sender' => [
                'identifier' => $message->sender_identifier,
                'type' => $message->sender_type,
                'name' => $message->sender_name,
            ],
            'recipient' => [
                'identifier' => $message->recipient_identifier,
                'type' => $message->recipient_type,
                'name' => $message->recipient_name,
            ],
            'message' => [
                'subject' => $message->subject,
                'body' => $message->body,
                'type' => $message->message_type,
                'confidentiality' => $message->confidentiality_level->value,
            ],
        ];
        
        $headers = ['Content-Type' => 'application/json'];
        if ($registry->api_key) {
            $headers['X-API-Key'] = $registry->api_key;
        }
        
        try {
            $response = $client->post($registry->endpoint_url, [
                'headers' => $headers,
                'json' => $payload,
                'http_errors' => false,
            ]);
            
            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody()->getContents(), true);
            
            if ($statusCode >= 200 && $statusCode < 300) {
                return $body['message_id'] ?? $body['id'] ?? uniqid('ebox_', true);
            }
            
            throw new MessageDeliveryException(
                "Échec de l'envoi au registre: " . ($body['error'] ?? 'Unknown error'),
                $statusCode
            );
        } catch (RequestException $e) {
            throw new MessageDeliveryException(
                "Erreur réseau lors de l'envoi: " . $e->getMessage(),
                $e->getCode() ?: 500,
                $e
            );
        }
    }
    
    public function fetchStatusFromRegistry(EboxMessage $message, MessageRegistry $registry): array
    {
        $client = new \GuzzleHttp\Client(['timeout' => 10]);
        
        $headers = [];
        if ($registry->api_key) {
            $headers['X-API-Key'] = $registry->api_key;
        }
        
        $response = $client->get(
            "{$registry->endpoint_url}/messages/{$message->external_message_id}/status",
            ['headers' => $headers]
        );
        
        return json_decode($response->getBody()->getContents(), true);
    }
}

