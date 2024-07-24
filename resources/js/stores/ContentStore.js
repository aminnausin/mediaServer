import { ref } from "vue";
import { defineStore, storeToRefs } from "pinia";
import { useAppStore } from "./AppStore";
import { useAuthStore } from "./AuthStore";
import { API } from "../service/api";

export const useContentStore = defineStore('Content', () => {
    const AppStore = useAppStore();
    const AuthStore = useAuthStore();

    const folders = ref([]);
    const videos = ref([]);
    const records = ref([]);

    const stateDirectory = ref({id:7, name:'anime', folders: []})
    const stateFolder = ref({id:7, name:'ODDTAXI', videos: []})

    const { pageTitle } = storeToRefs(AppStore);
    const { userData } = storeToRefs(AuthStore);

    async function getRecords(limit){
        if(!userData.value) return;
        
        const { data, error } = await API.get(`/records${limit ? `?limit=${limit}`: ''}`);

        if(error || !data?.success){
            console.log(error ?? data?.message);
            return Promise.reject([]);
        }
        records.value = data?.data ?? [];

        return Promise.resolve(records.value)
        //parseHistory(data.data);
    }

    async function addRecord(id, limit = 10){
        if(!userData.value) return;
        const { data, error } = await API.post('/records', { 'video_id': id });

        if(error || !data?.success){
            console.log(error ?? data?.message);
            return Promise.reject([]);
        }

        records.value = [data?.data, ...records.value.slice(0, Math.max(limit - data?.length, 0))];
        return Promise.resolve(records.value)
        //parseHistory([data.data], 10, false);
    }

    async function deleteRecord(id){
        const recordID = parseInt(id);
        const { data, error } = await API.delete(`/records/${recordID}`); 

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
        const { data, error } = await API.get(`/${URL_CATEGORY} ${URL_FOLDER ? '/' + URL_FOLDER : ''}`)

        if(error || !data?.success){
            // eslint-disable-next-line no-undef
            toastr['error'](data?.message ?? 'Unable to load data.');
            pageTitle.value = 'Folder not Found';
            console.log(error ?? data?.message);
            return false;
        }

        stateDirectory.value = data.data.dir;
        stateFolder.value = data.data.folder
        folders.value = data.data.dir.folders;
        return true;
        //parseFolders();
        //loadVideosAndParse();
    }

    async function getFolder(nextFolderName) {
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
            return Promise.reject(false);
        }

        stateFolder.value = { id: nextFolder.id, name: nextFolder.attributes.name, 'videos': data}

        return Promise.resolve(true);
    }

    return {
        folders, videos, records, 
        stateDirectory, stateFolder,
        getRecords, addRecord, deleteRecord,
        getCategory, getFolder
    };
});