<?php

declare(strict_types=1);

use App\Http\Controllers\Activities\ActivityController;
use App\Http\Controllers\Attachments\AttachmentController;
use App\Http\Controllers\Companies\CompanyController;
use App\Http\Controllers\Contacts\ContactController;
use App\Http\Controllers\Deals\DealController;
use App\Http\Controllers\Deals\MoveDealStageController;
use App\Http\Controllers\FollowUps\FollowUpController;
use App\Http\Controllers\Leads\ConvertLeadController;
use App\Http\Controllers\Leads\LeadController;
use App\Http\Controllers\Mobile\CaptureController as MobileCaptureController;
use App\Http\Controllers\Mobile\EventController as MobileEventController;
use App\Http\Controllers\Proposals\ProposalController;
use App\Http\Controllers\Proposals\ProposalDocumentController;
use App\Http\Controllers\Quotations\QuotationController;
use App\Http\Controllers\Quotations\QuotationWorkflowController;
use App\Http\Controllers\Tasks\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Sales Rep Guard Routes (Commercial Mutations & Authoring)
|--------------------------------------------------------------------------
|
| Gated by 'role.rep' and 'role.not_read_only'.
| Permitted: Super Admin, Org Admin, Head of Sales, Sales Manager, Sales Rep, Sales Executive.
| Denied: Read-Only Analyst and Viewer accounts receive 403 Forbidden.
|
*/

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

// Companies Mutations
Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');
Route::match(['put', 'patch'], '/companies/{company}', [CompanyController::class, 'update'])->name('companies.update');
Route::delete('/companies/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');

// Contacts Mutations
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
