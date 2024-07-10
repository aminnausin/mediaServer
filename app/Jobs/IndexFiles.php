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

class IndexFiles implements ShouldQueue, ShouldBeUnique
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
        $this->generateData();
    }

    public function generateData() {
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

        $categoryTransactions = array();
        $folderTransactions = array();
        $videoTransactions = array();

        $categoryDeletions = array();
        $folderDeletions = array();
        $videoDeletions = array();


        foreach ($categories as $categoryChange){ // for each in stored, remove from new (delete)
            $changeID = $categoryChange['id'];
            $changeName = $categoryChange["name"];
            $changeMediaContent = $categoryChange["media_content"] ?? 'False';
            $changeAction = $categoryChange["action"];

            if($changeAction === "INSERT"){
                $dbOut .= "INSERT INTO [Categories] VALUES ($changeID, $changeName, $changeMediaContent );\n";       // insert

                $transaction = $categoryChange;
                unset($transaction["action"]);
                array_push($categoryTransactions, $transaction);
            }
            else{
                $dbOut .= "DELETE FROM [Categories] WHERE [Categories].[ID] = {$changeID};\n";
                array_push($categoryDeletions, $changeID);
            }
        }

        foreach ($folders as $folderChange){ // for each in stored, remove from new (delete)
            $changeID = $folderChange['id'];
            $changeName = $folderChange["name"];
            $changePath = $folderChange["path"];
            $changeCategoryID = $folderChange["category_id"];
            $changeAction = $folderChange["action"];

            if($changeAction === "INSERT"){
                $dbOut .= "INSERT INTO [Folders] VALUES ({$changeID}, {$changeName}, {$changePath}, {$changeCategoryID} );\n";       // insert

                $transaction = $folderChange;
                unset($transaction["action"]);
                array_push($folderTransactions, $transaction);
            }
            else{
                $dbOut .= "DELETE FROM [Folders] WHERE [Folder].[ID] = {$changeID};\n";
                array_push($folderDeletions, $changeID);
            }
        }

        foreach ($videos as $videoChange){ // for each in stored, remove from new (delete)
            $changeID = $videoChange['id'];
            $changeName = $videoChange["name"];
            $changePath = $videoChange["path"];
            $changeFolderID = $videoChange["folder_id"];
            $changeDate = $videoChange["date"];
            $changeAction = $videoChange["action"];

            if($changeAction === "INSERT"){
                $dbOut .= "INSERT INTO [Videos] VALUES ({$changeID}, {$changeName}, {$changePath}, {$changeFolderID}, {$changeDate} );\n";       // insert

                $transaction = $videoChange;
                unset($transaction["action"]);
                array_push($videoTransactions, $transaction);
            }
            else{
                $dbOut .= "DELETE FROM [Videos] WHERE [Video].[ID] = {$changeID};\n";
                array_push($videoDeletions, $changeID);
            }
        }

        Video::destroy($videoDeletions);
        Folder::destroy($folderDeletions);
        Category::destroy($categoryDeletions);

        Category::insert($categoryTransactions);
        Folder::insert($folderTransactions);
        Video::insert($videoTransactions);

        Storage::disk('public')->put('categories.json', json_encode($directories["data"], JSON_UNESCAPED_SLASHES));
        Storage::disk('public')->put('folders.json', json_encode($subDirectories["data"], JSON_UNESCAPED_SLASHES));
        Storage::disk('public')->put('videos.json', json_encode($files["data"], JSON_UNESCAPED_SLASHES));

        $data = array("categories"=>$categories,"folders"=>$folders,"videos"=>$videos);

        $dataCache = Storage::json('public\dataCache.json') ?? array();
        $dataCache[date("Y-m-d-h:i:sa")] = array("job"=>"index", "data"=>$data);

        Storage::disk('public')->put('dataCache.json', json_encode($dataCache, JSON_UNESCAPED_SLASHES));
        dump('Categories | Folders | Videos | Data | SQL | DataCache', $directories, $subDirectories, $files, $data, $dbOut, $dataCache);
    }

    private function generateCategories($path){
        $data = Storage::json('public\categories.json') ?? array("next_ID"=>1, "categoryStructure" => array()); //array("anime"=>1,"tv"=>2,"yogscast"=>3); // read from json
        $scanned = array_map("htmlspecialchars", scandir($path));  // read folder structure

        $currentID = $data["next_ID"];
        $stored = $data["categoryStructure"];
        $changes = array(); // send to db
        $current = array(); // save into json

        // foreach ($stored as $savedID){ // O(n) where n = number of already known categories
        //     $currentID = max($currentID, $savedID + 1); // gets max currently used id
        // }

        /*
            If scanned is in stored, add to current, remove from stored
            If scanned not in stored, add to current, add to new (insert), id++

            after:

            for each in stored, remove from new (delete)

            send new to database

            save current to json
        */
        foreach ($scanned as $local){ // O(n) where n = number of already known categories
            if(is_dir($local)) continue; //? . and .. are dirs

            $name = basename($local);

            if(isset($stored[$name])){                                                          // If scanned is in stored, add to current, remove from stored
                $current[$name] = $stored[$name];                                                   // add to current
                unset($stored[$name]);                                                              // remove from stored
            }
            else{                                                                               // If scanned not in stored, add to current, add to new (insert), id++
                $generated = array("id"=>$currentID,"name"=>$name,"media_content"=>'False', "action"=>"INSERT");
                $current[$name] = $currentID;                                                       // add to current
                array_push($changes, $generated);                                                   // add to new (insert)
                $currentID++;
            }
        }

        foreach ($stored as $remainingID){
            $generated = array("id"=>$remainingID,"name"=>null,"media_content"=>null, "action"=>"DELETE"); // delete by id
            array_push($changes, $generated);                                                   // add to new (delete)
        }

        $data["next_ID"] = $currentID;
        $data["categoryStructure"] = $current;

        return array("categoryChanges"=> $changes, "data" => $data);
    }

    private function generateFolders($path, $categoryStructure){
        $data = Storage::json('public\folders.json') ?? array("next_ID"=>1,"folderStructure"=>array()); //array("anime/frieren"=>array("id"=>0,"name"=>"frieren"),"starwars/andor"=>array("id"=1,"name"="andor")); // read from json
        $scannedCategories = array_keys($categoryStructure);
        $cost = 0;
        
        $currentID = $data["next_ID"];
        $stored = $data["folderStructure"];
        $changes = array(); // send to db
        $current = array(); // save into json into json

        /* 
        foreach ($stored as $savedFolder){ // O(n) where n = number of already known categories
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
        }
        */

        foreach ($scannedCategories as $category) { // O(n) where n = number of folders * 2 (for scan)
            $cost++;
            
            $folders = Storage::directories("$path\\$category"); // Immediate folders (dont scan sub folders)

            foreach ($folders as $folder){
                $cost++;

                $name = basename($folder);
                $key = "$category\\$name";

                if($name === ".thumbs") continue;

                if(isset($stored[$key])){
                    $current[$key] = $stored[$key];                                                     // add to current
                    unset($stored[$key]);                                                               // remove from stored
                }
                else{
                    $generated = array("id"=>$currentID,"name"=>$name,"path"=>$key, "category_id"=>$categoryStructure[$category], "action"=>"INSERT");
                    $current[$key] = array("id"=>$currentID, "last_scan"=>-1);                                    // add to current
                    array_push($changes, $generated);                                                   // add to new (insert)
                    $currentID++;
                }
            }
        }
        foreach ($stored as $remainingFolder => $id){
            $generated = array("id"=>$id,"name"=>null,"path"=>null, "category_id"=>null, "action"=>"DELETE");  // delete by id -> Used to store just ID -> Now store id and last_scan
            array_push($changes, $generated);                                                               // add to new (delete)
            $cost++;
        }

        $data["next_ID"] = $currentID;
        $data["folderStructure"] = $current;
        return array("folderChanges"=> $changes, "data" => $data, "cost"=>$cost);
    }

    private function generateVideos($path, $folderStructure){
        $data = Storage::json('public\videos.json') ?? array("next_ID"=>1,"videoStructure"=>array()); //array("anime/frieren/S1E01.mp4"=>array("id"=>0,"name"=>"S1E01"),"starwars/andor/S1E01.mkv"=>array("id"=1,"name"="S1E01.mkv")); // read from json
        $scannedFolders = array_keys($folderStructure);
        $cost = 0;

        $currentID = $data["next_ID"];
        $stored = $data["videoStructure"];
        $changes = array(); // send to db
        $current = array(); // save into json into json

        $foldersCopy = $folderStructure;
        $unModefiedFolders = array();
        $rawPath = Storage::path('');
        
        // foreach ($stored as $savedVideo){ // O(n) where n = number of already known categories
        //     $currentID = max($currentID, $savedVideo + 1); // gets max currently used id
        //     $cost ++;
        // }

        /*
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

                
        */

        foreach ($scannedFolders as $folder) { // O(n) where n = number of folders * 2 (for scan)
            $cost++;
            $folderAccessTime = filemtime("{$rawPath}public\media\\$folder\\");

            if($folderAccessTime <= $folderStructure[$folder]["last_scan"]){
                $unModefiedFolders["storage\\". basename($path) . "\\$folder"] = 1;
                continue;
            }

            $files = Storage::files("$path$folder"); // Immediate folders (dont scan sub folders)
            $foldersCopy[$folder]['last_scan'] = $folderAccessTime;
            foreach ($files as $file){
                $cost++;
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                if (strtolower($ext) !== 'mp4' && strtolower($ext) !== 'mkv' ) continue;

                $name = basename($file);
                $cleanName = basename($file,".$ext");
                $key = "storage\\". basename($path) . "\\$folder\\$name";
                $rawFile = "$rawPath$file";
                if(isset($stored[$key])){
                    $current[$key] = $stored[$key];                                                     // add to current
                    unset($stored[$key]);                                                               // remove from stored
                }
                else{
                    $generated = array("id"=>$currentID,"name"=>$cleanName,"path"=>$key, "folder_id"=>$folderStructure[$folder]["id"], "date" => date("Y-m-d g:i A", filemtime($rawFile)), "action"=>"INSERT");
                    $current[$key] = $currentID;                                                        // add to current
                    array_push($changes, $generated);                                                   // add to new (insert)
                    $currentID++;
                }
            }
        }

        foreach ($stored as $video => $remainingID){ // unseen videos
            if(isset($unModefiedFolders[dirname($video)])){ // if folder was not modefied
                $current[$video] = $stored[$video];      // see video
                continue;
            } 
            $generated = array("id"=>$remainingID,"name"=>null,"path"=>null, "folder_id"=>null, "date" => null, "action"=>"DELETE");  // delete by id
            array_push($changes, $generated);                                                               // add to new (delete)
            $cost++;
        }

        if($foldersCopy === $folderStructure) $foldersCopy = null;

        $data["next_ID"] = $currentID;
        $data["videoStructure"] = $current;
        return array("videoChanges"=> $changes, "data" => $data, "cost"=>$cost, "updatedFolderStructure"=>$foldersCopy);
    }
}
