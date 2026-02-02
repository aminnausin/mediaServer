<?php

namespace App\Console\Commands;

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

    public function handle(FileJobService $fileJobService) {
        $this->info('Starting Scan Files');

        $library = $this->argument('library_id') ? Category::find($this->argument('library_id')) : null;
        $fileJobService->scanFiles(['userId' => null, 'namePrefix' => 'Console '], $library);

        $this->info('Scan Files Queued!');
    }
}
