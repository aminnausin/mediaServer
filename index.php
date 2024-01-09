<?php
const RESOURCE_DIR = ".\\resources\\";
const RESOURCE_DEFAULT = 'tv';
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
        
        
        
        
        

        
        <style>
            html[data-bs-theme="dark"] div.dtsp-panesContainer div.dtsp-searchPane div.dataTables_wrapper div.dataTables_scrollBody {
                background: var(--bs-table-bg) !important;
            }

            .neu-card{
                border-radius: 15px;
                background: #f2f2f2;
                box-shadow:  20px 20px 60px #cecece, -20px -20px 60px #ffffff;
                margin: 2%;
                padding: 2%;
            }

            .neu-card.dark-mode{
                border-radius: 15px;
                background: #161618;
                /* box-shadow:  20px 20px 60px #131314, -20px -20px 60px #19191c; */
                box-shadow:  0px 0px 0px #131314, -0px -0px 0px #19191c;
                margin: 2%;
                padding: 2%;
            }

            .neu-card.darker{
                border-radius: 15px;
                background: #121212;
                /* box-shadow:  20px 20px 60px #0f0f0f, -20px -20px 60px #151515; */
                box-shadow:  0px 0px 0px #0f0f0f, -0px -0px 0px #151515;
                margin: 2%;
                padding: 2%;
            }

            .toggle-switch {
                position: relative;
                width: 60px;
                height: 30px;
                --light: #d8dbe0;
                --dark: #28292c;
                --link: rgb(27, 129, 112);
                --link-hover: rgb(24, 94, 82);
            }

            .switch-label {
                position: absolute;
                width: 100%;
                height: 30px;
                background-color: var(--dark);
                border-radius: 15px;
                cursor: pointer;
                border: 2px solid var(--dark);
            }

            .checkbox {
                position: absolute;
                display: none;
            }

            .slider {
                position: absolute;
                width: 100%;
                height: 100%;
                border-radius: 15px;
                -webkit-transition: 0.3s;
                transition: 0.3s;
            }

            .checkbox:checked ~ .slider {
                background-color: var(--light);
            }

            .slider::before {
                content: "";
                position: absolute;
                top: 6px;
                left: 6px;
                width: 15px;
                height: 15px;
                border-radius: 50%;
                -webkit-box-shadow: inset 7px -2px 0px 0px var(--light);
                box-shadow: inset 7px -2px 0px 0px var(--light);
                background-color: var(--dark);
                -webkit-transition: 0.3s;
                transition: 0.3s;
            }

            .checkbox:checked ~ .slider::before {
                -webkit-transform: translateX(30px);
                -ms-transform: translateX(30px);
                transform: translateX(30px);
                background-color: var(--dark);
                -webkit-box-shadow: none;
                box-shadow: none;
            }

            .dark-mode{
                background-color: #121216;
                color:#e2e0e2;
            }

            /* .light{
                background: #f2f2f2;
                background-color: #e2e0e2;"
            } */
        </style>
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
                                <div style="display: flex; justify-content: space-between; width: -webkit-fill-available;">
                                    <h3 id="mp4-title" ></h3>
                                    <h3 id="mp4-title-folder"></h3>
                                </div>
                            </div>
                            <hr class="mt-2">
                            <video id="vid-source" width="100%" src="" type="video/mp4" controls>
                            </video>
                        </div>
                        <hr>

                        <?php
                            $path = RESOURCE_DIR . RESOURCE_DEFAULT;
                            
                            if(isset($_GET['dir'])) $path = RESOURCE_DIR . $_GET['dir'] . '\\';
                            $folderCount = 0;

                            if(! is_dir(($path))){
                                echo '<p>Invalid Directory: ' . $path . '</p>';
                                return;
                            }
                            
                            foreach ($iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST) as $file) {
                                /** @var SplFileInfo $file */

                                if ($file->isDir() && basename($file) != '.thumbs') {
                                    
                                    $out = '
                                    <div class="col-sm-12" style="padding: 2%;">
                                        <div class="row mb-4">
                                            <h4 class="col-sm-10">' . basename($file) . '</h4> 
                                            <button class="col-sm-2 btn btn-light folder-toggle" id="dataTable-' . $folderCount . '-collapse-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#dataTable-' . $folderCount . '-collapse">
                                                <i class="bi bi-list"></i>
                                                Show Folder
                                            </button>
                                        </div>
                                        <div class="collapse show" id="dataTable-' . $folderCount . '-collapse" data-state="0">
                                            <table class="vid-table hover stripe" id="dataTable-' . $folderCount . '" data-folder="' . basename($file) . '">
                                                <thead>
                                                    <tr>
                                                        <th>Title</th>
                                                        <!-- <th>Length</th> -->
                                                        <th>Date</th>
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
                                    if ($file->getExtension() == 'mp4' || $file->getExtension() == 'mkv' ) {
                                        echo('
                                            <tr>
                                                <td class="vid-row" data-col="col-title" value="' . $file . '">' . basename($file) . '</td>
                                                <!-- <td class="vid-row" data-col="col-length" value="0">Unimplemented</td> -->
                                                <td class="vid-row" data-col="col-date" value="' . filectime($file) . '">' . date("Y-m-d g:i A", filectime($file)) . '</td>
                                            </tr>
                                                
                                        ');
                                    }
                                }
                            }
                        ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>        
            </div>
        </main>
    </body>
    <script>
        $(document).ready(function(){
            var pastFirst = false;
            $('.vid-row').off('click').on('click', function(){
                var file = $(this).attr('value');
                var vidSource = document.getElementById('vid-source');
                var folder = $(this).closest('table');
                try {
                    vidSource.pause();
                } catch (error) {
                    console.log('No video running. Cannot pause.')
                }

                $('#vid-source').attr('src', file);
                vidSource.load();
                if(pastFirst) vidSource.play();
                else pastFirst = true;

                $('#mp4-title').text($(this).text());
                $('#mp4-title-folder').text($(folder).data('folder'));
            })

            $('.vid-row').first().click();

            $('.vid-table').each(function(){
                let id = $(this).attr('id');
                let table = new DataTable('#' + id, {
                    //columnDefs: [{ "width": "80%", "targets": 0 }]
                    columns: [{ "width": "80%" },{ "width": "20%" }]
                });
                // $('#' + id).DataTable({
                //     responsive: true,
                //     "columns": [{ "width": "80%" },null]
                // })
            })

            toggleDarkMode()

            $('.collapse').removeClass('show');

            $('#dataTable-0-collapse-toggle').trigger('click');

        })

        $('#dark-mode-toggle').off('click.main').on('click.main', function(){
            toggleDarkMode();
        })

        $('.folder-toggle').off('click.main').on('click.main', function(){
            var targetID = $(this).data('bs-target');

            if($(targetID).data('state') == 1){
                $(targetID).data('state', 0);
                $(this).empty().append('<i class="bi bi-list"></i> Show Folder');
            } 
            else {
                $(targetID).data('state', 1);
                $(this).empty().append('<i class="bi bi-list"></i> Hide Folder');
            }
        });

        function toggleDarkMode(){
            let html = document.querySelector('html');
            if (document.getElementById('dark-mode-toggle').checked) {
                $('.dark-mode').addClass('light-mode').removeClass('dark-mode');
                $('.btn-dark').addClass('btn-light').removeClass('btn-dark');
                // html.setAttribute('data-bs-theme', '');
                html.classList.remove("dark");
            }
            else{
                $('.light-mode').addClass('dark-mode').removeClass('light-mode');
                $('.btn-light').addClass('btn-dark').removeClass('btn-light');
                // html.setAttribute('data-bs-theme', 'dark');
                html.classList.add("dark");
            }
        }
    </script>
</html>