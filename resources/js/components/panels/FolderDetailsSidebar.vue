<script setup lang="ts">
import { defineAsyncComponent } from 'vue';
import { useFolderTabs } from '@/components/folders/FolderTabs';
import { useAppStore } from '@/stores/AppStore';

import SidebarSkeleton from '@/components/skeleton/composites/SidebarSkeleton.vue';

const { activeFolderTab } = useFolderTabs();

const AppStore = useAppStore();
const FolderSidebarAsync = defineAsyncComponent(async () => await import('@/components/panels/FolderSidebar.vue'));
const HistorySidebarAsync = defineAsyncComponent(async () => await import('@/components/panels/HistorySidebar.vue'));
</script>

<template>
    <Suspense v-if="AppStore.selectedSideBar === 'folders'">
        <FolderSidebarAsync :url-suffix="`${['details', activeFolderTab?.name].filter(Boolean).join('/')}`" />

        <template #fallback>
            <SidebarSkeleton />
        </template>
    </Suspense>
    <Suspense v-if="AppStore.selectedSideBar === 'history'">
        <HistorySidebarAsync />
        <template #fallback>
            <SidebarSkeleton />
        </template>
    </Suspense>
</template>
