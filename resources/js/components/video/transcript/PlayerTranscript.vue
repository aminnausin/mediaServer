<script setup lang="ts">
import type { ComponentPublicInstance } from 'vue';
import type { SubtitleResource } from '@/contracts/media';

import { computed, nextTick, onBeforeUnmount, onUnmounted, ref, useTemplateRef, watch } from 'vue';
import { ButtonBase, ButtonCorner } from '@/components/cedar-ui/button';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { cn, toast } from '@aminnausin/cedar-ui';

import ProiconsCancel from '~icons/proicons/cancel';

interface TranscriptLine {
    index: number;
    text: string;
    start: number;
    end: number;
}

const ALLOWED_TAG_PATTERN = /<(?!\/?(?:i|u|br)\b)[^>]*>/gi;

const props = defineProps<{ isVisible: boolean; player: HTMLVideoElement | null; subtitleTrack?: SubtitleResource }>();
const emit = defineEmits<{ seek: [value: number]; close: [] }>();

const { loadedSideBar } = storeToRefs(useAppStore());

const transcriptContainer = useTemplateRef('transcript-container');
const transcriptLineRefs = ref<(HTMLElement | null)[]>([]);

const rawTranscript = ref('');
const currentTime = ref(0);
const isFollowing = ref(true);

const isLoading = ref(false);
const loadedTranscriptUrl = ref<string>();

const isTeleported = computed(() => props.isVisible && loadedSideBar.value === 'transcript');

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

        lines.push({
            start: startTime,
            end: endTime,
            text,
            index: lines.length,
        });
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

        if (time < line.start) {
            high = mid - 1;
        } else if (!next || time < next.start) {
            return mid;
        } else {
            low = mid + 1;
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

async function loadTranscript() {
    const url = buildTranscriptUrl(props.subtitleTrack);

    if (!url || loadedTranscriptUrl.value === url) return;

    try {
        isLoading.value = true;

        const response = await fetch(url);

        if (!response.ok) {
            throw new Error(`Failed to fetch transcript: ${response.status}`);
        }

        rawTranscript.value = await response.text();
        loadedTranscriptUrl.value = url;
    } catch (error) {
        toast.error('Failed to load transcript');
        console.error('Failed to load transcript:', error);
    } finally {
        isLoading.value = false;
    }
}

function setTranscriptLineRef(index: number, el: Element | ComponentPublicInstance | null) {
    transcriptLineRefs.value[index] = el as HTMLElement | null;
}

function handleUpdateEvent() {
    currentTime.value = props.player?.currentTime ?? 0;
}

function scrollToLine(index: number, behavior: ScrollBehavior = 'smooth') {
    const line = transcriptLineRefs.value[index];
    const container = transcriptContainer.value;

    if (!line || !container) return;

    const lineTop = line.offsetTop;
    const lineBottom = lineTop + line.offsetHeight;
    const visibleTop = container.scrollTop;
    const visibleBottom = visibleTop + container.clientHeight;

    let target: number | undefined;

    if (lineTop < visibleTop) {
        target = lineTop - (isTeleported.value ? 90 : 40);
    } else if (lineBottom > visibleBottom) {
        target = lineBottom - container.clientHeight + 16;
    }

    if (target === undefined) return;

    container.scrollTo({ top: target, behavior });
}

function handleUserScroll() {
    isFollowing.value = false;
}

function resync() {
    isFollowing.value = true;
    if (activeIndex.value !== -1) scrollToLine(activeIndex.value);
}

watch(
    transcriptContainer,
    (el, oldEl) => {
        oldEl?.removeEventListener('wheel', handleUserScroll);
        oldEl?.removeEventListener('touchmove', handleUserScroll);
        el?.addEventListener('wheel', handleUserScroll, { passive: true });
        el?.addEventListener('touchmove', handleUserScroll, { passive: true });
    },
    { immediate: true },
);

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
    () => {
        rawTranscript.value = '';
        transcriptLineRefs.value = [];
        loadedTranscriptUrl.value = undefined;

        if (props.isVisible) {
            void loadTranscript();
        }
    },
);

watch(
    () => props.isVisible,
    (isVisible) => {
        if (isVisible) {
            void loadTranscript();
        }
    },
    { immediate: true },
);

watch(activeIndex, async (index) => {
    if (index === -1 || !isFollowing.value) return;

    await nextTick();

    scrollToLine(index);
});

onBeforeUnmount(() => {
    transcriptContainer.value?.removeEventListener('wheel', handleUserScroll);
    transcriptContainer.value?.removeEventListener('touchmove', handleUserScroll);
});

onUnmounted(() => {
    props.player?.removeEventListener('timeupdate', handleUpdateEvent);
});
</script>

<template>
    <Teleport v-if="isVisible" defer :disabled="!isTeleported" to="#list-content-transcript">
        <div
            v-show="isVisible"
            :class="[
                'pointer-events-auto w-full max-w-120 flex-1 overflow-y-auto',
                { 'rounded-xl border border-neutral-700/10 bg-neutral-800/90 p-1.5 backdrop-blur-md': !isTeleported },
                { 'relative text-xs': isTeleported },
            ]"
        >
            <div
                ref="transcript-container"
                :class="['scrollbar-minimal scrollbar-dark relative flex h-full flex-col gap-1 overflow-y-auto pe-1', { 'max-h-(--min-page-height-derived)': isTeleported }]"
            >
                <div :class="cn('sticky top-0 z-10 flex items-center justify-between gap-2 rounded-lg bg-neutral-800 px-2 py-1.5 backdrop-blur-xs', { hidden: isTeleported })">
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
                <div class="w-full space-y-1" v-if="isVisible">
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
                                'content-auto group flex w-full cursor-pointer items-start gap-2 rounded-lg p-1.5 pe-2 text-left transition-colors [contain-intrinsic-size:32px_auto]',
                                line.index === activeIndex && line.end >= currentTime ? 'bg-white/12 text-white' : 'text-white/70 hover:bg-white/7 hover:text-white',
                            ]"
                            :title="`${line.index + 1} of ${parsedTranscript.length}`"
                            type="button"
                            @click="
                                () => {
                                    isFollowing = true;
                                    emit('seek', line.start);
                                }
                            "
                        >
                            <div
                                class="shrink-0 rounded-md px-1.5 py-0.5 font-mono text-xs tabular-nums transition-colors"
                                :class="
                                    cn({
                                        'bg-white/15 text-white': line.index === activeIndex && line.end >= currentTime,
                                        'bg-white/10 text-white/50 group-hover:text-white/80': line.index === activeIndex && line.end < currentTime,
                                        'bg-white/5 text-white/50 group-hover:text-white/80': line.index !== activeIndex,
                                    })
                                "
                            >
                                {{ formatTimestamp(line.start) }}
                            </div>
                            <span class="font-dm-sans line-clamp-3 leading-5 select-text" role="text" v-html="line.text"></span>
                        </button>
                    </div>
                </div>
            </div>
            <Transition
                enter-active-class="transition duration-150 ease-out"
                enter-from-class="translate-y-1 opacity-0"
                enter-to-class="translate-y-0 opacity-100"
                leave-active-class="transition duration-100 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <ButtonBase
                    v-if="!isFollowing && !isLoading && activeIndex !== -1"
                    type="button"
                    class="text-foreground-0 dark:text-foreground-i absolute bottom-2 left-1/2 z-20 -translate-x-1/2 rounded-full bg-white px-2 py-1.5 font-medium transition hover:bg-white/90"
                    :use-size="false"
                    @click="resync"
                >
                    Jump to current
                </ButtonBase>
            </Transition>
        </div>
    </Teleport>
</template>
