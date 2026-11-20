# Changelog

**Last Updated:** 2026-11-20

All notable changes to **Sale Fusion CRM** are documented in this file. This project adheres to [Semantic Versioning](https://semver.org/) and [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [1.0.1] - 2026-11-20

### Added — Enterprise CRM Suite Extensions
- **Proposals & Private Document Storage (`/proposals`):** Auto-numbered proposals (`PR-YYYY-XXXXX`), status management, validity periods, access-controlled private document uploads (`ProposalDocumentController`), and daily expiration scan (`crm:check-proposal-expiry`).
- **Revenue Targets & Commission Tracking (`/targets`):** Monthly revenue targets across company, teams, and individual reps with commission recording on closed-won deals.
- **Urgent Attention & Stalled Deals (`/urgent-attention`):** Velocity tracking identifying deals rotting in stage past thresholds, overdue follow-ups, and pending tasks.
- **Unified Activity Feed (`/activities`):** Filterable activity log with My Activities vs. Team Activities tabs, filterable by communication type (call, meeting, email, note) and custom date ranges.
- **Mobile PWA & Offline Event Lead Capture (`/mobile/capture`, `/mobile/events`):** Progressive Web App manifest (`manifest.json`), service worker (`sw.js`), camera-based business card photo capture, and conference/trade show event manager.
- **In-App SMTP Configuration (`/settings/smtp`):** Custom mail host, port, username, AES-256 encrypted password storage, and one-click test email dispatch.
- **In-App Pipeline Stage Management (`/settings/stages`):** Add, position, set colors, configure win probabilities, and set rotting day thresholds for pipeline stages.
- **Profile Preferences (`/settings/profile`):** Personal name, phone, title, timezone, and password updates.
- **Role System Aliases (`RoleId` enum):** Added `Head of Sales`, `Sales Manager`, `Sales Executive`, and `Viewer` with Spatie `role:` middleware alias.
- **Duplicate Detection & List Export:** Added `GET /leads/check-duplicates` and streamed spreadsheet export `GET /leads/export`.
- **Bulk Spreadsheet Import (`/imports/bulk`):** Format specification card and duplicate handling for bulk CSV/Excel data.

### Security & Hardening
- **Production Guard on Demo Seeder:** `DemoCrmSeeder` now aborts with an explicit safety exception if executed in a production environment (`APP_ENV=production`) unless a secure `CRM_DEMO_PASSWORD` is supplied.
- **Webhook Replay Protection:** Added 300-second timestamp validation (`X-CRM-Timestamp`) to the HMAC-signed lead capture endpoint (`POST /api/leads/capture`).
- **Credential Sanitization in Docs:** Replaced hardcoded demo password references in `README.md` with references to the configurable `CRM_DEMO_PASSWORD` environment variable.
- **Route Security Test Suite:** Added `tests/Feature/RouteSecurityTest.php` asserting that all mutating web endpoints enforce authentication and all `/settings` routes enforce specific role-management permissions (`can:users.manage`, `can:teams.manage`, `can:webhooks.manage`).

### Fixed & Cleaned
- **CLAUDE.md Class References:** Corrected outdated job and concern class references (`ProcessCsvExportJob` → `GenerateExportFileJob`, `HasTeamOwnershipScope` → `HasVisibilityScope`).
- **Composer Post-Create Script:** Removed stale SQLite file creation and graceful migration commands from `composer.json`.
- **Bootstrap Cache Hygiene:** Cleared pre-compiled `packages.php` and `services.php` from `bootstrap/cache/` to ensure clean initial deployments.
- **Package Manifest Cleanup:** Added `engines` constraint (`node >= 20.0.0`) and removed unused optional dependency `@laravel/multiplex` in `package.json`.
- **Documentation Alignment:** Synchronized Laravel 13 framework links and `SESSION_ENCRYPT` guidance in `docs/security/authentication.md` and added a clean-machine setup checklist to `HowToSetup.md`.

## [1.0.0] - 2026-11-19

### Added
- **Core B2B Sales CRM Modules (15 modules, 19 migrations):**
  - **Leads & Public/Webhook Intake:** Web-to-lead capture form (`/capture/lead`), HMAC-SHA256 inbound webhook (`POST /api/leads/capture`), 20 professional `LeadSource` channels (`Website`, `Organic Search (SEO)`, `Paid Search Ads`, `Paid Social Ads`, `LinkedIn`, `WhatsApp`, `Partner / Reseller`, `Trade Show`, `Webinar`, etc.), round-robin team assignment, duplicate detection & lead merging, and atomic `ConvertLeadToDealAction`.
  - **Pipelines & Drag-and-Drop Kanban:** Multi-pipeline stage management, interactive Vanilla JS Kanban board (`resources/js/modules/kanban.js`), deal stage velocity history, rotting-deal detection, and mandatory Closed Lost reasons.
  - **Contacts & Companies:** Team- and owner-scoped B2B accounts, multi-contact deal roles, and unified activity timelines.
  - **Quotations & Client Portal:** Live line-item calculator (`resources/js/modules/quotation-calculator.js`), manager discount approval gating, version cloning, mPDF export, printable views, daily auto-expiry command, and temporary signed client acceptance/rejection links (`/q/{ulid}`).
  - **Tasks, Follow-Ups, Activities & Private Attachments:** Due-date reminder notifications, chained customer follow-ups, overdue escalation commands, polymorphic timeline logging, and private attachment downloads with strict MIME validation.
  - **Admin-Gated Webhooks (`webhooks` table + `/settings/webhooks`):** Admin-only CRUD for outbound CRM event webhooks (`lead.created`, `lead.converted`, `deal.stage_moved`, `deal.won`, `deal.lost`, `quotation.sent`, etc.) with encrypted signing secrets, queued HMAC-SHA256 delivery (`DeliverWebhookJob`), delivery status tracking, and inbound webhook status summary.
  - **Reports, CSV Import/Export & Audit Logs:** Per-currency revenue & forecast analytics (`AnalyticsService`), sanitized CSV import/export background jobs, user record reassignment, email-based Two-Factor Authentication, and immutable `audit_logs`.
- **UI & Brand Polish:**
  - Blade + Tailwind CSS v4 (`@theme`) component design system, responsive mobile drawer, accessible SVG notification bell badge, split-screen login, multi-format favicons (`favicon.svg`, `favicon.ico`, `apple-touch-icon.png`), and branded HTTP error pages (`401`–`503`).
- **Deployment, SQL & Developer Tooling:**
  - `HowToSetup.md` covering VPS (Nginx/Apache + MySQL 8 + Supervisor/Cron) and Docker setup, first admin login, and branding customization.
  - `docker-compose.yml`, `docker/php/Dockerfile`, and `docker/nginx/default.conf` using placeholder environment variables only.
  - `database/schema.sql` (clean MySQL 8 schema from empty) and `database/seed.sql` (populated demo dataset across all 7 Kanban stages).
  - `CLAUDE.md` AI-tool architecture guide, `LICENSE-NOTE.md`, and `README.md` with 28 live UI screenshots and non-technical walkthrough.
  - 30-second 1080p demo walkthrough video with synchronized voiceover narration (`docs/screenshots/sale-fusion-crm-demo.mp4`).
- **Verification:**
  - Automated PHPUnit Feature test suite covering authentication, 2FA, RBAC/IDOR boundaries, lead conversion, Kanban stage moves, quotations, webhooks, reports, CSV imports/exports, error pages, and all rendered views.
