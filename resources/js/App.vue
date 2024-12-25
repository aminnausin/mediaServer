<script setup lang="ts">
import type { ToastPostion } from './types/pinesTypes';

import { onMounted, ref, watch } from 'vue';
import { getScreenSize } from './service/util';
import { storeToRefs } from 'pinia';
import { useAppStore } from '@/stores/AppStore';
import { RouterView } from 'vue-router';
import { Toaster } from 'vue-sonner';

import ToastController from '@/components/pinesUI/ToastController.vue';

const appStore = useAppStore();
const toastPosition = ref<ToastPostion>();
const { lightMode, ambientMode, playbackHeatmap } = storeToRefs(appStore);
const { toggleDarkMode, initDarkMode, initAmbientMode, setAmbientMode, initPlaybackHeatmap, setPlaybackHeatmap } = appStore;

onMounted(async () => {
    initDarkMode();
    initAmbientMode();
    initPlaybackHeatmap();
    const screenSize = getScreenSize();

    if (screenSize === 'default') {
        toastPosition.value = 'top-center';
    } else {
        toastPosition.value = 'bottom-left';
    }
});

watch(ambientMode, setAmbientMode, { immediate: false });
watch(lightMode, toggleDarkMode, { immediate: false });
watch(playbackHeatmap, setPlaybackHeatmap, { immediate: false });
</script>

<template>
    <ToastController v-if="toastPosition" :position="toastPosition" />
    <Toaster />
    <RouterView />
</template>
