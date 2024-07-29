<script setup>
import VideoSidebar from '../components/panels/VideoSidebar.vue';
import LayoutBase from '../components/layout/LayoutBase.vue';
import VideoPlayer from '../components/video/VideoPlayer.vue';
import VideoTable from '../components/video/VideoTable.vue';
import VideoInfoPanel from '../components/video/VideoInfoPanel.vue';

import { nextTick, onMounted, watch } from 'vue';
import { useContentStore } from '../stores/ContentStore';
import { useAppStore } from '../stores/AppStore';
import { storeToRefs } from 'pinia';
import { useRoute } from 'vue-router'


const route = useRoute();
const appStore = useAppStore();
const ContentStore = useContentStore();
const { selectedSideBar } = storeToRefs(appStore);
const { getFolder, getCategory, getRecords } = ContentStore;

async function cycleSideBar(state) {
    if (state === "history") {
        await getRecords(10);
    }
    await nextTick();
    document.querySelector('#list-card').scrollIntoView({behavior: "smooth"});
}

async function reload(nextFolderName) {
    await getFolder(nextFolderName);
}

onMounted(async () => {
    const URL_CATEGORY = route.params.category;
    const URL_FOLDER = route.params.folder;

    getCategory(URL_CATEGORY, URL_FOLDER);
})

watch(() => route.params.folder, reload, { immediate: false });
watch(() => selectedSideBar.value, cycleSideBar, { immediate: false });
</script>

<template>
    <LayoutBase>
        <template v-slot:content>
            <section id="content-video" class="flex flex-col gap-3">
                <div id="video-container" class="flex flex-col gap-3">
                    <VideoPlayer />

                    <!-- <hr class=""> -->

                    <VideoInfoPanel />
                </div>

                <!-- <hr id='preData'> -->

                <VideoTable />
            </section>
        </template>
        <template v-slot:sidebar>
            <VideoSidebar />
        </template>
    </LayoutBase>
</template>