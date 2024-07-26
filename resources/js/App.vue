<script setup>
    import { onMounted, watch } from 'vue';
    import {RouterView} from 'vue-router'
    import { storeToRefs } from 'pinia';
    import { useAppStore } from './stores/AppStore';

    const appStore = useAppStore();
    const { lightMode } = storeToRefs( appStore );
    const { toggleDarkMode, initDarkMode } = appStore;

    onMounted(async () => {
        initDarkMode();
    });

    watch(lightMode, toggleDarkMode, {immediate: false})
</script>

<template>
    <RouterView/>
</template>