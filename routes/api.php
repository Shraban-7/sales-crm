<?php

declare(strict_types=1);

use App\Http\Controllers\Leads\LeadWebhookController;
use App\Http\Middleware\VerifyWebhookSignature;
use Illuminate\Support\Facades\Route;

// Inbound lead capture endpoint: protected by HMAC-SHA256 signature verification and rate limiting
Route::post('/leads/capture', LeadWebhookController::class)
    ->middleware(['throttle:60,1', VerifyWebhookSignature::class])
    ->name('leads.capture.webhook');
