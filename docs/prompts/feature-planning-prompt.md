# Feature Planning & Architecture Specification Prompt (No Code Changes) — Gemini Antigravity IDE

**Last Updated:** 2026-11-17  
**Target Environment:** Google Gemini Antigravity IDE  
**Related Docs:** `GEMINI.md`, `PROJECT_STATUS.md`, `docs/security/security-checklist.md`, `docs/testing/quality-assurance.md`

## 1. Role

Act as a Principal Software Architect, Product Systems Analyst, and Database Designer in Google Gemini Antigravity IDE.

## 2. Objective

Analyze a new requirement or CRM feature request and produce a complete, decision-ready technical implementation plan and Markdown specification update **without modifying any application source code**.

## 3. Required Context (Read Before Executing)

Before generating a plan or modifying any file, you **MUST** read and cross-reference:
- `GEMINI.md` and `PROJECT_STATUS.md`
- `docs/architecture/system-architecture.md` and `docs/architecture/backend-architecture.md`
- `docs/database/database-design.md` and `docs/database/erd.md`
- `docs/security/role-permission-matrix.md`
- Relevant module specs in `docs/modules/`

## 4. Instructions

1. **STRICT CONSTRAINT:** Do NOT create or modify any `.php`, `.blade.php`, `.js`, `.css`, or configuration files during this planning session.
2. Read the existing architecture, database schema, and related module specifications to ensure the proposed plan integrates cleanly without duplicating entities.
3. Identify any ambiguous business rules or missing requirements and list concrete clarifying questions if needed.
4. Produce a step-by-step blueprint covering: (a) Schema & Composite Index changes, (b) Eloquent Models, Enums & Relationships, (c) DTOs, Domain Actions & Services, (d) FormRequest validation & RBAC Policy rules, (e) Routes, Controllers & Blade/Tailwind CSS v4 UI components, (f) Edge cases & concurrency hazards, and (g) Pest/PHPUnit test scenarios.

## 5. Execution Workflow

1. **Read Docs & Inspect Context (Read-Only):** Review `GEMINI.md`, `docs/database/`, and `docs/modules/`.
2. **Analyze Impact:** Map affected tables, models, policies, and UI views.
3. **Draft Technical Plan:** Write the complete architectural and task breakdown with Mermaid workflow/sequence diagrams.
4. **Update Spec Docs (If Requested):** Update or create the corresponding Markdown specification under `docs/`.

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

- Comprehensive Technical Implementation Plan (Markdown only; zero code changes).
- Database schema & index specification with Mermaid diagrams.
- RBAC permission, validation, edge-case, and automated test matrix.

## 10. Completion Criteria

The task is complete **ONLY** when:
- [ ] All code adheres strictly to `GEMINI.md` and `docs/architecture/coding-standards.md`.
- [ ] All automated tests (`php artisan test`) pass with zero errors or lazy-loading violations.
- [ ] Security checklist items in `docs/security/security-checklist.md` are satisfied.
- [ ] `PROJECT_STATUS.md`, `CHANGELOG.md`, and affected `docs/` files are synchronized with the changes.
