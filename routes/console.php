<?php

use App\Jobs\ScheduledIndexFiles;
// use Illuminate\Foundation\Inspiring;
// use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote')->hourly();

Schedule::job(new ScheduledIndexFiles)->everySixHours()->environments(['staging', 'production']);
Schedule::command('sanctum:prune-expired --hours=2')->daily();
