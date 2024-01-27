<?php
require_once "./config.php";
?>
<!doctype html>
<html lang="en" >
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Anime?</title>

        <!-- Bootstrap -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet" >
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
        
        <!-- Jquery -->
        <script src="https://code.jquery.com/jquery-3.7.1.js"></script>

        <!-- DataTables -->
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
        <!-- <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script> -->  
        
        <!-- <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet" crossorigin="anonymous"> -->
        <link href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css" rel="stylesheet">


        <!-- Local -->
        <link href="./css/main.css" rel="stylesheet" >
        <script src="./js/main.js"></script>
    </head>
    <body class="light-mode">
        <main class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="neu-card light-mode">
                        <div class="col-sm-12">
                            <!-- <h3 id="mp4-title" class="col-sm-11"></h3>
                            <hr> -->
                            <div style="display: flex; flex-direction: row-reverse;">
                                <div style="width:auto; display: flex; align-items: center;" class="ms-3">
                                    <div class="toggle-switch">
                                        <label class="switch-label">
                                            <input type="checkbox" class="checkbox" id="dark-mode-toggle">
                                            <span class="slider"></span>
                                        </label>
                                    </div>  
                                </div>
                                <div style="display: flex; justify-content: space-between; width: 100%;">
                                    <h3 id="mp4-title" ></h3>
                                    <h3 id="mp4-title-folder"></h3>
                                </div>
                            </div>
                            <hr class="mt-2">
                            <video id="vid-source" width="100%" src="" type="video/mp4" controls>
                            </video>
                        </div>
                        <hr id='preData'>

                        <div id='dataContainer'>
                            <!-- <?php
                                // $path = RESOURCE_DIR . RESOURCE_DEFAULT;
                                
                                // if(isset($_GET['dir'])) $path = RESOURCE_DIR . $_GET['dir'] . '\\';
                                // $folderCount = 0;

                                // if(! is_dir(($path))){
                                //     echo '<p>Invalid Directory: ' . $path . '</p>';
                                //     return;
                                // }
                                
                                // foreach ($iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST) as $file) {
                                //     /** @var SplFileInfo $file */

                                //     if ($file->isDir() && basename($file) != '.thumbs') {
                                        
                                //         $out = '
                                //         <div class="col-sm-12" style="padding: 2%;">
                                //             <div class="row mb-4">
                                //                 <h4 class="col-sm-10">' . basename($file) . '</h4> 
                                //                 <button class="col-sm-2 btn btn-light folder-toggle" id="dataTable-' . $folderCount . '-collapse-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#dataTable-' . $folderCount . '-collapse">
                                //                     <i class="bi bi-list"></i>
                                //                     Show Folder
                                //                 </button>
                                //             </div>
                                //             <div class="collapse show" id="dataTable-' . $folderCount . '-collapse" data-state="0">
                                //                 <table class="vid-table hover stripe" id="dataTable-' . $folderCount . '" data-folder="' . basename($file) . '">
                                //                     <thead>
                                //                         <tr>
                                //                             <th>Title</th>
                                //                             <!-- <th>Length</th> -->
                                //                             <th>Date</th>
                                //                         </tr>
                                //                     </thead>
                                //                     <tbody>
                                //             ';

                                //             if($folderCount != 0) $out = '
                                //                     </tbody>
                                //                 </table>
                                //             </div>    
                                //         </div>
                                //         <hr>
                                //         ' . $out;

                                //         echo $out;

                                //         $folderCount += 1;
                                //     } else {
                                //         if ($file->getExtension() == 'mp4' || $file->getExtension() == 'mkv' ) {
                                //             echo('
                                //                 <tr>
                                //                     <td class="vid-row" data-col="col-title" value="' . $file . '">' . basename($file) . '</td>
                                //                     <!-- <td class="vid-row" data-col="col-length" value="0">Unimplemented</td> -->
                                //                     <td class="vid-row" data-col="col-date" value="' . filectime($file) . '">' . date("Y-m-d g:i A", filectime($file)) . '</td>
                                //                 </tr>
                                                    
                                //             ');
                                //         }
                                //     }
                                // }
                            // ?>
                                    </tbody>
                                </table>
                            </div> -->
                        </div>
                    </div>
                </div>        
            </div>
        </main>
    </body>
</html>