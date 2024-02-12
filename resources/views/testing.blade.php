<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Anime?</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

    <!-- Jquery -->
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>

    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <!-- <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script> -->

    <!-- <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet" crossorigin="anonymous"> -->
    <!-- <link href="https://cdn.datatables.net/1.13.7/css/dataTables.tailwindcss.min.css" rel="stylesheet"> -->
    <link href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css" rel="stylesheet">

    <!-- toastr -->
    <link href="<?php if (isset($folder_name)) echo ("..");
                else echo ("."); ?>/css/toastr.min.css" rel="stylesheet">

    <script src="<?php if (isset($folder_name)) echo ("..");
                    else echo ("."); ?>/js/toastr.min.js"></script>
    <!-- Local -->
    <link href="<?php if (isset($folder_name)) echo ("..");
                else echo ("."); ?>/css/main.css" rel="stylesheet">

    @vite('resources/css/app.css')
    <script>
        var videoDirectory = <?php echo json_encode($dir ?? 'anime') ?>;
        var folderName = <?php echo json_encode($folder_name ?? 'ODDTAXI') ?>;

        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": false,
            "progressBar": true,
            "positionClass": "toast-bottom-left",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        }
    </script>
    <script src="<?php if (isset($folder_name)) echo (".."); else echo ("."); ?>/js/main.js"></script>
</head>

<body class="light-mode" id="root">
    <div class="tw-p-6 tw-max-w-sm tw-mx-auto tw-bg-white tw-rounded-xl tw-shadow-lg tw-flex tw-items-center tw-space-x-4">
        <div class="tw-shrink-0">
            <img class="tw-h-12 tw-w-12" src="storage/avatars/12345.jpg" alt="ChitChat Logo">
        </div>
        <div>
            <div class="tw-text-xl tw-font-medium tw-text-black">ChitChat</div>
            <p class="tw-text-slate-500">You have a new message!</p>
        </div>
    </div>
    <button type="button" class="tw-flex tw-justify-center tw-items-center tw-select-none tw-bg-red-500 tw-border-2 tw-text-white tw-text-xl tw-font-bold tw-p-2 tw-m-2 tw-rounded-full tw-shadow tw-h-20 tw-w-20 focus:tw-outline-none focus:tw-shadow-outline">
        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tw-feather tw-feather-x">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
    </button>
    <main class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="neu-card light-mode">
                    <div class="col-sm-12">
                        <div class="tw-flex" style="flex-direction: row-reverse;">
                            <div class="tw-flex tw-p-1 tw-max-w-sm tw-mx-auto tw-items-center tw-space-x-2 tw-shrink-0">
                                <buttons id="user_options" class="dropdown tw-flex tw-space-x-2 tw-text-2xl tw-text-slate-900 dark:tw-text-white hover:tw-text-orange-600 dark:hover:tw-text-orange-600 tw-items-center tw-justify-center" aria-expanded="true" aria-haspopup="true" data-dropdown-toggle="user_dropdown">
                                <div class="tw-hidden tw-inline-block tw-text-left" id="user_dropdown">
                                        <!--
                                        Dropdown menu, show/hide based on menu state.

                                        Entering: "transition ease-out duration-100"
                                        From: "transform opacity-0 scale-95"
                                        To: "transform opacity-100 scale-100"
                                        Leaving: "transition ease-in duration-75"
                                        From: "transform opacity-100 scale-100"
                                        To: "transform opacity-0 scale-95"
                                        -->
                                        <div class="tw-hidden tw-absolute tw-right-0 tw-z-10 tw-mt-6 tw-w-56 tw-origin-top-right tw-divide-y tw-divide-gray-100 tw-rounded-md tw-bg-white tw-shadow-lg tw-ring-1 tw-ring-black tw-ring-opacity-5 focus:tw-outline-none" role="menu" aria-orientation="vertical" aria-labelledby="user_options" tabindex="-1" id="user_options_menu">
                                            <div class="tw-py-1" role="none">
                                                <!-- Active: "bg-gray-100 text-gray-900", Not Active: "text-gray-700" -->
                                                <a href="#" class="tw-text-gray-900 tw-block tw-px-4 tw-py-2 tw-text-sm" role="menuitem" tabindex="-1" id="menu-item-0">Edit</a>
                                                <a href="#" class="tw-text-gray-900 tw-block tw-px-4 tw-py-2 tw-text-sm" role="menuitem" tabindex="-1" id="menu-item-1">Duplicate</a>
                                            </div>
                                            <div class="tw-py-1" role="none">
                                                <a href="#" class="tw-text-gray-900 tw-block tw-px-4 tw-py-2 tw-text-sm" role="menuitem" tabindex="-1" id="menu-item-2">Delete</a>
                                            </div>
                                        </div>
                                        <div class="tw-absolute tw-right-0 tw-z-10 tw-w-56 tw-mt-6 tw-origin-top-right tw-bg-white tw-border tw-border-gray-200 tw-divide-y tw-divide-gray-100 tw-rounded-md tw-shadow-lg tw-outline-none" aria-labelledby="headlessui-menu-button-1" id="headlessui-menu-items-117" role="menu">
                                            <div class="tw-px-4 tw-py-3">
                                                <p class="tw-text-sm tw-leading-5">Signed in as</p>
                                                <p class="tw-text-sm tw-font-medium tw-leading-5 tw-text-gray-900 tw-truncate">tom@example.com</p>
                                            </div>
                                            <div class="tw-py-1">
                                                <a href="javascript:void(0)" class="tw-text-gray-700 tw-flex tw-justify-between tw-w-full tw-px-4 tw-py-2 tw-text-sm tw-leading-5 tw-text-left" role="menuitem">Account settings</a>
                                                <a href="javascript:void(0)" class="tw-text-gray-700 tw-flex tw-justify-between tw-w-full tw-px-4 tw-py-2 tw-text-sm tw-leading-5 tw-text-left" role="menuitem">Support</a>
                                                <span role="menuitem" tabindex="-1" class="tw-flex tw-justify-between tw-w-full tw-px-4 tw-py-2 tw-text-sm tw-leading-5 tw-text-left tw-text-gray-700 tw-cursor-not-allowed tw-opacity-50" aria-disabled="true">New feature (soon)</span>
                                                <a href="javascript:void(0)" class="tw-text-gray-700 tw-flex tw-justify-between tw-w-full tw-px-4 tw-py-2 tw-text-sm tw-leading-5 tw-text-left" role="menuitem">License</a>
                                            </div>
                                            <div class="tw-py-1">
                                                <a href="javascript:void(0)" class="tw-text-gray-700 tw-flex tw-justify-between tw-w-full tw-px-4 tw-py-2 tw-text-sm tw-leading-5 tw-text-left" role="menuitem">Sign out</a>
                                            </div>
                                        </div>
                                    </div><span id="user_name">Aminushki</span>
                                    <img src="storage/avatars/12345.jpg" class="tw-h-7 tw-w-7 tw-rounded-full sm:tw-mx-0 sm:tw-shrink-0 tw-ring-2 tw-ring-orange-600/60 tw-shadow-lg">
                                    
                                </buttons>
                                
                                <buttons id="history" class="tw-flex tw-justify-center tw-items-center tw-shrink-0 tw-h-8 tw-w-8 tw-rounded-lg tw-shadow-lg tw-bg-red-300 hover:tw-bg-red-200 bg-gray-300 hover:bg-slate-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" height="24" width="24" class="tw-stroke-slate-900">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 0 0-1.883 2.542l.857 6a2.25 2.25 0 0 0 2.227 1.932H19.05a2.25 2.25 0 0 0 2.227-1.932l.857-6a2.25 2.25 0 0 0-1.883-2.542m-16.5 0V6A2.25 2.25 0 0 1 6 3.75h3.879a1.5 1.5 0 0 1 1.06.44l2.122 2.12a1.5 1.5 0 0 0 1.06.44H18A2.25 2.25 0 0 1 20.25 9v.776" />
                                    </svg>
                                </buttons>
                                <buttons id="history" class="tw-flex tw-justify-center tw-items-center tw-shrink-0 tw-h-8 tw-w-8 tw-rounded-lg tw-shadow-lg tw-bg-purple-300 hover:tw-bg-purple-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" height="24" width="24" class="tw-fill-slate-900">
                                        <path d="M0 0h24v24H0z" fill="none" />
                                        <path fill-rule="evenodd" d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z" />
                                    </svg>
                                </buttons>
                                <buttons id="settings" class=" tw-hidden tw-flex tw-justify-center tw-items-center tw-shrink-0 tw-h-8 tw-w-8 tw-rounded-lg tw-shadow-lg tw-bg-red-300 hover:tw-bg-red-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" height="24" width="24" class="tw-fill-slate-900">
                                        <path fill-rule="evenodd" d="M3 6.75A.75.75 0 0 1 3.75 6h16.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 6.75ZM3 12a.75.75 0 0 1 .75-.75h16.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 12Zm0 5.25a.75.75 0 0 1 .75-.75h16.5a.75.75 0 0 1 0 1.5H3.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
                                    </svg>
                                </buttons>

                                <div class="toggle-switch tw-shrink-0">
                                    <label class="switch-label">
                                        <input type="checkbox" class="checkbox" id="dark-mode-toggle">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                            </div>
                            <div style="display: flex; justify-content: space-between; width: 100%;">
                                <h1 id="mp4-title-folder" class="tw-text-3xl"></h1>
                            </div>
                        </div>
                        <hr class="tw-mt-2 tw-mb-3">
                        <video id="vid-source" width="100%" src="" type="video/mp4" controls>
                        </video>

                        <hr class="tw-mt-4 tw-mb-3">

                        <div class="tw-p-6 tw-my-4 tw-w-full tw-mx-auto tw-bg-white tw-rounded-xl tw-shadow-lg tw-flex tw-justify-between tw-space-x-4">
                            <div class="tw-flex tw-items-center tw-space-x-4">
                                <div class="tw-shrink-0">
                                    <img class="tw-h-12 tw-w-12" src="storage/avatars/12345.jpg" alt="ChitChat Logo">
                                </div>
                                <div>
                                    <div id="mp4-title" class="tw-text-xl tw-font-medium tw-text-black">ChitChat</div>
                                    <p class="tw-text-slate-500">This episode is about a walrus going for a juice box run at 3am</p>
                                </div>
                            </div>
                            <div id="mp4-controls" class="tw-my-auto tw-container tw-flex tw-w-auto" role="group">
                                <button type="button" class="tw-bg-gray-800 tw-text-white tw-rounded-l-md tw-border-r tw-border-gray-100 py-2 hover:tw-bg-red-700 hover:tw-text-white tw-px-3">
                                    <div class="tw-flex tw-flex-row tw-align-middle">
                                        <svg class="tw-w-5 tw-mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M7.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                        <p class="tw-ml-2">Prev</p>
                                    </div>
                                </button>
                                <button type="button" class="tw-bg-gray-800 tw-text-white tw-rounded-r-md tw-py-2 tw-border-l tw-border-gray-200 hover:tw-bg-red-700 hover:tw-text-white tw-px-3">
                                    <div class="tw-flex tw-flex-row tw-align-middle">
                                        <span class="tw-mr-2">Next</span>
                                        <svg class="tw-w-5 tw-ml-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>

                    <hr id='preData' class="tw-my-4">

                    <div id='dataContainer'></div>
                </div>
            </div>
        </div>
    </main>
</body>

</html>


<!-- <div class="min-h-screen bg-white py-6 flex flex-col justify-center sm:py-12">
    <div class="flex items-center justify-center p-12">
        <div class=" relative inline-block text-left dropdown">
            <span class="rounded-md shadow-sm">
                <button class="inline-flex justify-center w-full px-4 py-2 text-sm font-medium leading-5 text-gray-700 transition duration-150 ease-in-out bg-white border border-gray-300 rounded-md hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-50 active:text-gray-800" type="button" aria-haspopup="true" aria-expanded="true" aria-controls="headlessui-menu-items-117">
                    <span>Options</span>
                    <svg class="w-5 h-5 ml-2 -mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </span>
            <div class="hidden dropdown-menu">
                <div class="absolute right-0 w-56 mt-2 origin-top-right bg-white border border-gray-200 divide-y divide-gray-100 rounded-md shadow-lg outline-none" aria-labelledby="headlessui-menu-button-1" id="headlessui-menu-items-117" role="menu">
                    <div class="px-4 py-3">
                        <p class="text-sm leading-5">Signed in as</p>
                        <p class="text-sm font-medium leading-5 text-gray-900 truncate">tom@example.com</p>
                    </div>
                    <div class="py-1">
                        <a href="javascript:void(0)" class="text-gray-700 flex justify-between w-full px-4 py-2 text-sm leading-5 text-left" role="menuitem">Account settings</a>
                        <a href="javascript:void(0)" class="text-gray-700 flex justify-between w-full px-4 py-2 text-sm leading-5 text-left" role="menuitem">Support</a>
                        <span role="menuitem" tabindex="-1" class="flex justify-between w-full px-4 py-2 text-sm leading-5 text-left text-gray-700 cursor-not-allowed opacity-50" aria-disabled="true">New feature (soon)</span>
                        <a href="javascript:void(0)" class="text-gray-700 flex justify-between w-full px-4 py-2 text-sm leading-5 text-left" role="menuitem">License</a>
                    </div>
                    <div class="py-1">
                        <a href="javascript:void(0)" class="text-gray-700 flex justify-between w-full px-4 py-2 text-sm leading-5 text-left" role="menuitem">Sign out</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> -->