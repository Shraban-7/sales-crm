# Database Schema, Relationship & Query Review Prompt — Gemini Antigravity IDE

**Last Updated:** 2026-11-17  
**Target Environment:** Google Gemini Antigravity IDE  
**Related Docs:** `GEMINI.md`, `PROJECT_STATUS.md`, `docs/security/security-checklist.md`, `docs/testing/quality-assurance.md`

## 1. Role

Act as a Principal MySQL 8.0 Database Architect and Laravel Eloquent Performance Engineer in Google Gemini Antigravity IDE.

## 2. Objective

Audit and optimize database migrations, Eloquent relationships, polymorphic mappings, foreign key constraints, composite indexes, and query execution plans across **Sales CRM**.

## 3. Required Context (Read Before Executing)

Before generating a plan or modifying any file, you **MUST** read and cross-reference:
- `GEMINI.md`
- `docs/database/database-design.md`, `docs/database/database-relationships.md`, `docs/database/erd.md`
- `docs/database/migrations-and-indexes.md`, `docs/database/database-optimization.md`, `docs/database/data-integrity.md`

## 4. Instructions

1. Compare all migrations in `database/migrations/` and models in `app/Models/` against `docs/database/database-design.md` and `docs/database/migrations-and-indexes.md`.
2. Verify all monetary columns use `DECIMAL(15, 2)` and percentage columns use `DECIMAL(5, 2)`—flag any `float` or `double` columns immediately.
3. Verify all foreign keys define explicit `restrictOnDelete()`, `cascadeOnDelete()`, or `nullOnDelete()` matching `docs/database/data-integrity.md`.
4. Verify `Relation::enforceMorphMap()` is registered in `AppServiceProvider` and all polymorphic tables have composite `(morph_type, morph_id, ...)` indexes.
5. Inspect Eloquent queries in Services, Actions, and Controllers for N+1 lazy loading, unbounded `get()` calls on large tables, or missing composite indexes.

## 5. Execution Workflow

1. **Read Database Specs:** Load all 6 files in `docs/database/`.
2. **Schema & Migration Audit:** Inspect `database/migrations/` for column types, nullability, foreign keys, soft deletes, and composite indexes.
3. **Model & Query Audit:** Inspect `app/Models/` and `app/Services/` for relationship definitions, `$casts`, and eager loading.
4. **Optimization & Documentation Sync:** Apply additive migration/query fixes and sync `docs/database/` if new indexes are added.

## 6. Technical Constraints

- **Backend:** PHP 8.3+ (`declare(strict_types=1);`), [Laravel 12.x](https://laravel.com/framework/docs/12.x/configuration), [Eloquent ORM](https://api.laravel.com/docs/12.x/index.html), MySQL 8.0+ (`InnoDB`).
- **Frontend:** Laravel Blade components, [Tailwind CSS v4](https://tailwindcss.com/docs/upgrade-guide) (`@import "tailwindcss";` and `@theme` tokens via `@tailwindcss/vite`), and Vanilla ES2022 JavaScript (or Alpine.js only when explicitly justified).
- **Prohibited:** Do NOT install React, Vue, Livewire, Inertia, legacy Tailwind v3 `tailwind.config.js`, or unapproved third-party packages. Do NOT use `env()` outside `config/*.php`. Do NOT place business logic or multi-table transactions inside Controllers or Blade views.

## 7. Security Requirements

- Enforce authentication (`auth`, `active_user`) and rate limiting (`throttle`) on routes.
- Enforce Laravel Policy checks (`$this->authorize()`) on every controller action AND apply row-level ownership/team scopes (`->visibleTo($request->user())`) on every query to prevent IDOR.
- Validate all inputs via dedicated `FormRequest` classes and scope foreign key `exists` rules to `$this->user()->team_id`.
- Escape all Blade output (`{ }`), store attachments on the `private` disk, and log sensitive mutations to `audit_logs`.

## 8. Testing Requirements

- Write or update [Pest PHP](https://pestphp.com/docs/writing-tests) / PHPUnit tests under `tests/Feature/` and `tests/Unit/`.
- Include positive happy-path tests, `422` validation failure tests, `403` cross-team/cross-owner IDOR authorization tests, and database transaction integrity assertions.
- Ensure `Model::preventLazyLoading()` passes with zero N+1 query violations.

## 9. Expected Deliverables

- Database Schema & Query Audit Report detailing index coverage, FK integrity, and N+1 risks.
- Optimized Eloquent queries and additive index migrations where needed.
- Updated `docs/database/migrations-and-indexes.md` and `docs/database/erd.md` if schema changes occurred.

## 10. Completion Criteria

The task is complete **ONLY** when:
- [ ] All code adheres strictly to `GEMINI.md` and `docs/architecture/coding-standards.md`.
- [ ] All automated tests (`php artisan test`) pass with zero errors or lazy-loading violations.
- [ ] Security checklist items in `docs/security/security-checklist.md` are satisfied.
- [ ] `PROJECT_STATUS.md`, `CHANGELOG.md`, and affected `docs/` files are synchronized with the changes.
