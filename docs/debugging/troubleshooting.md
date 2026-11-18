# Common Troubleshooting Runbook — Sale Fusion CRM

**Last Updated:** 2026-11-19  
**Implementation Status:** `Implemented (Active Operational Guide)`  
**Related Docs:** `docs/debugging/debugging-guide.md`, `docs/deployment/queue-and-scheduler.md`

## 1. Symptoms, Root Causes & Resolutions

| Symptom | Likely Root Cause | Resolution |
| :--- | :--- | :--- |
| `403 Forbidden` after assigning a new permission to a Role | Spatie permission cache is still serving stale role map | Run `php artisan permission:cache-reset` (or trigger automatic cache flush via Role observer) |
| `419 Page Expired` on Blade form submission or Vanilla JS `fetch` | Missing `@csrf` directive, missing `X-CSRF-TOKEN` header, or mismatched `SESSION_DOMAIN` | Verify `<meta name="csrf-token" content="{{ csrf_token() }}">` in `layouts/app.blade.php` and check `config/session.php` |
| `LazyLoadingViolationException` on Deal Kanban or Index page | Accessing `$deal->company->name` or `$deal->owner->name` in Blade without eager loading | Add `->with(['company:id,name', 'owner:id,name'])` to the query in the Controller/Service |
| Tailwind CSS v4 utility classes not applying after editing Blade views | Vite dev server not running or legacy v3 directives (`@tailwind base;`) used instead of `@import "tailwindcss";` | Ensure `resources/css/app.css` uses `@import "tailwindcss";`, `@tailwindcss/vite` is in `vite.config.js`, and run `npm run build` |
| Task reminders or rotting deal flags not firing | Cron entry for `php artisan schedule:run` or queue worker is not running | Check `crontab -l` for `* * * * * php /path/artisan schedule:run` and verify Supervisor queue worker status |
| CSV Import stuck in `pending` status | Queue worker for `imports` queue is stopped or `QUEUE_CONNECTION` is misconfigured | Run `php artisan queue:work --queue=high,notifications,imports` and inspect `failed_jobs` |
