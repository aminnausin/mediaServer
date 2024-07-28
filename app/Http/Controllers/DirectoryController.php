<?php

namespace App\Http\Controllers;

use App\Http\Requests\VideoCollectionRequest;
use App\Http\Resources\FolderResource;
use App\Http\Resources\VideoResource;
use App\Jobs\IndexFiles;
use App\Jobs\SyncFiles;
use App\Models\Category;
use App\Models\Folder;
use App\Models\Video;
use ErrorException;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;

class DirectoryController extends Controller
{
    use HttpResponses;

    public function showDirectory(Request $request) {
        // aim -> return directory and folder id where exists so folder controller is usable
        $privateCategories = array("legacy"=>1);

        if(isset($privateCategories[strtolower($request->dir)]) && !$request->user('sanctum')){
            $data['message'] = 'Unauthorized';
            return view('error', $data);
        }
        $dirRaw = Category::select('id')->firstWhere('name', 'ilike', '%' . $request->dir . '%'); 

        if(isset($dirRaw->id)){
            $data['dir'] = array('id'=>$dirRaw->id,'name'=>$request->dir);
            if(isset($request->folder_name)){
                $data['folder'] = array('id'=>null, 'name'=>$request->folder_name);
            }
            else{
                $folderRaw = Folder::select('id','name')->firstWhere('category_id', $data['dir']['id']);
                $data['folder'] = array('id'=>$folderRaw->id, 'name'=>$folderRaw->name);
            }
        }
        else{
            $data['dir'] = array('id'=>null,'name'=>$request->dir);
            $data['folder'] = array('id'=>null, 'name'=>null);
        }

        // dump($data);
        return view('home', $data);
    }

    public function showDirectoryAPI(Request $request) {
        // All this does is convert url to ids and names
        // IDEALLY it should also load data to prevent requiring more api requests
        // It does exactly that now it feels fast
        try {
            $privateCategories = array("legacy"=>1);
            $dir = trim(strtolower($request->dir));
            $folderName = trim(strtolower($request->folderName));

            if(isset($privateCategories[$dir]) && !$request->user('sanctum')){
                $data['message'] = 'Unauthorized';
                return $this->error(null, 'Access to this folder is forbidden', 403);
            }

            $dirRaw = Category::select('id')->firstWhere('name', 'ilike', '%' . $dir . '%'); 
            $data = array('dir'=>array('id'=>null,'name'=>$dir,'folders'=>null),'folder'=>array('id'=>null, 'name'=>$folderName ?? null, 'videos'=>null)); // Default null values

            if(!isset($dirRaw->id)){ // Cannot find category so return default nulls
                return $this->error(array('categoryName'=>$dir), 'Cannot find specified category', 200);
            }

            $folderList = Folder::where('category_id', $dirRaw->id)->withCount(['videos']); // Folders in category
            $data['dir'] = array('id'=>$dirRaw->id,'name'=>$dir,'folders'=>FolderResource::collection($folderList->get())); // Full category data
            $folderRaw = isset($request->folderName) ? $folderList->firstWhere('name', 'ilike', '%' . $folderName . '%') : $folderList->first(); // Folder in request ? search by name else select first in category

            if(!isset($folderRaw->id)){ // no folder found
                return $this->error(array('categoryName'=>$dir,'folderName'=>$folderName), 'Cannot find folder in specified category', 200);
            }

            $videoList = VideoResource::collection( Video::where('folder_id', $folderRaw->id)->get());
            $data['folder'] = array('id'=>$folderRaw->id, 'name'=>$folderRaw->name, 'videos'=>$videoList);

            return $this->success($data, '', 200);

        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to parse URL ' . $th->getMessage(), 500);
        }
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

    public function getFolderContents(VideoCollectionRequest $request){
        try {
            $folder_id = isset($request->folder_id) ? $request->folder_id : throw new ErrorException("No folder id or invalid folder name provided. Cannot generate videos.");

            $videos = Video::select('id','name','path','date')->where('folder_id', $folder_id)->get();

            return(json_encode(array("success"=>true, "result"=>$videos->toArray(), "error"=>""), JSON_UNESCAPED_SLASHES));
        } catch (\Throwable $th) {
            return(json_encode(array("success"=>false, "result"=>array(), "error"=>$th->getMessage()), JSON_UNESCAPED_SLASHES));
        }
    }


    public function indexFiles(Request $request){
        try {
            IndexFiles::dispatch();
            dump('success');
        } catch (\Throwable $th) {
            dump('Error cannot index files');
            dump($th);
        }
    }

    public function syncFiles(Request $request){
        try {
            SyncFiles::dispatch();
            dump('success');
        } catch (\Throwable $th) {
            dump('Error cannot index files');
            dump($th);
        }
    }
    // public function generateDirectory(Request $request) {
    //     $dir = $request->dir;
    //     $mediaRoot = '/media/';
    //     $publicRoot = '/storage';
    //     $default = 'tv';
    //     $path = isset($dir) ? $mediaRoot . $dir : $mediaRoot . $default ;

    //     if(!Storage::exists('public/' . $path)){
    //         $error = 'Invalid Directory: "' . $path . '"';
        
    //         return json_encode(array("success"=>false, "result"=>"", "error"=>$error), JSON_UNESCAPED_SLASHES);
    //     }


    //     $allDirectories = Storage::directories('public' . $path);

    //     $folders = array();
    //     $currentFolder = array("name"=>"", "files"=>array());


    //     foreach ($allDirectories as $directory) {
    //         if (basename($directory) != '.thumbs') {
    //             $currentFolder['name'] = basename($directory);
    //             $files = File::allFiles(storage_path('app') . '/' . $directory);
                
    //             foreach($files as $file){
    //                 if ($file->getExtension() == 'mp4' || $file->getExtension() == 'mkv' ) {
    //                     $fileData = array('name'=> $publicRoot . $path . '/' . basename($directory) . '/' . basename($file), 'title'=>basename($file), 'date' => filectime($file), 'formattedDate' => date("Y-m-d g:i A", filectime($file)));
    //                     $rawFile = array('name'=> $publicRoot . $path . '/' . basename($directory) . '/' . basename($file), 'title'=>basename($file), 'date' => filectime($file), 'formattedDate' => date("Y-m-d g:i A", filectime($file)));
    //                     array_push($currentFolder['files'], $fileData);
    //                 }
    //             }
    //         }
    //         array_push($folders, $currentFolder);
    //         $currentFolder['files'] = array();
    //     }

    //     return json_encode(array("success"=>true, "result"=>$folders, "error"=>""), JSON_UNESCAPED_SLASHES);
    // }
}