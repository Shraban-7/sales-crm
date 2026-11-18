# Queue Workers & Task Scheduler Configuration — Sales CRM

**Last Updated:** 2026-11-19  
**Implementation Status:** `Implemented (Active Operational Runbook)`  
**Related Docs:** `docs/architecture/request-lifecycle.md`, `docs/modules/tasks-and-reminders.md`, `docs/modules/import-and-export.md`

## 1. Queue Priority Topology

| Queue Name | Priority | Dispatched Jobs | Timeout / Retries |
| :--- | :---: | :--- | :--- |
| `high` | 1 (Highest) | Password resets, account security alerts | `--timeout=60 --tries=3` |
| `notifications` | 2 (Medium) | Task/follow-up reminders, quotation sent emails, lead assignment alerts | `--timeout=90 --tries=3` |
| `imports` | 3 (Bulk) | `ProcessCsvImportJob`, `GenerateExportFileJob` | `--timeout=600 --tries=2` |

## 2. Supervisor Configuration (`/etc/supervisor/conf.d/sales-crm-worker.conf`)

```ini
[program:sales-crm-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/sales-crm/current/artisan queue:work --queue=high,notifications,imports --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/sales-crm/shared/storage/logs/worker.log
stopwaitsecs=3600
```

## 3. Scheduled Tasks (`routes/console.php`)

```php
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

Schedule::command('crm:dispatch-due-reminders')
    ->everyMinute()
    ->withoutOverlapping(5)
    ->onOneServer();

Schedule::command('crm:flag-overdue-follow-ups')
    ->everyFiveMinutes()
    ->withoutOverlapping(10)
    ->onOneServer();

Schedule::command('crm:flag-rotting-deals')
    ->hourly()
    ->withoutOverlapping(30)
    ->onOneServer();

Schedule::command('crm:expire-past-due-quotations')
    ->dailyAt('00:15')
    ->onOneServer();
```
