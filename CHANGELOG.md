# Changelog

**Last Updated:** 2026-11-19

All notable changes to the **Sales CRM** application and documentation are tracked in this file. This project follows [Semantic Versioning](https://semver.org/) and [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

### Added & Updated (2026-11-18 — 2026-11-19)
- **Complete UI Modernization & Responsive Polish:**
  - Upgraded the Blade + Tailwind CSS v4 component library (`.crm-card`, `.crm-input`, `.crm-select`, `.crm-btn-*`, `.crm-table*`, accented KPI stat cards, pill status badges, and stage color borders).
  - Replaced plain-text navbar `"Alerts"` control with an accessible SVG notification bell icon and unread count badge (`99+` cap).
  - Redesigned the split-screen authentication experience (`/login`) with password visibility toggling and rate-limit indicators, keeping production login forms free of hardcoded credentials.
  - Added multi-format favicon suite (`public/favicon.svg`, multi-size `public/favicon.ico`, `public/apple-touch-icon.png`) via `<x-crm.favicons />` across all application and public `<head>` templates.
  - Designed cohesive branded HTTP error pages (`resources/views/errors/layout.blade.php` + `401`, `402`, `403`, `404`, `419`, `429`, `500`, `503`) with safe internal error redaction on `500`.
- **Comprehensive End-User & Deployment Documentation (`readme.md`):**
  - Rewrote [readme.md](readme.md) into an accessible product guide + technical reference featuring seeded demo credentials, a 7-step everyday user tutorial, `28` live browser UI screenshots (`docs/screenshots/`), and Nginx/MySQL/Scheduler setup for `https://sales-crm.devshraban.com`.
  - Synchronized `PROJECT_STATUS.md`, `CONTRIBUTING.md`, and `docs/` specifications with actual implemented routes, `18` migrations, and test coverage (`34` passed tests, `407` assertions).

### Core Platform Implementation (2026-11-17 — 2026-11-18)
- Initialized Laravel 13.x on PHP 8.3+ with MySQL 8.0+, Tailwind CSS v4 (`@tailwindcss/vite`), Spatie Laravel Permission, mPDF quotation PDFs, optional email 2FA, and `18` ordered database migrations.
- Implemented all `14` CRM domain modules: web-to-lead capture (`/capture/lead`) & HMAC webhook (`POST /api/leads/capture`), round-robin assignment, duplicate detection & merging, atomic `ConvertLeadToDealAction`, drag-and-drop Kanban pipelines, deal stage histories & mandatory lost reasons, polymorphic activities/attachments, tasks & due reminder commands, chained customer follow-ups & overdue escalations, discount-gated quotations with ULID signed client portals (`/q/{ulid}`) and daily expiry commands, per-currency analytics reports, sanitized CSV import/export jobs, user record reassignment, and immutable audit logs.
- Verified code formatting via `vendor/bin/pint --test` and automated tests via `php artisan test --compact`.
