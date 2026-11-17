# Performance Optimization Prompt — Gemini Antigravity IDE

**Last Updated:** 2026-11-17  
**Target Environment:** Google Gemini Antigravity IDE  
**Related Docs:** `GEMINI.md`, `PROJECT_STATUS.md`, `docs/security/security-checklist.md`, `docs/testing/quality-assurance.md`

## 1. Role

Act as a Principal Performance Engineer (Laravel 12, MySQL 8.0, Blade, Tailwind CSS v4, and Vanilla JS) in Google Gemini Antigravity IDE.

## 2. Objective

Identify and eliminate database, backend, caching, queue, and frontend performance bottlenecks across the **Sales CRM** application without breaking domain functionality or data isolation.

## 3. Required Context (Read Before Executing)

Before generating a plan or modifying any file, you **MUST** read and cross-reference:
- `GEMINI.md`
- `docs/architecture/scalability-strategy.md`
- `docs/database/database-optimization.md` and `docs/database/migrations-and-indexes.md`
- `docs/debugging/performance-debugging.md`

## 4. Instructions

1. Audit all Eloquent queries in Controllers, Services, Actions, and Blade views for N+1 lazy loading, `SELECT *` over-fetching, and PHP-memory collection aggregations (`->get()->sum()`).
2. Push all sums, counts, and averages (`SUM`, `COUNT`, `AVG`) down to MySQL using indexed `selectRaw` / `groupBy` queries.
3. Verify composite index usage on `leads`, `contacts`, `companies`, `deals`, `activities`, `tasks`, `follow_ups`, and `audit_logs` via `EXPLAIN` analysis.
4. Ensure heavy tasks (CSV imports, bulk exports, outbound emails) are dispatched to background queues (`ShouldQueue`) and large dataset iterations use `cursorPaginate()` or `lazyById()`.
5. Verify scoped cache keys (`Cache::remember`) include `team_id` / `user_id` so cached dashboard KPIs never leak across users or teams.

## 5. Execution Workflow

1. **Profile & Identify Bottlenecks:** Inspect queries, slow query logs, and Blade rendering loops.
2. **Optimize SQL & Eloquent:** Add constrained eager loading (`->with(['relation:id,name'])`), SQL aggregations, and missing composite indexes.
3. **Optimize Caching & Queues:** Add tenant-safe caching and queue offloading where appropriate.
4. **Verify Correctness:** Run `php artisan test` with `Model::preventLazyLoading()` enabled.

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

- Performance Bottleneck Analysis & Optimization Report.
- Refactored Eloquent queries, caching logic, and additive composite index migrations.
- Passing N+1 and regression test suite.

## 10. Completion Criteria

The task is complete **ONLY** when:
- [ ] All code adheres strictly to `GEMINI.md` and `docs/architecture/coding-standards.md`.
- [ ] All automated tests (`php artisan test`) pass with zero errors or lazy-loading violations.
- [ ] Security checklist items in `docs/security/security-checklist.md` are satisfied.
- [ ] `PROJECT_STATUS.md`, `CHANGELOG.md`, and affected `docs/` files are synchronized with the changes.
