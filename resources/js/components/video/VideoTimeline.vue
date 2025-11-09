<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, useTemplateRef } from 'vue';
import { getClientX, getScreenSize, toFormattedDuration } from '@/service/util';
import { throttle } from 'lodash';

import VideoTooltipSlider from '@/components/video/VideoTooltipSlider.vue';

const sliderStep = 0.01;
const thumbWidth = 8;

const emit = defineEmits<{
    seek: [];
    seekPreview: [];
    keyBind: [event: KeyboardEvent, override?: boolean];
}>();

const props = defineProps<{
    bufferPercentage: number;
    timeDuration: number;
    videoButtonOffset: number;
    timeElapsedVerbose: string;
}>();

const progressTooltip = useTemplateRef('progress-tooltip');
const progressContainer = useTemplateRef('progress-container');
const progressBar = useTemplateRef('progress-bar');

const containerWidth = ref(0);

const timeElapsed = defineModel<number>({ required: true });
const timeSeeking = ref('');

const thumbX = computed(() => {
    if (!progressContainer.value) return 0;
    const clamped = Math.min(Math.max(timeElapsed.value, 0), 100);

    return (clamped / 100) * (containerWidth.value - thumbWidth);
});

const handleKeydown = (event: KeyboardEvent) => {
    const allowedKeys = ['Tab', 'Shift'];

    if (!allowedKeys.includes(event.key)) {
        event.preventDefault();
    }

    emit('keyBind', event, !allowedKeys.includes(event.key));
};

function showTooltip(event: MouseEvent | TouchEvent) {
    if (!progressTooltip.value) return;
    progressTooltip.value.tooltipToggle(true);
    if ('touches' in event) handleProgressTooltip(event);
}

function hideTooltip() {
    if (!progressTooltip.value) return;
    progressTooltip.value.tooltipToggle(false);
}

const handleProgressTooltip = throttle((event: MouseEvent | TouchEvent) => {
    getProgressTooltip(getClientX(event));
    requestAnimationFrame(() => {
        if (!progressTooltip.value) return;
        progressTooltip.value.calculateTooltipPosition(event);
    });
}, 7);

const getProgressTooltip = (clientX: number) => {
    if (!props.timeDuration || !progressBar.value) return;

    const rect = progressBar.value.getBoundingClientRect();
    const offsetX = clientX - rect.left;

    const extendedLeft = thumbWidth / 2;
    const extendedRight = rect.width - thumbWidth / 2;

    const clampedX = Math.min(Math.max(offsetX, extendedLeft), extendedRight);

    const percent = (clampedX - extendedLeft) / (extendedRight - extendedLeft);

    const steppedDuration = Math.round((percent * props.timeDuration) / sliderStep) * sliderStep;
    const clampedDuration = Math.min(Math.max(0, steppedDuration), props.timeDuration);

    timeSeeking.value = toFormattedDuration(clampedDuration, true, 'digital') ?? '00:00';
};

let resizeObserver: ResizeObserver | null = null;

onMounted(() => {
    if (!progressContainer.value) return;

    containerWidth.value = progressContainer.value.offsetWidth;

    resizeObserver = new ResizeObserver(() => {
        if (progressContainer.value) {
            containerWidth.value = progressContainer.value.offsetWidth;
        }
    });

    resizeObserver.observe(progressContainer.value);
});

onUnmounted(() => {
    if (!resizeObserver) return;

    if (progressContainer.value) {
        resizeObserver.unobserve(progressContainer.value);
    }

    resizeObserver?.disconnect();
    resizeObserver = null;
});

defineExpose({ progressTooltip });
</script>

<template>
    <!-- Heatmap and Timeline -->
    <section class="relative flex h-8 min-h-8 w-full flex-1 flex-col-reverse rounded-full px-2">
        <VideoTooltipSlider
            ref="progress-tooltip"
            tooltip-position="top"
            class="-top-4 left-0"
            :tooltip-text="timeSeeking"
            :target-element="progressContainer ?? undefined"
            :offset="videoButtonOffset"
            :tooltip-arrow="false"
            :tooltip-delay="0"
        />
        <div class="group peer pointer-events-auto relative flex h-2 min-h-2 select-none items-center" ref="progress-container" role="group" aria-label="Video progress slider">
            <!-- Padding -->
            <div class="absolute -top-4 h-4 w-full" />
            <!-- Timeline -->
            <div
                :class="[
                    'pointer-events-none w-full overflow-clip rounded-full bg-white/30 transition-[height,border-radius] duration-100 ease-in-out',
                    getScreenSize() === 'default' ? 'mobile-hover h-2 rounded-[1px]' : 'h-1 group-hover:h-2 group-hover:rounded-[1px]',
                ]"
                style="transform: scaleY(1)"
            >
                <div
                    class="buffer"
                    :style="{
                        '--container-width': containerWidth,
                        '--time-buffer': bufferPercentage,
                        '--thumb-width': thumbWidth,
                        transform: `scaleX(calc(var(--scale-x) / 100))`,
                    }"
                />
                <div
                    class="progress"
                    :style="{
                        '--container-width': containerWidth,
                        '--time-elapsed': timeElapsed,
                        '--thumb-width': thumbWidth,
                        transform: `scaleX(calc(var(--scale-x) / 100))`,
                    }"
                />
            </div>

            <div
                :class="[
                    'pointer-events-none absolute transition-[top] duration-100 ease-in-out',
                    getScreenSize() === 'default' ? 'mobile-hover top-0' : 'top-0.5 group-hover:top-0',
                ]"
                :style="{
                    '--thumb-offset': `${(Math.min(Math.max(timeElapsed, 0), 100) / 100) * (thumbWidth / 2)}px`,
                    transform: `translateX(${thumbX}px)`,
                }"
            >
                <div
                    ref="progress-thumb"
                    :class="[
                        'thumb rounded-full bg-white transition-all duration-100 ease-in-out',
                        getScreenSize() === 'default' ? 'mobile-hover size-2' : 'size-1 group-hover:size-2',
                    ]"
                ></div>
            </div>

            <input
                @mousemove="handleProgressTooltip"
                @touchmove="handleProgressTooltip"
                @pointerdown="emit('seekPreview')"
                @pointerup="emit('seek')"
                @mouseenter="showTooltip"
                @mouseleave="hideTooltip"
                @touchstart="showTooltip"
                @touchend="hideTooltip"
                @touchcancel="hideTooltip"
                @keydown="handleKeydown"
                ref="progress-bar"
                placeholder="0"
                v-model.number="timeElapsed"
                type="range"
                min="0"
                max="100"
                value="0"
                :step="sliderStep"
                :aria-valuemin="0"
                :aria-valuemax="timeDuration"
                :aria-valuenow="`${timeElapsed as number}`"
                :aria-valuetext="timeElapsedVerbose"
                :class="[`slider pointer-events-auto absolute left-0 top-0 !h-2 w-full items-center`]"
                :style="{
                    '--thumb-color': 'ffffff00',
                    '--track-color': 'ffffff00',
                    '--progress-color': 'ffffff00',
                }"
            />
        </div>
        <slot> </slot>
    </section>
</template>

<style lang="css" scoped>
.thumb {
    transform: translateX(var(--thumb-offset));
}

.group:hover .thumb {
    --thumb-offset: 0px;
}

.mobile-hover .thumb {
    --thumb-offset: 0px;
}

.video-timeline {
    width: calc(100% + 8px);
    left: -4px;
}

.progress {
    --thumb-width: 8;
    --thumb-offset: calc(var(--thumb-width) / 2);
    --container-width: 0;
    --time-elapsed: 0;
    --scale-x: calc((var(--thumb-offset) / 2 / var(--container-width)) * 100 + var(--time-elapsed) * (1 - var(--thumb-offset) / var(--container-width)));

    position: absolute;
    left: 0;
    bottom: 0;
    width: 100%;
    height: 100%;
    transform-origin: left center;

    /* red background: #f03; */
    background: #111827;
}

.group:hover .progress {
    --thumb-offset: var(--thumb-width);
}

.mobile-hover .progress {
    --thumb-offset: var(--thumb-width);
}

.buffer {
    --buffer-color: rgba(255, 255, 255, 0.3);

    --thumb-width: 8;
    --thumb-offset: calc(var(--thumb-width) / 2);
    --container-width: 0;
    --time-buffer: 0;
    --scale-x: calc((var(--thumb-offset) / 2 / var(--container-width)) * 100 + var(--time-buffer) * (1 - var(--thumb-offset) / var(--container-width)));

    position: absolute;
    left: 0;
    bottom: 0;
    width: 100%;
    height: 100%;
    transform-origin: left center;

    background: var(--buffer-color);
}
</style>
