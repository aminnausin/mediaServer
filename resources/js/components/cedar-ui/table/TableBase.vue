<script setup lang="ts" generic="T extends TableRow">
import type { TableSortOption, TableProps, TableRow } from '@aminnausin/cedar-ui';

import { PhSortDescendingLight, PhSortAscendingLight } from '../icons';
import { onMounted, ref, toRef } from 'vue';
import { cn, useTable } from '@aminnausin/cedar-ui';
import { InputSelect } from '../select';
import { ButtonIcon } from '../button';
import { TextInput } from '../input';

import TableLoadingSpinner from './TableLoadingSpinner.vue';
import TablePagination from './TablePagination.vue';

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
const { currentPage, itemsPerPage, pageData, setPage } = useTable<T>({ itemsPerPage: props.itemsPerPage, data: toRef(props, 'data') });

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

const handleSortChange = (sortKey?: TableSortOption) => {
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
        <section v-if="props.useToolbar" class="flex h-10 flex-col justify-center gap-2 sm:flex-row sm:justify-between">
            <TextInput
                v-if="model !== undefined"
                v-model="model"
                :placeholder="`Search ${props.itemName ? `${props.itemName}...` : ''}`"
                :id="'table-search'"
                class="ring-r-button hocus:ring-2 dark:bg-surface-2 w-full ring-1 sm:w-80"
                title="Search with..."
            />

            <div :class="['flex flex-wrap items-end gap-2 sm:flex-nowrap', { 'flex-1': model === undefined }]">
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
                    class="inline-flex h-full"
                >
                    <template #icon>
                        <!-- Arrow Pointing Down if ascending and then Up otherwise (arrow shows what to change to ?? idk descending points up actually)-->
                        <PhSortDescendingLight v-if="sortAscending" width="24" height="24" />
                        <PhSortAscendingLight v-else width="24" height="24" />
                    </template>
                </ButtonIcon>
            </div>
        </section>
        <section :class="cn(useGrid || 'flex w-full flex-wrap gap-2', tableStyles)">
            <TableLoadingSpinner v-if="loading || pageData?.length === 0" :is-loading="loading" :data-length="pageData?.length" :no-results-message="noResultsMessage" />
            <template v-else>
                <template v-for="(row, index) in pageData" :key="row.id">
                    <slot name="row" :row="row" :index="index" :selectedID="props.selectedID">
                        <component
                            :is="props.row"
                            :data="row"
                            :index="index"
                            :currentID="props.selectedID ?? null"
                            v-bind="rowAttributes"
                            @clickAction="(...args: any[]) => props.clickAction?.(row.id, ...args)"
                            @otherAction="(...args: any[]) => props.otherAction?.(row.id, ...args)"
                        ></component>
                    </slot>
                </template>
            </template>
        </section>
        <TablePagination
            v-if="usePagination"
            :class="paginationClass"
            :listLength="props.data?.length ?? 0"
            :itemsPerPage="itemsPerPage"
            :currentPage="currentPage"
            :useIcons="props.usePaginationIcons"
            :max-visible-pages="props.maxVisiblePages"
            @setPage="setPage"
        />
    </section>
</template>
