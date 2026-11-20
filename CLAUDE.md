# CLAUDE.md — AI Coding Guide for Sale Fusion CRM

This file documents the architecture, directory layout, conventions, and verification workflow for **Sale Fusion CRM** (PHP 8.3+ · Laravel 13 · MySQL 8.0+ · Blade · Tailwind CSS v4 · Vanilla ES2022 JS).

---

## 1. Tech Stack & Strict Boundaries

- **Backend:** PHP 8.3+ (`declare(strict_types=1);`), Laravel 13.x, Eloquent ORM, Spatie Laravel Permission, mPDF (`mpdf/mpdf`).
- **Database:** MySQL 8.0+ (`InnoDB`, `utf8mb4_unicode_ci`). Automated PHPUnit tests run on in-memory SQLite (`phpunit.xml`).
- **Frontend:** Server-rendered Laravel Blade views (`resources/views/`) + Tailwind CSS v4 CSS-first `@theme` (`resources/css/app.css` via `@tailwindcss/vite`) + Vanilla ES2022 JS modules (`resources/js/`).
- **Do not introduce:** React, Vue, Svelte, Inertia, Livewire, or Tailwind v3 `tailwind.config.js` / `postcss.config.js`.

---

## 2. Architecture & Code Layering

Requests follow a strict thin-controller pattern:

```text
Route (auth + middleware)
  -> FormRequest (validation + authorize())
    -> Controller ($this->authorize() + delegates to Service/Action)
      -> Action / Service (DB::transaction(), AuditLoggerService, WebhookDispatcherService)
        -> Eloquent Model (explicit $fillable, backed Enum casts, scopeVisibleTo())
```

### Key Directories & File Locations

| Area | Primary Files |
| :--- | :--- |
| **Routes** | `routes/web.php` (CRM pages & admin routes), `routes/api.php` (`POST /api/leads/capture`), `routes/console.php` (scheduled jobs) |
| **Leads & Capture** | `app/Http/Controllers/Leads/LeadController.php`, `LeadCaptureController.php`, `LeadWebhookController.php`, `ConvertLeadController.php`; `app/Actions/Leads/ConvertLeadToDealAction.php`; `app/Services/Leads/LeadAssignmentService.php`, `LeadDuplicateService.php` |
| **Pipelines & Kanban** | `app/Http/Controllers/Pipelines/PipelineController.php`, `PipelineStageController.php`; `app/Http/Controllers/Deals/DealController.php`, `MoveDealStageController.php`; `app/Actions/Deals/MoveDealStageAction.php`; `resources/views/pages/pipelines/kanban.blade.php`; **`resources/js/modules/kanban.js`** |
| **Quotations & PDFs** | `app/Http/Controllers/Quotations/QuotationController.php`, `QuotationWorkflowController.php`, `PublicQuotationController.php`; `app/Actions/Quotations/CalculateQuotationTotalsAction.php`, `AcceptQuotationAction.php`, `RejectQuotationAction.php`, `CloneQuotationVersionAction.php`; `app/Services/Quotations/QuotationPdfService.php`; `resources/js/modules/quotation-calculator.js` |
| **Webhooks (Inbound + Outbound)** | Inbound HMAC intake: `app/Http/Controllers/Leads/LeadWebhookController.php` (`routes/api.php`). Outbound admin management: `app/Http/Controllers/Settings/WebhookController.php`, `app/Models/Webhook.php`, `app/Policies/WebhookPolicy.php`, `app/Enums/WebhookEvent.php`, `app/Services/Webhooks/WebhookDispatcherService.php`, `app/Jobs/DeliverWebhookJob.php`, `resources/views/pages/settings/webhooks/index.blade.php` |
| **Tasks, Follow-Ups & Activities** | `app/Http/Controllers/Tasks/TaskController.php`, `FollowUps/FollowUpController.php`, `Activities/ActivityController.php`, `Attachments/AttachmentController.php` |
| **Reports & Imports** | `app/Http/Controllers/Reports/ReportController.php`, `app/Services/Reports/AnalyticsService.php`; `app/Http/Controllers/Imports/ImportExportController.php`, `app/Jobs/ProcessCsvImportJob.php`, `app/Jobs/GenerateExportFileJob.php` |
| **RBAC, Policies & Security** | `database/seeders/RolePermissionSeeder.php`, `app/Policies/*`, `app/Models/Concerns/HasVisibilityScope.php`, `app/Providers/AppServiceProvider.php`, `app/Services/Audit/AuditLoggerService.php` |
| **Blade Components & Theme** | `resources/views/components/layouts/app.blade.php`, `nav-links.blade.php`, `resources/views/components/crm/*`, `resources/views/errors/*`, `resources/css/app.css` |
| **Database Schema & Seeders** | `database/migrations/`, `database/schema.sql`, `database/seed.sql`, `database/seeders/RolePermissionSeeder.php`, `database/seeders/DemoCrmSeeder.php` |

---

## 3. Coding Conventions

1. Start every PHP file with `declare(strict_types=1);` and use explicit parameter and return types.
2. Keep controllers thin — validate with dedicated `app/Http/Requests/**` classes, authorize with `$this->authorize(...)` (plus route middleware on admin groups), and place domain logic in `app/Services/` or `app/Actions/`.
3. Never use `protected $guarded = [];` on Eloquent models — declare explicit `$fillable` arrays.
4. Always scope list/search queries with `->visibleTo($request->user())` to prevent cross-team/cross-owner IDOR leaks.
5. Never interpolate user input into raw SQL; use Eloquent builders or bound parameters (`?`).
6. Wrap multi-table mutations in `DB::transaction(...)` and log security-sensitive changes via `AuditLoggerService`.
7. Access configuration via `config('crm.*')` — never call `env()` outside `config/*.php`.
8. Store monetary fields as `DECIMAL(15, 2)` and use PHP backed enums (`app/Enums/`) for statuses, priorities, sources, and webhook events.
9. Keep Blade output escaped (`{{ $var }}`) and include `@csrf` in forms and `X-CSRF-TOKEN` in JS `fetch()` requests (`resources/js/modules/http.js`).

---

## 4. Essential Commands

```bash
# Run the full automated test suite
php artisan test --compact

# Check PSR-12 / Laravel Pint formatting
vendor/bin/pint --test

# Auto-fix formatting
vendor/bin/pint

# Compile Tailwind CSS v4 and Vanilla JS modules
npm run build

# Fresh local database migration + demo seed
php artisan migrate:fresh --seed

# Security dependency checks
composer audit
npm audit
```
