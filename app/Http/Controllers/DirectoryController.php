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
}