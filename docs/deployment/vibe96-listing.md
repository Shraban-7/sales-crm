# Vibe96 Marketplace Listing & Submission Package — Sale Fusion CRM

**Last Updated:** 2026-11-19  
**Target Platform:** [Vibe96 Marketplace (`vibe96.com`)](https://vibe96.com/) & [Vibe Studio (`studio.vibe96.com`)](https://studio.vibe96.com/)  
**Live / Demo Domain:** [`https://sales-crm.devshraban.com`](https://sales-crm.devshraban.com)  
**Package Archive:** `dist/sales-crm-vibe96.zip`  
**Cover Visual & Screenshots:** `public/images/vibe96-cover.jpg` (`public/images/vibe96-cover.svg`) + `docs/screenshots/` (`28` live UI captures)  
**Related Docs:** `readme.md`, `PROJECT_STATUS.md`, `CHANGELOG.md`, `docs/deployment/deployment-guide.md`, `docs/deployment/environment-variables.md`, `docs/deployment/production-checklist.md`

---

## 1. Marketplace Listing Metadata (Copy-Ready Upload Form Fields)

Use the fields below directly when completing the seller submission form on **Vibe96** (`vibe96.com` / `studio.vibe96.com`):

| Upload Field | Recommended Value |
| :--- | :--- |
| **Product Title** | **Sale Fusion CRM — Full-Lifecycle B2B/B2C Revenue, Pipeline & Quotation Platform (Laravel + Tailwind v4)** |
| **Short Tagline (≤ 140 chars)** | Turnkey B2B/B2C Sale Fusion CRM with Kanban pipelines, atomic lead conversion, versioned PDF quotations, team RBAC, and revenue forecasting. |
| **Demo URL** | `https://sales-crm.devshraban.com` |
| **Primary Category** | CRM & Sales Platforms (`CRM and E-commerce platforms` / `Business Administration & Micro-SaaS`) |
| **Secondary Category** | Project Management & Operations Tools / Full Web Apps with Admin Dashboards & Customer Portals |
| **Delivery Format** | Full Source Code Archive (`dist/sales-crm-vibe96.zip`) + Pre-Compiled Vite Assets + `18` Migrations & Demo Seeder + `28` UI Screenshots + Structured Architecture & Module Docs |
| **Tech Stack Tags** | `PHP 8.3`, `Laravel 13`, `MySQL 8.0`, `Tailwind CSS v4`, `Blade`, `Vanilla JS`, `Spatie Permission`, `mPDF`, `Vite`, `PHPUnit` |
| **License & Pricing Tier (Suggested)** | **Standard Commercial License:** `$149` (Single business deployment) <br> **Extended / Agency SaaS License:** `$349` (Unlimited client customizations & resale rights) <br> **Optional Vibe96 Expert Customization:** Available for ERP/Webhook/Payment gateway integrations |

---

## 2. Copy-Ready Marketplace Product Description

> Copy the markdown block below directly into the **Project Description / Overview** field on the Vibe96 seller portal.

### Overview & Problem Solved

Modern revenue teams outgrow spreadsheets quickly, yet enterprise CRM subscriptions charge steep per-seat fees while locking away data and workflow customization. **Sale Fusion CRM** is a production-ready, self-hostable B2B/B2C Customer Relationship Management application engineered on **Laravel 13 (PHP 8.3+)**, **MySQL 8.0+**, **Tailwind CSS v4**, and **Vanilla ES2022 JavaScript**.

It delivers the complete revenue operations lifecycle out of the box—from public web-to-lead capture (`/capture/lead`), HMAC-signed webhook ingestion (`POST /api/leads/capture`), and lead scoring to interactive Kanban deal pipelines, versioned quotations with manager discount approvals, signed public customer acceptance portals (`/q/{ulid}`), mPDF proposal generation, SLA follow-up cadences, multi-currency revenue analytics, and branded HTTP error pages (`401–503`).

### Ideal Use Cases

- **B2B & Enterprise Sales Teams:** Manage multi-stage deal pipelines, track rotting opportunities, enforce discount approval thresholds, and forecast weighted revenue separately per currency across multiple sales teams.
- **Agencies & Software Consultancies:** Deploy a white-label, zero-per-seat-fee CRM for clients with built-in team isolation, immutable audit logging, and customizable quotation PDF templates.
- **Micro-SaaS Founders & SaaS Builders:** Start from a strictly typed, tested Laravel codebase with 14 complete domain modules, 5-tier RBAC, 28 documented screenshots, and clear architectural specs ready to extend.

### Core Features Across All 14 Implemented Modules

1. **Authentication, Email 2FA & Branded Error Pages (`docs/security/authentication.md`):**
   - Split-screen sign-in UI (`/login`), rate-limited throttling, active-user enforcement middleware (`EnsureUserIsActive`), optional 6-digit emailed 2FA (`/settings/security` → `/two-factor`), and custom branded HTTP error pages (`401`, `402`, `403`, `404`, `419`, `429`, `500`, `503`).
2. **Users, Sales Teams & 5-Tier RBAC (`docs/modules/users-teams-and-permissions.md`):**
   - Powered by Spatie Laravel Permission and custom Eloquent Policies. Includes 5 roles (`Super Admin`, `Org Admin`, `Sales Manager`, `Sales Rep`, `Read-Only Analyst`), sales team monthly targets, atomic record reassignment (`ReassignUserRecordsAction`), and automatic `scopeVisibleTo()` query scoping (`own`, `team`, `all`).
3. **Immutable Audit Logs (`docs/modules/audit-logs.md`):**
   - Automatic before/after JSON state capture, actor attribution, IP address, and user-agent logging enforced as immutable via `ImmutableRecordException`.
4. **Companies & Account Hierarchy (`docs/modules/companies.md`):**
   - Parent/subsidiary hierarchy (`parent_company_id`), industry and size segmentation, billing/shipping profiles, and linked contacts/deals rollups.
5. **Contacts Directory (`docs/modules/contacts.md`):**
   - Decision-maker & Do-Not-Contact flags, department/job-title metadata, and multi-deal stakeholder associations.
6. **Lead Capture, Scoring & Atomic Conversion (`docs/modules/leads.md`):**
   - Public web-to-lead form (`/capture/lead`) with honeypot + consent, HMAC-SHA256 webhook (`POST /api/leads/capture`) with optional round-robin assignment, `0–100` scoring, duplicate detection/merge, and transactional conversion (`ConvertLeadToDealAction`) into Company + Contact + Deal.
7. **Customizable Sales Pipelines (`docs/modules/sales-pipeline.md`):**
   - Multi-pipeline support with ordered stages, win probabilities (`0%–100%`), deal rotting day thresholds, and stage colors.
8. **Deals & Opportunities (`docs/modules/deals-and-opportunities.md`):**
   - Interactive Kanban drag-and-drop (`/pipelines/kanban`) and list views (`/deals`), stage duration history (`deal_stage_histories`), weighted forecasts, and mandatory lost-reason tracking.
9. **Activities & Private Attachments (`docs/modules/activities-and-communications.md`):**
   - Polymorphic timeline logging for calls, emails, meetings, and notes across Leads, Contacts, Companies, and Deals, plus private non-public disk file attachments.
10. **Tasks & Reminders (`docs/modules/tasks-and-reminders.md`):**
    - Polymorphic deal tasks with priorities (`Low`, `Medium`, `High`, `Urgent`), filters (`Open`, `Overdue`, `Completed`), and scheduled reminder commands (`crm:dispatch-due-reminders`).
11. **Customer Follow-Up Cadences (`docs/modules/customer-follow-ups.md`):**
    - Bucketed urgency views (`Overdue`, `Today`, `Upcoming`) with multi-channel tracking, outcome notes, inline next follow-up scheduling, and hourly escalation commands (`crm:escalate-overdue-follow-ups`).
12. **Quotations, Discount Approvals & Public Customer Portal (`docs/modules/quotations-and-proposals.md`):**
    - Dynamic JS calculator paired with server-side `DECIMAL(15,2)` recalculation, discount threshold approval gate (`CRM_QUOTE_DISCOUNT_THRESHOLD`), mPDF email delivery (`QuotationDeliveryMail`), daily expiry command (`crm:expire-quotations`), and ULID signed client links (`/q/{ulid}`).
13. **In-App & Email Notifications (`docs/modules/notifications.md`):**
    - Navbar notification bell SVG with live unread badge (`99+` cap) and user-configurable In-App / Email channel toggles (`/notifications`).
14. **Reports, Revenue Forecasting & CSV Import/Export (`docs/modules/reports-and-analytics.md`, `docs/modules/import-and-export.md`):**
    - Cached scoped reports strictly separating totals by currency, funnel stage bars, rep win leaderboard, lead-source conversion rates, and CSV formula sanitization (`CsvSanitizer`) across import/export jobs.

### Security & Architecture Highlights

- **Defense-in-Depth Authorization:** Every route enforces `FormRequest::authorize()`, Laravel `Policy` checks, and `scopeVisibleTo()` row scoping to eliminate cross-team IDOR access.
- **Strict Type Safety & Hygienes:** `declare(strict_types=1);` everywhere, explicit `$fillable` arrays on all models (`$guarded = []` prohibited), escaped Blade `{{ }}` output, CSRF tokens, `Model::shouldBeStrict(! app()->isProduction())`, and zero hardcoded credentials on UI pages.

---

## 3. Buyer Live Demo Guide & Seeded Demo Credentials

Provide the credentials below in the **Demo URL & Login Credentials** section of the Vibe96 upload form so reviewers and prospective buyers can test every role level at `https://sales-crm.devshraban.com`.

### Demo Accounts Matrix

> **Seeded Password Note:**
> - **Default (`DemoCrmSeeder`):** Every demo account below uses password **`password`** (`database/seeders/DemoCrmSeeder.php`). For public internet demos, update seeded passwords cleanly before launch.

| Role | Email | Password | Team Scope | Recommended Reviewer Test Flow |
| :--- | :--- | :--- | :--- | :--- |
| **Super Admin** | `superadmin@salescrm.test` | `password` | Global (All Teams) | Full access: users, teams, pipelines, RBAC, analytics, and `/settings/audit-logs`. |
| **Org Admin** | `admin@salescrm.test` | `password` | Global (All Teams) | VP of RevOps view: cross-team analytics, imports/exports, user reassignment. |
| **Sales Manager** | `manager@salescrm.test` | `password` | Enterprise Sales Team | Team leader (`Tariq Rahman`): manage Enterprise deals and approve `> 15%` quote discounts. |
| **Sales Rep** | `rep@salescrm.test` | `password` | Enterprise Sales Team | Account Executive (`Nadia Karim`): convert `Kamrul Hasan`, drag cards on `/pipelines/kanban`, draft quotes. |
| **Read-Only Analyst** | `analyst@salescrm.test` | `password` | Global Read-Only | Inspect `/` and `/reports`; verify mutations and `/settings/users` return `403`. |
| **SMB Sales Rep** | `smbrep@salescrm.test` | `password` | SMB Inbound Team | Verify cross-team isolation—cannot access Enterprise Sales leads or deals. |

### 5-Minute Guided Walkthrough for Buyers & Vibe96 Reviewers

1. **Executive Dashboard & Reports (`/` & `/reports`):** Sign in as `superadmin@salescrm.test` or `analyst@salescrm.test` to inspect per-currency pipeline totals, stage conversion bars, and leaderboard metrics.
2. **Lead Capture & Atomic Conversion (`/capture/lead` & `/leads`):** Sign in as `rep@salescrm.test`, open **Kamrul Hasan — Apex Textiles BD** (`score: 82`), and click **Convert to Deal** to atomically create Company, Contact, and Deal.
3. **Interactive Kanban Board (`/pipelines/kanban`):** Drag deals between **B2B Enterprise Pipeline** stages (`Prospecting` → `Qualification` → `Solution Demo` → `Proposal / Quote Sent` → `Negotiation` → `Closed Won / Lost`) and check `DealStageHistory` on `/deals/{id}`.
4. **Quotations, Manager Approval & Public Buyer Portal (`/quotations`):** Open **`QT-2026-00001`** (`BDT 650,000.00`), preview **Print View**, and test **Customer Link** (`/q/{ulid}`). Create a draft with `> 15%` discount as `rep@salescrm.test` to verify the approval gate, then approve as `manager@salescrm.test`.
5. **Audit Trail & Security (`/settings/audit-logs` & `/settings/security`):** Sign in as `superadmin@salescrm.test` to verify immutable JSON audit logs and optional email 2FA settings.

---

## 4. Step-by-Step Buyer Installation & Deployment Guide

### System Prerequisites

- **PHP:** `8.3+` with `bcmath`, `ctype`, `fileinfo`, `gd`, `intl`, `mbstring`, `openssl`, `pdo_mysql`, `xml`, `zip`
- **Database:** MySQL `8.0+` (`InnoDB`, `utf8mb4_unicode_ci`)
- **Composer:** `2.6+`
- **Node.js & NPM:** Node `20 LTS+` (optional when using pre-compiled `public/build/` assets inside `dist/sales-crm-vibe96.zip`)

### Quick Local Setup

```bash
unzip sales-crm-vibe96.zip -d sales-crm && cd sales-crm
composer install --no-interaction --prefer-dist --optimize-autoloader
cp .env.example .env
php artisan key:generate
# Configure DB_DATABASE, DB_USERNAME, DB_PASSWORD in .env, then:
php artisan migrate:fresh --seed
php artisan serve
```

---

## 5. Vibe96 Automated Audit ("Warden") Readiness Summary

| Audit Dimension | Sale Fusion CRM Verification Evidence | Status |
| :--- | :--- | :--- |
| **1. Zero Secrets & Clean Package Hygiene** | `.env`, `.git/`, `vendor/`, `node_modules/`, internal AI/audit files (`AGENTS.md`, `CLAUDE.md`, `GEMINI.md`, `project-discovery-report.md`, `report.md`, `docs/CHANGELOG.md`, `docs/prompts/`), logs, and sqlite artifacts are excluded by `scripts/package-vibe96.ps1`. | **PASS** |
| **2. Strict Type Safety & Code Formatting** | Every PHP file declares `declare(strict_types=1);` with typed signatures. `vendor/bin/pint --test` passes with `0` issues. | **PASS** |
| **3. Environment Variable Governance** | Zero `env()` calls outside `config/*.php`; `php artisan config:cache` works cleanly in production. | **PASS** |
| **4. Mass-Assignment & Input Validation** | Every write endpoint validates via dedicated `FormRequest` classes; all models declare explicit `$fillable` arrays. | **PASS** |
| **5. Multi-Team IDOR Prevention** | Controllers enforce Laravel `Policy` gates + `scopeVisibleTo()` row scoping (`own`, `team`, `all`). | **PASS** |
| **6. Database Schema & Financial Precision** | `18` ordered migrations with explicit foreign keys, composite indexes, `SoftDeletes`, and `DECIMAL(15,2)` monetary columns. | **PASS** |
| **7. Transactional Integrity & N+1 Guard** | Multi-table workflows run inside `DB::transaction()`; `Model::shouldBeStrict(! app()->isProduction())` prevents lazy-loading regressions. | **PASS** |
| **8. Automated Feature Test Suite** | `php artisan test --compact` passes `34` tests (`407` assertions), including all 28 views rendered in `AllPagesRenderTest` and all 8 error templates in `ErrorPagesTest`. | **PASS** |
