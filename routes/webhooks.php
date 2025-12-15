<?php

use Illuminate\Support\Facades\Route;
use Ebox\Enterprise\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| Webhook Routes for e-Box Enterprise
|--------------------------------------------------------------------------
|
| Routes pour les webhooks e-Box
|
*/

Route::post('/ebox/callback', [WebhookController::class, 'handle'])
    ->name('ebox.webhooks.callback');

