<script setup>
    import VideoSidebar from '../components/panels/VideoSidebar.vue';
    import LayoutBase from '../components/layout/LayoutBase.vue';
    import VideoPlayer from '../components/VideoPlayer.vue';

    import { ref, onMounted, watch } from 'vue';
    import { useContentStore } from '../stores/ContentStore';
    import { useAuthStore } from '../stores/AuthStore';
    import { useAppStore } from '../stores/AppStore';
    import { storeToRefs } from 'pinia';
    import { useRoute } from 'vue-router'
    import { API } from '../service/api';

    
    const route = useRoute();
    const appStore = useAppStore();
    const authStore = useAuthStore();
    const ContentStore = useContentStore();
    const { userData } = storeToRefs(authStore);
    const {  pageTitle, selectedSideBar } = storeToRefs(appStore);
    const { folders, records, stateFolder, stateDirectory} = storeToRefs(ContentStore);


    async function parseFolders(data){
        try {
            if(!stateDirectory.value.folders){
                // eslint-disable-next-line no-undef
                toastr["error"](`No valid folders in ${stateDirectory.value.name}`)
                return;
            }

            let newFoldersList = [];
    
            stateDirectory.value.folders.forEach(folder => {
                const folder_id = folder.id;
                const folder_name = folder.attributes.name;
                const file_count = folder.attributes.file_count;
    
                newFoldersList.push({folder_id, folder_name, file_count})
            });
    
            folders.value = newFoldersList;
        } catch (error) {
            console.log(data);
            console.log(error);
        }
    }

    async function loadVideosAndParse(){
        var folderTemplate = function(folderName, folderCount, fileElements) {
            return `
                <div class="w-full overflow-x-hidden">
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
                            <tbody class="">
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
                <td class="vid-row-title">${title}</td>
                <td class="vid-row-date truncate overflow-hidden">${(rawDate.toLocaleString([], {year: 'numeric', month: '2-digit', day:'2-digit', hour:'2-digit', minute:'2-digit', hour12: true})).toLocaleUpperCase().replaceAll('.','')}</td>
            </tr>
            `
        }

        pageTitle.value = stateFolder.value.name;

        if(!stateFolder.value.id){
            toastr["error"](`The folder '${stateFolder.value.name}' does not exist.`, "Invalid folder");
            console.log(stateFolder.value);
            return;
        }  

        const files = stateFolder.value.videos;
        // console.log(files);
        let fileElements = files.map(videoTemplate);
        let folderElement = folderTemplate(stateFolder.value.name, 0, fileElements.toString().replaceAll(',',''));
        $('#dataContainer').empty();    
        $('#dataContainer').append(folderElement);    
        
        initVideos();
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
            $('#folder-thumbnail').attr('src', `${stateThumbnailDir.value}${stateFolder.value.id}.jpg`);
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
        if(state === "history"){
            loadHistory();
        }
    }

    async function loadHistory(limit = 10){
        if(!userData.value) return;
        
        const { data, error } = await API.get(`/records?limit=${limit}`);

        if(error || !data?.success){
            console.log(error ?? data?.message);
            return;
        }

        parseHistory(data.data);
    }

    async function addToHistory(id){
        if(!userData.value) return;
        const { data, error } = await API.post('/records', { 'video_id': id });

        if(error || !data?.success){
            console.log(error ?? data?.message);
            return;
        }

        parseHistory([data.data], 10, false);
    }

    function parseHistory(data, count = 10) {
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

    async function reload(nextFolderName){
        const nextFolder = stateDirectory.value.folders?.find((folder) => {return folder.attributes.name === nextFolderName});

        if(!nextFolder.id){
            // eslint-disable-next-line no-undef
            toastr["error"](`The folder '${nextFolderName}' does not exist.`, "Invalid folder");
            return;
        }

        const { data, error } = await API.post('/videos', { folder_id: nextFolder.id});

        if(error || !data?.success){
            // eslint-disable-next-line no-undef
            toastr["error"](`The folder '${nextFolderName}' does not exist. ${error?.message}`, "Invalid folder");
            pageTitle.value = 'Folder not Found';
            console.log(error ?? data?.message);
            return;
        }

        stateFolder.value = { id: nextFolder.id, name: nextFolder.attributes.name, 'videos': data.data}
        loadVideosAndParse();
    }

    async function initData() {
        const URL_CATEGORY = route.params.category;
        const URL_FOLDER = route.params.folder;

        const { data, error } = await API.get(`/${URL_CATEGORY} ${URL_FOLDER ? '/' + URL_FOLDER : ''}`)

        if(error || !data?.success){
            // eslint-disable-next-line no-undef
            toastr['error'](data?.message ?? 'Unable to load data.');
            pageTitle.value = 'Folder not Found';
            console.log(error ?? data?.message);
            return;
        }

        stateDirectory.value = data.data.dir;
        stateFolder.value = data.data.folder

        parseFolders();
        loadVideosAndParse();
    }

    onMounted(() => {
        initData();
    })

    watch(() => route.params.folder, reload, {immediate: false});
    watch(() => selectedSideBar.value, cycleSideBar, {immediate: false});
    var stateThumbnailDir = ref("https://app.test:8080/storage/thumbnails/folders/");
</script>

<template>
    <LayoutBase>
        <template v-slot:content>
            <section id="content-video">
                <div id="video-container">
                    <VideoPlayer URL=""/>

                    <hr class="mt-4 mb-3">

                    <div class="p-6 my-4 w-full mx-auto dark:bg-primary-dark-800 bg-primary-800 rounded-xl shadow-lg flex justify-center sm:justify-between gap-4 flex-wrap sm:flex-nowrap">
                        <div class="flex items-center space-x-4">
                            <div class="shrink-0">
                                <img id="folder-thumbnail" class="h-16 lg:h-24 object-contain rounded-md" src="https://app.test:8080/storage/thumbnails/folders/5.jpg" onerror='this.onerror=null;this.src="https\:\/\/app.test:8080/storage/thumbnails/folders/5.jpg";' alt="Folder Cover Art">
                            </div>
                            <div class="h-full">
                                <div id="mp4-title" class="text-xl font-medium"></div>
                                <p class="dark:text-slate-400 text-slate-500 line-clamp-2">This episode is about a walrus going for a juice box run at 3am</p>
                            </div>
                        </div>
                        <!-- <div id="mp4-controls" class="my-auto container flex w-auto" role="group">
                            <button type="button" class="bg-gray-800 text-white rounded-l-md border-r border-gray-100 py-2 hover:bg-red-700 hover:text-white px-3 shadow-xl">
                                <div class="flex flex-row align-middle">
                                    <svg class="w-5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="https://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M7.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                    <p class="ml-2">Prev</p>
                                </div>
                            </button>
                            <button type="button" class="bg-gray-800 text-white rounded-r-md py-2 border-l border-gray-200 hover:bg-red-700 hover:text-white px-3 shadow-xl">
                                <div class="flex flex-row align-middle">
                                    <span class="mr-2">Next</span>
                                    <svg class="w-5 ml-2" fill="currentColor" viewBox="0 0 20 20" xmlns="https://www.w3.org/2000/svg">
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
    </LayoutBase>
</template>