<script setup>
import LabelledTextInput from '../inputs/LabelledTextInput.vue';
import FormInputLabel from '../labels/FormInputLabel.vue';
import TablePagination from '../table/TablePagination.vue';
import InputSelect from '../pinesUI/InputSelect.vue';
import VideoCard from '../cards/VideoCard.vue';

import PhSortAscendingLight from '~icons/ph/sort-ascending-light';
import PhSortDescendingLight from '~icons/ph/sort-descending-light';

import { useContentStore } from '../../stores/ContentStore';
import { storeToRefs } from 'pinia';
import { computed, ref, watch } from 'vue';
import ButtonIcon from '../inputs/ButtonIcon.vue';


const ContentStore = useContentStore();
const { searchQuery, stateFilteredPlaylist, stateVideo, stateFolder } = storeToRefs(ContentStore);
const { playlistFind, playlistSort } = ContentStore;

const currentPage = ref(1);
const itemsPerPage = ref(10);
const filteredPage = computed(() => {
    const minIndex = itemsPerPage.value * (currentPage.value - 1);
    const maxIndex = Math.min(itemsPerPage.value * (currentPage.value), stateFilteredPlaylist.value.length);

    return stateFilteredPlaylist.value.slice(minIndex, maxIndex);
});
const sortAscending = ref(true);
const lastSortKey = ref('');

const handlePageChange = (page) => {
    currentPage.value = page;
}

const handlePageReset = () => {
    currentPage.value = 1
};

const handleSortChange = (sortKey) => {
    if (sortKey?.value) lastSortKey.value = sortKey?.value;

    if (!lastSortKey.value) return;
    playlistSort(lastSortKey.value, sortAscending.value ? 1 : -1)
}

const sortingOptions = ref([
    {
        title: 'Title',
        value: 'title',
        disabled: false
    },
    {
        title: 'Duration',
        value: 'duration',
        disabled: false
    },
    {
        title: 'Views',
        value: 'view_count',
        disabled: false
    },
    {
        title: 'Date Uploaded',
        value: 'date',
        disabled: false
    },
    {
        title: 'Date released',
        value: 'date_released',
        disabled: true
    },
    {
        title: 'Episode',
        value: 'episode',
        disabled: true
    },
    {
        title: 'Season',
        value: 'season',
        disabled: true
    },
]);

watch(stateFolder, handlePageReset, { immediate: true })
</script>

<template>
    <table class="w-full flex flex-col gap-2">
        <section class="flex justify-center sm:justify-between py-2 flex-col sm:flex-row gap-2">
            <!-- <h2 class="text-2xl py-4">Episodes</h2> -->
            <LabelledTextInput v-model="searchQuery" :text="'Search:'" :placeholder="'Enter Search Query...'"
                :id="'table-search'" class="w-full sm:w-80" title="Search Results"/>
            <span class="flex items-end gap-2">
                <div class="flex gap-2 flex-col w-full sm:w-40">
                    <FormInputLabel :field="{ name: 'sort', text: 'Sort by:' }" />
                    <InputSelect :placeholder="'None'" :options="sortingOptions" class="w-full" title="Select Sort"
                        @selectItem="handleSortChange" :defaultItem="0" />
                </div>
                <ButtonIcon @click="sortAscending = !sortAscending; handleSortChange()">
                    <template #icon>
                        <PhSortAscendingLight v-if="sortAscending" width="24" height="24" />
                        <PhSortDescendingLight v-else width="24" height="24" />
                    </template>
                </ButtonIcon>
            </span>
        </section>
        <hr>
        <tbody class="flex w-full flex-wrap gap-2">
            <VideoCard v-for="(video, index) in filteredPage" :key="index" :video="video" :index="index"
                :currentID="stateVideo?.id" @playByID="playlistFind" />
        </tbody>
        <hr>
        <TablePagination :listLength="stateFilteredPlaylist?.length ?? 0" :itemsPerPage="itemsPerPage"
            :currentPage="currentPage" @setPage="handlePageChange" />
    </table>
</template>