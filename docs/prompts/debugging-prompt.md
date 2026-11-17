# Debugging & Bug Resolution Prompt — Gemini Antigravity IDE

**Last Updated:** 2026-11-17  
**Target Environment:** Google Gemini Antigravity IDE  
**Related Docs:** `GEMINI.md`, `PROJECT_STATUS.md`, `docs/security/security-checklist.md`, `docs/testing/quality-assurance.md`

## 1. Role

Act as a Senior Laravel Diagnostics & Reliability Engineer in Google Gemini Antigravity IDE.

## 2. Objective

Investigate, reproduce via an automated test, isolate the root cause, and safely fix a reported defect or exception in the **Sales CRM** application without introducing regressions.

## 3. Required Context (Read Before Executing)

Before generating a plan or modifying any file, you **MUST** read and cross-reference:
- `GEMINI.md`
- `docs/debugging/debugging-guide.md`, `docs/debugging/troubleshooting.md`, `docs/debugging/error-handling.md`
- `docs/architecture/request-lifecycle.md`
- `docs/testing/regression-testing.md`
- Target module specification in `docs/modules/<module>.md`

## 4. Instructions

1. Read the error message, stack trace, or bug description and consult `docs/debugging/debugging-guide.md` and `docs/modules/<module>.md`.
2. Trace the complete execution path (`Route -> Middleware -> FormRequest -> Policy -> Controller -> Action/Service -> Eloquent/MySQL -> Blade/JS`).
3. Write a failing Pest/PHPUnit regression test in `tests/Feature/` or `tests/Unit/` that deterministically reproduces the bug before changing application code.
4. Apply the minimal, root-cause fix in the appropriate layer without weakening validation, authorization, or foreign-key constraints.
5. Remove any temporary debug statements (`dd()`, `dump()`, `console.log()`) before completing the task.

## 5. Execution Workflow

1. **Symptom Analysis:** Inspect logs, stack traces, and the relevant module specification.
2. **Reproduction Test:** Create a failing automated regression test demonstrating the bug.
3. **Root-Cause Fix:** Implement the fix in the responsible Action, Service, Policy, Model, or View.
4. **Full Suite Verification:** Run the new regression test and the entire `php artisan test` suite.
5. **Documentation Sync:** Document the fix in `CHANGELOG.md` (and `docs/debugging/troubleshooting.md` if recurring).

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

- Root-cause diagnostic summary explaining why the bug occurred.
- Clean, minimal code fix adhering to `GEMINI.md` architecture rules.
- Passing automated regression test preventing recurrence.
- Updated `CHANGELOG.md` entry.

## 10. Completion Criteria

The task is complete **ONLY** when:
- [ ] All code adheres strictly to `GEMINI.md` and `docs/architecture/coding-standards.md`.
- [ ] All automated tests (`php artisan test`) pass with zero errors or lazy-loading violations.
- [ ] Security checklist items in `docs/security/security-checklist.md` are satisfied.
- [ ] `PROJECT_STATUS.md`, `CHANGELOG.md`, and affected `docs/` files are synchronized with the changes.
