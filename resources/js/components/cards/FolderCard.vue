<script setup>
    import { RouterLink } from 'vue-router';

    const { folder, categoryName } = defineProps(['folder', 'categoryName']);
</script>

<template>
    <RouterLink class="p-2 flex flex-wrap rounded-xl dark:bg-primary-dark-800 bg-primary-800 dark:text-white shadow-md w-full divide-y divide-gray-300 group" :to="`/${categoryName}/${folder.attributes.name}`">
        <section class="flex justify-between items-baseline w-full">
            <h2 class="text-xl truncate">{{ folder.attributes.name }}</h2>
        </section>
        <aside class="flex justify-between items-center w-full pt-1">
            <h3 class="text-left text-neutral-500 line-clamp-1">{{folder.attributes.file_count}} Episode{{folder.attributes.file_count !== 1 ? 's' : ''}}</h3>
            <button class="hidden group-hover:flex space-x-1 folder-link">
                <span class="flex hover:bg-orange-500 hover:stroke-none border-orange-500 border-2 rounded shadow px-2 space-x-1"> 
                    Watch
                </span>
            </button>
        </aside>
    </RouterLink>
</template>