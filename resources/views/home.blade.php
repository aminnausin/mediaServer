<x-app-layout>
    <script src="{{ URL::asset('js/main.js') }}"></script>
    <script>
        var stateDirectory = <?php echo json_encode($dir ?? array('id'=>7,'name'=>'anime')) ?>;
        var stateFolder = <?php echo json_encode($folder ?? array('id'=>7,'name'=>'ODDTAXI')) ?>;
        var stateThumbnailDir = "{{ URL::asset('storage/thumbnails/folders') }}/"
    </script>
    @auth
    <script>var user = true;</script>
    @else
    <script>var user = false;</script>
    @endauth
    <main class="p-6 flex gap-6 flex-row-reverse flex-wrap-reverse lg:flex-nowrap snap-y">
        <section id="list-card" class="invisible dark:bg-neutral-900 shadow-xl p-3 pb-6 rounded-2xl light-mode w-full lg:w-72 shrink-0 space-y-2 scroll-mt-6">
            <div class="flex p-1 text-ri">
                <h1 id="sidebar-title" class="text-2xl w-full">Folders</h1>
            </div>

            <hr class="mt-2 mb-3">
            <section id="list-content-folders" class="hidden flex space-y-2 flex-wrap">
                <div class="hidden flex flex-wrap rounded-xl dark:bg-neutral-800 bg-slate-100  dark:text-white shadow p-[3%] w-full divide-y divide-gray-300 group flex-grow">
                    <section class="flex flex-row-reverse justify-between items-baseline w-full">
                        <h2 class="text-lg text-right text-neutral-500 text-nowrap">24 Episodes</h2>
                        <h2 class="text-xl text-left">Frieren</h2>
                    </section>
                    <aside class="flex justify-between items-center w-full pt-1">
                        <h3 class="text-lg text-left text-neutral-500">Drama</h2>
                            <span class="hidden group-hover:flex space-x-1">
                                <button class="hover:bg-orange-500 hover:stroke-none border-orange-500 border-2 rounded shadow px-2">Watch</button>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3" />
                                </svg>
                            </span>
                    </aside>
                </div>
            </section>
            <section id="list-content-history" class="hidden flex space-y-2 flex-wrap">
                <div class="hidden flex flex-wrap rounded-xl dark:bg-neutral-800 dark:hover:bg-neutral-700 hover:bg-slate-200 bg-slate-100  dark:text-white shadow p-[3%] w-full divide-y divide-gray-300 group flex-grow">
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
        <section id="content-card" class=" dark:bg-neutral-900 shadow-xl p-6 pt-3 rounded-2xl light-mode w-full">
            <nav id="navbar">
                <div class="flex p-1 gap-y-3 flex-wrap justify-between">
                    <h1 id="mp4-title-folder" class="text-2xl"></h1>
                    <span class="flex flex-wrap sm:flex-nowrap sm:max-w-sm items-center gap-2  sm:shrink-0">
                        <section id="user_options" class="group inline-block relative" data-dropdown-toggle="user_dropdown" aria-haspopup="true">
                            <button id="user_header" class="flex space-x-2 text-2xl text-slate-900 dark:text-white hover:text-orange-600 dark:hover:text-orange-600 items-center justify-center" >
                                @auth
                                <span id="user_name">{{ Auth::user()->name }}</span>
                                <img src="{{ URL::asset('storage/avatars/12345.jpg')}}" class="h-7 w-7 rounded-full sm:mx-0 sm:shrink-0 ring-2 ring-orange-600/60 shadow-lg object-cover">

                                @else
                                <span id="user_name_unauth" class="w-[10vw] text-right">Guest</span>
                                <img src="{{ URL::asset('storage/avatars/12345.jpg')}}" class="h-7 w-7 rounded-full sm:mx-0 sm:shrink-0 ring-2 ring-orange-600/60 shadow-lg object-cover">
                                @endauth
                            </button>    
                            <div role="menu" id="user_dropdown" aria-orientation="vertical" aria-labelledby="user_options" class="hidden absolute left-0 z-30 mt-4 w-56 origin-top-right divide-y divide-gray-300 rounded-md shadow-lg ring-1 bg-white ring-black ring-opacity-5 focus:outline-none text-gray-700">
                                
                                @auth
                                <!-- <div class="divide-y divide-gray-300" role="menu" id="user-menu-auth"> -->
                                <section class="flex flex-wrap gap-1 px-4 py-3">
                                    <p class="text-sm leading-5 text-orange-500">Logged in as: </p>
                                    <p class="text-sm font-medium leading-5 text-gray-900 truncate">{{ Auth::user()->email }}</p>
                                </section>
                                <section class="py-1">
                                    <button class="text-gray-700 flex justify-between w-full px-4 py-2 text-sm leading-5 text-left" role="menuitem">Account settings</button>
                                    <button class="text-gray-700 flex justify-between w-full px-4 py-2 text-sm leading-5 text-left" role="menuitem">Collections</button>
                                    <button class="text-gray-700 flex justify-between w-full px-4 py-2 text-sm leading-5 text-left" role="menuitem">Dashboard</button>
                                    <button class="text-gray-700 flex justify-between w-full px-4 py-2 text-sm leading-5 text-left" role="menuitem">Full History</button>
                                    <button class="text-gray-700 flex justify-between w-full px-4 py-2 text-sm leading-5 text-left" role="menuitem"><a href="/jobs/indexFiles" class="w-full h-full">Index Files</a></button>
                                    <span role="menuitem" tabindex="-1" class="flex justify-between w-full px-4 py-2 text-sm leading-5 text-left text-gray-700 cursor-not-allowed opacity-50" aria-disabled="true">New feature (soon)</span>
                                </section>
                                <section class="py-1">
                                    <button class="text-gray-700 flex justify-between w-full px-4 py-2 text-sm leading-5 text-left" onclick="logout();" role="menuitem">Log out</button>
                                </section>
                                <!-- </div> -->
                                @else
                                <!-- <div role="menu" id="user-menu-unauth" class="text-gray-700"> -->
                                <section class="">
                                    <button class="rounded-t-md hover:bg-neutral-100 flex justify-between w-full px-4 py-2 text-sm leading-5 text-left" onclick="showLogin();" role="menuitem">Log in</button>
                                    <button class="rounded-b-md hover:bg-neutral-100 flex justify-between w-full px-4 py-2 text-sm leading-5 text-left" onclick="showSignup();" role="menuitem">Sign up</button>
                                </section>
                                <!-- </div> -->
                                @endauth
                            </div>
                            
                        </section>


                        <section id="navbar-video" class="flex items-center space-x-2">
                            <button id="btn-nav-folders" class="flex justify-center items-center shrink-0 h-8 w-8 rounded-lg shadow-lg bg-red-300 hover:bg-red-200 bg-gray-300 hover:bg-slate-200">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" height="24" width="24" class="stroke-slate-900">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 0 0-1.883 2.542l.857 6a2.25 2.25 0 0 0 2.227 1.932H19.05a2.25 2.25 0 0 0 2.227-1.932l.857-6a2.25 2.25 0 0 0-1.883-2.542m-16.5 0V6A2.25 2.25 0 0 1 6 3.75h3.879a1.5 1.5 0 0 1 1.06.44l2.122 2.12a1.5 1.5 0 0 0 1.06.44H18A2.25 2.25 0 0 1 20.25 9v.776" />
                                </svg>
                            </button>
                            @auth
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
                            @endauth
                        </section>

                        <div class="toggle-switch shrink-0">
                            <label class="switch-label">
                                <input type="checkbox" class="checkbox invisible" id="dark-mode-toggle">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </span>
                </div>
                <hr class="mt-2 mb-3">
            </nav>
            <section id="content-video">
                <div id="video-container">
                    <video id="vid-source" width="100%" src="" type="video/mp4" controls class="focus:outline-none aspect-video">
                    </video>

                    <hr class="mt-4 mb-3">

                    <div class="p-6 my-4 w-full mx-auto dark:bg-neutral-800 bg-white rounded-xl shadow-lg flex justify-center sm:justify-between gap-4 flex-wrap sm:flex-nowrap">
                        <div class="flex items-center space-x-4">
                            <div class="shrink-0">
                                <img id="folder-thumbnail" class="h-16 lg:h-24 object-contain rounded-md" src="{{ URL::asset('storage/thumbnails/folders/5.jpg')}}" onerror="this.onerror=null;this.src=`{{ URL::asset('storage/thumbnails/folders/5.jpg')}}`;" alt="Folder Cover Art">
                            </div>
                            <div class="h-full">
                                <div id="mp4-title" class="text-xl font-medium"></div>
                                <p class="dark:text-slate-400 text-slate-500 line-clamp-2">This episode is about a walrus going for a juice box run at 3am</p>
                            </div>
                        </div>
                        <!-- <div id="mp4-controls" class="my-auto container flex w-auto" role="group">
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
                        </div> -->
                    </div>
                </div>

                <hr id='preData' class="my-4">

                <div id='dataContainer'></div>
            </section>

            <section id="content-history">

            </section>
        </section>
        <section id="left-card" class="w-full lg:w-72 shrink-0 hidden lg:block"></section>
    </main>
</x-app-layout>