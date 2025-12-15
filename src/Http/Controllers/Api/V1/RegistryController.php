<?php

namespace Ebox\Enterprise\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Ebox\Enterprise\Http\Requests\RegistrySetupRequest;
use Ebox\Enterprise\Models\MessageRegistry;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class RegistryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }
    
    /**
     * Liste des registres
     * GET /api/ebox/v1/registries
     */
    public function index()
    {
        $registries = MessageRegistry::orderBy('priority')->get();
        
        return response()->json([
            'success' => true,
            'data' => $registries,
        ]);
    }
    
    /**
     * Création d'un registre
     * POST /api/ebox/v1/registries
     */
    public function create(RegistrySetupRequest $request)
    {
        $registry = MessageRegistry::create($request->validated());
        
        return response()->json([
            'success' => true,
            'data' => $registry,
            'message' => 'Registre créé avec succès',
        ], Response::HTTP_CREATED);
    }
    
    /**
     * Mise à jour d'un registre
     * PUT /api/ebox/v1/registries/{id}
     */
    public function update(string $id, RegistrySetupRequest $request)
    {
        $registry = MessageRegistry::findOrFail($id);
        $registry->update($request->validated());
        
        return response()->json([
            'success' => true,
            'data' => $registry,
            'message' => 'Registre mis à jour',
        ]);
    }
    
    /**
     * Suppression d'un registre
     * DELETE /api/ebox/v1/registries/{id}
     */
    public function delete(string $id)
    {
        $registry = MessageRegistry::findOrFail($id);
        $registry->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Registre supprimé',
        ]);
    }
    
    /**
     * Test de connexion à un registre
     * POST /api/ebox/v1/registries/{id}/test
     */
    public function test(string $id)
    {
        $registry = MessageRegistry::findOrFail($id);
        
        try {
            $response = Http::timeout(10)->get($registry->endpoint_url . '/health', [
                'headers' => [
                    'X-API-Key' => $registry->api_key,
                ],
            ]);
            
            return response()->json([
                'success' => $response->successful(),
                'status' => $response->status(),
                'message' => $response->successful() 
                    ? 'Connexion au registre réussie' 
                    : 'Échec de la connexion au registre',
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}

