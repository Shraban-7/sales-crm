# Safe Code Refactoring Prompt — Gemini Antigravity IDE

**Last Updated:** 2026-11-17  
**Target Environment:** Google Gemini Antigravity IDE  
**Related Docs:** `GEMINI.md`, `PROJECT_STATUS.md`, `docs/security/security-checklist.md`, `docs/testing/quality-assurance.md`

## 1. Role

Act as a Principal Laravel Software Architect and Clean Code Specialist in Google Gemini Antigravity IDE.

## 2. Objective

Refactor existing **Sales CRM** code to improve architectural alignment, type safety, readability, and maintainability while strictly preserving 100% of external behavior and passing all automated tests.

## 3. Required Context (Read Before Executing)

Before generating a plan or modifying any file, you **MUST** read and cross-reference:
- `GEMINI.md`
- `docs/architecture/backend-architecture.md`, `docs/architecture/frontend-architecture.md`, `docs/architecture/coding-standards.md`, `docs/architecture/design-patterns.md`
- `docs/adr/ADR-005-business-logic-organization.md`
- Target module specification in `docs/modules/<module>.md`

## 4. Instructions

1. Ensure full automated test coverage exists and passes (`php artisan test`) for the target code *before* beginning any refactoring. If tests are missing, write characterization tests first.
2. Extract business logic, transaction blocks, and multi-model writes out of Controllers into typed Invokable Actions (`app/Actions/`) or Domain Services (`app/Services/`).
3. Replace untyped associative array parameters with `readonly` PHP 8.3 DTOs (`app/Data/`) and replace magic status strings with backed Enums (`app/Enums/`).
4. Extract duplicated Blade markup into reusable `<x-ui.*>` or `<x-crm.*>` components styled with Tailwind CSS v4.
5. Do not alter database schemas, public route URIs, or business behavior during a pure refactoring task.

## 5. Execution Workflow

1. **Baseline Test Check:** Run `php artisan test` (and write characterization tests if coverage is missing).
2. **Identify Smells:** Locate fat controllers, duplicated queries, magic strings, or untyped arrays.
3. **Incremental Refactoring:** Extract DTOs, Enums, Actions, Scopes, and Blade components step-by-step.
4. **Verify Green Suite & Style:** Run `php artisan test` and `./vendor/bin/pint --test` after each extraction.

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

- Refactored, strictly typed PHP 8.3+ and Blade code adhering to `docs/architecture/design-patterns.md`.
- Passing test suite demonstrating zero behavioral regression.
- Updated documentation references if internal class locations changed.

## 10. Completion Criteria

The task is complete **ONLY** when:
- [ ] All code adheres strictly to `GEMINI.md` and `docs/architecture/coding-standards.md`.
- [ ] All automated tests (`php artisan test`) pass with zero errors or lazy-loading violations.
- [ ] Security checklist items in `docs/security/security-checklist.md` are satisfied.
- [ ] `PROJECT_STATUS.md`, `CHANGELOG.md`, and affected `docs/` files are synchronized with the changes.
