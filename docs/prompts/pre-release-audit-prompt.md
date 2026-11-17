# Pre-Release Production Readiness Audit Prompt — Gemini Antigravity IDE

**Last Updated:** 2026-11-17  
**Target Environment:** Google Gemini Antigravity IDE  
**Related Docs:** `GEMINI.md`, `PROJECT_STATUS.md`, `docs/security/security-checklist.md`, `docs/testing/quality-assurance.md`

## 1. Role

Act as a Principal Release Engineer, Security Auditor, Database Architect, and QA Lead in Google Gemini Antigravity IDE.

## 2. Objective

Execute a complete pre-release production-readiness audit of the **Sales CRM** repository across Architecture, Database Integrity, Security, Automated Testing, Deployment Configuration, and Documentation Consistency.

## 3. Required Context (Read Before Executing)

Before generating a plan or modifying any file, you **MUST** read and cross-reference:
- `GEMINI.md` and `PROJECT_STATUS.md`
- `docs/security/security-checklist.md`
- `docs/testing/quality-assurance.md` and `docs/testing/regression-testing.md`
- `docs/deployment/production-checklist.md`, `docs/deployment/environment-variables.md`, `docs/deployment/queue-and-scheduler.md`

## 4. Instructions

1. Verify every item in `docs/security/security-checklist.md` and `docs/deployment/production-checklist.md`.
2. Verify zero occurrences of `env()` outside `config/*.php`, zero occurrences of `dd()`/`dump()`/`console.log()`, and zero occurrences of `$guarded = []`.
3. Run `./vendor/bin/pint --test`, `./vendor/bin/phpstan analyse`, `php artisan test`, and `npm run build`.
4. Verify all migrations have valid `up()` and `down()` methods and seeders (`RolePermissionSeeder`, `DefaultPipelineSeeder`) are idempotent (`firstOrCreate` / `updateOrCreate`).
5. Verify `PROJECT_STATUS.md`, `CHANGELOG.md`, and `docs/modules/*.md` accurately reflect the release state.

## 5. Execution Workflow

1. **Automated QA Gate Execution:** Run Pint, PHPStan, Pest/PHPUnit, and Vite build checks.
2. **Security & Isolation Sweep:** Audit routes, policies, visibility scopes, file uploads, and `.env.example`.
3. **Database & Migration Sweep:** Verify migrations, indexes, foreign keys, and idempotent production seeders.
4. **Documentation & Release Sign-Off:** Verify documentation sync and generate a Go/No-Go Release Audit Report.

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

- Complete Go / No-Go Pre-Release Audit Report with pass/fail status for every gate.
- List of any blocking defects found and their immediate fixes.
- Finalized `CHANGELOG.md` and `PROJECT_STATUS.md` ready for release tagging.

## 10. Completion Criteria

The task is complete **ONLY** when:
- [ ] All code adheres strictly to `GEMINI.md` and `docs/architecture/coding-standards.md`.
- [ ] All automated tests (`php artisan test`) pass with zero errors or lazy-loading violations.
- [ ] Security checklist items in `docs/security/security-checklist.md` are satisfied.
- [ ] `PROJECT_STATUS.md`, `CHANGELOG.md`, and affected `docs/` files are synchronized with the changes.
