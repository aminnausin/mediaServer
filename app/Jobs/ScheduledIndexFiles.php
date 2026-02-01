<?php

namespace App\Jobs;

use App\Services\FileJobService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ScheduledIndexFiles implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(FileJobService $fileJobService): void {
        $fileJobService->indexFiles(['userId' => null, 'namePrefix' => 'Scheduled ']);
    }
}
