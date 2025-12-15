<?php

namespace Ebox\Enterprise\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Ebox\Enterprise\Models\EboxMessage;
use Ebox\Enterprise\Core\Enums\MessageStatus;
use Ebox\Enterprise\Events\MessageDelivered;
use Ebox\Enterprise\Events\MessageRead;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller pour les webhooks e-Box
 */
class WebhookController extends Controller
{
    public function __construct()
    {
        // Pas d'authentification standard pour les webhooks
        // Vérification par signature à implémenter
    }
    
    /**
     * Gestion des callbacks e-Box
     * POST /api/ebox/webhooks/ebox/callback
     */
    public function handle(Request $request)
    {
        // Vérification de la signature du webhook
        if (!$this->verifySignature($request)) {
            return response()->json([
                'error' => 'Signature invalide',
            ], Response::HTTP_UNAUTHORIZED);
        }
        
        $event = $request->input('event');
        $data = $request->input('data');
        
        try {
            switch ($event) {
                case 'message.delivered':
                    $this->handleMessageDelivered($data);
                    break;
                    
                case 'message.read':
                    $this->handleMessageRead($data);
                    break;
                    
                case 'message.failed':
                    $this->handleMessageFailed($data);
                    break;
                    
                case 'status.updated':
                    $this->handleStatusUpdated($data);
                    break;
                    
                default:
                    Log::warning("Événement webhook inconnu", ['event' => $event]);
            }
            
            return response()->json([
                'success' => true,
            ]);
            
        } catch (\Exception $e) {
            Log::error("Erreur lors du traitement du webhook", [
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'error' => 'Erreur lors du traitement',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    private function handleMessageDelivered(array $data): void
    {
        $message = EboxMessage::where('external_message_id', $data['message_id'])
            ->first();
        
        if ($message) {
            $message->markAsDelivered();
            event(new MessageDelivered($message));
        }
    }
    
    private function handleMessageRead(array $data): void
    {
        $message = EboxMessage::where('external_message_id', $data['message_id'])
            ->first();
        
        if ($message) {
            $message->markAsRead();
            event(new MessageRead($message));
        }
    }
    
    private function handleMessageFailed(array $data): void
    {
        $message = EboxMessage::where('external_message_id', $data['message_id'])
            ->first();
        
        if ($message) {
            $message->update([
                'status' => MessageStatus::FAILED,
                'status_updated_at' => now(),
            ]);
        }
    }
    
    private function handleStatusUpdated(array $data): void
    {
        $message = EboxMessage::where('external_message_id', $data['message_id'])
            ->first();
        
        if ($message && isset($data['status'])) {
            $message->update([
                'status' => MessageStatus::from($data['status']),
                'status_updated_at' => now(),
            ]);
        }
    }
    
    private function verifySignature(Request $request): bool
    {
        $secret = config('ebox.webhooks.secret');
        
        if (!$secret) {
            return true; // Pas de vérification si pas de secret configuré
        }
        
        $signature = $request->header('X-Ebox-Signature');
        $payload = $request->getContent();
        
        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        
        return hash_equals($expectedSignature, $signature);
    }
}

