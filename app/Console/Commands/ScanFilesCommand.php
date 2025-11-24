<?php

namespace App\Console\Commands;

use App\Jobs\IndexFiles;
use App\Jobs\SyncFiles;
use App\Models\Category;
use App\Services\FileJobService;
use Illuminate\Console\Command;

class ScanFilesCommand extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mediaServer:scan {library_id? : Library ID to scan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan Media Files';

    public function __construct(
        protected FileJobService $fileJobService
    ) {
        parent::__construct();
    }

    public function handle() {
        $fileJobService = $this->fileJobService; // isolate it from $this

        $name = 'Console Scan Files';
        $description = 'Scans for file changes and loads metadata from all libraries.';

        $library_id = $this->argument('library_id');

        $this->info('Starting Scan Files');

        $this->fileJobService->executeBatchOperation(
            userId: null,
            name: $name,
            description: $description,
            chain: function ($task) {
                return [
                    new SyncFiles($task->id),
                    new IndexFiles($task->id),
                ];
            },
            callback: function ($task) use ($fileJobService, $library_id) {
                $library = Category::find($library_id);

                $fileJobService->verifyFiles([], $library, $task->id);
            }
        );

        $this->info('Scan Files Queued!');
    }
}
