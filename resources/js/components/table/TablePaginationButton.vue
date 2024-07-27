<script setup>
const props = defineProps(['currentPage', 'pageNumber', 'text']);
</script>

<template>
    <li :class="{ 'hidden': props.currentPage !== props.pageNumber && !props.text }" class="h-full md:block">
        <button class="relative inline-flex items-center h-full px-3 group hover:text-neutral-900 hover:dark:text-neutral-50" :class="{ 'text-neutral-900 dark:text-neutral-50 bg-gray-50 dark:bg-gray-800': props.currentPage === props.pageNumber, }">
            <span>
                {{ props?.text ?? props.pageNumber }}
            </span>

            <span v-if="!props.text"
                class="box-content absolute bottom-0 w-0 h-px -mx-px duration-200 ease-out translate-y-px border-transparent group-hover:left-0 group-hover:w-full group-hover:border-l group-hover:border-r bg-neutral-900 dark:bg-neutral-200 group-hover:border-neutral-900 group-hover:dark:border-neutral-200"
                :class="{'left-0 w-full border-l border-r' : props.currentPage === props.pageNumber, 'left-1/2' : props.currentPage !== props.pageNumber}">
            </span>
        </button>
    </li>
</template>