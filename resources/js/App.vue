<script setup lang="ts">
import ToastController from '@/components/pinesUI/ToastController.vue';

import { onMounted, watch } from 'vue';
import { RouterView } from 'vue-router';
import { storeToRefs } from 'pinia';
import { useAppStore } from '@/stores/AppStore';

const appStore = useAppStore();
const { lightMode, ambientMode, playbackHeatmap } = storeToRefs(appStore);
const { toggleDarkMode, initDarkMode, initAmbientMode, setAmbientMode, initPlaybackHeatmap, setPlaybackHeatmap } = appStore;

onMounted(async () => {
    initDarkMode();
    initAmbientMode();
    initPlaybackHeatmap();
});

watch(ambientMode, setAmbientMode, { immediate: false });
watch(lightMode, toggleDarkMode, { immediate: false });
watch(playbackHeatmap, setPlaybackHeatmap, { immediate: false });
</script>

<template>
    <ToastController />
    <RouterView />
</template>
