<script setup lang="ts">
import { defineAsyncComponent, watch } from 'vue';
import { useFolderTabs } from '@/components/folders/FolderTabs';
import { useAppStore } from '@/stores/AppStore.ts';
import { storeToRefs } from 'pinia';
import { BaseDrawer } from '@/components/cedar-ui/drawer';
import { useDrawer } from '@aminnausin/cedar-ui';
import { useRoute } from 'vue-router';

const { selectedSideBar } = storeToRefs(useAppStore());
const { activeFolderTab } = useFolderTabs();

const FolderSidebarAsync = defineAsyncComponent(async () => await import('@/components/panels/FolderSidebar.vue'));

const drawer = useDrawer();
const route = useRoute();

watch(
    () => route.fullPath,
    () => drawer.close('programmatic'),
);
</script>

<template>
    <BaseDrawer>
        <Suspense v-if="selectedSideBar === 'folders'">
            <FolderSidebarAsync :url-suffix="`${['details', activeFolderTab?.name].filter(Boolean).join('/')}`" />

            <template #fallback>
                <SidebarSkeleton />
            </template>
        </Suspense>
    </BaseDrawer>
</template>
