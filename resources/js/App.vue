<script setup lang="ts">
import type { ToastPostion } from '@/types/pinesTypes';

import { onMounted, onUnmounted, ref, watch } from 'vue';
import { getScreenSize } from '@/service/util';
import { useAuthStore } from '@/stores/AuthStore';
import { storeToRefs } from 'pinia';
import { useAppStore } from '@/stores/AppStore';
import { RouterView } from 'vue-router';

import ToastController from '@/components/pinesUI/ToastController.vue';
import ContextMenu from '@/components/pinesUI/ContextMenu.vue';
import GlobalModal from '@/components/modals/GlobalModal.vue';

const toastPosition = ref<ToastPostion>();

const { toggleDarkMode, initDarkMode, initAmbientMode, initPlaybackHeatmap, initIsPlaylist, setAmbientMode, setPlaybackHeatmap, setIsPlaylist } = useAppStore();
const { lightMode, ambientMode, playbackHeatmap, contextMenuItems, contextMenuStyle, contextMenuItemStyle, isPlaylist } = storeToRefs(useAppStore());

async function loadUser() {
    const authStore = useAuthStore();
    await authStore.fetchUser();
}

onMounted(async () => {
    initDarkMode();
    initAmbientMode();
    initPlaybackHeatmap();
    initIsPlaylist();

    toastPosition.value = getScreenSize() === 'default' ? 'top-center' : 'bottom-left';
    loadUser();

    window.addEventListener('focus', loadUser);
});

onUnmounted(() => {
    window.removeEventListener('focus', loadUser);
});

watch(ambientMode, setAmbientMode, { immediate: false });
watch(lightMode, toggleDarkMode, { immediate: false });
watch(playbackHeatmap, setPlaybackHeatmap, { immediate: false });
watch(isPlaylist, setIsPlaylist, { immediate: false });
</script>

<template>
    <ToastController v-if="toastPosition" :position="toastPosition" />
    <RouterView />
    <GlobalModal />
    <ContextMenu
        ref="contextMenu"
        :items="contextMenuItems"
        :style="contextMenuStyle"
        :itemStyle="contextMenuItemStyle ?? 'hover:bg-purple-600 hover:text-white'"
        scrollContainer="body"
    />
</template>
