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
        loadHistory();

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

    $("#btn-nav-back").on('click', function(){
        cycleSideBar("folders");
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

//#region UI Navigation

function showLogin(){
    window.location.href = "/login";
}

function showSignup(){
    window.location.href = "/register";
}

//#endregion

async function loadHistory(){
    if(!user) return;
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
            parseHistory([json.data], false);
        };
    }).catch((error) => {
        console.log(error);
    });
}

function parseHistory(data, empty = true) {
    let destination = '#content-history';
    let recordTemplate = function(videoName, folderName, timeSpan, date, recordID) {
        return `
            <div class="flex gap-12 group cursor-default">
                <div class="flex rounded-xl items-center justify-center dark:bg-neutral-800 dark:hover:bg-neutral-700 hover:bg-slate-200 bg-slate-100  dark:text-white shadow p-3 w-full  group flex-grow cursor-pointer">
                    <div class="w-full flex-wrap divide-y divide-gray-300">
                        <section class="flex justify-between items-baseline w-full">
                            <h2 class="text-xl text-left truncate">${videoName}</h2>
                            <h2 class="text-xl text-right truncate">${date}</h2>
                        </section>
                        <aside class="flex justify-between items-center w-full pt-1">
                            <h3 class="text-lg text-left text-neutral-500 truncate">${folderName}</h3>
                            <h3 class="text-lg text-right text-neutral-500 text-nowrap line-clamp-1 truncate">${timeSpan}</h3>
                            <span class="hidden flex space-x-1">
                                <button class="hover:bg-orange-500 hover:stroke-none border-orange-500 border-2 rounded shadow px-2">Watch</button>
                                <svg xmlns="https://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3" />
                                </svg>
                            </span>
                        </aside>
                    </div>
                </div>
                <div class="aspect-square rounded-full items-center justify-center group-hover:visible invisible flex">
                    <button class="record-delete w-full p-3 items-center justify-center rounded-xl bg-red-700 hover:bg-red-600" data-id='${recordID}'>
                        Delete
                    </button>
                </div>
            </div>
        `
    }

    //if(empty) $(destination).empty();

    for (let recordCount = 0; recordCount < data.length; recordCount++) {
        const recordID = data[recordCount].id;
        const videoName = data[recordCount].relationships.video_name;
        const folderName = data[recordCount].relationships.folder_name;
        const categoryName = data[recordCount].relationships.category_name;
        const rawDate = (new Date(data[recordCount].attributes.created_at.replace(' ', 'T')));
        const rawAge = Date.now() - rawDate.getTime();
    
        const weeks = Math.round(rawAge / (1000 * 3600 * 24 * 7));
        const days = Math.round(rawAge / (1000 * 3600 * 24));
        const hours = Math.round(rawAge / (1000 * 3600));
        const minutes = Math.round(rawAge / (1000 * 60));
        const seconds = Math.round(rawAge / (1000));
    
        const timeSpan = weeks > 0 ? `${weeks} week${weeks > 1 ? 's' : ''} ago` : days > 0 ? `${days} day${days > 1 ? 's' : ''} ago` : hours > 0 ? `${hours} hour${hours > 1 ? 's' : ''} ago` : minutes > 0 ? `${minutes}m ago` : `${seconds}s ago`

        let recordElement = recordTemplate(videoName, folderName, timeSpan, `${rawDate.toLocaleDateString([], {year: "numeric", month: '2-digit', day: '2-digit', hour: '2-digit', hour12:false, minute:'2-digit'})}`, recordID);
        if(empty) $(destination).append(recordElement)
        else {
            $(destination).children().last().remove();
            $(destination).prepend(recordElement);
        }

        $(destination).children().last().children().first().on('click', () => {
            window.location.href = `/${categoryName}/${folderName}`
        })
    }

    $(".record-delete").off('click').on('click.delete', function(){
        const id = parseInt($(this).data('id'));
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        if(isNaN(id)){
            toastr.error(`Invalid Record ID... ${$(this).data('id')}`);
            return;
        }

        fetch(`/api/records/${id}`, {
            method: 'delete',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        }).then((response) => 
            response.json()
        ).then((json) => {
            if(json.success == true) {
                toastr.success('Record deleted!');
                $(this).parent().parent().remove();
            };
        }).catch((error) => {
            console.log(error);
            toastr.error('Unable to delete record.');
        });
    })
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