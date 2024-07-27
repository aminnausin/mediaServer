<script setup>
import VideoCard from '../cards/VideoCard.vue';
import LabelledTextInput from '../inputs/LabelledTextInput.vue';

import { useContentStore } from '../../stores/ContentStore';
import { storeToRefs } from 'pinia';
import { computed, ref } from 'vue';
import TablePagination from '../table/TablePagination.vue';


const ContentStore = useContentStore();
const { searchQuery, stateFilteredPlaylist, stateVideo } = storeToRefs(ContentStore);
const { playlistFind } = ContentStore;

const currentPage = ref(1);
const itemsPerPage = ref(10);
const filteredPage = computed(() => {
    const minIndex = itemsPerPage.value * (currentPage.value - 1);
    const maxIndex = Math.min(itemsPerPage.value * (currentPage.value), stateFilteredPlaylist.value.length);
    
    return stateFilteredPlaylist.value.slice(minIndex, maxIndex);
});

const handlePageChange = (page) => {
    currentPage.value = page;
}

const tableColumnGroups = [
    [{ name: 'Title', colspan: '2', minWidth: 'lg:min-w-48' },
    { name: 'Length', colspan: '1', minWidth: 'xl:min-w-6' }],
    [{ name: 'Views', colspan: '1', minWidth: 'xl:min-w-6' },
    { name: 'Date', colspan: '2', minWidth: 'xl:min-w-40' }],
];
</script>

<template>
    <table class="w-full flex flex-col gap-2">
        <section class="folder-header flex justify-center sm:justify-between p-2">
            <!-- <h2 class="text-2xl py-4">Episodes</h2> -->
            <LabelledTextInput v-model="searchQuery" :text="'Search'" class="w-80"/>
        </section>
        <hr>
        <thead class="w-full flex-col hidden sm:flex">
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
            class="flex w-full flex-wrap gap-2">
            <VideoCard v-for="(video, index) in filteredPage" :key="index" :video="video" :index="index"
                :currentID="stateVideo?.id" @playByID="playlistFind" />
        </tbody>
        <hr >
        <!-- <section class="w-full justify-between flex">
            <div class="">
                <p>{{ `Showing ${stateFilteredPlaylist.length} of ${stateFolder.videos.length}
                    Result${stateFolder.videos.length > 1 ? 's' : ''} ${searchQuery ? '(Filtered)' : ''}` }}</p>
                <p>{{ `Showing ${stateFilteredPlaylist.slice(videosPerPage * currentPage - videosPerPage, Math.min(videosPerPage * currentPage, stateFilteredPlaylist.length)).length} of ${stateFolder.videos.length} 
                    Result${stateFolder.videos.length > 1 ? 's' : ''} ${searchQuery ? '(Filtered)' : ''}`}}</p>
            </div>
            <div class="gap-2 items-center">
                <button v-for="index in Math.ceil(stateFilteredPlaylist?.length / videosPerPage)" :key="index"
                    class="aspect-square bg-button-100 dark:bg-button-900 rounded-lg hover:ring-indigo-500 hover:ring-[0.125rem] ring-inset"
                    @click="handlePageChange(index)">
                    {{ index }}
                </button>
            </div>
        </section> -->
        <TablePagination :listLength="stateFilteredPlaylist?.length ?? 0" :itemsPerPage="itemsPerPage" :currentPage="currentPage" @setPage="handlePageChange"/>
    </table>
</template>