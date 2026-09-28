import type { SubtitleResource } from '@/contracts/media';
import type { PlayerViewMode } from '@/components/video/VideoPlayer.vue';

import { computed, ref, shallowRef, watch } from 'vue';
import { toast } from '@aminnausin/cedar-ui';

export interface TranscriptLine {
    index: number;
    text: string;
    start: number;
    end: number;
}

const ALLOWED_TAG_PATTERN = /<(?!\/?(?:i|u|br)\b)[^>]*>/gi;

const player = shallowRef<HTMLVideoElement | null>(null);
const playerViewMode = ref<PlayerViewMode>('normal');
const isNormalView = computed(() => playerViewMode.value === 'normal');
const subtitleTrack = ref<SubtitleResource>();

const rawTranscript = ref('');
const currentTime = ref(0);
const isLoading = ref(false);
const loadedTranscriptUrl = ref<string>();

const parsedTranscript = computed<TranscriptLine[]>(() => {
    const lines: TranscriptLine[] = [];

    const vtt = rawTranscript.value.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
    const cueLines = vtt.split('\n');

    for (let i = 0; i < cueLines.length; i++) {
        const line = cueLines[i].trim();
        if (!line.includes(' --> ')) continue;

        const [start, end] = line.split(' --> ');
        const textLines: string[] = [];
        i++;

        while (i < cueLines.length && cueLines[i].trim() !== '') {
            textLines.push(cueLines[i]);
            i++;
        }

        const startTime = timestampToSeconds(start);
        const endTime = timestampToSeconds(end);
        const text = textLines.join('\n').trim().replace(ALLOWED_TAG_PATTERN, '');

        if (!text) continue;

        const previous = lines[lines.length - 1];
        if (previous && previous.text === text && startTime <= previous.end) {
            previous.end = Math.max(previous.end, endTime);
            continue;
        }

        lines.push({ start: startTime, end: endTime, text, index: lines.length });
    }
    return lines;
});

const activeIndex = computed(() => {
    const lines = parsedTranscript.value;
    const time = currentTime.value;

    let low = 0;
    let high = lines.length - 1;

    while (low <= high) {
        const mid = (low + high) >> 1;
        const line = lines[mid];
        const next = lines[mid + 1];

        if (time < line.start) high = mid - 1;
        else if (!next || time < next.start) return mid;
        else low = mid + 1;
    }

    return -1;
});

function timestampToSeconds(timestamp: string): number {
    const parts = timestamp.trim().split(':').map(Number);
    if (parts.length === 2) return parts[0] * 60 + parts[1];
    if (parts.length === 3) return parts[0] * 3600 + parts[1] * 60 + parts[2];
    return 0;
}

function buildTranscriptUrl(subtitle?: SubtitleResource): string | undefined {
    if (!subtitle) return undefined;

    const { metadata_uuid, track_id, language } = subtitle;
    const languageSlug = track_id === 0 ? `.${language}` : '';

    return `/data/subtitles/${metadata_uuid}/${track_id}${languageSlug}.vtt`;
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

type TranscriptHandlers = {
    seek: (seconds: number) => void;
    close: () => void;
    generated: (track: SubtitleResource) => void;
};

let handlers: TranscriptHandlers | null = null;

function registerTranscriptContext(h: TranscriptHandlers) {
    handlers = h;
}

function unregisterTranscriptContext() {
    handlers = null;
    player.value = null;
}

function seek(seconds: number) {
    handlers?.seek(seconds);
}

function close() {
    handlers?.close();
}

function generated(track: SubtitleResource) {
    handlers?.generated(track);
}

function handleTimeUpdate(this: HTMLVideoElement) {
    currentTime.value = this.currentTime;
}

watch(player, (newPlayer, oldPlayer) => {
    oldPlayer?.removeEventListener('timeupdate', handleTimeUpdate);
    newPlayer?.addEventListener('timeupdate', handleTimeUpdate);
});

export function useTranscript() {
    return {
        // transcript
        rawTranscript,
        currentTime,
        isLoading,
        loadedTranscriptUrl,
        parsedTranscript,
        activeIndex,
        loadTranscript,
        // player-owned state
        player,
        playerViewMode,
        isNormalView,
        subtitleTrack,
        // owner registration (VideoPlayer.vue calls these)
        registerTranscriptContext,
        unregisterTranscriptContext,
        // consumer actions (PlayerTranscript.vue / TranscriptSidebar.vue call these)
        seek,
        close,
        generated,
    };
}
