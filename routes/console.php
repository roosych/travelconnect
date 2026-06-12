<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Fetch CBAR exchange rates every morning at 09:00 Baku time (UTC+4)
Schedule::command('currencies:sync-rates')->dailyAt('05:00'); // 05:00 UTC = 09:00 Baku

// Mark sent proposals past their valid_until as expired — midnight Baku time (UTC+4 → 20:00 UTC)
Schedule::command('proposals:expire')->dailyAt('20:00');

// Mark received/reviewed offers past their valid_until as expired
Schedule::command('offers:expire')->dailyAt('20:00');

// Advance booking statuses based on travel dates (started → in_progress, ended → completed)
Schedule::command('bookings:advance-status')->dailyAt('20:00');
