<?php

declare(strict_types=1);

use App\Http\Controllers\Activities\ActivityController;
use App\Http\Controllers\Attachments\AttachmentController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Companies\CompanyController;
use App\Http\Controllers\Contacts\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Deals\DealController;
use App\Http\Controllers\Deals\UrgentAttentionController;
use App\Http\Controllers\FollowUps\FollowUpController;
use App\Http\Controllers\Imports\ImportExportController;
use App\Http\Controllers\Leads\LeadController;
use App\Http\Controllers\Mobile\CaptureController as MobileCaptureController;
use App\Http\Controllers\Mobile\EventController as MobileEventController;
use App\Http\Controllers\Notifications\NotificationController;
use App\Http\Controllers\Pipelines\PipelineController;
use App\Http\Controllers\Proposals\ProposalController;
use App\Http\Controllers\Proposals\ProposalDocumentController;
use App\Http\Controllers\Quotations\QuotationController;
use App\Http\Controllers\Quotations\QuotationWorkflowController;
use App\Http\Controllers\Reports\ReportController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use App\Http\Controllers\Targets\TargetController;
use App\Http\Controllers\Tasks\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Shared Authenticated Guard Routes
|--------------------------------------------------------------------------
|
| Accessible to ALL authenticated users (Super Admin, Org Admin, Sales Manager,
| Sales Rep, Sales Executive, Read-Only Analyst, and Viewer).
|
*/

// Session Termination
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

// User Self-Service Profile & Security
Route::prefix('settings')->name('settings.')->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/security', [SecurityController::class, 'edit'])->name('security.edit');
    Route::put('/security', [SecurityController::class, 'update'])->name('security.update');
});

// Notifications
Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
Route::put('/notifications/preferences', [NotificationController::class, 'updatePreferences'])->name('notifications.preferences');

// Primary Workspace Dashboards & Reports
Route::get('/dashboard', DashboardController::class)->name('dashboard');
Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
Route::post('/exports', [ImportExportController::class, 'storeExport'])->name('exports.store');

// Leads Views
Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
Route::get('/leads/check-duplicates', [LeadController::class, 'checkDuplicates'])->name('leads.check-duplicates');
Route::get('/leads/export', [LeadController::class, 'export'])->name('leads.export');
Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('leads.show');

// Companies & Contacts Views
Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
Route::get('/companies/{company}', [CompanyController::class, 'show'])->name('companies.show');
Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
Route::get('/contacts/{contact}', [ContactController::class, 'show'])->name('contacts.show');

// Pipelines & Kanban Views
Route::get('/pipelines', [PipelineController::class, 'index'])->name('pipelines.index');
Route::get('/pipelines/kanban', [PipelineController::class, 'kanban'])->name('pipelines.kanban');
Route::get('/pipelines/{pipeline}/kanban', [PipelineController::class, 'kanban'])->name('pipelines.kanban.show');

// Deals & Rotting Alerts
Route::get('/urgent-attention', UrgentAttentionController::class)->name('urgent-attention.index');
Route::get('/deals', [DealController::class, 'index'])->name('deals.index');
Route::get('/deals/{deal}', [DealController::class, 'show'])->name('deals.show');

// Activities & Attachments
Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');
Route::get('/attachments/{attachment}', [AttachmentController::class, 'download'])->name('attachments.download');

// Tasks & Follow-Ups
Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
Route::get('/follow-ups', [FollowUpController::class, 'index'])->name('follow-ups.index');

// Quotations Views & Printable
Route::get('/quotations', [QuotationController::class, 'index'])->name('quotations.index');
Route::get('/quotations/{quotation}', [QuotationController::class, 'show'])->name('quotations.show');
Route::get('/quotations/{quotation}/print', [QuotationWorkflowController::class, 'print'])->name('quotations.print');

// Proposals Views & Document Download
Route::get('/proposals', [ProposalController::class, 'index'])->name('proposals.index');
Route::get('/proposals/{proposal}', [ProposalController::class, 'show'])->name('proposals.show');
Route::get('/proposals/documents/{document}/download', [ProposalDocumentController::class, 'download'])->name('proposals.documents.download');

// Targets & Mobile Views
Route::get('/targets', [TargetController::class, 'index'])->name('targets.index');
Route::get('/mobile/capture', [MobileCaptureController::class, 'show'])->name('mobile.capture');
Route::get('/mobile/events', [MobileEventController::class, 'index'])->name('mobile.events.index');
