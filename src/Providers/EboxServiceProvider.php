<?php

namespace Ebox\Enterprise\Providers;

use Ebox\Enterprise\As4\Contracts\As4ClientInterface;
use Ebox\Enterprise\As4\EboxAs4Client;
use Ebox\Enterprise\Core\Contracts\IdentityResolverInterface;
use Ebox\Enterprise\Core\Contracts\MessagingInterface;
use Ebox\Enterprise\Core\Contracts\RegistryInterface;
use Ebox\Enterprise\Facades\Ebox;
use Ebox\Enterprise\Http\Middleware\AuditMessageAccess;
use Ebox\Enterprise\Http\Middleware\EnsureMessageConfidentiality;
use Ebox\Enterprise\Http\Middleware\VerifyBelgianIdentity;
use Ebox\Enterprise\Services\Audit\AuditLogger;
use Ebox\Enterprise\Services\Identity\IdentityResolverService;
use Ebox\Enterprise\Services\Messaging\CentralRegistryService;
use Ebox\Enterprise\Services\Messaging\EboxMessagingService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class EboxServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Configuration
        $this->mergeConfigFrom(
            __DIR__.'/../../config/ebox.php',
            'ebox'
        );

        $this->mergeConfigFrom(
            __DIR__.'/../../config/ebox-audit.php',
            'ebox-audit'
        );

        // Bindings des services
        $this->app->singleton(MessagingInterface::class, EboxMessagingService::class);
        $this->app->singleton(RegistryInterface::class, CentralRegistryService::class);
        $this->app->singleton(IdentityResolverInterface::class, IdentityResolverService::class);
        $this->app->singleton(AuditLogger::class);

        // AS4/ebMS3 push transport
        $this->app->singleton(As4ClientInterface::class, function (): EboxAs4Client {
            return EboxAs4Client::fromConfig(config('ebox.as4'));
        });
        $this->app->alias(As4ClientInterface::class, 'ebox.as4');

        // Alias pour la facade
        $this->app->alias('ebox', Ebox::class);
    }

    public function boot(): void
    {
        // Publication des fichiers de configuration
        $this->publishes([
            __DIR__.'/../../config/ebox.php' => config_path('ebox.php'),
            __DIR__.'/../../config/ebox-audit.php' => config_path('ebox-audit.php'),
        ], 'ebox-config');

        // Publication des migrations
        $this->publishes([
            __DIR__.'/../../database/migrations' => database_path('migrations'),
        ], 'ebox-migrations');

        // API routes (prefix configurable through ebox.routes.prefix)
        Route::prefix(config('ebox.routes.prefix', 'api/ebox'))->group(function (): void {
            $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
        });

        // Enregistrement des middleware
        $this->app['router']->aliasMiddleware('ebox.identity', VerifyBelgianIdentity::class);
        $this->app['router']->aliasMiddleware('ebox.audit', AuditMessageAccess::class);
        $this->app['router']->aliasMiddleware('ebox.confidentiality', EnsureMessageConfidentiality::class);
    }
}
