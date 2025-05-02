<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Jobs\CleanFolderPaths;
use App\Jobs\CleanVideoPaths;
use App\Jobs\EmbedUidInMetadata;
use App\Jobs\GeneratePreviewImage;
use App\Jobs\IndexFiles;
use App\Jobs\SyncFiles;
use App\Jobs\VerifyFiles;
use App\Jobs\VerifyFolders;
use App\Models\Category;
use App\Models\Folder;
use App\Models\Task;
use App\Models\Video;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class FileJobService {
    public function __construct(protected TaskService $taskService) {}

    public function scanFiles(array $data, ?Category $category = null) {
        $name = 'Scan Files';
        $description = 'Scans for file changes and loads metadata from all Libraries.';
        $categoryId = null;

        if (isset($category)) {
            $name .= " from the Library \"$category->name\"";
            $description = "Scans for file changes and loads metadata from the specified Library \"$category->name\"";
            $categoryId = $category->id;
        }

        return $this->executeBatchOperation(
            userId: $data['userId'] ?? null,
            name: $name,
            description: $description,
            chain: function ($task) {
                return [
                    new SyncFiles($task->id),
                    new IndexFiles($task->id),
                ];
            },
            callback: function ($task) use ($data, $categoryId) {
                $category = $categoryId ? Category::findOrFail($categoryId) : null;
                $this->verifyFiles($data, $category, $task->id);
            }
        );
    }

    public function indexFiles(array $data, ?Category $category = null) {
        $name = 'Index Files';
        $description = 'Looks for folder and video changes in in all Libraries.';

        if (isset($category)) {
            $name .= " for Library $category->name";
            $description = "Looks for folder and video changes in the specified Library $category->name";
        }

        return $this->executeBatchOperation(
            userId: $data['userId'] ?? null,
            name: $name,
            description: $description,
            chain: function ($task) {
                return [
                    new SyncFiles($task->id),
                    new IndexFiles($task->id),
                ];
            },
        );
    }

    public function syncFiles(array $data) {
        return $this->executeBatchOperation(
            userId: $data['userId'] ?? null,
            name: 'Sync Files',
            description: 'Syncs local file structure with database.',
            chain: function ($task) {
                return [
                    new SyncFiles($task->id),
                ];
            },
        );
    }

    public function verifyFiles(array $data, ?Category $category = null, ?int $taskId = null) {
        $name = 'Verify Files';
        $description = 'Verifies folder and video metadata for all Libraries.';

        if (isset($category)) {
            $name .= " for Library \"$category->name\"";
            $description = "Verifies folder and video metadata for the specified Library \"$category->name\"";
        }

        return $this->executeBatchOperation(
            userId: $data['userId'] ?? null,
            name: $name,
            description: $description,
            chain: function ($task) use ($category) {
                $chain = [];

                $videoQuery = Video::orderBy('id');
                $folderQuery = Folder::orderBy('id');

                if ($category) {
                    $videoQuery = $videoQuery->whereHas('folder.category', function ($query) use ($category) {
                        $query->where('id', $category->id);
                    })->with('folder.category');

                    $folderQuery = $folderQuery->whereHas('category', function ($query) use ($category) {
                        $query->where('id', $category->id);
                    })->with('category');
                }

                $videoQuery->chunk(100, function ($chunk) use (&$chain, $task) {
                    $chain[] = new VerifyFiles($chunk, $task->id);
                });

                $folderQuery->chunk(100, function ($chunk) use (&$chain, $task) {
                    $chain[] = new VerifyFolders($chunk, $task->id);
                });

                return $chain;
            },
            initialTaskId: $taskId,
        );
    }

    public function verifyFolders(array $data, ?Category $category = null) {
        $name = 'Verify Folders';
        $description = 'Verifies folder metadata for all Libraries.';

        if (isset($category)) {
            $name .= " for Library \"$category->name\"";
            $description = "Verifies folder metadata for the specified Library \"$category->name\"";
        }

        return $this->executeBatchOperation(
            userId: $data['userId'] ?? null,
            name: $name,
            description: $description,
            chain: function ($task) use ($category) {
                $chain = [];
                $query = Folder::orderBy('id');

                if ($category) {
                    $query->whereHas('category', fn ($q) => $q->where('id', $category->id))
                        ->with('category');
                }

                $query->chunk(100, function ($chunk) use (&$chain, $task) {
                    $chain[] = new VerifyFolders($chunk, $task->id);
                });

                return $chain;
            },
        );
    }

    public function cleanPaths(array $data) {
        $name = 'Clean Paths';
        $description = 'Cleans file and folder paths for all Libraries.';

        return $this->executeBatchOperation(
            userId: $data['userId'] ?? null,
            name: $name,
            description: $description,
            chain: function ($task) {
                $chain = [];

                Video::orderBy('id')->chunk(20)->each(function ($chunk) use (&$chain, $task) {
                    $chain[] = new CleanVideoPaths($chunk, $task->id);
                });

                Folder::orderBy('id')->chunk(20)->each(function ($chunk) use (&$chain, $task) {
                    $chain[] = new CleanFolderPaths($chunk, $task->id);
                });

                return $chain;
            },
        );
    }

    /**
     * Embed UIDs in file metadata
     * Unused?
     *
     * @param  array<array{path: string, uuid: string}>  $files
     * @return \Illuminate\Http\JsonResponse
     */
    public function embedUIDs(int $taskId, string $name, array $files) {
        $description = 'Embeds UIDs in file metadata.';
        $lastTask = Task::find($taskId);

        return $this->executeBatchOperation(
            userId: $lastTask ? $lastTask->user_id : null,
            name: $name,
            description: $description,
            chain: function ($task) use ($files) {
                $chain = [];

                foreach ($files as $file) {
                    $chain[] = new EmbedUidInMetadata($file['path'], $file['uuid'], $task->id);
                }

                return $chain;
            },
        );
    }

    /**
     * Regenerate open graph preview images
     *
     * @param  array<array{array: data, path: string}>  $rows
     * @return \Illuminate\Http\JsonResponse
     */
    public function regeneratePreviewImages(array $rows, ?int $userId = null) {
        $name = 'Regenerate Preview Images';
        $description = 'Regenerates open graph preview images.';

        return $this->executeBatchOperation(
            userId: $userId ?? null,
            name: $name,
            description: $description,
            chain: function ($task) use ($rows) {
                $chain = [];

                foreach ($rows as $row) {
                    if (! isset($row['data']) || ! isset($row['path'])) {
                        continue;
                    }
                    $chain[] = new GeneratePreviewImage($row['data'], $row['path'], $task->id);
                }

                return $chain;
            },
        );
    }

    public function executeBatchOperation(
        ?int $userId,
        string $name,
        string $description,
        array|callable $chain,
        ?callable $callback = null,
        array $initialTaskData = [],
        ?int $initialTaskId = null,
    ) {
        $task = $initialTaskId ? Task::findOrFail($initialTaskId) : $this->setupTask($userId, $name, $description);

        try {
            $finalChain = is_callable($chain) ? $chain($task) : $chain;
            $chainLength = count($finalChain);

            $taskData = array_merge([
                'sub_tasks_total' => $chainLength + $task->sub_tasks_total,
                'sub_tasks_pending' => $chainLength,
                'sub_tasks_current' => $chainLength,
            ], $initialTaskData);

            $this->setupBatch(
                $finalChain,
                $task,
                $callback,
                $taskData
            );

            return $task;
        } catch (\Throwable $th) {
            $this->handleOperationFailure($task, $th);
            throw $th;
        }
    }

    public function setupTask($userId, $name, $description = '', $taskCount = 0) {
        return $this->taskService->createTask([
            'user_id' => $userId,
            'name' => $name,
            'description' => $description,
            'sub_tasks_total' => $taskCount,
            'sub_tasks_pending' => $taskCount,
        ]);
    }

    public function setupBatch(array $chain, Task $task, ?callable $callback = null, ?array $taskData = []) {
        return Bus::batch($chain)
            ->catch(fn (Batch $batch, \Throwable $e) => $this->handleOperationFailure($task, $e))
            ->finally(fn (Batch $batch) => $this->finalizeBatch($batch, $task, $callback))
            ->before(fn (Batch $batch) => $this->taskService->updateTask($task->id, array_merge([
                'status' => TaskStatus::PROCESSING,
                'started_at' => $task->started_at ?? now(),
                'batch_id' => $batch->id,
            ], $taskData)))
            ->name($task->name)
            ->dispatch();
    }

    protected function finalizeBatch(Batch $batch, Task $task, ?callable $callback) {
        $task->refresh();
        // if task is not still processing, don't change what the current status is
        // otherwise base status on batch info

        // if not processing or numbers dont match, task was ended elsewhere or has not started ?
        if ($task->status !== TaskStatus::PROCESSING || $task->sub_tasks_current !== $batch->totalJobs) {
            Log::warning('Early finalize batch return', [$task->sub_tasks_current, $batch->totalJobs]);

            return;
            // Might be incorrect
            // The idea is if the status was updated elsewhere, let them finish the job.
            // Same goes for total tasks being different from the batch, meaning tasks were added externally.
        }
        $initialStatus = $batch->processedJobs() > $task->sub_tasks_total
            ? TaskStatus::INCOMPLETE
            : TaskStatus::COMPLETED;
        $status = $batch->cancelled()
            ? TaskStatus::CANCELLED : $initialStatus;

        try {
            if ($callback) {
                $callback($task);

                return;
            }

            $duration = $task->started_at
                ? (int) now()->diffInSeconds($task->started_at)
                : 0;

            $this->taskService->updateTask($task->id, [
                'status' => $status,
                'ended_at' => now(),
                'duration' => abs($duration),
            ], $status === TaskStatus::COMPLETED);
        } catch (\Throwable $th) {
            Log::error('Error finalizing batch', [
                'task_id' => $task->id,
                'error' => $th->getMessage(),
            ]);
            throw $th;
        }
    }

    protected function handleOperationFailure(?Task $task, \Throwable $exception) {
        if (! $task) {
            return;
        }

        try {
            $this->taskService->updateTask($task->id, [
                'status' => TaskStatus::FAILED,
                'ended_at' => now(),
            ]);

            Log::error('Batch operation failed', [
                'task_id' => $task->id,
                'error' => $exception->getMessage(),
            ]);
        } catch (\Throwable $updateError) {
            Log::error('Failed to update task status', [
                'task_id' => $task->id,
                'original_error' => $exception->getMessage(),
                'update_error' => $updateError->getMessage(),
            ]);
        }
    }
}
