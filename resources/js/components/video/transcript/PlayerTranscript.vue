<script setup lang="ts">
import { computed, nextTick, ref, useTemplateRef, watch } from 'vue';
import { ButtonBase, ButtonCorner } from '@/components/cedar-ui/button';
import { useReactiveBreakpoints } from '@/service/breakpoints/useReactiveBreakpoints';
import { formatTimestamp } from '@/components/video/transcript/transcriptUtil';
import { useTranscript } from '@/components/video/transcript/useTranscript';
import { useAppStore } from '@/stores/AppStore';
import { cn } from '@aminnausin/cedar-ui';

import GenerateTranscript from '@/components/video/transcript/GenerateTranscript.vue';
import TranscriptStatus from '@/components/video/transcript/TranscriptStatus.vue';
import TranscriptLine from '@/components/video/transcript/TranscriptLine.vue';

import ProiconsAlertTriangle from '~icons/proicons/alert-triangle';
import ProiconsPanelRight from '~icons/proicons/panel-right';
import ProiconsArrowSync from '~icons/proicons/arrow-sync';
import ProiconsCancel from '~icons/proicons/cancel';

const props = defineProps<{ isVisible?: boolean }>();

const { cycleSideBar } = useAppStore();
const { isDesktop } = useReactiveBreakpoints();

const { isNormalView, subtitleTrack, parsedTranscript, activeIndex, isActiveLive, isLoading, hasError, retry, generated, seek, close, placement } = useTranscript();

const transcriptContainer = useTemplateRef('transcript-container');
const transcriptDuration = computed(() => parsedTranscript.value.at(-1)?.end ?? 0);
const timestamps = computed(() => parsedTranscript.value.map((line) => formatTimestamp(line.start, transcriptDuration.value)));

const isFollowing = ref(true);

function onSelect(start: number) {
    isFollowing.value = true;
    seek(start);
}

function scrollToLine(index: number, behavior: ScrollBehavior = 'smooth') {
    const container = transcriptContainer.value;
    const line = container?.querySelector<HTMLElement>(`[data-transcript-index="${index}"]`);

    if (!line || !container) return;

    const lineTop = line.offsetTop;
    const lineBottom = lineTop + line.offsetHeight;
    const visibleTop = container.scrollTop;
    const visibleBottom = visibleTop + container.clientHeight;

    let target: number | undefined;

    if (lineTop < visibleTop) {
        target = lineTop - (placement.value === 'sidebar' ? 90 : 40);
    } else if (lineBottom > visibleBottom) {
        target = lineBottom - container.clientHeight + 16;
    }

    if (target === undefined) return;

    container.scrollTo({ top: target, behavior });
}

function handleUserScroll() {
    isFollowing.value = false;
}

async function resync(scrollBehavior: ScrollBehavior = 'smooth', waitForTick = false) {
    isFollowing.value = true;
    if (waitForTick) await nextTick();
    if (activeIndex.value !== -1) scrollToLine(activeIndex.value, scrollBehavior);
}

watch(
    () => props.isVisible,
    async (visible) => {
        if (!visible) return;
        resync('auto', true);
    },
    { immediate: true },
);

watch(activeIndex, async (index) => {
    if (index === -1 || !isFollowing.value) return;
    await nextTick();
    scrollToLine(index);
});
</script>

<template>
    <div
        v-if="isVisible"
        :class="[
            'pointer-events-auto w-full max-w-80 flex-1 overflow-y-auto xl:max-w-120',
            { 'rounded-xl border border-neutral-700/10 bg-neutral-800/90 p-1.5 text-[10px] backdrop-blur-md sm:text-xs': placement !== 'sidebar' },
            { 'relative text-xs': placement === 'sidebar' },
        ]"
    >
        <div
            ref="transcript-container"
            :class="['scrollbar-minimal scrollbar-dark relative flex h-full flex-col gap-1 overflow-y-auto pe-1', { 'max-h-(--min-page-height-derived)': placement === 'sidebar' }]"
            @wheel.passive="handleUserScroll"
            @touchmove.passive="handleUserScroll"
        >
            <div
                :class="cn('sticky top-0 z-10 flex items-center justify-between gap-2 rounded-lg bg-neutral-800 px-2 py-1.5 backdrop-blur-xs', { hidden: placement === 'sidebar' })"
            >
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
            <div class="w-full space-y-1" v-if="subtitleTrack">
                <div v-if="isLoading && !hasError">
                    <button type="button" :class="['group flex w-full animate-pulse cursor-pointer items-start gap-2 rounded-lg px-2 py-1.5 text-left transition-colors']">
                        <span class="shrink-0 rounded-md px-1.5 py-0.5 font-mono text-xs tabular-nums transition-colors" :class="'bg-white/5 text-transparent'"> 00:00 </span>
                        <span class="h-5 w-full rounded-md bg-white/15"></span>
                    </button>
                </div>
                <TranscriptStatus
                    v-if="hasError"
                    :is-busy="isLoading"
                    :copy="isLoading ? { heading: 'Loading transcript', button: 'Loading...' } : { heading: 'Failed', description: 'Transcript failed to load', button: 'Retry' }"
                    :badge-class="cn({ 'bg-danger-2/10 text-danger-2': hasError && !isLoading })"
                    @action="retry"
                >
                    <template #icon>
                        <ProiconsAlertTriangle class="size-4 sm:size-5" />
                    </template>
                    <template #buttonIcon>
                        <ProiconsArrowSync class="size-3.5" />
                    </template>
                </TranscriptStatus>

                <div class="space-y-0.5" v-else>
                    <TranscriptLine
                        v-for="line in parsedTranscript"
                        :key="line.index"
                        :data-transcript-index="line.index"
                        :line="line"
                        :active="line.index === activeIndex"
                        :live="line.index === activeIndex && isActiveLive"
                        :title="`${line.index + 1} of ${parsedTranscript.length}`"
                        :timestamp="timestamps[line.index]"
                        @select="onSelect"
                    />
                </div>
            </div>
            <GenerateTranscript
                v-else-if="!subtitleTrack"
                :class="{ 'py-20': placement !== 'sidebar' }"
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
                @click="resync()"
            >
                Jump to current
            </ButtonBase>
        </Transition>
    </div>
</template>
