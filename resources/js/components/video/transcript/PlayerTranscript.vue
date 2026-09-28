<script setup lang="ts">
import type { ComponentPublicInstance } from 'vue';
import type { SubtitleResource } from '@/contracts/media';

import { computed, nextTick, onBeforeUnmount, ref, useTemplateRef, watch } from 'vue';
import { breakpointsTailwind, useBreakpoints } from '@vueuse/core';
import { ButtonBase, ButtonCorner } from '@/components/cedar-ui/button';
import { useTranscript } from '@/components/video/transcript/useTranscript';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { cn } from '@aminnausin/cedar-ui';

import GenerateTranscript from '@/components/video/transcript/GenerateTranscript.vue';

import ProiconsPanelRight from '~icons/proicons/panel-right';
import ProiconsCancel from '~icons/proicons/cancel';

const props = defineProps<{ isVisible?: boolean }>();
const emit = defineEmits<{ seek: [value: number]; close: []; generated: [track: SubtitleResource] }>();

const breakpoints = useBreakpoints(breakpointsTailwind);
const isDesktop = breakpoints.greaterOrEqual('lg');

const { selectedSideBar } = storeToRefs(useAppStore());
const { cycleSideBar } = useAppStore();

const { player, isNormalView, rawTranscript, parsedTranscript, subtitleTrack, activeIndex, currentTime, loadedTranscriptUrl, loadTranscript, isLoading, generated, seek, close } =
    useTranscript();

const transcriptContainer = useTemplateRef('transcript-container');
const transcriptLineRefs = ref<(HTMLElement | null)[]>([]);

const isFollowing = ref(true);

const isTeleported = computed(() => props.isVisible && selectedSideBar.value === 'transcript');

function formatTimestamp(totalSeconds: number): string {
    const duration = player.value?.duration ?? 0;

    const h = Math.floor(totalSeconds / 3600);
    const m = Math.floor((totalSeconds % 3600) / 60);
    const s = totalSeconds % 60;

    const formattedSeconds = s.toFixed(0).padStart(2, '0');

    if (duration >= 3600) return `${h}:${String(m).padStart(2, '0')}:${formattedSeconds}`;
    if (duration >= 600) return `${String(m).padStart(2, '0')}:${formattedSeconds}`;
    return `${m}:${formattedSeconds}`;
}

function setTranscriptLineRef(index: number, el: Element | ComponentPublicInstance | null) {
    transcriptLineRefs.value[index] = el as HTMLElement | null;
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
    () => subtitleTrack.value,
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
</script>

<template>
    <Teleport v-if="isVisible" defer :disabled="!isTeleported" to="#list-content-transcript">
        <div
            v-show="isVisible"
            :class="[
                'pointer-events-auto w-full max-w-120 flex-1 overflow-y-auto',
                { 'rounded-xl border border-neutral-700/10 bg-neutral-800/90 p-1.5 text-[10px] backdrop-blur-md sm:text-xs': !isTeleported },
                { 'relative text-xs': isTeleported },
            ]"
        >
            <div
                ref="transcript-container"
                :class="['scrollbar-minimal scrollbar-dark relative flex h-full flex-col gap-1 overflow-y-auto pe-1', { 'max-h-(--min-page-height-derived)': isTeleported }]"
            >
                <div :class="cn('sticky top-0 z-10 flex items-center justify-between gap-2 rounded-lg bg-neutral-800 px-2 py-1.5 backdrop-blur-xs', { hidden: isTeleported })">
                    <p class="text-xs font-medium text-white/90 sm:text-sm">Transcript</p>
                    <div class="flex items-center gap-2">
                        <ButtonCorner
                            v-if="isDesktop && isNormalView"
                            @click="
                                () => {
                                    cycleSideBar('transcript', 'list-card');
                                }
                            "
                            title="Move to Sidebar"
                            colour-classes="hover:bg-transparent"
                            text-classes="text-white/70 hover:text-white"
                            position-classes="size-5"
                        >
                            <template #icon> <ProiconsPanelRight class="size-4" /> </template>
                        </ButtonCorner>
                        <ButtonCorner
                            title="Close Transcript"
                            @click="close()"
                            colour-classes="hover:bg-transparent"
                            text-classes="text-white/70 hover:text-danger-2"
                            position-classes="size-5"
                        >
                            <template #icon> <ProiconsCancel class="size-4" /> </template>
                        </ButtonCorner>
                    </div>
                </div>
                <div class="w-full space-y-1" v-if="isVisible && subtitleTrack">
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
                                    seek(line.start);
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
                <GenerateTranscript
                    v-else-if="!subtitleTrack"
                    :class="{ 'py-20': !isTeleported }"
                    @generated="
                        (data) => {
                            generated(data.track);
                        }
                    "
                />
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
