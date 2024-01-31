<?php
require_once "..\\config.php";
$postRaw = file_get_contents('php://input');
$postData = json_decode($postRaw);

$path = isset($postData->dir) ? '.' . RESOURCE_DIR . $postData->dir . '\\' : '.' . RESOURCE_DIR . RESOURCE_DEFAULT;
$folderCount = 0;
$output = array();

if(! is_dir($path)){
    $error = 'Invalid Directory: "' . $path . '"';

    echo json_encode(array("success"=>false, "result"=>"", "error"=>$error), JSON_UNESCAPED_SLASHES);
    return;
}

$folders = array();
$currentFolder = array("name"=>"", "files"=>array());

/**
 * Iterate over every file in the path and either treat them as $currentFolder if its a subdirectory or a file if its a video.
 * Upon discovery of a new subdirectory, assume previous directory is complete and push it to output. Then start a new subdirectory.
 */
foreach ($iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST) as $file) {
    /** @var SplFileInfo $file */

    if ($file->isDir() && basename($file) != '.thumbs') {
        if($folderCount != 0){
            array_push($output, 'pushing previous folder: ' . $currentFolder["name"]);
            array_push($folders, $currentFolder);
            $currentFolder["files"] = array();
            $currentFolder["name"] = "";
        }
        
        $currentFolder["name"] = basename($file);
        array_push($output, 'found another folder: ' . basename(($file)));
        
        $folderCount += 1;
    } else {
        if (in_array($file->getExtension(), FILE_TYPES)) {
            $fileData = array('name'=> '.' . (string) $file, 'title'=>basename($file), 'date' => filectime($file), 'formattedDate' => date("Y-m-d g:i A", filectime($file)));
            array_push($currentFolder['files'], $fileData);
            array_push($output, 'found file: ' . $fileData['title'] . ' at ' . $fileData['name']);
        }
    }
}

/**
 * Push last folder if it has content.
 */
if(count($currentFolder["files"]) != 0){
    array_push($output, 'pushing last folder: ' . $currentFolder["name"]);
    array_push($folders, $currentFolder);
    $currentFolder["files"] = array();
    $currentFolder["name"] = "";
}

echo json_encode(array("success"=>true, "result"=>$folders, "error"=>""), JSON_UNESCAPED_SLASHES);
?>