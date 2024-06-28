<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Models\Category;
use App\Models\Folder;
use App\Models\Video;
use Illuminate\Database\Eloquent\Collection;

class SyncFiles implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->syncCache();
    }

    public function syncCache() {
        // Idea: Compare categories folders and videos json files with data on sql server. Sync local copies with sql server if this is master storage (ie all files should be available) 
        // -> then if you index files, it should delete sql entries correctly if anything there does not exist locally



        $path = "public\media\\";

        if(!Storage::exists($path)){
            $error = 'Invalid Directory: "media"';
        
            dd(json_encode(array("success"=>false, "result"=>"", "error"=>$error), JSON_UNESCAPED_SLASHES));
        }

        $realPath = Storage::path($path);

        $directories = $this->generateCategories($realPath);   
        $subDirectories = $this->generateFolders($path, $directories["data"]["categoryStructure"]);
        $files = $this->generateVideos($path, $subDirectories["data"]["folderStructure"], $directories["data"]["categoryStructure"]);

        if(isset($files["updatedFolderStructure"])) $subDirectories["data"]["folderStructure"] = $files["updatedFolderStructure"];

        $categories = $directories["categoryChanges"];
        $folders = $subDirectories["folderChanges"];
        $videos = $files["videoChanges"];

        #region
        // $shows = array();
        // $show = array("id"=>null,"name"=>"", "path"=>"", "category_id"=>"");

        // $videos = array();
        // $video = array("id"=>null,"name"=>"","path"=>"","category_id"=>"","show_id"=>null,"file_id"=>null,"episode_id"=>null);

        // //Raw file data connected to video (when folder is not a show)
        // $rawFiles = array();
        // $rawFile = array("name"=> "", "path"=>"", "date" => "", "formattedDate" => null);

        // //Episodic data connected to video (when folder is a show)
        // $episodes = array();
        // $episode = array("id"=>null, "episode_no"=>null, "season_no"=>null, "episode_synopsis"=>"lorem ipsum");
        #endregion
        
        $dbOut = "";

        // $categoryTransactions = array();
        // $folderTransactions = array();
        // $videoTransactions = array();

        // $categoryDeletions = array();
        // $folderDeletions = array();
        // $videoDeletions = array();


        // foreach ($categories as $categoryChange){ // for each in stored, remove from new (delete)
        //     $changeID = $categoryChange['id'];
        //     $changeName = $categoryChange["name"];
        //     $changeMediaContent = $categoryChange["media_content"] ?? 'False';
        //     $changeAction = $categoryChange["action"];

        //     if($changeAction === "INSERT"){
        //         $dbOut .= "INSERT INTO [Categories] VALUES ($changeID, $changeName, $changeMediaContent );\n";       // insert

        //         $transaction = $categoryChange;
        //         unset($transaction["action"]);
        //         array_push($categoryTransactions, $transaction);
        //     }
        //     else{
        //         $dbOut .= "DELETE FROM [Categories] WHERE [Categories].[ID] = {$changeID};\n";
        //         array_push($categoryDeletions, $changeID);
        //     }
        // }

        // foreach ($folders as $folderChange){ // for each in stored, remove from new (delete)
        //     $changeID = $folderChange['id'];
        //     $changeName = $folderChange["name"];
        //     $changePath = $folderChange["path"];
        //     $changeCategoryID = $folderChange["category_id"];
        //     $changeAction = $folderChange["action"];

        //     if($changeAction === "INSERT"){
        //         $dbOut .= "INSERT INTO [Folders] VALUES ({$changeID}, {$changeName}, {$changePath}, {$changeCategoryID} );\n";       // insert

        //         $transaction = $folderChange;
        //         unset($transaction["action"]);
        //         array_push($folderTransactions, $transaction);
        //     }
        //     else{
        //         $dbOut .= "DELETE FROM [Folders] WHERE [Folder].[ID] = {$changeID};\n";
        //         array_push($folderDeletions, $changeID);
        //     }
        // }

        // foreach ($videos as $videoChange){ // for each in stored, remove from new (delete)
        //     $changeID = $videoChange['id'];
        //     $changeName = $videoChange["name"];
        //     $changePath = $videoChange["path"];
        //     $changeFolderID = $videoChange["folder_id"];
        //     $changeDate = $videoChange["date"];
        //     $changeAction = $videoChange["action"];

        //     if($changeAction === "INSERT"){
        //         $dbOut .= "INSERT INTO [Videos] VALUES ({$changeID}, {$changeName}, {$changePath}, {$changeFolderID}, {$changeDate} );\n";       // insert

        //         $transaction = $videoChange;
        //         unset($transaction["action"]);
        //         array_push($videoTransactions, $transaction);
        //     }
        //     else{
        //         $dbOut .= "DELETE FROM [Videos] WHERE [Video].[ID] = {$changeID};\n";
        //         array_push($videoDeletions, $changeID);
        //     }
        // }

        // Video::destroy($videoDeletions);
        // Folder::destroy($folderDeletions);
        // Category::destroy($categoryDeletions);

        // Category::insert($categoryTransactions);
        // Folder::insert($folderTransactions);
        // Video::insert($videoTransactions);

        Storage::disk('public')->put('categories.json', json_encode($directories["data"], JSON_UNESCAPED_SLASHES));
        Storage::disk('public')->put('folders.json', json_encode($subDirectories["data"], JSON_UNESCAPED_SLASHES));
        Storage::disk('public')->put('videos.json', json_encode($files["data"], JSON_UNESCAPED_SLASHES));

        $data = array("categories"=>$categories,"folders"=>$folders,"videos"=>$videos);

        // Storage::disk('public')->put('dataCache.json', json_encode($data, JSON_UNESCAPED_SLASHES));
        dump('Directories | Sub Directories | Files | Data | dbOut', $directories, $subDirectories, $files, $data, $dbOut);
    }

    private function generateCategories($path){
        $data = Storage::json('public\categories.json') ?? array("next_ID"=>1, "categoryStructure" => array()); //array("anime"=>1,"tv"=>2,"yogscast"=>3); // read from json
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

            if(!isset($stored[$name]) || ( isset($stored[$name]) && $stored[$name] != $id)) $changes[$name] = $id;
            if(isset($stored[$name])) unset($stored[$name]);
            // dump($current[$name]);
            if($id > $currentID) $currentID = $id + 1;
        }

        
        // foreach ($stored as $local){
        //     array_push($current, $local);
        // }

        dump($current);

        $data["next_ID"] = $currentID;
        $data["categoryStructure"] = $current;

        return array("categoryChanges"=> $changes, "data" => $data);
    }

    private function generateFolders($path, $categoryStructure){
        $data = Storage::json('public\folders.json') ?? array("next_ID"=>1,"folderStructure"=>array()); //array("anime/frieren"=>array("id"=>0,"name"=>"frieren"),"starwars/andor"=>array("id"=1,"name"="andor")); // read from json
        // $scannedCategories = array_keys($categoryStructure);
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
            $current[$name] = $id;
            if(!isset($stored[$path]) || ( isset($stored[$path]) && $stored[$path] != array("id"=>$id,"name"=>$name))) $changes[$name] = array("id"=>$id,"name"=>$name, "action" => "ADD or OVERWRITE");
            if(isset($stored[$path])) unset($stored[$path]);
            $cost += 1;
            if($id > $currentID) $currentID = $id + 1;
        }

        // foreach ($stored as $local){
        //     array_push($current, $local);
        // }

        dump($stored);
        dump($current);
        $data["next_ID"] = $currentID;
        $data["folderStructure"] = $current;
        return array("folderChanges"=> $changes, "data" => $data, "cost"=>$cost);
    }

    private function generateVideos($path, $folderStructure){
        $data = Storage::json('public\videos.json') ?? array("next_ID"=>1,"videoStructure"=>array()); //array("anime/frieren/S1E01.mp4"=>array("id"=>0,"name"=>"S1E01"),"starwars/andor/S1E01.mkv"=>array("id"=1,"name"="S1E01.mkv")); // read from json
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
            $path = $video->path;
            $name = $video->name;
            $id = $video->id;
            $current[$name] = $id;
            if(!isset($stored[$path]) || ( isset($stored[$path]) && $stored[$path] != array("id"=>$id,"name"=>$name))) $changes[$name] = array("id"=>$id,"name"=>$name);
            if(isset($stored[$path])) unset($stored[$path]);
            $cost += 1;
            if($id > $currentID) $currentID = $id + 1;
        }

        dump($stored);
        dump($current);

        $data["next_ID"] = $currentID;
        $data["videoStructure"] = $current;
        return array("videoChanges"=> $changes, "data" => $data, "cost"=>$cost, "updatedFolderStructure"=>$foldersCopy);
    }
}
