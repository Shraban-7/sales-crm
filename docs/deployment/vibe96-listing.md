# Vibe96 Marketplace Listing & Submission Package — Sales CRM

**Last Updated:** 2026-11-18  
**Target Platform:** [Vibe96 Marketplace (`vibe96.com`)](https://vibe96.com/) & [Vibe Studio (`studio.vibe96.com`)](https://studio.vibe96.com/)  
**Package Archive:** `dist/sales-crm-vibe96.zip`  
**Cover Visual:** `public/images/vibe96-cover.jpg` (or `public/images/vibe96-cover.svg`)  
**Related Docs:** `readme.md`, `PROJECT_STATUS.md`, `docs/deployment/deployment-guide.md`, `docs/deployment/environment-variables.md`, `docs/deployment/production-checklist.md`

---

## 1. Marketplace Listing Metadata (Copy-Ready Upload Form Fields)

Use the fields below directly when completing the seller submission form on **Vibe96** (`vibe96.com` / `studio.vibe96.com`):

| Upload Field | Recommended Value |
| :--- | :--- |
| **Product Title** | **Sales CRM — Full-Lifecycle B2B/B2C Revenue, Pipeline & Quotation Platform (Laravel + Tailwind v4)** |
| **Short Tagline (≤ 140 chars)** | Turnkey B2B/B2C Sales CRM with Kanban pipelines, atomic lead conversion, versioned PDF quotations, team RBAC, and revenue forecasting. |
| **Primary Category** | CRM & Sales Platforms (`CRM and E-commerce platforms` / `Business Administration & Micro-SaaS`) |
| **Secondary Category** | Project Management & Operations Tools / Full Web Apps with Admin Dashboards & Customer Portals |
| **Delivery Format** | Full Source Code Archive (`dist/sales-crm-vibe96.zip`) + Pre-Compiled Vite Assets + Database Migrations & Demo Seeder + 60+ Engineering Docs |
| **Tech Stack Tags** | `PHP 8.3`, `Laravel`, `MySQL 8.0`, `Tailwind CSS v4`, `Blade`, `Alpine.js`, `Vanilla JS`, `Spatie Permission`, `mPDF`, `Vite`, `PHPUnit` |
| **AI Coding Lineage** | Built & verified with Google Gemini Antigravity IDE (`GEMINI.md` governance + 12 specialized prompt templates in `docs/prompts/`) |
| **License & Pricing Tier (Suggested)** | **Standard Commercial License:** `$149` (Single business deployment) <br> **Extended / Agency SaaS License:** `$349` (Unlimited client customizations & resale rights) <br> **Optional Vibe96 Expert Customization:** Available for ERP/Webhook/Payment gateway integrations |

---

## 2. Copy-Ready Marketplace Product Description

> Copy the markdown block below directly into the **Project Description / Overview** field on the Vibe96 seller portal.

### Overview & Problem Solved

Modern revenue teams outgrow spreadsheets quickly, yet enterprise CRM subscriptions charge steep per-seat fees while locking away data and workflow customization. **Sales CRM** is a production-ready, self-hostable B2B/B2C Customer Relationship Management application engineered on **Laravel (PHP 8.3+)**, **MySQL 8.0+**, **Tailwind CSS v4**, and **Alpine.js / Vanilla ES2022 JavaScript**.

It delivers the complete revenue operations lifecycle out of the box—from public web-to-lead capture, HMAC-signed webhook ingestion, and lead scoring to interactive Kanban deal pipelines, versioned quotations with manager discount approvals, signed public customer acceptance portals, mPDF proposal generation, SLA follow-up cadences, and multi-currency revenue analytics.

### Ideal Use Cases

- **B2B & Enterprise Sales Teams:** Manage multi-stage deal pipelines, track rotting opportunities, enforce discount approval thresholds, and forecast weighted revenue across multiple sales teams.
- **Agencies & Software Consultancies:** Deploy a white-label, zero-per-seat-fee CRM for clients with built-in team isolation, audit logging, and customizable quotation PDF templates.
- **Micro-SaaS Founders & Vibe Builders:** Start from a strictly typed, Warden-ready Laravel codebase with 14 complete domain modules, 5-tier RBAC, and 60+ architecture/security specifications ready to extend inside Vibe Studio or Antigravity IDE.

### Core Features Across All 14 Implemented Modules

1. **Foundation & Authentication (`docs/security/authentication.md`):**
   - Hardened session authentication, password hashing, rate-limited login throttling, active-user enforcement middleware (`EnsureActiveUser`), and profile management.
2. **Users, Sales Teams & 5-Tier RBAC (`docs/modules/users-teams-and-permissions.md`):**
   - Powered by Spatie Laravel Permission and custom Eloquent Policies. Includes 5 pre-configured roles (`Super Admin`, `Org Admin`, `Sales Manager`, `Sales Rep`, `Read-Only Analyst`), sales team hierarchies, monthly revenue targets, and automatic `VisibleTo` query scopes (`own`, `team`, `all`).
3. **Immutable Audit Logs (`docs/modules/audit-logs.md`):**
   - Automatic before/after JSON state capture, actor attribution, IP address, and user-agent logging for all security-sensitive mutations (lead conversion, deal stage moves, discount approvals, permission changes, and data exports).
4. **Companies & Account Hierarchy (`docs/modules/companies.md`):**
   - Parent/subsidiary company hierarchy, industry and size segmentation, annual revenue tracking, billing/shipping profiles, and linked contacts/deals rollups.
5. **Contacts Directory (`docs/modules/contacts.md`):**
   - Primary decision-maker flags, department/job-title indexing, communication consent preferences, and multi-deal stakeholder role associations (`Economic Buyer`, `Technical Evaluator`, `Champion`).
6. **Lead Capture, Scoring & Atomic Conversion (`docs/modules/leads.md`):**
   - Public web-to-lead form, HMAC-verified JSON webhook endpoint (`/api/v1/webhooks/leads`) with optional round-robin rep assignment, lead qualification scoring (0–100), duplicate detection, and single-click transactional conversion (`ConvertLeadToDealAction`) into Company + Contact + Deal.
7. **Customizable Sales Pipelines (`docs/modules/sales-pipeline.md`):**
   - Multi-pipeline support with ordered stages, default win probabilities (`0%–100%`), configurable deal rotting day thresholds, and color-coded stage badges.
8. **Deals & Opportunities (`docs/modules/deals-and-opportunities.md`):**
   - Interactive Kanban board and filterable list views, stage transition history (`deal_stage_histories`) tracking time-in-stage, weighted value calculations, and structured Won/Lost reason analysis.
9. **Activities & Communication Timeline (`docs/modules/activities-and-communications.md`):**
   - Polymorphic timeline logging for inbound/outbound calls, emails, meetings, and internal notes across Leads, Contacts, Companies, and Deals—automatically updating `last_activity_at` timestamps.
10. **Tasks & Reminders (`docs/modules/tasks-and-reminders.md`):**
    - Polymorphic task assignments with priority badges (`Low`, `Medium`, `High`, `Urgent`), due-date filters, team member delegation, and automated reminder notifications.
11. **Customer Follow-Up Cadences (`docs/modules/customer-follow-ups.md`):**
    - Scheduled follow-up tracking across Call, Email, WhatsApp, LinkedIn, and In-Person channels with overdue SLA highlighting and quick outcome completion.
12. **Quotations, Discount Approvals & Public Customer Portal (`docs/modules/quotations-and-proposals.md`):**
    - Dynamic line-item calculator (`DECIMAL(15,2)` precision), configurable discount approval workflow (auto-flags quotes exceeding `CRM_QUOTE_DISCOUNT_THRESHOLD` for Sales Manager approval), versioned revisions, server-side PDF rendering via `mpdf/mpdf`, and ULID-based signed public links allowing clients to accept or reject proposals online.
13. **In-App & Email Notifications (`docs/modules/notifications.md`):**
    - Real-time notification bell and drawer for lead assignments, deal stage updates, quote approvals/acceptances, and overdue tasks/follow-ups.
14. **Reports, Revenue Forecasting & CSV Import/Export (`docs/modules/reports-and-analytics.md`, `docs/modules/import-and-export.md`):**
    - Pipeline funnel conversion charts, weighted vs. committed revenue forecasts, rep win-rate leaderboards, activity velocity metrics, and chunked CSV import/export jobs with row-level validation reporting.

### Security & RBAC Highlights

- **Defense-in-Depth Authorization:** Every controller endpoint enforces both a dedicated `FormRequest::authorize()` check and a model `Policy` gate, paired with `scopeVisibleTo(Builder $query, User $user)` on all list/search queries to eliminate Cross-Team Insecure Direct Object Reference (IDOR) vulnerabilities.
- **Strict Input & Output Hygiene:** 100% `FormRequest` validation whitelisting, explicit `$fillable` arrays on all 18 Eloquent models (`$guarded = []` is strictly prohibited), escaped Blade `{{ }}` rendering, and CSRF protection on all mutations.
- **Webhook & Public Link Cryptography:** Inbound lead webhooks verify `X-CRM-Signature` HMAC-SHA256 headers; public quotation portals use expiring signed URLs and non-enumerable ULID tokens.

### Architecture Highlights

- **Strict Layered Laravel Architecture:** Thin resource/action controllers (≤ 5 public actions per controller) delegate to single-purpose Action classes (`app/Actions/`) and transactional Domain Services (`app/Services/`).
- **PHP 8.3+ Strict Typing & Backed Enums:** Every PHP file declares `declare(strict_types=1);` with explicit parameter/return types and 13 backed `enum` classes (`app/Enums/`) for statuses, priorities, sources, and channels.
- **Financial & Relational Integrity:** All monetary columns use `DECIMAL(15, 2)` (never `FLOAT`), multi-table writes run inside `DB::transaction()`, and `Model::preventLazyLoading(! app()->isProduction())` prevents N+1 queries.
- **Zero-Bloat Frontend:** Server-rendered Laravel Blade components styled with **Tailwind CSS v4** (`@import "tailwindcss";` and `@theme` tokens via `@tailwindcss/vite`) and progressive **Alpine.js / Vanilla ES2022 JS**—no heavy SPA build complexity.

---

## 3. Buyer Live Demo Guide & Seeded Demo Credentials

Provide the credentials below in the **Demo URL & Login Credentials** section of the Vibe96 upload form so Vibe96 QA reviewers and prospective buyers can test every role level immediately.

### Demo Accounts Matrix

> **Note on Demo Passwords:**
> - **Hosted Live Demo Environment (Vibe96 Reviewer / Buyer Demo):** Password for all demo accounts is **`Password@12345`**
> - **Local `DemoCrmSeeder` Default (`php artisan migrate:fresh --seed`):** Defaults to `password` in local dev (`database/seeders/DemoCrmSeeder.php`), or set `DEMO_USER_PASSWORD="Password@12345"` / update the seeder when deploying a public demo instance.

| Role | Email | Live Demo Password | Team Scope | Recommended Reviewer Test Flow |
| :--- | :--- | :--- | :--- | :--- |
| **Super Admin** | `superadmin@salescrm.test` | `Password@12345` | Global (All Teams) | Full system access: manage users, teams, pipelines, RBAC roles, global reports, and complete immutable audit logs. |
| **Org Admin** | `admin@salescrm.test` | `Password@12345` | Global (All Teams) | VP of Revenue Operations view: cross-team pipeline analytics, user/team administration, and organization-wide exports. |
| **Sales Manager** | `manager@salescrm.test` | `Password@12345` | Enterprise Sales Team | Team leader (`Tariq Rahman`): view all Enterprise Sales team deals, reassign leads/deals, approve high-discount quotations (`> 15%`), and monitor team forecasts. |
| **Sales Rep** | `rep@salescrm.test` | `Password@12345` | Enterprise Sales Team | Account Executive (`Nadia Karim`): manage own leads (`Apex Textiles BD`, `Delta FinServe`), move `Bengal Tech Enterprise CRM Rollout` on the Kanban board, log calls/tasks, and generate quotation PDFs. |
| **Read-Only Analyst** | `analyst@salescrm.test` | `Password@12345` | Global Read-Only | Revenue Analyst (`Farhana Yasmin`): inspect executive dashboards, funnel conversion metrics, and export CSV reports without mutation privileges. |
| **SMB Sales Rep** *(Bonus Cross-Team Isolation Test)* | `smbrep@salescrm.test` | `Password@12345` | SMB Inbound Team | Verify strict team isolation—cannot view or access Enterprise Sales records owned by `rep@salescrm.test` or `manager@salescrm.test`. |

### 5-Minute Guided Walkthrough for Buyers & Vibe96 Reviewers

1. **Executive Dashboard & Analytics (`/dashboard` & `/reports`):** Log in as `superadmin@salescrm.test` or `analyst@salescrm.test` to inspect real-time pipeline KPIs, weighted revenue forecasts, stage conversion funnels, and win-rate analytics.
2. **Lead Qualification & Atomic Conversion (`/leads`):** Log in as `rep@salescrm.test`, open the qualified lead **Kamrul Hasan — Apex Textiles BD** (`score: 82`, `BDT 450,000.00`), and click **Convert Lead** to atomically create the Company, Contact, and Deal inside a single database transaction.
3. **Interactive Pipeline Kanban (`/deals`):** Switch between the Kanban board and tabular view for the **B2B Enterprise Pipeline** (`Prospecting` → `Qualification` → `Solution Demo` → `Proposal / Quote Sent` → `Negotiation` → `Closed Won / Lost`), drag deals across stages, and inspect the automatic `DealStageHistory` duration log.
4. **Quotations, Manager Approval & PDF Proposal (`/quotations`):** Open Quotation **`QT-2026-00001`** (`Bengal Tech Holdings Ltd.`, `BDT 650,000.00`), download the formatted mPDF proposal, or test the public signed acceptance portal link. Create a quote with a `20%` discount as `rep@salescrm.test` to trigger the **Pending Approval** lock, then approve it as `manager@salescrm.test`.
5. **Audit Trail Verification (`/audit-logs`):** Log back in as `superadmin@salescrm.test` and open **Audit Logs** to inspect the immutable JSON diff records recorded for every action above.

---

## 4. Step-by-Step Buyer Installation & Deployment Guide

### System Prerequisites

- **PHP:** `8.3+` with `bcmath`, `ctype`, `fileinfo`, `gd`, `intl`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `zip` extensions enabled
- **Database:** MySQL `8.0+` (`InnoDB`, `utf8mb4_unicode_ci`)
- **Composer:** `2.6+`
- **Node.js & NPM:** Node `20 LTS+` (optional if using pre-compiled `public/build/` assets included in `dist/sales-crm-vibe96.zip`)

### Quick Local or VPS Setup (5 Commands)

```bash
# 1. Extract the Vibe96 archive and install PHP dependencies
unzip sales-crm-vibe96.zip -d sales-crm && cd sales-crm
composer install --no-interaction --prefer-dist --optimize-autoloader

# 2. Configure environment variables and generate application key
cp .env.example .env
php artisan key:generate

# 3. Configure your MySQL 8.0 database credentials in .env, then run migrations & demo seeder
php artisan migrate --seed

# 4. (Optional) Rebuild frontend Tailwind v4 + Vite assets if customizing UI styles
npm install && npm run build

# 5. Start the application server and background queue worker
php artisan serve
php artisan queue:work --tries=3
```

### Customizing Business Rules via `.env` (`config/crm.php`)

Buyers can tailor currency, regional defaults, quotation numbering, discount approval gates, and lead webhooks without touching PHP code:

```ini
CRM_DEFAULT_CURRENCY=BDT
CRM_DEFAULT_COUNTRY=BD
CRM_QUOTE_DISCOUNT_THRESHOLD=15.00
CRM_QUOTE_PREFIX=QT
CRM_QUOTE_PUBLIC_LINK_DAYS=30
CRM_LEAD_WEBHOOK_SECRET=your-hmac-sha256-webhook-secret
CRM_LEAD_CAPTURE_TEAM_ID=1
CRM_LEAD_ROUND_ROBIN=true
```

### Deploying on Vibe Studio (`studio.vibe96.com`) or Production Linux VPS

1. **Vibe Studio One-Click / Managed Hosting:** Upload `dist/sales-crm-vibe96.zip` to your Vibe Studio workspace, attach a managed MySQL 8.0 instance, populate the environment variables from `.env.example`, and enable Vibe Studio's SSL, automated nightly backups, and one-click rollback features.
2. **Production Nginx + PHP-FPM + Supervisor:** Follow the zero-downtime deployment runbook in `docs/deployment/deployment-guide.md`, queue worker configuration in `docs/deployment/queue-and-scheduler.md`, and pre-launch security verification in `docs/deployment/production-checklist.md`.

---

## 5. Vibe96 "Warden" Automated Audit Readiness Summary

Vibe96's automated **Warden** code auditor inspects uploaded archives file-by-file across security guardrails, database schema quality, clean code standards, and documentation completeness. **Sales CRM** has been architected and verified against every Warden inspection pillar:

| Warden Audit Dimension | Sales CRM Implementation & Verification Evidence | Status |
| :--- | :--- | :--- |
| **1. Zero Secrets & Clean Package Hygiene** | `.env`, `.git/`, `vendor/`, `node_modules/`, `storage/logs/*.log`, and `database/*.sqlite` are strictly excluded by `scripts/package-vibe96.ps1`. `.env.example` contains only safe placeholder defaults. | **PASS** |
| **2. Strict Type Safety & Code Formatting** | Every PHP class, enum, migration, seeder, and test declares `declare(strict_types=1);` with explicit parameter and return types. Verified with `vendor/bin/pint --test` (0 style issues). | **PASS** |
| **3. Environment Variable Governance** | Zero `env()` calls exist outside `config/*.php`. All business settings resolve through `config('crm.*')` so `php artisan config:cache` succeeds cleanly in production. | **PASS** |
| **4. Mass-Assignment & Input Validation** | Every `POST`/`PUT`/`PATCH` route uses a dedicated `FormRequest` class in `app/Http/Requests/`. All 18 Eloquent models define explicit `$fillable` arrays; `$guarded = []` is never used. | **PASS** |
| **5. Multi-Tenant / Team IDOR Prevention** | Every domain controller enforces Laravel `Policy` checks (`$this->authorize()`) and every list/report query applies `->visibleTo($request->user())` scoping (`own`, `team`, `all`). | **PASS** |
| **6. Database Schema & Financial Precision** | 17 ordered MySQL 8.0 migrations with foreign-key constraints (`cascadeOnDelete` / `restrictOnDelete` / `nullOnDelete`), composite indexes, `SoftDeletes` on core entities, and `DECIMAL(15,2)` for all monetary columns. | **PASS** |
| **7. Transactional Data Integrity** | Multi-table mutations (`ConvertLeadToDealAction`, `QuotationService`, stage transitions with history logging) execute inside `DB::transaction()` blocks. | **PASS** |
| **8. N+1 Query Prevention & Performance** | `Model::preventLazyLoading(! app()->isProduction())` is active in `AppServiceProvider`, and all index/list/Kanban queries eager-load relationships via `->with([...])`. | **PASS** |
| **9. XSS, CSRF & Webhook Security** | Blade templates use escaped `{{ }}` output exclusively; all forms include `@csrf`; public quotation links use signed URLs + ULIDs; inbound lead webhooks verify HMAC signatures. | **PASS** |
| **10. Automated Feature & RBAC Test Suite** | Comprehensive PHPUnit test suite (`php artisan test --compact`) covering guest rejection, cross-team IDOR `403` rejection, validation errors, happy-path persistence, and `audit_logs` creation. | **PASS** |
| **11. Production Documentation & AI Readiness** | Includes 60+ structured Markdown specifications across `docs/architecture/`, `docs/database/`, `docs/modules/`, `docs/security/`, `docs/testing/`, `docs/deployment/`, and `docs/prompts/`. | **PASS** |
