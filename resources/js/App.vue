<script setup>
    import { onMounted, watch } from 'vue';
    import {RouterView} from 'vue-router'
    import { storeToRefs } from 'pinia';
    import { useAppStore } from './stores/AppStore';

    import ToastRoot from './components/pinesUI/ToastRoot.vue';

    const appStore = useAppStore();
    const { lightMode } = storeToRefs( appStore );
    const { toggleDarkMode, initDarkMode } = appStore;

    onMounted(async () => {
        initDarkMode();
    });

    watch(lightMode, toggleDarkMode, {immediate: false})
</script>

<template>
    <ToastRoot :position="'bottom-left'">
        <template v-slot:app="{ handleSetLayout, handleToastShow }">
            <RouterView @set-toasts-layout="(event) => {handleSetLayout(event)}" @toast-show="(event) => {handleToastShow(event)}"/>
        </template>
    </ToastRoot>
</template>