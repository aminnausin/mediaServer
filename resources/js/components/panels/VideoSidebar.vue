<script setup>
    import FolderCard from '../FolderCard.vue';
    import RecordCard from '../RecordCard.vue';
    import { storeToRefs } from 'pinia';
    import { useAuthStore } from '../../stores/AuthStore';

    const authStore = useAuthStore();
    const { folders, records } = storeToRefs(authStore);
</script>

<template>
    <div class="flex p-1 text-ri">
        <h1 id="sidebar-title" class="text-2xl w-full">Folders</h1>
    </div>

    <hr class="mt-2 mb-3">
    <section id="list-content-folders" class="hidden flex space-y-2 flex-wrap">
        <FolderCard v-for="folder in folders" :key="folder.folder_id" :folder-data="folder"/>
    </section>
    <section id="list-content-history" class="hidden flex space-y-2 flex-wrap">
        <RecordCard v-for="record in records" :key="record.record_id" :record="record"/>
    </section>
</template>