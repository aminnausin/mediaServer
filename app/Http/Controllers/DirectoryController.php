<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Events\TaskEnded;
use App\Http\Resources\FolderResource;
use App\Http\Resources\VideoResource;
use App\Jobs\CleanFolderPaths;
use App\Jobs\CleanVideoPaths;
use App\Jobs\EmbedUidInMetadata;
use App\Jobs\IndexFiles;
use App\Jobs\SyncFiles;
use App\Jobs\VerifyFiles;
use App\Jobs\VerifyFolders;
use App\Models\Category;
use App\Models\Folder;
use App\Models\Task;
use App\Models\Video;
use App\Services\TaskService;
use App\Traits\HttpResponses;
use Illuminate\Bus\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DirectoryController extends Controller {
    use HttpResponses;

    protected $taskService;

    public function __construct(TaskService $taskService) {
        $this->taskService = $taskService;
    }

    public function showDirectoryAPI(Request $request) {
        // All this does is convert url to ids and names
        // IDEALLY it should also load data to prevent requiring more api requests
        // It does exactly that now it feels fast
        try {
            $privateCategories = ['legacy' => 1];
            $dir = trim(strtolower($request?->dir ?? ''));
            $folderName = trim(strtolower($request?->folderName ?? ''));

            $dirRaw = Category::select('id', 'default_folder_id')->firstWhere('name', 'ilike', '%' . $dir . '%');

            if (! isset($dirRaw->id)) { // Cannot find category so return default nulls
                return $this->error(['categoryName' => $dir], 'Cannot find specified category', 404);
            }

            if ((isset($privateCategories[$dir]) || $dirRaw?->is_private) && (! $request->user('sanctum') || (Auth::user() && Auth::user()->id !== 1))) {
                return $this->error(null, 'Access to this folder is forbidden', 403);
            }

            $data = ['dir' => ['id' => null, 'name' => $dir, 'folders' => null], 'folder' => ['id' => null, 'name' => $folderName ?? null, 'videos' => null]]; // Default null values

            $folderList = Folder::with('series')->where('category_id', $dirRaw->id)->orderBy('name')->get(); // Folders in category
            $data['dir'] = ['id' => $dirRaw->id, 'name' => $dir, 'folders' => FolderResource::collection($folderList)]; // Full category data

            $folderRaw = isset($request->folderName)
                ? (
                    // $folderList->firstWhere('name', $folderName)
                    // $folderList->firstWhere('name', 'ilike', '%' . $folderName . '%')
                    $data['dir']['folders']->first(function ($folder) use ($folderName) {
                        return Str::lower($folder->name) === Str::lower($folderName);
                    })
                    ?: $data['dir']['folders']->first(function ($folder) use ($folderName) {
                        return Str::contains(Str::lower($folder->name), Str::lower($folderName));
                    })
                )
                : (
                    isset($dirRaw->default_folder_id)
                    ? $folderList->firstWhere('id', $dirRaw->default_folder_id)
                    : $folderList->first()); // Folder in request ? search by name else select first in category

            if (! isset($folderRaw->id)) { // no folder found
                return $this->error(['categoryName' => $dir, 'folderName' => $folderName], 'Cannot find folder in specified category', 404);
            }

            $folderRaw->load(['videos.metadata.videoTags.tag']);

            $videoList = VideoResource::collection($folderRaw->videos); // VideoResource::collection(Video::where('folder_id', $folderRaw->id)->get());
            $data['folder'] = ['id' => $folderRaw->id, 'name' => $folderRaw->name, 'videos' => $videoList, 'series' => $data['dir']['folders']->first(function ($folder) use ($folderRaw) {
                return $folder->id === $folderRaw->id;
            })->series];

            return $this->success($data, '', 200);
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to parse URL ' . $th->getMessage(), 500);
        }
    }

    public function scanFiles(Request $request, ?Category $category = null) {
        $name = 'Scan Files';
        $description = 'Scans for file changes and loads metadata from all Libraries.';

        if (isset($category)) {
            $name .= " from the Library \"$category->name\"";
            $description = "Scans for file changes and loads metadata from the specified Library \"$category->name\"";
        }

        try {
            $userId = $request->user() ? $request->user()->id : null;
            $task = $this->setupTask($userId, $name, $description);

            $chain = [
                new SyncFiles($task->id),
                new IndexFiles($task->id),
            ];

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

            $videos = $videoQuery->get();
            $folders = $folderQuery->get();

            $videos->chunk(100)->each(function ($chunk) use (&$chain, $task) {
                $chain[] = new VerifyFiles($chunk, $task->id);
            });

            $folders->chunk(100)->each(function ($chunk) use (&$chain, $task) {
                $chain[] = new VerifyFolders($chunk, $task->id);
            });

            $batch = $this->setupBatch($chain, $task);
            $task->update(['batch_id' => $batch->id, 'sub_tasks_total' => count($chain), 'sub_tasks_pending' => count($chain)]);

            return response()->json(['task_id' => $task->id, 'message' => 'Async Task "SCAN FILES" was started.']);
        } catch (\Throwable $th) {
            if ($task) {
                $task->update(['status' => TaskStatus::FAILED, 'ended_at' => now()]);
            }

            return response()->json(['error' => 'Error cannot scan files', 'details' => $th->getMessage()], 500);
        }
    }

    public function indexFiles(Request $request, ?Category $category = null) {
        $name = 'Index Files';
        $description = 'Looks for folder and video changes in in all Libraries.';

        if (isset($category)) {
            $name .= " for Library $category->name";
            $description = "Looks for folder and video changes in the specified Library $category->name";
        }

        $userId = $request->user() ? $request->user()->id : null;
        $task = $this->setupTask($userId, $name, $description, 2);

        try {
            $chain = [
                new SyncFiles($task->id),
                new IndexFiles($task->id),
            ];

            $batch = $this->setupBatch($chain, $task);
            $task->update(['batch_id' => $batch->id]);

            return response()->json(['task_id' => $task->id, 'message' => 'Async Task "INDEX FILES" was started.']);
        } catch (\Throwable $th) {
            if ($task) {
                $task->update(['status' => TaskStatus::FAILED, 'ended_at' => now()]);
            }

            return response()->json(['error' => 'Error cannot index files', 'details' => $th->getMessage()], 500);
            // dump($th);
        }
    }

    public function syncFiles(Request $request) {
        $userId = $request->user() ? $request->user()->id : null;
        $task = $this->setupTask($userId, 'Sync Files', 'Syncs local file structure with database.', 1);

        try {
            $chain = [
                new SyncFiles($task->id),
            ];

            $batch = $this->setupBatch($chain, $task);
            $task->update(['batch_id' => $batch->id]);

            return response()->json(['task_id' => $task->id, 'message' => 'Async Task "SYNC FILES" was started.']);
        } catch (\Throwable $th) {
            if ($task) {
                $task->update(['status' => TaskStatus::FAILED, 'ended_at' => now()]);
            }

            return response()->json(['error' => 'Error cannot sync files', 'details' => $th->getMessage()], 500);
        }
    }

    public function verifyFiles(Request $request, ?Category $category = null) {
        $name = 'Verify Files';
        $description = 'Verifies folder and video metadata for all Libraries.';

        if (isset($category)) {
            $name .= " for Library \"$category->name\"";
            $description = "Verifies folder and video metadata for the specified Library \"$category->name\"";
        }

        try {
            $userId = $request->user() ? $request->user()->id : null;
            $task = $this->setupTask($userId, $name, $description);

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

            $videos = $videoQuery->get();
            $folders = $folderQuery->get();

            $videos->chunk(100)->each(function ($chunk) use (&$chain, $task) {
                $chain[] = new VerifyFiles($chunk, $task->id);
            });

            $folders->chunk(100)->each(function ($chunk) use (&$chain, $task) {
                $chain[] = new VerifyFolders($chunk, $task->id);
            });

            $batch = $this->setupBatch($chain, $task);
            $task->update(['batch_id' => $batch->id, 'sub_tasks_total' => count($chain), 'sub_tasks_pending' => count($chain)]);

            return response()->json(['task_id' => $task->id, 'message' => 'Async Task "VERIFY FILES" was started.']);
        } catch (\Throwable $th) {
            if ($task) {
                $task->update(['status' => TaskStatus::FAILED, 'ended_at' => now()]);
            }

            return response()->json(['error' => 'Error cannot verify files', 'details' => $th->getMessage()], 500);
            // dump('Error cannot verify file metadata');
            // dump($th);
        }
    }

    public function verifyFolders(Request $request, ?Category $category = null) {
        $name = 'Verify Folders';
        $description = 'Verifies folder metadata for all Libraries.';

        if (isset($category)) {
            $name .= " for Library \"$category->name\"";
            $description = "Verifies folder metadata for the specified Library \"$category->name\"";
        }

        try {
            $userId = $request->user() ? $request->user()->id : null;
            $task = $this->setupTask($userId, $name, $description);

            $chain = [];

            $folderQuery = Folder::orderBy('id');

            if ($category) {
                $folderQuery = $folderQuery->whereHas('category', function ($query) use ($category) {
                    $query->where('id', $category->id);
                })->with('category');
            }

            $folders = $folderQuery->get();

            $folders->chunk(100)->each(function ($chunk) use (&$chain, $task) {
                $chain[] = new VerifyFolders($chunk, $task->id);
            });

            $batch = $this->setupBatch($chain, $task);
            $task->update(['batch_id' => $batch->id, 'sub_tasks_total' => count($chain), 'sub_tasks_pending' => count($chain)]);

            return response()->json(['task_id' => $task->id, 'message' => 'Async Task "VERIFY FOLDERS" was started.']);
        } catch (\Throwable $th) {
            if ($task) {
                $task->update(['status' => TaskStatus::FAILED, 'ended_at' => now()]);
            }

            return response()->json(['error' => 'Error cannot verify folders', 'details' => $th->getMessage()], 500);
            // dump('Error cannot verify file metadata');
            // dump($th);
        }
    }

    public function cleanPaths(Request $request) {
        $name = 'Clean Paths';
        $description = 'Cleans file and folder paths for all Libraries.';

        try {
            $userId = $request->user() ? $request->user()->id : null;
            $task = $this->setupTask($userId, $name, $description);

            $chain = [];

            Video::orderBy('id')->chunk(20)->each(function ($chunk) use (&$chain, $task) {
                $chain[] = new CleanVideoPaths($chunk, $task->id);
            });

            Folder::orderBy('id')->chunk(20)->each(function ($chunk) use (&$chain, $task) {
                $chain[] = new CleanFolderPaths($chunk, $task->id);
            });

            $batch = $this->setupBatch($chain, $task);
            $task->update(['batch_id' => $batch->id, 'sub_tasks_total' => count($chain), 'sub_tasks_pending' => count($chain)]);

            return response()->json(['task_id' => $task->id, 'message' => 'Async Task "CLEAN PATHS" was started.']);
        } catch (\Throwable $th) {
            if (isset($task)) {
                $task->update(['status' => TaskStatus::FAILED, 'ended_at' => now()]);
            }

            return response()->json(['error' => 'Error cannot clean folder or video paths', 'details' => $th->getMessage()], 500);
        }
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
        try {
            $userId = $lastTask ? $lastTask->user_id : null;
            $task = $this->setupTask($userId, $name, $description);

            $chain = [];
            foreach ($files as $key => $file) {
                $chain[] = new EmbedUidInMetadata($file['path'], $file['uuid'], $task->id);
            }

            $batch = $this->setupBatch($chain, $task);
            $task->update(['batch_id' => $batch->id, 'sub_tasks_total' => count($chain), 'sub_tasks_pending' => count($chain)]);

            return response()->json(['task_id' => $task->id, 'message' => 'Async Task "EMBED UIDS" was started.']);
        } catch (\Throwable $th) {
            if ($task) {
                $task->update(['status' => TaskStatus::FAILED, 'ended_at' => now()]);
            }

            return response()->json(['error' => 'Error cannot embed UIDs', 'details' => $th->getMessage()], 500);
            // dump($th);
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

    public function setupBatch($chain, Task $task) {
        return Bus::batch($chain)->progress(function (Batch $batch) {
            // $task->refresh();
            // $task->update([
            //     'sub_tasks_pending' => $batch->pendingJobs,
            //     'sub_tasks_failed' => $batch->failedJobs,
            //     'sub_tasks_complete' => $task->sub_tasks_total - $batch->pendingJobs - $batch->failedJobs,
            // ]);
        })->catch(function (Batch $batch, \Throwable $e) use ($task) {
            $task->update([
                'status' => TaskStatus::FAILED,
            ]);

            Log::error('Batch failed', ['task_id' => $task->id, 'error' => $e->getMessage()]);
        })->finally(function (Batch $batch) use ($task) {
            $task->refresh();
            // if task is not still processing, don't change what the current status is
            // otherwise base status on batch info
            $status = $task->status !== TaskStatus::PROCESSING || $task->sub_tasks_total !== $batch->totalJobs ?
                $task->status : ($batch->cancelled() ?
                    TaskStatus::CANCELLED : ($batch->processedJobs() > $task->sub_tasks_total ?
                        TaskStatus::INCOMPLETE :
                        TaskStatus::COMPLETED
                    ));

            if ($task->status == TaskStatus::PROCESSING && $task->sub_tasks_total !== $batch->totalJobs) {
                return;
            }

            $ended_at = now();

            try {
                $started_at = $task->started_at ? \Carbon\Carbon::parse($task->started_at) : null;
                $duration = $started_at ? (int) $ended_at->diffInSeconds($started_at) : 0;
            } catch (\Throwable $th) {
                Log::error('Batch Error on Completion', ['task_id' => $task->id, 'error' => $th->getMessage()]);
                $duration = 0;
            }

            try {
                $this->taskService->updateTask($task->id, [
                    'status' => $status,
                    // 'sub_tasks_pending' => $batch->pendingJobs,
                    // 'sub_tasks_failed' => $batch->failedJobs,
                    // 'sub_tasks_complete' => $task->sub_tasks_total - $batch->pendingJobs - $batch->failedJobs,
                    'ended_at' => $ended_at,
                    'duration' => $duration < 0 ? $duration * -1 : $duration,
                ], $status === TaskStatus::COMPLETED);
            } catch (\Throwable $th) {
                Log::error('Error when completing task', $th->getMessage());
            }
        })->before(function (Batch $batch) use ($task) {
            $this->taskService->updateTask($task->id, [
                'status' => TaskStatus::PROCESSING,
                'started_at' => now(),
            ]);
        })->name($task->name)->dispatch();
    }
}
