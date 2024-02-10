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
        if(!Storage::exists('public\media\\')){
            $error = 'Invalid Directory: "media"';
        
            dd(json_encode(array("success"=>false, "result"=>"", "error"=>$error), JSON_UNESCAPED_SLASHES));
        }

        $path = Storage::path('public\media\\');

        $categories = $this->generateCategories($path);

        //Database models
        
        $shows = array();
        $show = array("id"=>null,"name"=>"", "path"=>"", "category_id"=>"");

        $videos = array();
        $video = array("id"=>null,"name"=>"","path"=>"","category_id"=>"","show_id"=>null,"file_id"=>null,"episode_id"=>null);

        //Raw file data connected to video (when folder is not a show)
        $rawFiles = array();
        $rawFile = array("name"=> "", "path"=>"", "date" => "", "formattedDate" => null);

        //Episodic data connected to video (when folder is a show)
        $episodes = array();
        $episode = array("id"=>null, "episode_no"=>null, "season_no"=>null, "episode_synopsis"=>"lorem ipsum");
        
        $dbOut = "";

        foreach ($categories as $categoryChange){ // for each in stored, remove from new (delete)
            $changeID = $categoryChange['id'];
            $changeName = $categoryChange["name"];
            $changeMediaContent = $categoryChange["media_content"];
            $changeAction = $categoryChange["action"];
            if($changeAction == "INSERT"){
                $dbOut .= "INSERT INTO [Categories] VALUES ({$changeID}, {$changeName}, {$changeMediaContent} );";       // insert
            }
            else{
                $dbOut .= "DELETE FROM [Categories] WHERE [Categories].[ID] = {$changeID};";
            }
        }


        $data = array("categories"=>$categories,"folders"=>$shows,"videos"=>$videos);

        Storage::disk('public')->put('testCache.json', json_encode($data, JSON_UNESCAPED_SLASHES));
        dd($data, $dbOut);
    }

    private function generateCategories($path){
        $currentID = 0;

        $scanned = array_map("htmlspecialchars", scandir($path));  // read folder structure
        $stored = File::json(Storage::path('public\categories.json')); //array("anime"=>1,"tv"=>2,"yogscast"=>3); // read from json
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

        Storage::disk('public')->put('categories.json', json_encode($current, JSON_UNESCAPED_SLASHES));

        return $changes;
    }

    private function generateShows($path){
        $currentID = 0;

        $scanned = array_map("htmlspecialchars", scandir($path));  // read folder structure
        $stored = File::json(Storage::path('public\categories.json')); //array("anime"=>1,"tv"=>2,"yogscast"=>3); // read from json
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

        Storage::disk('public')->put('categories.json', json_encode($current, JSON_UNESCAPED_SLASHES));

        return $changes;
    }

    private function generateEpisodes($path){
        $currentID = 0;

        $scanned = array_map("htmlspecialchars", scandir($path));  // read folder structure
        $stored = File::json(Storage::path('public\categories.json')); //array("anime"=>1,"tv"=>2,"yogscast"=>3); // read from json
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

        Storage::disk('public')->put('categories.json', json_encode($current, JSON_UNESCAPED_SLASHES));

        return $changes;
    }
}