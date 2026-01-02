<script setup lang="ts">
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';

import HistorySidebar from '@/components/panels/HistorySidebar.vue';
import FolderSidebar from '@/components/panels/FolderSidebar.vue';

const { selectedSideBar } = storeToRefs(useAppStore());
</script>

<template>
    <FolderSidebar v-if="selectedSideBar === 'folders'" />
    <HistorySidebar v-if="selectedSideBar === 'history'" />
</template>
