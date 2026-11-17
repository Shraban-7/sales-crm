<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

Schedule::command('crm:dispatch-due-reminders')->everyMinute()->withoutOverlapping();
