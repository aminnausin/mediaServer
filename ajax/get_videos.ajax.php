<?php
require_once "..\\config.php";
$postRaw = file_get_contents('php://input');
$postData = json_decode($postRaw);

$path = isset($postData->dir) ? '.' . RESOURCE_DIR . $postData->dir . '\\' : '.' . RESOURCE_DIR . RESOURCE_DEFAULT;
$folderCount = 0;

if(! is_dir($path)){
    $error = 'Invalid Directory: "' . $path . '"';

    echo json_encode(array("success"=>false, "result"=>"", "error"=>$error), JSON_UNESCAPED_SLASHES);
    return;
}

// $out = '';

$folders = array();
$currentFolder = array("name"=>"", "files"=>array());

foreach ($iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST) as $file) {
    /** @var SplFileInfo $file */

    if ($file->isDir() && basename($file) != '.thumbs') {

        if($folderCount != 0){
            array_push($folders, $currentFolder);
            $currentFolder["files"] = array();
        }
        
        $currentFolder["name"] = basename($file);
        
        #region Old HTML Output
        // $out .= '
        // <div class="col-sm-12" style="padding: 2%;">
        //     <div class="row mb-4">
        //         <h4 class="col-sm-10">' . basename($file) . '</h4> 
        //         <button class="col-sm-2 btn btn-light folder-toggle" id="dataTable-' . $folderCount . '-collapse-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#dataTable-' . $folderCount . '-collapse">
        //             <i class="bi bi-list"></i>
        //             Show Folder
        //         </button>
        //     </div>
        //     <div class="collapse show" id="dataTable-' . $folderCount . '-collapse" data-state="0">
        //         <table class="vid-table hover stripe" id="dataTable-' . $folderCount . '" data-folder="' . basename($file) . '">
        //             <thead>
        //                 <tr>
        //                     <th>Title</th>
        //                     <!-- <th>Length</th> -->
        //                     <th>Date</th>
        //                 </tr>
        //             </thead>
        //             <tbody>
        //     ';

        //     if($folderCount != 0) $out = '
        //             </tbody>
        //         </table>
        //     </div>    
        // </div>
        // <hr>
        // ' . $out; // if not first folder, then close the last folder
        #endregion
        
        $folderCount += 1;
    } else {
        if (in_array($file->getExtension(), FILE_TYPES)) {
            $fileData = array('name'=> '.' . (string) $file, 'title'=>basename($file), 'date' => filectime($file), 'formattedDate' => date("Y-m-d g:i A", filectime($file)));
            array_push($currentFolder['files'], $fileData);
            #region Old HTML Output
            // $out .= '
            //     <tr>
            //         <td class="vid-row" data-col="col-title" value="' . '.' . $file . '">' . basename($file) . '</td>
            //         <!-- <td class="vid-row" data-col="col-length" value="0">Unimplemented</td> -->
            //         <td class="vid-row" data-col="col-date" value="' . filectime($file) . '">' . date("Y-m-d g:i A", filectime($file)) . '</td>
            //     </tr>
                    
            // ';
            #endregion
        }
    }
}
echo json_encode(array("success"=>true, "result"=>$folders, "error"=>""), JSON_UNESCAPED_SLASHES);
?>