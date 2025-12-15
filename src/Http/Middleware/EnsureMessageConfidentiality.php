<?php

namespace Ebox\Enterprise\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Ebox\Enterprise\Core\Enums\ConfidentialityLevel;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware pour vérifier la confidentialité des messages
 */
class EnsureMessageConfidentiality
{
    public function handle(Request $request, Closure $next): Response
    {
        // Vérification si c'est une requête de lecture de message
        if ($request->routeIs('ebox.messages.show')) {
            $messageId = $request->route('id');
            
            if ($messageId) {
                $message = \Ebox\Enterprise\Models\EboxMessage::find($messageId);
                
                if ($message && $message->confidentiality_level === ConfidentialityLevel::MAXIMUM) {
                    // Vérification supplémentaire pour confidentialité maximale
                    $user = $request->user();
                    
                    // Vérifier que l'utilisateur est soit l'expéditeur soit le destinataire
                    $isAuthorized = 
                        ($user->belgian_identity === $message->sender_identifier) ||
                        ($user->belgian_identity === $message->recipient_identifier);
                    
                    if (!$isAuthorized) {
                        return response()->json([
                            'error' => 'Accès non autorisé',
                            'message' => 'Ce message nécessite une autorisation spéciale',
                        ], 403);
                    }
                }
            }
        }
        
        return $next($request);
    }
}

