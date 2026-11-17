# Project Discovery Report — Sales CRM

**Last Updated:** 2026-11-17  
**Project Phase:** Pre-Implementation / Greenfield Specification  
**Implementation Status:** `Missing / Proposed (Greenfield)`

## 1. Executive Summary

This report documents the Phase 1 repository inspection for the **Sales CRM** engineering project. Based on repository inspection and project owner confirmation on **2026-11-17**, this system is a **future greenfield project** with no existing PHP, Blade, JavaScript, migration, or configuration source files committed yet.

All architectural blueprints, database schemas, CRM domain modules, security matrices, testing suites, and operational workflows documented across this engineering knowledge base are therefore explicitly classified as **Proposed** (`0% Implemented`). This specification serves as the authoritative, zero-ambiguity blueprint for initializing and building the application in **Google Gemini Antigravity IDE**.

## 2. Repository Inspection Findings

| Artifact / Directory | Expected Location | Current Status | Verification Notes |
| :--- | :--- | :--- | :--- |
| Composer Manifest | `composer.json` | `Missing` | Greenfield repository; awaiting `composer create-project laravel/laravel` initialization |
| Node / Vite Manifest | `package.json`, `vite.config.js` | `Missing` | Awaiting Vite + [Tailwind CSS v4](https://tailwindcss.com/docs/upgrade-guide) (`@tailwindcss/vite`) setup |
| Application Core | `app/Models`, `app/Http`, `app/Services` | `Missing` | Proposed modular service + action architecture documented in `docs/architecture/` |
| Database Migrations | `database/migrations/` | `Missing` | 18 core CRM tables proposed and specified in `docs/database/migrations-and-indexes.md` |
| Frontend Views | `resources/views/` | `Missing` | Blade + Tailwind CSS v4 + Vanilla JS / Alpine.js architecture specified in `docs/architecture/frontend-architecture.md` |
| Automated Tests | `tests/Feature`, `tests/Unit` | `Missing` | [Pest PHP](https://pestphp.com/docs/writing-tests) / PHPUnit test suite specified in `docs/testing/` |

## 3. Target Technology Stack Baseline

| Layer | Technology | Target Version / Standard | Reference Documentation |
| :--- | :--- | :--- | :--- |
| Backend Language | PHP | PHP 8.3+ / 8.4 (`declare(strict_types=1);`) | PSR-12 / PER Coding Style |
| Application Framework | Laravel | Laravel 12.x | [Laravel 12 Configuration](https://laravel.com/framework/docs/12.x/configuration) |
| Database & ORM | MySQL + Eloquent ORM | MySQL 8.0+ / InnoDB (`utf8mb4_unicode_ci`) | [Laravel 12 API Docs](https://api.laravel.com/docs/12.x/index.html) |
| Frontend Templates | Laravel Blade | Anonymous & Class-based Blade Components | `docs/architecture/frontend-architecture.md` |
| Styling Engine | Tailwind CSS | Tailwind CSS v4 (`@import "tailwindcss";` + `@theme`) | [Tailwind CSS v4 Upgrade Guide](https://tailwindcss.com/docs/upgrade-guide) |
| Client Interactivity | Vanilla JS / Alpine.js | ES2022 Modules via Vite; Alpine.js only when state warrants | `docs/architecture/frontend-architecture.md` |
| RBAC & Permissions | Spatie Permission | `spatie/laravel-permission` + Laravel Policies | [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission/v8/installation-laravel) |
| Testing Framework | Pest PHP / PHPUnit | Pest v3/v4 or PHPUnit 11 | [Pest PHP Documentation](https://pestphp.com/docs/writing-tests) |
| Primary AI IDE | Gemini Antigravity | Antigravity Agent Workflow via `GEMINI.md` | `GEMINI.md`, `docs/prompts/` |

## 4. Module Implementation Matrix

| Module ID | Module Name | Specification File | Implemented | Partially Implemented | Proposed |
| :--- | :--- | :--- | :--- | :--- | :--- |
| MOD-01 | Users, Teams & Permissions | `docs/modules/users-teams-and-permissions.md` | None | None | Full RBAC, Teams, Territories, Policies |
| MOD-02 | Leads Management | `docs/modules/leads.md` | None | None | Lead capture, scoring, qualification, atomic conversion |
| MOD-03 | Contacts Management | `docs/modules/contacts.md` | None | None | Decision-maker profiles, multi-company roles, communication opt-ins |
| MOD-04 | Companies / Accounts | `docs/modules/companies.md` | None | None | B2B account hierarchy, billing/shipping addresses, revenue tiers |
| MOD-05 | Sales Pipeline | `docs/modules/sales-pipeline.md` | None | None | Multi-pipeline support, ordered stages, win probabilities, rotting rules |
| MOD-06 | Deals & Opportunities | `docs/modules/deals-and-opportunities.md` | None | None | Deal valuation, stage transitions, win/loss reasons, line items |
| MOD-07 | Tasks & Reminders | `docs/modules/tasks-and-reminders.md` | None | None | Polymorphic tasks, priority SLA, scheduled reminders |
| MOD-08 | Activities & Communications | `docs/modules/activities-and-communications.md` | None | None | Polymorphic timeline logs (calls, emails, meetings, notes) |
| MOD-09 | Customer Follow-Ups | `docs/modules/customer-follow-ups.md` | None | None | Cadence scheduling, overdue escalation, next-action enforcement |
| MOD-10 | Quotations & Proposals | `docs/modules/quotations-and-proposals.md` | None | None | Versioned quotes, tax/discount math, PDF export, deal sync |
| MOD-11 | Reports & Analytics | `docs/modules/reports-and-analytics.md` | None | None | Funnel conversion, weighted forecast, rep leaderboard, velocity |
| MOD-12 | Notifications | `docs/modules/notifications.md` | None | None | Database & queued mail notifications, user preference matrix |
| MOD-13 | Import & Export | `docs/modules/import-and-export.md` | None | None | Queued CSV/XLSX chunked imports, validation error reports, exports |
| MOD-14 | Audit Logs | `docs/modules/audit-logs.md` | None | None | Immutable entity diff tracking, IP/User-Agent metadata, retention |

## 5. Identified Open Business Questions & Defaults Applied

To prevent ambiguity during initial scaffolding while respecting strict anti-invention guardrails, the following architectural defaults are marked as **Proposed** and flagged for owner confirmation prior to Phase 1 coding:

1. **Multi-Tenancy vs. Single-Tenant Organization**: Defaulted to a **Single-Tenant Enterprise Instance with Team/Department Data Isolation** (`team_id` and `owner_id` scoping). If SaaS multi-tenancy is required later, `tenant_id` scoping can be added.
2. **Default Currency & Tax Rules**: Defaulted to configurable ISO-4217 currency (`BDT` / `USD` with `DECIMAL(15,2)` precision) and line-item tax/discount support in Quotations.
3. **PDF Generation Engine for Quotations**: Proposed `barryvdh/laravel-dompdf` or headless Chromium printing in Phase 3, isolated behind a `QuotationPdfGeneratorInterface` contract.
4. **Testing Runner Selection**: Documented examples support both **Pest PHP** and **PHPUnit**, matching standard [Laravel 12 Configuration](https://laravel.com/framework/docs/12.x/configuration) defaults.

## 6. Cross-References

- Root AI Agent Directives: `GEMINI.md`
- Project Roadmap & Status: `PROJECT_STATUS.md`
- System Architecture: `docs/architecture/system-architecture.md`
- Database Design & ERD: `docs/database/database-design.md`, `docs/database/erd.md`
