<script setup lang="ts">
import { ref, watch } from 'vue';

import TextInputLabelled from '@/components/inputs/TextInputLabelled.vue';
import TablePagination from '@/components/table/TablePagination.vue';
import FormInputLabel from '@/components/labels/FormInputLabel.vue';
import InputSelect from '@/components/pinesUI/InputSelect.vue';
import ButtonIcon from '@/components/inputs/ButtonIcon.vue';
import useTable from '@/composables/useTable.ts';

import PhSortDescendingLight from '~icons/ph/sort-descending-light';
import PhSortAscendingLight from '~icons/ph/sort-ascending-light';

const props = defineProps([
    'useToolbar',
    'data',
    'row',
    'clickAction',
    'otherAction',
    'loading',
    'sortAction',
    'sortingOptions',
    'itemsPerPage',
    'searchQuery',
    'selectedID',
]);
const tableData = useTable(props);
const sortAscending = ref(true);
const lastSortKey = ref('');

const handleSortChange = (sortKey?: { title?: string; value?: string; disabled?: boolean }) => {
    if (sortKey?.value) {
        lastSortKey.value = sortKey.value;
    }

    if (!lastSortKey.value) return;
    props.sortAction(lastSortKey.value, sortAscending.value ? 1 : -1);
};

watch(props.data, tableData.handlePageReset, { immediate: true });
</script>

<template>
    <div v-if="props.loading" class="text-center text-lg text-gray-500 dark:text-gray-400 uppercase tracking-wider w-full py-16">
        Loading...
    </div>
    <table v-else class="flex flex-col gap-4 divide-y first:pt-0 [&>*:not(:first-child)]:pt-4">
        <section v-if="props.useToolbar" class="flex justify-center sm:justify-between py-2 flex-col sm:flex-row gap-2">
            <TextInputLabelled
                v-model="tableData.fields.searchQuery"
                :text="'Search:'"
                :placeholder="'Enter Search Query...'"
                :id="'table-search'"
                class="w-full sm:w-80"
                @input="$emit('search', tableData.fields.searchQuery)"
                title="Search Here"
            />
            <span class="flex items-end gap-2 flex-wrap">
                <div class="flex gap-2 flex-col w-full sm:w-40 flex-1">
                    <FormInputLabel :field="{ name: 'sort', text: 'Sort by:' }" />
                    <InputSelect
                        :placeholder="'None'"
                        :options="props.sortingOptions"
                        class="w-full"
                        title="Select Sort"
                        @selectItem="handleSortChange"
                        :defaultItem="0"
                    />
                </div>
                <ButtonIcon
                    @click="
                        sortAscending = !sortAscending;
                        handleSortChange();
                    "
                    title="Sort Results"
                    aria-label="Sort Results"
                >
                    <template #icon>
                        <PhSortAscendingLight v-if="sortAscending" width="24" height="24" />
                        <PhSortDescendingLight v-else width="24" height="24" />
                    </template>
                </ButtonIcon>
            </span>
        </section>
        <tbody class="flex w-full flex-wrap gap-2">
            <div
                v-if="tableData.filteredPage.length === 0 && !props.loading"
                class="text-center text-lg text-gray-500 dark:text-gray-400 uppercase tracking-wider w-full"
            >
                No Results
            </div>
            <component
                v-else
                :is="props.row"
                v-for="(row, index) in tableData.filteredPage"
                :key="row?.id ?? index"
                :data="row"
                :index="index"
                :currentID="props.selectedID ?? null"
                @clickAction="props.clickAction(row?.id)"
                @otherAction="$emit('otherAction', $event)"
            ></component>
        </tbody>
        <TablePagination
            :listLength="props.data?.length ?? 0"
            :itemsPerPage="tableData.fields.itemsPerPage"
            :currentPage="tableData.fields.currentPage"
            @setPage="tableData.handlePageChange"
        />
    </table>
</template>
