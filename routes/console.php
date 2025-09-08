<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('schedule-training:send-notifications')
    ->dailyAt('00:00')
    ->withoutOverlapping();

Schedule::command('schedule-match:send-notifications')
    ->dailyAt('00:00')
    ->withoutOverlapping();
