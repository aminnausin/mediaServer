<script setup lang="ts">
import type { ToastPostion } from '@aminnausin/cedar-ui';

import { DrawerController } from './components/cedar-ui/drawer';
import { ToastController } from '@/components/cedar-ui/toast';
import { onMounted, ref } from 'vue';
import { useFullscreen } from '@/composables/useFullscreen';
import { getScreenSize } from '@/service/util';
import { useAuthStore } from '@/stores/AuthStore';
import { ContextMenu } from '@/components/cedar-ui/context-menu';
import { GlobalModal } from '@/components/cedar-ui/modal';
import { storeToRefs } from 'pinia';
import { useAppStore } from '@/stores/AppStore';
import { RouterView } from 'vue-router';

const { contextMenuItems, contextMenuStyle, contextMenuItemStyle } = storeToRefs(useAppStore());
const { isFullscreen } = useFullscreen();

const authStore = useAuthStore();

const toastPosition = ref<ToastPostion>();

async function loadUser() {
    await authStore.fetchUser();
}

onMounted(() => {
    useAppStore().initBrowserState();

    toastPosition.value = getScreenSize() === 'default' ? 'top-center' : 'bottom-left';
    loadUser();
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
