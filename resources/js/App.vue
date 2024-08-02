<script setup>
    import { onMounted, watch } from 'vue';
    import {RouterView} from 'vue-router'
    import { storeToRefs } from 'pinia';
    import { useAppStore } from './stores/AppStore';

    import PrimeToast from './components/primevue/PrimeToast.vue';

    const appStore = useAppStore();
    const { lightMode } = storeToRefs( appStore );
    const { toggleDarkMode, initDarkMode } = appStore;

    onMounted(async () => {
        initDarkMode();
    });

    watch(lightMode, toggleDarkMode, {immediate: false})
</script>

<template>
    <PrimeToast/>
    <RouterView/>
</template>