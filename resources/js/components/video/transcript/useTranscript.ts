import type { SubtitleResource } from '@/contracts/media';
import type { TranscriptLine } from '@/components/video/transcript/transcriptParser';
import type { PlayerViewMode } from '@/components/video/VideoPlayer.vue';
import type { Ref } from 'vue';

import { buildTranscriptUrl, findActiveTranscriptIndex } from '@/components/video/transcript/transcriptUtil.ts';
import { computed, ref, shallowRef, watch } from 'vue';
import { useReactiveBreakpoints } from '@/service/breakpoints/useReactiveBreakpoints';
import { parseTranscript } from '@/components/video/transcript/transcriptParser';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { toast } from '@aminnausin/cedar-ui';

const { selectedSideBar } = storeToRefs(useAppStore());
const { isDesktop } = useReactiveBreakpoints();

const isShowingTranscript = ref(false);
const isLoading = ref(false);

const currentTime = ref(0);
const rawTranscript = ref('');
const loadedTranscriptUrl = ref<string>();

const hasError = ref(false);
const transcriptUrl = computed(() => buildTranscriptUrl(subtitleTrack.value));

let controller: AbortController | null = null;

const canUseSidebar = computed(() => isNormalView.value && isDesktop.value);

const placement = computed<'hidden' | 'sidebar' | 'overlay'>(() => {
    if (!isShowingTranscript.value) return 'hidden';
    return canUseSidebar.value && selectedSideBar.value === 'transcript' ? 'sidebar' : 'overlay';
});

const parsedTranscript = computed<TranscriptLine[]>(() => parseTranscript(rawTranscript.value));

const activeIndex = computed(() => findActiveTranscriptIndex(parsedTranscript.value, currentTime.value));

const isActiveLive = computed(() => {
    const line = parsedTranscript.value[activeIndex.value];
    return !!line && line.end >= currentTime.value;
});

//#region Context

type TranscriptContext = {
    player: Readonly<Ref<HTMLVideoElement | null>>;
    viewMode: Readonly<Ref<PlayerViewMode>>;
    subtitleTrack: Readonly<Ref<SubtitleResource | undefined>>;
    seek: (seconds: number) => void;
    close: () => void;
    generated: (track: SubtitleResource) => void;
};

const context = shallowRef<TranscriptContext | null>(null);

const player = computed(() => context.value?.player.value ?? null);
const isNormalView = computed(() => context.value?.viewMode.value === 'normal');
const subtitleTrack = computed(() => context.value?.subtitleTrack.value);

function registerTranscriptContext(c: TranscriptContext) {
    context.value = c;
}

function unregisterTranscriptContext() {
    context.value = null;
}

function seek(seconds: number) {
    context.value?.seek(seconds);
}

function close() {
    context.value?.close();
}

function generated(track: SubtitleResource) {
    context.value?.generated(track);
}

//#endregion

//#region API

function handleTimeUpdate(this: HTMLVideoElement) {
    currentTime.value = this.currentTime;
}

function reset() {
    controller?.abort();
    controller = null;
    rawTranscript.value = '';
    loadedTranscriptUrl.value = undefined;
    hasError.value = false;
    isLoading.value = false;
}

async function loadTranscript(url: string | undefined, force = false) {
    // const url = buildTranscriptUrl(subtitleTrack.value);

    // if (!url || loadedTranscriptUrl.value === url) return;

    if (!url || (!force && url === loadedTranscriptUrl.value)) return;

    controller?.abort();
    const current = (controller = new AbortController());
    isLoading.value = true;

    try {
        const response = await fetch(url, { signal: current.signal });
        if (!response.ok) throw new Error(`Failed to fetch transcript: ${response.status}`);

        rawTranscript.value = await response.text();
        loadedTranscriptUrl.value = url;
        hasError.value = false;
    } catch (error) {
        if (current.signal.aborted) return;

        hasError.value = true;
        toast.error('Failed to load transcript');
        console.error('Failed to load transcript:', error);
    } finally {
        if (controller === current) isLoading.value = false;
    }
}

function retry() {
    void loadTranscript(transcriptUrl.value, true);
}

//#endregion

watch(player, (newPlayer, oldPlayer) => {
    oldPlayer?.removeEventListener('timeupdate', handleTimeUpdate);
    newPlayer?.addEventListener('timeupdate', handleTimeUpdate);
});

watch(isShowingTranscript, (show) => {
    if (show && canUseSidebar.value) selectedSideBar.value = 'transcript';
    else if (!show && selectedSideBar.value === 'transcript') selectedSideBar.value = '';
});

watch(transcriptUrl, reset);

watch([isShowingTranscript, transcriptUrl], ([show, url]) => show && void loadTranscript(url), { immediate: true });

export function useTranscript() {
    return {
        // transcript
        isShowingTranscript,
        rawTranscript,
        currentTime,
        parsedTranscript,
        activeIndex,
        isActiveLive,
        placement,
        // api
        loadedTranscriptUrl,
        isLoading,
        hasError,
        loadTranscript,
        retry,
        // player-owned state
        player,
        isNormalView,
        subtitleTrack,
        // context setup
        registerTranscriptContext,
        unregisterTranscriptContext,
        // actions
        seek,
        close,
        generated,
    };
}
