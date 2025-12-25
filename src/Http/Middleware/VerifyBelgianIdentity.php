<?php

namespace Ebox\Enterprise\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Ebox\Enterprise\Core\ValueObjects\BelgianIdentity;
use Ebox\Enterprise\Core\Enums\IdentityType;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to verify strong authentication via CBE/NRN
 * Compliant with e-Box documentation
 */
class VerifyBelgianIdentity
{
    public function handle(Request $request, Closure $next): Response
    {
        // Retrieve identity from authentication token
        $user = $request->user();
        
        if (!$user || !$user->belgian_identity) {
            return response()->json([
                'error' => 'Strong authentication required',
                'message' => 'A Belgian identity (CBE/NRN) is required to use e-Box',
            ], 401);
        }
        
        // Validate identity
        try {
            $identity = new BelgianIdentity(
                $user->belgian_identity,
                $user->identity_type === 'company' ? IdentityType::CBE : IdentityType::NRN,
                $user->name
            );
            
            // Add identity to request for later use
            $request->attributes->set('belgian_identity', $identity);
            
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'error' => 'Invalid identity',
                'message' => $e->getMessage(),
            ], 400);
        }
        
        return $next($request);
    }
}

