<script setup>
import ToastController from './components/pinesUI/ToastController.vue';

import { onMounted, watch } from 'vue';
import { RouterView } from 'vue-router';
import { storeToRefs } from 'pinia';
import { useAppStore } from './stores/AppStore';

const appStore = useAppStore();
const { lightMode, ambientMode } = storeToRefs(appStore);
const { toggleDarkMode, initDarkMode, initAmbientMode, setAmbientMode } = appStore;

onMounted(async () => {
    initDarkMode();
    initAmbientMode();
});

watch(() => ambientMode.value, setAmbientMode, { immediate: false });
watch(lightMode, toggleDarkMode, { immediate: false });
</script>

<template>
    <ToastController />
    <RouterView />
</template>
