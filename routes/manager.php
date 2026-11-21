<?php

declare(strict_types=1);

use App\Http\Controllers\Imports\BulkImportController;
use App\Http\Controllers\Imports\ImportExportController;
use App\Http\Controllers\Quotations\QuotationWorkflowController;
use App\Http\Controllers\Targets\TargetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Sales Manager Guard Routes
|--------------------------------------------------------------------------
|
| Gated by 'role.manager'.
| Permitted: Super Admin, Org Admin, Head of Sales, Sales Manager.
| Denied: Sales Rep, Sales Executive, Read-Only Analyst, Viewer (403 Forbidden).
|
*/

// High-Discount Quotation Approvals
Route::post('/quotations/{quotation}/approve-discount', [QuotationWorkflowController::class, 'approveDiscount'])
    ->name('quotations.approve-discount');

// Bulk CSV Data Imports
Route::get('/imports', [ImportExportController::class, 'index'])->name('imports.index');
Route::get('/imports/bulk', [BulkImportController::class, 'index'])->name('imports.bulk');
Route::post('/imports/bulk', [BulkImportController::class, 'store'])->name('imports.bulk.store');
Route::post('/imports', [ImportExportController::class, 'storeImport'])->name('imports.store');
Route::get('/imports/{job}/download', [ImportExportController::class, 'download'])->name('imports.download');

// Sales Targets & Commission Configuration
Route::post('/targets', [TargetController::class, 'store'])->name('targets.store');
Route::post('/targets/commissions', [TargetController::class, 'storeCommission'])->name('targets.commissions.store');
