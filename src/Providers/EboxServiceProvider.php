<?php

namespace Ebox\Enterprise\Providers;

use Illuminate\Support\ServiceProvider;
use Ebox\Enterprise\Core\Contracts\MessagingInterface;
use Ebox\Enterprise\Core\Contracts\RegistryInterface;
use Ebox\Enterprise\Core\Contracts\IdentityResolverInterface;
use Ebox\Enterprise\Services\Messaging\EboxMessagingService;
use Ebox\Enterprise\Services\Messaging\CentralRegistryService;
use Ebox\Enterprise\Services\Identity\IdentityResolverService;
use Ebox\Enterprise\Services\Audit\AuditLogger;

class EboxServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/ebox.php',
            'ebox'
        );
        
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/ebox-audit.php',
            'ebox-audit'
        );
        
        // Bindings des services
        $this->app->singleton(MessagingInterface::class, EboxMessagingService::class);
        $this->app->singleton(RegistryInterface::class, CentralRegistryService::class);
        $this->app->singleton(IdentityResolverInterface::class, IdentityResolverService::class);
        $this->app->singleton(AuditLogger::class);
        
        // Alias pour la facade
        $this->app->alias('ebox', \Ebox\Enterprise\Facades\Ebox::class);
    }
    
    public function boot(): void
    {
        // Publication des fichiers de configuration
        $this->publishes([
            __DIR__ . '/../../config/ebox.php' => config_path('ebox.php'),
            __DIR__ . '/../../config/ebox-audit.php' => config_path('ebox-audit.php'),
        ], 'ebox-config');
        
        // Publication des migrations
        $this->publishes([
            __DIR__ . '/../../database/migrations' => database_path('migrations'),
        ], 'ebox-migrations');
        
        // Publication des routes
        $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');
        
        // Enregistrement des middleware
        $this->app['router']->aliasMiddleware('ebox.identity', \Ebox\Enterprise\Http\Middleware\VerifyBelgianIdentity::class);
        $this->app['router']->aliasMiddleware('ebox.audit', \Ebox\Enterprise\Http\Middleware\AuditMessageAccess::class);
        $this->app['router']->aliasMiddleware('ebox.confidentiality', \Ebox\Enterprise\Http\Middleware\EnsureMessageConfidentiality::class);
    }
}

