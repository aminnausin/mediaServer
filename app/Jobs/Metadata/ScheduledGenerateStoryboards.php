<?php

namespace App\Jobs\Metadata;

use App\Services\FileJobService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;

class ScheduledGenerateStoryboards implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable;

    public function handle(FileJobService $fileJobService): void {
        $fileJobService->generateStoryboards(
            data: ['userId' => null, 'namePrefix' => 'Scheduled '],
        );
    }
}
