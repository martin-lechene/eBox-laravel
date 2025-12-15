<?php

namespace Ebox\Enterprise\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Ebox\Enterprise\Services\Messaging\EboxMessagingService;
use Ebox\Enterprise\Services\Messaging\MessageStatusService;
use Ebox\Enterprise\Http\Resources\StatusResource;
use Ebox\Enterprise\Models\EboxMessage;
use Symfony\Component\HttpFoundation\Response;

class StatusController extends Controller
{
    private EboxMessagingService $messagingService;
    private MessageStatusService $statusService;
    
    public function __construct(
        EboxMessagingService $messagingService,
        MessageStatusService $statusService
    ) {
        $this->messagingService = $messagingService;
        $this->statusService = $statusService;
        $this->middleware('auth:sanctum');
    }
    
    /**
     * Récupération du statut d'un message
     * GET /api/ebox/v1/status/{messageId}
     */
    public function getStatus(string $messageId)
    {
        try {
            $status = $this->messagingService->getMessageStatus($messageId);
            
            return response()->json([
                'success' => true,
                'data' => new StatusResource($status),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }
    }
    
    /**
     * Historique des statuts d'un message
     * GET /api/ebox/v1/status/{messageId}/history
     */
    public function getHistory(string $messageId)
    {
        $message = EboxMessage::where('external_message_id', $messageId)
            ->orWhere('id', $messageId)
            ->firstOrFail();
        
        $history = $this->statusService->getStatusHistory($message);
        
        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }
    
    /**
     * Logs d'audit d'un message
     * GET /api/ebox/v1/status/{messageId}/audit
     */
    public function getAudit(string $messageId, Request $request)
    {
        $message = EboxMessage::where('external_message_id', $messageId)
            ->orWhere('id', $messageId)
            ->firstOrFail();
        
        $logs = $message->auditLogs()
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 20));
        
        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }
}

