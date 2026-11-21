<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Leads\LeadCaptureController;
use App\Http\Controllers\Quotations\PublicQuotationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public & Guest Guard Routes
|--------------------------------------------------------------------------
|
| Handled by 'guest' middleware and cryptographic URL signature validation.
|
*/

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');

    Route::get('/two-factor', [TwoFactorChallengeController::class, 'create'])->name('two-factor.challenge');
    Route::post('/two-factor', [TwoFactorChallengeController::class, 'store'])->name('two-factor.store');
    Route::post('/two-factor/resend', [TwoFactorChallengeController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('two-factor.resend');
    Route::post('/two-factor/cancel', [TwoFactorChallengeController::class, 'cancel'])->name('two-factor.cancel');

    Route::get('/capture/lead', [LeadCaptureController::class, 'show'])
        ->middleware('throttle:30,1')
        ->name('leads.capture.show');
    Route::post('/capture/lead', [LeadCaptureController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('leads.capture.store');
});

// Public, signed quotation client links (no login required)
Route::get('/q/{quotation:ulid}', [PublicQuotationController::class, 'show'])
    ->middleware('signed')
    ->name('quotations.public.show');
Route::post('/q/{quotation:ulid}/accept', [PublicQuotationController::class, 'accept'])
    ->middleware(['signed', 'throttle:10,1'])
    ->name('quotations.public.accept');
Route::post('/q/{quotation:ulid}/reject', [PublicQuotationController::class, 'reject'])
    ->middleware(['signed', 'throttle:10,1'])
    ->name('quotations.public.reject');

// Public Marketing Landing Page (Root `/`) & Welcome Showcase
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    if ((bool) config('crm.demo_mode', false)) {
        return view('pages.landing');
    }

    return redirect()->route('login');
})->name('home');

Route::get('/welcome', function () {
    return view('pages.landing');
})->name('landing');
