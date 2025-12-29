<script setup lang="ts">
import type { ToastPostion } from '@aminnausin/cedar-ui';

import { onMounted, onUnmounted, ref } from 'vue';
import { DrawerController } from './components/cedar-ui/drawer';
import { ToastController } from '@/components/cedar-ui/toast';
import { useFullscreen } from '@/composables/useFullscreen';
import { getScreenSize } from '@/service/util';
import { useAuthStore } from '@/stores/AuthStore';
import { ContextMenu } from '@/components/cedar-ui/context-menu';
import { storeToRefs } from 'pinia';
import { useAppStore } from '@/stores/AppStore';
import { RouterView } from 'vue-router';

import GlobalModal from '@/components/modals/GlobalModal.vue';

const toastPosition = ref<ToastPostion>();

const { contextMenuItems, contextMenuStyle, contextMenuItemStyle } = storeToRefs(useAppStore());
const { isFullscreen } = useFullscreen();

async function loadUser() {
    const authStore = useAuthStore();
    await authStore.fetchUser();
}

onMounted(async () => {
    useAppStore().initBrowserState();

    toastPosition.value = getScreenSize() === 'default' ? 'top-center' : 'bottom-left';
    loadUser();

    window.addEventListener('focus', loadUser);
});

onUnmounted(() => {
    window.removeEventListener('focus', loadUser);
});
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
        :itemStyle="contextMenuItemStyle ?? 'hover:bg-primary hover:text-white'"
        scrollContainer="body"
    />
</template>
