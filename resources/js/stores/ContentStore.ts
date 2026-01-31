import type { CategoryResource, FolderResource, SeriesResource, VideoResource } from '@/types/resources';
import type { SortCriteria, SortKey } from '@/service/sort/types';

import { formatFileSize, toFormattedDuration } from '@/service/util';
import { computed, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { CompareStrategies } from '@/service/sort/strategies';
import { sortObjectNew } from '@/service/sort/baseSort';
import { toParamNumber } from '@/util/route';
import { defineStore } from 'pinia';
import { toast } from '@aminnausin/cedar-ui';

import mediaAPI from '@/service/mediaAPI.ts';

// dir: {id: 1, name: 'anime', folders: ["id": "6", "name": "Frieren", "path": "anime/Frieren", "file_count": 28, "category_id": "1","series": null] } -> api/categories/1 -> folders dont hold video data
// folder: {id: 11, name: 'BOCCHI THE ROCK', videos: [id, name, pat, date, metadata], series: {}}

const emptyLibrary: CategoryResource = { id: 0, name: '', folders: [], folders_count: 0, total_size: 0, last_scan: -1 };
const emptyFolder: FolderResource = { id: 0, name: '', title: '', path: '', file_count: 0, total_size: 0, is_majority_audio: false, category_id: 0, videos: [], last_scan: -1 };
const emptyMedia: VideoResource = { id: 0, name: '', path: '', view_count: 0, video_tags: [], created_at: '', subtitles: [] };

const DEFAULT_SORT = { column: 'name', dir: 1 };

export const useContentStore = defineStore('Content', () => {
    const router = useRouter();
    const route = useRoute();

    // #region State data
    const stateDirectory = ref<CategoryResource>(emptyLibrary);
    const stateFolder = ref<FolderResource>(emptyFolder);
    const stateVideo = ref<VideoResource>(emptyMedia);

    // Video search and sort and results
    const videoSort = ref<SortCriteria<VideoResource>>({ column: 'title', dir: 1 });
    const searchQuery = ref('');

    const stateFilteredPlaylist = computed<VideoResource[]>(() => {
        if (!stateFolder.value?.videos) return [];

        // TODO: pre-generate this search string on load and on update per video instead of regenerating all videos on every change
        const searchTerm = searchQuery.value.toLowerCase().trim();
        const filteredResult = searchTerm
            ? stateFolder.value.videos.filter((video) => {
                  try {
                      const strRepresentation = [
                          video.name,
                          video.title,
                          video.description,
                          video.file_modified_at,
                          video.episode ?? '',
                          video.season ?? '',
                          video.view_count,
                          toFormattedDuration(video.duration) ?? 'N/A',
                          video.video_tags?.map((tag) => tag?.name).join(' ') ?? '',
                          video.file_size ? formatFileSize(video.file_size) : '',
                          video.metadata?.codec ?? '',
                          video.album ?? '',
                          video.artist ?? '',
                      ];
                      return strRepresentation.join(' ').toLowerCase().includes(searchTerm);
                  } catch (error) {
                      console.error('Error filtering video:', video, error);
                      return false;
                  }
              })
            : stateFolder.value.videos;

        let sortCriteria: SortKey<VideoResource>[] = [{ key: videoSort.value.column }];

        if (videoSort.value.column === 'episode') {
            sortCriteria = [{ compareFn: CompareStrategies.episode }];
        }

        if (['released_at', 'file_modified_at'].includes(videoSort.value.column)) {
            sortCriteria[0].compareFn = CompareStrategies.date;
        }

        // old sorting function: return filteredResult.sort(sortObject(videoSort.value.column, videoSort.value.dir));
        return filteredResult.sort(sortObjectNew(sortCriteria, videoSort.value.dir));
    });

    // Relative media tracking
    const nextVideoURL = computed(() => {
        if (!stateFilteredPlaylist.value || !stateDirectory.value.name || !stateFolder.value.name || !stateVideo.value) return '';

        const currentIndex = stateFilteredPlaylist.value.findIndex((video) => video.id === stateVideo.value?.id);

        if (currentIndex === -1 || currentIndex === stateFilteredPlaylist.value.length - 1) return '';

        return encodeURI(`/${stateDirectory.value.name}/${stateFolder.value.name}?video=${stateFilteredPlaylist.value[currentIndex + 1].id}`);
    });

    const previousVideoURL = computed(() => {
        if (!stateFilteredPlaylist.value || !stateDirectory.value.name || !stateFolder.value.name || !stateVideo.value) return '';

        const currentIndex = stateFilteredPlaylist.value.findIndex((video) => video.id === stateVideo.value?.id);

        if (currentIndex <= 0) return '';

        return encodeURI(`/${stateDirectory.value.name}/${stateFolder.value.name}?video=${stateFilteredPlaylist.value[currentIndex - 1].id}`);
    });

    /**
     * Select a video by ID or default to the first video.
     * @param queryId - ID of the video from url query params or table
     * @returns {boolean} if stateVideo was updated
     */
    function playlistFind(queryId?: number): boolean {
        let result: VideoResource | undefined;

        if (Number.isFinite(queryId)) {
            result = stateFilteredPlaylist.value.find((media) => media.id === queryId);
        } else {
            // the default is the first video in the list
            result = stateFilteredPlaylist.value[0];
        }

        // Media matching query not found or no media in playlist in the first place
        if (!result) {
            // Reset state video on invalid selection
            stateVideo.value = emptyMedia;
            toast.add('Invalid File', {
                type: 'danger',
                description: stateFolder.value.videos?.length ? 'Selected file cannot be found...' : 'This folder has no files...',
            });
            return false;
        }

        // Queried media already loaded
        if (stateVideo.value.id === result.id) {
            return false;
        }

        // Load queried media
        stateVideo.value = result;
        return true;
    }

    /**
     * needs to go in the computed property
     * what is this ???
     */
    function playlistSort(sort: SortCriteria<VideoResource>) {
        videoSort.value = { ...videoSort.value, ...(sort ?? DEFAULT_SORT) };
    }

    // #endregion

    //#region DATA FETCHING
    /**
     * Loads a category and optional folder by name and updates the current category and folder state
     * @param URL_CATEGORY string url parameter describing category name or id
     * @param URL_FOLDER optional string url parameter describing folder name or id
     * @returns True if successful, false otherwise
     */
    async function getCategory(URL_CATEGORY: string, URL_FOLDER?: string): Promise<boolean> {
        try {
            const { data: response } = await mediaAPI.getCategory(`${URL_CATEGORY}${URL_FOLDER ? '/' + URL_FOLDER : ''}`); // => {dir: {id,name,folderCount}, folder: {id,name,videos[],series}}

            // statedir (list of folders) = dir => /api/categories/1
            // statefolder (list of videos) = folder => /api/folders/8?videos=true

            stateDirectory.value = response.data.dir;
            stateFolder.value = response.data.folder; // Can be the specified folder (null if invalid folder / no folders) or the first folder if not specified

            // This never happens because the api returns a 404 if the folder does not exist
            if (!stateFolder.value.id) {
                toast.add('Invalid folder', { type: 'danger', description: `The folder '${stateFolder.value.name}' does not exist.` });
                return false;
            }

            // build based on folder title (from series) and category name
            const correctCategory = stateDirectory.value.name;
            const correctFolder = stateFolder.value.title;

            // rebuilds url with exact values if url parameters were partial
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
            playlistFind(toParamNumber(route.query.video));

            return true;
        } catch (error: any) {
            const message = error?.response?.data?.message || error?.message || 'Unable to load data.';

            toast.add('Error', {
                type: 'danger',
                description: message,
            });

            console.error('Failed to load category:', error);
            clearState();
            return false;
        }
    }

    /**
     * Loads a folder by name and updates the current folder state
     * @param nextFolderName Name or title of the folder to load
     * @returns True if successful, false otherwise
     */
    async function getFolder(nextFolderName: string): Promise<boolean> {
        if (stateFolder.value.name === nextFolderName) {
            return true;
        }

        const nextFolder = stateDirectory.value.folders?.find((folder) => {
            return folder.name === nextFolderName || folder.title === nextFolderName;
        });

        if (!nextFolder?.id) {
            toast.add('Invalid folder', { type: 'danger', description: `The folder '${nextFolderName}' does not exist.` });
            stateFolder.value = emptyFolder;
            stateVideo.value = emptyMedia;
            return false;
        }

        try {
            const { data } = await mediaAPI.getFolder(nextFolder.id); // get videos with given folder id (list of videos organised by folder id)

            stateFolder.value = { ...data.data };
            searchQuery.value = '';
            playlistFind(toParamNumber(route.query.video));

            return true;
        } catch (error: any) {
            const message = error?.response?.data?.message || error?.message || 'Failed to load folder';

            toast.add('Error loading folder', {
                type: 'danger',
                description: message,
            });
            console.error('Failed to load folder:', error);
            return false;
        }
    }
    //#endregion

    //#region DATA UPDATES

    /**
     * Adds a view to a video (and metadata)
     * @param id media id
     */
    async function updateViewCount(id: number): Promise<void> {
        try {
            await mediaAPI.viewVideo(id);

            // has default value of 0
            stateVideo.value.view_count += 1;
        } catch (error: any) {
            const message = error?.message || 'Unknown error occurred';
            console.log(message);
            throw new Error(message);
        }
    }

    /**
     * Updates metadata for a video locally
     * @param data partial video resource containing updated data
     */
    function updateVideoData(data: Partial<VideoResource>) {
        if (!data?.id) return;

        if (data.id === stateVideo.value.id) stateVideo.value = { ...stateVideo.value, ...data };

        const updatedVideos = (stateFolder.value.videos ?? []).map((video) => (video.id === data.id ? { ...video, ...data } : video));

        stateFolder.value = {
            ...stateFolder.value,
            videos: updatedVideos,
        };
    }

    /**
     * Updates series for a folder locally
     * @param data partial series resource containing updated data
     */
    function updateFolderData(data: SeriesResource) {
        if (!data?.id) return;

        if (data.folder_id === stateFolder.value.id) stateFolder.value = { ...stateFolder.value, series: { ...data } };

        const updatedFolders = (stateDirectory.value.folders ?? []).map((folder) => (folder.id === data.folder_id ? { ...folder, series: { ...data } } : folder));

        stateDirectory.value = {
            ...stateDirectory.value,
            folders: updatedFolders,
        };
    }

    function clearState() {
        stateDirectory.value = emptyLibrary;
        stateFolder.value = emptyFolder;
        stateVideo.value = emptyMedia;
    }

    //#endregion

    /**
     * Watches the current route and clears app state when leaving the video player page.
     */
    watch(route, (to) => {
        if (to.name !== 'home') {
            clearState();
        }
    });

    return {
        stateDirectory,
        stateFolder,
        stateVideo,
        searchQuery,
        stateFilteredPlaylist,
        videoSort,
        nextVideoURL,
        previousVideoURL,
        getCategory,
        getFolder,
        updateViewCount,
        updateVideoData,
        updateFolderData,
        playlistFind,
        playlistSort,
    };
});
