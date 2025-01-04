<?php

namespace App\Jobs;

use App\Enums\TaskStatus;
use App\Models\Category;
use App\Models\Folder;
use App\Models\SubTask;
use App\Models\Video;
use App\Services\TaskService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SyncFiles implements ShouldBeUnique, ShouldQueue {
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $taskId;

    protected $subTaskId;

    protected $startedAt;

    protected $taskService;

    /**
     * Create a new job instance.
     */
    public function __construct($taskId) {
        //
        $subTask = SubTask::create(['task_id' => $taskId, 'status' => TaskStatus::PENDING, 'name' => 'Sync Files']); //
        $this->taskId = $taskId;
        $this->subTaskId = $subTask->id;
    }

    /**
     * Execute the job.
     */
    public function handle(TaskService $taskService): void {
        $this->taskService = $taskService;

        if ($this->batch() && $this->batch()->cancelled()) {
            // Determine if the batch has been cancelled...
            $this->taskService->updateSubTask($this->subTaskId, ['status' => TaskStatus::CANCELLED, 'summary' => 'Parent Task was Cancelled']);

            return;
        }

        $this->startedAt = now();
        $this->taskService->updateTaskCounts($this->taskId, ['sub_tasks_pending' => '--']);
        $this->taskService->updateSubTask($this->subTaskId, ['status' => TaskStatus::PROCESSING, 'started_at' => $this->startedAt]);

        try {
            $this->syncCache();
            $endedAt = now();
            $duration = (int) $this->startedAt->diffInSeconds($endedAt);
            $this->taskService->updateTaskCounts($this->taskId, ['sub_tasks_complete' => '++'], false);
            $this->taskService->updateSubTask($this->subTaskId, ['status' => TaskStatus::COMPLETED, 'summary' => '', 'ended_at' => $endedAt, 'duration' => $duration, 'progress' => 100]);
        } catch (\Throwable $th) {
            $endedAt = now();
            $duration = (int) $this->startedAt->diffInSeconds($endedAt);
            $this->taskService->updateTaskCounts($this->taskId, ['sub_tasks_failed' => '++']);
            $this->taskService->updateSubTask($this->subTaskId, ['status' => TaskStatus::FAILED, 'summary' => 'Error: ' . $th->getMessage(), 'ended_at' => $endedAt, 'duration' => $duration]);
            throw $th;
        }
    }

    public function syncCache() {
        // Idea: Compare categories folders and videos json files with data on sql server. Sync local copies with sql server if this is master storage (ie all files should be available)
        // -> then if you index files, it should delete sql entries correctly if anything there does not exist locally

        $path = 'media/';
        // $path = "private/media/";

        if (! Storage::disk('public')->exists($path)) {
            $error = 'Missing "media" directory in storage';

            dd(json_encode(['success' => false, 'result' => '', 'error' => $error], JSON_UNESCAPED_SLASHES));
            throw new \Exception($error, 404);
        }

        $directories = $this->generateCategories();
        $subDirectories = $this->generateFolders($path);
        $files = $this->generateVideos($path, $subDirectories['data']['folderStructure'], $directories['data']['categoryStructure']);

        if (isset($files['updatedFolderStructure'])) {
            $subDirectories['data']['folderStructure'] = $files['updatedFolderStructure'];
        }

        $categories = $directories['categoryChanges'];
        $folders = $subDirectories['folderChanges'];
        $videos = $files['videoChanges'];

        Storage::put('categories.json', json_encode($directories['data'], JSON_UNESCAPED_SLASHES));
        Storage::put('folders.json', json_encode($subDirectories['data'], JSON_UNESCAPED_SLASHES));
        Storage::put('videos.json', json_encode($files['data'], JSON_UNESCAPED_SLASHES));

        $data = ['categories' => $categories, 'folders' => $folders, 'videos' => $videos];

        $dataCache = Storage::json('dataCache.json') ?? [];
        $dataCache[date('Y-m-d-h:i:sa')] = ['job' => 'sync', 'data' => $data];

        Storage::put('dataCache.json', json_encode($dataCache, JSON_UNESCAPED_SLASHES));

        if (! $this->batch()) {
            dump('Categories | Folders | Videos | Data | dataCache', $directories, $subDirectories, $files, $data, $dataCache);
        }
    }

    private function generateCategories() {
        $this->taskService->updateSubTask($this->subTaskId, ['summary' => 'Generating Categories']);

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

    private function generateFolders($path) {
        $this->taskService->updateSubTask($this->subTaskId, ['summary' => 'Generating Folders', 'progress' => 25]);

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

    private function generateVideos($path, $folderStructure) {
        $this->taskService->updateSubTask($this->subTaskId, ['summary' => 'Generating Videos', 'progress' => 50]);

        $data = Storage::json('videos.json') ?? ['next_ID' => 1, 'videoStructure' => []]; // array("anime/frieren/S1E01.mp4"=>array("id"=>0,"name"=>"S1E01"),"starwars/andor/S1E01.mkv"=>array("id"=1,"name"="S1E01.mkv")); // read from json
        $scanned = Video::all();
        $cost = 0;

        $currentID = $data['next_ID'];
        $stored = $data['videoStructure'];
        $changes = []; // send to db
        $current = []; // save into json into json

        $foldersCopy = $folderStructure;

        foreach ($scanned as $video) {
            // from database with each video
            // if that exists locally in stored, overwrite with db data (add to current) if different else add to current
            // if not exists, add db directly to current
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
}
