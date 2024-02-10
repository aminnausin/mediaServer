<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class DirectoryController extends Controller
{
    public function showDirectory($dir) {
        $data['dir'] = $dir;
        return view('home', $data);
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

    public function testStorage() {
        $dir = 'tv';
        $mediaRoot = '/storage/media/';
        $default = 'tv';
        $path = isset($dir) ? $mediaRoot . $dir : $mediaRoot . $default ;

        $allDirectories = Storage::directories('public' . $path);

        dd($allDirectories);
        $folders = array();
        $currentFolder = array("name"=>"", "files"=>array());

        foreach ($allDirectories as $directory) {
            if (basename($directory) != '.thumbs') {
                $currentFolder['name'] = basename($directory);

                //$files = File::allFiles(public_path('resources\\' . $dir) . '\\' . basename($directory));
                $files = File::allFiles(storage_path('app') . '/' . $directory);
                
                foreach($files as $file){
                    if ($file->getExtension() == 'mp4' || $file->getExtension() == 'mkv' ) {
                        $fileData = array('name'=> $path . '/' . basename($directory) . '/' . basename($file), 'title'=>basename($file), 'date' => filectime($file), 'formattedDate' => date("Y-m-d g:i A", filectime($file)));
                        array_push($currentFolder['files'], $fileData);
                    }
                }
            }
            array_push($folders, $currentFolder);
            $currentFolder['files'] = array();
        }

        dd(json_encode(array("success"=>true, "result"=>$folders, "error"=>""), JSON_UNESCAPED_SLASHES));
    }

    public function generateData() {
        /*

        category = array("id"=>$currentID,"name"=>$name,"media_content"=>"false", "action"=>"INSERT");

        folder = array("id"=>$currentID,"name"=>$name,"path"=>category/$name, "category_id"=>$categoryStructure[$category], "action"=>"INSERT");

        video = array("id"=>$currentID,"name"=>$name,"path"=>category/folder/name, "folder_id"=>$folderStructure[$folder], "date" => date("Y-m-d g:i A", filectime($file))", action"=>"INSERT");

        */

        $path = "public\media\\";

        if(!Storage::exists($path)){
            $error = 'Invalid Directory: "media"';
        
            dd(json_encode(array("success"=>false, "result"=>"", "error"=>$error), JSON_UNESCAPED_SLASHES));
        }

        $realPath = Storage::path($path);

        $directories = $this->generateCategories($realPath);   
        $subDirectories = $this->generateFolders($path, $directories["categoryStructure"]);
        $files = $this->generateVideos($path, $subDirectories["folderStructure"], $directories["categoryStructure"]);

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

        foreach ($categories as $categoryChange){ // for each in stored, remove from new (delete)
            $changeID = $categoryChange['id'];
            $changeName = $categoryChange["name"];
            $changeMediaContent = $categoryChange["media_content"];
            $changeAction = $categoryChange["action"];

            if($changeAction == "INSERT"){
                $dbOut .= "INSERT INTO [Categories] VALUES ({$changeID}, {$changeName}, {$changeMediaContent} );\n";       // insert
            }
            else{
                $dbOut .= "DELETE FROM [Categories] WHERE [Categories].[ID] = {$changeID};\n";
            }
        }

        foreach ($folders as $folderChange){ // for each in stored, remove from new (delete)
            $changeID = $folderChange['id'];
            $changeName = $folderChange["name"];
            $changePath = $folderChange["path"];
            $changeCategoryID = $folderChange["category_id"];
            $changeAction = $folderChange["action"];

            if($changeAction == "INSERT"){
                $dbOut .= "INSERT INTO [Folders] VALUES ({$changeID}, {$changeName}, {$changePath}, {$changeCategoryID} );\n";       // insert
            }
            else{
                $dbOut .= "DELETE FROM [Folders] WHERE [Folder].[ID] = {$changeID};";
            }
        }

        foreach ($videos as $videoChange){ // for each in stored, remove from new (delete)
            $changeID = $videoChange['id'];
            $changeName = $videoChange["name"];
            $changePath = $videoChange["path"];
            $changeFolderID = $videoChange["folder_id"];
            $changeDate = $videoChange["date"];
            $changeAction = $videoChange["action"];

            if($changeAction == "INSERT"){
                $dbOut .= "INSERT INTO [Videos] VALUES ({$changeID}, {$changeName}, {$changePath}, {$changeFolderID}, {$changeDate} );\n";       // insert
            }
            else{
                $dbOut .= "DELETE FROM [Videos] WHERE [Categories].[ID] = {$changeID};\n";
            }
        }

        $data = array("categories"=>$categories,"folders"=>$folders,"videos"=>$videos);

        Storage::disk('public')->put('dataCache.json', json_encode($data, JSON_UNESCAPED_SLASHES));
        dump($directories, $subDirectories, $files, $data, $dbOut);
    }

    private function generateCategories($path){
        $currentID = 0;

        $scanned = array_map("htmlspecialchars", scandir($path));  // read folder structure
        $stored = Storage::json('public\categories.json') ?? array(); //array("anime"=>1,"tv"=>2,"yogscast"=>3); // read from json
        $storedCopy = $stored;
        $changes = array(); // send to db
        $current = array(); // save into json

        foreach ($stored as $savedID){ // O(n) where n = number of already known categories
            $currentID = max($currentID, $savedID) + 1; // gets max currently used id
        }

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
                $generated = array("id"=>$currentID,"name"=>$name,"media_content"=>"false", "action"=>"INSERT");
                $current[$name] = $currentID;                                                       // add to current
                array_push($changes, $generated);                                                   // add to new (insert)
                $currentID++;
            }
        }

        foreach ($stored as $remainingID){
            $generated = array("id"=>$remainingID,"name"=>null,"media_content"=>null, "action"=>"DELETE"); // delete by id
            array_push($changes, $generated);                                                   // add to new (delete)
        }

        
        if($current !== $storedCopy) {
            Storage::disk('public')->put('categories.json', json_encode($current, JSON_UNESCAPED_SLASHES));
        }
        return array("categoryChanges"=> $changes, "categoryStructure" => $current);
    }

    private function generateFolders($path, $categoryStructure){
        $cost = 0;
        $currentID = 0;
        $scannedCategories = array_keys($categoryStructure);
        $rawPath = Storage::path('');

        $stored = Storage::json('public\folders.json') ?? array(); //array("anime/frieren"=>array("id"=>0,"name"=>"frieren"),"starwars/andor"=>array("id"=1,"name"="andor")); // read from json
        $storedCopy = $stored;
        $changes = array(); // send to db
        $current = array(); // save into json into json

        foreach ($stored as $savedFolder){ // O(n) where n = number of already known categories
            $currentID = max($currentID, $savedFolder["id"]) + 1; // gets max currently used id
            $cost ++;
        }

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


        if($current !== $storedCopy) {
            Storage::disk('public')->put('folders.json', json_encode($current, JSON_UNESCAPED_SLASHES));
        }

        return array("folderChanges"=> $changes, "folderStructure" => $current, "cost"=>$cost);
    }

    private function generateVideos($path, $folderStructure){
        $cost = 0;
        $currentID = 0;
        $scannedFolders = array_keys($folderStructure);
        $foldersCopy = $folderStructure;
        $unModefiedFolders = array();
        $rawPath = Storage::path('');

        $stored = Storage::json('public\videos.json') ?? array(); //array("anime/frieren/S1E01.mp4"=>array("id"=>0,"name"=>"S1E01"),"starwars/andor/S1E01.mkv"=>array("id"=1,"name"="S1E01.mkv")); // read from json
        $storedCopy = $stored;
        $changes = array(); // send to db
        $current = array(); // save into json into json
        
        foreach ($stored as $savedVideo){ // O(n) where n = number of already known categories
            $currentID = max($currentID, $savedVideo) + 1; // gets max currently used id
            $cost ++;
        }

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

                $name = basename($file, $ext);
                $key = "$folder\\$name";
                $rawFile = "$rawPath$file";

                if(isset($stored[$key])){
                    $current[$key] = $stored[$key];                                                     // add to current
                    unset($stored[$key]);                                                               // remove from stored
                }
                else{
                    $generated = array("id"=>$currentID,"name"=>$name,"path"=>$key, "folder_id"=>$folderStructure[$folder]["id"], "date" => date("Y-m-d g:i A", filemtime($rawFile)), "action"=>"INSERT");

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

        
        if($foldersCopy !== $folderStructure) {
            Storage::disk('public')->put('folders.json', json_encode($foldersCopy, JSON_UNESCAPED_SLASHES));
        }

        if($current !== $storedCopy) {
            Storage::disk('public')->put('videos.json', json_encode($current, JSON_UNESCAPED_SLASHES));
        }
        return array("videoChanges"=> $changes, "videoStructure" => $current, "cost"=>$cost);
    }
}