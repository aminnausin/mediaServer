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
                            console.log(i);
                            range = [...range, i];
                        }
                        out =  range;
                    }
                    console.log(out);
                    
                    return out === null ? [props.currentPage - 1, props.currentPage, props.currentPage + 1] : out;
                    
                })
</script>

<template>
    <div class="flex items-center flex-col sm:flex-row sm:justify-between flex-wrap gap-2">
        <p class="text-gray-700 dark:text-neutral-300 line-clamp-1">
            Showing
            <span class="font-medium dark:text-neutral-100">{{ props.itemsPerPage * (currentPage - 1) + 1 }}</span>
            to
            <span class="font-medium dark:text-neutral-100">{{ Math.min(props.itemsPerPage * (currentPage), props.listLength) }}</span>
            of
            <span class="font-medium dark:text-neutral-100">{{ listLength }}</span>
            Results
        </p>
        <nav class="">
            <ul
                class="flex flex-wrap items-center text-sm leading-tight bg-white dark:bg-neutral-800 border divide-x rounded h-9 text-neutral-500 dark:text-neutral-200 divide-neutral-200 dark:divide-neutral-700 border-neutral-200 dark:border-neutral-700">
                <TablePaginationButton :pageNumber="-1" :text="'Previous'"
                    @click="$emit('setPage', Math.max(1, props.currentPage - 1))" />

                <template v-if="pageCount > 5 && props.currentPage > 3">
                    <TablePaginationButton :pageNumber="1" :currentPage="props.currentPage" @click="$emit('setPage', 1)" />
                    <TablePaginationButton :pageNumber="-1" :text="'...'" @click="$emit('setPage', Math.floor(currentPage/2))" />
                </template>

                <TablePaginationButton v-for="page in pageRange" :key="page" :pageNumber="page"
                    :currentPage="props.currentPage" @click="$emit('setPage', page)" />

                <template v-if="pageCount > 5 && pageCount - props.currentPage > 2">
                    <TablePaginationButton :pageNumber="-1" :text="'...'" @click="$emit('setPage', Math.floor((pageCount - currentPage) / 2 + currentPage))" />
                    <TablePaginationButton :pageNumber="pageCount" :currentPage="props.currentPage" @click="$emit('setPage', pageCount)" />
                </template>

                <TablePaginationButton :pageNumber="-1" :text="'Next'"
                    @click="$emit('setPage', Math.min(pageCount, props.currentPage + 1))" />

                <!-- 1 ... 5 7 8 ... 12 - current is 7

                1 ... 5 7 8 9 current is 8

                1 2 3 4 ... 12
                
                1 2 3 4 5 6 7 8 9 10
                12 - 3 = 9 -> 9 > 3
                ie within 3 of start

                9 < 3 not within 3 of end

                12345
                5 - 3 < 3
                

                if number is 1 .. 3 4 5 right
                so if number is greater than 3 add a ... between start and finish



                <li class="h-full">
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