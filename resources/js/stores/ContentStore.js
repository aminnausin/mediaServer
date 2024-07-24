import { ref } from "vue";
import { defineStore } from "pinia";

export const useContentStore = defineStore('Content', () => {
    const folders = ref([]);
    const videos = ref([]);
    const records = ref(['hah']);

    const stateDirectory = ref({id:7, name:'anime', folders: []})
    const stateFolder = ref({id:7, name:'ODDTAXI', videos: []})

    return {
        folders, videos, records, 
        stateDirectory, stateFolder
    };
});