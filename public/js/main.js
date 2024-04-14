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
        if(stateFolder === null | stateFolder === undefined) { loadVideos(); }
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

function reload(newFolderID, newFolderName){
    stateFolder = {id: newFolderID, name: newFolderName};
    window.history.pushState({dir: stateDirectory.name, folder: stateFolder.name},`${stateDirectory.name} - ${stateFolder.name}`, `/${stateDirectory.name}/${stateFolder.name}`);
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

//#endregion

async function loadHistory(limit = 10){
    if(!user) return;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
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
    let destination = '#list-content-history';
    let recordTemplate = function(videoName, folderName, timeSpan) {
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

    if(empty) $(destination).empty();

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
        if(empty) $(destination).append(recordElement)
        else {
            $(destination).children().last().remove();
            $(destination).prepend(recordElement);
        }
    }
}

async function loadCategoryFolders(){
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    if(stateDirectory.id === -1){
        toastr["error"](`An invalid category "${stateDirectory.name}" was provided in the URL.`, "Invalid Category");
        return;
    }

    fetch(`/api/folders`, {
        method: 'post',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
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

    data.forEach(folder => {
        const folder_id = folder.id;
        const folder_name = folder.attributes.name;
        const file_count = folder.attributes.file_count;

        let folder_element = folderTemplate(folder_id, folder_name, file_count);

        $('#list-content-folders').append(folder_element);
    });

    $('.folder-link').on('mousedown', (e) => {
        let name = e.currentTarget.dataset.name;
        let id = e.currentTarget.dataset.id;

        if(e.which != 2) reload(id, name);
        else window.open(`/${stateDirectory.name}/${name}`, '_blank');
    });
}

async function loadVideosAndParse(data){
    const darkModeSettings = getDarkModeSettings();
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
        const date = fileArray.attributes.date;
        const id = fileArray.id;
        const filePrefix = '../';
        return `
        <tr class="vid-row" data-id="${id}" data-path="${filePrefix}${filePath}">
            <td class="vid-row-title line-clamp-1">${title}</td>
            <td class="vid-row-date line-clamp-1 truncate overflow-hidden">${date}</td>
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