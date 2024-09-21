<script setup>
import VideoAmbientPlayer from '../components/video/VideoAmbientPlayer.vue';
import VideoInfoPanel from '../components/video/VideoInfoPanel.vue';
import VideoSidebar from '../components/panels/VideoSidebar.vue';
import LayoutBase from '../layouts/LayoutBase.vue';
import VideoCard from '../components/cards/VideoCard.vue';
import TableBase from '../components/table/TableBase.vue';

import { nextTick, onMounted, ref, watch } from 'vue';
import { useContentStore } from '../stores/ContentStore';
import { useAppStore } from '../stores/AppStore';
import { storeToRefs } from 'pinia';
import { useRoute } from 'vue-router';

const route = useRoute();
const loading = ref(true);
const appStore = useAppStore();
const ContentStore = useContentStore();
const { selectedSideBar } = storeToRefs(appStore);
const { searchQuery, stateFilteredPlaylist, stateVideo } = storeToRefs(ContentStore);
const { getFolder, getCategory, getRecords, playlistFind, playlistSort } = ContentStore;

async function cycleSideBar(state) {
    if (state === 'history') {
        await getRecords(10);
    }
    if (!state) return;

    await nextTick();
    document.querySelector('#list-card').scrollIntoView({ behavior: 'smooth' });
}

async function reload(nextFolderName) {
    loading.value = true;
    await getFolder(nextFolderName);
    loading.value = false;
}

// Table

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
    playlistSort(column, dir);
};

const handleSearch = (query) => {
    searchQuery.value = query;
};

onMounted(async () => {
    const URL_CATEGORY = route.params.category;
    const URL_FOLDER = route.params.folder;

    await getCategory(URL_CATEGORY, URL_FOLDER);
    loading.value = false;
});

watch(() => route.params.folder, reload, { immediate: false });
watch(() => selectedSideBar.value, cycleSideBar, { immediate: false });
</script>

<template>
    <LayoutBase>
        <template v-slot:content>
            <section id="content-video" class="flex flex-col gap-3">
                <div id="video-container" class="flex flex-col gap-3">
                    <VideoAmbientPlayer />

                    <!-- <hr class=""> -->

                    <VideoInfoPanel />
                </div>

                <!-- <hr id='preData'> -->

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
