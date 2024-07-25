<script setup>
import VideoCard from './VideoCard.vue';
import LabelledTextInput from './inputs/LabelledTextInput.vue';

import { useContentStore } from '../stores/ContentStore';
import { storeToRefs } from 'pinia';
import { ref } from 'vue';


const ContentStore = useContentStore();
const { searchQuery, stateFilteredPlaylist, stateFolder, stateVideo } = storeToRefs(ContentStore);
const { playlistFind } = ContentStore;
const videosPerPage = ref(10);
const currentPage = ref(1);

const handlePageChange = (page) => {
    currentPage.value = page;
}

const tableColumnGroups = [
    [{ name: 'Title', colspan: '2', minWidth: 'min-w-48' },
    { name: 'Length', colspan: '1', minWidth: 'min-w-6' }],
    [{ name: 'Views', colspan: '1', minWidth: 'min-w-6' },
    { name: 'Date', colspan: '2', minWidth: 'min-w-40' }],
];
</script>

<template>
    <table class="w-full overflow-x-hidden flex flex-col gap-2">
        <section class="folder-header flex justify-center sm:justify-start p-2">
            <!-- <h2 class="text-2xl py-4">Episodes</h2> -->
            <LabelledTextInput v-model="searchQuery" :text="'Search'" class="w-80" />
        </section>
        <hr>
        <thead class="w-full flex">
            <tr class="w-full flex justify-between gap-12 text-left">
                <th class="px-3 flex gap-12 w-full items-center">
                    <span v-for="(column, idx) in tableColumnGroups[0]" :key="idx" :class="column.minWidth">{{
                        column.name }}</span>
                </th>
                <th class="px-3 flex gap-12 w-full justify-end items-center">
                    <span v-for="(column, idx) in tableColumnGroups[1]" :key="idx" :class="column.minWidth">{{
                        column.name }}</span>
                </th>
                <!-- <th v-for="(column, index) in tableColumns" :key="index" :class="column.minWidth" class="px-3 flex items-center justify-start">{{ column.name }}</th> -->
            </tr>
        </thead>
        <tbody
            class="flex w-full flex-wrap gap-2 [&>*:nth-child(odd)]:bg-violet-200 hover:[&>*:nth-child(odd)]:bg-violet-300 dark:[&>*:nth-child(odd)]:bg-zinc-700 dark:hover:[&>*:nth-child(odd)]:bg-indigo-900">
            <!-- <VideoCard v-for="(video, index) in stateFilteredPlaylist.slice(videosPerPage * currentPage - videosPerPage, Math.min(videosPerPage * currentPage, stateFilteredPlaylist.length))" :key="index" :video="video" :index="index" -->
                <!-- :currentID="stateVideo.id" @playByID="playlistFind" /> -->
            <VideoCard v-for="(video, index) in stateFilteredPlaylist" :key="index" :video="video" :index="index"
                :currentID="stateVideo.id" @playByID="playlistFind" />
        </tbody>
        <hr>
        <section class="flex w-full justify-between">
            <p>{{ `Showing ${stateFilteredPlaylist.length} of ${stateFolder.videos.length}
                Result${stateFolder.videos.length > 1 ? 's' : ''} ${searchQuery ? '(Filtered)' : ''}` }}</p>
            <!-- <p>{{ `Showing ${stateFilteredPlaylist.slice(videosPerPage * currentPage - videosPerPage, Math.min(videosPerPage * currentPage, stateFilteredPlaylist.length)).length} of ${stateFolder.videos.length} -->
                <!-- Result${stateFolder.videos.length > 1 ? 's' : ''} ${searchQuery ? '(Filtered)' : ''}`}}</p> -->
            <div class="flex gap-2">
                (Disabled)
                <button v-for="index in Math.ceil(stateFilteredPlaylist?.length / videosPerPage)" :key="index"
                    class="py-2 px-auto aspect-square bg-button-100 dark:bg-button-900 rounded-lg hover:ring-indigo-500 hover:ring-[0.125rem] ring-inset"
                    @click="handlePageChange(index)">
                    {{ index }}
                </button>
            </div>
        </section>
    </table>
</template>