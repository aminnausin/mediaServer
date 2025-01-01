<script setup lang="ts">
import { computed, nextTick, ref } from 'vue';

import TablePaginationButton from './TablePaginationButton.vue';

const props = defineProps<{
    listLength: number;
    currentPage: number;
    itemsPerPage: number;
}>();
const $element = ref<null | HTMLElement>(null);

const emit = defineEmits(['setPage']);

const pageCount = computed(() => {
    return Math.ceil(props.listLength / props.itemsPerPage);
});
const pageRange = computed(() => {
    let out = null;
    if (pageCount.value <= 5) out = pageCount.value;
    else if (props.currentPage <= 3) out = Math.min(4, pageCount.value);
    else if (pageCount.value - props.currentPage <= 2) {
        let range: number[] = [];
        for (var i = pageCount.value - 3; i <= pageCount.value; i++) {
            range = [...range, i];
        }
        out = range;
    }

    return out === null ? [props.currentPage - 1, props.currentPage, props.currentPage + 1] : out;
});

const handleSetPage = async (page: number) => {
    emit('setPage', page);
    await nextTick();
    $element.value?.scrollIntoView({ behavior: 'smooth', block: 'end' });
};
</script>

<template>
    <div class="flex items-center flex-col sm:flex-row sm:justify-between flex-wrap gap-2 scroll-mb-12" ref="$element">
        <p class="text-gray-700 dark:text-neutral-300 line-clamp-1 text-sm">
            Showing
            <span class="font-medium dark:text-neutral-100">{{ props.listLength ? props.itemsPerPage * (currentPage - 1) + 1 : 0 }}</span>
            to
            <span class="font-medium dark:text-neutral-100">{{ Math.min(props.itemsPerPage * currentPage, props.listLength) }}</span>
            of
            <span class="font-medium dark:text-neutral-100">{{ listLength }}</span>
            <!-- Results -->
        </p>
        <nav>
            <ul
                class="flex flex-wrap items-center text-sm leading-tight bg-white dark:bg-primary-dark-800/70 border divide-x rounded h-9 text-neutral-500 dark:text-neutral-200 divide-neutral-200 dark:divide-neutral-700 border-neutral-200 dark:border-neutral-700"
            >
                <TablePaginationButton :pageNumber="-1" :text="'Previous'" :disabled="props.currentPage === 1" @click="handleSetPage(Math.max(1, props.currentPage - 1))" />

                <template v-if="pageCount > 5 && props.currentPage > 3">
                    <TablePaginationButton :pageNumber="1" :currentPage="props.currentPage" @click="handleSetPage(1)" :sticky="true" />
                    <TablePaginationButton :pageNumber="-1" :text="'...'" @click="handleSetPage(Math.floor(currentPage / 2))" :underline="true" />
                </template>

                <TablePaginationButton v-for="page in pageRange" :key="page" :pageNumber="page" :currentPage="props.currentPage" @click="handleSetPage(page)" />

                <template v-if="pageCount > 5 && pageCount - props.currentPage > 2">
                    <TablePaginationButton :pageNumber="-1" :text="'...'" @click="handleSetPage(Math.floor((pageCount - currentPage) / 2 + currentPage))" :underline="true" />
                    <TablePaginationButton :pageNumber="pageCount" :currentPage="props.currentPage" @click="handleSetPage(pageCount)" :sticky="true" />
                </template>

                <TablePaginationButton
                    :pageNumber="-1"
                    :text="'Next'"
                    :disabled="props.currentPage === pageCount"
                    @click="handleSetPage(Math.min(pageCount, props.currentPage + 1))"
                />
            </ul>
        </nav>
    </div>
</template>
