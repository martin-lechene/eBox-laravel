<?php

namespace Ebox\Enterprise\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Ebox\Enterprise\Core\Enums\ConfidentialityLevel;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to verify message confidentiality
 */
class EnsureMessageConfidentiality
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if this is a message read request
        if ($request->routeIs('ebox.messages.show')) {
            $messageId = $request->route('id');
            
            if ($messageId) {
                $message = \Ebox\Enterprise\Models\EboxMessage::find($messageId);
                
                if ($message && $message->confidentiality_level === ConfidentialityLevel::MAXIMUM) {
                    // Additional verification for maximum confidentiality
                    $user = $request->user();
                    
                    // Verify that the user is either the sender or the recipient
                    $isAuthorized = 
                        ($user->belgian_identity === $message->sender_identifier) ||
                        ($user->belgian_identity === $message->recipient_identifier);
                    
                    if (!$isAuthorized) {
                        return response()->json([
                            'error' => 'Unauthorized access',
                            'message' => 'This message requires special authorization',
                        ], 403);
                    }
                }
            }
        }
        
        return $next($request);
    }
}

