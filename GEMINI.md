# GEMINI.md — Antigravity IDE Engineering Directives for Sales CRM

**Last Updated:** 2026-11-17  
**Target Agent:** Google Gemini (Antigravity IDE Primary Coding Agent)  
**Project State:** Greenfield Specification (`0% Implemented` / `100% Proposed` — see `PROJECT_STATUS.md`)

## 1. Mandatory Pre-Execution Protocol

Before planning, generating, refactoring, or debugging any code in this repository, Gemini Antigravity **MUST**:
1. Read `PROJECT_STATUS.md` to verify which modules and migrations are `Implemented`, `Partially Implemented`, or `Proposed`.
2. Read the target module specification in `docs/modules/<module>.md`.
3. Read the database schema and indexing rules in `docs/database/database-design.md` and `docs/database/migrations-and-indexes.md`.
4. Read the RBAC matrix in `docs/security/role-permission-matrix.md` and `docs/security/authorization.md`.
5. Never assume a model, migration, route, or helper exists without inspecting the workspace files first.

## 2. Technology Stack & Strict Boundaries

- **Backend:** PHP 8.3+ (`declare(strict_types=1);`), [Laravel 12.x](https://laravel.com/framework/docs/12.x/configuration), [Eloquent ORM](https://api.laravel.com/docs/12.x/index.html).
- **Database:** MySQL 8.0+ (`InnoDB`, `utf8mb4_unicode_ci`).
- **Frontend:** Laravel Blade components + [Tailwind CSS v4](https://tailwindcss.com/docs/upgrade-guide) (`@import "tailwindcss";` and `@theme` directives via `@tailwindcss/vite`) + Vanilla ES2022 JavaScript.
- **Conditional Libraries:** Alpine.js or jQuery may ONLY be introduced if explicitly justified by complex interactive UI requirements (e.g., multi-column Kanban drag-and-drop or dynamic quotation line-item calculators) and approved in documentation.
- **Testing:** [Pest PHP](https://pestphp.com/docs/writing-tests) or PHPUnit 11.

### Strictly Prohibited Without Explicit User Approval
- Do **NOT** install React, Vue, Angular, Svelte, Inertia.js, Livewire, or SPA state libraries.
- Do **NOT** create or use legacy `tailwind.config.js` or `postcss.config.js` patterns from Tailwind v3; use Tailwind CSS v4 CSS-first `@theme` configuration.
- Do **NOT** install unapproved Composer or NPM packages when native Laravel 12 or Vanilla JS features suffice.
- Do **NOT** place business logic, raw SQL queries, or transaction orchestration inside Controllers or Blade views.

## 3. Architecture Rules

1. **Thin Controllers:** Controllers (`app/Http/Controllers/`) must only accept a typed `FormRequest` (or Request), call `$this->authorize()`, invoke a Domain `Service` or `Action`, and return a Blade `View`, `RedirectResponse`, or `JsonResponse`. Maximum 5 public action methods per resource controller.
2. **FormRequest Validation:** Every `POST`, `PUT`, `PATCH` endpoint must use a dedicated `FormRequest` class in `app/Http/Requests/` with explicit `authorize()` and `rules()` methods.
3. **Services & Actions:**
   - Place multi-step domain orchestration in `app/Services/<Domain>/<Name>Service.php`.
   - Place single-purpose atomic operations (e.g., `ConvertLeadToDealAction`, `CalculateQuotationTotalsAction`) in `app/Actions/<Domain>/<Name>Action.php`.
4. **Multi-Step Database Transactions:** Wrap any write operation touching 2+ tables (such as Lead Conversion or Quotation Line Item sync) in `DB::transaction(function () { ... })`.

## 4. Coding Conventions

- Declare `declare(strict_types=1);` at the top of every PHP file.
- Use explicit parameter types and return types on all methods (`public function handle(Lead $lead, User $actor): Deal`).
- Use PHP 8.1+ backed `enum` classes (`app/Enums/`) for all statuses, priorities, and stage types (e.g., `LeadStatus`, `DealStatus`, `TaskPriority`, `QuotationStatus`).
- Never use `env()` outside `config/*.php` files; always access configuration via `config('crm.key')`.
- Use `$fillable` arrays explicitly on every Eloquent model; `protected $guarded = [];` is strictly forbidden.

## 5. Database Conventions

- **Primary & Foreign Keys:** Use `id()` (`BIGINT UNSIGNED AUTO_INCREMENT`) and `foreignId('..._id')->constrained()->cascadeOnDelete()` or `restrictOnDelete()` as specified in `docs/database/data-integrity.md`.
- **Monetary Values:** Store all currency amounts as `DECIMAL(15, 2)` (never `FLOAT` or `DOUBLE`). Store percentages/probabilities as `DECIMAL(5, 2)` or `TINYINT UNSIGNED`.
- **Timestamps & Soft Deletes:** Include `$table->timestamps()` on all domain tables and `$table->softDeletes()` on core business entities (`leads`, `contacts`, `companies`, `deals`, `quotations`, `tasks`).
- **Eager Loading:** Prevent N+1 queries by eager-loading relationships (`->with([...])`) in every index/list query and enabling `Model::preventLazyLoading(! app()->isProduction())` in `AppServiceProvider`.

## 6. Security Requirements

- **Authorization (RBAC + Ownership):** Enforce Laravel `Policy` checks on every controller action AND apply query scopes (`scopeVisibleTo(Builder $query, User $user)`) on every list/search query to prevent Insecure Direct Object Reference (IDOR) across teams.
- **XSS Prevention:** Always use escaped Blade output `{{ $variable }}`. Never use `{!! $untrusted !!}` on user-supplied data.
- **CSRF Protection:** Include `@csrf` in all Blade forms and `X-CSRF-TOKEN` headers on all Vanilla JS `fetch()` mutations.
- **File Uploads:** Validate MIME types and file extensions strictly, store private CRM attachments on a non-public disk (`storage/app/private/attachments`), and serve downloads via authorized controller actions or temporary signed URLs.
- **Audit Trail:** Log all security-sensitive mutations (lead conversion, deal stage change, quote approval, permission change, data export) to `audit_logs`.

## 7. Testing Requirements

- Every new feature or bug fix must include automated Feature and Unit tests under `tests/Feature/` and `tests/Unit/`.
- Every CRM endpoint must be tested for:
  1. Unauthenticated guest rejection (`302` redirect to login or `401`).
  2. Unauthorized role/team access rejection (`403 Forbidden` — IDOR test).
  3. Validation failure assertions (`422` / session errors).
  4. Successful happy-path database persistence (`assertDatabaseHas`) and audit log creation.

## 8. Documentation Synchronization Requirement

Whenever a feature is implemented, modified, or refactored in Antigravity IDE:
1. Update the module status in `PROJECT_STATUS.md` and `docs/modules/<module>.md` from `Proposed` to `Implemented` (or `Partially Implemented`).
2. Update `docs/database/migrations-and-indexes.md` and `docs/database/erd.md` if any schema or index changes occur.
3. Log the change under `[Unreleased]` in `CHANGELOG.md`.
