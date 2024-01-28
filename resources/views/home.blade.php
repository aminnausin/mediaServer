<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}"> 

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

                        <div id='dataContainer'></div>
                    </div>
                </div>        
            </div>
        </main>
    </body>
</html>