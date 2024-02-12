document.addEventListener("DOMContentLoaded", function(event) {
    if(folderName === null | folderName === undefined) { loadVideos(); }
    else { loadVideosTest(); }
    toggleDarkMode();

    document.getElementById('dark-mode-toggle').addEventListener('click', () => { toggleDarkMode(); });

    $("#user_options").on('click', function(){
        document.querySelector("#user_dropdown").classList.toggle("tw-hidden");
        console.log("nfw");
    });
});

async function loadVideos(){
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const dir = videoDirectory ?? 'anime'; 

    fetch('/ajax/generateDir', {
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
        parseVideos(json.result);
    }).catch((error) => {
        console.log(error);
    });
}

async function loadVideosTest(){
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const dir = videoDirectory ?? 'anime'; 

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
        parseVideosTest(json.result);
    }).catch((error) => {
        console.log(error);
    });
}

function parseVideos(data){
    // const dataContainer = document.getElementById('dataContainer');
    const darkModeSettings = getDarkModeSettings();

    var folderTemplate = function(folderName, folderCount, fileElements) {
        return `
            <div class="col-sm-12">
                <div class="folder-header row mb-4">
                    <h4 class="col-sm-8 col-lg-10 ps-0 text-center text-sm-start"> ${folderName} </h4> 
                    <button class="col-sm-4 col-lg-2 btn ${darkModeSettings.btnClass} folder-toggle" id="dataTable-${folderCount}-collapse-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#dataTable-${folderCount}-collapse">
                        <i class="bi bi-list"></i>
                        Show Folder
                    </button>
                </div>
                <div class="collapse show" id="dataTable-${folderCount}-collapse" data-state="0">
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
        let fileName = fileArray['name'].substring(1);
        let title = fileArray['title'];
        let date = fileArray['date'];
        let formattedDate = fileArray['formattedDate'];
        return `
        <tr>
            <td class="vid-row" data-col="col-title" value="${fileName}">${title}</td>
            <!-- <td class="vid-row" data-col="col-length" value="0">Unimplemented</td> -->
            <td class="vid-row" data-col="col-date" value="${date}">${formattedDate}</td>
        </tr>
        `
    }
    
    // console.log(data.length);
    
    for (let folderCount = 0; folderCount < data.length; folderCount++) {
        const folderName = data[folderCount]['name'];
        const files = data[folderCount]['files'];

        let fileElements = files.map(videoTemplate);
        let folderElement = folderTemplate(folderName, folderCount, fileElements.toString().replaceAll(',',''));
        $('#dataContainer').append(folderElement);
    }

    initVideos();
}

async function parseVideosTest(data){
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
        let filePrefix = '../';
        return `
        <tr>
            <td class="vid-row" data-col="col-title" value="${filePrefix}${fileName}">${title}</td>
            <!-- <td class="vid-row" data-col="col-length" value="0">Unimplemented</td> -->
            <td class="vid-row" data-col="col-date" value="${date}">${date}</td>
        </tr>
        `
    }

    let selectedFolderName = folderName;
    let folder_id = -1;

    if(folderName === null | folderName === undefined) {
        toastr["error"](`An invalid folder name [${folderName}] was provided in the URL.`, "Invalid Folder");
        return;
    }

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
        $('#dataContainer').append(folderElement);    
        
        initVideos();
    }).catch((error) => {
        console.log(error);
    });
}

function initVideos(){
    let pastFirst = false;
    $('.vid-row').off('click').on('click', function(){
        let file = $(this).attr('value');
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
        if(pastFirst) vidSource.play();
        else pastFirst = true;

        try {
            root.scrollIntoView();
        } catch (error) {
            console.log(error);
            console.log(root);
            console.log(document.getElementById('root'));
        }

        $('#mp4-title').text($(this).text());
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
    let darkModeToggle = document.getElementById('dark-mode-toggle');
    let rootHTML = document.querySelector('html');
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

function getDarkModeSettings(){
    let darkModeToggle = document.getElementById('dark-mode-toggle');
    let currElemClass = darkModeToggle.checked ? 'light-mode' : 'dark-mode';
    let currBtnClass = darkModeToggle.checked ? 'btn-light' : 'btn-dark';

    return {
        elementClass: currElemClass,
        btnClass: currBtnClass
    }
}