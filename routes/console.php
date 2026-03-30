<?php

use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('accounts:cleanup-temporary --type=all')
    ->dailyAt('02:00')
    ->withoutOverlapping();

Schedule::command('accounts:cleanup-temporary --type=cancelled')
    ->everySixHours()
    ->withoutOverlapping();

Schedule::command('accounts:cleanup-temporary --type=rejected')
    ->everySixHours()
    ->withoutOverlapping();

// ── Overdue guest cleanup ──────────────────────────────────────────────
Schedule::command('guests:cleanup-overdue --grace=1')
    ->dailyAt('02:05')
    ->timezone('Asia/Manila')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/cleanup-overdue-guests.log'));