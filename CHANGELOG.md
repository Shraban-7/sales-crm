# Changelog

**Last Updated:** 2026-11-17

All notable changes to the **Sales CRM** project and its engineering documentation system are documented in this file. This project adheres to [Semantic Versioning](https://semver.org/) and [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

### Added (Documentation & Architecture Specification — 2026-11-17)
- Initialized complete Markdown engineering knowledge base for greenfield **Sales CRM** development in Google Gemini Antigravity IDE.
- Added Root governance files (`README.md`, `GEMINI.md`, `PROJECT_STATUS.md`, `CONTRIBUTING.md`, `project-discovery-report.md`, `DOCUMENTATION_GENERATION_REPORT.md`).
- Added 8 Architecture specification documents (`docs/architecture/`).
- Added 6 Database design, relationship, ERD, migration, optimization, and integrity documents (`docs/database/`).
- Added 14 comprehensive CRM Module functional & technical specifications (`docs/modules/`).
- Added 8 Security & RBAC governance documents (`docs/security/`).
- Added 8 Testing & QA strategy documents (`docs/testing/`).
- Added 5 Debugging & observability guides (`docs/debugging/`).
- Added 5 Deployment & operational runbooks (`docs/deployment/`).
- Added 5 Architecture Decision Records (`docs/adr/ADR-001` through `ADR-005`).
- Added 12 reusable Gemini Antigravity IDE engineering prompts (`docs/prompts/`).

### Implementation Status (Updated 2026-11-18)
- Initialized Laravel 13.x on repository root with MySQL 8.0+, Tailwind CSS v4 (`@tailwindcss/vite`), Spatie Laravel Permission, mPDF quotation generation, email-based 2FA, and 18 ordered migrations.
- Resolved all P0/P1/P2 findings from `report.md`: public lead capture + HMAC webhook + round-robin assignment + dedupe/merge, full lead/company/contact/deal/quotation edit UIs, signed public quotation accept/reject + email delivery + activity logging + discount approval gate + daily expiry command, multi-currency & `closed_at`/`expected_close_date` reporting, accurate dashboard KPI counts, Read-Only Analyst permission alignment, complete user reassignment, private attachments upload/download, CSV import phone & dedupe fixes, mobile navigation drawer, overdue follow-up escalation, admin user/team/pipeline-stage UIs, GitHub Actions CI, and Pint formatting.
- Verified with `vendor/bin/pint --test` (0 style issues) and `php artisan test --compact` (28 passed, 146 assertions).
