async function loadVideos(){
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const dir = stateVideoDirectory ?? 'anime'; 

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

