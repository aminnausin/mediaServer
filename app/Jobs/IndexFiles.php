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
use Carbon\Carbon;
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

        $this->logToConsole('Starting Index Files');

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
            $error = 'Invalid Directory: "media" directory is missing';

            throw new \Exception($error);
        }

        $mediaRoot = storage_path('app/public/media');

        $directories = $this->generateCategories($mediaRoot);
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
                $categoryTransactions[] = $transaction;
            } else {
                $dbOut .= "DELETE FROM [Categories] WHERE [Categories].[ID] = {$changeID};\n\n";
                $categoryDeletions[] = $changeID;
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
                $folderTransactions[] = $transaction;
            } else {
                $dbOut .= "DELETE FROM [Folders] WHERE [Folder].[ID] = {$changeID};\n\n";
                $folderDeletions[] = $changeID;
            }
        }

        foreach ($seriesEntries as $seriesChange) { // log series additions
            $folderId = $seriesChange['folder_id'];
            $compositeId = $seriesChange['composite_id'];

            $dbOut .= "INSERT INTO [series] VALUES ({$folderId}, {$compositeId});\n\n";       // insert

            $seriesTransactions[] = $seriesChange;
        }

        foreach ($videos as $videoChange) { // for each in stored, remove from new (delete)
            $changeID = $videoChange['id'];
            $changeUUID = $videoChange['uuid'] ?? 'null';
            $changeName = $videoChange['name'];
            $changePath = $videoChange['path'];
            $changeFolderID = $videoChange['folder_id'];
            $changeAction = $videoChange['action'];

            if ($changeAction === 'INSERT' || $changeAction === 'REPLACE') {
                $dbOut .= "{$changeAction} INTO [Videos] VALUES ({$changeID}, {$changeUUID}, {$changeName}, {$changePath}, {$changeFolderID});\n\n";       // insert or replace (this isn't valid its just for reference and understanding)
                $transaction = $videoChange;
                unset($transaction['action']);
                $videoTransactions[] = $transaction;

                continue;
            }

            $dbOut .= "DELETE FROM [Videos] WHERE [Video].[ID] = {$changeID};\n\n";
            $videoDeletions[] = $changeID;
        }

        foreach ($metaDataEntries as $metadataChange) { // log metadata additions
            $videoId = $metadataChange['video_id'];
            $compositeId = $metadataChange['composite_id'];
            $uuid = $metadataChange['uuid'];
            $file_size = $metadataChange['file_size'];
            $duration = $metadataChange['duration'];
            $file_scanned_at = $metadataChange['file_scanned_at'];
            $file_modified_at = $metadataChange['file_modified_at'];

            $dbOut .= "UPSERT INTO [metadata] VALUES ({$videoId}, {$compositeId}, {$uuid}, {$file_size}, {$duration}, {$file_scanned_at}, {$file_modified_at});\n\n";       // upsert

            $metadataTransactions[] = $metadataChange;
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
            Metadata::upsert($metadataTransactions, 'uuid', [
                'video_id',
                'composite_id',
                'file_size',
                'duration',
                'mime_type',
                'media_type',
                'file_scanned_at',
                'file_modified_at',
            ]);

            // One day logging should be put in the database

            Storage::put('categories.json', json_encode($directories['data'], JSON_UNESCAPED_SLASHES));
            Storage::put('folders.json', json_encode($subDirectories['data'], JSON_UNESCAPED_SLASHES));
            Storage::put('videos.json', json_encode($files['data'], JSON_UNESCAPED_SLASHES));

            $data = ['categories' => $categories, 'folders' => $folders, 'videos' => $videos];

            $dataCache = Storage::json('dataCache.json') ?? [];
            $dataCache[date('Y-m-d-h:i:sa')] = ['job' => 'index', 'data' => $data];

            // TODO: stop adding empty data cache entries if the last entry was also empty. Need to check last one but popping removes it and loses the key so I cannot add it back on if it wasnt empty.

            Storage::put('dataCache.json', json_encode($dataCache, JSON_UNESCAPED_SLASHES));
            $this->logToConsole('Categories | Folders | Videos | Changes | SQL ', $directories, ['count' => count($subDirectories['data']['folderStructure'])], ['count' => count($files['data']['videoStructure'])], $data, $dbOut);

            return 'Changed ' . count($data['categories']) . ' libraries, ' . count($data['folders']) . ' folders and ' . count($data['videos']) . " Videos. \n\n$dbOut";
        } catch (BatchCancelledException $e) {
            throw $e;
        } catch (\Throwable $th) {
            $this->logToConsole($th);
            throw new \Exception('Unable to index files, ' . $th->getMessage());
        }
    }

    private function generateCategories($path) {
        $data = Storage::json('categories.json') ?? ['next_ID' => 1, 'categoryStructure' => []]; // array("anime"=>1,"tv"=>2,"yogscast"=>3); // read from json
        $scanned = array_filter(
            scandir($path),
            fn($item) => $item !== '.' &&
                $item !== '..' &&
                is_dir($path . DIRECTORY_SEPARATOR . $item)
        ); // read folder structure

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
                $changes[] = $generated;                                                   // add to new (insert)
                $currentID++;
            }
        }

        foreach ($stored as $remainingID) {
            $generated = ['id' => $remainingID, 'name' => null, 'media_content' => null, 'action' => 'DELETE']; // delete by id
            $changes[] = $generated;                                                   // add to new (delete)
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
                    $changes[] = $generated;
                    $seriesChanges[] = $series;                                                  // add to new (insert)
                    $currentID++;
                }
            }
        }
        foreach ($stored as $remainingFolder) {
            $generated = ['id' => $remainingFolder['id'], 'name' => null, 'path' => null, 'category_id' => null, 'action' => 'DELETE'];  // delete by id -> Used to store just ID -> Now store id and last_scan
            $changes[] = $generated;                                                               // add to new (delete)
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

        $pendingNewFiles = [];
        $deletedVideoIds = [];

        $logicalCompositeIds = [];
        $embeddedUuids = [];

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

            $this->logToConsole("$path$folder");

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
                    $current[$key] = $stored[$key]; // add to current
                    unset($stored[$key]);           // remove from stored

                    continue;
                }

                // Only check uuid on new videos, old video uuid will be checked in verify files with chunking
                try {
                    $mime_type = File::mimeType($absolutePath) ?? null;
                    $is_audio = is_string($mime_type) && str_starts_with($mime_type, 'audio');
                    $media_type = $is_audio ? MediaType::AUDIO : MediaType::VIDEO;
                    $fileMetaData = VerifyFiles::getFileMetadata($absolutePath, 'index');
                } catch (\Throwable $th) {
                    Log::warning('IndexFiles: file skipped during index because it was locked or unavailable', [
                        'name' => $cleanName,
                        'path' => $absolutePath,
                        'error' => $th->getMessage(),
                    ]);

                    continue;
                }

                $embeddedUuid = $fileMetaData['tags']['uuid'] ?? null;
                if ($embeddedUuid && Uuid::isValid($embeddedUuid)) {
                    $embeddedUuids[] = $embeddedUuid;
                }

                $logicalCompositeId = "$folder/$cleanName";
                $logicalCompositeIds[] = $logicalCompositeId;

                $rawDuration = $fileMetaData['format']['duration'] ?? $fileMetaData['streams'][0]['duration'] ?? null;
                $duration = is_numeric($rawDuration) ? floor($rawDuration) : null;

                $pendingNewFiles[] = [
                    'embeddedUuid' => $embeddedUuid,
                    'compositeId' => "$folder/$name",
                    'logicalCompositeId' => $logicalCompositeId,

                    'absolutePath' => $absolutePath,
                    'rawFile' => $rawFile,
                    'folder' => $folder,
                    'cleanName' => $cleanName,
                    'key' => $key,

                    'media_type' => $media_type,
                    'mime_type' => $mime_type,
                    'duration' => $duration,
                ];
            }
        }

        // Deletes videos if not seen and the folder has been modified
        foreach ($stored as $video => $remainingID) { // unseen videos are leftover in $stored array
            if (isset($unModifiedFolders[dirname($video)])) { // if folder was not modified
                $current[$video] = $stored[$video];      // see video

                continue;
            }
            $generated = ['id' => $remainingID, 'uuid' => null, 'name' => null, 'path' => null, 'folder_id' => null, 'action' => 'DELETE'];  // delete by id
            $changes[] = $generated;                // add to new (delete)
            $deletedVideoIds[] = $remainingID;    // mark uuid as deleted
            $cost++;
        }

        // Generate bulk queries for metadata rows that may be upserted into

        // Dedupe query keys (so the same metadata isnt queried twice) (data stays un-touched and conflict is handled elsewhere)
        $embeddedUuids = array_values(array_unique($embeddedUuids));
        $logicalCompositeIds = array_values(array_unique($logicalCompositeIds));

        // Build queries
        $metadataByUuid = Metadata::whereIn('uuid', $embeddedUuids)->get(['uuid', 'video_id', 'composite_id', 'updated_at'])->keyBy('uuid')->all(); // manually normalise the fields? idk what this entails
        $metadataByComposite = Metadata::where(function ($q) use ($deletedVideoIds) {
            $q->whereNull('video_id')
                ->orWhereIn('video_id', $deletedVideoIds);
        })
            ->whereIn('logical_composite_id', $logicalCompositeIds)
            ->orderBy('updated_at', 'desc')
            ->get(['uuid', 'video_id', 'composite_id', 'logical_composite_id', 'updated_at'])
            ->groupBy('logical_composite_id')
            ->map(fn($group) => $group->first())
            ->all();

        // Creates insert and upsert transactions for videos and metadata
        foreach ($pendingNewFiles as $file) {
            [
                'embeddedUuid' => $embeddedUuid,
                'compositeId' => $compositeId,
                'logicalCompositeId' => $logicalCompositeId,

                'absolutePath' => $absolutePath,
                'rawFile' => $rawFile,
                'folder' => $folder,
                'cleanName' => $cleanName,
                'key' => $key,

                'media_type' => $media_type,
                'mime_type' => $mime_type,
                'duration' => $duration,

            ] = $file;

            ['uuid' => $uuid, 'willEmbedUuid' => $willEmbedUuid, 'willReplaceMissing' => $willReplaceMissing] = $this->resolveExistingUuid($embeddedUuid, $compositeId, $logicalCompositeId, $deletedVideoIds, $metadataByUuid, $metadataByComposite);

            if ($willEmbedUuid) {
                $this->embedChain[] = new EmbedUidInMetadata($absolutePath, $uuid, $this->taskId, $currentID); // TODO: Make tagging user configurable, probably by library but always use a uuid
            }

            // Dont add uuid to video if embedding job is to be scheduled. This prevents not knowing if the uuid was applied to the video in case a job fails.

            $mtime = filemtime($rawFile);
            $ctime = filectime($rawFile);

            $generated = [
                'id' => $currentID,
                'uuid' => $willEmbedUuid ? null : $uuid,
                'name' => $cleanName,
                'path' => $key,
                'folder_id' => $folderStructure[$folder]['id'],
                'action' => $willReplaceMissing ? 'REPLACE' : 'INSERT',
            ];
            $metadata = [
                'video_id' => $currentID,
                'composite_id' => $compositeId,
                'uuid' => $uuid,
                'file_size' => filesize($rawFile),
                'duration' => $duration,
                'mime_type' => $mime_type ?? null,
                'media_type' => $media_type,
                'file_scanned_at' => now(),
                'file_modified_at' => Carbon::createFromTimestampUTC($mtime < $ctime ? $mtime : $ctime),
            ];
            $current[$key] = $currentID;    // add to current
            $changes[] = $generated;        // add to new (insert)
            $metadataChanges[] = $metadata; // create metadata (insert)
            $currentID++;
        }

        if ($foldersCopy === $folderStructure) {
            $foldersCopy = null;
        }

        $data['next_ID'] = $currentID;
        $data['videoStructure'] = $current;

        $this->taskService->updateSubTask($this->subTaskId, ['summary' => $this->generatedChangesText(count($changes), 'Video'), 'progress' => 80]);

        return ['videoChanges' => $changes, 'data' => $data, 'cost' => $cost, 'updatedFolderStructure' => $foldersCopy, 'metadataChanges' => $metadataChanges];
    }

    private function resolveExistingUuid(?string $embeddedUuid, string $compositeId, string $logicalCompositeId, array $deletedIds, array $metadataByUuid, array $metadataByComposite): array {
        // sanitise input
        $embeddedUuid = $embeddedUuid && Uuid::isValid($embeddedUuid) ? $embeddedUuid : null;

        /**
         * Scenario 1:
         * the file has a uuid embedded, and there is an existing metadata with the same uuid without an existing video
         * -> dont embed, use the embedded uuid and dont embed but update the metadata composite id
         */
        if ($embeddedUuid && isset($metadataByUuid[$embeddedUuid])) {
            $metadata = $metadataByUuid[$embeddedUuid];
            $isUnlinked = $metadata->video_id === null || in_array($metadata->video_id, $deletedIds, true);

            if ($isUnlinked) {
                $this->logToConsole("Replace $embeddedUuid at {$metadata->composite_id} with new file $logicalCompositeId via uuid match");
                Log::info('INDEX: Replacing missing video', [
                    'method' => 'uuid',
                    'uuid' => $metadata->uuid,
                    'oldComposite' => $metadata->composite_id,
                    'newComposite' => $compositeId,
                ]);
            }

            return [
                'uuid' => $isUnlinked ? $metadata->uuid : Str::uuid()->toString(), // if not unlinked, the file was explicitly duplicated (copied from previously scanned library) -> force new uuid
                'willEmbedUuid' => ! $isUnlinked, // don't embed when matching unlinked uuid found
                'willReplaceMissing' => $isUnlinked,
            ];
        }

        /**
         * Scenario 2:
         * the file has no uuid, but its composite id matches an existing metadata also without an existing video
         * -> embed the existing metadata uuid into the file
         */
        if (isset($metadataByComposite[$logicalCompositeId])) {
            $metadata = $metadataByComposite[$logicalCompositeId];
            $this->logToConsole("Replace {$metadata->uuid} at {$metadata->composite_id} with new file {$compositeId} via composite");

            Log::info('INDEX: Replacing missing video', [
                'method' => 'Composite ID',
                'uuid' => $metadata->uuid,
                'oldComposite' => $metadata->composite_id,
                'newComposite' => $compositeId,
            ]);

            return [
                'uuid' => $metadata->uuid,
                'willEmbedUuid' => true,
                'willReplaceMissing' => true,
            ];
        }

        /**
         * Scenario 3:
         * the file has no uuid and matches no metadata
         * -> generate new uuid and upsert new metadata
         */
        return [
            'uuid' => Str::uuid()->toString(),  // defines uuid to upsert on
            'willEmbedUuid' => true,            // determines if uuid job runs
            'willReplaceMissing' => false,       // for logging only
        ];
    }

    private function generatedChangesText($count, $type) {
        return 'Generated ' . $count . ' ' . $type . ' Changes';
    }

    protected function logToConsole(mixed ...$vars): bool {
        if (config('app.env') !== 'local') {
            return false;
        }

        dump($vars);

        return true;
    }
}
class BatchCancelledException extends \Exception {
}
