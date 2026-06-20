<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import type { StoryboardCue } from '@/service/storyboard/types';
import type { VideoResource } from '@/contracts/media';

import { computed, reactive, ref, useTemplateRef, watch } from 'vue';
import { SvgSpinners90RingWithBg } from '@/components/cedar-ui/icons';
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
const hasHovered = ref(false);
const hovered = ref(false);

const preloadedSprites = reactive(new Map<string, 'loading' | 'loaded' | 'error'>());

const timestamp = computed(() => {
    if (props.isAudio || !props.data.duration) return '';
    return `${toFormattedDuration(hoverProgress.value, false, 'digital', true)}`;
});

const previewCues = computed<StoryboardCue[]>(() => {
    const uuid = props.data.metadata?.uuid;
    const durationSeconds = props.data.duration;
    const storyboard = props.data.storyboard;

    if (props.isAudio || !storyboard || !uuid || !durationSeconds || !hasHovered.value) return [];

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
    if (!previewCues.value.length || props.isAudio) return undefined;

    const duration = props.data.duration ?? 0;
    const index = Math.min(Math.floor((hoverProgress.value / duration) * PREVIEW_FRAME_COUNT), PREVIEW_FRAME_COUNT - 1);
    return previewCues.value[index];
});

const spriteStyle = computed<HTMLAttributes['style']>(() => {
    if (props.isAudio || !hovered.value) return undefined;

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

function updateProgressFromX(clientX: number) {
    const rect = scrubContainer.value!.getBoundingClientRect();
    const ratio = Math.max(0, Math.min(1, (clientX - rect.left) / rect.width));
    hoverProgress.value = ratio * (props.data.duration ?? 0);
}

function preloadSprite(url: string) {
    if (!url) return;

    const existing = preloadedSprites.get(url);

    if (existing === 'loading' || existing === 'loaded') return;

    preloadedSprites.set(url, 'loading');

    const img = new Image();

    img.onload = () => preloadedSprites.set(url, 'loaded');
    img.onerror = () => preloadedSprites.set(url, 'error');
    img.src = url;
}

//#region Input Events

function onTouchStart(e: TouchEvent) {
    if (props.isAudio) return;
    hovered.value = true;
    updateProgressFromX(e.touches[0].clientX);
}

function onTouchMove(e: TouchEvent) {
    if (props.isAudio) return;
    updateProgressFromX(e.touches[0].clientX);
}

function handleLeave() {
    hovered.value = false;
}

function onMouseMove(e: MouseEvent) {
    if (props.isAudio) return;
    updateProgressFromX(e.clientX);
}

function onMouseEnter() {
    hovered.value = true;
    hasHovered.value = true;

    containerRect.value = scrubContainer.value?.getBoundingClientRect() ?? null;
}

//#endregion

const generatePosterStyle = (url?: string): HTMLAttributes['style'] => {
    if (!url) return {};

    // I could use blur hash instead
    return {
        backgroundImage: `url("${url}")`,
        backgroundPosition: 'center',
        backgroundSize: 'cover',
        backgroundRepeat: 'no-repeat',
    };
};

watch(previewCues, (cues) => {
    for (const cue of cues) {
        if (cue.image) preloadSprite(cue.image);
    }
});

watch(
    () => props.data.storyboard,
    () => preloadedSprites.clear(),
);

defineOptions({
    inheritAttrs: false,
});

defineExpose({ hovered });
</script>

<template>
    <div
        :class="cn('relative flex items-center overflow-clip text-xs select-none')"
        @mouseenter="onMouseEnter"
        @mouseleave="handleLeave"
        @mousemove="onMouseMove"
        @touchstart.passive="onTouchStart"
        @touchmove.passive="onTouchMove"
        @touchend="handleLeave"
        ref="scrubContainer"
    >
        <template v-if="posterUrl">
            <div :class="[isFolderMajorityAudio ? 'aspect-square' : 'aspect-video', 'size-full', $attrs.class]">
                <div class="absolute inset-0 scale-120 blur-sm" :style="generatePosterStyle(posterUrl)"></div>

                <LazyImage
                    :src="posterUrl"
                    alt="poster"
                    :animate="false"
                    loading="lazy"
                    :wrapper-class="cn('transition-opacity duration-input', { 'opacity-0': hovered && activeCue })"
                    :class="cn('absolute inset-0 size-full object-contain')"
                />

                <Transition name="fade">
                    <div
                        v-if="hovered && activeCue && preloadedSprites.get(activeCue.image) === 'loading'"
                        :class="cn('duration-input absolute inset-0 flex items-center justify-center transition-opacity')"
                    >
                        <SvgSpinners90RingWithBg class="size-4" />
                    </div>
                </Transition>
                <Transition name="fade">
                    <div
                        v-if="hovered && activeCue && preloadedSprites.get(activeCue.image) === 'loaded'"
                        :class="cn('duration-input absolute inset-0 flex items-center justify-center transition-opacity')"
                    >
                        <div :style="spriteStyle"></div>
                    </div>
                </Transition>
            </div>

            <!-- Overlay -->
            <div
                v-if="data.duration"
                :class="
                    cn('duration-input pointer-events-none absolute inset-0 z-3 flex flex-col justify-end gap-1 transition-[translate,margin]', {
                        'ms-0.5 -translate-y-0.5': dataActive,
                    })
                "
            >
                <VideoControlWrapper
                    :class="cn('ml-1 w-fit opacity-0 transition-opacity duration-150', { 'opacity-100': hovered, 'mb-2': !activeCue, 'backdrop-blur-none': !hovered })"
                >
                    <p :class="cn('font-figtree px-1 text-white tabular-nums text-shadow-lg')">
                        {{ activeCue ? timestamp : toFormattedDuration(data.duration, false, 'digital') }}
                    </p>
                </VideoControlWrapper>
                <Transition name="fade">
                    <div v-if="activeCue && hovered" :class="cn('h-1 w-full bg-white/20 transition-opacity duration-150')">
                        <div class="h-full bg-white" :style="{ width: `${(hoverProgress / data.duration) * 100}%` }" />
                    </div>
                </Transition>
            </div>
        </template>

        <div v-else :class="cn('bg-surface-3 flex aspect-video h-24 shrink-0 items-center justify-center dark:bg-neutral-900/80', { 'w-24': isFolderMajorityAudio })">
            <ProIconsPhotoOff class="text-foreground-1 size-5" />
        </div>
    </div>
</template>
<style lang="css" scoped>
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
.fade-enter-to,
.fade-leave-from {
    opacity: 1;
}
</style>
