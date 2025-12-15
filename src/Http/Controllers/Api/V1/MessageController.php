<?php

namespace Ebox\Enterprise\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Ebox\Enterprise\Http\Requests\SendMessageRequest;
use Ebox\Enterprise\Services\Messaging\EboxMessagingService;
use Ebox\Enterprise\Core\Enums\IntegrationProfile;
use Ebox\Enterprise\Http\Resources\MessageResource;
use Ebox\Enterprise\Models\EboxMessage;
use Symfony\Component\HttpFoundation\Response;

class MessageController extends Controller
{
    private EboxMessagingService $messagingService;
    
    public function __construct(EboxMessagingService $messagingService)
    {
        $this->messagingService = $messagingService;
        $this->middleware('auth:sanctum');
        $this->middleware('ebox.identity');
    }
    
    /**
     * Envoi d'un message via e-Box
     * POST /api/ebox/v1/messages
     */
    public function send(SendMessageRequest $request)
    {
        try {
            $profile = IntegrationProfile::from($request->input('integration_profile', 'central'));
            
            $message = $this->messagingService->sendMessage(
                $request->validated(),
                $profile
            );
            
            return response()->json([
                'success' => true,
                'data' => new MessageResource($message),
                'message' => 'Message envoyé avec succès via e-Box',
            ], Response::HTTP_CREATED);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * Liste des messages envoyés
     * GET /api/ebox/v1/messages
     */
    public function index(Request $request)
    {
        $query = EboxMessage::query();
        
        // Filtrage par expéditeur/destinataire
        if ($request->has('sender_identifier')) {
            $query->where('sender_identifier', $request->input('sender_identifier'));
        }
        
        if ($request->has('recipient_identifier')) {
            $query->where('recipient_identifier', $request->input('recipient_identifier'));
        }
        
        // Filtrage par statut
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }
        
        // Filtrage par période
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }
        
        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }
        
        $messages = $query->orderBy('created_at', 'desc')
                         ->paginate($request->input('per_page', 20));
        
        return MessageResource::collection($messages);
    }
    
    /**
     * Détails d'un message
     * GET /api/ebox/v1/messages/{id}
     */
    public function show(string $id)
    {
        $message = EboxMessage::findOrFail($id);
        
        // Vérification des permissions
        $this->authorize('view', $message);
        
        return new MessageResource($message);
    }
    
    /**
     * Réessayer l'envoi d'un message échoué
     * PATCH /api/ebox/v1/messages/{id}/retry
     */
    public function retry(string $id)
    {
        $message = EboxMessage::findOrFail($id);
        
        if ($message->status->value !== 'failed') {
            return response()->json([
                'success' => false,
                'error' => 'Seuls les messages échoués peuvent être réessayés',
            ], Response::HTTP_BAD_REQUEST);
        }
        
        // Dispatch du job de retry
        \Ebox\Enterprise\Jobs\ProcessMessageDelivery::dispatch($message->id);
        
        return response()->json([
            'success' => true,
            'message' => 'Réessai de l\'envoi programmé',
        ]);
    }
    
    /**
     * Suppression d'un message
     * DELETE /api/ebox/v1/messages/{id}
     */
    public function delete(string $id)
    {
        $message = EboxMessage::findOrFail($id);
        $message->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Message supprimé',
        ]);
    }
}

