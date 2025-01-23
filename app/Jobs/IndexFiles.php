<?php

namespace App\Jobs;

use App\Enums\TaskStatus;
use App\Models\Category;
use App\Models\Folder;
use App\Models\Metadata;
use App\Models\Series;
use App\Models\SubTask;
use App\Models\Video;
use App\Services\TaskService;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class IndexFiles implements ShouldBeUnique, ShouldQueue {
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $taskId;

    protected $subTaskId;

    protected $startedAt;

    protected $taskService;

    protected $embedChain = [];

    /**
     * Create a new job instance.
     */
    public function __construct($taskId) {
        $subTask = SubTask::create(['task_id' => $taskId, 'status' => TaskStatus::PENDING, 'name' => 'Index Files']); //
        $this->taskId = $taskId;
        $this->subTaskId = $subTask->id;
    }

    /**
     * Execute the job.
     */
    public function handle(TaskService $taskService): void {
        $this->taskService = $taskService;

        if ($this->batch()->cancelled()) {
            // Determine if the batch has been cancelled...
            $this->taskService->updateSubTask($this->subTaskId, ['status' => TaskStatus::CANCELLED, 'summary' => 'Parent Task was Cancelled']);

            return;
        }

        dump('Starting Index Files');

        $this->startedAt = now();
        $this->taskService->updateTaskCounts($this->taskId, ['sub_tasks_pending' => '--']);
        $this->taskService->updateSubTask($this->subTaskId, ['status' => TaskStatus::PROCESSING, 'started_at' => $this->startedAt, 'summary' => 'Starting Index Files']);

        try {
            $summary = $this->generateData();
            $endedAt = now();
            $duration = (int) $this->startedAt->diffInSeconds($endedAt);

            if (count($this->embedChain)) {
                $this->taskService->updateTaskCounts($this->taskId, ['sub_tasks_complete' => '++', 'sub_tasks_total' => count($this->embedChain), 'sub_tasks_pending' => count($this->embedChain)]);
                foreach ($this->embedChain as $key => $embedTask) {
                    Bus::dispatch($embedTask);
                }
                // $controller = new DirectoryController($this->taskService);
                // $controller->embedUIDs($this->taskId, "Embed UIDs for task $this->taskId via Index Files", $this->embedChain);
            } else {
                $this->taskService->updateTaskCounts($this->taskId, ['sub_tasks_complete' => '++'], false);
            }

            $this->taskService->updateSubTask($this->subTaskId, [
                'status' => TaskStatus::COMPLETED,
                'summary' => $summary,
                'progress' => 100,
                'ended_at' => $endedAt,
                'duration' => $duration,
            ]);
        } catch (BatchCancelledException $e) {
            $this->taskService->updateSubTask($this->subTaskId, ['status' => TaskStatus::CANCELLED, 'summary' => 'Parent Task was Cancelled During the Task']);
        } catch (\Throwable $th) {
            $endedAt = now();
            $duration = (int) $this->startedAt->diffInSeconds($endedAt);
            $this->taskService->updateTaskCounts($this->taskId, ['sub_tasks_failed' => '++']);
            $this->taskService->updateSubTask($this->subTaskId, ['status' => TaskStatus::FAILED, 'summary' => 'Error: ' . $th->getMessage(), 'ended_at' => $endedAt, 'duration' => $duration]);
            throw $th;
        }
    }

    public function generateData() {
        $path = 'media/';
        $dbOut = '';

        if (! Storage::disk('public')->exists($path)) {
            $error = 'Invalid Directory: "media"';

            throw new \Exception($error);
            // dd(json_encode(['success' => false, 'result' => '', 'error' => $error], JSON_UNESCAPED_SLASHES));
        }

        $realPath = Storage::disk('public')->path($path);

        $directories = $this->generateCategories($realPath);
        $subDirectories = $this->generateFolders($path, $directories['data']['categoryStructure']);
        $files = $this->generateVideos($path, $subDirectories['data']['folderStructure'], $directories['data']['categoryStructure']);

        if (isset($files['updatedFolderStructure'])) {
            $subDirectories['data']['folderStructure'] = $files['updatedFolderStructure'];
        }

        $categories = $directories['categoryChanges'];
        $folders = $subDirectories['folderChanges'];
        $videos = $files['videoChanges'];

        $seriesEntries = $subDirectories['seriesChanges'];
        $metaDataEntries = $files['metadataChanges'];

        $categoryTransactions = [];
        $folderTransactions = [];
        $videoTransactions = [];

        $seriesTransactions = [];
        $metadataTransactions = [];

        $categoryDeletions = [];
        $folderDeletions = [];
        $videoDeletions = [];

        foreach ($categories as $categoryChange) { // for each in stored, remove from new (delete)
            $changeID = $categoryChange['id'];
            $changeName = $categoryChange['name'];
            $changeMediaContent = $categoryChange['media_content'] ?? 'False';
            $changeAction = $categoryChange['action'];

            if ($changeAction === 'INSERT') {
                $dbOut .= "INSERT INTO [Categories] VALUES ($changeID, $changeName, $changeMediaContent );\n\n";       // insert

                $transaction = $categoryChange;
                unset($transaction['action']);
                array_push($categoryTransactions, $transaction);
            } else {
                $dbOut .= "DELETE FROM [Categories] WHERE [Categories].[ID] = {$changeID};\n\n";
                array_push($categoryDeletions, $changeID);
            }
        }

        foreach ($folders as $folderChange) { // for each in stored, remove from new (delete)
            $changeID = $folderChange['id'];
            $changeName = $folderChange['name'];
            $changePath = $folderChange['path'];
            $changeCategoryID = $folderChange['category_id'];
            $changeAction = $folderChange['action'];

            if ($changeAction === 'INSERT') {
                $dbOut .= "INSERT INTO [Folders] VALUES ({$changeID}, {$changeName}, {$changePath}, {$changeCategoryID} );\n\n";       // insert

                $transaction = $folderChange;
                unset($transaction['action']);
                array_push($folderTransactions, $transaction);
            } else {
                $dbOut .= "DELETE FROM [Folders] WHERE [Folder].[ID] = {$changeID};\n\n";
                array_push($folderDeletions, $changeID);
            }
        }

        foreach ($seriesEntries as $seriesChange) { // log series additions
            $folderID = $seriesChange['folder_id'];
            $compositeID = $seriesChange['composite_id'];

            $dbOut .= "INSERT INTO [series] VALUES ({$folderID}, {$compositeID});\n\n";       // insert

            array_push($seriesTransactions, $seriesChange);
        }

        foreach ($videos as $videoChange) { // for each in stored, remove from new (delete)
            $changeID = $videoChange['id'];
            $changeUUID = $videoChange['uuid'];
            $changeName = $videoChange['name'];
            $changePath = $videoChange['path'];
            $changeFolderID = $videoChange['folder_id'];
            $changeDate = $videoChange['date'];
            $changeAction = $videoChange['action'];

            if ($changeAction === 'INSERT') {
                $dbOut .= "INSERT INTO [Videos] VALUES ({$changeID}, {$changeUUID}, {$changeName}, {$changePath}, {$changeFolderID}, {$changeDate});\n\n";       // insert

                $transaction = $videoChange;
                unset($transaction['action']);
                array_push($videoTransactions, $transaction);
            } else {
                $dbOut .= "DELETE FROM [Videos] WHERE [Video].[ID] = {$changeID};\n\n";
                array_push($videoDeletions, $changeID);
            }
        }

        foreach ($metaDataEntries as $metadataChange) { // log metadata additions
            $videoID = $metadataChange['video_id'];
            $compositeID = $metadataChange['composite_id'];
            $uuid = $metadataChange['uuid'];
            $file_size = $metadataChange['file_size'];
            $duration = $metadataChange['duration'];
            $date_scanned = $metadataChange['date_scanned'];
            $date_uploaded = $metadataChange['date_uploaded'];

            $dbOut .= "UPSERT INTO [metadata] VALUES ({$videoID}, {$compositeID}, {$uuid}, {$file_size}, {$duration}, {$date_scanned}, {$date_uploaded});\n\n";       // upsert

            array_push($metadataTransactions, $metadataChange);
        }

        try {
            if ($this->batch()->cancelled()) {
                throw new BatchCancelledException;
            }

            Video::destroy($videoDeletions);
            Folder::destroy($folderDeletions);
            Category::destroy($categoryDeletions);

            // Series::upsert(['folder_id'=>1,'composite_id'=>'anime/frieren'], 'composite_id', ['folder_id']);
            // Metadata::upsert(['video_id'=>1,'composite_id'=>'anime/frieren/S1E02.mp4'], 'composite_id', ['video_id']);

            Category::insert($categoryTransactions);
            Folder::insert($folderTransactions);
            Series::upsert($seriesTransactions, 'composite_id', ['folder_id']);
            Video::insert($videoTransactions);
            // Metadata::upsert($metadataTransactions, ['composite_id', 'uuid'], ['video_id']);
            // Iterate through the metadata transactions and call upsertMetadata
            foreach ($metadataTransactions as $data) {
                $this->upsertMetadata($data);
            }

            // One day logging should be put in the database

            Storage::put('categories.json', json_encode($directories['data'], JSON_UNESCAPED_SLASHES));
            Storage::put('folders.json', json_encode($subDirectories['data'], JSON_UNESCAPED_SLASHES));
            Storage::put('videos.json', json_encode($files['data'], JSON_UNESCAPED_SLASHES));

            $data = ['categories' => $categories, 'folders' => $folders, 'videos' => $videos];

            $dataCache = Storage::json('dataCache.json') ?? [];
            $dataCache[date('Y-m-d-h:i:sa')] = ['job' => 'index', 'data' => $data];

            // TODO: stop adding empty data cache entries if the last entry was also empty. Need to check last one but popping removes it and loses the key so I cannot add it back on if it wasnt empty.

            Storage::put('dataCache.json', json_encode($dataCache, JSON_UNESCAPED_SLASHES));
            // dump('Categories | Folders | Videos | Data | SQL | DataCache', $directories, $subDirectories, $files, $data, $dbOut, $dataCache);
            dump('Categories | Folders | Videos | Changes | SQL ', $directories, ['count' => count($subDirectories['data']['folderStructure'])], ['count' => count($files['data']['videoStructure'])], $data, $dbOut);

            return 'Changed ' . count($data['categories']) . ' libraries, ' . count($data['folders']) . ' folders and ' . count($data['videos']) . " Videos. \n\n$dbOut";
        } catch (BatchCancelledException $e) {
            throw $e;
        } catch (\Throwable $th) {
            dump($th);
            throw new \Exception('Unable to index files, ' . $th->getMessage());
        }
    }

    private function generateCategories($path) {
        $data = Storage::json('categories.json') ?? ['next_ID' => 1, 'categoryStructure' => []]; // array("anime"=>1,"tv"=>2,"yogscast"=>3); // read from json
        $scanned = array_map('htmlspecialchars', scandir($path));  // read folder structure

        $currentID = $data['next_ID'];
        $stored = $data['categoryStructure'];
        $changes = []; // send to db
        $current = []; // save into json

        /*
            If scanned is in stored, add to current, remove from stored
            If scanned not in stored, add to current, add to new (insert), id++

            after:

            for each in stored, remove from new (delete)

            send new to database

            save current to json
        */
        foreach ($scanned as $local) { // O(n) where n = number of already known categories
            if (is_dir($local)) {
                continue;
            } // ? . and .. are dirs

            if ($this->batch()->cancelled()) {
                throw new BatchCancelledException;
            }

            $name = basename($local);

            if (isset($stored[$name])) {                                                          // If scanned is in stored, add to current, remove from stored
                $current[$name] = $stored[$name];                                                   // add to current
                unset($stored[$name]);                                                              // remove from stored
            } else {                                                                               // If scanned not in stored, add to current, add to new (insert), id++
                $generated = ['id' => $currentID, 'name' => $name, 'media_content' => 'False', 'action' => 'INSERT'];
                $current[$name] = $currentID;                                                       // add to current
                array_push($changes, $generated);                                                   // add to new (insert)
                $currentID++;
            }
        }

        foreach ($stored as $remainingID) {
            $generated = ['id' => $remainingID, 'name' => null, 'media_content' => null, 'action' => 'DELETE']; // delete by id
            array_push($changes, $generated);                                                   // add to new (delete)
        }

        $data['next_ID'] = $currentID;
        $data['categoryStructure'] = $current;
        $this->taskService->updateSubTask($this->subTaskId, ['summary' => 'Generated ' . count($changes) . ' Library Changes', 'progress' => 10]);

        return ['categoryChanges' => $changes, 'data' => $data];
    }

    private function generateFolders($path, $categoryStructure) {
        $data = Storage::json('folders.json') ?? ['next_ID' => 1, 'folderStructure' => []]; // array("anime/frieren"=>array("id"=>0,"name"=>"frieren"),"starwars/andor"=>array("id"=1,"name"="andor")); // read from json
        $scannedCategories = array_keys($categoryStructure);
        $cost = 0;

        $currentID = $data['next_ID'];
        $stored = $data['folderStructure'];
        $changes = []; // send to db
        $current = []; // save into json into json

        $seriesChanges = [];

        /*foreach ($stored as $savedFolder){ // O(n) where n = number of already known categories
            $currentID = max($currentID, $savedFolder["id"] + 1); // gets max currently used id
            $cost ++;

            Double Foreach loop

            o(M*N) where M = number of categories and N = number of files in each category
            really becomes O(n) where n = number of files since N is not constant

            for each folder in each category
                key = category/folder
                if isset in stored
                    // old folder already seen

                    add to current -> key, id, basename(folder)
                    remove from stored
                    continue
                else
                    // new folder not seen before

                    add to current -> key, id, basename(folder) ?
                    add to changes (insert)
                    id += 1;
        }*/

        foreach ($scannedCategories as $category) { // O(n) where n = number of folders * 2 (for scan)
            $cost++;

            $folders = Storage::disk('public')->directories("$path/$category"); // Immediate folders (dont scan sub folders)

            foreach ($folders as $folder) {
                if ($this->batch()->cancelled()) {
                    throw new BatchCancelledException;
                }
                $cost++;

                $name = basename($folder);
                $key = "$category/$name";

                if ($name === '.thumbs') {
                    continue;
                }

                if (isset($stored[$key])) {
                    $current[$key] = $stored[$key];                                                     // add to current
                    unset($stored[$key]);                                                               // remove from stored
                } else {
                    $generated = ['id' => $currentID, 'name' => $name, 'path' => $key, 'category_id' => $categoryStructure[$category], 'action' => 'INSERT'];
                    $series = ['folder_id' => $currentID, 'composite_id' => $key];
                    $current[$key] = ['id' => $currentID, 'last_scan' => -1];                                    // add to current
                    array_push($changes, $generated);
                    array_push($seriesChanges, $series);                                                  // add to new (insert)
                    $currentID++;
                }
            }
        }
        foreach ($stored as $remainingFolder) {
            $generated = ['id' => $remainingFolder['id'], 'name' => null, 'path' => null, 'category_id' => null, 'action' => 'DELETE'];  // delete by id -> Used to store just ID -> Now store id and last_scan
            array_push($changes, $generated);                                                               // add to new (delete)
            $cost++;
        }

        $data['next_ID'] = $currentID;
        $data['folderStructure'] = $current;
        $this->taskService->updateSubTask($this->subTaskId, ['summary' => 'Generated ' . count($changes) . ' Folder Changes', 'progress' => 30]);

        return ['folderChanges' => $changes, 'data' => $data, 'cost' => $cost, 'seriesChanges' => $seriesChanges];
    }

    private function generateVideos($path, $folderStructure) {
        $data = Storage::json('videos.json') ?? ['next_ID' => 1, 'videoStructure' => []]; // array("anime/frieren/S1E01.mp4"=>array("id"=>0,"name"=>"S1E01"),"starwars/andor/S1E01.mkv"=>array("id"=1,"name"="S1E01.mkv")); // read from json
        $scannedFolders = array_keys($folderStructure);
        $cost = 0;

        $currentID = $data['next_ID'];
        $stored = $data['videoStructure'];
        $changes = []; // send to db
        $current = []; // save into json into json

        $metadataChanges = [];

        $foldersCopy = $folderStructure;
        $unModefiedFolders = [];
        $rawPath = Storage::disk('public')->path('');

        /*foreach ($stored as $savedVideo){ // O(n) where n = number of already known categories
            $currentID = max($currentID, $savedVideo + 1); // gets max currently used id
            $cost ++;
            Double Foreach loop

            o(M*N) where M = number of categories and N = number of files in each category
            really becomes O(n) where n = number of files since N is not constant

            for each video in each category
                folder = parent directory of video
                key = category/folder/video
                if isset in stored
                    // old video already seen

                    add to current -> key, id, basename(folder)
                    remove from stored
                    continue
                else
                    // new folder not seen before

                    add to current -> key, id, basename(folder) ?
                    add to changes (insert)
                    id += 1;
        }*/

        foreach ($scannedFolders as $folder) { // O(n) where n = number of folders * 2 (for scan)
            $cost++;
            $folderAccessTime = filemtime("$rawPath" . "media/$folder");

            if ($folderAccessTime <= $folderStructure[$folder]['last_scan']) {
                $unModefiedFolders['storage/' . basename($path) . "/$folder"] = 1;

                continue;
            }

            $files = Storage::disk('public')->files("$path$folder"); // Immediate folders (dont scan sub folders)
            $foldersCopy[$folder]['last_scan'] = $folderAccessTime;

            dump("$path$folder");

            foreach ($files as $file) {
                if ($this->batch()->cancelled()) {
                    throw new BatchCancelledException;
                }
                $cost++;
                $absolutePath = str_replace('\\', '/', Storage::disk('public')->path('')) . $file;

                $ext = pathinfo($file, PATHINFO_EXTENSION);
                if (strtolower($ext) !== 'mp4' && strtolower($ext) !== 'mkv' && strtolower($ext) !== 'mp3' && strtolower($ext) !== 'ogg' && strtolower($ext) !== 'flac') { // && strtolower($ext) !== 'ogg' && strtolower($ext) !== 'flac' the conversion breaks ogg idk about flac
                    continue;
                }

                $name = basename($file);
                $cleanName = basename($file, ".$ext");
                $key = 'storage/' . basename($path) . "/$folder/$name";
                $rawFile = "$rawPath$file";

                if (isset($stored[$key])) {
                    $current[$key] = $stored[$key];                                                     // add to current
                    unset($stored[$key]);                                                               // remove from stored
                } else {
                    $mime_type = File::mimeType($absolutePath) ?? null;

                    // Only check uuid on new videos, old video uuid will be checked in verify files with chunking
                    $fileMetaData = VerifyFiles::getFileMetadata($absolutePath);
                    $uuid = isset($fileMetaData['tags']['uuid']) ? $fileMetaData['tags']['uuid'] : (isset($fileMetaData['tags']['uid']) ? $fileMetaData['tags']['uid'] : null);
                    $embeddingUuid = false;
                    if (! $uuid || ! Uuid::isValid($uuid)) {
                        $uuid = Str::uuid()->toString();
                        $this->embedChain[] = new EmbedUidInMetadata($absolutePath, $uuid, $this->taskId, $currentID);
                        $embeddingUuid = true;
                        // $this->embedChain[] = ["path" => $absolutePath, "uid" => $uuid];
                        // EmbedUidInMetadata::dispatch($absolutePath, $uuid, $this->taskId);
                    }

                    // Dont add uuid to video if embedding job is to be scheduled. This prevents not knowing if the uuid was applied to the video in case a job fails.

                    $mtime = filemtime($rawFile);
                    $ctime = filectime($rawFile);

                    $generated = ['id' => $currentID, 'uuid' => $embeddingUuid ? null : $uuid, 'name' => $cleanName, 'path' => $key, 'folder_id' => $folderStructure[$folder]['id'], 'date' => date('Y-m-d h:i A', $mtime < $ctime ? $mtime : $ctime), 'action' => 'INSERT'];
                    $metadata = ['video_id' => $currentID, 'composite_id' => "$folder/$name", 'uuid' => $uuid, 'file_size' => filesize($rawFile), 'duration' => isset($fileMetaData['duration']) ? (int) $fileMetaData['duration'] : null, 'mime_type' => $mime_type ?? null, 'date_scanned' => date('Y-m-d h:i:s A'), 'date_uploaded' => date('Y-m-d h:i A', $mtime < $ctime ? $mtime : $ctime)];
                    $current[$key] = $currentID;                                                        // add to current
                    array_push($changes, $generated);                                                   // add to new (insert)
                    array_push($metadataChanges, $metadata);                                            // create metadata (insert)
                    $currentID++;
                }
            }
        }

        foreach ($stored as $video => $remainingID) { // unseen videos
            if (isset($unModefiedFolders[dirname($video)])) { // if folder was not modefied
                $current[$video] = $stored[$video];      // see video

                continue;
            }
            $generated = ['id' => $remainingID, 'uuid' => null, 'name' => null, 'path' => null, 'folder_id' => null, 'date' => null, 'action' => 'DELETE'];  // delete by id
            array_push($changes, $generated);                                                               // add to new (delete)
            $cost++;
        }

        if ($foldersCopy === $folderStructure) {
            $foldersCopy = null;
        }

        $data['next_ID'] = $currentID;
        $data['videoStructure'] = $current;

        $this->taskService->updateSubTask($this->subTaskId, ['summary' => 'Generated ' . count($changes) . ' Video Changes', 'progress' => 80]);

        return ['videoChanges' => $changes, 'data' => $data, 'cost' => $cost, 'updatedFolderStructure' => $foldersCopy, 'metadataChanges' => $metadataChanges];
    }

    // Not Using
    private function embedUidInMetadataDirect($filePath, $uid) {
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        // Define a temporary file path with the correct extension
        $tempFilePath = $filePath . '.tmp';

        // Map file extensions to FFmpeg formats
        $formatMap = ['mp4' => 'mp4', 'mkv' => 'matroska'];
        // Determine the correct format to use
        $format = isset($formatMap[$ext]) ? $formatMap[$ext] : $ext;

        $command = [
            'ffmpeg',
            '-i',
            $filePath,
            '-c',
            'copy',
            '-movflags',
            'use_metadata_tags',
            '-metadata',
            "uid=$uid",
            '-f',
            $format,
            $tempFilePath,
        ];
        // Execute the FFmpeg command
        $process = new Process($command);
        $process->run();

        // Check if the process was successful
        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        // Replace the original file with the temporary file
        if (file_exists($tempFilePath)) {
            // Get the original file's timestamps
            $originalCreatedTime = filectime($filePath);
            $originalModifiedTime = filemtime($filePath);
            // Replace the original file with the temporary file
            rename($tempFilePath, $filePath);
            // Restore the original timestamps
            touch($filePath, $originalModifiedTime, $originalCreatedTime);
        } else {
            throw new Exception('Failed to create the temporary file with metadata.');
        }
    }

    public function upsertMetadata($data) {
        // update by UUID first
        try {
            $metadata = Metadata::where('uuid', $data['uuid'])->first();

            if ($metadata) {
                Metadata::where('uuid', $data['uuid'])->update($data);
            } else {
                // If UUID not found, try to update by composite_id
                $metadata = Metadata::where('composite_id', $data['composite_id'])->first();

                if ($metadata) {
                    Metadata::where('composite_id', $data['composite_id'])->update($data);
                } else {
                    // If neither found, insert a new record
                    Metadata::insert($data);
                }
            }
        } catch (\Throwable $th) {
            dump($th);
            throw $th;
        }
    }
}
class BatchCancelledException extends \Exception {
}
