<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Attendance
Schedule::command('attendance:mark-absent')->dailyAt('00:05');
Schedule::command('attendance:send-alerts')->dailyAt('00:30');

// Work Reports
Schedule::command('reports:send-work-reminder')->dailyAt('16:30')->weekdays();
Schedule::command('reports:escalate-missing')->dailyAt('19:00')->weekdays();

// Smart AI Alerts
Schedule::command('alerts:run-smart-checks')->dailyAt('07:00');

// Leave balance summary every Monday 8 AM
Schedule::command('leaves:send-balance-summary')->weeklyOn(1, '08:00');
