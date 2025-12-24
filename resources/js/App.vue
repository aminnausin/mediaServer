<script setup lang="ts">
import type { ToastPostion } from '@aminnausin/cedar-ui';

import { onMounted, onUnmounted, ref, watch } from 'vue';
import { DrawerController } from './components/cedar-ui/drawer';
import { ToastController } from '@/components/cedar-ui/toast';
import { useFullscreen } from '@/composables/useFullscreen';
import { getScreenSize } from '@/service/util';
import { useAuthStore } from '@/stores/AuthStore';
import { storeToRefs } from 'pinia';
import { useAppStore } from '@/stores/AppStore';
import { RouterView } from 'vue-router';

import ContextMenu from '@/components/pinesUI/ContextMenu.vue';
import GlobalModal from '@/components/modals/GlobalModal.vue';

const toastPosition = ref<ToastPostion>();

const {
    toggleDarkMode,
    initDarkMode,
    initAmbientMode,
    initPlaybackHeatmap,
    initIsPlaylist,
    initPlayerModernUI,
    setAmbientMode,
    setPlaybackHeatmap,
    setIsPlaylist,
    setPlayerModernUI,
} = useAppStore();
const { lightMode, ambientMode, playbackHeatmap, contextMenuItems, contextMenuStyle, contextMenuItemStyle, isPlaylist, usingPlayerModernUI } = storeToRefs(useAppStore());
const { isFullscreen } = useFullscreen();

async function loadUser() {
    const authStore = useAuthStore();
    await authStore.fetchUser();
}

onMounted(async () => {
    initDarkMode();
    initAmbientMode();
    initPlaybackHeatmap();
    initIsPlaylist();
    initPlayerModernUI();

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
watch(usingPlayerModernUI, setPlayerModernUI, { immediate: false });
</script>

<template>
    <RouterView />
    <GlobalModal />
    <DrawerController :teleportTarget="'body'" />
    <ToastController v-if="toastPosition && !isFullscreen" :position="toastPosition" />
    <ContextMenu
        v-if="!isFullscreen"
        ref="contextMenu"
        :items="contextMenuItems"
        :style="contextMenuStyle"
        :itemStyle="contextMenuItemStyle ?? 'hover:bg-purple-600 hover:text-white'"
        scrollContainer="body"
    />
</template>
