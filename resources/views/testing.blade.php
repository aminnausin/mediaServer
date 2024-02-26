<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Anime?</title>

        <!-- Bootstrap -->
        <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet"> -->
        <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous"> -->

        <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script> -->

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
        <div class=" hidden p-6 max-w-sm mx-auto bg-white rounded-xl shadow-lg flex items-center space-x-4">
            <div class="shrink-0">
                <img class="h-12 w-12" src="storage/avatars/12345.jpg" alt="ChitChat Logo">
            </div>
            <div>
                <div class="text-xl font-medium text-black">ChitChat</div>
                <p class="text-slate-500">You have a new message!</p>
            </div>
        </div>
        <button type="button" class="hidden flex justify-center items-center select-none bg-red-500 border-2 text-white text-xl font-bold p-2 m-2 rounded-full shadow h-20 w-20 focus:outline-none focus:shadow-outline">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        
        <main class="mx-[4%] flex flex-row-reverse flex-wrap-reverse lg:flex-nowrap">
            <section id="list-card" class="invisible dark:bg-neutral-900 shadow-xl m-[1%] p-[1%] pb-[2%] rounded-[15px] light-mode w-full lg:w-[18%] space-y-2">
                <div class="flex p-1 text-ri" >
                    <h1 id="sidebar-title" class="text-2xl w-full">Folders</h1>
                </div>
                
                <hr class="mt-2 mb-3">
                <section id="list-content-folders" class="hidden flex space-y-2 flex-wrap">
                    <div class="hidden flex flex-wrap rounded-xl dark:bg-neutral-800 bg-slate-100  dark:text-white shadow p-[3%] w-full divide-y divide-gray-300 group flex-grow">
                        <section class="flex flex-row-reverse justify-between items-baseline w-full">
                            <h2 class="text-lg text-right text-neutral-500 text-nowrap">24 Episodes</h2>
                            <h2 class="text-xl text-left">Frieren</h2>
                        </section>
                        <aside class="hidden group-hover:flex justify-between items-center w-full pt-1">
                            <h3 class="text-lg text-left text-neutral-500">Drama</h2>
                            <span class="flex space-x-1">
                                <button class="hover:bg-orange-500 hover:stroke-none border-orange-500 border-2 rounded shadow px-2">Watch</button>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3" />
                                </svg>
                            </span>
                        </aside>
                    </div>
                </section>
                <section id="list-content-history" class="hidden flex space-y-2 flex-wrap">
                    <div class="flex flex-wrap rounded-xl dark:bg-neutral-800 dark:hover:bg-neutral-700 hover:bg-slate-200 bg-slate-100  dark:text-white shadow p-[3%] w-full divide-y divide-gray-300 group flex-grow">
                        <section class="flex justify-between items-baseline w-full">
                            <h2 class="text-xl text-left">S1E24</h2>
                        </section>
                        <aside class="flex justify-between items-center w-full pt-1">
                            <h3 class="text-lg text-left text-neutral-500">Frieren</h2>
                            <h3 class="text-lg text-right text-neutral-500 text-nowrap">3 days ago</h3>
                            <span class="hidden flex space-x-1">
                                <button class="hover:bg-orange-500 hover:stroke-none border-orange-500 border-2 rounded shadow px-2">Watch</button>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3" />
                                </svg>
                            </span>
                        </aside>
                    </div>
                </section>
            </section>
            <section id="content-card" class=" dark:bg-neutral-900 shadow-xl m-[1%] p-[2%] pt-[1%] rounded-[15px] light-mode w-full lg:w-[70%]">
                <nav id="navbar">
                    <div class="flex flex-row-reverse p-1 ">
                        <span class="flex max-w-sm mx-auto items-center space-x-2 shrink-0">
                            <section id="user_options" class="dropdown group inline-block relative" aria-expanded="true" aria-haspopup="true" data-dropdown-toggle="user_dropdown">
                                <div class="hidden absolute top-12 origin-top-right left-7 w-56 z-10 divide-y divide-gray-200 rounded-md shadow-lg ring-1 bg-white ring-black ring-opacity-5 focus:outline-none" id="user_dropdown" aria-orientation="vertical" aria-labelledby="user_options">
                                    @auth()
                                    <div class="divide-y divide-gray-300" role="menu" id="user-menu-auth">
                                        <section class="px-4 py-3">
                                            <p class="text-sm leading-5 text-orange-500">Signed in as</p>
                                            <p class="text-sm font-medium leading-5 text-gray-900 truncate">tom@example.com</p>
                                        </section>
                                        <section class="py-1">
                                            <button class="text-gray-700 flex justify-between w-full px-4 py-2 text-sm leading-5 text-left" role="menuitem">Account settings</button>
                                            <button class="text-gray-700 flex justify-between w-full px-4 py-2 text-sm leading-5 text-left" role="menuitem">Collections</button>
                                            <button class="text-gray-700 flex justify-between w-full px-4 py-2 text-sm leading-5 text-left" role="menuitem">Dashboard</button>
                                            <button class="text-gray-700 flex justify-between w-full px-4 py-2 text-sm leading-5 text-left" role="menuitem">Full History</button>
                                            <span role="menuitem" tabindex="-1" class="flex justify-between w-full px-4 py-2 text-sm leading-5 text-left text-gray-700 cursor-not-allowed opacity-50" aria-disabled="true">New feature (soon)</span>
                                        </section>
                                        <section class="py-1">
                                            <button class="text-gray-700 flex justify-between w-full px-4 py-2 text-sm leading-5 text-left" onclick="signOut();" role="menuitem">Sign out</button>
                                        </section>
                                    </div>
                                    @else
                                    <div role="menu" id="user-menu-unauth" class="text-gray-700">
                                        <section class="">
                                            <button class="rounded-t-md hover:bg-neutral-100 flex justify-between w-full px-4 py-2 text-sm leading-5 text-left" onclick="showSignIn();" role="menuitem">Sign in</button>
                                            <button class="rounded-b-md hover:bg-neutral-100 flex justify-between w-full px-4 py-2 text-sm leading-5 text-left" onclick="showSignUp();" role="menuitem">Sign up</button>
                                        </section>
                                    </div>
                                    @endauth
                                </div>
                                <button id="user_header" class="flex space-x-2 text-2xl text-slate-900 dark:text-white hover:text-orange-600 dark:hover:text-orange-600 items-center justify-center">
                                    @auth
                                    <span id="user_name">Aminushki</span>
                                    <img src="storage/avatars/12345.jpg" class="h-7 w-7 rounded-full sm:mx-0 sm:shrink-0 ring-2 ring-orange-600/60 shadow-lg">

                                    @else
                                    <span id="user_name_unauth" class="w-[10vw] text-right">Guest</span>
                                    <img src="storage/avatars/12345.jpg" class="h-7 w-7 rounded-full sm:mx-0 sm:shrink-0 ring-2 ring-orange-600/60 shadow-lg">
                                    @endauth

                                </button>
                            </section>

                            @auth
                            <section id="navbar-video" class="flex items-center space-x-2">
                                <button id="btn-nav-folders" class="flex justify-center items-center shrink-0 h-8 w-8 rounded-lg shadow-lg bg-red-300 hover:bg-red-200 bg-gray-300 hover:bg-slate-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" height="24" width="24" class="stroke-slate-900">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 0 0-1.883 2.542l.857 6a2.25 2.25 0 0 0 2.227 1.932H19.05a2.25 2.25 0 0 0 2.227-1.932l.857-6a2.25 2.25 0 0 0-1.883-2.542m-16.5 0V6A2.25 2.25 0 0 1 6 3.75h3.879a1.5 1.5 0 0 1 1.06.44l2.122 2.12a1.5 1.5 0 0 0 1.06.44H18A2.25 2.25 0 0 1 20.25 9v.776" />
                                    </svg>
                                </button>
                                <button id="btn-nav-history" class="flex justify-center items-center shrink-0 h-8 w-8 rounded-lg shadow-lg bg-purple-300 hover:bg-purple-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" height="24" width="24" class="fill-slate-900">
                                        <path d="M0 0h24v24H0z" fill="none" />
                                        <path fill-rule="evenodd" d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z" />
                                    </svg>
                                </button>
                                <button id="btn-nav-settings" class=" hidden flex justify-center items-center shrink-0 h-8 w-8 rounded-lg shadow-lg bg-red-300 hover:bg-red-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" height="24" width="24" class="fill-slate-900">
                                        <path fill-rule="evenodd" d="M3 6.75A.75.75 0 0 1 3.75 6h16.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 6.75ZM3 12a.75.75 0 0 1 .75-.75h16.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 12Zm0 5.25a.75.75 0 0 1 .75-.75h16.5a.75.75 0 0 1 0 1.5H3.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </section>
                            @endauth

                            <div class="toggle-switch shrink-0">
                                <label class="switch-label">
                                    <input type="checkbox" class="checkbox" id="dark-mode-toggle">
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </span>
                        <h1 id="mp4-title-folder" class="text-2xl w-full"></h1>
                    </div>
                    <hr class="mt-2 mb-3">
                </nav>
                <section id="content-video">
                    <div id="video-container">
                        <video id="vid-source" width="100%" src="" type="video/mp4" controls class="focus:outline-none">
                        </video>

                        <hr class="mt-4 mb-3">

                        <div class="p-6 my-4 w-full mx-auto dark:bg-neutral-800 bg-white rounded-xl shadow-lg flex justify-between space-x-4">
                            <div class="flex items-center space-x-4">
                                <div class="shrink-0">
                                    <img class="h-12 w-12" src="storage/avatars/12345.jpg" alt="ChitChat Logo">
                                </div>
                                <div>
                                    <div id="mp4-title" class="text-xl font-medium">ChitChat</div>
                                    <p class="dark:text-slate-400 text-slate-500">This episode is about a walrus going for a juice box run at 3am</p>
                                </div>
                            </div>
                            <div id="mp4-controls" class="my-auto container flex w-auto" role="group">
                                <button type="button" class="bg-gray-800 text-white rounded-l-md border-r border-gray-100 py-2 hover:bg-red-700 hover:text-white px-3 shadow-xl">
                                    <div class="flex flex-row align-middle">
                                        <svg class="w-5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M7.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                        <p class="ml-2">Prev</p>
                                    </div>
                                </button>
                                <button type="button" class="bg-gray-800 text-white rounded-r-md py-2 border-l border-gray-200 hover:bg-red-700 hover:text-white px-3 shadow-xl">
                                    <div class="flex flex-row align-middle">
                                        <span class="mr-2">Next</span>
                                        <svg class="w-5 ml-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>

                    <hr id='preData' class="my-4">

                    <div id='dataContainer'></div>
                </section>

                <section id="content-sign-up">

                </section>
                <section id="content-sign-in">
                    
                </section>
            </section>
            <section id="left-card" class="w-full lg:w-[15%]"></section>
        </main>
    </body>
</html>
