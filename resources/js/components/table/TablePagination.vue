<script setup>
import { computed } from 'vue';
import TablePaginationButton from './TablePaginationButton.vue';

const props = defineProps(['listLength', 'currentPage', 'itemsPerPage']);
const pageCount = computed(() => { return Math.ceil(props.listLength / props.itemsPerPage); });
const pageRange = computed(() => {
                    let out = null;
                    if(pageCount.value <= 5) out = pageCount.value;
                    else if(props.currentPage <= 3) out = Math.min(4, pageCount.value)
                    else if(pageCount.value - props.currentPage <= 2){
                        let range = [];
                        for(var i = pageCount.value - 3; i <= pageCount.value; i++){
                            range = [...range, i];
                        }
                        out =  range;
                    }
                    
                    return out === null ? [props.currentPage - 1, props.currentPage, props.currentPage + 1] : out;
                })
</script>

<template>
    <div class="flex items-center flex-col sm:flex-row sm:justify-between flex-wrap gap-2" id="pageinate">
        <p class="text-gray-700 dark:text-neutral-300 line-clamp-1">
            Showing
            <span class="font-medium dark:text-neutral-100">{{ props.listLength ? props.itemsPerPage * (currentPage - 1) + 1 : 0 }}</span>
            to
            <span class="font-medium dark:text-neutral-100">{{ Math.min(props.itemsPerPage * (currentPage), props.listLength) }}</span>
            of
            <span class="font-medium dark:text-neutral-100">{{ listLength }}</span>
            <!-- Results -->
        </p>
        <nav id="pageinate">
            <ul
                class="flex flex-wrap items-center text-sm leading-tight bg-white dark:bg-primary-dark-800/70 border divide-x rounded h-9 text-neutral-500 dark:text-neutral-200 divide-neutral-200 dark:divide-neutral-700 border-neutral-200 dark:border-neutral-700">
                <TablePaginationButton :pageNumber="-1" :text="'Previous'" :disabled="props.currentPage === 1"
                    @click="$emit('setPage', Math.max(1, props.currentPage - 1))" />

                <template v-if="pageCount > 5 && props.currentPage > 3">
                    <TablePaginationButton :pageNumber="1" :currentPage="props.currentPage" @click="$emit('setPage', 1)" :sticky="true"/>
                    <TablePaginationButton :pageNumber="-1" :text="'...'" @click="$emit('setPage', Math.floor(currentPage/2))" :underline="true"/>
                </template>

                <TablePaginationButton v-for="page in pageRange" :key="page" :pageNumber="page"
                    :currentPage="props.currentPage" @click="$emit('setPage', page)" />

                <template v-if="pageCount > 5 && pageCount - props.currentPage > 2">
                    <TablePaginationButton :pageNumber="-1" :text="'...'" @click="$emit('setPage', Math.floor((pageCount - currentPage) / 2 + currentPage))" :underline="true"/>
                    <TablePaginationButton :pageNumber="pageCount" :currentPage="props.currentPage" @click="$emit('setPage', pageCount)" :sticky="true"/>
                </template>

                <TablePaginationButton :pageNumber="-1" :text="'Next'" :disabled="props.currentPage === pageCount"
                    @click="$emit('setPage', Math.min(pageCount, props.currentPage + 1))" />
            </ul>
        </nav>
    </div>
</template>