<script setup>
    import { onMounted, watch } from 'vue';
    import {RouterView} from 'vue-router'
    import { storeToRefs } from 'pinia';
    import { useAppStore } from './stores/AppStore';
    // import { getCSRF } from './service/auth';

    const appStore = useAppStore();
    const { lightMode } = storeToRefs( appStore );
    const { toggleDarkMode, initDarkMode } = appStore;

    onMounted(async () => {
        // getCSRF();
        initDarkMode();
    });

    watch(lightMode, toggleDarkMode, {immediate: false})
</script>

<template>
    <RouterView/>
</template>