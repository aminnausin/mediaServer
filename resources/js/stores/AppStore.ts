import { type ContextMenu as ContextMenuType, type ContextMenuItem, type Broadcaster, type AppManifest } from '@/types/types';
import { nextTick, ref, useTemplateRef, watch } from 'vue';
import { useGetManifest } from '@/service/queries';
import { defineStore } from 'pinia';
import { EchoConfig } from '@/echo.ts';

import ContextMenu from '@/components/pinesUI/ContextMenu.vue';
import Echo from 'laravel-echo';

export const useAppStore = defineStore('App', () => {
    const ws = ref<Echo<keyof Broadcaster> | null>(null);

    const pageTitle = ref('');
    const lightMode = ref<null | boolean>(null);
    const ambientMode = ref<null | boolean>(null);
    const playbackHeatmap = ref<null | boolean>(null);
    const isAutoPlay = ref<boolean>(false);
    const selectedSideBar = ref('');
    const sideBarTarget = ref('');
    const scrollLock = ref(false);

    const contextMenuItems = ref<ContextMenuItem[]>([]);
    const contextMenuStyle = ref('');
    const contextMenuItemStyle = ref('');
    const contextMenuEvent = ref<MouseEvent>();

    const contextMenu = useTemplateRef<InstanceType<typeof ContextMenu> | null>('contextMenu');

    const { data: rawAppManifest } = useGetManifest();
    const appManifest = ref<AppManifest>({ version: 'Unversioned', commit: null });

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

    async function cycleSideBar(target = '', scrollTarget: '' | 'left-card' | 'list-card' | 'root' = '') {
        sideBarTarget.value = scrollTarget;

        if (selectedSideBar.value === target) {
            selectedSideBar.value = '';
            document.getElementById('root')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            return;
        }
        selectedSideBar.value = target;
        if (scrollTarget) {
            await nextTick();
            document.getElementById(scrollTarget)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function setScrollLock(state = false) {
        scrollLock.value = state;
    }

    function booleanToString(val: null | boolean) {
        return val ? 'true' : 'false';
    }

    const setContextMenu = (event: MouseEvent, options: ContextMenuType) => {
        contextMenuEvent.value = event;
        contextMenuItems.value = options.items ?? contextMenuItems.value;
        contextMenuStyle.value = options.style ?? '';
        contextMenuItemStyle.value = options.itemStyle ?? '';

        if (contextMenu.value) contextMenu.value.contextMenuToggle(event);
    };

    const createEcho = () => {
        if (!ws.value) {
            ws.value = new Echo(EchoConfig);
            window.Echo = ws.value;
        }
        return ws;
    };
    const disconnectEcho = () => {
        if (ws.value) {
            ws.value.disconnect();
            ws.value = null;
            window.Echo = null;
        }
    };

    watch(rawAppManifest, (v: any) => {
        appManifest.value = v ?? { version: 'Unversioned', commit: 'unknown' };
    });

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
        contextMenuItems,
        contextMenuStyle,
        contextMenuItemStyle,
        contextMenuEvent,
        setContextMenu,
        createEcho,
        disconnectEcho,
        isAutoPlay,
        ws,
        appManifest,
    };
});
