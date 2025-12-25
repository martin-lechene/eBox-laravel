<?php

namespace Ebox\Enterprise\Database\Seeders;

use Illuminate\Database\Seeder;
use Ebox\Enterprise\Models\MessageRegistry;

class EboxConfigSeeder extends Seeder
{
    public function run(): void
    {
        // Default central registry
        MessageRegistry::create([
            'name' => 'e-Box Central Registry',
            'type' => 'central',
            'endpoint_url' => config('ebox.registries.central.endpoint', 'https://api.eboxenterprise.be/v1'),
            'api_key' => config('ebox.registries.central.api_key'),
            'api_secret' => config('ebox.registries.central.api_secret'),
            'supports_high_confidentiality' => false,
            'supports_private_registry' => false,
            'description' => 'Centralized e-Box message registry',
            'is_active' => true,
            'priority' => 1,
        ]);
        
        // Private registry if configured
        if (config('ebox.registries.private.enabled', false)) {
            MessageRegistry::create([
                'name' => 'Private Registry',
                'type' => 'private',
                'endpoint_url' => config('ebox.registries.private.endpoint'),
                'api_key' => config('ebox.registries.private.api_key'),
                'api_secret' => config('ebox.registries.private.api_secret'),
                'supports_high_confidentiality' => true,
                'supports_private_registry' => true,
                'description' => 'Private message registry for maximum confidentiality',
                'is_active' => true,
                'priority' => 1,
            ]);
        }
    }
}

