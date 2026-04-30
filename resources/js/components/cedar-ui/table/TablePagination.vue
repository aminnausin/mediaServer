<script setup lang="ts">
import type { TablePaginationProps } from '@aminnausin/cedar-ui';

import { ProiconsChevronRight, ProiconsChevronLeft } from '../icons';
import { computed, nextTick, ref } from 'vue';

import TablePaginationButton from './TablePaginationButton.vue';

const props = withDefaults(defineProps<TablePaginationProps>(), {
    maxVisiblePages: 5,
});
const $element = ref<null | HTMLElement>(null);

const emit = defineEmits(['setPage']);

const pageCount = computed(() => {
    return Math.ceil(props.listLength / props.itemsPerPage);
});

const pageRange = computed<number | number[]>(() => {
    const total = pageCount.value;
    const current = props.currentPage;
    const maxVisible = props.maxVisiblePages;
    const edgeThreshold = Math.max(maxVisible - 1, 3);
    const edgeLength = Math.max(maxVisible - 1, 2);
    if (total <= maxVisible) return Array.from({ length: total }, (_, i) => i + 1); // Threshold to show all pages at once

    // 1 2 or 3, then show 1 2 3 4... options should be 1 2 or more, so max - 1 or 3 whichever is higher
    // Threshold (by page selected) to show groups of pages at left end (ie 1, 2, [3], 4, ..., 10)
    if (current < edgeThreshold) return Array.from({ length: edgeLength }, (_, i) => i + 1); // Number of pages to show with current page on left end (ie [1], 2, 3, 4, ..., 10) (inclusive)
    if (pageCount.value - current < edgeThreshold - 1) {
        // 7 8 9 or 10
        // Threshold (by page selected) to show group of pages at right end (ie 1, ..., 7, [8], 9, 10)
        let range: number[] = []; // Number of pages to show with current page on left end (ie 1, ..., 7, 8, 9, [10]) (exclusive so 3 items means 3 + 1 [the selected page])
        for (let i = pageCount.value - edgeLength + 1; i <= pageCount.value; i++) {
            range = [...range, i]; // Fill an array with all integers from pageCount - (number of items in group - 1) to pageCount
        }

        return range;
    }

    return maxVisible >= 5 ? [current - 1, current, current + 1] : [current];
});

const handleSetPage = async (page: number) => {
    emit('setPage', page);
    await nextTick();
    $element.value?.scrollIntoView({ behavior: 'instant', block: 'end' });
};
</script>

<template>
    <div class="@container flex max-w-full scroll-mb-12 flex-col flex-wrap items-center gap-2 text-sm sm:flex-row sm:justify-between" ref="$element">
        <p class="dark:text-foreground-7 text-nowrap">
            Showing
            <span class="dark:text-foreground-0 font-medium">{{ props.listLength ? props.itemsPerPage * (currentPage - 1) + 1 : 0 }}</span>
            to
            <span class="dark:text-foreground-0 font-medium">{{ Math.min(props.itemsPerPage * currentPage, props.listLength) }}</span>
            of
            <span class="dark:text-foreground-0 font-medium">{{ listLength }}</span>
            <!-- Results -->
        </p>
        <ul v-if="pageCount" class="bg-overlay-t divide-d text-foreground-7 border-r-button flex h-(--table-input-height) items-center divide-x rounded-md border leading-tight">
            <TablePaginationButton
                :pageNumber="-1"
                :text="'Previous'"
                :disabled="props.currentPage === 1"
                @click="handleSetPage(Math.max(1, props.currentPage - 1))"
                title="Previous Page"
                class="*:rounded-l-md"
            >
                <template #content v-if="useIcons">
                    <ProiconsChevronLeft class="size-4" title="Previous" />
                </template>
                <template #content v-else>
                    <span class="hidden @xs:block">
                        {{ 'Previous' }}
                    </span>
                    <ProiconsChevronLeft class="size-4 @xs:hidden" title="Previous" />
                </template>
            </TablePaginationButton>

            <!-- Diff between start and current page is greater than 2 -->
            <template v-if="pageCount > props.maxVisiblePages && props.currentPage > Math.max(props.maxVisiblePages - 2, 2)">
                <TablePaginationButton :pageNumber="1" :currentPage="props.currentPage" @click="handleSetPage(1)" :sticky="true" v-if="maxVisiblePages > 3" />
                <TablePaginationButton
                    @click="handleSetPage(Math.floor(currentPage / 2))"
                    :title="`Page ${Math.floor(currentPage / 2)}`"
                    :class="'@4xs:block hidden'"
                    :text="'...'"
                    :pageNumber="-1"
                    :underline="true"
                />
            </template>

            <TablePaginationButton v-for="page in pageRange" :key="page" :pageNumber="page" :currentPage="props.currentPage" @click="handleSetPage(page)" :title="`Page ${page}`" />

            <template v-if="pageCount > props.maxVisiblePages && pageCount - props.currentPage > Math.max(props.maxVisiblePages - 3, 1)">
                <TablePaginationButton
                    @click="handleSetPage(Math.floor((pageCount - currentPage) / 2 + currentPage))"
                    :title="`Page ${Math.floor((pageCount - currentPage) / 2 + currentPage)}`"
                    :class="'@4xs:block hidden'"
                    :text="'...'"
                    :pageNumber="-1"
                    :underline="true"
                />
                <TablePaginationButton :pageNumber="pageCount" :currentPage="props.currentPage" @click="handleSetPage(pageCount)" :sticky="true" v-if="maxVisiblePages > 3" />
            </template>

            <TablePaginationButton
                :pageNumber="-1"
                :text="'Next'"
                :disabled="props.currentPage === pageCount"
                @click="handleSetPage(Math.min(pageCount, props.currentPage + 1))"
                title="Next Page"
                class="*:rounded-r-md"
            >
                <template #content v-if="useIcons">
                    <ProiconsChevronRight class="size-4" title="Next" />
                </template>
                <template #content v-else>
                    <span class="hidden @xs:block">
                        {{ 'Next' }}
                    </span>
                    <ProiconsChevronRight class="size-4 @xs:hidden" title="Next" />
                </template>
            </TablePaginationButton>
        </ul>
    </div>
</template>

<style lang="css" scoped>
.\@4xs\:block {
    @container (width >= 12rem) {
        display: block;
    }
}
</style>
