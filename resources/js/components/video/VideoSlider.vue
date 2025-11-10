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
    <div class="relative mx-1 w-12 duration-300 ease-out sm:invisible sm:mx-0 sm:w-0 sm:group-hover:visible sm:group-hover:mx-1 sm:group-hover:w-12">
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
            :class="['slider volume h-2 w-full py-1', style]"
            :title="title"
        />
        <VideoTooltipBase v-if="useTooltip" v-cloak :tooltip-text="text" :tooltip-arrow="tooltipArrow" ref="tooltip" :target-element="targetElement" :verticalOffset="'-3.25rem'" />
    </div>
</template>
