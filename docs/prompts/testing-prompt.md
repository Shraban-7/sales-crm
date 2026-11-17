# Automated Testing Generation & Execution Prompt — Gemini Antigravity IDE

**Last Updated:** 2026-11-17  
**Target Environment:** Google Gemini Antigravity IDE  
**Related Docs:** `GEMINI.md`, `PROJECT_STATUS.md`, `docs/security/security-checklist.md`, `docs/testing/quality-assurance.md`

## 1. Role

Act as a Principal QA Architect and Laravel Pest/PHPUnit Testing Specialist in Google Gemini Antigravity IDE.

## 2. Objective

Design, generate, and execute comprehensive Unit, Feature, Database Integrity, and RBAC Authorization tests for `[TARGET_MODULE_OR_CLASS]` in **Sales CRM**.

## 3. Required Context (Read Before Executing)

Before generating a plan or modifying any file, you **MUST** read and cross-reference:
- `GEMINI.md`
- `docs/testing/testing-strategy.md`, `docs/testing/unit-testing.md`, `docs/testing/feature-testing.md`, `docs/testing/database-testing.md`, `docs/testing/authorization-testing.md`, `docs/testing/test-cases.md`
- Target module specification in `docs/modules/<module>.md`
- `docs/security/role-permission-matrix.md`

## 4. Instructions

1. Read `docs/testing/test-cases.md` and Section 12 (`Automated Test Scenarios`) of `docs/modules/<module>.md`.
2. Use Eloquent Model Factories (`database/factories/`) and `RefreshDatabase` to keep every test isolated and deterministic.
3. For every HTTP endpoint in the target module, write test cases covering: (a) unauthenticated guest redirect, (b) cross-team and cross-rep `403 Forbidden` IDOR rejection, (c) `422` validation failure on invalid payloads and boundary values, and (d) `200`/`302` happy-path database persistence and `audit_logs` creation.
4. Write isolated Unit tests for domain math (e.g., quotation totals, weighted deal forecast, lead scoring) and Enum state transitions.
5. Run `php artisan test` and fix any failing assertions or lazy-loading violations.

## 5. Execution Workflow

1. **Review Test Specs:** Read `docs/testing/` and `docs/modules/<module>.md`.
2. **Verify Factories & Seeders:** Ensure factories exist for all required models and `RolePermissionSeeder` is available.
3. **Author Unit & Feature Tests:** Create structured test files under `tests/Unit/<Module>/` and `tests/Feature/<Module>/`.
4. **Execute & Verify:** Run `php artisan test --filter=<Module>` and confirm 100% pass rate with `Model::preventLazyLoading()` active.

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

- Complete Unit, Feature, Database, and Authorization test files under `tests/`.
- Required Model Factories under `database/factories/`.
- Test execution summary output confirming all scenarios pass.

## 10. Completion Criteria

The task is complete **ONLY** when:
- [ ] All code adheres strictly to `GEMINI.md` and `docs/architecture/coding-standards.md`.
- [ ] All automated tests (`php artisan test`) pass with zero errors or lazy-loading violations.
- [ ] Security checklist items in `docs/security/security-checklist.md` are satisfied.
- [ ] `PROJECT_STATUS.md`, `CHANGELOG.md`, and affected `docs/` files are synchronized with the changes.
