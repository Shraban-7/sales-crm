<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

Schedule::command('crm:dispatch-due-reminders')->everyMinute()->withoutOverlapping();
Schedule::command('crm:escalate-overdue-follow-ups')->hourly()->withoutOverlapping();
Schedule::command('crm:expire-quotations')->dailyAt('00:10')->withoutOverlapping();
