<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';

import SidebarHeader from '@/components/headers/SidebarHeader.vue';

const { loadedSideBar } = storeToRefs(useAppStore());

onMounted(() => {
    loadedSideBar.value = 'transcript';
});
onUnmounted(() => {
    loadedSideBar.value = '';
});
</script>

<template>
    <SidebarHeader :text="'Transcript'" />

    <section id="list-content-transcript" class="full-height-sidebar flex flex-col flex-wrap gap-2"></section>
</template>
