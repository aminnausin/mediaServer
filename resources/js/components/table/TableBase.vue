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
    searchQuery: '',
    usePaginationIcons: false,
    maxVisiblePages: 5,
    noResultsMessage: 'No Results',
});

const emit = defineEmits<(e: 'search', value: string) => void>();

const tableData = useTable(props);
const sortAscending = ref(props.startAscending);
const lastSortKey = ref('');

const model = defineModel<string | undefined>({
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

const handleSearch = (event: InputEvent) => {
    const target = event.target as HTMLInputElement;
    const value = target.value;

    if (model.value !== undefined) {
        model.value = value;
    } else {
        tableData.fields.searchQuery = value;
    }
    emit('search', value);
};

onMounted(() => {
    if (props.useToolbar && props.sortAction) props.sortAction(lastSortKey.value as keyof T, props.startAscending ? 1 : -1);
});
</script>

<template>
    <!-- [&>*:not(:first-child)]:pt-4 -->
    <section class="flex flex-col gap-4 w-full">
        <section v-if="props.useToolbar" class="flex justify-center sm:justify-between flex-col sm:flex-row gap-2">
            <TextInputLabelled
                :value="model ?? tableData.fields.searchQuery"
                :text="''"
                :placeholder="`Search ${props.itemName ? `${props.itemName}...` : ''}`"
                :id="'table-search'"
                class="w-full sm:w-80"
                title="Search with..."
                @input="handleSearch"
            />
            <span class="flex items-end gap-2 flex-wrap">
                <div class="flex gap-2 flex-col w-full sm:w-40 flex-1">
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
                class="col-span-full flex items-center justify-center text-center text-lg text-gray-500 dark:text-gray-400 uppercase tracking-wider w-full gap-2"
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
        <!-- <hr class="p-0" /> -->
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
