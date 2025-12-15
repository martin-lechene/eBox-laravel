<?php

namespace Ebox\Enterprise\Database\Seeders;

use Illuminate\Database\Seeder;
use Ebox\Enterprise\Models\MessageRegistry;

class EboxConfigSeeder extends Seeder
{
    public function run(): void
    {
        // Registre central par défaut
        MessageRegistry::create([
            'name' => 'Registre Central e-Box',
            'type' => 'central',
            'endpoint_url' => config('ebox.registries.central.endpoint', 'https://api.eboxenterprise.be/v1'),
            'api_key' => config('ebox.registries.central.api_key'),
            'api_secret' => config('ebox.registries.central.api_secret'),
            'supports_high_confidentiality' => false,
            'supports_private_registry' => false,
            'description' => 'Registre de messages centralisé e-Box',
            'is_active' => true,
            'priority' => 1,
        ]);
        
        // Registre privé si configuré
        if (config('ebox.registries.private.enabled', false)) {
            MessageRegistry::create([
                'name' => 'Registre Privé',
                'type' => 'private',
                'endpoint_url' => config('ebox.registries.private.endpoint'),
                'api_key' => config('ebox.registries.private.api_key'),
                'api_secret' => config('ebox.registries.private.api_secret'),
                'supports_high_confidentiality' => true,
                'supports_private_registry' => true,
                'description' => 'Registre de messages privé pour confidentialité maximale',
                'is_active' => true,
                'priority' => 1,
            ]);
        }
    }
}

