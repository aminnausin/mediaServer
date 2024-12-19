<script setup lang="ts">
import type { FolderResource, VideoResource } from '@/types/resources';
import type { Metadata } from '@/types/model';

import { nextTick, onMounted, ref, watch, type Ref } from 'vue';
import { useContentStore } from '@/stores/ContentStore';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { useRoute } from 'vue-router';

import VideoAmbientPlayer from '@/components/video/VideoAmbientPlayer.vue';
import VideoInfoPanel from '@/components/video/VideoInfoPanel.vue';
import VideoSidebar from '@/components/panels/VideoSidebar.vue';
import LayoutBase from '@/layouts/LayoutBase.vue';
import VideoCard from '@/components/cards/VideoCard.vue';
import TableBase from '@/components/table/TableBase.vue';

const ContentStore = useContentStore();
const appStore = useAppStore();
const route = useRoute();
const loading = ref(true);

const { getFolder, getCategory, getRecords, playlistFind, playlistSort } = ContentStore;
const { searchQuery, stateFilteredPlaylist, stateVideo, stateFolder } = storeToRefs(ContentStore) as {
    searchQuery: Ref<string>;
    stateFilteredPlaylist: Ref<VideoResource[]>;
    stateVideo: Ref<VideoResource | { id?: number; metadata?: Metadata; path?: string }>;
    stateFolder: Ref<FolderResource | any>;
};
const { selectedSideBar } = storeToRefs(appStore);

async function cycleSideBar(state: string) {
    if (state === 'history') {
        await getRecords(10);
    }
    if (!state) return;

    await nextTick();
    document.querySelector('#list-card')?.scrollIntoView({ behavior: 'smooth' });
}

async function reload(nextFolderName: string | string[]) {
    loading.value = true;
    await getFolder(nextFolderName.toString());
    loading.value = false;
}

//#region TABLE

const sortingOptions = ref([
    {
        title: 'Title',
        value: 'title',
        disabled: false,
    },
    {
        title: 'Duration',
        value: 'duration',
        disabled: false,
    },
    {
        title: 'Views',
        value: 'view_count',
        disabled: false,
    },
    {
        title: 'Date Uploaded',
        value: 'date',
        disabled: false,
    },
    {
        title: 'Date released',
        value: 'date_released',
        disabled: true,
    },
    {
        title: 'Episode',
        value: 'episode',
        disabled: true,
    },
    {
        title: 'Season',
        value: 'season',
        disabled: true,
    },
]);

const handleSort = (column = 'date', dir = 1) => {
    playlistSort({ column, dir });
};

const handleSearch = (query: string) => {
    searchQuery.value = query;
};

//#endregion

onMounted(async () => {
    const URL_CATEGORY = route.params.category;
    const URL_FOLDER = route.params.folder;

    await getCategory(URL_CATEGORY, URL_FOLDER);
    loading.value = false;

    selectedSideBar.value = '';
});

watch(
    () => route.query.video,
    (newVideo) => {
        if (stateFolder.value.name === route.params.folder) {
            playlistFind(newVideo);
        }
    },
    { immediate: false },
);
watch(() => route.params.folder, reload, { immediate: false });
watch(() => selectedSideBar.value, cycleSideBar, { immediate: false });
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
                    :clickAction="playlistFind"
                    :loading="loading"
                    :useToolbar="true"
                    :sortAction="handleSort"
                    :sortingOptions="sortingOptions"
                    :selectedID="stateVideo?.id"
                    @search="handleSearch"
                />
            </section>
        </template>
        <template v-slot:sidebar>
            <VideoSidebar />
        </template>
    </LayoutBase>
</template>
