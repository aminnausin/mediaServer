<script setup lang="ts">
import type { VideoResource } from '@/types/resources';
import type { SortDir } from '@/service/sort/types';

import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { CopyToClipboard } from '@/components/cedar-ui/clipboard';
import { useContentStore } from '@/stores/ContentStore';
import { toFormattedDate } from '@/service/util';
import { toParamNumber } from '@/util/route';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { TableBase } from '@/components/cedar-ui/table';
import { useRoute } from 'vue-router';

import VideoAmbientPlayer from '@/components/video/VideoAmbientPlayer.vue';
import VideoInfoPanel from '@/components/video/VideoInfoPanel.vue';
import VideoSidebar from '@/components/panels/VideoSidebar.vue';
import LayoutBase from '@/layouts/LayoutBase.vue';
import VideoCard from '@/components/cards/data/VideoCard.vue';
import ModalBase from '@/components/pinesUI/ModalBase.vue';

import EditVideo from '@/components/forms/EditVideo.vue';
import useModal from '@/composables/useModal';

const route = useRoute();

const { selectedSideBar, pageTitle } = storeToRefs(useAppStore());
const { getFolder, getCategory, playlistFind, playlistSort, updateVideoData } = useContentStore();
const { searchQuery, stateFilteredPlaylist, stateDirectory, stateVideo, stateFolder } = storeToRefs(useContentStore());

const editVideoModal = useModal({ title: 'Edit Video Details', submitText: 'Submit Details' });
const shareVideoModal = useModal({ title: 'Share Video' });

const queryVideoId = computed(() => toParamNumber(route.query.video));
const cachedVideo = ref<VideoResource>();
const cachedVideoUrl = computed(() => {
    if (!cachedVideo.value) return '';
    return encodeURI(document.location.origin + route.path + `?video=${cachedVideo.value.id}`);
});

const isLoading = ref(false);

const handleVideoDetailsUpdate = (res: any) => {
    if (res?.data?.id) updateVideoData(res.data as VideoResource);
    editVideoModal.toggleModal(false);
};

async function cycleSideBar(state: string) {
    if (!state) return;
    if (state === 'history') {
        // Invalidate query everytime sidebar is opened ( not anymore ? )
    }
}

async function reload() {
    if (isLoading.value) return;

    try {
        const toSingleParam = (p: string | string[]) => (Array.isArray(p) ? p[0] : p);

        const URL_CATEGORY = toSingleParam(route.params.category);
        const URL_FOLDER = toSingleParam(route.params.folder);

        isLoading.value = true;

        await nextTick();
        document.body.scrollTo({ top: 0, behavior: 'instant' });
        if (stateDirectory.value?.name && stateDirectory.value.name === URL_CATEGORY && URL_FOLDER) {
            await getFolder(URL_FOLDER);
        } else {
            await getCategory(URL_CATEGORY, URL_FOLDER);
        }
    } catch (error) {
        console.log(error);
    }
    isLoading.value = false;
    setFolderAsPageTitle();
}

//#region TABLE

const sortingOptions = computed(() => {
    return [
        {
            title: 'Title',
            value: 'title',
            disabled: false,
        },
        {
            title: 'Date Uploaded',
            value: 'date',
            disabled: false,
        },
        {
            title: 'Date Released',
            value: 'date_released',
            disabled: false,
        },
        {
            title: 'Views',
            value: 'view_count',
            disabled: false,
        },
        {
            title: 'Artist',
            value: 'artist',
            disabled: !stateFolder.value.is_majority_audio,
            hidden: !stateFolder.value.is_majority_audio,
        },
        {
            title: 'Album',
            value: 'album',
            disabled: !stateFolder.value.is_majority_audio,
            hidden: !stateFolder.value.is_majority_audio,
        },
        {
            title: stateFolder.value.is_majority_audio ? 'Track Number' : `Episode`,
            value: 'episode',
            disabled: false,
        },
        {
            title: stateFolder.value.is_majority_audio ? 'Disc Number' : 'Season',
            value: 'season',
            disabled: false,
        },
        {
            title: 'Duration',
            value: 'duration',
            disabled: false,
        },
        {
            title: 'File Size',
            value: 'file_size',
            disabled: false,
        },
    ];
});

const handleSort = (column: keyof VideoResource = 'date', dir: SortDir = 1) => {
    playlistSort({ column, dir });
};

const handleVideoAction = (e: Event, id: number, action: 'edit' | 'share') => {
    if (!stateFolder.value?.videos) return;

    const video = stateFolder.value.videos.find((video: VideoResource) => video.id === id);
    if (video) cachedVideo.value = video; // idk what this does as removing it does not change functionality

    if (action === 'edit') editVideoModal.toggleModal();
    else shareVideoModal.toggleModal();
};

const setFolderAsPageTitle = () => {
    const title = stateFolder.value?.series?.title ?? stateFolder?.value?.name;
    if (!title) {
        pageTitle.value = 'Folder not Found';
        document.title = 'Folder not Found';
        return;
    }
    pageTitle.value = title;
};

const setVideoAsDocumentTitle = () => {
    const folderTitle = stateFolder.value.series?.title ?? stateFolder.value.name;
    const videoTitle = stateVideo.value?.metadata?.title ?? stateVideo.value?.name;
    if (!folderTitle || !videoTitle) return;
    document.title = `${folderTitle} · ${videoTitle}`;
};

//#endregion

onMounted(async () => {
    reload();

    selectedSideBar.value = '';
});

watch(
    queryVideoId,
    (id) => {
        if (id == null || stateFolder.value.name !== route.params.folder) return;

        if (!playlistFind(id)) return;

        setVideoAsDocumentTitle();
    },
    { immediate: false },
);

watch(
    () => [route.params.category, route.params.folder],
    async () => {
        await reload();
    },
    { immediate: false },
);
watch(() => selectedSideBar.value, cycleSideBar, { immediate: false });
watch(() => stateFolder.value, setFolderAsPageTitle);
watch(() => stateVideo.value, setVideoAsDocumentTitle, { immediate: true });
</script>

<template>
    <LayoutBase>
        <template v-slot:content>
            <section id="content-video" class="flex flex-col gap-3">
                <div id="video-container" class="flex flex-col gap-3">
                    <VideoAmbientPlayer />
                    <VideoInfoPanel />
                </div>

                <TableBase
                    :data="stateFilteredPlaylist"
                    :row="VideoCard"
                    :otherAction="handleVideoAction"
                    :loading="isLoading"
                    :useToolbar="true"
                    :sortAction="handleSort"
                    :sortingOptions="sortingOptions.filter((s) => !s.hidden)"
                    :selectedID="stateVideo?.id"
                    :startAscending="true"
                    v-model="searchQuery"
                />

                <ModalBase :modalData="editVideoModal" :useControls="false">
                    <template #description>
                        <template v-if="cachedVideo && cachedVideo.metadata?.editor_id && cachedVideo.metadata.updated_at">
                            <span>
                                Last edited by
                                <a
                                    title="Editor profile"
                                    target="_blank"
                                    :href="`/profile/${cachedVideo.metadata.editor_id}`"
                                    class="hover:text-purple-600 dark:hover:text-purple-500"
                                >
                                    @{{ cachedVideo.metadata.editor_id }}
                                </a>
                                at
                                {{ toFormattedDate(new Date(cachedVideo.metadata.updated_at)) }}
                            </span>
                        </template>
                        <span class="block w-full">{{ stateVideo.metadata?.uuid }}</span>
                    </template>
                    <template #content>
                        <EditVideo v-if="cachedVideo" :video="cachedVideo" @handleFinish="handleVideoDetailsUpdate" />
                    </template>
                </ModalBase>
                <ModalBase :modalData="shareVideoModal">
                    <template #description> Copy link to clipboard to share it.</template>
                    <template #controls>
                        <CopyToClipboard :text="cachedVideoUrl" />
                    </template>
                </ModalBase>
            </section>
        </template>
        <template v-slot:sidebar>
            <VideoSidebar />
        </template>
    </LayoutBase>
</template>
