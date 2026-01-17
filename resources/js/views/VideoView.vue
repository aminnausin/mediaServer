<script setup lang="ts">
import type { VideoResource } from '@/types/resources';
import type { SortDir } from '@/service/sort/types';

import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { useContentStore } from '@/stores/ContentStore';
import { useModalStore } from '@/stores/ModalStore';
import { toParamNumber } from '@/util/route';
import { queryClient } from '@/service/vue-query';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { TableBase } from '@/components/cedar-ui/table';
import { MediaType } from '@/types/types';
import { useRoute } from 'vue-router';
import { toast } from '@aminnausin/cedar-ui';

import VideoAmbientPlayer from '@/components/video/VideoAmbientPlayer.vue';
import EditMediaModal from '@/components/modals/EditMediaModal.vue';
import VideoInfoPanel from '@/components/video/VideoInfoPanel.vue';
import VideoSidebar from '@/components/panels/VideoSidebar.vue';
import LayoutBase from '@/layouts/LayoutBase.vue';
import ShareModal from '@/components/modals/ShareModal.vue';
import VideoCard from '@/components/cards/data/VideoCard.vue';

const { selectedSideBar, pageTitle } = storeToRefs(useAppStore());
const { getFolder, getCategory, playlistFind, playlistSort, updateVideoData } = useContentStore();
const { searchQuery, stateFilteredPlaylist, stateDirectory, stateVideo, stateFolder } = storeToRefs(useContentStore());

const isLoading = ref(false);

const modal = useModalStore();
const route = useRoute();

const mediaTypeDescription = computed(() => (stateVideo.value?.metadata?.media_type === MediaType.AUDIO || stateFolder.value?.is_majority_audio ? 'Track' : 'Video'));
const queryVideoId = computed(() => toParamNumber(route.query.video));

async function cycleSideBar(state: string) {
    // Invalidate query everytime sidebar is opened
    if (state === 'history') {
        await queryClient.invalidateQueries({
            queryKey: ['records', 'limited'],
        });
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

const handleVideoAction = (e: Event, id: number, action: 'edit' | 'share' | 'download') => {
    if (!stateFolder.value?.videos) return;

    const mediaResource = stateFolder.value.videos.find((video: VideoResource) => video.id === id);

    if (!mediaResource) {
        toast.error('File not found');
        return;
    }

    switch (action) {
        case 'edit': {
            const metadataInfo = mediaResource.metadata ? { titleTooltip: `UUID: ${mediaResource.metadata.uuid}` } : {};

            modal.open(EditMediaModal, { title: `Edit ${mediaTypeDescription.value} Metadata`, mediaResource: mediaResource, ...metadataInfo });
            break;
        }
        case 'share':
            modal.open(ShareModal, { title: `Share ${mediaTypeDescription.value}`, shareLink: encodeURI(document.location.origin + route.path + `?video=${mediaResource.id}`) });
            break;
        default:
            toast.error('Option Unavailable', { description: `You cannot ${action} this file.` });
            break;
    }
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
            </section>
        </template>
        <template v-slot:sidebar>
            <VideoSidebar />
        </template>
    </LayoutBase>
</template>
