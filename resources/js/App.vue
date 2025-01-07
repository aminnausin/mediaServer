<script setup lang="ts">
import type { ToastPostion } from './types/pinesTypes';

import { onMounted, ref, useTemplateRef, watch } from 'vue';
import { getScreenSize } from './service/util';
import { storeToRefs } from 'pinia';
import { useAppStore } from '@/stores/AppStore';
import { RouterView } from 'vue-router';

import ToastController from '@/components/pinesUI/ToastController.vue';
import ContextMenu from '@/components/pinesUI/ContextMenu.vue';

const appStore = useAppStore();
const toastPosition = ref<ToastPostion>();
const { lightMode, ambientMode, playbackHeatmap, contextMenuItems, contextMenuStyle, contextMenuItemStyle, contextMenuEvent } = storeToRefs(appStore);
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
    <RouterView />
    <ContextMenu ref="contextMenu" :items="contextMenuItems" :style="contextMenuStyle" :itemStyle="contextMenuItemStyle ?? 'hover:bg-purple-600 hover:text-white'" />
</template>
