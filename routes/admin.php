<?php

declare(strict_types=1);

use App\Http\Controllers\Pipelines\PipelineController;
use App\Http\Controllers\Pipelines\PipelineStageController;
use App\Http\Controllers\Settings\AuditLogController;
use App\Http\Controllers\Settings\SmtpController;
use App\Http\Controllers\Settings\StagesController;
use App\Http\Controllers\Settings\SystemSettingController;
use App\Http\Controllers\Settings\TeamController;
use App\Http\Controllers\Settings\UserController;
use App\Http\Controllers\Settings\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Org Admin & Super Admin Guard Routes
|--------------------------------------------------------------------------
|
| Gated by 'role.admin'.
| Permitted: Super Admin, Org Admin, Head of Sales.
| Denied: Sales Manager, Sales Rep, Read-Only Analyst (403 Forbidden).
|
*/

// Pipeline Structure & Stage Management
Route::post('/pipelines', [PipelineController::class, 'store'])->name('pipelines.store');
Route::post('/pipelines/{pipeline}/stages', [PipelineStageController::class, 'store'])->name('pipelines.stages.store');
Route::post('/pipelines/{pipeline}/stages/reorder', [PipelineStageController::class, 'reorder'])->name('pipelines.stages.reorder');
Route::delete('/pipelines/{pipeline}/stages/{stage}', [PipelineStageController::class, 'destroy'])->name('pipelines.stages.destroy');

// System Settings & Administrative Configuration
Route::prefix('settings')->name('settings.')->group(function (): void {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->middleware('can:users.manage')->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->middleware('can:users.manage')->name('users.update');
    Route::post('/users/{user}/reassign', [UserController::class, 'reassign'])->middleware('can:users.manage')->name('users.reassign');
    Route::post('/teams', [TeamController::class, 'store'])->middleware('can:teams.manage')->name('teams.store');

    Route::get('/stages', [StagesController::class, 'index'])->middleware('can:pipelines.manage')->name('stages.index');
    Route::post('/stages', [StagesController::class, 'store'])->middleware('can:pipelines.manage')->name('stages.store');

    Route::get('/smtp', [SmtpController::class, 'index'])->middleware('can:settings.manage')->name('smtp.index');
    Route::post('/smtp', [SmtpController::class, 'store'])->middleware('can:settings.manage')->name('smtp.store');
    Route::post('/smtp/test', [SmtpController::class, 'testEmail'])->middleware('can:settings.manage')->name('smtp.test');

    Route::get('/system', [SystemSettingController::class, 'edit'])->middleware('can:settings.manage')->name('system.edit');
    Route::put('/system', [SystemSettingController::class, 'update'])->middleware('can:settings.manage')->name('system.update');

    Route::middleware('can:webhooks.manage')->group(function (): void {
        Route::get('/webhooks', [WebhookController::class, 'index'])->name('webhooks.index');
        Route::post('/webhooks', [WebhookController::class, 'store'])->name('webhooks.store');
        Route::put('/webhooks/{webhook}', [WebhookController::class, 'update'])->name('webhooks.update');
        Route::delete('/webhooks/{webhook}', [WebhookController::class, 'destroy'])->name('webhooks.destroy');
    });

    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
});
