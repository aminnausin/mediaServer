<?php

use App\Jobs\ScheduledIndexFiles;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new ScheduledIndexFiles)->twiceDaily()->withoutOverlapping()->environments(['staging', 'production']);
Schedule::command('auth:clear-resets')->everyFifteenMinutes();
Schedule::command('sanctum:prune-expired --hours=2')->daily();

if (app()->environment('demo')) {
    Schedule::command('demo:reset')->everyFifteenMinutes()->withoutOverlapping();
}

if (config('queue.default') === 'redis') {
    Schedule::command('horizon:snapshot')->everyFiveMinutes();
}
