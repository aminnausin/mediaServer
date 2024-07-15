<script setup>
    import Layout from '../components/layout/Layout.vue';
    import VideoPlayer from '../components/VideoPlayer.vue';
    import { storeToRefs } from 'pinia';
    import { useAuthStore } from '../stores/AuthStore';
    import { ref, onMounted } from 'vue';
    import VideoSidebar from '../components/panels/VideoSidebar.vue';

    const authStore = useAuthStore();
    const { user, auth, csrfToken, folders, records } = storeToRefs(authStore);

    async function loadCategoryFolders(){
        if(isNaN(parseInt(stateDirectory.id))){
            toastr["error"](`An invalid category "${stateDirectory.name}" was provided in the URL.`, "Invalid Category");
            return;
        }

        fetch(`/api/folders`, {
            method: 'post',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.value
            },
            body: JSON.stringify({
                category_id:stateDirectory.id
            })
        }).then((response) => 
            response.json()
        ).then((json) => {
            // console.log(json);
            if(json.success == false){
                toastr["error"](`The directory '${stateDirectory.name}' does not exist.`, "Invalid Category");
                return;
            }

            folderData = json.data;
            parseFolders(folderData);
            loadVideosAndParse(folderData);
        }).catch((error) => {
            console.log(error);
        });
    }

    
    async function parseFolders(data){

        var folderTemplate = function(folder_id, folder_name, file_count) {
            return `
                <div class="p-2 flex flex-wrap rounded-xl bg-neutral-100 dark:bg-neutral-800 dark:text-white shadow-md w-full divide-y divide-gray-300 group">
                    <section class="flex justify-between items-baseline w-full">
                        <h2 class="text-xl truncate">${folder_name}</h2>
                    </section>
                    <aside class="flex justify-between items-center w-full pt-1">
                        <h3 class="text-lg text-left text-neutral-500">${file_count} Episode${file_count > 1 ? 's' : ''}</h2>
                        <button class="hidden group-hover:flex space-x-1 folder-link" data-id="${folder_id}" data-name="${folder_name}">
                            <span class="flex hover:bg-orange-500 hover:stroke-none border-orange-500 border-2 rounded shadow px-2 space-x-1"> 
                                Watch
                            </span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3" />
                            </svg>
                        </button>
                    </aside>
                </div>
            `
        }

        let newFoldersList = [];

        data.forEach(folder => {
            const folder_id = folder.id;
            const folder_name = folder.attributes.name;
            const file_count = folder.attributes.file_count;

            // let folder_element = folderTemplate(folder_id, folder_name, file_count);

            // $('#list-content-folders').append(folder_element);

            newFoldersList.push({folder_id, folder_name, file_count})
        });

        folders.value = [...newFoldersList];

        $('.folder-link').on('mousedown', (e) => {
            let name = e.currentTarget.dataset.name;
            let id = e.currentTarget.dataset.id;

            if(e.which != 2) reload(id, name);
            else window.open(`/${stateDirectory.name}/${name}`, '_blank');
        });
        }

    async function loadVideosAndParse(data){
    // const darkModeSettings = getDarkModeSettings();
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var folderTemplate = function(folderName, folderCount, fileElements) {
        return `
            <div class="w-full">
                <div class="folder-header flex justify-center sm:justify-start">
                    <h2 class="text-2xl py-4 hidden"> ${folderName} </h2> 
                    <h2 class="text-2xl py-4">Episodes</h2> 
                </div>
                <div class="" id="dataTable-${folderCount}-collapse" data-state="1">
                    <table class="vid-table hover stripe" id="dataTable-${folderCount}" data-folder="${folderName}">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <!-- <th>Length</th> -->
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${fileElements}
                        </tbody>
                    </table>
                </div>    
            </div>
            <hr>
        `
    }
    var videoTemplate = function(fileArray){
        const filePath = fileArray.attributes.path;
        const title = fileArray.attributes.name;
        const rawDate = new Date(fileArray.attributes.date + ' GMT');
        const id = fileArray.id;
        const filePrefix = '../';
        return `
        <tr class="vid-row" data-id="${id}" data-path="${filePrefix}${filePath}">
            <td class="vid-row-title line-clamp-1">${title}</td>
            <td class="vid-row-date line-clamp-1 truncate overflow-hidden">${(rawDate.toLocaleString([], {year: 'numeric', month: '2-digit', day:'2-digit', hour:'2-digit', minute:'2-digit', hour12: true})).toLocaleUpperCase().replaceAll('.','')}</td>
        </tr>
        `
    }
        
    // parse the user provided folder name into an actual folder in the directory and get id if id not present
    if(isNaN(parseInt(stateFolder.id))){
        if(stateFolder.name === null | stateFolder.name === undefined) {
            toastr["error"](`An invalid folder name "${stateFolder.name}" was provided in the URL.`, "Invalid Folder");
            return;
        }

        let res = data.find(folder => folder.attributes["name"].toLowerCase().localeCompare(stateFolder.name.toLowerCase()) == 0);
        if(!res){
            toastr["error"](`An invalid folder name "${stateFolder.name}" was provided in the URL.`, "Invalid Folder");
            return;
        }
        stateFolder = {id: res["id"], name: res.attributes["name"]};
    }

    fetch(`/api/videos`, {
        method: 'post',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            folder_id: stateFolder.id
        })
    }).then((response) => 
        response.json()
    ).then((json) => {
        if(json.success == false){
            toastr["error"](`The folder '${stateFolder.name}' does not exist.`, "Invalid folder");
            return;
        }

        const files = json.data;
        // console.log(files);
        let fileElements = files.map(videoTemplate);
        let folderElement = folderTemplate(stateFolder.name, 0, fileElements.toString().replaceAll(',',''));
        $('#dataContainer').empty();    
        $('#dataContainer').append(folderElement);    
        
        initVideos();
    }).catch((error) => {
        console.log(error);
    });
    }

    function initVideos(){
    let pastFirst = false;
    $('.vid-row').off('click').on('click', function(){
        let file = $(this).data('path');
        let id = $(this).data('id');
        let vidSource = document.getElementById('vid-source');
        let root = document.getElementById('root');
        let folder = $(this).closest('table');
        // let lastID = $(vidSource).data('vid-id');

        // if(!isNaN(parseInt(lastID))){
        //     addToHistory(lastID);
        // }

        try {
            vidSource.pause();
        } catch (error) {
            console.log('No video running. Cannot pause.')
        }

        $(vidSource).attr('src', file);
        // $(vidSource).data('vid-id', id);
        vidSource.load();
        if(pastFirst){
            vidSource.play();
        }
        else pastFirst = true;

        try {
            root.scrollIntoView();
        } catch (error) {
            console.log(error);
            console.log(root);
            console.log(document.getElementById('root'));
        }

        $('#mp4-title').text($(this).find(".vid-row-title").text());
        $('#mp4-title-folder').text($(folder).data('folder'));
        $('#folder-thumbnail').attr('src', `${stateThumbnailDir}${stateFolder.id}.jpg`);
        addToHistory(id);
    })

    $('.vid-row').first().click();

    $('.vid-table').each(function(){
        let id = $(this).attr('id');
        let table = new DataTable('#' + id, {
            responsive: true,
            columnDefs: [{ "width": "80%", "targets": 0 }]
        });

        document.getElementById(id).style.removeProperty('width');
    });


    $('.collapse').removeClass('show');
    //$('#dataTable-0-collapse-toggle').trigger('click');

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
    }
    

    function cycleSideBar(state){
        let listCard = document.querySelector('#list-card');
        if(state === "folders" && document.querySelector("#list-content-folders").classList.contains('hidden')){
            document.querySelector("#list-card").classList.remove("invisible");
            document.querySelector("#list-content-folders").classList.toggle("hidden");
            document.querySelector("#list-content-history").classList.add("hidden");
            $('#sidebar-title').text('Folders');
            listCard.scrollIntoView({behavior: "smooth"});
        }
        else if(state === "history" && document.querySelector("#list-content-history").classList.contains('hidden')){
            document.querySelector("#list-card").classList.remove("invisible");
            document.querySelector("#list-content-history").classList.toggle("hidden");
            document.querySelector("#list-content-folders").classList.add("hidden");
            $('#sidebar-title').text('History');
            listCard.scrollIntoView({behavior: "smooth"});
            loadHistory();
        }
        else{
            document.querySelector("#list-card").classList.add("invisible");
            document.querySelector("#list-content-history").classList.add("hidden");
            document.querySelector("#list-content-folders").classList.add("hidden");
            document.getElementById('root').scrollIntoView({behavior: "smooth"});
        }
    }

    async function loadHistory(limit = 10){
        if(!user) return;
        
        fetch(`/api/records?limit=${limit}`, {
            method: 'get',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        }).then((response) => 
            response.json()
        ).then((json) => {
            // console.log(json);
            if(json.success == true){
                parseHistory(json.data);
                console.log('loading history');
            }
        }).catch((error) => {
            console.log(error);
        });
    }

    async function addToHistory(id){
        if(!user) return;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch(`/api/records`, {
            method: 'post',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                'video_id': id,
            })
        }).then((response) => 
            response.json()
        ).then((json) => {
            // console.log(json);
            if(json.success == true) {
                // toastr['success']('Added to history!');
                // loadHistory();
                parseHistory([json.data], 10, false);
            };
        }).catch((error) => {
            console.log(error);
        });
    }

    function parseHistory(data, count = 10, empty = true) {
        let newRecordList = [];

        for (let recordCount = 0; recordCount < Math.min(data.length, count); recordCount++) {
            const recordID = data[recordCount].id;
            const videoName = data[recordCount].relationships.video_name;
            const folderName = data[recordCount].relationships.folder_name;
            const rawDate = (new Date(data[recordCount].attributes.created_at.replace(' ', 'T')));
            const rawAge = Date.now() - rawDate.getTime();
        
            const weeks = Math.round(rawAge / (1000 * 3600 * 24 * 7));
            const days = Math.round(rawAge / (1000 * 3600 * 24));
            const hours = Math.round(rawAge / (1000 * 3600));
            const minutes = Math.round(rawAge / (1000 * 60));
            const seconds = Math.round(rawAge / (1000));
        
            const timeSpan = weeks > 0 ? `${weeks} week${weeks > 1 ? 's' : ''} ago` : days > 0 ? `${days} day${days > 1 ? 's' : ''} ago` : hours > 0 ? `${hours} hour${hours > 1 ? 's' : ''} ago` : minutes > 0 ? `${minutes}m ago` : `${seconds}s ago`

            newRecordList.push({recordID, videoName, folderName, timeSpan});
        }

        records.value = [...newRecordList];
    }

    function reload(newFolderID, newFolderName){
        stateFolder = {id: newFolderID, name: newFolderName};
        window.history.pushState({dir: stateDirectory.name, folder: stateFolder.name},`${stateDirectory.name} - ${stateFolder.name}`, `/${stateDirectory.name}/${stateFolder.name}`);
        loadVideosAndParse(folderData);
    }


    onMounted(() => {
        try {
            if(stateFolder === null | stateFolder === undefined) { loadVideos(); }
            else { loadCategoryFolders(); }
        } catch (error) {
            toastr['error']('Unable to load data.');
            console.log(error);
            return;
        }
        
        $("#btn-nav-folders").on('click', function(){
            cycleSideBar("folders");
        });

        $("#btn-nav-history").on('click', function(){
            cycleSideBar("history");
        });
    })

    var folderData = [];
</script>

<template>
    <Layout>
        <template v-slot:content>
            <section id="content-video">
                <div id="video-container">
                    <VideoPlayer URL=""/>

                    <hr class="mt-4 mb-3">

                    <div class="p-6 my-4 w-full mx-auto dark:bg-neutral-800 bg-white rounded-xl shadow-lg flex justify-center sm:justify-between gap-4 flex-wrap sm:flex-nowrap">
                        <div class="flex items-center space-x-4">
                            <div class="shrink-0">
                                <img id="folder-thumbnail" class="h-16 lg:h-24 object-contain rounded-md" src="http://app.test:8080/storage/thumbnails/folders/5.jpg" onerror='this.onerror=null;this.src="http\:\/\/app.test:8080/storage/thumbnails/folders/5.jpg";' alt="Folder Cover Art">
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
        </template>
        <template v-slot:sidebar>
            <VideoSidebar/>
        </template>
    </Layout>
</template>