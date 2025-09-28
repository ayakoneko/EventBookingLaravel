<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\WaitlistSweeper;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Automatic waitlist sweeper (runs every minute)
Schedule::call(function () {
    app(WaitlistSweeper::class)->sweepAllOverdue();
})
->everyMinute()
->name('waitlist-sweep')
->withoutOverlapping();
