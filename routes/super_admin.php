<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Super Admin Exclusive Guard Routes
|--------------------------------------------------------------------------
|
| Gated by 'role.super_admin'.
| Permitted: Super Admin only.
| Denied: All other roles including Org Admin (403 Forbidden).
|
*/

Route::prefix('super-admin')->name('super-admin.')->group(function (): void {
    // Reserved for Super Admin system health, tenant isolation diagnostics, and maintenance operations
    Route::get('/status', function () {
        return response()->json([
            'status' => 'healthy',
            'version' => config('crm.version', '1.0.1'),
            'timestamp' => now()->toIso8601String(),
        ]);
    })->name('status');
});
