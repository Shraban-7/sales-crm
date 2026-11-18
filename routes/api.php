<?php

declare(strict_types=1);

use App\Http\Controllers\Leads\LeadWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/leads/capture', LeadWebhookController::class)
    ->middleware('throttle:60,1')
    ->name('leads.capture.webhook');
