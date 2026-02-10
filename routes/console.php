<?php

use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

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
