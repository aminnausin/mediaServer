import { onMounted, onUnmounted, ref } from 'vue';

export function useFullscreen() {
    const isFullscreen = ref(!!document.fullscreenElement);

    const update = () => (isFullscreen.value = !!document.fullscreenElement);
    onMounted(() => document.addEventListener('fullscreenchange', update));
    onUnmounted(() => document.removeEventListener('fullscreenchange', update));

    return { isFullscreen };
}
