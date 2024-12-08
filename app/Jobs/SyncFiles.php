<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use App\Models\Category;
use App\Models\Folder;
use App\Models\Video;
use Illuminate\Bus\Batchable;

class SyncFiles implements ShouldQueue, ShouldBeUnique {
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct() {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void {
        if ($this->batch() && $this->batch()->cancelled()) {
            // Determine if the batch has been cancelled...
            return;
        }

        $this->syncCache();
    }

    public function syncCache() {
        // Idea: Compare categories folders and videos json files with data on sql server. Sync local copies with sql server if this is master storage (ie all files should be available)
        // -> then if you index files, it should delete sql entries correctly if anything there does not exist locally

        $path = "public/media/";
        // $path = "private/media/";

        if (!Storage::exists($path)) {
            $error = 'Missing "media" directory in storage';

            dd(json_encode(array("success" => false, "result" => "", "error" => $error), JSON_UNESCAPED_SLASHES));
        }

        $directories = $this->generateCategories();
        $subDirectories = $this->generateFolders($path);
        $files = $this->generateVideos($path, $subDirectories["data"]["folderStructure"], $directories["data"]["categoryStructure"]);

        if (isset($files["updatedFolderStructure"])) $subDirectories["data"]["folderStructure"] = $files["updatedFolderStructure"];

        $categories = $directories["categoryChanges"];
        $folders = $subDirectories["folderChanges"];
        $videos = $files["videoChanges"];

        Storage::disk('public')->put('categories.json', json_encode($directories["data"], JSON_UNESCAPED_SLASHES));
        Storage::disk('public')->put('folders.json', json_encode($subDirectories["data"], JSON_UNESCAPED_SLASHES));
        Storage::disk('public')->put('videos.json', json_encode($files["data"], JSON_UNESCAPED_SLASHES));

        $data = array("categories" => $categories, "folders" => $folders, "videos" => $videos);

        $dataCache = Storage::json('public/dataCache.json') ?? array();
        $dataCache[date("Y-m-d-h:i:sa")] = array("job" => "sync", "data" => $data);

        Storage::disk('public')->put('dataCache.json', json_encode($dataCache, JSON_UNESCAPED_SLASHES));
        if (!$this->batch()) dump('Categories | Folders | Videos | Data | dataCache', $directories, $subDirectories, $files, $data, $dataCache);
    }

    private function generateCategories() {
        $data = Storage::json('public/categories.json') ?? array("next_ID" => 1, "categoryStructure" => array()); //array("anime"=>1,"tv"=>2,"yogscast"=>3); // read from json
        $scanned = Category::all();  // read folder structure

        $currentID = $data["next_ID"];
        $stored = $data["categoryStructure"];
        $changes = array(); // send to db
        $current = array(); // save into json

        foreach ($scanned as $category) {
            // from database with each category
            // if that exists locally in stored, overwrite with db data (add to current) if different else add to current
            // if not exists, add db directly to current
            $name = $category->name;
            $id = $category->id;
            $current[$name] = $id;

            if (!isset($stored[$name])) {
                // category is not cached locally
                // add
                array_push($changes, array("id" => $id, "name" => $name, "action" => "ADD"));
            } else if (isset($stored[$name])) {
                if ($stored[$name] != $id) {
                    // category is cached locally but id is not the same
                    // overwrite
                    array_push($changes, array("id" => $id, "name" => $name, "action" => "OVERWRITE"));
                }
                // else category is cached locally and id is correct
                // no action
                unset($stored[$name]);
            }

            if ($id >= $currentID) $currentID = $id + 1;
        }

        $data["next_ID"] = $currentID;
        $data["categoryStructure"] = $current;

        return array("categoryChanges" => $changes, "data" => $data);
    }

    private function generateFolders($path) {
        $data = Storage::json('public/folders.json') ?? array("next_ID" => 1, "folderStructure" => array()); //array("anime/frieren"=>array("id"=>0,"name"=>"frieren"),"starwars/andor"=>array("id"=1,"name"="andor")); // read from json
        $cost = 0;
        $scanned = Folder::all();

        $currentID = $data["next_ID"];
        $stored = $data["folderStructure"];
        $changes = array(); // send to db
        $current = array(); // save into json into json
        foreach ($scanned as $folder) {
            // from database with each folder
            // if that exists locally in stored, overwrite with db data (add to current) if different else add to current
            // if not exists, add db directly to current
            $path = $folder->path;
            $name = $folder->name;
            $id = $folder->id;
            $current[$path] = array("id" => $id, "last_scan" => -1);

            if (!isset($stored[$path])) {
                // folder is not cached locally
                // add
                // no last scan
                array_push($changes, array("id" => $id, "name" => $name, "last_scan" => -1, "action" => "ADD"));
            } else {
                if ($stored[$path]['id'] != $id || !isset($stored[$path]['last_scan'])) {
                    // folder is cached locally but id is not the same
                    // overwrite
                    array_push($changes, array("id" => $id, "name" => $name, "last_scan" => -1, "action" => "OVERWRITE"));
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

        $data["next_ID"] = $currentID;
        $data["folderStructure"] = $current;
        return array("folderChanges" => $changes, "data" => $data, "cost" => $cost);
    }

    private function generateVideos($path, $folderStructure) {
        $data = Storage::json('public/videos.json') ?? array("next_ID" => 1, "videoStructure" => array()); //array("anime/frieren/S1E01.mp4"=>array("id"=>0,"name"=>"S1E01"),"starwars/andor/S1E01.mkv"=>array("id"=1,"name"="S1E01.mkv")); // read from json
        $scanned = Video::all();
        $cost = 0;

        $currentID = $data["next_ID"];
        $stored = $data["videoStructure"];
        $changes = array(); // send to db
        $current = array(); // save into json into json

        $foldersCopy = $folderStructure;

        foreach ($scanned as $video) {
            // from database with each video
            // if that exists locally in stored, overwrite with db data (add to current) if different else add to current
            // if not exists, add db directly to current
            $name = $video->name;
            $path = dirname($video->path) . "/" . basename($video->path);
            $id = $video->id;
            $current[$path] = $id;

            if (!isset($stored[$path])) {
                // video is not cached locally
                // add
                array_push($changes, array("id" => $id, "name" => $name, "action" => "ADD"));
            } else if (isset($stored[$path])) {
                if ($stored[$path] != $id) {
                    // video is cached locally but id is not the same
                    // overwrite
                    array_push($changes, array("id" => $id, "name" => $name, "action" => "OVERWRITE"));
                }
                // else video is cached locally and id is correct
                // no action
                unset($stored[$path]);
            }

            $cost += 1;
            if ($id >= $currentID) $currentID = $id + 1;
        }

        $data["next_ID"] = $currentID;
        $data["videoStructure"] = $current;
        return array("videoChanges" => $changes, "data" => $data, "cost" => $cost, "updatedFolderStructure" => $foldersCopy);
    }
}
