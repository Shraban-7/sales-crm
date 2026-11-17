# Security & Authorization Audit Prompt — Gemini Antigravity IDE

**Last Updated:** 2026-11-17  
**Target Environment:** Google Gemini Antigravity IDE  
**Related Docs:** `GEMINI.md`, `PROJECT_STATUS.md`, `docs/security/security-checklist.md`, `docs/testing/quality-assurance.md`

## 1. Role

Act as a Principal Application Security Engineer and Laravel Penetration Tester in Google Gemini Antigravity IDE.

## 2. Objective

Perform a comprehensive security audit of the **Sales CRM** codebase (or a target module) covering Authentication, RBAC Authorization, IDOR/Team Isolation, Mass Assignment, XSS, CSRF, SQL Injection, File Uploads, CSV Injection, and Audit Logging.

## 3. Required Context (Read Before Executing)

Before generating a plan or modifying any file, you **MUST** read and cross-reference:
- `GEMINI.md`
- `docs/security/security-policy.md`, `docs/security/authentication.md`, `docs/security/authorization.md`, `docs/security/role-permission-matrix.md`
- `docs/security/data-protection.md`, `docs/security/input-validation.md`, `docs/security/file-upload-security.md`, `docs/security/security-checklist.md`
- `docs/testing/authorization-testing.md`

## 4. Instructions

1. Audit every registered route in `routes/web.php` to verify `auth`, `active_user`, and appropriate `throttle:` middleware.
2. Verify every Controller action calls `$this->authorize()` and every list/report/export query applies `->visibleTo($request->user())`.
3. Verify every `FormRequest` scopes foreign key rules (`company_id`, `contact_id`, `deal_id`, `assigned_to_id`) to `$this->user()->team_id`.
4. Scan all Eloquent models for forbidden `$guarded = []` or exposed sensitive attributes in `$hidden`.
5. Scan all Blade templates for unescaped `{!! !!}` and all Eloquent queries for raw string interpolation in `whereRaw`/`selectRaw`/`orderByRaw`.
6. Verify file uploads use the `private` storage disk and CSV exports neutralize leading `=`, `+`, `-`, `@` formula characters.

## 5. Execution Workflow

1. **Load Security Baseline:** Read all 8 files in `docs/security/`.
2. **Route & Policy Audit:** Cross-check `routes/web.php`, `app/Http/Controllers/`, and `app/Policies/` against `docs/security/role-permission-matrix.md`.
3. **Injection & Upload Audit:** Inspect `app/Http/Requests/`, `app/Models/`, `resources/views/`, and file/import services.
4. **Remediation & IDOR Tests:** Patch any identified vulnerabilities and write automated security tests in `tests/Feature/Security/`.

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

- Comprehensive Security Audit Report mapped against `docs/security/security-checklist.md`.
- Remediation patches for any identified vulnerabilities.
- Passing authorization and IDOR security test suite (`tests/Feature/Security/`).

## 10. Completion Criteria

The task is complete **ONLY** when:
- [ ] All code adheres strictly to `GEMINI.md` and `docs/architecture/coding-standards.md`.
- [ ] All automated tests (`php artisan test`) pass with zero errors or lazy-loading violations.
- [ ] Security checklist items in `docs/security/security-checklist.md` are satisfied.
- [ ] `PROJECT_STATUS.md`, `CHANGELOG.md`, and affected `docs/` files are synchronized with the changes.
