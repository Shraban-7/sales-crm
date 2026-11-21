<?php

declare(strict_types=1);

use App\Http\Controllers\Leads\LeadWebhookController;
use App\Http\Middleware\VerifyWebhookSignature;
use Illuminate\Support\Facades\Route;

// Inbound lead capture endpoint: protected by HMAC-SHA256 signature verification and configurable rate limiting
Route::post('/leads/capture', LeadWebhookController::class)
    ->middleware(['throttle:lead-webhook', VerifyWebhookSignature::class])
    ->name('leads.capture.webhook');
