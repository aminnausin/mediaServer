<script setup>
import { computed } from 'vue';
import TablePaginationButton from './TablePaginationButton.vue';

const props = defineProps(['listLength', 'currentPage', 'itemsPerPage']);
const pageCount = computed(() => { return Math.ceil(props.listLength / props.itemsPerPage); });
</script>

<template>
    <div class="flex items-center flex-col sm:flex-row sm:justify-between flex-wrap gap-2">
        <p class="text-gray-700 dark:text-neutral-100 line-clamp-1">
            Showing
            <span class="font-medium">{{ props.itemsPerPage * (currentPage - 1) + 1 }}</span>
            to
            <span class="font-medium">{{ Math.min(props.itemsPerPage * (currentPage), props.listLength) }}</span>
            of
            <span class="font-medium">{{ listLength }}</span>
            Results
        </p>
        <nav class="">
            <ul
                class="flex items-center text-sm leading-tight bg-white dark:bg-gray-700 border divide-x rounded h-9 text-neutral-500 dark:text-neutral-200 divide-neutral-200 dark:divide-neutral-700 border-neutral-200 dark:border-neutral-700">
                <TablePaginationButton :pageNumber="-1" :text="'Previous'"
                    @click="$emit('setPage', Math.max(1, props.currentPage - 1))" />
                <TablePaginationButton v-for="page in pageCount" :key="page" :pageNumber="page"
                    :currentPage="props.currentPage" @click="$emit('setPage', page)" />
                <TablePaginationButton :pageNumber="-1" :text="'Next'"
                    @click="$emit('setPage', Math.min(pageCount, props.currentPage + 1))" />


                <!-- <li class="h-full">
                    <button @click="$emit('setPage', Math.max(1, props.currentPage - 1))" 
                        class="relative inline-flex items-center h-full px-3 ml-0 rounded-l group hover:text-neutral-900">
                        <span>Previous</span>
                    </button>
                </li> -->
                <!-- <li v-for="page in pageCount" :key="page" :class="{'hidden' : props.currentPage !== page}" class="h-full md:block" >
                    <button @click="$emit('setPage', page)"  class="relative inline-flex items-center h-full px-3" :class="{'text-neutral-900 group bg-gray-50' : props.currentPage === page, 'group hover:text-neutral-900' : props.currentPage !== page}">
                        <span>{{ page }}</span>
                        <span v-if="props.currentPage === page"
                            class="box-content absolute bottom-0 left-0 w-full h-px -mx-px translate-y-px border-l border-r bg-neutral-900 border-neutral-900"></span>
                        <span v-else
                            class="box-content absolute bottom-0 w-0 h-px -mx-px duration-200 ease-out translate-y-px border-transparent bg-neutral-900 group-hover:border-l group-hover:border-r group-hover:border-neutral-900 left-1/2 group-hover:left-0 group-hover:w-full"></span>
                    </button>
                </li> -->
                <!-- <li class="hidden h-full md:block">
                    <div class="relative inline-flex items-center h-full px-2.5 group">
                        <span>...</span>
                    </div>
                </li> -->
                <!-- <li class="h-full">
                    <button @click="$emit('setPage', Math.min(pageCount, props.currentPage + 1))" 
                        class="relative inline-flex items-center h-full px-3 rounded-r group hover:text-neutral-900">
                        <span>Next</span>
                    </button>
                </li> -->
            </ul>
        </nav>
    </div>
</template>