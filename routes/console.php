<?php

use App\Jobs\SendCardDueReminders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule card due reminders to run daily at 9 AM
Schedule::job(new SendCardDueReminders())->dailyAt('09:00');
