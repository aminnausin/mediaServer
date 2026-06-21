import type { ContextMenu as ContextMenuType, ContextMenuItem, Broadcaster, AppManifest, WaitTimesResponse } from '@/types/types';

import { nextTick, ref, useTemplateRef, watch } from 'vue';
import { useGetManifest, useGetTaskWaitTimes } from '@/service/queries';
import { ContextMenu } from '@/components/cedar-ui/context-menu';
import { defineStore } from 'pinia';
import { EchoConfig } from '@/echo.ts';
import { FLAGS } from '@/config/featureFlags';

import Echo from 'laravel-echo';

function booleanToString(val: undefined | boolean) {
    return val ? 'true' : 'false';
}

function usePersisted<T extends boolean>(key: string, defaultValue: T) {
    const state = ref<boolean>(defaultValue);

    function init() {
        const cached = localStorage.getItem(key);
        state.value = cached === null ? defaultValue : cached === 'true';
        localStorage.setItem(key, booleanToString(state.value));
    }

    function persist(value: boolean | undefined) {
        localStorage.setItem(key, booleanToString(value));
    }

    return { state, init, persist };
}

export const useAppStore = defineStore('App', () => {
    const { data: rawWaitTimes, isLoading: isLoadingWaitTimes } = useGetTaskWaitTimes();
    const { data: rawAppManifest } = useGetManifest();

    const ws = ref<Echo<keyof Broadcaster> | null>(null);

    const selectedSideBar = ref('');
    const sideBarTarget = ref('');
    const pageTitle = ref('');
    const scrollLock = ref(false);

    const isAutoPlay = ref<boolean>(false);

    const contextMenu = useTemplateRef<InstanceType<typeof ContextMenu> | null>('contextMenu');
    const contextMenuItems = ref<ContextMenuItem[]>([]);
    const contextMenuEvent = ref<MouseEvent>();
    const contextMenuItemStyle = ref('');
    const contextMenuStyle = ref('');

    const taskWaitTimes = ref<WaitTimesResponse>({ scan: 0, verify_files: 0, verify_folders: 0 });
    const appManifest = ref<AppManifest>({ version: 'Unversioned', commit: null });

    //#region User Preferences

    // Legacy
    const lightMode = ref<boolean>();

    // New
    const isPlaylist = usePersisted('isPlaylist', false);
    const showAutoSubtitles = usePersisted('showAutoSubtitles', true);
    const showPlaybackHeatmap = usePersisted('showPlaybackHeatmap', false);
    const showSeekButtons = usePersisted('showSeekButtons', false);
    const showAudioGraph = usePersisted('showAudioGraph', false);
    const isAmbientMode = usePersisted('ambientMode', false);
    const showModernUI = usePersisted('showModernUI', true);

    //#endregion

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

        contextMenu.value?.contextMenuToggle(event);
    };

    //#region WebSockets

    const createEcho = () => {
        if (!ws.value) {
            ws.value = new Echo(EchoConfig);
            window.Echo = ws.value;
        }

        return ws;
    };

    const disconnectEcho = () => {
        if (!ws.value) return;
        ws.value.disconnect();
        ws.value = null;
        window.Echo = null;
    };

    //#endregion

    watch(rawAppManifest, (v: any) => {
        appManifest.value = v ?? { version: 'Unversioned', commit: 'unknown' };
    });

    watch(rawWaitTimes, (v: any, prev: WaitTimesResponse) => {
        taskWaitTimes.value = v ?? prev;
    });

    //#region User preference init and watchers

    const initBrowserState = () => {
        initDarkMode();

        showPlaybackHeatmap.init();
        showAutoSubtitles.init();
        showSeekButtons.init();
        showAudioGraph.init();
        showModernUI.init();

        isPlaylist.init();
        isAmbientMode.init();
    };

    watch(lightMode, toggleDarkMode, { immediate: false });

    watch(showPlaybackHeatmap.state, showPlaybackHeatmap.persist);
    watch(showAutoSubtitles.state, showAutoSubtitles.persist);
    watch(showSeekButtons.state, showSeekButtons.persist);
    watch(showAudioGraph.state, showAudioGraph.persist);
    watch(showModernUI.state, (value) => {
        showModernUI.persist(FLAGS.FORCE_MODERN_PLAYER_UI ?? value);
    });

    watch(isAmbientMode.state, isAmbientMode.persist);
    watch(isPlaylist.state, isPlaylist.persist);

    //#endregion

    return {
        // Browser State
        ambientMode: isAmbientMode.state, // should be isAmbientModeEnabled?
        lightMode, // should be isLightMode
        playbackHeatmap: showPlaybackHeatmap.state,
        isPlaylist: isPlaylist.state,
        usingPlayerModernUI: showModernUI.state, // should be usePlayerModernUi
        isAudioGraphEnabled: showAudioGraph.state,
        showAutoSubtitles: showAutoSubtitles.state,
        showSeekButtons: showSeekButtons.state,
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
