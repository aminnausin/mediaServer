<script setup lang="ts">
import { defineAsyncComponent } from 'vue';
import { useAppStore } from '@/stores/AppStore';

import HistorySidebar from '@/components/panels/HistorySidebar.vue';
import SidebarSkeleton from '../skeleton/composites/SidebarSkeleton.vue';

const AppStore = useAppStore();
const FolderSidebarAsync = defineAsyncComponent(async () => await import('@/components/panels/FolderSidebar.vue'));
</script>

<template>
    <Suspense v-if="AppStore.selectedSideBar === 'folders'">
        <FolderSidebarAsync />
        <template #fallback>
            <SidebarSkeleton />
        </template>
    </Suspense>
    <Suspense v-if="AppStore.selectedSideBar === 'history'">
        <HistorySidebar />
        <template #fallback>
            <SidebarSkeleton />
        </template>
    </Suspense>
</template>
