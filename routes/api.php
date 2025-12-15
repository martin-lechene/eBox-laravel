<?php

use Illuminate\Support\Facades\Route;
use Ebox\Enterprise\Http\Controllers\Api\V1\{
    MessageController,
    StatusController,
    RegistryController
};

/*
|--------------------------------------------------------------------------
| API Routes for e-Box Enterprise
|--------------------------------------------------------------------------
|
| Conforme à la documentation technique de dev.eboxenterprise.be
|
*/

Route::prefix('v1')->middleware(config('ebox.routes.middleware'))->group(function () {
    
    // Messages
    Route::prefix('messages')->group(function () {
        Route::post('/', [MessageController::class, 'send'])->name('ebox.messages.send');
        Route::get('/', [MessageController::class, 'index'])->name('ebox.messages.index');
        Route::get('/{id}', [MessageController::class, 'show'])->name('ebox.messages.show');
        Route::patch('/{id}/retry', [MessageController::class, 'retry'])->name('ebox.messages.retry');
        Route::delete('/{id}', [MessageController::class, 'delete'])->name('ebox.messages.delete');
    });
    
    // Statuts (audit)
    Route::prefix('status')->group(function () {
        Route::get('/{messageId}', [StatusController::class, 'getStatus'])->name('ebox.status.get');
        Route::get('/{messageId}/history', [StatusController::class, 'getHistory'])->name('ebox.status.history');
        Route::get('/{messageId}/audit', [StatusController::class, 'getAudit'])->name('ebox.status.audit');
    });
    
    // Registres
    Route::prefix('registries')->group(function () {
        Route::get('/', [RegistryController::class, 'index'])->name('ebox.registries.index');
        Route::post('/', [RegistryController::class, 'create'])->name('ebox.registries.create');
        Route::put('/{id}', [RegistryController::class, 'update'])->name('ebox.registries.update');
        Route::delete('/{id}', [RegistryController::class, 'delete'])->name('ebox.registries.delete');
        Route::post('/{id}/test', [RegistryController::class, 'test'])->name('ebox.registries.test');
    });
    
    // Webhooks pour les notifications e-Box
    Route::prefix('webhooks')->withoutMiddleware(['auth:sanctum'])->group(function () {
        Route::post('/ebox/callback', [\Ebox\Enterprise\Http\Controllers\WebhookController::class, 'handle'])
            ->name('ebox.webhooks.callback');
    });
    
    // Santé du service
    Route::get('/health', function () {
        return response()->json([
            'status' => 'healthy',
            'version' => config('ebox.version'),
            'timestamp' => now()->toISOString(),
        ]);
    })->name('ebox.health');
});

