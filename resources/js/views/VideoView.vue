<script setup>
import VideoSidebar from '../components/panels/VideoSidebar.vue';
import LayoutBase from '../components/layout/LayoutBase.vue';
import VideoPlayer from '../components/video/VideoPlayer.vue';
import VideoTable from '../components/video/VideoTable.vue';

import CircumShare1 from '~icons/circum/share-1';

import { onMounted, watch } from 'vue';
import { useContentStore } from '../stores/ContentStore';
import { useAppStore } from '../stores/AppStore';
import { storeToRefs } from 'pinia';
import { useRoute } from 'vue-router'


const route = useRoute();
const appStore = useAppStore();
const ContentStore = useContentStore();
const { selectedSideBar } = storeToRefs(appStore);
const { stateVideo } = storeToRefs(ContentStore);
const { getFolder, getCategory, getRecords } = ContentStore;


async function cycleSideBar(state) {
    if (state === "history") {
        getRecords(10);
    }
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

                    <div
                        class="p-6 w-full mx-auto dark:bg-primary-dark-800 bg-primary-800 rounded-xl shadow-lg flex justify-center sm:justify-between gap-4 flex-wrap sm:flex-nowrap overflow-hidden">
                        <div id="mp4-description" class="hidden sm:flex items-center gap-4 md:w-2/3 ">
                            <img id="folder-thumbnail" class="h-28 object-contain rounded-md shadow-md"
                                :src="stateVideo?.attributes?.thumbnail?.url ?? 'https://m.media-amazon.com/images/M/MV5BMjVjZGU5ZTktYTZiNC00N2Q1LThiZjMtMDVmZDljN2I3ZWIwXkEyXkFqcGdeQXVyMTUzMTg2ODkz._V1_.jpg'"
                                alt="Folder Cover Art">
                            <div class="h-full flex flex-col gap-2">
                                <div id="mp4-title" class="text-xl font-medium line">{{ stateVideo?.attributes?.name ?? '[Video Name]'}}</div>
                                <p class="dark:text-slate-400 text-slate-500 line-clamp-3 text-sm">After defeating the
                                    Demon Lord, Himmel the Hero, priest Heiter, dwarf warrior Eisen, and elf mage
                                    Frieren return to the royal capital. After their procession, they view a meteor
                                    shower and discuss their future plans. Himmel, Heiter, and Eisen are ready to retire
                                    from adventuring after their ten-year quest. Frieren, whose lifespan is much longer,
                                    considers the ten years to be trivially short and plans to travel and learn new
                                    spells. To her colleagues' amusement, she promises to show them a better site to
                                    observe the meteor shower at its next occurrence in 50 years. Frieren keeps her word
                                    and returns 50 years later to find that Himmel and Heiter have become elderly, and
                                    Eisen is middle-aged. The week-long journey to Frieren's viewing site reminds the
                                    party of their past adventures. Himmel the Hero dies of old age shortly after the
                                    expedition. At his funeral, Frieren tearfully realizes she did not adequately get to
                                    know him and decides to learn as much about humans as possible. 20 years later, she
                                    visits Heiter to find that he has adopted a war orphan, nine year old Fern. Heiter,
                                    suffering from death anxiety in his advanced age, asks Frieren to research
                                    life-extending magic and tutor Fern in magic in her spare time. She agrees after
                                    seeing Fern is already remarkably skilled despite her youth.</p>
                            </div>
                        </div>
                        <div id="mp4-description-mobile" class="flex sm:hidden items-center gap-4 flex-col ">
                            <div id="mp4-title" class="text-xl font-medium w-full">{{ stateVideo?.attributes?.name ?? '[Video Name]'}}</div>
                            <div class="flex items-start gap-4 md:w-2/3">
                                <img id="folder-thumbnail" class="h-28 object-contain rounded-md shadow-sm"
                                    :src="stateVideo?.attributes?.thumbnail?.url ?? 'https://m.media-amazon.com/images/M/MV5BMjVjZGU5ZTktYTZiNC00N2Q1LThiZjMtMDVmZDljN2I3ZWIwXkEyXkFqcGdeQXVyMTUzMTg2ODkz._V1_.jpg'"
                                    alt="Folder Cover Art">
                                <p class="dark:text-slate-400 text-slate-500 line-clamp-3 text-sm">After defeating the
                                    Demon Lord, Himmel the Hero, priest Heiter, dwarf warrior Eisen, and elf mage
                                    Frieren return to the royal capital. After their procession, they view a meteor
                                    shower and discuss their future plans. Himmel, Heiter, and Eisen are ready to retire
                                    from adventuring after their ten-year quest. Frieren, whose lifespan is much longer,
                                    considers the ten years to be trivially short and plans to travel and learn new
                                    spells. To her colleagues' amusement, she promises to show them a better site to
                                    observe the meteor shower at its next occurrence in 50 years. Frieren keeps her word
                                    and returns 50 years later to find that Himmel and Heiter have become elderly, and
                                    Eisen is middle-aged. The week-long journey to Frieren's viewing site reminds the
                                    party of their past adventures. Himmel the Hero dies of old age shortly after the
                                    expedition. At his funeral, Frieren tearfully realizes she did not adequately get to
                                    know him and decides to learn as much about humans as possible. 20 years later, she
                                    visits Heiter to find that he has adopted a war orphan, nine year old Fern. Heiter,
                                    suffering from death anxiety in his advanced age, asks Frieren to research
                                    life-extending magic and tutor Fern in magic in her spare time. She agrees after
                                    seeing Fern is already remarkably skilled despite her youth.</p>
                            </div>
                        </div>
                        <div id="mp4-details"
                            class="container flex sm:w-auto sm:flex-col justify-between lg:min-w-32 items-center sm:items-end gap-3 flex-wrap"
                            role="group">
                            <section class="flex gap-2">
                                <button aria-label="edit details"
                                    class="p-2 bg-button-100 dark:bg-button-900 rounded-lg ring-violet-500 hover:ring-violet-700 hover:bg-violet-400/50 ring-[0.125rem] ring-inset shadow">Edit
                                    Details</button>
                                <button aria-label="share"
                                    class="p-2 bg-button-100 dark:bg-button-900 rounded-lg ring-neutral-700 hover:ring-violet-700 hover:bg-violet-400/50 dark:ring-[0.125rem] hover:ring-[0.125rem] ring-inset shadow">
                                    <CircumShare1 height="24" width="24"/>
                                </button>
                            </section>
                            <section
                                class="flex gap flex-col items-end text-sm dark:text-slate-400 text-slate-500 justify-between">
                                <p>{{ stateVideo?.views ?? 20 }} views</p>
                                <p class="line-clamp-1 truncate">{{ stateVideo?.tags ?? '#atmospheroc #sad #rocky' }}</p>
                            </section>
                        </div>
                    </div>
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