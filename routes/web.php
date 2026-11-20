<?php

declare(strict_types=1);

use App\Http\Controllers\Activities\ActivityController;
use App\Http\Controllers\Attachments\AttachmentController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Companies\CompanyController;
use App\Http\Controllers\Contacts\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Deals\DealController;
use App\Http\Controllers\Deals\MoveDealStageController;
use App\Http\Controllers\Deals\UrgentAttentionController;
use App\Http\Controllers\FollowUps\FollowUpController;
use App\Http\Controllers\Imports\BulkImportController;
use App\Http\Controllers\Imports\ImportExportController;
use App\Http\Controllers\Leads\ConvertLeadController;
use App\Http\Controllers\Leads\LeadCaptureController;
use App\Http\Controllers\Leads\LeadController;
use App\Http\Controllers\Mobile\CaptureController as MobileCaptureController;
use App\Http\Controllers\Mobile\EventController as MobileEventController;
use App\Http\Controllers\Notifications\NotificationController;
use App\Http\Controllers\Pipelines\PipelineController;
use App\Http\Controllers\Pipelines\PipelineStageController;
use App\Http\Controllers\Proposals\ProposalController;
use App\Http\Controllers\Proposals\ProposalDocumentController;
use App\Http\Controllers\Quotations\PublicQuotationController;
use App\Http\Controllers\Quotations\QuotationController;
use App\Http\Controllers\Quotations\QuotationWorkflowController;
use App\Http\Controllers\Reports\ReportController;
use App\Http\Controllers\Settings\AuditLogController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use App\Http\Controllers\Settings\SmtpController;
use App\Http\Controllers\Settings\StagesController;
use App\Http\Controllers\Settings\SystemSettingController;
use App\Http\Controllers\Settings\TeamController;
use App\Http\Controllers\Settings\UserController;
use App\Http\Controllers\Settings\WebhookController;
use App\Http\Controllers\Targets\TargetController;
use App\Http\Controllers\Tasks\TaskController;
use Illuminate\Support\Facades\Route;

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

// Public, signed quotation links (no login required)
Route::get('/q/{quotation:ulid}', [PublicQuotationController::class, 'show'])
    ->middleware('signed')
    ->name('quotations.public.show');
Route::post('/q/{quotation:ulid}/accept', [PublicQuotationController::class, 'accept'])
    ->middleware(['signed', 'throttle:10,1'])
    ->name('quotations.public.accept');
Route::post('/q/{quotation:ulid}/reject', [PublicQuotationController::class, 'reject'])
    ->middleware(['signed', 'throttle:10,1'])
    ->name('quotations.public.reject');

// Public Landing Page (Root `/`)
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('pages.landing');
})->name('home');

Route::get('/welcome', function () {
    return view('pages.landing');
})->name('landing');

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Leads
    Route::get('/leads/check-duplicates', [LeadController::class, 'checkDuplicates'])->name('leads.check-duplicates');
    Route::get('/leads/export', [LeadController::class, 'export'])->name('leads.export');
    Route::resource('leads', LeadController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::post('/leads/{lead}/convert', ConvertLeadController::class)->name('leads.convert');
    Route::post('/leads/{lead}/merge', [LeadController::class, 'merge'])->name('leads.merge');

    // Companies & Contacts
    Route::resource('companies', CompanyController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::resource('contacts', ContactController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

    // Pipelines & Kanban
    Route::get('/pipelines', [PipelineController::class, 'index'])->name('pipelines.index');
    Route::post('/pipelines', [PipelineController::class, 'store'])->name('pipelines.store');
    Route::get('/pipelines/kanban', [PipelineController::class, 'kanban'])->name('pipelines.kanban');
    Route::get('/pipelines/{pipeline}/kanban', [PipelineController::class, 'kanban'])->name('pipelines.kanban.show');
    Route::post('/pipelines/{pipeline}/stages', [PipelineStageController::class, 'store'])->name('pipelines.stages.store');
    Route::post('/pipelines/{pipeline}/stages/reorder', [PipelineStageController::class, 'reorder'])->name('pipelines.stages.reorder');
    Route::delete('/pipelines/{pipeline}/stages/{stage}', [PipelineStageController::class, 'destroy'])->name('pipelines.stages.destroy');

    // Deals
    Route::get('/urgent-attention', UrgentAttentionController::class)->name('urgent-attention.index');
    Route::resource('deals', DealController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::patch('/deals/{deal}/stage', MoveDealStageController::class)->name('deals.move-stage');

    // Activities
    Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');
    Route::post('/activities', [ActivityController::class, 'store'])->name('activities.store');
    Route::delete('/activities/{activity}', [ActivityController::class, 'destroy'])->name('activities.destroy');

    // Attachments
    Route::post('/attachments', [AttachmentController::class, 'store'])->name('attachments.store');
    Route::get('/attachments/{attachment}', [AttachmentController::class, 'download'])->name('attachments.download');
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

    // Tasks
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::patch('/tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    // Follow-Ups
    Route::get('/follow-ups', [FollowUpController::class, 'index'])->name('follow-ups.index');
    Route::post('/follow-ups', [FollowUpController::class, 'store'])->name('follow-ups.store');
    Route::patch('/follow-ups/{followUp}/complete', [FollowUpController::class, 'complete'])->name('follow-ups.complete');
    Route::delete('/follow-ups/{followUp}', [FollowUpController::class, 'destroy'])->name('follow-ups.destroy');

    // Quotations
    Route::resource('quotations', QuotationController::class)->only(['index', 'create', 'store', 'show', 'update', 'destroy']);
    Route::post('/quotations/{quotation}/send', [QuotationWorkflowController::class, 'send'])->name('quotations.send');
    Route::post('/quotations/{quotation}/accept', [QuotationWorkflowController::class, 'accept'])->name('quotations.accept');
    Route::post('/quotations/{quotation}/reject', [QuotationWorkflowController::class, 'reject'])->name('quotations.reject');
    Route::post('/quotations/{quotation}/clone', [QuotationWorkflowController::class, 'cloneVersion'])->name('quotations.clone');
    Route::post('/quotations/{quotation}/approve-discount', [QuotationWorkflowController::class, 'approveDiscount'])->name('quotations.approve-discount');
    Route::get('/quotations/{quotation}/print', [QuotationWorkflowController::class, 'print'])->name('quotations.print');

    // Proposals
    Route::resource('proposals', ProposalController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    Route::post('/proposals/{proposal}/documents', [ProposalDocumentController::class, 'store'])->name('proposals.documents.store');
    Route::get('/proposals/documents/{document}/download', [ProposalDocumentController::class, 'download'])->name('proposals.documents.download');
    Route::delete('/proposals/documents/{document}', [ProposalDocumentController::class, 'destroy'])->name('proposals.documents.destroy');

    // Targets & Commissions
    Route::get('/targets', [TargetController::class, 'index'])->name('targets.index');
    Route::post('/targets', [TargetController::class, 'store'])->name('targets.store');
    Route::post('/targets/commissions', [TargetController::class, 'storeCommission'])->name('targets.commissions.store');

    // Mobile & Event Capture
    Route::get('/mobile/capture', [MobileCaptureController::class, 'show'])->name('mobile.capture');
    Route::post('/mobile/capture', [MobileCaptureController::class, 'store'])->name('mobile.capture.store');
    Route::get('/mobile/events', [MobileEventController::class, 'index'])->name('mobile.events.index');
    Route::post('/mobile/events', [MobileEventController::class, 'store'])->name('mobile.events.store');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::put('/notifications/preferences', [NotificationController::class, 'updatePreferences'])->name('notifications.preferences');

    // Imports & Exports
    Route::get('/imports', [ImportExportController::class, 'index'])->name('imports.index');
    Route::get('/imports/bulk', [BulkImportController::class, 'index'])->name('imports.bulk');
    Route::post('/imports/bulk', [BulkImportController::class, 'store'])->name('imports.bulk.store');
    Route::post('/imports', [ImportExportController::class, 'storeImport'])->name('imports.store');
    Route::post('/exports', [ImportExportController::class, 'storeExport'])->name('exports.store');
    Route::get('/imports/{job}/download', [ImportExportController::class, 'download'])->name('imports.download');

    // Settings (Users, Teams, Webhooks, Audit Logs)
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

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::middleware('can:webhooks.manage')->group(function (): void {
            Route::get('/webhooks', [WebhookController::class, 'index'])->name('webhooks.index');
            Route::post('/webhooks', [WebhookController::class, 'store'])->name('webhooks.store');
            Route::put('/webhooks/{webhook}', [WebhookController::class, 'update'])->name('webhooks.update');
            Route::delete('/webhooks/{webhook}', [WebhookController::class, 'destroy'])->name('webhooks.destroy');
        });

        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/security', [SecurityController::class, 'edit'])->name('security.edit');
        Route::put('/security', [SecurityController::class, 'update'])->name('security.update');
    });
});
