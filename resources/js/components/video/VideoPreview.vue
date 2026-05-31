<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import type { StoryboardCue } from '@/service/storyboard/types';
import type { VideoResource } from '@/contracts/media';

import { computed, ref, useTemplateRef } from 'vue';
import { buildStoryboardCues } from '@/service/storyboard';
import { toFormattedDuration } from '@/service/util';
import { cn } from '@aminnausin/cedar-ui';

import VideoControlWrapper from '@/components/video/VideoControlWrapper.vue';
import ProIconsPhotoOff from '@/components/icons/ProIconsPhotoOff.vue';
import LazyImage from '@/components/lazy/LazyImage.vue';

const PREVIEW_FRAME_COUNT = 10;

const props = defineProps<{
    data: VideoResource;
    dataActive: boolean;
    posterUrl?: string;
    isAudio?: boolean;
    isFolderMajorityAudio?: boolean;
}>();

const scrubContainer = useTemplateRef('scrubContainer');

const containerRect = ref<DOMRect | null>(null);
const hoverProgress = ref(0);
const hovered = ref(false);

const previewCues = computed<StoryboardCue[]>(() => {
    const uuid = props.data.metadata?.uuid;
    const durationSeconds = props.data.duration;
    const storyboard = props.data.storyboard;

    if (props.isAudio || !storyboard || !uuid || !durationSeconds) return [];

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

const spriteStyle = computed<HTMLAttributes['style']>(() => {
    if (props.isAudio) return {};

    const c = activeCue.value;
    const storyboard = props.data.storyboard;

    if (!c || c.x === undefined || !storyboard || !scrubContainer.value) return undefined;

    const { width: maxWidth, height: maxHeight } = containerRect.value ?? scrubContainer.value.getBoundingClientRect();

    const scaleX = maxWidth / c.w;
    const scaleY = maxHeight / c.h;
    const scale = Math.min(scaleX, scaleY);

    return {
        backgroundImage: `url(${c.image})`,
        backgroundPosition: `-${c.x * scale}px -${c.y * scale}px`,
        backgroundSize: `${c.w * storyboard.tile_cols * scale}px ${c.h * storyboard.tile_rows * scale}px`,
        width: `${c.w * scale}px`,
        height: `${c.h * scale}px`,
    };
});

const timestamp = computed(() => {
    if (props.isAudio || !props.data.duration) return '';
    return `${toFormattedDuration(hoverProgress.value, false, 'digital', true)}`;
});

function onTouchStart(e: TouchEvent) {
    if (props.isAudio) return;
    hovered.value = true;
    updateProgressFromX(e.touches[0].clientX);
}

function onTouchMove(e: TouchEvent) {
    if (props.isAudio) return;
    updateProgressFromX(e.touches[0].clientX);
}

function onTouchEnd() {
    hovered.value = false;
    hoverProgress.value = 0;
}

function updateProgressFromX(clientX: number) {
    const rect = scrubContainer.value!.getBoundingClientRect();
    const ratio = Math.max(0, Math.min(1, (clientX - rect.left) / rect.width));
    hoverProgress.value = ratio * (props.data.duration ?? 0);
}

// Could debounce
function onMouseMove(e: MouseEvent) {
    if (props.isAudio) return;
    updateProgressFromX(e.clientX);
}

function onMouseEnter() {
    hovered.value = true;
    containerRect.value = scrubContainer.value?.getBoundingClientRect() ?? null;
}

// I could use blur hash instead
const generatePosterStyle = (url?: string): HTMLAttributes['style'] => {
    if (!url) return {};

    return {
        backgroundImage: `url("${url}")`,
        backgroundPosition: 'center',
        backgroundSize: 'cover',
        backgroundRepeat: 'no-repeat',
    };
};

defineOptions({
    inheritAttrs: false,
});
</script>

<template>
    <div
        :class="cn('group relative flex items-center overflow-clip')"
        @mouseenter="onMouseEnter"
        @mouseleave="hovered = false"
        @mousemove="onMouseMove"
        @touchstart.passive="onTouchStart"
        @touchmove.passive="onTouchMove"
        @touchend="onTouchEnd"
        ref="scrubContainer"
    >
        <template v-if="posterUrl">
            <div :class="[isFolderMajorityAudio ? 'aspect-square' : 'aspect-video', 'size-full', $attrs.class]">
                <div class="absolute inset-0 scale-120 blur-sm" :style="generatePosterStyle(posterUrl)"></div>

                <LazyImage
                    :src="posterUrl"
                    alt="poster"
                    :animate="true"
                    loading="eager"
                    fetchpriority="high"
                    :wrapper-class="
                        cn('transition-opacity duration-input', {
                            'opacity-0': hovered && activeCue,
                        })
                    "
                    :class="cn('absolute inset-0 size-full cursor-crosshair object-contain')"
                />
                <div
                    :class="
                        cn('duration-input absolute inset-0 flex items-center justify-center opacity-0 transition-opacity', {
                            'opacity-100': hovered && activeCue,
                        })
                    "
                >
                    <div class="h-full w-fit bg-cover" :style="spriteStyle"></div>
                </div>
            </div>

            <!-- Overlay -->
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

        <div v-else :class="cn('bg-surface-3 flex aspect-video h-24 shrink-0 items-center justify-center dark:bg-neutral-900/80', { 'w-24': isFolderMajorityAudio })">
            <ProIconsPhotoOff class="text-foreground-1 size-5" />
        </div>
    </div>
</template>
