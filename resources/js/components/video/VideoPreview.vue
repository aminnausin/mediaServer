<script setup lang="ts">
import type { StoryboardCue } from '@/service/storyboard/types';
import type { VideoResource } from '@/contracts/media';

import { computed, ref, useTemplateRef } from 'vue';
import { buildStoryboardCues } from '@/service/storyboard';
import { toFormattedDuration } from '@/service/util';
import { cn } from '@aminnausin/cedar-ui';

import VideoControlWrapper from '@/components/video/VideoControlWrapper.vue';
import LazyImage from '@/components/lazy/LazyImage.vue';

const PREVIEW_FRAME_COUNT = 10;

const props = defineProps<{
    data: VideoResource;
    dataActive: boolean;
    posterUrl?: string;
}>();

const scrubContainer = useTemplateRef('scrubContainer');
const hoverProgress = ref(0);
const hovered = ref(false);

const previewCues = computed<StoryboardCue[]>(() => {
    const uuid = props.data.metadata?.uuid;
    const durationSeconds = props.data.duration;
    const storyboard = props.data.storyboard;

    if (!storyboard || !uuid || !durationSeconds) return [];

    const storyboardCues = buildStoryboardCues(uuid, durationSeconds, storyboard);

    if (!storyboardCues.length) return [];

    const frameCount = Math.min(storyboardCues.length, PREVIEW_FRAME_COUNT);
    const sliceSize = (props.data.duration ?? 0) / frameCount;
    const step = Math.floor(storyboardCues.length / frameCount);

    return Array.from({ length: frameCount }, (_, i) => ({
        ...storyboardCues[i * step],
        start: i * sliceSize,
        end: (i + 1) * sliceSize,
    })).filter(Boolean);
});

const activeCue = computed<StoryboardCue | undefined>(() => {
    if (!previewCues.value.length) return undefined;
    const duration = props.data.duration ?? 0;
    const index = Math.min(Math.floor((hoverProgress.value / duration) * PREVIEW_FRAME_COUNT), PREVIEW_FRAME_COUNT - 1);
    return previewCues.value[index];
});

const spriteStyle = computed(() => {
    const c = activeCue.value;
    const storyboard = props.data.storyboard;

    if (!c || c.x === undefined || !storyboard || !scrubContainer.value) return undefined;

    const containerWidth = scrubContainer.value.getBoundingClientRect().width;
    const scale = containerWidth / c.w;

    return {
        backgroundImage: `url(${c.image})`,
        backgroundPosition: `-${c.x * scale}px -${c.y * scale}px`,
        backgroundSize: `${c.w * storyboard.tile_cols * scale}px ${c.h * storyboard.tile_rows * scale}px`,
    };
});

const timestamp = computed(() => {
    if (!props.data.duration) return '';
    return `${toFormattedDuration(hoverProgress.value, false, 'digital', true)}`;
});

// Could debounce
function onMouseMove(e: MouseEvent) {
    const el = e.currentTarget as HTMLElement;
    const rect = el.getBoundingClientRect();
    const ratio = (e.clientX - rect.left) / rect.width;
    const duration = props.data.duration ?? 0;
    hoverProgress.value = ratio * duration;
}
</script>

<template>
    <div
        :class="['group relative aspect-video size-full', $attrs.class]"
        :style="spriteStyle"
        @mouseenter="hovered = true"
        @mouseleave="hovered = false"
        @mousemove="onMouseMove"
        ref="scrubContainer"
    >
        <LazyImage
            :src="posterUrl"
            alt="poster"
            :class="
                cn('absolute inset-0 size-full cursor-crosshair object-cover opacity-100', {
                    'transition-opacity group-hover:opacity-0': activeCue,
                })
            "
        />
    </div>

    <div
        v-if="hovered && activeCue && data.duration"
        :class="
            cn('duration-input pointer-events-none absolute inset-0 z-3 flex flex-col justify-end gap-1 opacity-0 transition-[translate,opacity,margin]', {
                'opacity-100': hovered,
                'ms-0.5 -translate-y-0.5': dataActive,
            })
        "
    >
        <VideoControlWrapper class="ml-1 w-fit">
            <p :class="cn('font-figtree px-1 text-xs text-white tabular-nums text-shadow-lg')">
                {{ timestamp }}
            </p>
        </VideoControlWrapper>
        <div :class="cn('h-1 w-full bg-white/20')">
            <div class="h-full bg-white" :style="{ width: `${(hoverProgress / data.duration) * 100}%` }" />
        </div>
    </div>
</template>
