<script setup>
    import FolderCard from '../FolderCard.vue';
    import RecordCard from '../RecordCard.vue';
    import { storeToRefs } from 'pinia';
    import { useContentStore } from '../../stores/ContentStore';
    import { useAppStore } from '../../stores/AppStore';

    const appStore = useAppStore();
    const contentStore = useContentStore();

    const { selectedSideBar } = storeToRefs(appStore);
    const { folders, records } = storeToRefs(contentStore);
</script>

<template>
    <div class="flex p-1 text-ri">
        <h1 id="sidebar-title" class="text-2xl w-full capitalize dark:text-white">{{ selectedSideBar }}</h1>
    </div>

    <hr class="mt-2 mb-3">
    <section v-if="selectedSideBar === 'folders'" id="list-content-folders" class="flex space-y-2 flex-wrap">
        <FolderCard v-for="folder in folders" :key="folder.folder_id" :folder-data="folder"/>
    </section>
    <section v-if="selectedSideBar === 'history'" id="list-content-history" class="flex space-y-2 flex-wrap">
        <RecordCard v-for="record in records" :key="record.record_id" :record="record"/>
    </section>
</template>