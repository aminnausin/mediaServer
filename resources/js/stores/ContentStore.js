import recordsAPI from "../service/recordsAPI";
import mediaAPI from "../service/mediaAPI";

import { ref, watch } from "vue";
import { defineStore, storeToRefs } from "pinia";
import { useAppStore } from "./AppStore";
import { useAuthStore } from "./AuthStore";


export const useContentStore = defineStore('Content', () => {
    const AppStore = useAppStore();
    const AuthStore = useAuthStore();

    const folders = ref([]);
    const videos = ref([]);
    const records = ref([]);
    
    const stateDirectory = ref({id:7, name:'anime', folders: []})
    const stateFolder = ref({id:7, name:'ODDTAXI', videos: []})

    const statePlaylist = ref([]);
    const stateFilteredPlaylist  = ref([]);
    const stateVideo = ref({});

    const { pageTitle } = storeToRefs(AppStore);
    const { userData } = storeToRefs(AuthStore);

    const searchQuery = ref('');
    const filterQuery = ref({search: '', season: -1, tag: ''});
    const sortQuery = ref({name: 0, date: 0});

    async function getRecords(limit){
        if(!userData.value) return;
        
        const { data, error } = await recordsAPI.getRecords(limit ? `?limit=${limit}`: '');

        if(error || !data?.success){
            console.log(error ?? data?.message);
            return Promise.reject([]);
        }
        records.value = data?.data ?? [];

        return Promise.resolve(records.value)
        //parseHistory(data.data);
    }

    async function createRecord(id, limit = 10){
        if(!userData.value) return;
        const { data, error } = await recordsAPI.createRecord({ 'video_id': id });

        if(error || !data?.success){
            console.log(error ?? data?.message);
            return Promise.reject([]);
        }
        console.log(Math.max(limit - 1, records.value.length - 1));
        records.value = [data?.data, ...records.value.slice(0, Math.max(limit, records.value.length - 1))];
        return Promise.resolve(records.value)
        //parseHistory([data.data], 10, false);
    }

    async function deleteRecord(id){
        const recordID = parseInt(id);
        const { data, error } = await recordsAPI.deleteRecord(`/${recordID}`)
        if(error || !data?.success){
            // eslint-disable-next-line no-undef
            toastr['error'](data?.message ?? 'Unable to delete record.');
            console.log(error ?? data?.message);
            return;
        }

        // eslint-disable-next-line no-undef
        toastr.success('Record deleted!');
        records.value = records.value.filter((record) => { 
            return record.id != recordID;
        });
    }

    async function getCategory(URL_CATEGORY, URL_FOLDER) {
        const { data, error } = await mediaAPI.getCategory(`${URL_CATEGORY}${URL_FOLDER ? '/' + URL_FOLDER : ''}`)
        if(error || !data?.success){
            // eslint-disable-next-line no-undef
            toastr['error'](data?.message ?? 'Unable to load data.');
            pageTitle.value = 'Folder not Found';
            console.log(error ?? data?.message);
            return false;
        }

        stateDirectory.value = data.data.dir;
        stateFolder.value = data.data.folder;
        folders.value = data.data.dir.folders;

        pageTitle.value = stateFolder.value.name;

        InitPlaylist();
        return true;
    }

    async function getFolder(nextFolderName) {
        const nextFolder = stateDirectory.value.folders?.find((folder) => {return folder.attributes.name === nextFolderName});

        if(!nextFolder?.id){
            // eslint-disable-next-line no-undef
            toastr["error"](`The folder '${nextFolderName}' does not exist.`, "Invalid folder");
            return;
        }

        const { data, error } = await mediaAPI.getVideos({ folder_id: nextFolder.id}); // get videos with given folder id (list of videos organised by folder id)

        if(error || !data?.success){
            // eslint-disable-next-line no-undef
            toastr["error"](`The folder '${nextFolderName}' does not exist. ${error?.message}`, "Invalid folder");
            pageTitle.value = 'Folder not Found';
            console.log(error ?? data?.message);
            return Promise.reject(false);
        }
        stateFolder.value = {id: nextFolder.id, name: nextFolder.attributes.name, videos: data.data};
        pageTitle.value = stateFolder.value.name;

        InitPlaylist();
        return Promise.resolve(true);
    }

    //Playlist and Search

    // PlaylistSeek (index) next previous specific
    function playlistSeek(direction){
        let nextIndex = stateVideo.value.index + direction;
        if( nextIndex < 0 || nextIndex > statePlaylist.value.length ) return;

        stateVideo.value = statePlaylist.value[nextIndex];
    }

    function playlistFind(id){
        if(stateVideo.value.id === id) return;
        stateVideo.value = statePlaylist.value.find((video) => { 
            return video.id === id
        });
    }

    // InitPlaylist (set up playlist with indexes and current video)
    async function InitPlaylist(){
        if(!stateFolder.value.id){
            // eslint-disable-next-line no-undef
            toastr["error"](`The folder '${stateFolder.value.name}' does not exist.`, "Invalid folder");
            console.log(stateFolder.value);
            return;
        }  

        statePlaylist.value = stateFolder.value.videos.map((video, index) => { return {index, ...video}; } );
        stateVideo.value = statePlaylist.value.length > 0 ? statePlaylist.value[0] : {};
        playlistSort();
        playlistFilter();
    }

    const playlistSort = (column = 'date', dir = 1) => {
        if(dir === 0 && sortQuery.value[column] === 0) dir = 1; // If never used, sort ascending
        else if(dir === 0) dir = -sortQuery.value[column]; // if toggle, set negative order

        let tempList = statePlaylist.value.sort((videoA, videoB) => {
            if(column === 'date'){
                let dateA = new Date(videoA?.attributes[column]);
                let dateB = new Date(videoB?.attributes[column]);
                return (dateB - dateA) * dir;
            }
            return videoA?.attributes[column].localeCompare(videoB?.attributes[column]) * dir;
        });

        stateFilteredPlaylist.value = tempList;

        let tempQuery = sortQuery.value;
        tempQuery[column] = dir;
        sortQuery.value = tempQuery;
        return tempList;
    }

    const playlistFilter = (query) => {
        let tempList = query ? statePlaylist.value.filter((video) => {{
            try {
                let strRepresentation = [video.attributes.name, video.attributes.date].join(' ').toLowerCase();
                return strRepresentation.includes(query.toLowerCase())
            } catch (error) {
                console.log(error);
                return false
            }
        }}) : statePlaylist.value;
        stateFilteredPlaylist.value = tempList;
    }

    watch(searchQuery, playlistFilter, {immediate: false});

    return {
        folders, videos, records, 
        stateDirectory, stateFolder, stateVideo,
        searchQuery, filterQuery, stateFilteredPlaylist,
        getRecords, createRecord, deleteRecord,
        getCategory, getFolder, playlistSeek, playlistFind, playlistSort
    };
});