import { formatFileSize, toFormattedDuration } from '@/service/util';
import { defineStore, storeToRefs } from 'pinia';
import { useRoute, useRouter } from 'vue-router';
import { CompareStrategies } from '@/service/sort/strategies';
import { computed, ref } from 'vue';
import { sortObjectNew } from '@/service/sort/baseSort';
import { useAuthStore } from '@/stores/AuthStore';
import { toast } from '@/service/toaster/toastService';

import recordsService from '@/service/recordsService';
import mediaAPI from '@/service/mediaAPI.ts';

export const useContentStore = defineStore('Content', () => {
    const AuthStore = useAuthStore();
    const route = useRoute();
    const router = useRouter();

    const fullRecordsLoaded = ref(false);
    const stateRecords = ref([]);

    // dir: {id: 1, name: 'anime', folders: ["id": "6", "name": "Frieren", "path": "anime/Frieren", "file_count": 28, "category_id": "1","series": null] } -> api/categories/1 -> folders dont hold video data
    // folder: {id: 11, name: 'BOCCHI THE ROCK', videos: [id, name, pat, date, metadata], series: {}}

    const videoSort = ref({ column: 'title', dir: 1 });
    const searchQuery = ref('');

    const stateDirectory = ref({ id: -1, name: '', folders: [] });
    const stateFolder = ref({ id: -1, name: '', videos: [], series: null });
    const stateVideo = ref({});

    const stateFilteredPlaylist = computed(() => {
        if (!stateFolder.value?.videos) return [];

        const searchTerm = searchQuery.value.toLowerCase().trim();
        const filteredResult = searchTerm
            ? stateFolder.value.videos.filter((video) => {
                  try {
                      let strRepresentation = [
                          video.name,
                          video.title,
                          video.description,
                          video.date_uploaded,
                          video.episode ?? '',
                          video.season ?? '',
                          video.views,
                          toFormattedDuration(video.duration) ?? 'N/A',
                          video.video_tags?.map((tag) => tag?.name).join(' ') ?? '',
                          video.file_size ? formatFileSize(video.file_size) : '',
                      ];
                      return strRepresentation.join(' ').toLowerCase().includes(searchTerm);
                  } catch (error) {
                      console.error('Error filtering video:', video, error);
                      return false;
                  }
              })
            : stateFolder.value.videos;

        let sortCriteria = [{ key: videoSort.value.column }];

        if (videoSort.value.column === 'episode') {
            sortCriteria = [{ compareFn: CompareStrategies.episode }];
        }

        if (['date', 'date_released', 'date_uploaded'].includes(videoSort.value.column)) {
            sortCriteria[0].compareFn = CompareStrategies.date;
        }

        // old sorting function: return filteredResult.sort(sortObject(videoSort.value.column, videoSort.value.dir));
        return filteredResult.sort(sortObjectNew(sortCriteria, videoSort.value.dir));
    });

    const nextVideoURL = computed(() => {
        if (!stateFilteredPlaylist.value || !stateDirectory.value.name || !stateFolder.value.name || !stateVideo.value) return '';

        const currentIndex = stateFilteredPlaylist.value.findIndex((video) => video.id === stateVideo.value.id);

        if (currentIndex === -1 || currentIndex === stateFilteredPlaylist.value.length - 1) return '';

        return encodeURI(`/${stateDirectory.value.name}/${stateFolder.value.name}?video=${stateFilteredPlaylist.value[currentIndex + 1].id}`);
    });

    const previousVideoURL = computed(() => {
        if (!stateFilteredPlaylist.value || !stateDirectory.value.name || !stateFolder.value.name || !stateVideo.value) return '';

        const currentIndex = stateFilteredPlaylist.value.findIndex((video) => video.id === stateVideo.value.id);

        if (currentIndex <= 0) return '';

        return encodeURI(`/${stateDirectory.value.name}/${stateFolder.value.name}?video=${stateFilteredPlaylist.value[currentIndex - 1].id}`);
    });

    const { userData } = storeToRefs(AuthStore);

    async function recordsSort(column = 'created_at', dir = 1) {
        let tempList = stateRecords.value.sort((recordA, recordB) => {
            if (column === 'created_at') {
                let dateA = new Date(recordA?.attributes['created_at']);
                let dateB = new Date(recordB?.attributes['created_at']);
                return (dateB - dateA) * dir;
            }
            let valueA = column === 'folder_name' ? recordA.relationships?.folder?.name : recordA.relationships[column];
            let valueB = column === 'folder_name' ? recordB.relationships?.folder?.name : recordB.relationships[column];

            if (valueA && valueB && typeof valueA === 'number' && typeof valueB === 'number') return (valueA - valueB) * dir;
            return `${valueA}`?.toLowerCase().replaceAll(/\s+/g, ' ')?.localeCompare(`${valueB}`?.toLowerCase().replaceAll(/\s+/g, ' ')) * dir;
        });
        stateRecords.value = tempList;
        return tempList;
    }

    function playlistFind(id) {
        let result = stateFilteredPlaylist.value.length > 0 ? stateFilteredPlaylist.value[0] : {};

        if (id && stateVideo.value.id === id) return false;

        if (!isNaN(parseInt(id))) {
            result = stateFilteredPlaylist.value.find((video) => {
                return video.id === id;
            });
        }
        if (!result) {
            toast.add('Invalid Video', { type: 'danger', description: 'Selected video cannot be found...' });
            return false;
        }
        stateVideo.value = result;
        return true;
    }

    // needs to go in the computed property
    async function playlistSort(sort = { column: 'name', dir: 1 }) {
        videoSort.value = { ...videoSort.value, ...sort };
    }

    //#region DATA FETCHING
    /**
     *
     * @param {number} [limit] - Optional limit
     * @returns User Watch History
     */
    async function getRecords(limit) {
        if (!userData.value) return;

        if (fullRecordsLoaded.value) {
            Promise.resolve(stateRecords.value);
            return;
        }

        if (!limit) fullRecordsLoaded.value = true;

        const { data, error } = await recordsService.getRecords(limit);

        if (error) {
            const message = error?.message || data?.message || 'Unknown error occurred';
            console.log(message);
            throw new Error(message);
        }
        stateRecords.value = data?.data ?? []; // always overwrite because if limit is set and results cached, no request is made. Otherwise its a full request.

        return Promise.resolve(stateRecords.value);
    }

    async function getCategory(URL_CATEGORY, URL_FOLDER) {
        try {
            const { data: response } = await mediaAPI.getCategory(`${URL_CATEGORY}${URL_FOLDER ? '/' + URL_FOLDER : ''}`); // => {dir: {id,name,folderCount}, folder: {id,name,videos[],series}}

            // statedir (list of folders) = dir => /api/categories/1
            // statefolder (list of videos) = folder => /api/folders/8?videos=true

            stateDirectory.value = response.data.dir;
            stateFolder.value = response.data.folder;

            if (!stateFolder.value.id) {
                toast.add('Invalid folder', { type: 'danger', description: `The folder '${stateFolder.value.name}' does not exist.` });
                return false;
            }

            const correctCategory = stateDirectory.value.name;
            const correctFolder = stateFolder.value.name;

            if (route.params.category !== correctCategory || route.params.folder !== correctFolder) {
                router.replace({
                    name: route.name,
                    params: {
                        category: correctCategory,
                        folder: correctFolder,
                    },
                    query: route.query,
                });
            }

            searchQuery.value = '';

            playlistFind(route.query?.video);

            return true;
        } catch (error) {
            if ((error?.name ?? 'AxiosError') !== 'AxiosError') {
                toast.add('Error', { type: 'danger', description: response?.message ?? 'Unable to load data.' });
                console.log(error ?? response?.message);
            }
            return false;
        }
    }

    async function getFolder(nextFolderName) {
        if (stateFolder.value.name === nextFolderName) {
            return Promise.resolve(true);
        }

        const nextFolder = stateDirectory.value.folders?.find((folder) => {
            return folder.name === nextFolderName;
        });

        if (!nextFolder?.id) {
            const message = `The folder '${nextFolderName}' does not exist.`;
            toast.add('Invalid folder', { type: 'danger', description: message });
            throw new Error(message);
        }

        const { data, error } = await mediaAPI.getFolder(nextFolder.id); // get videos with given folder id (list of videos organised by folder id)

        if (error) {
            toast.add('Invalid folder', { type: 'danger', description: `The folder '${nextFolderName}' does not exist.` });
            console.log(error ?? data?.message);
            throw error;
        }

        stateFolder.value = { ...data.data };
        searchQuery.value = '';
        playlistFind(route.query?.video);

        return true;
    }
    //#endregion

    //#region DATA UPDATES

    async function updateViewCount(id) {
        const { data, error } = await mediaAPI.viewVideo(id);

        if (error) {
            const message = error?.message || data?.message || 'Unknown error occurred';
            console.log(message);
            throw new Error(message);
        }
        stateVideo.value.view_count += 1;
        return Promise.resolve(stateVideo.value);
    }

    //these should go maybe in video player and record card idk
    async function createRecord(id) {
        if (!userData.value) return;
        const { data, error } = await recordsService.createRecord({ video_id: id });

        if (error) {
            const message = error?.message || data?.message || 'Unknown error occurred';
            console.log(message);
            throw new Error(message);
        }
        stateRecords.value = [data?.data, ...stateRecords.value];
        return Promise.resolve(stateRecords.value);
    }

    async function deleteRecord(id) {
        const recordID = parseInt(id);
        const { data, error } = await recordsService.deleteRecord(recordID);
        if (error) {
            console.log(error ?? data?.message);
            return false;
        }

        stateRecords.value = stateRecords.value.filter((record) => {
            return record.id != recordID;
        });

        return true;
    }

    // Video Resource
    async function updateVideoData(data) {
        if (!data) return;

        if (data.id === stateVideo.value.id) stateVideo.value = { ...stateVideo.value, ...data };

        const updatedVideos = stateFolder.value.videos.map((video) => (video.id === data.id ? { ...video, ...data } : video));

        stateFolder.value = {
            ...stateFolder.value,
            videos: updatedVideos,
        };
    }

    // Series Model
    async function updateFolderData(data) {
        if (!data) return;

        if (data.folder_id === stateFolder.value.id) stateFolder.value = { ...stateFolder.value, series: { ...data } };

        const updatedFolders = stateDirectory.value.folders.map((folder) => (folder.id === data.folder_id ? { ...folder, series: { ...data } } : folder));

        stateDirectory.value = {
            ...stateDirectory.value,
            folders: updatedFolders,
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
        videoSort,
        nextVideoURL,
        previousVideoURL,
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
