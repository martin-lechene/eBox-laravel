<?php

namespace Ebox\Enterprise\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Ebox\Enterprise\Core\ValueObjects\BelgianIdentity;
use Ebox\Enterprise\Core\Enums\IdentityType;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware pour vérifier l'authentification forte via CBE/NRN
 * Conforme à la documentation e-Box
 */
class VerifyBelgianIdentity
{
    public function handle(Request $request, Closure $next): Response
    {
        // Récupération de l'identité depuis le token d'authentification
        $user = $request->user();
        
        if (!$user || !$user->belgian_identity) {
            return response()->json([
                'error' => 'Authentification forte requise',
                'message' => 'Une identité belge (CBE/NRN) est requise pour utiliser e-Box',
            ], 401);
        }
        
        // Validation de l'identité
        try {
            $identity = new BelgianIdentity(
                $user->belgian_identity,
                $user->identity_type === 'company' ? IdentityType::CBE : IdentityType::NRN,
                $user->name
            );
            
            // Ajout de l'identité au request pour utilisation ultérieure
            $request->attributes->set('belgian_identity', $identity);
            
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'error' => 'Identité invalide',
                'message' => $e->getMessage(),
            ], 400);
        }
        
        return $next($request);
    }
}

