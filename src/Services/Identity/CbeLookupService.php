<?php

namespace Ebox\Enterprise\Services\Identity;

use Ebox\Enterprise\Models\IdentityMapping;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

/**
 * CBE (Crossroads Bank for Enterprises) lookup service
 */
class CbeLookupService
{
    private string $cbeApiEndpoint;
    
    public function __construct()
    {
        $this->cbeApiEndpoint = config('ebox.cbe_api_endpoint', 'https://api.cbe.be/v1');
    }
    
    /**
     * Lookup an enterprise by CBE number
     */
    public function lookup(string $cbeNumber): ?array
    {
        return Cache::remember(
            "cbe_lookup_{$cbeNumber}",
            config('ebox.cache.identity_ttl', 86400),
            function () use ($cbeNumber) {
                try {
                    $response = Http::timeout(10)->get(
                        "{$this->cbeApiEndpoint}/enterprises/{$cbeNumber}"
                    );
                    
                    if ($response->successful()) {
                        $data = $response->json();
                        
                        // Update local cache
                        IdentityMapping::updateOrCreate(
                            [
                                'identifier' => $cbeNumber,
                                'type' => 'CBE',
                            ],
                            [
                                'name' => $data['name'] ?? null,
                                'cached_data' => $data,
                                'last_verified_at' => now(),
                            ]
                        );
                        
                        return $data;
                    }
                } catch (\Exception $e) {
                    \Log::warning("CBE lookup error", [
                        'cbe' => $cbeNumber,
                        'error' => $e->getMessage(),
                    ]);
                }
                
                return null;
            }
        );
    }
}

