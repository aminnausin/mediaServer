<script setup>
    import { RouterLink } from 'vue-router';
    import { useAuthStore } from '../stores/AuthStore';
    import { storeToRefs } from 'pinia';

    const authStore = useAuthStore();
    const { folderData } = defineProps(['folderData']);
    const { stateDirectory } = storeToRefs(authStore);
</script>

<template>
    <div class="p-2 flex flex-wrap rounded-xl bg-neutral-100 dark:bg-neutral-800 dark:text-white shadow-md w-full divide-y divide-gray-300 group">
        <section class="flex justify-between items-baseline w-full">
            <h2 class="text-xl truncate">{{ folderData?.folder_name }}</h2>
        </section>
        <aside class="flex justify-between items-center w-full pt-1">
            <h3 class="text-lg text-left text-neutral-500 line-clamp-1">{{folderData?.file_count}} Episode{{folderData?.file_count > 1 ? 's' : ''}}</h3>
            <RouterLink class="hidden group-hover:flex space-x-1 folder-link" :to="`/${stateDirectory.name}/${folderData?.folder_name}`">
                <span class="flex hover:bg-orange-500 hover:stroke-none border-orange-500 border-2 rounded shadow px-2 space-x-1"> 
                    Watch
                </span>
            </RouterLink>
        </aside>
    </div>
</template>