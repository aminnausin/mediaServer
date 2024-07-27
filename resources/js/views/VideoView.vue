<script setup>
    import VideoSidebar from '../components/panels/VideoSidebar.vue';
    import LayoutBase from '../components/layout/LayoutBase.vue';
    import VideoPlayer from '../components/VideoPlayer.vue';

    import { ref, onMounted, watch } from 'vue';
    import { useContentStore } from '../stores/ContentStore';
    import { useAppStore } from '../stores/AppStore';
    import { storeToRefs } from 'pinia';
    import { useRoute } from 'vue-router'
    import VideoTable from '../components/VideoTable.vue';

    
    const route = useRoute();
    const appStore = useAppStore();
    const ContentStore = useContentStore();
    const { selectedSideBar } = storeToRefs(appStore);
    const { stateVideo} = storeToRefs(ContentStore);
    const { getFolder, getCategory, getRecords, playlistSeek } = ContentStore;

    async function cycleSideBar(state){
        if(state === "history"){
            getRecords(10);
        }
    }

    async function reload(nextFolderName){
        await getFolder(nextFolderName);
    }

    onMounted(async () => {
        const URL_CATEGORY = route.params.category;
        const URL_FOLDER = route.params.folder;

        getCategory(URL_CATEGORY, URL_FOLDER);
    })

    watch(() => route.params.folder, reload, {immediate: false});
    watch(() => selectedSideBar.value, cycleSideBar, {immediate: false});
</script>

<template>
    <LayoutBase>
        <template v-slot:content>
            <section id="content-video" class="flex flex-col gap-3">
                <div id="video-container" class="flex flex-col gap-3">
                    <VideoPlayer/>

                    <!-- <hr class=""> -->

                    <div class="p-6 w-full mx-auto dark:bg-primary-dark-800 bg-primary-800 rounded-xl shadow-lg flex justify-center sm:justify-between gap-4 flex-wrap sm:flex-nowrap overflow-hidden">
                        <div id="mp4-description" class="hidden sm:flex items-center gap-4 md:w-2/3 ">
                            <div class="shrink-0">
                                <img id="folder-thumbnail" class="h-28 object-contain rounded-md" src="https://app.test:8080/storage/thumbnails/folders/5.jpg" onerror='this.onerror=null;this.src="https\:\/\/app.test:8080/storage/thumbnails/folders/5.jpg";' alt="Folder Cover Art">
                            </div>
                            <div class="h-full flex flex-col gap-2">
                                <div id="mp4-title" class="text-xl font-medium">{{ stateVideo?.attributes ? stateVideo.attributes.name : ''}}</div>
                                <p class="dark:text-slate-400 text-slate-400 line-clamp-3 text-sm">After defeating the Demon Lord, Himmel the Hero, priest Heiter, dwarf warrior Eisen, and elf mage Frieren return to the royal capital. After their procession, they view a meteor shower and discuss their future plans. Himmel, Heiter, and Eisen are ready to retire from adventuring after their ten-year quest. Frieren, whose lifespan is much longer, considers the ten years to be trivially short and plans to travel and learn new spells. To her colleagues' amusement, she promises to show them a better site to observe the meteor shower at its next occurrence in 50 years. Frieren keeps her word and returns 50 years later to find that Himmel and Heiter have become elderly, and Eisen is middle-aged. The week-long journey to Frieren's viewing site reminds the party of their past adventures. Himmel the Hero dies of old age shortly after the expedition. At his funeral, Frieren tearfully realizes she did not adequately get to know him and decides to learn as much about humans as possible. 20 years later, she visits Heiter to find that he has adopted a war orphan, nine year old Fern. Heiter, suffering from death anxiety in his advanced age, asks Frieren to research life-extending magic and tutor Fern in magic in her spare time. She agrees after seeing Fern is already remarkably skilled despite her youth.</p>
                            </div>
                        </div>
                        <div id="mp4-description-mobile" class="flex sm:hidden items-center gap-4 flex-col ">
                            <div id="mp4-title" class="text-xl font-medium w-full">{{ stateVideo?.attributes ? stateVideo.attributes.name : ''}}</div>
                            <div class="flex items-center gap-4 md:w-2/3">
                                <img id="folder-thumbnail" class="h-28 object-contain rounded-md" src="https://app.test:8080/storage/thumbnails/folders/5.jpg" onerror='this.onerror=null;this.src="https\:\/\/app.test:8080/storage/thumbnails/folders/5.jpg";' alt="Folder Cover Art">
                                <div class="h-full flex flex-col gap-2">
                                    <p class="dark:text-slate-400 text-slate-400 line-clamp-3 text-sm">After defeating the Demon Lord, Himmel the Hero, priest Heiter, dwarf warrior Eisen, and elf mage Frieren return to the royal capital. After their procession, they view a meteor shower and discuss their future plans. Himmel, Heiter, and Eisen are ready to retire from adventuring after their ten-year quest. Frieren, whose lifespan is much longer, considers the ten years to be trivially short and plans to travel and learn new spells. To her colleagues' amusement, she promises to show them a better site to observe the meteor shower at its next occurrence in 50 years. Frieren keeps her word and returns 50 years later to find that Himmel and Heiter have become elderly, and Eisen is middle-aged. The week-long journey to Frieren's viewing site reminds the party of their past adventures. Himmel the Hero dies of old age shortly after the expedition. At his funeral, Frieren tearfully realizes she did not adequately get to know him and decides to learn as much about humans as possible. 20 years later, she visits Heiter to find that he has adopted a war orphan, nine year old Fern. Heiter, suffering from death anxiety in his advanced age, asks Frieren to research life-extending magic and tutor Fern in magic in her spare time. She agrees after seeing Fern is already remarkably skilled despite her youth.</p>
                                </div>
                            </div>
                            
                        </div>
                        <div id="mp4-details" class="container flex sm:w-auto sm:flex-col justify-between sm:min-w-64 items-center sm:items-end gap-3 flex-wrap" role="group">
                            <section class="flex gap-2">
                                <button class="p-2 bg-button-100 dark:bg-button-900 rounded-lg ring-indigo-500 hover:ring-indigo-800 hover:bg-button-200 dark:hover:bg-indigo-400 hover:text-neutral-950 ring-[0.125rem] ring-inset">Edit Details</button>
                                <button class="p-2 bg-button-100 dark:bg-button-900 rounded-lg hover:ring-indigo-500 hover:ring-[0.125rem] ring-inset">s</button>
                            </section>
                            <section class="hidden">
                                <button type="button" class="bg-button-100 dark:bg-button-900 dark:text-white rounded-l-md border-r ring border-indigo-300 py-2 hover:bg-red-700 hover:text-white px-3 shadow-xl" @click="playlistSeek(-1)">
                                    <div class="flex flex-row align-middle">
                                        <svg class="w-5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="https://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M7.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                        <p class="ml-2">Prev</p>
                                    </div>
                                </button>
                                <button type="button" class="bg-button-100 dark:bg-button-900 dark:text-white rounded-r-md py-2 border-l ring border-indigo-300 hover:bg-red-700 hover:text-white px-3 shadow-xl" @click="playlistSeek(1)">
                                    <div class="flex flex-row align-middle">
                                        <span class="mr-2">Next</span>
                                        <svg class="w-5 ml-2" fill="currentColor" viewBox="0 0 20 20" xmlns="https://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </button>
                            </section>
                            <section class="flex gap flex-col items-end text-sm text-slate-400 justify-between">
                                <p>20 views</p>
                                <p class="line-clamp-1 truncate">#atmospheroc #sad #rocky</p>
                            </section> 
                        </div>
                    </div>
                </div>

                <!-- <hr id='preData'> -->

                <VideoTable/>
            </section>
        </template>
        <template v-slot:sidebar>
            <VideoSidebar/>
        </template>
    </LayoutBase>
</template>