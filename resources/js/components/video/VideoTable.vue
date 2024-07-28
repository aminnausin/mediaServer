<script setup>
import LabelledTextInput from '../inputs/LabelledTextInput.vue';
import TablePagination from '../table/TablePagination.vue';
import VideoCard from '../cards/VideoCard.vue';

import { useContentStore } from '../../stores/ContentStore';
import { storeToRefs } from 'pinia';
import { computed, ref } from 'vue';


const ContentStore = useContentStore();
const { searchQuery, stateFilteredPlaylist, stateVideo } = storeToRefs(ContentStore);
const { playlistFind, playlistSort } = ContentStore;

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
    [{ name: 'Title', colspan: '2', minWidth: 'lg:min-w-48', colName: 'name'  },
    { name: 'Length', colspan: '1', minWidth: 'xl:min-w-6', colName: 'name'  }],
    [{ name: 'Views', colspan: '1', minWidth: 'xl:min-w-6', colName: 'date'  },
    { name: 'Date', colspan: '2', minWidth: 'xl:min-w-40', colName: 'date' }],
];
</script>

<template>
    <table class="w-full flex flex-col gap-2">
        <section class="flex justify-center sm:justify-between p-2">
            <!-- <h2 class="text-2xl py-4">Episodes</h2> -->
            <LabelledTextInput v-model="searchQuery" :text="'Search'" :placeholder="'Enter search query'" :id="'table-search'" class="w-80"/>
        </section>
        <hr>
        <thead class="w-full flex-col hidden sm:flex">
            <tr class="w-full flex justify-between gap-12 text-left" >
                <th class="px-3 flex gap-12 w-full items-center">
                    <span v-for="(column, idx) in tableColumnGroups[0]" :key="idx" :class="column.minWidth" @click="playlistSort(column.colName, 0)">{{column.name }}</span>
                </th>
                <th class="px-3 flex gap-12 w-full justify-end text-right items-center">
                    <span v-for="(column, idx) in tableColumnGroups[1]" :key="idx" :class="column.minWidth" @click="playlistSort(column.colName, 0)">{{ column.name }}</span>
                </th>
            </tr>
        </thead>
        <tbody
            class="flex w-full flex-wrap gap-2">
            <VideoCard v-for="(video, index) in filteredPage" :key="index" :video="video" :index="index"
                :currentID="stateVideo?.id" @playByID="playlistFind" />
        </tbody>
        <hr>
        <TablePagination :listLength="stateFilteredPlaylist?.length ?? 0" :itemsPerPage="itemsPerPage" :currentPage="currentPage" @setPage="handlePageChange"/>
    </table>
</template>