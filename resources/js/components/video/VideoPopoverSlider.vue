<script setup lang="ts">
import type { PopoverSlider } from '@/types/types';

const props = withDefaults(defineProps<PopoverSlider>(), { min: 10, max: 200, step: 5 });
const model = defineModel();
</script>
<template>
    <section
        :disabled="disabled"
        :title="title ?? 'Popover Slider'"
        :class="`relative w-full flex flex-wrap gap-y-2 hover:bg-neutral-900 items-center rounded px-2 py-1.5 text-xs outline-none transition-colors data-[disabled=true]:pointer-events-none data-[disabled=true]:opacity-50 ${style ?? ''}`"
        @wheel="wheelAction"
    >
        <component v-if="icon" :is="icon" class="w-4 h-4 mr-2 shrink-0" />

        <span class="text-nowrap">{{ text }}</span>
        <span class="ml-auto text-xs tracking-wide opacity-60">{{ shortcut ?? '' }}</span>
        <input
            type="range"
            @input="action"
            :min="min"
            :max="max"
            :step="step"
            v-model="model"
            :class="`w-full h-full appearance-none flex items-center cursor-pointer bg-transparent slider volume`"
        />
    </section>
</template>

<style scoped>
.slider {
    --thumb-size: 2;
    --thumb-rounded: 9999px; /* rounded-full */
    --track-color: rgba(255, 255, 255, 0.3); /* white with 30% opacity */
    --track-rounded: 9999px; /* rounded-full */
    --progress-color: #111827; /* gray-900 */
}

.slider.timeline {
    --thumb-size: 1;
}

.slider.timeline:hover {
    --thumb-size: 2;
}

.slider.volume {
    --thumb-color: #ffffff;
    --progress-color: #9333eaca;
}

.group:hover .show-fade {
    animation: fadeOut 1s forwards;
    animation-delay: 7s;
}
@keyframes fadeOut {
    0% {
        opacity: 0.65;
    }
    100% {
        opacity: 0;
    }
}

/* WebKit (Chrome, Safari) */
.slider::-webkit-slider-thumb {
    transition: all 200ms ease-in-out;
    appearance: none;
    border: 0;
    background: var(--thumb-color);
    border-radius: var(--thumb-rounded);
    width: calc(var(--thumb-size) * 0.25rem);
    height: calc(var(--thumb-size) * 0.25rem);
    box-shadow: -995px 0 0 992px var(--progress-color);
}

.slider::-webkit-slider-runnable-track {
    background: var(--track-color);
    border-radius: var(--track-rounded);
    overflow: hidden;
}

.slider::-webkit-slider-runnable-track {
    background: var(--track-color);
    border-radius: var(--track-rounded);
    overflow: hidden;
}

/* Firefox */
.slider::-moz-range-thumb {
    transition: all 200ms ease-in-out;
    appearance: none;
    border: 0;
    background: var(--thumb-color);
    border-radius: var(--thumb-rounded);
    width: calc(var(--thumb-size) * 0.25rem);
    height: calc(var(--thumb-size) * 0.25rem);
}

.slider::-moz-range-track {
    transition: all 200ms ease-in-out;
    background: var(--track-color);
    border-radius: var(--track-rounded);
    height: calc(var(--thumb-size) * 0.25rem);
}

.slider::-moz-range-progress {
    transition: all 200ms ease-in-out;
    background: var(--progress-color);
    border-radius: var(--track-rounded);
    height: calc(var(--thumb-size) * 0.25rem);
}

.slider.timeline:hover::-moz-range-track,
.slider.timeline:hover::-moz-range-progress {
    border-radius: 1px;
}
.slider.timeline:hover::-webkit-slider-runnable-track {
    border-radius: 1px;
}
</style>
