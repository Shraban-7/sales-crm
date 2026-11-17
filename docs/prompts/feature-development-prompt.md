# Feature Development Prompt — Gemini Antigravity IDE

**Last Updated:** 2026-11-17  
**Target Environment:** Google Gemini Antigravity IDE  
**Related Docs:** `GEMINI.md`, `PROJECT_STATUS.md`, `docs/security/security-checklist.md`, `docs/testing/quality-assurance.md`

## 1. Role

Act as a Senior Laravel 12 Domain Engineer and Full-Stack Blade/Tailwind CSS v4 Developer in Google Gemini Antigravity IDE.

## 2. Objective

Plan and implement a complete CRM feature or module end-to-end (database migration, Eloquent model, Enum, DTO, Action/Service, Policy, FormRequest, Controller, Blade UI, Vanilla JS interactivity, and automated tests) for `[TARGET_MODULE_OR_FEATURE]`.

## 3. Required Context (Read Before Executing)

Before generating a plan or modifying any file, you **MUST** read and cross-reference:
- `GEMINI.md` and `PROJECT_STATUS.md`
- Target module specification: `docs/modules/[TARGET_MODULE].md`
- `docs/database/database-design.md`, `docs/database/migrations-and-indexes.md`, `docs/database/data-integrity.md`
- `docs/security/role-permission-matrix.md` and `docs/security/input-validation.md`
- `docs/architecture/frontend-architecture.md`

## 4. Instructions

1. Read `docs/modules/[TARGET_MODULE].md` completely and verify every business rule, validation rule, edge case, and permission slug before writing code.
2. Implement migrations in the dependency order defined in `docs/database/migrations-and-indexes.md` using `DECIMAL(15,2)` for money and composite indexes for query filters.
3. Create backed PHP Enums (`app/Enums/`), immutable DTOs (`app/Data/`), and transactional Actions (`app/Actions/`) using `DB::transaction()` and `lockForUpdate()` where required.
4. Enforce both Policy authorization (`$this->authorize()`) and row-level ownership/team scoping (`->visibleTo($request->user())`).
5. Build responsive Blade views using reusable `<x-ui.*>` and `<x-crm.*>` components with Tailwind CSS v4 classes.

## 5. Execution Workflow

1. **Read Spec:** Load `GEMINI.md` and `docs/modules/[TARGET_MODULE].md`.
2. **Database & Models:** Create/verify migrations, Eloquent models (`$fillable`, `$casts`, relationships, `HasVisibilityScope`), and Model Factories.
3. **Domain Layer:** Implement Enums, DTOs, and `Action` / `Service` classes with transaction and audit logging support.
4. **HTTP & Security Layer:** Create `FormRequest` classes, `Policy` classes, thin `Controller` methods, and routes in `routes/web.php`.
5. **Frontend Layer:** Create Blade index/show/form views and any required Vanilla JS modules under `resources/js/modules/`.
6. **Testing & Docs:** Run `php artisan test` on new Feature/Unit/IDOR tests and update `PROJECT_STATUS.md` and `CHANGELOG.md`.

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

- Complete backend and frontend implementation for `[TARGET_MODULE_OR_FEATURE]`.
- Model factories and seeder updates for local development and testing.
- Feature, Unit, and cross-team IDOR authorization test suite.
- Updated status in `PROJECT_STATUS.md` and `docs/modules/[TARGET_MODULE].md`.

## 10. Completion Criteria

The task is complete **ONLY** when:
- [ ] All code adheres strictly to `GEMINI.md` and `docs/architecture/coding-standards.md`.
- [ ] All automated tests (`php artisan test`) pass with zero errors or lazy-loading violations.
- [ ] Security checklist items in `docs/security/security-checklist.md` are satisfied.
- [ ] `PROJECT_STATUS.md`, `CHANGELOG.md`, and affected `docs/` files are synchronized with the changes.
