<?php

namespace Ebox\Enterprise\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Ebox\Enterprise\Services\Audit\AuditLogger;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware pour auditer l'accès aux messages
 */
class AuditMessageAccess
{
    private AuditLogger $auditLogger;
    
    public function __construct(AuditLogger $auditLogger)
    {
        $this->auditLogger = $auditLogger;
    }
    
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Audit après la requête si c'est un accès à un message
        if ($request->routeIs('ebox.messages.show') || $request->routeIs('ebox.messages.*')) {
            $messageId = $request->route('id');
            
            if ($messageId) {
                $message = \Ebox\Enterprise\Models\EboxMessage::find($messageId);
                
                if ($message) {
                    $this->auditLogger->logStatusCheck($message, [
                        'action' => 'message_access',
                        'method' => $request->method(),
                        'ip' => $request->ip(),
                    ]);
                }
            }
        }
        
        return $response;
    }
}

