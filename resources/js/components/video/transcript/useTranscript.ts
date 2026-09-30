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

async function loadTranscript() {
    const url = buildTranscriptUrl(subtitleTrack.value);

    if (!url || loadedTranscriptUrl.value === url) return;

    try {
        isLoading.value = true;

        const response = await fetch(url);

        if (!response.ok) throw new Error(`Failed to fetch transcript: ${response.status}`);

        rawTranscript.value = await response.text();
        loadedTranscriptUrl.value = url;
    } catch (error) {
        toast.error('Failed to load transcript');
        console.error('Failed to load transcript:', error);
    } finally {
        isLoading.value = false;
    }
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

export function useTranscript() {
    return {
        // transcript
        isShowingTranscript,
        rawTranscript,
        currentTime,
        isLoading,
        loadedTranscriptUrl,
        parsedTranscript,
        activeIndex,
        isActiveLive,
        loadTranscript,
        placement,
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
