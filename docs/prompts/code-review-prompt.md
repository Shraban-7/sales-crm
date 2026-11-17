# Code Review Prompt — Gemini Antigravity IDE

**Last Updated:** 2026-11-17  
**Target Environment:** Google Gemini Antigravity IDE  
**Related Docs:** `GEMINI.md`, `PROJECT_STATUS.md`, `docs/security/security-checklist.md`, `docs/testing/quality-assurance.md`

## 1. Role

Act as a Principal Laravel Architect and Security-Focused Code Reviewer in Google Gemini Antigravity IDE.

## 2. Objective

Perform a rigorous architectural, security, performance, database, and coding-standard review of the target files or branch without introducing breaking changes, producing a prioritized findings report and exact remediation patches.

## 3. Required Context (Read Before Executing)

Before generating a plan or modifying any file, you **MUST** read and cross-reference:
- `GEMINI.md`
- `docs/architecture/coding-standards.md`, `docs/architecture/design-patterns.md`, `docs/architecture/backend-architecture.md`
- `docs/security/security-checklist.md`, `docs/security/authorization.md`, `docs/security/input-validation.md`
- `docs/database/database-optimization.md` and `docs/database/data-integrity.md`
- Relevant module specification in `docs/modules/<module>.md`

## 4. Instructions

1. Inspect all modified or target PHP, Blade, JavaScript, and migration files against `GEMINI.md` and `docs/architecture/coding-standards.md`.
2. Check for fat controller anti-patterns, missing `declare(strict_types=1);`, missing return types, or raw `$request->all()` usage.
3. Check for missing `$this->authorize()` calls, missing `->visibleTo($user)` query scopes (IDOR risk), or unescaped `{!! !!}` Blade outputs (XSS risk).
4. Check for N+1 lazy-loading queries in loops, missing composite indexes, missing `DB::transaction()` wrappers on multi-table writes, or `float` math on currency values.
5. Classify every finding by severity (`Critical`, `High`, `Medium`, `Low`) with exact file paths, line numbers, and concrete code fixes.

## 5. Execution Workflow

1. **Load Standards:** Read `GEMINI.md`, `docs/architecture/coding-standards.md`, and `docs/security/security-checklist.md`.
2. **Diff & File Inspection:** Analyze all target files across Architecture, Security, Database/Performance, Frontend, and Tests.
3. **Automated Verification:** Run `./vendor/bin/pint --test` and `php artisan test` if code exists.
4. **Report Generation:** Output a structured Markdown review table with severity, root cause, and exact refactored code snippets.

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

- Structured Code Review Report categorized by `Critical`, `High`, `Medium`, and `Low` findings.
- Exact file-and-line remediation diffs compliant with Laravel 12 and Tailwind CSS v4 standards.
- Verification checklist confirming whether the code is ready to merge.

## 10. Completion Criteria

The task is complete **ONLY** when:
- [ ] All code adheres strictly to `GEMINI.md` and `docs/architecture/coding-standards.md`.
- [ ] All automated tests (`php artisan test`) pass with zero errors or lazy-loading violations.
- [ ] Security checklist items in `docs/security/security-checklist.md` are satisfied.
- [ ] `PROJECT_STATUS.md`, `CHANGELOG.md`, and affected `docs/` files are synchronized with the changes.
