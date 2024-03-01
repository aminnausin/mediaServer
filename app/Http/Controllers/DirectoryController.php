<?php

namespace App\Http\Controllers;

use App\Http\Requests\VideoCollectionRequest;
use App\Jobs\IndexFiles;
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