# Sales Fusion CRM Audit Follow-Up

Tracks audit run **#3065** (version 1.0.4, 2026-11-21; score 76/100) against the current working tree and remediation in this update.

## Fixed In This Update

- Removed known database password fallbacks; Docker Compose now requires `DB_PASSWORD` and `DB_ROOT_PASSWORD` and shares app/worker environment settings through a YAML anchor (`docker-compose.yml`).
- Removed unused `concurrently` and updated `package-lock.json`.
- Removed hard-coded admin/demo password defaults from config. Missing credentials are generated randomly in non-production; direct production admin/demo seeding refuses to proceed without the relevant environment value (`config/crm.php`, `app/Support/SecuritySecret.php`, seeders).
- Removed password values from seeder tables and README/setup docs; documented the required environment settings.
- Made webhook timestamp mandatory and included it in the HMAC-signed payload, matching `docs/api-reference.md` and preventing a captured signature from being replayed with a substituted timestamp (`app/Http/Middleware/VerifyWebhookSignature.php`).
- Preserved the original signed quote-link expiration when generating accept/reject form URLs, added sent-state guards, and recorded acceptance email/IP in the audit event (`PublicQuotationController`, `AcceptQuotationAction`).
- Made rejection use `Quotation::isExpired()` so null validity dates are safe (`RejectQuotationAction`).
- Attachment deletion now also checks access to its parent record (`AttachmentPolicy`).
- Aligned the Docker runtime to PHP 8.4 and Node.js 22; setup docs now state runtime prerequisites (`docker/php/Dockerfile`, `HowToSetup.md`).
- Changed the example log level from `debug` to `warning` (`.env.example`).

## Previously Fixed

- Unassigned Sales Managers are scoped to unassigned-team records.
- Accepting a quotation with a null `valid_until` no longer errors.
- Existing unrelated companies no longer prevent demo sample data from being seeded.
- Removed optional `env_file` mapping syntax that was unsupported by older Compose versions.
- Demo seeders are split into an orchestrator and account, pipeline, and business-data seeders.

## Still Open Or Unverified

- `laravel/tinker` remains in Composer production requirements. Moving it safely requires regenerating and validating `composer.lock`; this session cannot run PHP/Composer because Herd's PHP executable is not available on this session's PATH.
- Composer package advisories and lockfile freshness remain unverified. `npm audit --offline` reported zero vulnerabilities; no live registry audit was run.
- Demo user emails, names, and role assignments remain hard-coded in `DemoAccountSeeder`.
- Analytics/reporting visibility coverage and other raw SQL usage need a full query audit. Analytics summary and export job paths checked here do use `visibleTo()`; that does not establish coverage of every query.
- Migration/schema parity, full foreign-key/index coverage, clean install/migrate, PHP syntax, and feature tests remain unverified because PHP/Composer is unavailable in this session.
- The main routes file remains large; notification composition remains in `PublicQuotationController`.
- The PWA camera capture feature, full install prerequisites, and production queue/scheduler setup still need a clean deployment verification.

## Validation

- `docker compose config --quiet` succeeded with validation-only values supplied for the required database passwords; no actual stack was started.
- `npm audit --offline` reported 0 vulnerabilities.
- `git diff --check` reported no whitespace errors.
- PHP tests, PHP lint, and Composer audit were not run because `php` is not resolvable in this coding session.
