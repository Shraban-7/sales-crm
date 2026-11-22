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

/*
|--------------------------------------------------------------------------
| 1. Public & Guest Guard Routes
|--------------------------------------------------------------------------
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

// Public, signed quotation links (no login required)
Route::get('/q/{quotation:ulid}', [PublicQuotationController::class, 'show'])
    ->middleware(['signed', 'throttle:30,1'])
    ->name('quotations.public.show');
Route::post('/q/{quotation:ulid}/accept', [PublicQuotationController::class, 'accept'])
    ->middleware(['signed', 'throttle:10,1'])
    ->name('quotations.public.accept');
Route::post('/q/{quotation:ulid}/reject', [PublicQuotationController::class, 'reject'])
    ->middleware(['signed', 'throttle:10,1'])
    ->name('quotations.public.reject');

// Public Landing Page & Welcome
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

/*
|--------------------------------------------------------------------------
| 2. Sales Rep Guard (Mutating Routes & Authoring)
| Enforced by: role.group:sales_rep & role.not_read_only (blocks Read-Only Analyst & Viewer)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'auth.session', 'role.group:sales_rep', 'role.not_read_only'])->group(function (): void {
    // Quotations Authoring (Defined before wildcard {quotation} route)
    Route::get('/quotations/create', [QuotationController::class, 'create'])->name('quotations.create');
    Route::post('/quotations', [QuotationController::class, 'store'])->name('quotations.store');
    Route::match(['put', 'patch'], '/quotations/{quotation}', [QuotationController::class, 'update'])->name('quotations.update');
    Route::delete('/quotations/{quotation}', [QuotationController::class, 'destroy'])->name('quotations.destroy');
    Route::post('/quotations/{quotation}/send', [QuotationWorkflowController::class, 'send'])->name('quotations.send');
    Route::post('/quotations/{quotation}/accept', [QuotationWorkflowController::class, 'accept'])->name('quotations.accept');
    Route::post('/quotations/{quotation}/reject', [QuotationWorkflowController::class, 'reject'])->name('quotations.reject');
    Route::post('/quotations/{quotation}/clone', [QuotationWorkflowController::class, 'cloneVersion'])->name('quotations.clone');

    // Proposals Authoring (Defined before wildcard {proposal} route)
    Route::get('/proposals/create', [ProposalController::class, 'create'])->name('proposals.create');
    Route::post('/proposals', [ProposalController::class, 'store'])->name('proposals.store');
    Route::delete('/proposals/{proposal}', [ProposalController::class, 'destroy'])->name('proposals.destroy');
    Route::post('/proposals/{proposal}/documents', [ProposalDocumentController::class, 'store'])->name('proposals.documents.store');
    Route::delete('/proposals/documents/{document}', [ProposalDocumentController::class, 'destroy'])->name('proposals.documents.destroy');

    // Leads Mutations
    Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
    Route::match(['put', 'patch'], '/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
    Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
    Route::post('/leads/{lead}/convert', ConvertLeadController::class)->name('leads.convert');
    Route::post('/leads/{lead}/merge', [LeadController::class, 'merge'])->name('leads.merge');

    // Companies & Contacts Mutations
    Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');
    Route::match(['put', 'patch'], '/companies/{company}', [CompanyController::class, 'update'])->name('companies.update');
    Route::delete('/companies/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');

    Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');
    Route::match(['put', 'patch'], '/contacts/{contact}', [ContactController::class, 'update'])->name('contacts.update');
    Route::delete('/contacts/{contact}', [ContactController::class, 'destroy'])->name('contacts.destroy');

    // Deals Mutations
    Route::post('/deals', [DealController::class, 'store'])->name('deals.store');
    Route::match(['put', 'patch'], '/deals/{deal}', [DealController::class, 'update'])->name('deals.update');
    Route::patch('/deals/{deal}/stage', MoveDealStageController::class)->name('deals.move-stage');
    Route::delete('/deals/{deal}', [DealController::class, 'destroy'])->name('deals.destroy');

    // Activities Mutations
    Route::post('/activities', [ActivityController::class, 'store'])->name('activities.store');
    Route::delete('/activities/{activity}', [ActivityController::class, 'destroy'])->name('activities.destroy');

    // Attachments Mutations
    Route::post('/attachments', [AttachmentController::class, 'store'])->name('attachments.store');
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

    // Tasks Mutations
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::patch('/tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    // Follow-Ups Mutations
    Route::post('/follow-ups', [FollowUpController::class, 'store'])->name('follow-ups.store');
    Route::patch('/follow-ups/{followUp}/complete', [FollowUpController::class, 'complete'])->name('follow-ups.complete');
    Route::delete('/follow-ups/{followUp}', [FollowUpController::class, 'destroy'])->name('follow-ups.destroy');

    // Mobile & Event Lead Capture
    Route::post('/mobile/capture', [MobileCaptureController::class, 'store'])->name('mobile.capture.store');
    Route::post('/mobile/events', [MobileEventController::class, 'store'])->name('mobile.events.store');
});

/*
|--------------------------------------------------------------------------
| 3. Sales Manager Guard Routes
| Enforced by: role.group:sales_manager
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'auth.session', 'role.group:sales_manager'])->group(function (): void {
    // High-Discount Quotation Approvals
    Route::post('/quotations/{quotation}/approve-discount', [QuotationWorkflowController::class, 'approveDiscount'])
        ->name('quotations.approve-discount');

    // Bulk CSV Data Imports
    Route::get('/imports', [ImportExportController::class, 'index'])->name('imports.index');
    Route::get('/imports/bulk', [BulkImportController::class, 'index'])->name('imports.bulk');
    Route::post('/imports/bulk', [BulkImportController::class, 'store'])->name('imports.bulk.store');
    Route::post('/imports', [ImportExportController::class, 'storeImport'])->name('imports.store');
    Route::get('/imports/{job}/download', [ImportExportController::class, 'download'])->name('imports.download');

    // Sales Targets & Commissions
    Route::post('/targets', [TargetController::class, 'store'])->name('targets.store');
    Route::post('/targets/commissions', [TargetController::class, 'storeCommission'])->name('targets.commissions.store');
});

/*
|--------------------------------------------------------------------------
| 4. Org Admin & Super Admin Guard Routes
| Enforced by: role.group:organization_admin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'auth.session', 'role.group:organization_admin'])->group(function (): void {
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
});

/*
|--------------------------------------------------------------------------
| 5. Super Admin Exclusive Guard Routes
| Enforced by: role.group:super_admin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'auth.session', 'role.group:super_admin'])->prefix('super-admin')->name('super-admin.')->group(function (): void {
    Route::get('/status', function () {
        return response()->json([
            'status' => 'healthy',
            'version' => config('crm.version', '1.0.1'),
            'timestamp' => now()->toIso8601String(),
        ]);
    })->name('status');
});

/*
|--------------------------------------------------------------------------
| 6. Shared Authenticated Guard Routes
| Accessible to: All authenticated users (including Read-Only Analyst & Viewer)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'auth.session'])->group(function (): void {
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

    // Deals & Urgent Attention
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
});
