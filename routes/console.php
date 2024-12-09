<?php

use App\Jobs\IndexFiles;

// use Illuminate\Foundation\Inspiring;
// use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote')->hourly();

Schedule::job(new IndexFiles)->everySixHours()->environments(['staging', 'production']);
Schedule::command('sanctum:prune-expired --hours=2')->daily();
