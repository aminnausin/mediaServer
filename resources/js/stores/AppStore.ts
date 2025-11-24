import type { ContextMenu as ContextMenuType, ContextMenuItem, Broadcaster, AppManifest, WaitTimesResponse } from '@/types/types';

import { nextTick, ref, useTemplateRef, watch } from 'vue';
import { useGetManifest, useGetTaskWaitTimes } from '@/service/queries';
import { defineStore } from 'pinia';
import { EchoConfig } from '@/echo.ts';

import ContextMenu from '@/components/pinesUI/ContextMenu.vue';
import Echo from 'laravel-echo';

export const useAppStore = defineStore('App', () => {
    const { data: rawWaitTimes, isLoading: isLoadingWaitTimes } = useGetTaskWaitTimes();
    const { data: rawAppManifest } = useGetManifest();

    const ws = ref<Echo<keyof Broadcaster> | null>(null);

    const pageTitle = ref('');
    const lightMode = ref<null | boolean>(null);
    const ambientMode = ref<null | boolean>(null);
    const playbackHeatmap = ref<null | boolean>(null);
    const isPlaylist = ref<null | boolean>(null);
    const isAutoPlay = ref<boolean>(false);
    const selectedSideBar = ref('');
    const sideBarTarget = ref('');
    const scrollLock = ref(false);

    const contextMenu = useTemplateRef<InstanceType<typeof ContextMenu> | null>('contextMenu');
    const contextMenuItems = ref<ContextMenuItem[]>([]);
    const contextMenuEvent = ref<MouseEvent>();
    const contextMenuItemStyle = ref('');
    const contextMenuStyle = ref('');

    const appManifest = ref<AppManifest>({ version: 'Unversioned', commit: null });
    const taskWaitTimes = ref<WaitTimesResponse>({ scan: 0, verify_files: 0, verify_folders: 0 });

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
        const init = lightMode.value === null;
        const cachedState = localStorage.getItem('lightMode');
        if (!init) return;

        lightMode.value = cachedState === 'true';
        localStorage.setItem('lightMode', booleanToString(lightMode.value));
    }

    function setAmbientMode() {
        localStorage.setItem('ambientMode', booleanToString(ambientMode.value));
    }

    function initAmbientMode() {
        const init = ambientMode.value === null;
        const cachedState = localStorage.getItem('ambientMode');
        if (!init) return;

        ambientMode.value = cachedState === 'true';
        localStorage.setItem('ambientMode', booleanToString(ambientMode.value));
    }

    function setPlaybackHeatmap() {
        localStorage.setItem('playbackHeatmap', booleanToString(playbackHeatmap.value));
    }

    function initPlaybackHeatmap() {
        const init = playbackHeatmap.value === null;
        const cachedState = localStorage.getItem('playbackHeatmap');
        if (!init) return;

        playbackHeatmap.value = cachedState === 'true';
        localStorage.setItem('playbackHeatmap', booleanToString(playbackHeatmap.value));
    }

    function setIsPlaylist() {
        localStorage.setItem('isPlaylist', booleanToString(isPlaylist.value));
    }

    function initIsPlaylist() {
        const init = isPlaylist.value === null;
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

    function booleanToString(val: null | boolean) {
        return val ? 'true' : 'false';
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

    watch(rawAppManifest, (v: any) => {
        appManifest.value = v ?? { version: 'Unversioned', commit: 'unknown' };
    });

    watch(rawWaitTimes, (v: any, prev: WaitTimesResponse) => {
        taskWaitTimes.value = v ?? prev;
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
        initIsPlaylist,
        setIsPlaylist,
        isPlaylist,
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
        appManifest,
        ws,
        isLoadingWaitTimes,
        taskWaitTimes,
    };
});
