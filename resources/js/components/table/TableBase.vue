<script setup lang="ts" generic="T">
import type { SortOption, TableProps } from '@/types/types';

import { onMounted, ref } from 'vue';

import TextInputLabelled from '@/components/inputs/TextInputLabelled.vue';
import TablePagination from '@/components/table/TablePagination.vue';
import InputSelect from '@/components/pinesUI/InputSelect.vue';
import ButtonIcon from '@/components/inputs/ButtonIcon.vue';
import useTable from '@/composables/useTable.ts';

import SvgSpinners90RingWithBg from '~icons/svg-spinners/90-ring-with-bg';
import PhSortDescendingLight from '~icons/ph/sort-descending-light';
import PhSortAscendingLight from '~icons/ph/sort-ascending-light';

const props = withDefaults(defineProps<TableProps<T>>(), {
    useToolbar: true,
    usePagination: true,
    itemsPerPage: 12,
    selectedID: null,
    startAscending: true,
    usePaginationIcons: false,
    maxVisiblePages: 5,
    noResultsMessage: 'No Results',
});

const tableData = useTable(props);
const sortAscending = ref(props.startAscending);
const lastSortKey = ref('');

// Search Query
const model = defineModel<string>({
    required: false,
    default: undefined,
});

defineSlots<{
    row(props: { row: T; index: number; selectedID: any }): any;
}>();

const handleSortChange = (sortKey?: SortOption) => {
    if (sortKey?.value) {
        lastSortKey.value = sortKey.value;
    }

    if (!lastSortKey.value) return;
    props.sortAction?.(lastSortKey.value as keyof T, sortAscending.value ? 1 : -1);
};

onMounted(() => {
    if (props.useToolbar && props.sortAction) props.sortAction(lastSortKey.value as keyof T, props.startAscending ? 1 : -1);
});
</script>

<template>
    <section class="flex w-full flex-col gap-3">
        <section v-if="props.useToolbar" class="flex flex-col justify-center gap-2 sm:flex-row sm:justify-between">
            <TextInputLabelled
                v-if="model !== undefined"
                v-model="model"
                :placeholder="`Search ${props.itemName ? `${props.itemName}...` : ''}`"
                :id="'table-search'"
                class="w-full sm:w-80"
                title="Search with..."
            />

            <span :class="['flex flex-wrap items-end gap-2 sm:flex-nowrap', { 'flex-1': model === undefined }]">
                <div class="flex w-full flex-1 flex-col gap-2 sm:w-40">
                    <InputSelect
                        :name="'sort'"
                        :placeholder="'Sort by...'"
                        :prefix="'By '"
                        :options="sortingOptions"
                        :defaultItem="0"
                        class="w-full"
                        title="Sort by..."
                        @selectItem="handleSortChange"
                    />
                </div>
                <ButtonIcon
                    @click="
                        sortAscending = !sortAscending;
                        handleSortChange();
                    "
                    :title="`Reorder Results...`"
                    :aria-label="`Reorder Results`"
                    class="ring-inset"
                >
                    <template #icon>
                        <!-- Arrow Pointing Down if ascending and then Up otherwise (arrow shows what to change to ?? idk descending points up actually)-->
                        <PhSortDescendingLight v-if="sortAscending" width="24" height="24" />
                        <PhSortAscendingLight v-else width="24" height="24" />
                    </template>
                </ButtonIcon>
            </span>
        </section>
        <section :class="[useGrid || `flex w-full flex-wrap gap-2 ${tableStyles ?? ''}`]">
            <div
                v-if="loading || tableData.filteredPage.length === 0"
                class="col-span-full flex w-full items-center justify-center gap-2 text-center text-lg tracking-wider text-gray-500 uppercase dark:text-gray-400"
            >
                <p>{{ loading ? '...Loading' : noResultsMessage }}</p>
                <SvgSpinners90RingWithBg v-show="loading" />
            </div>
            <template v-else>
                <template v-for="(row, index) in tableData.filteredPage" :key="row?.id ?? index">
                    <slot name="row" :row="row" :index="index" :selectedID="props.selectedID">
                        <component
                            :is="props.row"
                            :data="row"
                            :index="index"
                            :currentID="props.selectedID ?? null"
                            v-bind="rowAttributes"
                            @clickAction="(...args: any[]) => props.clickAction?.(row?.id, ...args)"
                            @otherAction="(...args: any[]) => props.otherAction?.(row?.id, ...args)"
                        ></component>
                    </slot>
                </template>
            </template>
        </section>
        <TablePagination
            v-if="usePagination"
            :class="paginationClass"
            :listLength="props.data?.length ?? 0"
            :itemsPerPage="tableData.fields.itemsPerPage"
            :currentPage="tableData.fields.currentPage"
            :useIcons="props.usePaginationIcons"
            :max-visible-pages="props.maxVisiblePages"
            @setPage="tableData.handlePageChange"
        />
    </section>
</template>
