<?php

namespace App\Jobs;

use App\Enums\TaskStatus;
use App\Models\Category;
use App\Models\Folder;
use App\Models\SubTask;
use App\Models\Video;
use App\Services\TaskService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SyncFiles extends ManagedSubTask {
    /**
     * Create a new job instance.
     */
    public function __construct($taskId) {
        if (config('queue.default') === 'redis') {
            $this->onQueue('pipeline');
        }
        $subTask = SubTask::create(['task_id' => $taskId, 'status' => TaskStatus::PENDING, 'name' => 'Sync Files']); //
        $this->taskId = $taskId;
        $this->subTaskId = $subTask->id;
    }

    /**
     * Execute the job.
     */
    public function handle(TaskService $taskService): void {
        if (! $this->beginSubTask($taskService)) {
            return;
        }

        try {
            $this->syncCache($taskService);
            $this->completeSubTask($taskService);
        } catch (\Throwable $th) {
            $this->failSubTask($taskService, $th);
            throw $th;
        }
    }

    public function syncCache(TaskService $taskService) {
        // Idea: Compare categories folders and videos json files with data on sql server. Sync local copies with sql server if this is master storage (ie all files should be available)
        // -> then if you index files, it should delete sql entries correctly if anything there does not exist locally

        $path = 'media/';
        // When moving to private content urls -> $path = "private/media/";

        if (! Storage::disk('public')->exists($path)) {
            $error = 'Missing "media" directory in storage';

            dd(json_encode(['success' => false, 'result' => '', 'error' => $error], JSON_UNESCAPED_SLASHES));
            throw new NotFoundHttpException($error);
        }

        $directories = $this->generateCategories($taskService);
        $subDirectories = $this->generateFolders($taskService);
        $files = $this->generateVideos($taskService, $subDirectories['data']['folderStructure']); // idk what this 2nd/3rd parameter was  $directories['data']['categoryStructure']

        if (isset($files['updatedFolderStructure'])) {
            $subDirectories['data']['folderStructure'] = $files['updatedFolderStructure'];
        }

        $categories = $directories['categoryChanges'];
        $folders = $subDirectories['folderChanges'];
        $videos = $files['videoChanges'];

        $outputFiles = [
            'categories.json' => json_encode($directories['data'], JSON_UNESCAPED_SLASHES),
            'folders.json' => json_encode($subDirectories['data'], JSON_UNESCAPED_SLASHES),
            'videos.json' => json_encode($files['data'], JSON_UNESCAPED_SLASHES),
        ];

        $data = ['categories' => $categories, 'folders' => $folders, 'videos' => $videos];

        $dataCache = Storage::json('dataCache.json') ?? [];
        $dataCache[date('Y-m-d-h:i:sa')] = ['job' => 'sync', 'data' => $data];

        $outputFiles['dataCache.json'] = json_encode($dataCache, JSON_UNESCAPED_SLASHES);

        foreach ($outputFiles as $path => $data) {
            $this->safePut($path, $data);
        }

        if (! $this->batch()) {
            dump('Categories | Folders | Videos | Data | dataCache', $directories, $subDirectories, $files, $data, $dataCache);
        }
    }

    private function generateCategories(TaskService $taskService) {
        $taskService->updateSubTask($this->subTaskId, ['summary' => 'Generating Categories']);

        $data = Storage::json('categories.json') ?? ['next_ID' => 1, 'categoryStructure' => []]; // array("anime"=>1,"tv"=>2,"yogscast"=>3); // read from json
        $scanned = Category::all();  // read folder structure

        $currentID = $data['next_ID'];
        $stored = $data['categoryStructure'];
        $changes = []; // send to db
        $current = []; // save into json

        foreach ($scanned as $category) {
            // from database with each category
            // if that exists locally in stored, overwrite with db data (add to current) if different else add to current
            // if not exists, add db directly to current
            $name = $category->name;
            $id = $category->id;
            $current[$name] = $id;

            if (! isset($stored[$name])) {
                // category is not cached locally
                // add
                array_push($changes, ['id' => $id, 'name' => $name, 'action' => 'ADD']);
            } elseif (isset($stored[$name])) {
                if ($stored[$name] != $id) {
                    // category is cached locally but id is not the same
                    // overwrite
                    array_push($changes, ['id' => $id, 'name' => $name, 'action' => 'OVERWRITE']);
                }
                // else category is cached locally and id is correct
                // no action
                unset($stored[$name]);
            }

            if ($id >= $currentID) {
                $currentID = $id + 1;
            }
        }

        $data['next_ID'] = $currentID;
        $data['categoryStructure'] = $current;

        return ['categoryChanges' => $changes, 'data' => $data];
    }

    private function generateFolders(TaskService $taskService) {
        $taskService->updateSubTask($this->subTaskId, ['summary' => 'Generating Folders', 'progress' => 25]);

        $data = Storage::json('folders.json') ?? ['next_ID' => 1, 'folderStructure' => []]; // array("anime/frieren"=>array("id"=>0,"name"=>"frieren"),"starwars/andor"=>array("id"=1,"name"="andor")); // read from json
        $cost = 0;
        $scanned = Folder::all();

        $currentID = $data['next_ID'];
        $stored = $data['folderStructure'];
        $changes = []; // send to db
        $current = []; // save into json into json
        foreach ($scanned as $folder) {
            // from database with each folder
            // if that exists locally in stored, overwrite with db data (add to current) if different else add to current
            // if not exists, add db directly to current
            $path = $folder->path;
            $name = $folder->name;
            $id = $folder->id;
            $current[$path] = ['id' => $id, 'last_scan' => -1];

            if (! isset($stored[$path])) {
                // folder is not cached locally
                // add
                // no last scan
                array_push($changes, ['id' => $id, 'name' => $name, 'last_scan' => -1, 'action' => 'ADD']);
            } else {
                if ($stored[$path]['id'] != $id || ! isset($stored[$path]['last_scan'])) {
                    // folder is cached locally but id is not the same
                    // overwrite
                    array_push($changes, ['id' => $id, 'name' => $name, 'last_scan' => -1, 'action' => 'OVERWRITE']);
                }
                // else folder is cached locally and is correct
                // no action
                $current[$path] = $stored[$path]; // Copy stored data
                unset($stored[$path]);
            }

            $cost += 1;
            if ($id >= $currentID) {
                $currentID = $id + 1;
            }
        }

        $data['next_ID'] = $currentID;
        $data['folderStructure'] = $current;

        return ['folderChanges' => $changes, 'data' => $data, 'cost' => $cost];
    }

    private function generateVideos(TaskService $taskService, $folderStructure) {
        $taskService->updateSubTask($this->subTaskId, ['summary' => 'Generating Videos', 'progress' => 50]);

        $data = Storage::json('videos.json') ?? ['next_ID' => 1, 'videoStructure' => []]; // array("anime/frieren/S1E01.mp4"=>array("id"=>0,"name"=>"S1E01"),"starwars/andor/S1E01.mkv"=>array("id"=1,"name"="S1E01.mkv")); // read from json
        $scanned = Video::all();
        $cost = 0;

        $currentID = $data['next_ID'];
        $stored = $data['videoStructure'];
        $changes = []; // send to db
        $current = []; // save into json

        $foldersCopy = $folderStructure;

        foreach ($scanned as $video) {
            // from database with each video
            // if that exists locally in stored, overwrite with db data (add to current) if different else add to current
            // if not exists, add db directly to current

            // Needs an update
            // Should remove local entries if they dont exist on the database and cause a rescan

            $name = $video->name;
            $path = dirname($video->path) . '/' . basename($video->path);
            $id = $video->id;
            $current[$path] = $id;

            if (! isset($stored[$path])) {
                // video is not cached locally
                // add
                array_push($changes, ['id' => $id, 'name' => $name, 'action' => 'ADD']);
            } elseif (isset($stored[$path])) {
                if ($stored[$path] != $id) {
                    // video is cached locally but id is not the same
                    // overwrite
                    array_push($changes, ['id' => $id, 'name' => $name, 'action' => 'OVERWRITE']);
                }
                // else video is cached locally and id is correct
                // no action
                unset($stored[$path]);
            }

            $cost += 1;
            if ($id >= $currentID) {
                $currentID = $id + 1;
            }
        }

        $data['next_ID'] = $currentID;
        $data['videoStructure'] = $current;

        return ['videoChanges' => $changes, 'data' => $data, 'cost' => $cost, 'updatedFolderStructure' => $foldersCopy];
    }

    /**
     * Write to storage with error checking
     *
     * @throws \RuntimeException
     */
    private function safePut(string $path, $contents): bool {
        if (! Storage::put($path, $contents)) {
            throw new \RuntimeException("Failed to write file: {$path}");
        }

        if (config('app.debug')) {
            $written = Storage::get($path);
            if ($written !== $contents) {
                throw new \RuntimeException("Write verification failed for: {$path}");
            }
        }

        return true;
    }
}
