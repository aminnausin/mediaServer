<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Folder;
use App\Models\Video;
use ErrorException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class DirectoryController extends Controller
{
    public function showDirectory($dir,$folder_name = null) {
        $data['dir'] = $dir;
        $data['folder_name'] = $folder_name;
        return view('home', $data);
    }

    public function showDirectoryTest($dir,$folder_name = null) {
        $data['dir'] = $dir;
        $data['folder_name'] = $folder_name;
        return view('testing', $data);
    }

    public function getDirectory(Request $request){
        try {
            $default = 'tv';
            $dir = isset($request->dir) ? $request->dir : $default;
    
            $category_id = Category::select('id')->firstWhere('name', $dir)->id;
            $folders = Folder::select('id','name')->where('category_id', $category_id)->get();
            $firstFolder_id = $folders->first()->id;
    
            $videos = Video::select('name','path','date')->where('folder_id', $firstFolder_id)->get();
            dump(json_encode(array("success"=>true, "result"=>array("folders"=>$folders->toArray(),"videos"=>$videos->toArray()), "error"=>""), JSON_UNESCAPED_SLASHES));
            //return json_encode(array("success"=>true, "result"=>array("folders"=>$folders->toArray(),"videos"=>$videos->toArray()), "error"=>""), JSON_UNESCAPED_SLASHES);
        } catch (\Throwable $th) {
            //throw $th;
            return json_encode(array("success"=>false, "result"=>array("folders"=>array(), "videos"=>array()), "error"=>$th->getMessage()), JSON_UNESCAPED_SLASHES);
        }
    }

    public function getDirectoryContents(Request $request){
        try {
            $default = 'tv';
            $dir = isset($request->dir) ? $request->dir : $default;
    
            $category_id = Category::select('id')->firstWhere('name', $dir)->id;
            $folders = Folder::select('id','name')->where('category_id', $category_id)->get();
    
            return(json_encode(array("success"=>true, "result"=>$folders->toArray(), "error"=>""), JSON_UNESCAPED_SLASHES));
        } catch (\Throwable $th) {
            return(json_encode(array("success"=>false, "result"=>array(), "error"=>$th->getMessage()), JSON_UNESCAPED_SLASHES));
        }
    }

    public function getFolderContents(Request $request){
        try {
            $folder_id = isset($request->folder_id) ? $request->folder_id : throw new ErrorException("No folder id or invalid folder name provided. Cannot generate videos.");

            $videos = Video::select('id','name','path','date')->where('folder_id', $folder_id)->get();

            return(json_encode(array("success"=>true, "result"=>$videos->toArray(), "error"=>""), JSON_UNESCAPED_SLASHES));
        } catch (\Throwable $th) {
            return(json_encode(array("success"=>false, "result"=>array(), "error"=>$th->getMessage()), JSON_UNESCAPED_SLASHES));
        }
    }

    public function generateDirectory(Request $request) {
        $dir = $request->dir;
        $mediaRoot = '/media/';
        $publicRoot = '/storage';
        $default = 'tv';
        $path = isset($dir) ? $mediaRoot . $dir : $mediaRoot . $default ;

        if(!Storage::exists('public/' . $path)){
            $error = 'Invalid Directory: "' . $path . '"';
        
            return json_encode(array("success"=>false, "result"=>"", "error"=>$error), JSON_UNESCAPED_SLASHES);
        }


        $allDirectories = Storage::directories('public' . $path);

        $folders = array();
        $currentFolder = array("name"=>"", "files"=>array());


        foreach ($allDirectories as $directory) {
            if (basename($directory) != '.thumbs') {
                $currentFolder['name'] = basename($directory);
                $files = File::allFiles(storage_path('app') . '/' . $directory);
                
                foreach($files as $file){
                    if ($file->getExtension() == 'mp4' || $file->getExtension() == 'mkv' ) {
                        $fileData = array('name'=> $publicRoot . $path . '/' . basename($directory) . '/' . basename($file), 'title'=>basename($file), 'date' => filectime($file), 'formattedDate' => date("Y-m-d g:i A", filectime($file)));
                        $rawFile = array('name'=> $publicRoot . $path . '/' . basename($directory) . '/' . basename($file), 'title'=>basename($file), 'date' => filectime($file), 'formattedDate' => date("Y-m-d g:i A", filectime($file)));
                        array_push($currentFolder['files'], $fileData);
                    }
                }
            }
            array_push($folders, $currentFolder);
            $currentFolder['files'] = array();
        }

        return json_encode(array("success"=>true, "result"=>$folders, "error"=>""), JSON_UNESCAPED_SLASHES);
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
                // Category::create(
                //     ["name"=>$changeName, "media_content" => $changeMediaContent]
                // );
            }
            else{
                $dbOut .= "DELETE FROM [Categories] WHERE [Categories].[ID] = $changeID;\n";
                Category::where('id', $changeID)->delete();
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

                //Folder::create(["name"=>$changeName, "path"=>$changePath,"category_id"=>$changeCategoryID]);
            }
            else{
                $dbOut .= "DELETE FROM [Folders] WHERE [Folder].[ID] = {$changeID};";
                Folder::where('id', $changeID)->delete();
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

                // $video = new Video();
                // $video->name = "anime";
                // $video->path = $changePath;
                // $video->folder_id = $changeCategoryID;
                // $video->date = $changeDate;

                // $video->save();

                $transaction = $videoChange;
                unset($transaction["action"]);
                array_push($videoTransactions, $transaction);

                //Video::create( ["name"=>$changeName, "path"=>$changePath,"folder_id"=>$changeCategoryID, "date"=>$changeDate] );
            }
            else{
                $dbOut .= "DELETE FROM [Videos] WHERE [Categories].[ID] = {$changeID};\n";
                Video::where('id', $changeID)->delete();
            }
        }

        Storage::disk('public')->put('categories.json', json_encode($directories["data"], JSON_UNESCAPED_SLASHES));
        Storage::disk('public')->put('folders.json', json_encode($subDirectories["data"], JSON_UNESCAPED_SLASHES));
        Storage::disk('public')->put('videos.json', json_encode($files["data"], JSON_UNESCAPED_SLASHES));

        Category::insert($categoryTransactions);
        Folder::insert($folderTransactions);
        Video::insert($videoTransactions);

        $data = array("categories"=>$categories,"folders"=>$folders,"videos"=>$videos);

        Storage::disk('public')->put('dataCache.json', json_encode($data, JSON_UNESCAPED_SLASHES));
        dump($directories, $subDirectories, $files, $data, $dbOut);
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

        // foreach ($stored as $savedFolder){ // O(n) where n = number of already known categories
        //     $currentID = max($currentID, $savedFolder["id"] + 1); // gets max currently used id
        //     $cost ++;
        // }

        /*
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


        foreach ($stored as $remainingID){
            $generated = array("id"=>$remainingID,"name"=>null,"path"=>null, "category_id"=>null, "action"=>"DELETE");  // delete by id
            array_push($changes, $generated);                                                               // add to new (delete)
            $cost++;
        }


        // if($current !== $storedCopy) {
        //     //Storage::disk('public')->put('folders.json', json_encode($current, JSON_UNESCAPED_SLASHES));
        // }

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
                $unModefiedFolders[$folder] = 1;
                continue;
            }

            $files = Storage::files("$path$folder"); // Immediate folders (dont scan sub folders)
            $foldersCopy[$folder]['last_scan'] = $folderAccessTime;
            
            foreach ($files as $file){
                $cost++;
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                if ($ext !== 'mp4' && $ext !== 'mkv' ) continue;

                $name = basename($file);
                $cleanName = basename($file,".$ext");
                $key = "$folder\\$name";
                $rawFile = "$rawPath$file";

                if(isset($stored[$key])){
                    $current[$key] = $stored[$key];                                                     // add to current
                    unset($stored[$key]);                                                               // remove from stored
                }
                else{
                    $generated = array("id"=>$currentID,"name"=>$cleanName,"path"=>"storage\\". basename($path) . "\\$key", "folder_id"=>$folderStructure[$folder]["id"], "date" => date("Y-m-d g:i A", filemtime($rawFile)), "action"=>"INSERT");
                    $current[$key] = $currentID;                                                        // add to current
                    array_push($changes, $generated);                                                   // add to new (insert)
                    $currentID++;
                }
            }
        }

        foreach ($stored as $video => $remainingID){
            if(isset($unModefiedFolders[dirname($video)])){
                $current[$video] = $stored[$video];     
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