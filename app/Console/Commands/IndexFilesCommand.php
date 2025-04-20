<?php

namespace App\Console\Commands;

use App\Jobs\IndexFiles;
use App\Jobs\SyncFiles;
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

    public function __construct(
        protected FileJobService $fileJobService
    ) {
        parent::__construct();
    }

    public function handle() {
        $name = 'Console Index Files';
        $description = 'Looks for folder and video changes in in all Libraries.';
        $this->info('Starting Index Files');

        try {
            $this->info('Index Files Queued!');
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
            );

            return true;
        } catch (\Throwable $th) {
            $this->error($th->getMessage());

            return false;
        }
    }
}
