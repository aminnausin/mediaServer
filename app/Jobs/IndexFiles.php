<?php

namespace App\Jobs;

use App\Enums\MediaType;
use App\Enums\TaskStatus;
use App\Models\Category;
use App\Models\Folder;
use App\Models\Metadata;
use App\Models\Series;
use App\Models\SubTask;
use App\Models\Video;
use App\Services\TaskService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class IndexFiles extends ManagedSubTask {
    protected $taskService;

    protected $embedChain = [];

    /**
     * Create a new job instance.
     */
    public function __construct($taskId) {
        if (config('queue.default') === 'redis') {
            $this->onQueue('pipeline');
        }

        $subTask = SubTask::create(['task_id' => $taskId, 'status' => TaskStatus::PENDING, 'name' => 'Index Files']); //
        $this->taskId = $taskId;
        $this->subTaskId = $subTask->id;
    }

    /**
     * Execute the job.
     */
    public function handle(TaskService $taskService): void {
        $this->taskService = $taskService; // Only for this job for compatibility since this will be re-written soon
        $this->beginSubTask($taskService, 'Starting Index Files');

        dump('Starting Index Files');

        try {
            $summary = $this->generateData();
            $taskCountUpdates = count($this->embedChain) ? ['sub_tasks_complete' => '++', 'sub_tasks_total' => count($this->embedChain), 'sub_tasks_current' => count($this->embedChain), 'sub_tasks_pending' => count($this->embedChain)] : ['sub_tasks_complete' => '++'];

            $this->completeSubTask($taskService, $summary, $taskCountUpdates);

            foreach ($this->embedChain as $embedTask) {
                $this->batch()->add($embedTask);
            }
        } catch (BatchCancelledException $e) {
            $taskService->updateSubTask($this->subTaskId, ['status' => TaskStatus::CANCELLED, 'summary' => 'Parent Task was Cancelled During the Task']);
        } catch (\Throwable $th) {
            $this->failSubTask($taskService, $th);
            throw $th;
        }
    }

    public function generateData() {
        $path = 'media/';
        $dbOut = '';

        if (! Storage::disk('public')->exists($path)) {
            $error = 'Invalid Directory: "media"';

            throw new \Exception($error);
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

            Category::insert($categoryTransactions);
            Folder::insert($folderTransactions);
            Series::upsert($seriesTransactions, 'composite_id', ['folder_id']);
            Video::insert($videoTransactions);
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
        $this->taskService->updateSubTask($this->subTaskId, ['summary' => $this->generatedChangesText(count($changes), 'Library'), 'progress' => 10]);

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
        $this->taskService->updateSubTask($this->subTaskId, ['summary' => $this->generatedChangesText(count($changes), 'Folder'), 'progress' => 30]);

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
        $unModifiedFolders = [];
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
                $unModifiedFolders['storage/' . basename($path) . "/$folder"] = 1;

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

                // TODO: This line defines what file types are supported. Move this somewhere else that is easy to configure
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                if (strtolower($ext) !== 'mp4' && strtolower($ext) !== 'm4a' && strtolower($ext) !== 'mkv' && strtolower($ext) !== 'mp3' && strtolower($ext) !== 'ogg' && strtolower($ext) !== 'flac' && strtolower($ext) !== 'webm') { // && strtolower($ext) !== 'ogg' && strtolower($ext) !== 'flac' the conversion breaks ogg idk about flac
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
                    // Only check uuid on new videos, old video uuid will be checked in verify files with chunking
                    try {
                        $mime_type = File::mimeType($absolutePath) ?? null;
                        $is_audio = is_string($mime_type) && str_starts_with($mime_type, 'audio');
                        $media_type = $is_audio ? MediaType::AUDIO : MediaType::VIDEO;
                        $fileMetaData = VerifyFiles::getFileMetadata($absolutePath);
                    } catch (\Throwable $th) {
                        Log::warning('IndexFiles: file skipped during index because it was locked or unavailable', [
                            'name' => $cleanName,
                            'path' => $absolutePath,
                            'error' => $th->getMessage(),
                        ]);
                        unset($stored[$key]); // "See" video by clearing stored key but not adding to changes

                        continue;
                    }

                    $uuid = $fileMetaData['tags']['uuid'] ?? $fileMetaData['tags']['uid'] ?? null;
                    $embeddingUuid = false;
                    if (! $uuid || ! Uuid::isValid($uuid)) {
                        $embeddingUuid = true;
                    } else {
                        // Check for an existing file (not deleted) with the scanned uuid only if a uuid was found on the video. Usually this means the user copied the previously scanned video to a new folder.
                        $existingData = Metadata::where('uuid', $uuid)->first();
                        $embeddingUuid = $existingData && File::exists(public_path("storage/media/$existingData->composite_id"));
                    }

                    if ($embeddingUuid) {
                        $uuid = Str::uuid()->toString();
                        $this->embedChain[] = new EmbedUidInMetadata($absolutePath, $uuid, $this->taskId, $currentID); // TODO: Make tagging user configurable, probably by library but always use a uuid
                    }

                    // Dont add uuid to video if embedding job is to be scheduled. This prevents not knowing if the uuid was applied to the video in case a job fails.

                    $mtime = filemtime($rawFile);
                    $ctime = filectime($rawFile);

                    $rawDuration = $fileMetaData['format']['duration'] ?? $fileMetaData['streams'][0]['duration'] ?? null;
                    $duration = is_numeric($rawDuration) ? floor($rawDuration) : null;

                    $generated = ['id' => $currentID, 'uuid' => $embeddingUuid ? null : $uuid, 'name' => $cleanName, 'path' => $key, 'folder_id' => $folderStructure[$folder]['id'], 'date' => date('Y-m-d h:i A', $mtime < $ctime ? $mtime : $ctime), 'action' => 'INSERT'];
                    $metadata = ['video_id' => $currentID, 'composite_id' => "$folder/$name", 'uuid' => $uuid, 'file_size' => filesize($rawFile), 'duration' => $duration, 'mime_type' => $mime_type ?? null, 'media_type' => $media_type, 'date_scanned' => date('Y-m-d h:i:s A'), 'date_uploaded' => date('Y-m-d h:i A', $mtime < $ctime ? $mtime : $ctime)];
                    $current[$key] = $currentID;                                                        // add to current
                    array_push($changes, $generated);                                                   // add to new (insert)
                    array_push($metadataChanges, $metadata);                                            // create metadata (insert)
                    $currentID++;
                }
            }
        }

        // Deletes videos if not seen and the folder has been modified
        foreach ($stored as $video => $remainingID) { // unseen videos are leftover in $stored array
            if (isset($unModifiedFolders[dirname($video)])) { // if folder was not modified
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

        $this->taskService->updateSubTask($this->subTaskId, ['summary' => $this->generatedChangesText(count($changes), 'Video'), 'progress' => 80]);

        return ['videoChanges' => $changes, 'data' => $data, 'cost' => $cost, 'updatedFolderStructure' => $foldersCopy, 'metadataChanges' => $metadataChanges];
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
            Log::warning('Failed to upsert metadata on index', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);
            throw $th;
        }
    }

    private function generatedChangesText($count, $type) {
        return 'Generated ' . $count . ' ' . $type . ' Changes';
    }
}
class BatchCancelledException extends \Exception {}
