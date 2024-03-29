document.addEventListener("DOMContentLoaded", function(event) {
    //loadVideosJQuery();
    loadVideos();
    //initVideos();
    toggleDarkMode();

    document.getElementById('dark-mode-toggle').addEventListener('click', () => { toggleDarkMode(); });
});

function loadVideosJQuery(){
    $.ajax({
        type: 'post',
        url: './ajax/get_videos.ajax.php',
        data: {
            dir: 'tv'
        }
    }).done((result) => {
        console.log(result);
    });
}

async function loadVideos(){
    fetch('./ajax/get_videos.ajax.php', {
        method: 'post',
        body: JSON.stringify({
            dir:'tv'
        }),
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    }).then((response) => 
        response.json()
    ).then((json) => {
        console.log(json);
        //let border = document.getElementById('preData');
        //border.insertAdjacentHTML("afterend", json.result);
        parseVideos(json.result);
    }).catch((error) => {
        console.log(error);
    });
}

function parseVideos(data){
    const dataContainer = document.getElementById('dataContainer');
    const darkModeSettings = getDarkModeSettings();

    var folderTemplate = function(folderName, folderCount, fileElements) {
        return `
            <div class="col-sm-12" style="padding: 2%;">
                <div class="row mb-4">
                <h4 class="col-sm-10"> ${folderName} </h4> 
                    <button class="col-sm-2 btn ${darkModeSettings.btnClass} folder-toggle" id="dataTable-${folderCount}-collapse-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#dataTable-${folderCount}-collapse">
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
    
    for (let folderCount = 0; folderCount < data.length; folderCount++) {
        const folderName = data[folderCount]['name'];
        const files = data[folderCount]['files'];

        let fileElements = files.map(videoTemplate);
        let folderElement = folderTemplate(folderName, folderCount, fileElements.toString().replaceAll(',',''));
        $('#dataContainer').append(folderElement);
    }

    initVideos();
}

function initVideos(){
    let pastFirst = false;
    $('.vid-row').off('click').on('click', function(){
        let file = $(this).attr('value');
        let vidSource = document.getElementById('vid-source');
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

        $('#mp4-title').text($(this).text());
        $('#mp4-title-folder').text($(folder).data('folder'));
    })

    $('.vid-row').first().click();

    $('.vid-table').each(function(){
        let id = $(this).attr('id');
        let table = new DataTable('#' + id, {
            responsive: true,
            columnDefs: [{ "width": "80%", "targets": 0 }]
            // columns: [{ "width": "80%" },{ "width": "20%" }]
        });

        document.getElementById(id).style.removeProperty('width');
    });

    
    $('.collapse').removeClass('show');
    $('#dataTable-0-collapse-toggle').trigger('click');

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

    darkModeToggle.checked ? rootHTML.classList.remove("dark") : rootHTML.classList.add("dark");

    let elems = document.querySelectorAll('.' + currElemClass);
    let btns = document.querySelectorAll('.' + currBtnClass);

    for (let el of elems) {
        el.classList.replace(currElemClass, nextElemClass);
    }

    for (let btn of btns) {
        btn.classList.replace(currBtnClass, nextBtnClass);
    }

    // if (document.getElementById('dark-mode-toggle').checked) {
    //     $('.dark-mode').addClass('light-mode').removeClass('dark-mode');
    //     $('.btn-dark').addClass('btn-light').removeClass('btn-dark');
    //     // html.setAttribute('data-bs-theme', '');
    //     root_html.classList.remove("dark");
    // }
    // else{
    //     $('.light-mode').addClass('dark-mode').removeClass('light-mode');
    //     $('.btn-light').addClass('btn-dark').removeClass('btn-light');
    //     // html.setAttribute('data-bs-theme', 'dark');
    //     root_html.classList.add("dark");
    // }
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