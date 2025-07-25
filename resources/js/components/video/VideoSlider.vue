<script setup lang="ts">
import { useTemplateRef, watch, type Component } from 'vue';

import VideoTooltipBase from '@/components/video/VideoTooltipBase.vue';

import ProiconsFullScreenMaximize from '~icons/proicons/full-screen-maximize';

const props = withDefaults(
    defineProps<{
        icon?: Component;
        title?: string;
        text?: string;
        useTooltip?: boolean;
        tooltipArrow?: boolean;
        style?: string;
        offset?: number;
        targetElement?: HTMLElement;
        controls?: boolean;
        action?: (...args: any[]) => void;
        wheelAction?: (event: WheelEvent) => void;
    }>(),
    {
        icon: ProiconsFullScreenMaximize,
        tooltipArrow: false,
        useTooltip: true,
        style: '',
        offset: 8,
    },
);

const tooltip = useTemplateRef('tooltip');
const model = defineModel();

const tooltipToggle = (event: MouseEvent, state: boolean = true) => {
    if (!props.useTooltip || !tooltip.value) return;

    tooltip.value.tooltipToggle(event, state);
};

watch(
    () => props.controls,
    () => {
        if (!props.controls) tooltipToggle(new MouseEvent('mouseleave'), false);
    },
);
</script>
<template>
    <div class="relative h-full flex items-center mx-1 sm:mx-0 sm:group-hover:mx-1 sm:group-hover:w-12 sm:invisible sm:group-hover:visible w-12 sm:w-0 ease-out duration-300">
        <input
            v-model="model"
            @input="action"
            @wheel="wheelAction"
            @mouseenter="tooltipToggle"
            @mouseleave="(e) => tooltipToggle(e, false)"
            type="range"
            min="0"
            max="1"
            step="0.01"
            :class="['w-full h-2 slider volume', style]"
            :title="title"
        />
        <VideoTooltipBase v-if="useTooltip" v-cloak :tooltip-text="text" :tooltip-arrow="tooltipArrow" ref="tooltip" :target-element="targetElement" />
    </div>
</template>
