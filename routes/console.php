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
