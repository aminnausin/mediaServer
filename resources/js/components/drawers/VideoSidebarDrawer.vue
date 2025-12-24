<script setup lang="ts">
import { useAppStore } from '@/stores/AppStore';
import { BaseDrawer } from '@/components/cedar-ui/drawer';

import VideoSidebar from '@/components/panels/VideoSidebar.vue';

const { cycleSideBar } = useAppStore();
</script>

<template>
    <BaseDrawer @close="cycleSideBar()">
        <VideoSidebar />
    </BaseDrawer>
</template>
