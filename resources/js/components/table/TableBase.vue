<script setup>
import useTable from '../../composables/useTable';
import LabelledTextInput from '../inputs/LabelledTextInput.vue';
import TablePagination from '../table/TablePagination.vue';

import { watch } from 'vue';


const props = defineProps(['useToolbar','data', 'row', 'clickAction']);
const tableData = useTable(props);

watch(props.data, tableData.handlePageReset, {immediate: true})
</script>

<template>
    <table class="w-full flex flex-col gap-4 divide-y first:pt-0 [&>*:not(:first-child)]:pt-4">
        <section v-if="props.useToolbar" class="flex justify-center sm:justify-between p-2">
            <LabelledTextInput v-model="tableData.fields.searchQuery" :text="'Search'" :placeholder="'Enter search query'" :id="'table-search'" class="w-80"/>
        </section>
        <tbody class="flex w-full flex-wrap gap-2">
            <component :is="props.row" v-for="(row, index) in tableData.filteredPage" :key="row?.id ?? index" :data="row" @clickAction="props.clickAction"></component>
            <div v-if="tableData.filteredPage.length === 0" class="text-center text-lg text-gray-500 dark:text-gray-400 uppercase tracking-wider w-full">No Results</div>
        </tbody>
        <TablePagination :listLength="props.data?.length ?? 0" :itemsPerPage="tableData.fields.itemsPerPage" :currentPage="tableData.fields.currentPage" @setPage="tableData.handlePageChange"/>
    </table>
</template>