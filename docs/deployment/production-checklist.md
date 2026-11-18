# Production Readiness Checklist — Sales CRM

**Last Updated:** 2026-11-19  
**Implementation Status:** `Active Release Gate (Verified)`  
**Related Docs:** `docs/deployment/deployment-guide.md`, `docs/security/security-checklist.md`

## 1. Security & Configuration Verification
- [ ] `APP_ENV=production` and `APP_DEBUG=false` are set in `.env`.
- [ ] Unique 32-byte `APP_KEY` generated via `php artisan key:generate`.
- [ ] HTTPS / TLS 1.2+ enforced with HSTS header and `SESSION_SECURE_COOKIE=true`.
- [ ] Nginx blocks access to dotfiles (`/\.`, `.env`, `.git`) and points `root` strictly to `/public`.
- [ ] `storage/` and `bootstrap/cache/` are writable only by the web/PHP-FPM system user (`chmod 775` / `750`).

## 2. Database & Seeding Verification
- [ ] MySQL 8.0+ strict SQL mode enabled (`STRICT_TRANS_TABLES`).
- [ ] All migrations applied cleanly (`php artisan migrate --force`).
- [ ] Base roles & permissions seeded (`php artisan db:seed --class=RolePermissionSeeder --force`).
- [ ] Default sales pipeline and stages seeded (`php artisan db:seed --class=DefaultPipelineSeeder --force`).

## 3. Performance, Queue & Scheduler Verification
- [ ] `php artisan optimize` (`config:cache`, `route:cache`, `view:cache`, `event:cache`) executed.
- [ ] Vite production assets compiled (`public/build/manifest.json` exists).
- [ ] Supervisor running queue workers for `high,notifications,imports` queues.
- [ ] System cron configured for `* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1`.
- [ ] Automated daily MySQL and `storage/app/private` backups tested and verified.
