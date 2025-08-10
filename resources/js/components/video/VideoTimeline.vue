<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, useTemplateRef } from 'vue';
import { toFormattedDuration } from '@/service/util';
import { throttle } from 'lodash';

import VideoTooltipSlider from '@/components/video/VideoTooltipSlider.vue';

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
const progressThumb = useTemplateRef('progress-thumb');

const timeSeeking = ref('');
const containerWidth = ref(0);

const timeElapsed = defineModel<number>({ required: true });

const thumbX = computed(() => {
    if (!progressContainer.value) return 0;
    const clamped = Math.min(Math.max(timeElapsed.value, 0), 100);

    return (clamped / 100) * (containerWidth.value - 8);
});

const thumbWidth = ref(8);

const handleProgressTooltip = throttle((event: MouseEvent) => {
    getProgressTooltip(event);
    requestAnimationFrame(() => {
        if (!progressTooltip.value) return;
        progressTooltip.value.calculateTooltipPosition(event);
    });
}, 7);

const getProgressTooltip = (event: MouseEvent) => {
    if (!props.timeDuration || !progressBar.value) return;

    const rect = progressBar.value.getBoundingClientRect();
    const offsetX = event.clientX - rect.left;

    thumbWidth.value = 8;

    const extendedLeft = thumbWidth.value / 2;
    const extendedRight = rect.width - thumbWidth.value / 2;

    const clampedX = Math.min(Math.max(offsetX, extendedLeft), extendedRight);

    const percent = (clampedX - extendedLeft) / (extendedRight - extendedLeft);

    const time = Math.min(Math.max(0, percent * props.timeDuration), props.timeDuration);

    const sliderStep = 0.001;

    // Convert rawPercent to slider value with steps
    // const rawValue = sliderMin + percent * (sliderMax - sliderMin);

    const durationStepped = Math.round((percent * props.timeDuration) / sliderStep) * sliderStep;

    // Round to nearest step
    // console.log(rawValue, steppedValue, percent, percent * props.timeDuration, Math.round(percent / sliderStep) * sliderStep * props.timeDuration);

    const steppedValue = Math.round(percent / sliderStep) * sliderStep;
    const clampedValue = Math.min(Math.max(0, steppedValue), 1);
    const clampedDuration = Math.min(Math.max(0, durationStepped), props.timeDuration);

    // timeSeeking.value = toFormattedDuration(clampedValue * props.timeDuration, true, 'digital') ?? '00:00';
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
    if (resizeObserver && progressContainer.value) {
        resizeObserver.unobserve(progressContainer.value);
    }
});

defineExpose({ progressTooltip });
</script>

<template>
    <!-- Heatmap and Timeline -->
    <section class="flex-1 w-full rounded-full flex flex-col-reverse px-2 h-8 relative">
        <VideoTooltipSlider
            ref="progress-tooltip"
            tooltip-position="top"
            class="-top-4 left-0"
            :tooltip-text="timeSeeking"
            :target-element="progressContainer ?? undefined"
            :offset="videoButtonOffset"
            :tooltip-arrow="false"
        />
        <div class="relative group h-2 flex items-center pointer-events-auto min-h-2" ref="progress-container" role="group" aria-label="Video progress slider">
            <div class="h-1 group-hover:h-2 transition-[height,border-radius] duration-200 ease-in-out w-full rounded-full overflow-clip pointer-events-none bg-white/30">
                <div class="h-full w-full buffer" :style="{ '--buffer': bufferPercentage }">
                    <div
                        class="h-full bg-[#111827]"
                        :style="{
                            transformOrigin: 'left center',
                            transform: `scaleX(${timeElapsed}%)`,
                            // width: `${timeElapsed}%`,
                        }"
                    ></div>
                </div>
            </div>

            <div
                class="absolute top-0.5 group-hover:top-0 transition-[top] duration-200 ease-in-out pointer-events-none z-10"
                :style="{
                    '--thumb-offset': `${(Math.min(Math.max(timeElapsed, 0), 100) / 100) * 4}px`,
                    transform: `translateX(${thumbX}px)`,
                }"
            >
                <div ref="progress-thumb" class="transition-all size-1 group-hover:size-2 duration-200 ease-in-out bg-white rounded-full thumb"></div>
            </div>

            <input
                @mousemove="handleProgressTooltip"
                @pointerdown="emit('seekPreview')"
                @pointerup="emit('seek')"
                @mouseenter="
                    () => {
                        if (!progressTooltip) return;
                        progressTooltip?.tooltipToggle();
                    }
                "
                @mouseleave="
                    () => {
                        if (!progressTooltip) return;
                        progressTooltip?.tooltipToggle(false);
                    }
                "
                @keydown.prevent="(e) => emit('keyBind', e, true)"
                ref="progress-bar"
                placeholder="0"
                v-model.number="timeElapsed"
                type="range"
                min="0"
                max="100"
                value="0"
                step="0.01"
                :aria-valuemin="0"
                :aria-valuemax="timeDuration"
                :aria-valuenow="`${timeElapsed as number}`"
                :aria-valuetext="timeElapsedVerbose"
                :class="[`peer absolute left-0 top-0 w-full !h-2 flex items-center slider pointer-events-auto focus:outline-none`]"
                :style="{
                    // '--buffer': bufferPercentage,
                    '--thumb-color': 'ffffff00',
                    '--track-color': 'ffffff00',
                    '--progress-color': 'ffffff00',
                }"
            />
        </div>
        <slot> </slot>
        {{ thumbWidth }}
        {{ progressBar?.value }}
        {{ thumbX }}
    </section>
</template>

<style lang="css" scoped>
.thumb {
    /* --thumb-offset: 0px; */
    transform: translateX(var(--thumb-offset));
}

.group:hover .thumb {
    --thumb-offset: -0px;
}

.video-timeline {
    width: calc(100% + 8px);
    left: -4px;
}

.buffer {
    --buffer-color: rgba(255, 255, 255, 0.3);
    --buffer: 0;

    background: linear-gradient(
        to right,
        var(--buffer-color) 0%,

        var(--buffer-color) calc(var(--buffer, 0) * 1%),
        rgba(0, 0, 0, 0) calc(var(--buffer, 0) * 1%),
        rgba(0, 0, 0, 0) 100%
    );
}
</style>
