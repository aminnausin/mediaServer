<?php

use App\Jobs\ScheduledIndexFiles;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new ScheduledIndexFiles)->everySixHours()->environments(['staging', 'production']);
Schedule::command('auth:clear-resets')->everyFifteenMinutes();
Schedule::command('sanctum:prune-expired --hours=2')->daily();
