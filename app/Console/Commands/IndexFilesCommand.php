<?php

namespace App\Console\Commands;

use App\Services\FileJobService;
use Illuminate\Console\Command;

class IndexFilesCommand extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mediaServer:index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index Media Files';

    public function handle(FileJobService $fileJobService) {
        $this->info('Starting Index Files...');
        $fileJobService->indexFiles(['userId' => null, 'namePrefix' => 'Console ']);
        $this->info('Index Files Queued!');
    }
}
