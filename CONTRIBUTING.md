# Contributing Guide & Engineering Workflow

**Last Updated:** 2026-11-19  
**Status:** `Active Engineering Standard`

## 1. Development Workflow Overview

Development on **Sale Fusion CRM** follows a documentation-first, test-verified workflow across PHP 8.3+, Laravel 13.x, MySQL 8.0+, Blade, Tailwind CSS v4, and Vanilla ES2022 JavaScript.

```mermaid
flowchart LR
    A["1. Review Module Spec & Schema"] --> B["2. Create Feature Branch"]
    B --> C["3. Write Migrations, Models & Policies"]
    C --> D["4. Implement Actions, Services, Controllers & Blade Views"]
    D --> E["5. Run Pint & PHPUnit Suite"]
    E --> F["6. Sync Module Docs, PROJECT_STATUS.md & CHANGELOG.md"]
    F --> G["7. Open Pull Request"]
```

## 2. Branching Strategy

- `main` — Production-ready, tagged releases only. Protected branch.
- `develop` — Integration branch for verified deliverables.
- `feature/<module>-<short-description>` — New CRM features (e.g., `feature/leads-atomic-conversion`).
- `fix/<module>-<bug-description>` — Bug fixes with accompanying regression tests.
- `docs/<topic>` — Documentation-only updates.

## 3. Commit Message Convention (Conventional Commits)

Format: `<type>(<scope>): <imperative summary>`

- `feat(leads): implement atomic lead conversion service and policy`
- `fix(quotations): prevent rounding drift on line item tax calculation`
- `test(deals): add cross-team IDOR authorization tests for stage movement`
- `docs(database): add composite index specification for follow_ups table`
- `refactor(pipeline): extract stage reordering logic into ReorderStagesAction`

## 4. Definition of Done (Pull Request Checklist)

Every feature or bugfix PR must satisfy all of the following before merge:

1. **Architecture Alignment:** Controllers remain thin; validation lives in `FormRequest` classes; domain logic resides in `app/Services/` or `app/Actions/`.
2. **Frontend Compliance:** Uses Blade components, Tailwind CSS v4 utility classes (`resources/css/app.css`), and Vanilla ES2022 JS. No unapproved JS frameworks.
3. **Database Safety:** Migrations include `down()` methods, appropriate foreign key constraints, and composite indexes matching query filters. Avoid N+1 queries (`Model::shouldBeStrict()` is enabled in non-production).
4. **Security Verification:** Every route enforces authentication, Policy authorization, team visibility scopes (`scopeVisibleTo()`), and CSRF protection.
5. **Automated Tests & Style:** Passes `vendor/bin/pint --test` and `php artisan test --compact`.
6. **Documentation Synchronization:** Updates implementation status in `PROJECT_STATUS.md`, affected `docs/` specs, and `CHANGELOG.md`.
