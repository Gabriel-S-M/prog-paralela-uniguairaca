<?php

use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::post('/webhook', [WebhookController::class, 'handle']);

Route::prefix('v1')->group(function () {
    Route::prefix('webhooks')->group(function () {
        Route::post('crm', [WebhookController::class, 'crm']);
    });
});
