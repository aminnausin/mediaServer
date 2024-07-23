<script setup>
    import FolderCard from '../FolderCard.vue';
    import { storeToRefs } from 'pinia';
    import { useAuthStore } from '../../stores/AuthStore';

    const authStore = useAuthStore();
    const { folders, stateDirectory, selectedSideBar } = storeToRefs(authStore);
</script>

<template>
    <div class="flex p-1 text-ri">
        <h1 id="sidebar-title" class="text-2xl w-full capitalize dark:text-white">{{ selectedSideBar }}</h1>
    </div>

    <hr class="mt-2 mb-3">
    <section v-if="folders.values.length > 0 && selectedSideBar === 'folders'" id="list-content-folders" class="flex space-y-2 flex-wrap">
        <FolderCard v-for="folder in folders" :key="folder.folder_id" :folder-data="folder"/>
    </section>
</template>