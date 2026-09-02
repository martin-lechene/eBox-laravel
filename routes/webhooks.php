<?php

use Ebox\Enterprise\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Webhook Routes for e-Box Enterprise
|--------------------------------------------------------------------------
|
| Routes for e-Box webhooks
|
*/

Route::post('/ebox/callback', [WebhookController::class, 'handle'])
    ->name('ebox.webhooks.callback');
