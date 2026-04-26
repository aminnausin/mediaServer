<script setup lang="ts">
import { defineAsyncComponent } from 'vue';
import { useAppStore } from '@/stores/AppStore';

import SidebarSkeleton from '@/components/skeleton/composites/SidebarSkeleton.vue';
import HistorySidebar from '@/components/panels/HistorySidebar.vue';

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
