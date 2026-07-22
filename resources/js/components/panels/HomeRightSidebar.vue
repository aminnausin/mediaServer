<script setup lang="ts">
import { useRecentlyUpdated, useRecentlyUploaded } from '@/service/home/useHomeQueries';
import { defineAsyncComponent } from 'vue';
import { useAppStore } from '@/stores/AppStore';
import { computed } from 'vue';

import SidebarSkeleton from '@/components/skeleton/composites/SidebarSkeleton.vue';

const { data: recentlyUploadedMusic, isLoading: isLoadingMusic } = useRecentlyUploaded('audio');
const { data: recentlyUploaded, isLoading: isLoadingVideo } = useRecentlyUploaded('video');
const { data: recentlyUpdated, isLoading: isLoadingUpdated } = useRecentlyUpdated();

const AppStore = useAppStore();
const HomeActivityFeedAsync = defineAsyncComponent(async () => await import('@/components/home/HomeActivityFeed.vue'));
const HistorySidebarAsync = defineAsyncComponent(async () => await import('@/components/panels/HistorySidebar.vue'));

const isLoading = computed(() => isLoadingVideo.value || isLoadingMusic.value || isLoadingUpdated.value);
</script>

<template>
    <Suspense v-if="AppStore.selectedSideBar === 'feed'">
        <HomeActivityFeedAsync :videos="recentlyUploaded" :music="recentlyUploadedMusic" :updated-folders="recentlyUpdated" :is-loading="isLoading" />

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
