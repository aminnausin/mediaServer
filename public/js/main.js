var token = ''
var folderData = [];

/*
    Video Retrieval Structure :

    Requires :
        - directory
        - folder name
    
    Path :
        -> DOMContentLoaded
        -> loadCategoryFolders()      -  get folders in directory and then
            -> parseFolders()    -  create ui components for each folder from last request
            -> loadVideosAndParse() -  parses the given folder name to a folder from last request and creates a datatable to correspond to it
                                 -  gets videos that have given folder's id and creates ui components (table rows) and then
                -> initVideos()  -  initialises ui functionality for each video row
    
    Video History Structure :


*/

document.addEventListener("DOMContentLoaded", function(event) {
    try {
        if(stateFolderName === null | stateFolderName === undefined) { loadVideos(); }
        else { loadCategoryFolders(); }

        if(document.getElementById('dark-mode-toggle')){
            document.getElementById('dark-mode-toggle').addEventListener('click', () => { toggleDarkMode(); });
            toggleDarkMode();
        }
    } catch (error) {
        toastr['error']('Unable to load data.');
        console.log(error);
        return;
    }

    //#region UI Navigation

    $("#user_header").on('click', function(){
        document.querySelector("#user_dropdown").classList.toggle("hidden");
    });

    $("#btn-nav-folders").on('click', function(){
        cycleSideBar("folders");
    });

    $("#btn-nav-history").on('click', function(){
        cycleSideBar("history");
    });

    window.addEventListener('click', function(e){
        try {
            let dropdown = this.document.querySelector("#user_dropdown")
            if (!this.document.querySelector("#user_options").contains(e.target)){
                dropdown.classList.add("hidden");
            } 
        } catch (error) {
            console.log(error);
        }
    });

    //#endregion
});

function reload(newFolder){
    stateFolderName = newFolder;
    window.history.pushState({dir: stateVideoDirectory, folder: stateFolderName},`${stateVideoDirectory} - ${stateFolderName}`, `/${stateVideoDirectory}/${stateFolderName}`);
    loadVideosAndParse(folderData);
}

//#region UI Navigation

function showLogin(){
    //logIn();
    window.location.href = "/login";
    // document.querySelector("#content-video").classList.add("hidden");
    // document.querySelector("#content-sign-up").classList.add("hidden");
    // document.querySelector("#content-sign-in").classList.remove("hidden");
}

function showSignup(){
    window.location.href = "/register";
    // document.querySelector("#content-video").classList.add("hidden");
    // document.querySelector("#content-sign-in").classList.add("hidden");
    // document.querySelector("#content-sign-up").classList.remove("hidden");
}

function showHistory(){
    document.querySelector("#content-video").classList.add("hidden");
    document.querySelector("#content-history").classList.remove("hidden");
    // document.querySelector("#content-sign-in").classList.add("hidden");
    // document.querySelector("#content-sign-up").classList.remove("hidden");
}

function showContent(){
    // document.querySelector("#content-sign-up").classList.add("hidden");
    // document.querySelector("#content-sign-in").classList.add("hidden");
    document.querySelector("#content-history").classList.add("hidden");
    document.querySelector("#content-video").classList.remove("hidden");
}

function cycleSideBar(state){
    if(state === "folders" && document.querySelector("#list-content-folders").classList.contains('hidden')){
        document.querySelector("#list-card").classList.remove("invisible");
        document.querySelector("#list-content-folders").classList.toggle("hidden");
        document.querySelector("#list-content-history").classList.add("hidden");
        $('#sidebar-title').text('Folders');
    }
    else if(state === "history" && document.querySelector("#list-content-history").classList.contains('hidden')){
        document.querySelector("#list-card").classList.remove("invisible");
        document.querySelector("#list-content-history").classList.toggle("hidden");
        document.querySelector("#list-content-folders").classList.add("hidden");
        $('#sidebar-title').text('History');
        loadHistory();
    }
    else{
        document.querySelector("#list-card").classList.add("invisible");
        document.querySelector("#list-content-history").classList.add("hidden");
        document.querySelector("#list-content-folders").classList.add("hidden");
    }
}

//#endregion

async function loadHistory(){
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch(`/api/records`, {
        method: 'get',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    }).then((response) => 
        response.json()
    ).then((json) => {
        console.log(json);
        if(json.success == true){
            parseHistory(json.data);
            console.log('loading history');
        }
    }).catch((error) => {
        console.log(error);
    });
}

async function addToHistory(id){
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
        console.log(json);
        if(json.success == true) {
            // toastr['success']('Added to history!');
            loadHistory();
        };
    }).catch((error) => {
        console.log(error);
    });
}

function parseHistory(data, count = 10, destination = '#list-content-history') {
    var recordTemplate = function(videoName, folderName, timeSpan) {
        return `
            <div class="flex flex-wrap rounded-xl dark:bg-neutral-800 dark:hover:bg-neutral-700 hover:bg-slate-200 bg-slate-100  dark:text-white shadow p-[3%] w-full divide-y divide-gray-300 group flex-grow">
                <section class="flex justify-between items-baseline w-full">
                    <h2 class="text-xl text-left truncate">${videoName}</h2>
                </section>
                <aside class="flex justify-between items-center w-full pt-1">
                    <h3 class="text-lg text-left text-neutral-500 truncate">${folderName}</h2>
                        <h3 class="text-lg text-right text-neutral-500 text-nowrap line-clamp-1 truncate">${timeSpan}</h3>
                        <span class="hidden flex space-x-1">
                            <button class="hover:bg-orange-500 hover:stroke-none border-orange-500 border-2 rounded shadow px-2">Watch</button>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3" />
                            </svg>
                        </span>
                </aside>
            </div>
        `
    }
    $(destination).empty();

    for (let recordCount = 0; recordCount < Math.min(data.length, count); recordCount++) {
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

        let recordElement = recordTemplate(videoName, folderName, timeSpan);

        $(destination).append(recordElement);
    }
}

async function loadCategoryFolders(){
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const dir = stateVideoDirectory ?? 'anime'; 

    fetch(`/ajax/getFolders`, {
        method: 'post',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            dir:dir
        })
    }).then((response) => 
        response.json()
    ).then((json) => {
        console.log(json);
        if(json.success == false){
            toastr["error"](`The directory '${dir}' does not exist.`, "Invalid Category");
            return;
        }

        folderData = json.result;
        parseFolders(folderData);
        loadVideosAndParse(folderData);
    }).catch((error) => {
        console.log(error);
    });
}

async function parseFolders(data){

    var folderTemplate = function(folderName, fileCount) {
        return `
            <div class="p-2 flex flex-wrap rounded-xl dark:bg-neutral-800 bg-slate-100 dark:text-white shadow w-full divide-y divide-gray-300 group">
                <section class="flex justify-between items-baseline w-full">
                    <h2 class="text-xl truncate">${folderName}</h2>
                </section>
                <aside class="flex justify-between items-center w-full pt-1">
                    <h3 class="text-lg text-left text-neutral-500">${fileCount} Episodes</h2>
                    <span class="hidden group-hover:flex space-x-1">
                        <button class="flex hover:bg-orange-500 hover:stroke-none border-orange-500 border-2 rounded shadow px-2 space-x-1" onClick="reload('${folderName}');"> 
                            Watch
                        </button>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3" />
                        </svg>
                    </span>
                </aside>
            </div>
        `
    }

    for (let folderCount = 0; folderCount < data.length; folderCount++) {
        const thisFolderName = data[folderCount]['name'];
        const fileCount = data[folderCount]['file_count']
        console.log(data[folderCount]);
        let folderElement = folderTemplate(thisFolderName, fileCount);

        $('#list-content-folders').append(folderElement);
    }
}

async function loadVideosAndParse(data){
    const darkModeSettings = getDarkModeSettings();
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    var folderTemplate = function(folderName, folderCount, fileElements) {

        old = `
                <button class="col-sm-4 col-lg-2 btn ${darkModeSettings.btnClass} folder-toggle" id="dataTable-${folderCount}-collapse-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#dataTable-${folderCount}-collapse">
                    <i class="bi bi-list"></i>
                    Show Folder
                </button>
            `
        return `
            <div class="col-sm-12">
                <div class="folder-header row mb-4">
                    <h4 class="col-sm-8 col-lg-10 ps-0 text-center text-sm-start"> ${folderName} </h4> 
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
        let fileName = fileArray['path'];
        let title = fileArray['name'];
        let date = fileArray['date'];
        let id = fileArray['id'];
        let filePrefix = '../';
        return `
        <tr class="vid-row" data-id="${id}" data-path="${filePrefix}${fileName}">
            <td class="vid-row-title truncate">${title}</td>
            <td class="vid-row-date truncate">${date}</td>
        </tr>
        `
    }

    let selectedFolderName = stateFolderName;
    let folder_id = -1;

    if(selectedFolderName === null | selectedFolderName === undefined) {
        toastr["error"](`An invalid folder name [${selectedFolderName}] was provided in the URL.`, "Invalid Folder");
        return;
    }

    // parse the user provided folder name into an actual folder in the directory and get id
    for (let i = 0; i < data.length; i++) {
        const folder = data[i];
        if(folder["name"].toLowerCase().localeCompare(selectedFolderName.toLowerCase()) == 0){
            folder_id = folder["id"];
            selectedFolderName = folder["name"];
            break;
        }
    }

    fetch(`/ajax/getVideos`, {
        method: 'post',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            folder_id:folder_id
        })
    }).then((response) => 
        response.json()
    ).then((json) => {
        if(json.success == false){
            toastr["error"](`The folder '${selectedFolderName}' does not exist.`, "Invalid folder");
            return;
        }

        const files = json.result;
        let fileElements = files.map(videoTemplate);
        let folderElement = folderTemplate(selectedFolderName, 0, fileElements.toString().replaceAll(',',''));
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
        try {
            vidSource.pause();
        } catch (error) {
            console.log('No video running. Cannot pause.')
        }

        $('#vid-source').attr('src', file);
        vidSource.load();
        if(pastFirst){
            vidSource.play();
            addToHistory(id);
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

function toggleDarkMode(){
    let rootHTML = document.querySelector('html');
    try {
        let darkModeToggle = document.getElementById('dark-mode-toggle');
        let currElemClass = !darkModeToggle.checked ? 'light-mode' : 'dark-mode';
        let nextElemClass = !darkModeToggle.checked ? 'dark-mode' : 'light-mode';
        let currBtnClass = !darkModeToggle.checked ? 'btn-light' : 'btn-dark';
        let nextBtnClass = !darkModeToggle.checked ? 'btn-dark' : 'btn-light';

        darkModeToggle.checked ? rootHTML.classList.remove("dark", "tw-dark") : rootHTML.classList.add("dark", "tw-dark");

        let elems = document.querySelectorAll('.' + currElemClass);
        let btns = document.querySelectorAll('.' + currBtnClass);

        for (let el of elems) {
            el.classList.replace(currElemClass, nextElemClass);
        }

        for (let btn of btns) {
            btn.classList.replace(currBtnClass, nextBtnClass);
        }
    } catch (error) {
        let darkModeToggle = {checked: true}
        let currElemClass = !darkModeToggle.checked ? 'light-mode' : 'dark-mode';
        let nextElemClass = !darkModeToggle.checked ? 'dark-mode' : 'light-mode';
        let currBtnClass = !darkModeToggle.checked ? 'btn-light' : 'btn-dark';
        let nextBtnClass = !darkModeToggle.checked ? 'btn-dark' : 'btn-light';

        darkModeToggle.checked ? rootHTML.classList.remove("dark", "tw-dark") : rootHTML.classList.add("dark", "tw-dark");

        let elems = document.querySelectorAll('.' + currElemClass);
        let btns = document.querySelectorAll('.' + currBtnClass);

        for (let el of elems) {
            el.classList.replace(currElemClass, nextElemClass);
        }

        for (let btn of btns) {
            btn.classList.replace(currBtnClass, nextBtnClass);
        }
    }
}

function getDarkModeSettings(){
    try {
        let darkModeToggle = document.getElementById('dark-mode-toggle');
        let currElemClass = darkModeToggle.checked ? 'light-mode' : 'dark-mode';
        let currBtnClass = darkModeToggle.checked ? 'btn-light' : 'btn-dark';
    
        return {
            elementClass: currElemClass,
            btnClass: currBtnClass
        }
    } catch (error) {

        let darkModeToggle = true;
        let currElemClass = darkModeToggle ? 'light-mode' : 'dark-mode';
        let currBtnClass = darkModeToggle ? 'btn-light' : 'btn-dark';
    
        return {
            elementClass: currElemClass,
            btnClass: currBtnClass
        }
    }
}