<script setup lang="ts">
import type { CategoryResource, FolderResource, VideoResource } from '@/types/resources';
import type { Metadata } from '@/types/model';

import { computed, onMounted, ref, watch, type Ref } from 'vue';
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
import useModal from '@/composables/useModal';
import ModalBase from '@/components/pinesUI/ModalBase.vue';
import EditVideo from '@/components/forms/EditVideo.vue';
import ButtonClipboard from '@/components/pinesUI/ButtonClipboard.vue';
import useMetaData from '@/composables/useMetaData';

const route = useRoute();
const loading = ref(true);

const editVideoModal = useModal({ title: 'Edit Video Details', submitText: 'Submit Details' });
const shareVideoModal = useModal({ title: 'Share Video' });

const cachedVideo = ref<VideoResource>();
const cachedVideoUrl = computed(() => {
    if (!cachedVideo.value) return null;
    return encodeURI(document.location.origin + route.path + `?video=${cachedVideo.value.id}`);
});
const { selectedSideBar } = storeToRefs(useAppStore());
const { getFolder, getCategory, getRecords, playlistFind, playlistSort, updateVideoData } = useContentStore();
const { searchQuery, stateFilteredPlaylist, stateDirectory, stateVideo, stateFolder } = storeToRefs(useContentStore()) as unknown as {
    searchQuery: Ref<string>;
    stateFilteredPlaylist: Ref<VideoResource[]>;
    stateDirectory: Ref<CategoryResource>;
    stateVideo: Ref<VideoResource | { id?: number; metadata?: Metadata; path?: string }>;
    stateFolder: Ref<FolderResource | any>;
};

const handleVideoDetailsUpdate = (res: any) => {
    if (res?.data?.id) updateVideoData(res.data as VideoResource);
    editVideoModal.toggleModal(false);
};

async function cycleSideBar(state: string) {
    if (state === 'history') {
        await getRecords(10);
    }
    if (!state) return;
}

async function reload() {
    const URL_CATEGORY = route.params.category;
    const URL_FOLDER = route.params.folder;

    loading.value = true;

    if (stateDirectory.value?.name && stateDirectory.value.name === URL_CATEGORY && URL_FOLDER) {
        await getFolder(URL_FOLDER);
    } else {
        await getCategory(URL_CATEGORY, URL_FOLDER);
    }
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
        title: 'Date Released',
        value: 'date_released',
        disabled: false,
    },
    {
        title: 'Episode',
        value: 'episode',
        disabled: false,
    },
    {
        title: 'Season',
        value: 'season',
        disabled: false,
    },
]);

const handleSort = (column = 'date', dir = 1) => {
    playlistSort({ column, dir });
};

const handleSearch = (query: string) => {
    searchQuery.value = query;
};

const handleVideoAction = (e: Event, id: number, action: 'edit' | 'share') => {
    let video = stateFolder.value?.videos.find((video: VideoResource) => video.id === id);
    if (video) cachedVideo.value = video;

    if (action === 'edit') editVideoModal.toggleModal();
    else shareVideoModal.toggleModal();
};

//#endregion

onMounted(async () => {
    reload();

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
                    :otherAction="handleVideoAction"
                    :loading="loading"
                    :useToolbar="true"
                    :sortAction="handleSort"
                    :sortingOptions="sortingOptions"
                    :selectedID="stateVideo?.id"
                    :startAscending="true"
                    @search="handleSearch"
                />

                <ModalBase :modalData="editVideoModal" :useControls="false">
                    <template #content>
                        <div class="pt-2">
                            <EditVideo :video="cachedVideo" @handleFinish="handleVideoDetailsUpdate" />
                        </div>
                    </template>
                </ModalBase>
                <ModalBase :modalData="shareVideoModal">
                    <template #content>
                        <div class="py-3">Copy link to clipboard to share it.</div>
                    </template>
                    <template #controls>
                        <ButtonClipboard :text="cachedVideoUrl" tabindex="1" />
                    </template>
                </ModalBase>
            </section>
        </template>
        <template v-slot:sidebar>
            <VideoSidebar />
            <table class="medialist table">
                <div class="entry row">
                    <div class="cover">
                        <div class="edit"></div>
                        <div class="image" style="background-image: url('https://s4.anilist.co/file/anilistcdn/media/anime/cover/medium/bx128547-TWRVIu5zRTYx.jpg')"></div>
                    </div>
                    <div class="title"></div>
                    <div score="9.4" label="Score" class="score">
                        9.4
                        <!---->
                    </div>
                    <div label="Progress" class="progress">
                        13
                        <!---->
                    </div>
                    <!---->
                    <div label="Status" class="status">Completed</div>
                    <div label="Format" class="format">TV</div>
                    <span class="release-status FINISHED"></span>
                </div>
            </table>
        </template>
    </LayoutBase>
</template>
<style lang="css" scoped>
.medialist.table .entry:hover {
    background: rgb(50, 50, 50);
    color: rgb(255, 244, 233);
}
.medialist.table .row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.medialist.table .entry {
    position: relative;
    transition: 0.15s;
}

.medialist.table .entry .cover {
    align-items: center;
    display: flex;
    flex: 1;
    justify-content: flex-end;
    max-width: 60px;
    min-width: 60px;
    padding: 0;
}

.medialist.table .entry .title {
    flex: 5;
    padding-left: 15px;
    text-align: left;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    word-break: break-word;
}

.medialist.table .entry > div {
    flex: 1;
    padding: 18px 20px;
    text-align: center;
}

.medialist.table .entry .cover .image {
    background-position: 50%;
    background-repeat: no-repeat;
    background-size: cover;
    border-radius: 3px;
    height: 40px;
    width: 40px;
}

.medialist.table .entry:hover .cover .image {
    border-radius: 3px;
    box-shadow: 0 2px 20px rgba(49, 54, 68, 0.3);
    display: block;
    height: 200px;
    left: -160px;
    padding: 0;
    position: absolute;
    top: -60px;
    width: 140px;
    z-index: 1;
}
</style>
