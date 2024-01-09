<?php
$path = $_POST["directory"];
$folderCount = 0;

foreach ($iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST) as $file) {
    /** @var SplFileInfo $file */

    if ($file->isDir() && basename($file) != '.thumbs') {
        
        $out = '
        <div class="col-sm-12" style="padding: 2%;">
            <div class="row mb-4">
                <h4 class="col-sm-10">' . basename($file) . '</h4> 
                <button class="col-sm-2 btn btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#dataTable-' . $folderCount . '-collapse">
                    <i class="bi bi-list"></i>
                    Hide Folder
                </button>
            </div>
            <div class="collapse" id="dataTable-' . $folderCount . '-collapse">
                <table class="vid-table table table-hover table-striped" id="dataTable-' . $folderCount . '">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Who knows?</th>
                        </tr>
                    </thead>
                    <tbody>
            ';

            if($folderCount != 0) $out = '
                    </tbody>
                </table>
            </div>    
        </div>
        <hr>
        ' . $out;

        echo $out;

        $folderCount += 1;
    } else {
        if ($file->getExtension() == 'mp4') {
            echo('
                <tr>
                    <td class="vid-row" data-col="col-title" value="' . $file . '">' . basename($file) . '</td>
                    <td></td>
                </tr>
            ');
        }
    }
}


echo $result;
?>