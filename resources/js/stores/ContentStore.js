import { defineStore, storeToRefs } from 'pinia';
import { computed, ref } from 'vue';
import { useAuthStore } from './AuthStore';
import { useAppStore } from './AppStore';
import { useToast } from '@/composables/useToast';
import { useRoute } from 'vue-router';

import recordsAPI from '@/service/recordsAPI';
import mediaAPI from '@/service/mediaAPI.ts';

export const useContentStore = defineStore('Content', () => {
    const AppStore = useAppStore();
    const AuthStore = useAuthStore();

    const route = useRoute();
    const toast = useToast();

    const fullRecordsLoaded = ref(false);
    const stateRecords = ref([]);

    // dir: {id: 1, name: 'anime', folders: ["id": "6", "name": "Frieren", "path": "anime/Frieren", "file_count": 28, "category_id": "1","series": null] } -> api/categories/1 -> folders dont hold video data
    // folder: {id: 11, name: 'BOCCHI THE ROCK', videos: [id, name, pat, date, metadata], series: {}}

    const videoSortColumn = ref('title');
    const videoSortDir = ref(1);
    const searchQuery = ref('');

    const stateDirectory = ref({ id: 7, name: 'anime', folders: [] });
    const stateFolder = ref({ id: 7, name: 'ODDTAXI', videos: [], series: null });

    const stateFilteredPlaylist = computed(() => {
        // let dir = videoSortDir.value;
        let list = stateFolder.value.videos;

        let searchedList = searchQuery.value
            ? list.filter((video) => {
                  {
                      try {
                          let strRepresentation = [video.name, video.date].join(' ').toLowerCase();
                          // console.log(strRepresentation);

                          return strRepresentation.includes(searchQuery.value.toLowerCase());
                      } catch (error) {
                          console.log(error);
                          return false;
                      }
                  }
              })
            : list;

        let sortedList = videoSortColumn.value
            ? searchedList.sort((videoA, videoB) => {
                  if (videoSortColumn.value === 'date') {
                      let dateA = new Date(videoA[videoSortColumn.value]);
                      let dateB = new Date(videoB[videoSortColumn.value]);
                      return (dateB - dateA) * videoSortDir.value;
                  }
                  if (videoSortColumn.value === 'name' || videoSortColumn.value === 'title')
                      return videoA[videoSortColumn.value].localeCompare(videoB[videoSortColumn.value]) * videoSortDir.value;
                  return (videoB[videoSortColumn.value] - videoA[videoSortColumn.value]) * videoSortDir.value;
              })
            : searchedList;

        return sortedList;
    }); // use a computed ref?
    const stateVideo = ref({});

    const { pageTitle } = storeToRefs(AppStore);
    const { userData } = storeToRefs(AuthStore);

    async function recordsSort(column = 'created_at', dir = 1) {
        let tempList = stateRecords.value.sort((recordA, recordB) => {
            if (column === 'created_at') {
                let dateA = new Date(recordA['created_at']);
                let dateB = new Date(recordB['created_at']);
                return (dateB - dateA) * dir;
            }
            return recordB?.relationships[column]?.localeCompare(recordA?.relationships[column]) * dir;
        });
        stateRecords.value = tempList;
        return tempList;
    }

    function playlistFind(id) {
        let result = stateFilteredPlaylist.value.length > 0 ? stateFilteredPlaylist.value[0] : {};

        if (id && stateVideo.value.id === id) return;

        if (!isNaN(parseInt(id))) {
            result = stateFilteredPlaylist.value.find((video) => {
                return video.id === id;
            });
        }
        if (!result) toast.add({ type: 'danger', title: 'Invalid Video', description: 'Selected video cannot be found...' });
        else {
            stateVideo.value = result;
            document.title = `${stateFolder.value.name} · ${stateVideo.value?.title ?? stateVideo.value?.name}`;
        }
    }

    // needs to go in the computed property
    async function playlistSort(column = 'name', dir = 1) {
        // videoSort.value = { ...videoSort.value, column, dir };

        let parsedDir = parseInt(dir);

        videoSortDir.value = !isNaN(parsedDir) ? parsedDir : 1;
        videoSortColumn.value = column;
        // searchQuery.value = '';
        // console.log(videoSortDir.value);

        // console.log('outter', videoSort);

        // let tempList = statePlaylist.value.sort((videoA, videoB) => {
        //     if (column === 'date') {
        //         let dateA = new Date(videoA[column]);
        //         let dateB = new Date(videoB[column]);
        //         return (dateB - dateA) * dir;
        //     }
        //     if (column === 'name' || column === 'title') return videoA[column].localeCompare(videoB[column]) * dir;
        //     return (videoB[column] - videoA[column]) * dir;
        // });
        // statePlaylist.value = tempList;
        // return tempList;
    }

    //#region DATA FETCHING
    async function getRecords(limit) {
        if (!userData.value) return;

        if (fullRecordsLoaded.value) {
            Promise.resolve(stateRecords.value);
            return;
        }

        if (!limit) fullRecordsLoaded.value = true;

        stateRecords.value = [];

        const { data, error } = await recordsAPI.getRecords(limit ? `?limit=${limit}` : '');

        if (error || !data?.success) {
            console.log(error ?? data?.message);
            return Promise.reject([]);
        }
        stateRecords.value = data?.data ?? []; // always overwrite because if limit is set and results cached, no request is made. Otherwise its a full request.

        return Promise.resolve(stateRecords.value);
    }

    async function getCategory(URL_CATEGORY, URL_FOLDER) {
        const { data: response, error } = await mediaAPI.getCategory(`${URL_CATEGORY}${URL_FOLDER ? '/' + URL_FOLDER : ''}`); // => {dir: {id,name,folderCount}, folder: {id,name,videos[],series}}

        // statedir (list of folders) = dir => /api/categories/1
        // statefolder (list of videos) = folder => /api/folders/8?videos=true

        if (error || !response?.success) {
            toast.add({ type: 'danger', title: 'Error', description: response?.message ?? 'Unable to load data.' });
            pageTitle.value = 'Folder not Found';
            console.log(error ?? response?.message);
            return false;
        }

        stateDirectory.value = response.data.dir;
        stateFolder.value = response.data.folder;
        // folders.value = data.data.dir.folders;

        pageTitle.value = stateFolder.value.name;

        if (!stateFolder.value.id) {
            toast.add({ type: 'danger', title: 'Invalid folder', description: `The folder '${stateFolder.value.name}' does not exist.` });
            return false;
        }

        searchQuery.value = '';

        playlistFind(route.query?.video);

        // InitPlaylist();
        return true;
    }

    async function getFolder(nextFolderName) {
        const nextFolder = stateDirectory.value.folders?.find((folder) => {
            return folder.name === nextFolderName;
        });

        if (!nextFolder?.id) {
            toast.add({ type: 'danger', title: 'Invalid folder', description: `The folder '${nextFolderName}' does not exist.` });
            return;
        }

        const { data, error } = await mediaAPI.getFolder(nextFolder.id); // get videos with given folder id (list of videos organised by folder id)

        if (error) {
            toast.add({ type: 'danger', title: 'Invalid folder', description: `The folder '${nextFolderName}' does not exist.` });
            pageTitle.value = 'Folder not Found';
            console.log(error ?? data?.message);
            return Promise.reject(false);
        }

        stateFolder.value = { ...data.data };
        pageTitle.value = stateFolder.value.name;

        playlistFind(route.query?.video);
        return Promise.resolve(true);
    }
    //#endregion

    //#region DATA UPDATES

    async function updateViewCount(id) {
        const { data, error } = await mediaAPI.viewVideo(id);

        if (error || !data?.success) {
            console.log(error ?? data?.message);
            return Promise.reject([]);
        }
        stateVideo.value.view_count += 1;
        return Promise.resolve(stateVideo.value);
    }

    //these should go maybe in video player and record card idk
    async function createRecord(id) {
        if (!userData.value) return;
        const { data, error } = await recordsAPI.createRecord({ video_id: id });

        if (error || !data?.success) {
            console.log(error ?? data?.message);
            return Promise.reject([]);
        }
        stateRecords.value = [data?.data, ...stateRecords.value];
        return Promise.resolve(stateRecords.value);
    }

    async function deleteRecord(id) {
        const recordID = parseInt(id);
        const { data, error } = await recordsAPI.deleteRecord(`/${recordID}`);
        if (error || !data?.success) {
            console.log(error ?? data?.message);
            return false;
        }

        stateRecords.value = stateRecords.value.filter((record) => {
            return record.id != recordID;
        });

        return true;
    }

    async function updateVideoData(data) {
        if (!data) return;

        stateVideo.value = { ...stateVideo.value, ...data };

        stateFolder.value = {
            ...stateFolder.value,
            videos: stateFolder.value.videos.map((video) => {
                return video.id === stateVideo.value.id ? stateVideo.value : video;
            }),
        };
    }

    async function updateFolderData(data) {
        if (!data) return;

        stateFolder.value = { ...stateFolder.value, series: { ...data } };

        stateDirectory.value = {
            ...stateDirectory.value,
            folders: stateDirectory.value.folders.map((folder) => {
                return folder.id === stateFolder.value.id ? { ...folder, series: { ...data } } : folder;
            }),
        };
    }

    //#endregion

    return {
        // folders,
        stateRecords,
        stateDirectory,
        stateFolder,
        stateVideo,
        searchQuery,
        stateFilteredPlaylist,
        getRecords,
        createRecord,
        deleteRecord,
        recordsSort,
        getCategory,
        getFolder,
        updateViewCount,
        updateVideoData,
        updateFolderData,
        playlistFind,
        playlistSort,
    };
});
