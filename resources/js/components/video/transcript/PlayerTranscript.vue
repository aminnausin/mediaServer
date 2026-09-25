<script setup lang="ts">
import type { SubtitleResource } from '@/contracts/media';

import { computed, nextTick, onUnmounted, ref, useTemplateRef, watch, type ComponentPublicInstance } from 'vue';
import { ButtonCorner } from '@/components/cedar-ui/button';

import ProiconsCancel from '~icons/proicons/cancel';

const props = defineProps<{ isVisible: boolean; player: HTMLVideoElement | null; subtitleTrack?: SubtitleResource }>();

const emit = defineEmits<{ seek: [value: number]; close: [] }>();

const currentTime = ref(0);
const rawTranscript = ref('');
const isLoading = ref(false);

const transcriptContainer = useTemplateRef('transcript-container');

interface TranscriptLine {
    index: number;
    text: string;
    timestamp: number;
}

const ALLOWED_TAG_PATTERN = /<(?!\/?(?:i|u|br)\b)[^>]*>/gi;

const parsedTranscript = computed<TranscriptLine[]>(() => {
    const lines: TranscriptLine[] = [];

    const vtt = rawTranscript.value.replace(/\r\n/g, '\n').replace(/\r/g, '\n');

    const cueLines = vtt.split('\n');

    for (let i = 0; i < cueLines.length; i++) {
        const line = cueLines[i].trim();

        if (!line.includes(' --> ')) continue;

        const [start] = line.split(' --> ');
        const textLines: string[] = [];

        i++;

        while (i < cueLines.length && cueLines[i].trim() !== '') {
            textLines.push(cueLines[i]);
            i++;
        }

        const text = textLines.join('\n').trim();

        if (!text) continue;

        lines.push({
            timestamp: timestampToSeconds(start),
            text: text.replace(ALLOWED_TAG_PATTERN, ''),
            index: lines.length,
        });
    }
    return lines;
});

const activeIndex = computed(() => {
    const lines = parsedTranscript.value;
    for (let i = 0; i < lines.length; i++) {
        const next = lines[i + 1];
        if (currentTime.value >= lines[i].timestamp && (!next || currentTime.value < next.timestamp)) {
            return i;
        }
    }
    return -1;
});

function timestampToSeconds(timestamp: string): number {
    const parts = timestamp.trim().split(':').map(Number);

    if (parts.length === 2) {
        const [minutes, seconds] = parts;
        return minutes * 60 + seconds;
    }

    if (parts.length === 3) {
        const [hours, minutes, seconds] = parts;
        return hours * 3600 + minutes * 60 + seconds;
    }

    return 0;
}

function formatTimestamp(totalSeconds: number): string {
    const duration = props.player?.duration ?? 0;

    const h = Math.floor(totalSeconds / 3600);
    const m = Math.floor((totalSeconds % 3600) / 60);
    const s = totalSeconds % 60;

    const formattedSeconds = s.toFixed(0).padStart(2, '0');

    if (duration >= 3600) return `${h}:${String(m).padStart(2, '0')}:${formattedSeconds}`;
    if (duration >= 600) return `${String(m).padStart(2, '0')}:${formattedSeconds}`;
    return `${m}:${formattedSeconds}`;
}

function buildTranscriptUrl(subtitle?: SubtitleResource): string | undefined {
    if (!subtitle) return undefined;

    const { metadata_uuid, track_id, language } = subtitle;
    const languageSlug = track_id === 0 ? `.${language}` : '';

    return `/data/subtitles/${metadata_uuid}/${track_id}${languageSlug}.vtt`;
}

const transcriptLineRefs = ref<(HTMLElement | null)[]>([]);

function setTranscriptLineRef(index: number, el: Element | ComponentPublicInstance | null) {
    transcriptLineRefs.value[index] = el as HTMLElement | null;
}

function handleUpdateEvent() {
    currentTime.value = props.player?.currentTime ?? 0;
}

watch(
    () => props.player,
    (newPlayer, oldPlayer) => {
        oldPlayer?.removeEventListener('timeupdate', handleUpdateEvent);
        newPlayer?.addEventListener('timeupdate', handleUpdateEvent);
    },
    { immediate: true },
);

watch(
    () => props.subtitleTrack,
    async (track) => {
        rawTranscript.value = '';
        transcriptLineRefs.value = [];

        const url = buildTranscriptUrl(track);
        if (!url) return;

        try {
            isLoading.value = true;
            const response = await fetch(url);
            if (!response.ok) throw new Error(`Failed to fetch transcript: ${response.status}`);

            rawTranscript.value = await response.text();
        } catch (error) {
            console.error('Failed to load transcript:', error);
        } finally {
            isLoading.value = false;
        }
    },
    { immediate: true },
);

watch(activeIndex, async (index) => {
    if (index === -1) return;

    await nextTick();

    const line = transcriptLineRefs.value[index];
    const container = transcriptContainer.value;

    if (!line || !container) return;

    const lineTop = line.offsetTop;
    const lineBottom = lineTop + line.offsetHeight;
    const visibleTop = container.scrollTop;
    const visibleBottom = visibleTop + container.clientHeight;

    if (lineTop < visibleTop) {
        container.scrollTo({ top: lineTop - 16, behavior: 'smooth' });
    } else if (lineBottom > visibleBottom) {
        container.scrollTo({ top: lineBottom - container.clientHeight + 16, behavior: 'smooth' });
    }
});

onUnmounted(() => {
    props.player?.removeEventListener('timeupdate', handleUpdateEvent);
});
</script>

<template>
    <div v-show="isVisible" class="pointer-events-auto w-full max-w-120 flex-1 overflow-y-auto rounded-xl border border-neutral-700/10 bg-neutral-800/90 p-1.5 backdrop-blur-md">
        <div ref="transcript-container" class="scrollbar-minimal scrollbar-dark relative flex h-full gap-1 overflow-y-auto pe-1">
            <div class="w-full space-y-1" v-if="isVisible">
                <div class="sticky top-0 z-10 flex items-center justify-between gap-2 rounded-lg px-2 py-1.5 backdrop-blur-md">
                    <p class="text-sm font-medium text-white/90">Transcript</p>
                    <ButtonCorner
                        title="Close Transcript"
                        @click="emit('close')"
                        colour-classes="hover:bg-transparent"
                        text-classes=" hover:text-danger-2"
                        position-classes="size-5"
                    >
                        <template #icon> <ProiconsCancel /> </template>
                    </ButtonCorner>
                </div>
                <div v-if="isLoading">
                    <button type="button" :class="['group flex w-full animate-pulse cursor-pointer items-start gap-2 rounded-lg px-2 py-1.5 text-left transition-colors']">
                        <span class="shrink-0 rounded-md px-1.5 py-0.5 font-mono text-xs tabular-nums transition-colors" :class="'bg-white/5 text-transparent'"> 00:00 </span>
                        <span class="h-5 w-full rounded-md bg-white/15"></span>
                    </button>
                </div>
                <div class="space-y-0.5" v-else>
                    <button
                        v-for="line in parsedTranscript"
                        :key="line.index"
                        :ref="(el) => setTranscriptLineRef(line.index, el)"
                        :class="[
                            'group flex w-full cursor-pointer items-start gap-2 rounded-lg px-2 py-1.5 text-left transition-colors',
                            line.index === activeIndex ? 'bg-white/12 text-white' : 'text-white/70 hover:bg-white/7 hover:text-white',
                        ]"
                        :title="`${line.index + 1} of ${parsedTranscript.length}`"
                        type="button"
                        @click="emit('seek', line.timestamp)"
                    >
                        <span
                            class="shrink-0 rounded-md px-1.5 py-0.5 font-mono text-xs tabular-nums transition-colors"
                            :class="line.index === activeIndex ? 'bg-white/15 text-white' : 'bg-white/5 text-white/50 group-hover:text-white/80'"
                        >
                            {{ formatTimestamp(line.timestamp) }}
                        </span>
                        <span class="font-dm-sans line-clamp-3 text-sm leading-5" v-html="line.text"> </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
