import type { ContextMenu as ContextMenuType, ContextMenuItem, Broadcaster, AppManifest, WaitTimesResponse } from '@/types/types';

import { nextTick, ref, useTemplateRef, watch } from 'vue';
import { useGetManifest, useGetTaskWaitTimes } from '@/service/queries';
import { defineStore } from 'pinia';
import { EchoConfig } from '@/echo.ts';

import ContextMenu from '@/components/pinesUI/ContextMenu.vue';
import Echo from 'laravel-echo';

function booleanToString(val: undefined | boolean) {
    return val ? 'true' : 'false';
}

export const useAppStore = defineStore('App', () => {
    const { data: rawWaitTimes, isLoading: isLoadingWaitTimes } = useGetTaskWaitTimes();
    const { data: rawAppManifest } = useGetManifest();

    const ws = ref<Echo<keyof Broadcaster> | null>(null);

    const selectedSideBar = ref('');
    const sideBarTarget = ref('');
    const pageTitle = ref('');
    const scrollLock = ref(false);

    const usingPlayerModernUI = ref<boolean>();
    const playbackHeatmap = ref<boolean>();
    const ambientMode = ref<boolean>();
    const lightMode = ref<boolean>();

    const isPlaylist = ref<boolean>();
    const isAutoPlay = ref<boolean>(false);

    const contextMenu = useTemplateRef<InstanceType<typeof ContextMenu> | null>('contextMenu');
    const contextMenuItems = ref<ContextMenuItem[]>([]);
    const contextMenuEvent = ref<MouseEvent>();
    const contextMenuItemStyle = ref('');
    const contextMenuStyle = ref('');

    const taskWaitTimes = ref<WaitTimesResponse>({ scan: 0, verify_files: 0, verify_folders: 0 });
    const appManifest = ref<AppManifest>({ version: 'Unversioned', commit: null });

    function toggleDarkMode() {
        const rootHTML = document.querySelector('html');
        if (!rootHTML) return;

        localStorage.setItem('lightMode', booleanToString(lightMode.value));

        if (lightMode.value) {
            rootHTML.classList.remove('dark');
            return;
        }

        rootHTML.classList.add('dark');
    }

    function initDarkMode() {
        const init = lightMode.value === undefined;
        const cachedState = localStorage.getItem('lightMode');

        if (!init) return;

        lightMode.value = cachedState === 'true';
        localStorage.setItem('lightMode', booleanToString(lightMode.value));
    }

    function setAmbientMode() {
        localStorage.setItem('ambientMode', booleanToString(ambientMode.value));
    }

    function initAmbientMode() {
        const init = ambientMode.value === undefined;
        const cachedState = localStorage.getItem('ambientMode');
        if (!init) return;

        ambientMode.value = cachedState === 'true';
        localStorage.setItem('ambientMode', booleanToString(ambientMode.value));
    }

    function setPlaybackHeatmap() {
        localStorage.setItem('playbackHeatmap', booleanToString(playbackHeatmap.value));
    }

    function initPlaybackHeatmap() {
        const init = playbackHeatmap.value === undefined;
        const cachedState = localStorage.getItem('playbackHeatmap');
        if (!init) return;

        playbackHeatmap.value = cachedState === 'true';
        localStorage.setItem('playbackHeatmap', booleanToString(playbackHeatmap.value));
    }

    function initPlayerModernUI() {
        const init = usingPlayerModernUI.value === undefined;
        const cachedState = localStorage.getItem('playerModernUI');
        if (!init) return;

        usingPlayerModernUI.value = cachedState === 'true';
        localStorage.setItem('playerModernUI', booleanToString(usingPlayerModernUI.value));
    }

    function setPlayerModernUI() {
        localStorage.setItem('playerModernUI', booleanToString(usingPlayerModernUI.value));
    }

    function setIsPlaylist() {
        localStorage.setItem('isPlaylist', booleanToString(isPlaylist.value));
    }

    function initIsPlaylist() {
        const init = isPlaylist.value === undefined;
        const cachedState = localStorage.getItem('isPlaylist');
        if (!init) return;

        isPlaylist.value = cachedState === 'true';
        localStorage.setItem('isPlaylist', booleanToString(isPlaylist.value));
    }

    async function cycleSideBar(target = '', scrollTarget: '' | 'left-card' | 'list-card' | 'root' = '', scrollToTarget = true) {
        sideBarTarget.value = scrollTarget;

        if (selectedSideBar.value === target) {
            selectedSideBar.value = '';
            document.getElementById('root')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            return;
        }
        selectedSideBar.value = target;
        if (scrollTarget && scrollToTarget) {
            await nextTick();
            document.getElementById(scrollTarget)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function setScrollLock(state = false) {
        scrollLock.value = state;
    }

    const setContextMenu = (event: MouseEvent, options: ContextMenuType) => {
        if (!options.items || options.items.length === 0) return;
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

    const initBrowserState = () => {
        initDarkMode();
        initAmbientMode();
        initPlaybackHeatmap();
        initIsPlaylist();
        initPlayerModernUI();
    };

    watch(rawAppManifest, (v: any) => {
        appManifest.value = v ?? { version: 'Unversioned', commit: 'unknown' };
    });

    watch(rawWaitTimes, (v: any, prev: WaitTimesResponse) => {
        taskWaitTimes.value = v ?? prev;
    });

    watch(ambientMode, setAmbientMode, { immediate: false });
    watch(lightMode, toggleDarkMode, { immediate: false });
    watch(playbackHeatmap, setPlaybackHeatmap, { immediate: false });
    watch(isPlaylist, setIsPlaylist, { immediate: false });
    watch(usingPlayerModernUI, setPlayerModernUI, { immediate: false });

    return {
        // Browser State
        ambientMode,
        lightMode,
        playbackHeatmap,
        isPlaylist,
        usingPlayerModernUI,
        initBrowserState,

        // Local State
        isAutoPlay,

        // Nav State
        cycleSideBar,
        selectedSideBar,
        sideBarTarget,
        pageTitle,

        // Scroll Lock
        scrollLock,
        setScrollLock,

        // Context Menu State
        contextMenuItems,
        contextMenuStyle,
        contextMenuItemStyle,
        contextMenuEvent,
        setContextMenu,

        // Ws State
        createEcho,
        disconnectEcho,
        ws,

        // Cached Info
        appManifest,
        isLoadingWaitTimes,
        taskWaitTimes,
    };
});
