<?php

namespace App\Jobs\Maintenance;

use App\Services\FileJobService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ScheduledPurgeStaleData implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(FileJobService $fileJobService): void {
        $fileJobService->purgeStaleData(['userId' => null, 'namePrefix' => 'Scheduled ']);
    }
}
