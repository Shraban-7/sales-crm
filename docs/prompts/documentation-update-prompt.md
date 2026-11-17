# Documentation Synchronization Prompt — Gemini Antigravity IDE

**Last Updated:** 2026-11-17  
**Target Environment:** Google Gemini Antigravity IDE  
**Related Docs:** `GEMINI.md`, `PROJECT_STATUS.md`, `docs/security/security-checklist.md`, `docs/testing/quality-assurance.md`

## 1. Role

Act as a Principal Technical Writer and Software Architect in Google Gemini Antigravity IDE.

## 2. Objective

Synchronize the **Sales CRM** Markdown engineering documentation (`PROJECT_STATUS.md`, `CHANGELOG.md`, `docs/modules/`, `docs/database/`, `docs/architecture/`, `docs/security/`) with actual codebase changes.

## 3. Required Context (Read Before Executing)

Before generating a plan or modifying any file, you **MUST** read and cross-reference:
- `GEMINI.md`
- `PROJECT_STATUS.md` and `CHANGELOG.md`
- `docs/database/database-design.md`, `docs/database/migrations-and-indexes.md`, `docs/database/erd.md`
- All affected module specifications in `docs/modules/*.md`

## 4. Instructions

1. Inspect `git status`, `git diff`, `database/migrations/`, `app/Models/`, `app/Http/Controllers/`, and `tests/` to verify what has actually been implemented or modified.
2. Update `PROJECT_STATUS.md` module status badges accurately (`Proposed` -> `Partially Implemented` or `Implemented` only when verified by code and tests).
3. In affected `docs/modules/<module>.md` files, move completed features from `Proposed Functionality` to `Existing / Implemented Functionality` and update the `Last Updated` date.
4. If migrations or relationships changed, update `docs/database/database-design.md`, `docs/database/migrations-and-indexes.md`, `docs/database/database-relationships.md`, and the Mermaid diagrams in `docs/database/erd.md`.
5. Record a clear entry under `[Unreleased]` in `CHANGELOG.md`.

## 5. Execution Workflow

1. **Audit Codebase vs Docs:** Compare actual source files and migrations against `PROJECT_STATUS.md` and `docs/`.
2. **Update Module & Database Docs:** Update status badges, feature lists, schema tables, and Mermaid diagrams.
3. **Update Root Logs:** Update `PROJECT_STATUS.md` and `CHANGELOG.md`.
4. **Link & Consistency Check:** Verify all cross-document Markdown references and Mermaid blocks are valid.

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

- Synchronized Markdown files across root and `docs/` with updated `Last Updated` timestamps.
- Accurate `Implemented` vs `Partially Implemented` vs `Proposed` status tracking.
- Updated `CHANGELOG.md` entry.

## 10. Completion Criteria

The task is complete **ONLY** when:
- [ ] All code adheres strictly to `GEMINI.md` and `docs/architecture/coding-standards.md`.
- [ ] All automated tests (`php artisan test`) pass with zero errors or lazy-loading violations.
- [ ] Security checklist items in `docs/security/security-checklist.md` are satisfied.
- [ ] `PROJECT_STATUS.md`, `CHANGELOG.md`, and affected `docs/` files are synchronized with the changes.
