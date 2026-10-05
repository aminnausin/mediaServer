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
import { API } from '@/service/api';

const { selectedSideBar } = storeToRefs(useAppStore());
const { isDesktop } = useReactiveBreakpoints();

let controller: AbortController | null = null;

const SEARCH_TAGS = /<\/?[a-z][^>]*>/gi;

const generationStatus = ref<'idle' | 'requesting' | 'queued' | 'processing' | 'error'>('idle');

const isShowingTranscript = ref(false);
const isLoading = ref(false);
const hasError = ref(false);

const transcriptUrl = computed(() => buildTranscriptUrl(subtitleTrack.value));
const loadedTranscriptUrl = ref<string>();
const rawTranscript = ref('');

const canUseSidebar = computed(() => isNormalView.value && isDesktop.value);

const currentTime = ref(0);

const searchQuery = ref('');

const searchIndex = computed(() => parsedTranscript.value.map((l) => l.text.replace(SEARCH_TAGS, '').toLowerCase()));

const placement = computed<'hidden' | 'sidebar' | 'overlay'>(() => {
    if (!isShowingTranscript.value) return 'hidden';
    return canUseSidebar.value && selectedSideBar.value === 'transcript' ? 'sidebar' : 'overlay';
});

const parsedTranscript = computed<TranscriptLine[]>(() => parseTranscript(rawTranscript.value));

const filteredTranscript = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    if (!q || placement.value === 'overlay') return parsedTranscript.value;
    return parsedTranscript.value.filter((l) => searchIndex.value[l.index].includes(q));
});

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
    metadataId: Readonly<Ref<number | undefined>>;
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

//#region Events
function handleTimeUpdate(this: HTMLVideoElement) {
    currentTime.value = this.currentTime;
}

watch(player, (newPlayer, oldPlayer) => {
    oldPlayer?.removeEventListener('timeupdate', handleTimeUpdate);
    newPlayer?.addEventListener('timeupdate', handleTimeUpdate);
});
//#endregion

//#region API

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

async function requestTranscript() {
    const id = context.value?.metadataId.value;
    if (!id || generationStatus.value === 'processing') return;

    generationStatus.value = 'processing';
    try {
        const { data } = await API.post(`/metadata/${id}/transcript`, undefined, { headers: { 'X-Skip-Toast': true } });
        if (context.value?.metadataId.value !== id) {
            console.error('Selected video changed while loading transcript.', { old: id, new: context.value?.metadataId });
            return;
        }
        generated(data.subtitle as SubtitleResource);
        generationStatus.value = 'idle';
    } catch {
        generationStatus.value = 'error';
    }
}

watch(
    () => context.value?.metadataId.value,
    () => (generationStatus.value = 'idle'),
);

//#endregion

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
        parsedTranscript,
        filteredTranscript,
        activeIndex,
        isActiveLive,
        placement,
        searchQuery,
        // api
        isLoading,
        hasError,
        retry,
        // generator
        generationStatus,
        requestTranscript,
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
