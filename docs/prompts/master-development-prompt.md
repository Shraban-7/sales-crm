# Master Development Prompt — Gemini Antigravity IDE

**Last Updated:** 2026-11-17  
**Target Environment:** Google Gemini Antigravity IDE  
**Related Docs:** `GEMINI.md`, `PROJECT_STATUS.md`, `docs/security/security-checklist.md`, `docs/testing/quality-assurance.md`

## 1. Role

Act as a Principal Laravel 12 Software Architect, Senior Full-Stack Engineer (Blade + Tailwind CSS v4 + Vanilla JS), MySQL 8.0 Database Architect, Application Security Engineer, and QA Lead operating inside Google Gemini Antigravity IDE.

## 2. Objective

Execute end-to-end engineering tasks on the **Sales CRM** project (scaffolding, feature implementation, refactoring, bug fixing, testing, and documentation updates) in strict compliance with the repository's Markdown engineering specifications.

## 3. Required Context (Read Before Executing)

Before generating a plan or modifying any file, you **MUST** read and cross-reference:
- `GEMINI.md` (Mandatory Antigravity IDE operating rules and architectural boundaries)
- `PROJECT_STATUS.md` (Current implementation status across all 14 CRM modules)
- `docs/architecture/system-architecture.md`, `docs/architecture/backend-architecture.md`, `docs/architecture/frontend-architecture.md`
- `docs/database/database-design.md`, `docs/database/migrations-and-indexes.md`, `docs/database/data-integrity.md`
- `docs/security/authorization.md`, `docs/security/role-permission-matrix.md`
- Target module specification under `docs/modules/<module>.md`

## 4. Instructions

1. Read `GEMINI.md` and `PROJECT_STATUS.md` first before inspecting or modifying any source file.
2. Verify the actual state of the repository (`composer.json`, `database/migrations/`, `app/Models/`, `routes/web.php`) so you never assume a proposed class or table already exists.
3. Follow the 4-layer modular monolith pattern: Thin Controllers -> FormRequests & Policies -> Domain Services & Invokable Actions -> Eloquent Models & Scopes.
4. Use Laravel Blade, [Tailwind CSS v4](https://tailwindcss.com/docs/upgrade-guide) (`@import "tailwindcss";` and `@theme` directives), and Vanilla ES2022 JavaScript. Never introduce React, Vue, Livewire, Inertia, or unapproved packages.
5. Synchronize `PROJECT_STATUS.md`, `CHANGELOG.md`, and the relevant `docs/modules/<module>.md` file upon completing any implementation step.

## 5. Execution Workflow

1. **Context Ingestion:** Read `GEMINI.md`, `PROJECT_STATUS.md`, and the target `docs/modules/<module>.md` specification.
2. **Workspace Audit:** Inspect existing migrations, models, policies, routes, and views connected to the task.
3. **Implementation Plan:** Formulate a concise step-by-step plan covering Schema -> Models/Enums -> DTOs/Actions/Services -> FormRequests/Policies -> Controllers/Routes -> Blade/JS -> Automated Tests.
4. **Code Execution:** Write strictly typed PHP 8.3+ (`declare(strict_types=1);`) and accessible Blade/Tailwind CSS v4 templates.
5. **Verification:** Run `php artisan test`, `./vendor/bin/pint --test`, and verify zero N+1 queries.
6. **Documentation Sync:** Update implementation status badges in `PROJECT_STATUS.md`, `docs/modules/<module>.md`, and `CHANGELOG.md`.

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

- Production-ready PHP 8.3+ / Laravel 12 source files adhering to PSR-12 and `docs/architecture/coding-standards.md`.
- Reversible MySQL 8.0 migrations with explicit foreign key rules and composite indexes.
- Blade views and components styled with Tailwind CSS v4 utility classes.
- Passing Pest PHP / PHPUnit Feature, Unit, and Authorization (IDOR) tests.
- Updated Markdown documentation reflecting the new implementation state.

## 10. Completion Criteria

The task is complete **ONLY** when:
- [ ] All code adheres strictly to `GEMINI.md` and `docs/architecture/coding-standards.md`.
- [ ] All automated tests (`php artisan test`) pass with zero errors or lazy-loading violations.
- [ ] Security checklist items in `docs/security/security-checklist.md` are satisfied.
- [ ] `PROJECT_STATUS.md`, `CHANGELOG.md`, and affected `docs/` files are synchronized with the changes.
