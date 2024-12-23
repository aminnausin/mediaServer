import { nextTick, ref } from 'vue';
import { defineStore } from 'pinia';

export const useAppStore = defineStore('App', () => {
    const pageTitle = ref('');
    const lightMode = ref<null | boolean>(null);
    const ambientMode = ref<null | boolean>(null);
    const playbackHeatmap = ref<null | boolean>(null);
    const selectedSideBar = ref('');
    const sideBarTarget = ref('');
    const scrollLock = ref(false);

    function toggleDarkMode() {
        let rootHTML = document.querySelector('html');

        localStorage.setItem('lightMode', booleanToString(lightMode.value));
        lightMode.value ? rootHTML?.classList.remove('dark', 'tw-dark') : rootHTML?.classList.add('dark', 'tw-dark');
    }

    function initDarkMode() {
        let init = lightMode.value === null;
        let cachedState = localStorage.getItem('lightMode');
        if (!init) return;

        lightMode.value = cachedState === 'true';
        localStorage.setItem('lightMode', booleanToString(lightMode.value));
    }

    function setAmbientMode() {
        localStorage.setItem('ambientMode', booleanToString(ambientMode.value));
    }

    function initAmbientMode() {
        let init = ambientMode.value === null;
        let cachedState = localStorage.getItem('ambientMode');
        if (!init) return;

        ambientMode.value = cachedState === 'true';
        localStorage.setItem('ambientMode', booleanToString(ambientMode.value));
    }

    function setPlaybackHeatmap() {
        localStorage.setItem('playbackHeatmap', booleanToString(playbackHeatmap.value));
    }

    function initPlaybackHeatmap() {
        let init = playbackHeatmap.value === null;
        let cachedState = localStorage.getItem('playbackHeatmap');
        if (!init) return;

        playbackHeatmap.value = cachedState === 'true';
        localStorage.setItem('playbackHeatmap', booleanToString(playbackHeatmap.value));
    }

    async function cycleSideBar(target = '', scrollTarget: '' | '#left-card' | '#list-card' | 'root' = '') {
        sideBarTarget.value = scrollTarget;

        if (selectedSideBar.value === target) {
            selectedSideBar.value = '';
            document.getElementById('root')?.scrollIntoView({ behavior: 'smooth' });
            return;
        }
        selectedSideBar.value = target;
        if (scrollTarget) {
            await nextTick();
            document.getElementById(scrollTarget)?.scrollIntoView({ behavior: 'smooth' });
        }
    }

    function setScrollLock(state = false) {
        scrollLock.value = state;
    }

    function booleanToString(val: null | boolean) {
        return val ? 'true' : 'false';
    }

    return {
        initDarkMode,
        toggleDarkMode,
        lightMode,
        initAmbientMode,
        setAmbientMode,
        ambientMode,
        initPlaybackHeatmap,
        setPlaybackHeatmap,
        playbackHeatmap,
        cycleSideBar,
        selectedSideBar,
        sideBarTarget,
        pageTitle,
        scrollLock,
        setScrollLock,
    };
});
