import recordsAPI from "@/service/recordsAPI";
import mediaAPI from "@/service/mediaAPI";
import { toFormattedDate } from '@/service/util';

import { ref, watch } from "vue";
import { defineStore, storeToRefs } from "pinia";
import { useAppStore } from "./AppStore";
import { useAuthStore } from "./AuthStore";
import { useRoute } from "vue-router";
import { useToast } from "../composables/useToast";


export const useContentStore = defineStore('Content', () => {
    const AppStore = useAppStore();
    const AuthStore = useAuthStore();

    const route = useRoute();
    const toast = useToast();

    const folders = ref([]);
    const records = ref([]);

    const fullRecordsLoaded = ref(false);

    const stateDirectory = ref({id:7, name:'anime', folders: []})
    const stateFolder = ref({id:7, name:'ODDTAXI', videos: []})

    const statePlaylist = ref([]);
    const stateFilteredPlaylist  = ref([]);
    const stateVideo = ref({});

    const { pageTitle } = storeToRefs(AppStore);
    const { userData } = storeToRefs(AuthStore);

    const searchQuery = ref('');
    const filterQuery = ref({search: '', season: -1, tag: ''});

    async function updateViewCount(id){
        const { data, error } = await mediaAPI.viewVideo(id);

        if(error || !data?.success){
            console.log(error ?? data?.message);
            return Promise.reject([]);
        }
        stateVideo.value.attributes.view_count += 1;
        return Promise.resolve(stateVideo.value)
    }

    async function getRecords(limit){
        if(!userData.value) return;
        
        if(fullRecordsLoaded.value){
            Promise.resolve(records.value);
            return;
        }

        if(!limit) fullRecordsLoaded.value = true;

        records.value = [];

        const { data, error } = await recordsAPI.getRecords(limit ? `?limit=${limit}`: '');

        if(error || !data?.success){
            console.log(error ?? data?.message);
            return Promise.reject([]);
        }
        records.value = data?.data ?? []; // always overwrite because if limit is set and results cached, no request is made. Otherwise its a full request.

        return Promise.resolve(records.value)
    }

    async function createRecord(id){
        if(!userData.value) return;
        const { data, error } = await recordsAPI.createRecord({ 'video_id': id });

        if(error || !data?.success){
            console.log(error ?? data?.message);
            return Promise.reject([]);
        }
        records.value = [data?.data, ...records.value];
        return Promise.resolve(records.value)
    }

    async function deleteRecord(id){
        const recordID = parseInt(id);
        const { data, error } = await recordsAPI.deleteRecord(`/${recordID}`)
        if(error || !data?.success){
            console.log(error ?? data?.message);
            return false;
        }

        records.value = records.value.filter((record) => { 
            return record.id != recordID;
        });

        return true;
    }

    const recordsSort = (column = 'created_at', dir = 1) => {
        let tempList = records.value.sort((recordA, recordB) => {
            if(column === 'created_at'){
                let dateA = new Date(recordA?.attributes['created_at']);
                let dateB = new Date(recordB?.attributes['created_at']);
                return (dateB - dateA) * dir;
            }
            return recordB?.relationships[column]?.localeCompare(recordA?.relationships[column]) * dir;
        });
        records.value = tempList;
        return tempList;
    }

    async function getCategory(URL_CATEGORY, URL_FOLDER) {
        const { data, error } = await mediaAPI.getCategory(`${URL_CATEGORY}${URL_FOLDER ? '/' + URL_FOLDER : ''}`)
        if(error || !data?.success){
            toast({ type: 'danger', title:'Error', description: data?.message ?? 'Unable to load data.'})
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
            toast({ type: 'danger', title:'Invalid folder', description: `The folder '${nextFolderName}' does not exist.`})
            return;
        }

        const { data, error } = await mediaAPI.getVideos({ folder_id: nextFolder.id}); // get videos with given folder id (list of videos organised by folder id)

        if(error || !data?.success){
            toast({ type: 'danger', title:'Invalid folder', description: `The folder '${nextFolderName}' does not exist.`})
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
        let result = statePlaylist.value.length > 0 ? statePlaylist.value[0] : {};

        if(id && stateVideo.value.id === id) return;

        if(!isNaN(parseInt(id))){
            result = statePlaylist.value.find((video) => { 
                return video.id === id
            });
        }
        if(!result) toast({ type: 'danger', title:'Invalid Video', description: 'Selected video cannot be found...'})
        else stateVideo.value = result;
    }

    // InitPlaylist (set up playlist with indexes and current video)
    async function InitPlaylist(){
        filterQuery.value = {search: '', season: -1, tag: ''};
        if(!stateFolder.value.id){
            toast({ type: 'danger', title:'Invalid folder', description: `The folder '${stateFolder.value.name}' does not exist.`})
            return;
        }  

        statePlaylist.value = stateFolder.value.videos.map((video, index) => { 
            video.attributes.date = toFormattedDate(new Date(video.attributes.date + ' GMT'));
            return {index, ...video}; 
        } );
        playlistSort();
        searchQuery.value = '';
        playlistFind(route.query?.video);
    }

    const playlistSort = (column = 'name', dir = 1) => {
        let tempList = statePlaylist.value.sort((videoA, videoB) => {
            if(column === 'date'){
                let dateA = new Date(videoA?.attributes[column]);
                let dateB = new Date(videoB?.attributes[column]);
                return (dateB - dateA) * dir;
            }
            if(column === 'name' || column === 'title') return videoA?.attributes[column].localeCompare(videoB?.attributes[column]) * dir;
            return (videoB?.attributes[column] - videoA?.attributes[column]) * dir;
        });

        stateFilteredPlaylist.value = tempList;
        return tempList;
    }

    const playlistFilter = (query) => {
        console.log(query);
        
        let tempList = query ? statePlaylist.value.filter((video) => {{
            try {
                let strRepresentation = [video.attributes.name, video.attributes.date].join(' ').toLowerCase();
                console.log(strRepresentation);
                
                return strRepresentation.includes(query.toLowerCase())
            } catch (error) {
                console.log(error);
                return false
            }
        }}) : statePlaylist.value;
        stateFilteredPlaylist.value = tempList;
    }

    const updateVideoData = (data, index) => {
        const localIndex = Object.keys(statePlaylist.value).find((entry) => statePlaylist.value[entry].index == index);

        if(localIndex) {
            statePlaylist.value[localIndex] = {...data};
        }
    }

    watch(searchQuery, playlistFilter, {immediate: false});

    return {
        folders, records, 
        stateDirectory, stateFolder, stateVideo,
        searchQuery, filterQuery, stateFilteredPlaylist,
        getRecords, createRecord, deleteRecord, recordsSort,
        getCategory, getFolder, 
        updateViewCount, updateVideoData,
        playlistSeek, playlistFind, playlistSort
    };
});